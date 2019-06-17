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
    public function get_adv2(){
        global $_GPC;
        global $_W;
        $uniacid = $_W['uniacid'];
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
        //存货大于0
        $condition .= ' and total > 0';

        $page = ((!(empty($_GPC['page'])) ? intval($_GPC['page']) : 1));
        $pagesize = 5;

//        app_json(array( "list" => $condition ));
        $sql = " select * from ims_ewei_shop_goods where 1 " . $condition . " order by displayorder desc " . ' LIMIT ' . (($page - 1) * $pagesize) . ',' . $pagesize;;
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_goods') . ' where 1 ' . $condition . ' ', $params);
        $list = (pdo_fetchall($sql,$params));
        foreach ($list as $k => $v){
            $list[$k]['thumb'] = tomedia($list[$k]['thumb']);
            $list[$k]['keywords2'] = str_replace('，',',',$list[$k]['keywords']);
            $list[$k]['keywords2'] = explode(',',$list[$k]['keywords2']);

//            for($i=0; $i < count($list[$k]['keywords2']); $i++){
            for($i=0; $i < 2; $i++){
                if(($list[$k]['keywords2'][$i] == '')){

                }else{
                    $list[$k]['keywords2title'][] = $list[$k]['keywords2'][$i];
                }

            }

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
//        $diylist = pdo_fetch(" select * from ims_ewei_shop_diyform_type where title='服务申请表单' ");
        $diylist['fields'] = unserialize($list['fields']);
        $housetype = $list['fields']['diyhuxing']['tp_text'];
        app_json(array("list" => $list,"housetype"=>$housetype));
    }



    public function get_diyform_service_list(){

        $diyform_data = $this->diyformData();
//        app_json(array('sss'=>$diyform_data));die;
        $result = array(
//            'member'  => $memberArr,
            'diyform' => array('template_flag' => $diyform_data['template_flag'], 'f_data' => $diyform_data['f_data'], 'fields' => $diyform_data['fields'])
        );
        app_json($result);
    }

    protected function diyformData()
    {
        $template_flag = 0;
        $diyform_plugin = p('diyform');
//        return $diyform_plugin;die;
        if ($diyform_plugin) {
            $set_config = $diyform_plugin->getSet();
            $service_diyform_open = $set_config['service_diyform_open'];

            if ($service_diyform_open == 1) {
                $template_flag = 1;
                $diyform_id = $set_config['service_diyform'];

                if (!empty($diyform_id)) {
                    $formInfo = $diyform_plugin->getDiyformInfo($diyform_id);
                    $fields = $formInfo['fields'];
                    $diyform_data = iunserializer($this->member['diyservicedata']);
                    $f_data = $diyform_plugin->getDiyformData($diyform_data, $fields, $this->member);
                }
            }
        }
//        return $diyform_data;die;
        $appDatas = array();

        if ($diyform_plugin) {
            $appDatas = $diyform_plugin->wxApp($fields, $f_data, $this->member);
        }
//        return $appDatas;die;
        return array('template_flag' => $template_flag, 'f_data' => $appDatas['f_data'], 'fields' => $appDatas['fields'], 'set_config' => $set_config, 'diyform_plugin' => $diyform_plugin, 'formInfo' => $formInfo, 'diyform_id' => $diyform_id, 'diyform_data' => $diyform_data);
    }


    /**
     * diy service 数据表入库
     */
    public function addservice_from(){
        global $_GPC;
        global $_W;
//        $result=array(
//            "list" =>$_W['openid']
//        );
//        app_json($result);
        $list = $_GPC['diypostData'];
        $list = serialize($list);
        pdo_insert("ewei_shop_reservation",array("uniacid"=>$_W['uniacid'],"alldata"=>$list,'openid'=>$_W['openid']));
        $id = pdo_insertid();
        if($id){

        }
        $result=array(
            "list" =>$list
        );
        app_json($result);
    }




}

?>
