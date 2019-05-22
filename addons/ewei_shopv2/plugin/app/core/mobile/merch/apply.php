<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/merchbase.php");
class Apply_EweiShopV2Page extends Merchbase_EweiShopV2Page{
    
    public function add(){
        global $_W;
        global $_GPC;
        $merchid = $_W['merchid'];
        $price = $_GPC['price'];
        $this->check_perm("mini.apply.add");
        $benefit = $this->getMerchBenefit();
        if($benefit < 0 || $benefit < $price){
            app_error(-1, "你的提现额不足");
        }
        
        //将零售商底下的各店铺分润额清零，更新零售商账号的分润字段
        $storeids = $this->getMerchStore();
        pdo_begin();
        if($storeids){
            $sql = "UPDATE " . tablename("ewei_shop_store") . " SET benefitbalance = 0 WHERE id IN ($storeids) AND merchid = $merchid AND uniacid = {$_W['uniacid']}";
            $store_res = pdo_query($sql);
        } else {
            $store_res = true;
        }
        $user_res = pdo_update("ewei_shop_merch_user", array("benefitbalance" => $benefit), array("id" => $merchid, "uniacid" => $_W['uniacid']));
        if($store_res !== false && $user_res !== false){
            pdo_commit();
        } else {
            pdo_rollback();
            app_error(-1, "数据更新出错");
        }
        $data = array(
            "applyno"           => m('common')->createNO('merch_bill', 'applyno', 'MO'),
            "uniacid"           => $_W['uniacid'],
            "pid"               => $merchid,
            "realprice"         => $price,
            "realpricerate"     => $price,
            "orderprice"        => $price,
            "price"             => $price,
            "status"            => 1,
            "applytime"         => time(),
            "member_type"       => 1,
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
        $user_res = pdo_update("ewei_shop_merch_user", array("benefitbalance" => $balance), array("id" => $merchid, "uniacid" => $_W['uniacid']));
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
