<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
require(EWEI_SHOPV2_PLUGIN . "app/core/page_mobile.php");
class Op_EweiShopV2Page extends AppMobilePage 
{
	public function cancel() 
	{
		global $_W;
		global $_GPC;
		$orderid = intval($_GPC["id"]);
		if( empty($orderid) ) 
		{
			app_error(AppError::$ParamsError);
		}
		$order = pdo_fetch("select id,ordersn,openid,status,deductcredit,deductcredit2,deductprice,couponid,`virtual`,`virtual_info`,merchid, multicoupon  from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));
		if( empty($order) ) 
		{
			app_error(AppError::$OrderNotFound);
		}
		if( 0 < $order["status"] ) 
		{
			app_error(AppError::$OrderCannotCancel);
		}
		if( $order["status"] < 0 ) 
		{
			app_error(AppError::$OrderCannotCancel);
		}
        if($order['multicoupon'] && $order['multicoupon'] == 1){
            $_GPC['orderid'] = $orderid;
            $this->saler_cancel();
        }
		if( !empty($order["virtual"]) && $order["virtual"] != 0 ) 
		{
			$goodsid = pdo_fetch("SELECT goodsid FROM " . tablename("ewei_shop_order_goods") . " WHERE uniacid = " . $_W["uniacid"] . " AND orderid = " . $order["id"]);
			$typeid = $order["virtual"];
			$vkdata = ltrim($order["virtual_info"], "[");
			$vkdata = rtrim($vkdata, "]");
			$arr = explode("}", $vkdata);
			foreach( $arr as $k => $v ) 
			{
				if( !$v ) 
				{
					unset($arr[$k]);
				}
			}
			$vkeynum = count($arr);
			pdo_query("update " . tablename("ewei_shop_virtual_data") . " set openid=\"\",usetime=0,orderid=0,ordersn=\"\",price=0,merchid=" . $order["merchid"] . " where typeid=" . intval($typeid) . " and orderid = " . $order["id"]);
			pdo_query("update " . tablename("ewei_shop_virtual_type") . " set usedata=usedata-" . $vkeynum . " where id=" . intval($typeid));
		}
		m("order")->setStocksAndCredits($orderid, 2);
		if( 0 < $order["deductprice"] ) 
		{
			m("member")->setCredit($order["openid"], "credit1", $order["deductcredit"], array( "0", $_W["shopset"]["shop"]["name"] . "购物返还抵扣积分 积分: " . $order["deductcredit"] . " 抵扣金额: " . $order["deductprice"] . " 订单号: " . $order["ordersn"] ));
		}
		m("order")->setDeductCredit2($order);
		if( com("coupon") && !empty($order["couponid"]) ) 
		{
			com("coupon")->returnConsumeCoupon($orderid);
		}
		pdo_update("ewei_shop_order", array( "status" => -1, "canceltime" => time(), "closereason" => trim($_GPC["remark"]) ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
		m("notice")->sendOrderMessage($orderid);      
		app_json();
	}
	public function finish() 
	{
		global $_W;
		global $_GPC;
		$orderid = intval($_GPC["id"]);
		if( empty($orderid) ) 
		{
			app_error(AppError::$ParamsError);
		}
		$order = pdo_fetch("select id,status,openid,couponid,refundstate,refundid from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));
		if( empty($order) ) 
		{
			app_error(AppError::$OrderNotFound);
		}
		if( $order["status"] != 2 ) 
		{
			app_error(AppError::$OrderCannotFinish);
		}
		if( 0 < $order["refundstate"] && !empty($order["refundid"]) ) 
		{
			$change_refund = array( );
			$change_refund["status"] = -2;
			$change_refund["refundtime"] = time();
			pdo_update("ewei_shop_order_refund", $change_refund, array( "id" => $order["refundid"], "uniacid" => $_W["uniacid"] ));
		}
		pdo_update("ewei_shop_order", array( "status" => 3, "finishtime" => time(), "refundstate" => 0 ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
		m("order")->setStocksAndCredits($orderid, 3);
		m("order")->fullback($orderid);
		m("member")->upgradeLevel($order["openid"], $orderid);
		m("order")->setGiveBalance($orderid, 1);       
		if( com("coupon") ) 
		{
			com("coupon")->sendcouponsbytask($orderid);
		}
		if( com("coupon") && !empty($order["couponid"]) ) 
		{
			com("coupon")->backConsumeCoupon($orderid);
		}
        
		m("notice")->sendOrderMessage($orderid);
		if( p("commission") ) 
		{
			p("commission")->checkOrderFinish($orderid);
		}
		app_json();
	}
	public function delete() 
	{
		global $_W;
		global $_GPC;
		$orderid = intval($_GPC["id"]);
		$userdeleted = intval($_GPC["userdeleted"]);
		if( empty($orderid) ) 
		{
			app_error(AppError::$ParamsError);
		}
		$order = pdo_fetch("select id,status,refundstate,refundid from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));
		if( empty($order) ) 
		{
			app_error(AppError::$OrderNotFound);
		}
		if( $userdeleted == 0 ) 
		{
			if( $order["status"] != 3 ) 
			{
				app_error(AppError::$OrderCannotRestore);
			}
		}
		else 
		{
			if( $order["status"] != 3 && $order["status"] != -1 ) 
			{
				app_error(AppError::$OrderCannotDelete);
			}
			if( 0 < $order["refundstate"] && !empty($order["refundid"]) ) 
			{
				$change_refund = array( );
				$change_refund["status"] = -2;
				$change_refund["refundtime"] = time();
				pdo_update("ewei_shop_order_refund", $change_refund, array( "id" => $order["refundid"], "uniacid" => $_W["uniacid"] ));
			}
		}
		pdo_update("ewei_shop_order", array( "userdeleted" => $userdeleted, "refundstate" => 0 ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
		app_json();
	}
    
    public function saler_order_finish(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $orderid = intval($_GPC['orderid']);
        $saler = m('store')->getSalerInfo();
        if($saler){
            //获取订单数据
            $sql     = "SELECT * FROM " . tablename('ewei_shop_order') . 
                       " WHERE saleropenid = :saleropenid AND id = :orderid AND uniacid = :uniacid";
            $params  = array(
                ':saleropenid'      => $openid,
                ':orderid'          => $orderid,
                ':uniacid'          => $_W['uniacid']
            );
            $order = pdo_fetch($sql, $params);
            if($order){
                if($order['status'] >= 0){
                    $update = array(
                        'status'    => 2,
                        'paytype'   => 4,
                        'paytime'   => time(),
                        'verified'  => 1,
                        'verifytime' => time(),
                        'verifyopenid' => $openid,
                        'verifystoreid' => $saler['storeid'],
                        'sendtime'  => time(),
                        'fetchtime' => time()
                    );
                    pdo_update('ewei_shop_order', $update, array('id' => $orderid, 'uniacid' => $_W['uniacid']));
                    $this->_saler_finish($orderid);
                } else {
                    app_error(AppError::$OrderOnlyVerify);
                }
            } else {
                app_error(AppError::$OrderNotFound);
            }
        } else {
            app_error(AppError::$OrderCreateNoSaler);
        }
    }
    
    private function _saler_finish($orderid) {
        global $_W;
		if( empty($orderid) ) 
		{
			app_error(AppError::$ParamsError);
		}
		$order = pdo_fetch("select id,status,openid,couponid,refundstate,refundid from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid  limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"]));
		if( empty($order) ) 
		{
			app_error(AppError::$OrderNotFound);
		}
		if( $order["status"] != 2 ) 
		{
			app_error(AppError::$OrderCannotFinish);
		}
		if( 0 < $order["refundstate"] && !empty($order["refundid"]) ) 
		{
			$change_refund = array( );
			$change_refund["status"] = -2;
			$change_refund["refundtime"] = time();
			pdo_update("ewei_shop_order_refund", $change_refund, array( "id" => $order["refundid"], "uniacid" => $_W["uniacid"] ));
		}
		pdo_update("ewei_shop_order", array( "status" => 3, "finishtime" => time(), "refundstate" => 0 ), array( "id" => $orderid, "uniacid" => $_W["uniacid"] ));
		m("order")->setStocksAndCredits($orderid, 3);
		m("order")->fullback($orderid);
		m("member")->upgradeLevel($order["openid"], $orderid);
		m("order")->setGiveBalance($orderid, 1);       
		if( com("coupon") ) 
		{
			com("coupon")->sendcouponsbytask($orderid);
		}
		if( com("coupon") && !empty($order["couponid"]) ) 
		{
			com("coupon")->backConsumeCoupon($orderid);
		}
        
		m("notice")->sendOrderMessage($orderid);
		if( p("commission") ) 
		{
			p("commission")->checkOrderFinish($orderid);
		}
        //返回订单分润记录
        $benefit_log = pdo_get("ewei_shop_order_benefit_log", array("orderid" => $orderid));
		app_json(array("log" => $benefit_log));
	}
    
    /**
     * 导购取消客户订单
     */
    public function saler_cancel(){
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC['orderid']);
        $params = array(
            ':uniacid'      => $_W['uniacid']
        );
        $sql = "SELECT id, ordersn, status FROM " . tablename('ewei_shop_order') . 
               " WHERE id = $orderid AND uniacid = :uniacid AND status = 0 ";
        $order = pdo_fetch($sql, $params);
        if($order){
            //返还已选择的优惠券
            $sql = "UPDATE " . tablename("ewei_shop_coupon_data") . 
                   "SET used = 0, usetime = 0, ordersn = '' " .
                   "WHERE ordersn = '{$order['ordersn']}'";
            pdo_query($sql);
            //删除订单商品及相关扫描记录
            $sql = "DELETE og, ql " .
                   " FROM " . tablename("ewei_shop_order_goods") . " og " . 
                   "LEFT JOIN " . tablename("ewei_shop_goods_qrcode_log") . " ql ON ql.qid = og.qrcodeid AND ql.no = og.qrcodeno " .
                   "WHERE og.orderid = {$orderid}";
            pdo_query($sql);
            
            //删除订单
            pdo_delete('ewei_shop_order', array('id' => $orderid));
            m("notice")->sendOrderMessage($orderid);
            app_json(array('message' => '成功取消订单'));
        } else {
            app_error(AppError::$OrderCannotCancel);
        }
    }
    
    /**
     * 修改订单支付方式
     */
    public function set_order_paytype(){
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC['orderid']);
        $paytype = isset($_GPC['paytype']) ? intval($_GPC['paytype']) : 0;
        $order_model = m('order');
        $order_model->setOrderPayType($orderid, $paytype);
        app_json();
    }
    
    /**
     * 绑定订单商品的二维码
     * @global array $_W
     * @global array $_GPC
     */
    public function set_goods_qrcode(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $orderid = $_GPC['orderid'];
        //订单商品表的id
        $order_goodsid = $_GPC['order_goodsid'];
        $qrcodeid = $_GPC['qrcodeid'];
        $qrcodeno = $_GPC['qrcodeno'];
        if($orderid && $order_goodsid){
            $order = m("order")->getOrderDetail($orderid);
            $order_goods = pdo_get("ewei_shop_order_goods", array("id" => $order_goodsid));
            if($order_goods['qrcodeid'] && $order_goods['qrcodeno']){
                app_error(-3, "此订单商品已绑定二维码");
            }
            
            $log = pdo_get("ewei_shop_goods_qrcode_log", array("qid" => $qrcodeid, "no" => $qrcodeno));
            if($log){
                //查看订单状态
                if($log['saleropenid'] != $openid){
                    $sql = "SELECT o.status, o.paytype FROM " . tablename("ewei_shop_order_goods") . " o " .
                           "LEFT JOIN " . tablename("ewei_shop_order") . " og ON og.orderid = o.id ".
                           "WHERE og.qrcodeid = {$log['qid']} AND og.qrcodeno = {$log['no']}";
                    $db_order = pdo_fetch($sql);
                    if($db_order){
                        app_error(-2, "此二维码已被扫描");
                    } else {
                        $saleropenid = empty($order['saleropenid']) ? $_W['openid'] : $order['saleropenid'];
                        $update = array(
                            'openid'        => $order['openid'],
                            'saleropenid'   => $saleropenid,
                            'updatetime'    => time()
                        );
                        pdo_update("ewei_shop_goods_qrcode_log", $update, array("id" => $log["id"]));
                    }
                } else if(!empty($log['openid'])){
                    app_error(-2, "此二维码已被扫描");
                }
            } else {
                app_error(-2, "此二维码不存在");
            }           
           
            $update = array(
                "qrcodeid"      => $qrcodeid,
                "qrcodeno"      => $qrcodeno
            );
            pdo_update("ewei_shop_order_goods", $update, array("id" => $order_goodsid));
            app_json(array('goods_qrcode' => $order_goods));
        } else {
            app_error(-1, "参数不正确");
        }
    }
}
?>