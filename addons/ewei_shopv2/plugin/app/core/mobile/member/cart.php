<?php
// 1 小程序添加购物车无反应修复
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Cart_EweiShopV2Page extends AppMobilePage 
{
	public function get_cart() 
	{
		global $_W;
		global $_GPC;
		$uniacid = $_W['uniacid'];
		$openid = $_W['openid'];
		$return_list = $this->_getList();
        $list = $return_list['list'];
		$result = array('ischeckall' => $return_list['ischeckall'], 'total' => $return_list['total'], 'totalprice' => $return_list['totalprice'], 'empty' => $return_list['empty']);
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) 
		{
			$getListUser = $merch_plugin->getListUser($list);
			$merch_user = $getListUser['merch_user'];
			$merch = $getListUser['merch'];
			if (is_array($list) && !(empty($list))) 
			{
				$newlist = array();
				foreach ($merch as $merchid => $merchlist ) 
				{
					$newlist[] = array('merchname' => $merch_user[$merchid]['merchname'], 'merchid' => $merchid, 'list' => $merchlist);
				}
			}
			$result['merch_list'] = $newlist;
		}
		else if ($this->iswxapp) 
		{
			$result['list'] = $list;
		}
		else 
		{
			$result['merch_list'] = array( array('merchname' => '', 'merchid' => 0, 'list' => $list) );
		}
		app_json($result);
	}
	public function add() 
	{
		global $_W;
		global $_GPC;
        $id = intval($_GPC['id']);  	     
		$total = intval($_GPC['total']);
		($total <= 0) && ($total = 1);
		if (empty($id)) 
		{
			app_error(AppError::$ParamsError);
		}
		$optionid = intval($_GPC['optionid']);
		$goods = pdo_fetch('select id,marketprice,`type`,total,diyformid,diyformtype,diyfields, isverify,merchid,cannotrefund,hasoption from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($goods)) 
		{
			app_error(AppError::$GoodsNotFound);
		}
		if ((0 < $goods['hasoption']) && empty($optionid)) 
		{
			app_error(1, '请选择规格!');
		}
		if ($goods['total'] < $total) 
		{
			$total = $goods['total'];
		}
		if (($goods['isverify'] == 2) || ($goods['type'] == 2) || ($goods['type'] == 3) || ($goods['type'] == 5) || !(empty($goods['cannotrefund']))) 
		{
			app_error(AppError::$NotAddCart);
		}
		$diyform_plugin = p('diyform');
		$diyformid = 0;
		$diyformfields = iserializer(array());
		$diyformdata = iserializer(array());
		if ($diyform_plugin) 
		{
			$diyformdata = $_GPC['diyformdata'];
			if (is_string($diyformdata)) 
			{
				$diyformdatastring = htmlspecialchars_decode(str_replace('\\', '', $_GPC['diyformdata']));
				$diyformdata = @json_decode($diyformdatastring, true);
			}
			if (!(empty($diyformdata)) && is_array($diyformdata)) 
			{
				$diyformfields = false;
				if ($goods['diyformtype'] == 1) 
				{
					$diyformid = intval($goods['diyformid']);
					$formInfo = $diyform_plugin->getDiyformInfo($diyformid);
					if (!(empty($formInfo))) 
					{
						$diyformfields = $formInfo['fields'];
					}
				}
				else if ($goods['diyformtype'] == 2) 
				{
					$diyformfields = iunserializer($goods['diyfields']);
				}
				if (!(empty($diyformfields))) 
				{
					$insert_data = $diyform_plugin->getInsertData($diyformfields, $diyformdata, true);
					$diyformdata = $insert_data['data'];
					$diyformfields = iserializer($diyformfields);
				}
			}
		}      
        $data = pdo_fetch('select id,total,diyformid from ' . tablename('ewei_shop_member_cart') . ' where goodsid=:id and openid=:openid and   optionid=:optionid  and deleted=0 and  uniacid=:uniacid   limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ':optionid' => $optionid, ':id' => $id));		
        if (empty($data)) 
        {
            $data = array('uniacid' => $_W['uniacid'], 'merchid' => $goods['merchid'], 'openid' => $_W['openid'], 'goodsid' => $id, 'optionid' => $optionid, 'marketprice' => $goods['marketprice'], 'total' => $total, 'selected' => 1, 'diyformid' => $diyformid, 'diyformdata' => $diyformdata, 'diyformfields' => $diyformfields, 'createtime' => time());
            pdo_insert('ewei_shop_member_cart', $data);
        }
        else 
        {
            $data['diyformid'] = $diyformid;
            $data['diyformdata'] = $diyformdata;
            $data['diyformfields'] = $diyformfields;
            $data['total'] += $total;
            pdo_update('ewei_shop_member_cart', $data, array('id' => $data['id']));
        }       
		$cartcount = pdo_fetchcolumn('select sum(total) from ' . tablename('ewei_shop_member_cart') . ' where openid=:openid and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        $return = array(
            'isnew'         => false,
            'cartcount'     => $cartcount
        );
        
		app_json($return);
	}
	public function update() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$goodstotal = intval($_GPC['total']);
		if (empty($id)) 
		{
			app_error(AppError::$ParamsError);
		}
		$optionid = intval($_GPC['optionid']);
		empty($goodstotal) && ($goodstotal = 1);
		$data = pdo_fetch('select * from ' . tablename('ewei_shop_member_cart') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		if (empty($data)) 
		{
			app_error(AppError::$NotInCart);
		}
		$goods = pdo_fetch('select id,maxbuy,minbuy,total,unit from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid and status=1 and deleted=0', array(':id' => $data['goodsid'], ':uniacid' => $_W['uniacid']));
		if (empty($goods)) 
		{
			app_error(AppError::$GoodsNotFound);
		}
		$diyform_plugin = p('diyform');
		$diyformid = 0;
		$diyformfields = iserializer(array());
		$diyformdata = iserializer(array());
		if ($diyform_plugin) 
		{
			$diyformdata = $_GPC['diyformdata'];
			if (!(empty($diyformdata)) && is_string($diyformdata)) 
			{
				$diyformdatastring = htmlspecialchars_decode(str_replace('\\', '', $_GPC['diyformdata']));
				$diyformdata = @json_decode($diyformdatastring, true);
			}
			if (!(empty($diyformdata)) && is_array($diyformdata)) 
			{
				$diyformfields = false;
				if ($goods['diyformtype'] == 1) 
				{
					$diyformid = intval($goods['diyformid']);
					$formInfo = $diyform_plugin->getDiyformInfo($diyformid);
					if (!(empty($formInfo))) 
					{
						$diyformfields = $formInfo['fields'];
					}
				}
				else if ($goods['diyformtype'] == 2) 
				{
					$diyformfields = iunserializer($goods['diyfields']);
				}
				if (!(empty($diyformfields))) 
				{
					$insert_data = $diyform_plugin->getInsertData($diyformfields, $diyformdata, true);
					$diyformdata = $insert_data['data'];
					$diyformfields = iserializer($diyformfields);
				}
			}
		}
		$arr = array('total' => $goodstotal, 'optionid' => $optionid, 'diyformid' => $diyformid, 'diyformdata' => $diyformdata, 'diyformfields' => $diyformfields);
		pdo_update('ewei_shop_member_cart', $arr, array('id' => $id, 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
		app_json();
	}
	public function remove() 
	{
		global $_W;
		global $_GPC;
		$ids = $_GPC['ids'];
		if (empty($ids)) 
		{
			app_error(AppError::$ParamsError);
		}
		if (!(is_array($ids))) 
		{
			$ids = htmlspecialchars_decode(str_replace('\\', '', $ids));
			$ids = @json_decode($ids, true);
		}
		if (empty($ids)) 
		{
			app_error(AppError::$ParamsError);
		}
		$sql = 'update ' . tablename('ewei_shop_member_cart') . ' set deleted=1 where uniacid=:uniacid and openid=:openid and id in (' . implode(',', $ids) . ')';
		pdo_query($sql, array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
		app_json();
	}
	public function tofavorite() 
	{
		global $_W;
		global $_GPC;
		$uniacid = $_W['uniacid'];
		$openid = $_W['openid'];
		$ids = $_GPC['ids'];
		if (empty($ids)) 
		{
			app_error(AppError::$ParamsError);
		}
		if (!(is_array($ids))) 
		{
			$ids = htmlspecialchars_decode(str_replace('\\', '', $ids));
			$ids = @json_decode($ids, true);
		}
		foreach ($ids as $id ) 
		{
			$goodsid = pdo_fetchcolumn('select goodsid from ' . tablename('ewei_shop_member_cart') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1 ', array(':id' => $id, ':uniacid' => $uniacid, ':openid' => $openid));
			if (!(empty($goodsid))) 
			{
				$fav = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where goodsid=:goodsid and uniacid=:uniacid and openid=:openid and deleted=0 limit 1 ', array(':goodsid' => $goodsid, ':uniacid' => $uniacid, ':openid' => $openid));
				if ($fav <= 0) 
				{
					$fav = array('uniacid' => $uniacid, 'goodsid' => $goodsid, 'openid' => $openid, 'deleted' => 0, 'createtime' => time());
					pdo_insert('ewei_shop_member_favorite', $fav);
				}
			}
		}
		$sql = 'update ' . tablename('ewei_shop_member_cart') . ' set deleted=1 where uniacid=:uniacid and openid=:openid and id in (' . implode(',', $ids) . ')';
		pdo_query($sql, array(':uniacid' => $uniacid, ':openid' => $openid));
		app_json();
	}
	public function select() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$select = intval($_GPC['select']);
		if (!(empty($id))) 
		{
			$data = pdo_fetch('select id,goodsid,optionid, total from ' . tablename('ewei_shop_member_cart') . ' ' . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
			if (!(empty($data))) 
			{
				pdo_update('ewei_shop_member_cart', array('selected' => $select), array('id' => $id, 'uniacid' => $_W['uniacid']));
			}
		}
		else 
		{
			pdo_update('ewei_shop_member_cart', array('selected' => $select), array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
		}
		app_json();
	}
	public function count() 
	{
		global $_W;
		global $_GPC;
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$cartcount = (int) pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and selected =1', $params);
		app_json(array('cartcount' => $cartcount));
	}
    
    /**
     * 导购扫码
     */
    public function saleradd(){
        global $_W;
		global $_GPC;  
        $store_status = m("store")->getStoreStatus($_W['openid']);
        if(!$store_status){
            app_error(AppError::$StoreNotValid);
        }
        $saler = m("store")->getSalerInfo();
        if(!$saler || $saler['status'] != 1 || $saler['is_delete'] == 1){
            app_error(AppError::$SalerNotValid);
        }
        $qid = intval($_GPC['qid']);
        $no = intval($_GPC['no']);

        if($qid){
            //获取二维码对应的商品数据
            $params = array(
                ':qid'  => $qid,
                ':uniacid'  => $_W['uniacid']
            );
            $sql = "SELECT goodsid FROM " . tablename('ewei_shop_goods_qrcode') . " WHERE id = :qid AND uniacid = :uniacid";
            $item = pdo_fetch($sql, $params);
            if($item){
                $id = $item['goodsid'];
            } else {
                app_error(AppError::$QrcodeNotFound);
            }
            
            //查找该二维码是否已被使用
            if(empty($no)){
                app_error(AppError::$QrcodeNotFound);
            }                     
        }		     
		$total = intval($_GPC['total']);
		($total <= 0) && ($total = 1);
		if (empty($id)) 
		{
			app_error(AppError::$ParamsError);
		}
		$optionid = intval($_GPC['optionid']);
		$goods = pdo_fetch('select id,marketprice,`type`,total,diyformid,diyformtype,diyfields, isverify,merchid,cannotrefund,hasoption from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($goods)) 
		{
			app_error(AppError::$GoodsNotFound);
		}
		if ((0 < $goods['hasoption']) && empty($optionid)) 
		{
			app_error(1, '请选择规格!');
		}
		if ($goods['total'] < $total) 
		{
			$total = $goods['total'];
		}
		if ((($goods['isverify'] == 2) || ($goods['type'] == 2) || ($goods['type'] == 3) || ($goods['type'] == 5) || !(empty($goods['cannotrefund']))) && !$qid) 
		{
			app_error(AppError::$NotAddCart);
		}
		$diyform_plugin = p('diyform');
		$diyformid = 0;
		$diyformfields = iserializer(array());
		$diyformdata = iserializer(array());
		if ($diyform_plugin) 
		{
			$diyformdata = $_GPC['diyformdata'];
			if (is_string($diyformdata)) 
			{
				$diyformdatastring = htmlspecialchars_decode(str_replace('\\', '', $_GPC['diyformdata']));
				$diyformdata = @json_decode($diyformdatastring, true);
			}
			if (!(empty($diyformdata)) && is_array($diyformdata)) 
			{
				$diyformfields = false;
				if ($goods['diyformtype'] == 1) 
				{
					$diyformid = intval($goods['diyformid']);
					$formInfo = $diyform_plugin->getDiyformInfo($diyformid);
					if (!(empty($formInfo))) 
					{
						$diyformfields = $formInfo['fields'];
					}
				}
				else if ($goods['diyformtype'] == 2) 
				{
					$diyformfields = iunserializer($goods['diyfields']);
				}
				if (!(empty($diyformfields))) 
				{
					$insert_data = $diyform_plugin->getInsertData($diyformfields, $diyformdata, true);
					$diyformdata = $insert_data['data'];
					$diyformfields = iserializer($diyformfields);
				}
			}
		}
        
        //导购扫码商品直接在购物车添加一条记录，相同商品不叠加在一起               
        $error = 0;
        $return = array();
        $cartid = 0; 
        $is_add = true;  //是否添加商品到购物车
        
        //查找商品是否已购买或者已被扫码
        $log = m('qrcode')->getCodeLog($qid, $no);   
        if($log){
            if($log['saleropenid'] != $_W['openid']){
                //查找之前扫的人是否已生成订单
                $sql = "SELECT orderid FROM " . tablename('ewei_shop_order_goods') . " WHERE qrcodeid = $qid AND qrcodeno = $no";
                $orderid = pdo_fetchcolumn($sql);
                if($orderid){
                    $is_add = false;
                    $error = AppError::$QrcodeisUsedOther;
                } else {
                    //没生成订单，删除之前的记录
                    pdo_delete('ewei_shop_member_salercart', array('qrcodeid' => $qid, 'qrcodeno' => $no));
                    $update = array(
                        'openid'        => '0',
                        'saleropenid'   => $_W['openid'],
                        'updatetime'    => time()
                    );
                    pdo_update('ewei_shop_goods_qrcode_log', $update, array('qid' => $qid, 'no' => $no));
                }
            } else {
                $is_add = false;
                $error = AppError::$QrcodeIsUsed;
            }  
        } else {
            app_error(AppError::$QrcodeNotFound);
        }
        
        if($is_add) {
            $data = array(
                'uniacid' => $_W['uniacid'], 
                'merchid' => $goods['merchid'], 
                'openid' => $_W['openid'], 
                'saleropenid' => $_W['openid'],
                'goodsid' => $id, 
                'optionid' => $optionid,
                'marketprice' => $goods['marketprice'], 
                'total' => $total, 
                'selected' => 1, 
                'diyformid' => $diyformid, 
                'diyformdata' => $diyformdata, 
                'diyformfields' => $diyformfields, 
                'createtime' => time(),
                'qrcodeid' => intval($_GPC['qid']),
                'qrcodeno' => intval($_GPC['no'])
            );
            pdo_insert('ewei_shop_member_salercart', $data);
            $cartid = pdo_insertid();           
        }
                   
		$cartcount = pdo_fetchcolumn('select sum(total) from ' . tablename('ewei_shop_member_salercart') . ' where openid=:openid and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        $return['isnew'] = false;
        $return['cartcount'] = $cartcount;
        
        //获取可使用的社交优惠券数量
        $com = com('coupon');
        $list = $com->getSalerSocialCoupon($this->member['openid']);
        foreach($list as &$row){
            $row['expire_start']    = date("Y.m.d", $row['gettime']);
            $row['expire_end']      = date("Y.m.d", $row['gettime'] + ($row['timedays'] * 3600 * 24));
        }
        unset($row);
        $return['couponcount']      = count($list);
        $return['couponlist']       = $list;
        
        if($is_add){
            $cart_model = m('cart');
            $goods_coupon = $cart_model->getCouponsByGood($id, array(COUPON_SOCIAL));
            $return['goods_coupon']     = $goods_coupon;
            $return['goods']            = $goods;
        }
        
        //返回购物车中的商品信息
        $return_list = $this->_getList('ewei_shop_member_salercart');
        $cartindex = 0;
        foreach($return_list as $index => $list){
            if($id == $list['goodsid']){
                $cartindex = $index;
            }            
        }
        //获取与商品关联的优惠券信息
        foreach($return_list['list'] as &$goods){
            if($goods['couponid']){
                $info = com('coupon')->getCouponByDataID($goods['couponid']);
                $goods['couponname'] = $info['couponname'];
                $goods['coupondeduct'] = $info['deduct'];
            }
        }
        
        $return['total']        = $return_list['total'];
        $return['totalprice']   = $return_list['totalprice'];
        $return['empty']        = $return_list['empty'];
        $return['list']         = $return_list['list'];
        $return['id']           = $cartid;      
        $return['goodsid']      = $id;
        $return['cartindex']    = $cartindex;
        $return['error']        = $error;
        $return['message']      = AppError::getError($error);
        
		app_json($return);
    }
      
    private function _getList($table = 'ewei_shop_member_cart'){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
		$openid = $_W['openid'];
		$condition = ' and f.uniacid= :uniacid and f.openid=:openid and f.deleted=0';
		$params = array(':uniacid' => $uniacid, ':openid' => $openid);
		$list = array();
		$total = 0;
		$totalprice = 0;
		$ischeckall = true;
		$level = m('member')->getLevel($openid);
        $fields = ' f.id,f.total,f.goodsid,g.total as stock, o.stock as optionstock, g.maxbuy,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,' .
               ' g.productprice,o.title as optiontitle,f.optionid,o.specs,g.minbuy,g.maxbuy,g.unit,f.merchid,g.merchsale' . 
               ' ,f.selected';
        if($table == 'ewei_shop_member_salercart'){
            $fields .= ', f.couponid ';
        }
		$sql = 'SELECT ' . $fields . ' FROM ' . tablename($table) . ' f ' . 
               ' left join ' . tablename('ewei_shop_goods') . ' g on f.goodsid = g.id ' . 
               ' left join ' . tablename('ewei_shop_goods_option') . ' o on f.optionid = o.id ' . 
               ' where 1 ' . $condition . ' ORDER BY `id` DESC ';
		$list = pdo_fetchall($sql, $params);
		foreach ($list as &$g ) 
		{
			if (!(empty($g['optionid']))) 
			{
				$g['stock'] = $g['optionstock'];
				if (!(empty($g['specs']))) 
				{
					$thumb = m('goods')->getSpecThumb($g['specs']);
					if (!(empty($thumb))) 
					{
						$g['thumb'] = $thumb;
					}
				}
			}
			$g['marketprice'] = (double) $g['marketprice'];
			$totalmaxbuy = $g['stock'];
			if (0 < $g['maxbuy']) 
			{
				if ($totalmaxbuy != -1) 
				{
					if ($g['maxbuy'] < $totalmaxbuy) 
					{
						$totalmaxbuy = $g['maxbuy'];
					}
				}
				else 
				{
					$totalmaxbuy = $g['maxbuy'];
				}
			}
			if (0 < $g['usermaxbuy']) 
			{
				$order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join ' . tablename('ewei_shop_order') . ' o on og.orderid=o.id ' . ' where og.goodsid=:goodsid and  o.status>=1 and o.openid=:openid  and og.uniacid=:uniacid ', array(':goodsid' => $g['goodsid'], ':uniacid' => $uniacid, ':openid' => $openid));
				$last = $g['usermaxbuy'] - $order_goodscount;
				if ($last <= 0) 
				{
					$last = 0;
				}
				if ($totalmaxbuy != -1) 
				{
					if ($last < $totalmaxbuy) 
					{
						$totalmaxbuy = $last;
					}
				}
				else 
				{
					$totalmaxbuy = $last;
				}
			}
			if (0 < $g['minbuy']) 
			{
				if ($totalmaxbuy < $g['minbuy']) 
				{
					$g['minbuy'] = $totalmaxbuy;
				}
			}
			if ($totalmaxbuy < $g['total']) 
			{
				$g['total'] = $totalmaxbuy;
			}
			if ($g['selected']) 
			{
				$prices = m('order')->getGoodsDiscountPrice($g, $level, 1);
				$g['marketprice'] = $prices['price'];
				$totalprice += $g['marketprice'] * $g['total'];
				$total += $g['total'];
			}
			$g['totalmaxbuy'] = $totalmaxbuy;
			$g['productprice'] = price_format($g['productprice']);
			$g['unit'] = ((empty($data['unit']) ? '件' : $data['unit']));
			if (empty($g['selected'])) 
			{
				$ischeckall = false;
			}
			unset($g['maxbuy']);
		}
		unset($g);
		$list = set_medias($list, 'thumb');
        $return = array(
            'total'             => (int)$total,
            'totalprice'        => (double)$totalprice,
            'ischeckall'        => $ischeckall,
            'empty'             => empty($list),
            'list'              => $list
        );
        return $return;
    }
    
    public function get_goods_coupon(){
        global $_W;
        global $_GPC;
        $goodsid = intval($_GPC['goodsid']);
        $openid = $_W['openid'];
        if(!$goodsid){
            app_error(AppError::$NotAddCart);
        }
        $cart_model = m('cart');
        $goods_coupon = $cart_model->getCouponsByGood($goodsid, array(COUPON_SOCIAL));
        foreach($goods_coupon as &$row){
            $row['expire_start']    = date("Y.m.d", $row['gettime']);
            $row['expire_end']      = date("Y.m.d", $row['gettime'] + ($row['timedays'] * 3600 * 24));
            //获取优惠券发送人信息
            if($row['sender']){
                if($row['sender'] == $row['openid']){
                    $row['sender_avatar'] = '';
                    switch ($row['gettype']){
                        case COUPON_SENDSHARE:
                            $row['sender_nickname'] = "发券分享";
                            break;
                        case COUPON_BUY:
                            $row['sender_nickname'] = "购买赠送";
                            break;
                        case COUPON_BACKEND:
                            $row['sender_nickname'] = "系统赠送";
                            break;
                        default : $row['sender_nickname'] = "系统赠送";
                            break;
                    }
                } else {
                    $sender = m('member')->getInfo($row['sender']);
                    if($sender){
                        $row['sender_nickname'] = $sender['nickname'];
                        $row['sender_avatar'] = $sender['avatar'];
                    }
                }                
            }
            $row['has_benefit'] = $openid == $row['sender'] ? 1 : 0 ;
        }
        unset($row);
        app_json(array('goods_coupon' => $goods_coupon));
    }
    
    public function set_goods_coupon(){
        global $_W;
        global $_GPC;
        $where = array(
            'uniacid'           => $_W['uniacid'],
            'id'                => intval($_GPC['id']),
            'openid'            => $_W['openid']
        );
        $res = pdo_update('ewei_shop_member_salercart', array('couponid' => intval($_GPC['couponid'])), $where);
        $return = array();
        //获取优惠券信息
        $info = com('coupon')->getCouponByDataID($_GPC['couponid']);
        $return['couponname'] = $info['couponname'];
        $return['coupondeduct'] = $info['deduct'];
        if($res !== false){
            app_json($return);
        }
        app_error(AppError::$SystemError);
    }
    
    public function change_cart_owner(){
        global $_W;
        global $_GPC;
        $salerid = intval($_GPC['salerid']);
        $saler = m('store')->getSalerInfo(array('id' => $salerid));
        if($saler){
            $where = array(
                'saleropenid'       => $saler['openid'],
                'deleted'           => 0,
                'selected'          => 1,
                'uniacid'           => $_W['uniacid']
            );
            pdo_update('ewei_shop_member_salercart', array('openid' => $_W['openid']), $where);
            $this->change_saler_goods_coupon();
            app_json();
        } else {
            app_error(AppError::$NotAddCart);
        }
    }
    
    private function change_saler_goods_coupon(){
        global $_W;
        $where = array(
            ':openid'       => $_W['openid'],
            ':uniacid'      => $_W['uniacid']
        );
        $sql = "SELECT * FROM " . tablename("ewei_shop_member_salercart") . " WHERE openid = :openid AND uniacid = :uniacid AND deleted = 0 AND selected = 1 ORDER BY couponid ASC";
        $cart_goods = pdo_fetchall($sql, $where);
        $model = m("goods");
        $selected = array();
        foreach($cart_goods as $goods){
            $couponid = $model->get_auto_goods_coupon($_W['openid'], $goods['goodsid'], $selected);
            if($couponid){
                pdo_update("ewei_shop_member_salercart", array("couponid" => $couponid), array("id" => $goods['id']));
                $selected[] = $couponid;
            }            
        }
    }
    
    public function get_salercart(){
        global $_W;
        global $_GPC;
        $list = $this->_getList('ewei_shop_member_salercart');
        $goods_model = m('goods');
        $barcode = $this->get_cart_goods_barcode();
        foreach($list['list'] as &$row){
            $goods_coupon = $goods_model->calculate_goods_coupon($row['goodsid'], $row['couponid']);
            if($goods_coupon){
                $row['deductprice'] = $goods_coupon['deductprice'];
                $row['originprice'] = $goods_coupon['marketprice'];
                $row['couponprice'] = $goods_coupon['couponprice'];
            } else {
                $row['deductprice'] = 0;
                $row['originprice'] = $row['marketprice'];
                $row['couponprice'] = $row['marketprice'];
            }
            $row['barcode'] = $barcode[$row['id']];
        }
        $result = array(
            'list'      => $list['list'],
            'total'     => $list['total'],
            'totalprice'    => $list['totalprice'],
            'ischeckall'    => $list['ischeckall']
        );
        app_json($result);
    }
    
    public function saler_cart_remove(){
        global $_W;
        global $_GPC;
        $id = $_GPC['id'];
        $sql = "SELECT * FROM " . tablename("ewei_shop_member_salercart") . " WHERE uniacid = {$_W['uniacid']} AND id = $id";
        $cart_row = pdo_fetch($sql);
        if($cart_row){
            if($cart_row['qrcodeid'] && $cart_row['qrcodeno']){
                $qrcode_sql = "SELECT * FROM " . tablename("ewei_shop_goods_qrcode_log") . " WHERE qid = {$cart_row['qrcodeid']} AND no = {$cart_row['qrcodeno']}";
                $code_log = pdo_fetch($qrcode_sql);
                if($code_log){
                    pdo_delete("ewei_shop_goods_qrcode_log", array('id' => $code_log['id']));
                }
            }
            pdo_delete("ewei_shop_member_salercart", array('id' => $id));
        }
        
        $list = $this->_getList('ewei_shop_member_cart');
        $return = array(
            'has_coupon'    => 0
        );
        if(!empty($cart_row['couponid'])){
            $return['has_coupon'] = 1;
        }
        
        app_json($return);
    }
    
    /**
     * 获取saler cart中商品的条码
     */
    public function get_cart_goods_barcode(){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
        $openid = $_W['openid'];
        $barcode_list = array();
        $sql = " SELECT g.originbarcode, g.couponbarcode, g.barcodecoupontype, g.verifybarcode, s.couponid, s.id
                 FROM " . tablename('ewei_shop_member_salercart') . ' s '.
               " INNER JOIN " . tablename('ewei_shop_goods') . " g ON g.id = s.goodsid AND g.uniacid = :uniacid
                 WHERE s.openid = :openid AND s.selected = 1 AND s.deleted = 0";
        $cart_goods = pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $uniacid)); 
        foreach($cart_goods as $goods){
            $barcode_type = 1; //原价
            if($goods['couponid']){
                $coupon = com('coupon')->getCouponByDataID($goods['couponid']);
                if($coupon && $coupon['coupontype'] == $goods['barcodecoupontype']){
                    $barcode_type = 2; //优惠券价
                }
            }
            switch ($barcode_type){
                case 1:
                    $barcode_list[$goods['id']] = $goods['originbarcode'];
                    break;
                case 2:
                    $barcode_list[$goods['id']] = $goods['couponbarcode'];
                    break;
                default : 
                    $barcode_list[$goods['id']] = $goods['originbarcode'];
                    break;
            }
        }
         return $barcode_list;     
    }
}
?>