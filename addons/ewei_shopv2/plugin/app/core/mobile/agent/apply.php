<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Apply_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    
    public function add(){
        global $_W;
        global $_GPC;
        $agentid = $_W['agentid'];
        $price = $_GPC['price'];
        
        $benefit = $this->account['benefitbalance'];
        if($benefit < 0 || $benefit < $price){
            app_error(-1, "你的提现额不足");
        }
        
        $data = array(
            "applyno"           => m('common')->createNO('merch_bill', 'applyno', 'MO'),
            "uniacid"           => $_W['uniacid'],
            "pid"               => $agentid,
            "realprice"         => $price,
            "realpricerate"     => $price,
            "orderprice"        => $price,
            "price"             => $price,
            "status"            => 1,
            "applytime"         => time(),
            "member_type"       => 2,
            "bill_type"         => 3,
            "creditstatus"      => -1,
            "orderids"          => '',
            "alipay"            => '',
            "bankname"          => '',
            "bankcard"          => '',
            "applyrealname"     => '',
            "remark"            => '',
            "passorderids"      => ''
            
        ); 
        
        $balance = $benefit - $price;
        pdo_begin();
        $user_res = pdo_update("ewei_shop_member", array("benefitbalance" => $balance), array("id" => $agentid, "uniacid" => $_W['uniacid']));
        $insert_res = pdo_insert("ewei_shop_merch_bill", $data);
        if($user_res && $insert_res){
            pdo_commit();
            app_json();
        } else {
            pdo_rollback();
            app_error(-1, "数据更新出错");
        }
    }
}
