<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Ajax_EweiShopV2Page extends AppMobilePage
{

    /**
     * 首页 获取订单列表
     */
	public function get_orderList()
	{
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];
		$sql = " select o.id,o.openid,o.paytime,o.status,og.goodsid,og.optionid,og.optionname from ims_ewei_shop_order o LEFT JOIN ims_ewei_shop_order_goods og on o.id=og.orderid where status = 3 and o.uniacid = {$uniacid} order by o.paytime desc limit 10";
        $prams=array();
		$list = pdo_fetchall($sql,$prams);
		foreach ($list as $k => $v){
			if($list[$k]['optionid'] > 0 || $list[$k]['optionname'] != ''){
                $list[$k]['goodsname'] = $list[$k]['optionname'];
			}else{
                $list[$k]['goodsname'] = pdo_getcolumn("ewei_shop_goods",array("id"=>$list[$k]['goodsid']),'title',1);
			}
            $list[$k]['paytime'] = date("Y-m-d H:i:s",$list[$k]['paytime']);
			$tmpmember = m("member")->getMember($list[$k]['openid']);
            $list[$k]['nickname'] = $tmpmember['nickname'];
            $list[$k]['mobile'] = $tmpmember['mobile'];
            unset($tmpmember);
		}
        app_json(array( "list" => $list ));
	}

	/**
	 * 首页 获取轮播图片
	 */
	public function get_adv(){
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];
		$sql = " select * from ims_ewei_shop_xcx_adv where enabled = 1 and uniacid = {$uniacid} order by displayorder desc ";
		$sql = " select * from ims_ewei_shop_adv where enabled = 1 and uniacid = {$uniacid} order by displayorder desc ";
        $parms=array();
		$list = pdo_fetchall($sql,$parms);
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
            unset($list[$k]['shopid']);
            unset($list[$k]['iswxapp']);
		}
        app_json(array( "list" => $list ));
	}


    /**
     * 首页 广告
     */
    public function get_banner(){
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];
        $sql = " select * from ims_ewei_shop_banner where enabled = 1 and uniacid = {$uniacid} order by displayorder desc ";
        $parms=array();
        $list = pdo_fetchall($sql,$parms);
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
            unset($list[$k]['shopid']);
            unset($list[$k]['iswxapp']);
        }
        app_json(array( "list" => $list ));
    }


    /**
     * 获取所有分类
     */
    public function get_category(){
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];

        $sql = " select * from ims_ewei_shop_category where enabled = 1 and uniacid = {$uniacid} order by displayorder desc ";
        $parms=array();
        $list = pdo_fetchall($sql,$parms);
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
        }
        app_json(array( "list" => $list ));
    }




    /**
     * @param $merchid //商品站点 0：总店；1：家电；2：...
     * @param $cate //商品分类 0：不限 或 对应id分类
     * @param $isnew // 是否新品 1：新品
     * @param $isrecommand// 是否推荐 1：推荐
     * @param $ishot// 是否热 1：热销
     * @param $issendfree// 是否包邮 1：包邮
     */
    public function get_goodslist(){
        global $_GPC;
        global $_W;
//        $catestr = $_GPC['cates'];//
//        $catearr = explode(",",$catestr);
        $condition = "";
        $params=array();

        $condition .= " and uniacid = :uniacid ";
        $params[":uniacid"] = $_W['uniacid'];

        //子站点商品
        $merchid = $_GPC['merchid'];
        if($merchid != 0){
            $condition .= " and merchid = :merchid ";
            $params[":merchid"]=$merchid;
        }

        //商品分类
        $cate = $_GPC['cates'];
        if(!empty($cate)) {
            $catearr = explode(",", $cate);
            $catearr = array_unique($catearr);
            $condition .= ' AND ( ';
            foreach ($catearr as $key => $value) {
                if ($key == 0) {
                    $condition .= 'FIND_IN_SET(' . $value . ',cates)';
                } else {
                    $condition .= ' || FIND_IN_SET(' . $value . ',cates)';
                }
            }
        $condition .= ' ) ';
        }

        //搜索关键词
        $keywords = ((!(empty($_GPC['keywords'])) ? $_GPC['keywords'] : ''));
        if (!(empty($keywords)))
        {
            $condition .= ' AND (`title` LIKE :keywords OR `keywords` LIKE :keywords)';
            $params[':keywords'] = '%' . trim($keywords) . '%';
        }
        //搜索价格筛选
        $priceMin = ((!(empty($_GPC['priceMin'])) ? trim($_GPC['priceMin']) : ''));
        $priceMax = ((!(empty($_GPC['priceMax'])) ? trim($_GPC['priceMax']) : ''));
        if (!(empty($priceMin)))
        {
            $condition .= ' and minprice > \'' . $priceMin . '\'';
        }
        if (!(empty($priceMax)))
        {
            $condition .= ' and maxprice < \'' . $priceMax . '\'';
        }

        //是否新品
        $isnew = ((!(empty($_GPC['isnew'])) ? 1 : 0));
        if (!(empty($isnew)))
        {
            $condition .= ' and isnew=1';
        }
        //是否热卖
        $ishot = ((!(empty($_GPC['ishot'])) ? 1 : 0));
        if (!(empty($ishot)))
        {
            $condition .= ' and ishot=1';
        }
        //是否推荐
        $isrecommand = ((!(empty($_GPC['isrecommand'])) ? 1 : 0));
        if (!(empty($isrecommand)))
        {
            $condition .= ' and isrecommand=1';
        }
        //是否包邮
        $issendfree = ((!(empty($_GPC['issendfree'])) ? 1 : 0));
        if (!(empty($issendfree)))
        {
            $condition .= ' and issendfree=1';
        }

        //上架中
        $condition .= ' and status = 1';
        $condition .= ' and status = 1';

        $page = ((!(empty($_GPC['page'])) ? intval($_GPC['page']) : 1));
        $pagesize = 5;

//        app_json(array( "list" => $condition ));
        $sql = " select * from ims_ewei_shop_goods where 1 " . $condition . " order by displayorder desc " . ' LIMIT ' . (($page - 1) * $pagesize) . ',' . $pagesize;;
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_goods') . ' where 1 ' . $condition . ' ', $params);
        $list = (pdo_fetchall($sql,$params));
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
        }
        app_json(array("total"=>$total,"maxpage"=>ceil($total/$pagesize), "list" => $list));
    }


    /**
     * 获取服务申请表数据
     */
    public function get_diyform_service(){
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];
        $sql = " select * from ims_ewei_shop_diyform_type where uniacid={$uniacid} and title='服务申请表单' ";
        $list = pdo_fetch($sql);
        $list['fields'] = unserialize($list['fields']);
        app_json(array("list" => $list));
    }




}

?>
