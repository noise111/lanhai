<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage {
    
    /**
     * 获取可以查看的排行榜单列表
     */
    public function get_rank_list(){
        global $_W;
        global $_GPC;
        
        $openid = $_W['openid'];
        $rank_list = pdo_getall("ewei_shop_rank", array("is_show" => 1, "status" => 1), array("*"), '', array("displayorder DESC"));
        $saler_info = m("store")->get_full_saler_info($openid);
        $return_list = array();
        foreach($rank_list as $item){
            //1:全部；2：省级运营中心；3：零售商
            switch($item['range']){
                case 1:
                    $return_list[] = $item;
                    break;
                case 2:
                    if($item['range_id']){
                        $agent_info = m("member")->getAgentInfo($openid);
                        if($agent_info && $agent_info['is_agent'] == 1 && isset($agent_info['list'][$item['range_id']])){
                            $return_list[] = $item;
                        }
                    } else {
                        $return_list[] = $item;
                    }
                    break;
                case 3:
                    if($item['range_id']){
                        $merch_info = m("member")->getMerchInfo($openid);
                        if($merch_info && $merch_info['is_merch'] == 1 && isset($merch_info['list'][$item['range_id']])){
                            $return_list[] = $item;
                        }
                    } else {
                        $return_list[] = $item;
                    }
                    break;
            }
        }
        
        app_json(array("list" => $return_list));
    }
    
    public function get_rank_data(){
        global $_W;
        global $_GPC;
        
        $openid = $_W['openid'];
        $rank_id = intval($_GPC['rank_id']);
        $pid = intval($_GPC['pid']);
        $agent_info = m("member")->getAgentInfo($openid);
        $rank_model = com("rank");
        $list = array();
        $rank = pdo_get("ewei_shop_rank", array("id" => $rank_id));
        $return = array();
        if($agent_info && $agent_info['is_agent'] == 1 && isset($agent_info['list'][$pid])){
            $list = $rank_model->get_data($rank_id, $pid, 4);
        } else {
            $merch_info = m("member")->getMerchInfo($openid);
            if($merch_info && $merch_info['is_merch'] == 1 && isset($merch_info['list'][$pid])){
                $list = $rank_model->get_data($rank_id, $pid, 3);
            } else {
                $saler = m("store")->getSalerInfo();
                if($saler && $saler['storemanager'] == 1){
                    $list = $rank_model->get_data($rank_id, $saler['storeid'], 2);
                }
            }
        }
        $return['list'] = $list;
        
        if($rank['goodsid']){
            $goods_info = pdo_get("ewei_shop_goods", array("id" => $rank['goodsid']), array("id", "title", "thumb"));
            $return['goods_info'] = $goods_info;
        }
        app_json($return);
    }
}