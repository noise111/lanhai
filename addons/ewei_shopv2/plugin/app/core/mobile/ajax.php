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
		global $_W;
		$sql = " select o.id,o.openid,o.paytime,o.status,og.goodsid,og.optionid,og.optionname from ims_ewei_shop_order o LEFT JOIN ims_ewei_shop_order_goods og on o.id=og.orderid where status = 3 order by o.paytime desc limit 10";
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
		$sql = " select * from ims_ewei_shop_xcx_adv where enabled = 1 order by displayorder desc ";
		$sql = " select * from ims_ewei_shop_adv where enabled = 1 order by displayorder desc ";
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
        $sql = " select * from ims_ewei_shop_banner where enabled = 1 order by displayorder desc ";
        $parms=array();
        $list = pdo_fetchall($sql,$parms);
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
            unset($list[$k]['shopid']);
            unset($list[$k]['iswxapp']);
        }
        app_json(array( "list" => $list ));
    }




}

?>
