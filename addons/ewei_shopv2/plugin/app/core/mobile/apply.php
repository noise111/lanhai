<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Apply_EweiShopV2Page extends AppMobilePage{
    
    public function getBenefitApply(){
        global $_W;
        global $_GPC;
        $member_type = isset($_GPC['member_type']) ? intval($_GPC['member_type']) : 3;
        $bill_type = isset($_GPC['bill_type']) ? intval($_GPC['bill_type']) : 3;
        $openid = isset($_W['openid']) && !empty(trim($_W['openid'])) ? $_W['openid'] : $_GPC['openid'];
        $page = isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $psize = 10;
        $offset = ($page - 1) * $psize;
        
        $pid = 0;
        switch($member_type){
            //经销商
            case 1;
                $merchid = isset($_GPC['merchid']) ? $_GPC['merchid'] : $_W['merchid'];
                $pid = $merchid;
                break;
            //运营中心
            case 2:
                $agent_id = isset($_GPC['agentid']) ? $_GPC['agentid'] : false;
                $pid = $agent_id;
                break;
            //用户
            case 3:
                $member = m("member")->getMemberBase($openid);
                if(!$member){
                    app_error(-1);
                } else {
                    $pid = $member['id'];
                }
                break;
        }
        if(!$pid){
            app_error(-1, "pid参数错误");
        }
        $list = pdo_getall("ewei_shop_merch_bill", array(
            'pid'           => $pid,
            'member_type'   => $member_type,
            'bill_type'     => $bill_type
        ), array(), "*", array("applytime desc"), array($offset, $psize));
        foreach($list as &$log){
            $log['applytime'] = date("Y-m-d H:i", $log['applytime']);
        }
        $total = pdo_fetchcolumn("SELECT COUNT(id) as total FROM " . tablename("ewei_shop_merch_bill") . " WHERE pid = $pid AND member_type = $member_type AND bill_type = $bill_type");
        app_json(array("list" => $list, "total" => $total));
    }
}
