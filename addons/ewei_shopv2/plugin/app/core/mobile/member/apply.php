<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
require(EWEI_SHOPV2_PLUGIN . "app/core/page_mobile.php");
class Apply_EweiShopV2Page extends AppMobilePage {
    
    public function add(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $price = $_GPC['price'];
        
        $member = m("member")->getMember($openid);
        $benefit = $member['benefitbalance'];
        if($benefit < 0 || $benefit < $price){
            app_error(-1, "你的提现额不足");
        }
        
        pdo_begin();
        $data = array(
            "applyno"           => m('common')->createNO('merch_bill', 'applyno', 'MO'),
            "uniacid"           => $_W['uniacid'],
            "pid"               => $member['id'],
            "realprice"         => $price,
            "realpricerate"     => $price,
            "orderprice"        => $price,
            "price"             => $price,
            "status"            => 1,
            "applytime"         => time(),
            "member_type"       => 3,
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
        $user_res = pdo_update("ewei_shop_member", array("benefitbalance" => $balance), array("id" => $member['id'], "uniacid" => $_W['uniacid']));
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
