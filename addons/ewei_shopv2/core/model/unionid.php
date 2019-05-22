<?php

/**
 * 替换原表openid为对应帐号的unionid
 */
class Unionid_EweiShopV2Model {
    
    private $tables = array(
        "ewei_shop_abonus_billp"            => "openid",
        "ewei_shop_address_applyfor"        => "openid",
        "ewei_shop_article_comment"         => "openid",
        "ewei_shop_article_log"             => "openid",
        "ewei_shop_article_report"          => "openid",
        "ewei_shop_author_billp"            => "openid",
        "ewei_shop_bargain_actor"           => "openid",
        "ewei_shop_bargain_record"          => "openid",
        "ewei_shop_cashier_order"           => "openid",
        "ewei_shop_cashier_pay_log"         => "openid",
        "ewei_shop_cashier_user"            => "openid,manageopenid",
        "ewei_shop_cashier_operator"        => "manageopenid",
        "ewei_shop_commission_clickcount"   => "openid",        
        "ewei_shop_commission_repurchase"   => "openid",
        "ewei_shop_coupon_benefit_log"      => "openid,customeropenid",
        "ewei_shop_coupon_data"             => "sender,openid",
        "ewei_shop_coupon_guess"            => "openid",
        "ewei_shop_coupon_log"              => "openid",
        "ewei_shop_coupon_relation"         => "saleropenid,customeropenid",
        "ewei_shop_coupon_sendshow"         => "openid",
        "ewei_shop_coupon_taskdata"         => "openid",
        "ewei_shop_creditshop_comment"      => "openid",
        "ewei_shop_creditshop_log"          => "openid",
        "ewei_shop_creditshop_verify"       => "openid",
        "ewei_shop_customer_guestbook"      => "openid",
        "ewei_shop_diyform_data"            => "openid",
        "ewei_shop_diyform_temp"            => "openid",
        "ewei_shop_exchange_cart"           => "openid",
        "ewei_shop_exchange_code"           => "openid",
        "ewei_shop_exchange_query"          => "openid",
        "ewei_shop_exchange_record"         => "openid",
        "ewei_shop_feedback"                => "openid",
        "ewei_shop_fullback_log"            => "openid",
        "ewei_shop_globonus_billp"          => "openid",
        "ewei_shop_goods_comment"           => "openid",
        "ewei_shop_goods_qrcode_log"        => "openid,saleropenid",
        "ewei_shop_groups_order"            => "openid",
        "ewei_shop_groups_order_refund"     => "openid",
        "ewei_shop_groups_paylog"           => "openid",
        "ewei_shop_groups_verify"           => "openid",
        "ewei_shop_invitation_log"          => "openid,invitation_openid",
        "ewei_shop_invitation_qr"           => "openid",
        "ewei_shop_live_view"               => "openid",
        "ewei_shop_lottery_join"            => "join_user",
        "ewei_shop_lottery_log"             => "join_user",
        "ewei_shop_member_address"          => "openid",
        "ewei_shop_member_card_buysend"     => "openid",
        "ewei_shop_member_card_history"     => "openid",
        "ewei_shop_member_card_monthsend"   => "openid",
        "ewei_shop_member_card_order"       => "openid",
        "ewei_shop_member_card_uselog"      => "openid",
        "ewei_shop_member_cart"             => "openid",
        "ewei_shop_member_credit_record"    => "openid",
        "ewei_shop_member_favorite"         => "openid",
        "ewei_shop_member_group_log"        => "openid",
        "ewei_shop_member_history"          => "openid",
        "ewei_shop_member_log"              => "openid",
        "ewei_shop_member_salercart"        => "openid,saleropenid",
        "ewei_shop_merch_account"           => "openid",
        "ewei_shop_merch_reg"               => "openid",
        "ewei_shop_merch_saler"             => "openid",
        "ewei_shop_merch_user"              => "openid",
        "ewei_shop_order"                   => "openid,saleropenid",
        "ewei_shop_order_benefit_log"       => "openid,saleropenid,agentopenid",
        "ewei_shop_order_buysend"           => "openid",
        "ewei_shop_order_comment"           => "openid",
        "ewei_shop_order_peerpay_payinfo"   => "openid",
        "ewei_shop_perm_user"               => "openid",
        "ewei_shop_postera_log"             => "openid,from_openid",
        "ewei_shop_postera_qr"              => "openid",
        "ewei_shop_poster_log"              => "openid,from_openid",
        "ewei_shop_poster_qr"               => "openid",
        "ewei_shop_poster_scan"             => "openid,from_openid",
        "ewei_shop_quick_cart"              => "openid",
        "ewei_shop_refund_address"          => "openid",
        "ewei_shop_saler"                   => "openid",
        "ewei_shop_sale_coupon_data"        => "openid",
        "ewei_shop_sendticket_draw"         => "openid",
        "ewei_shop_sign_records"            => "openid",
        "ewei_shop_sign_user"               => "openid",
        "ewei_shop_sns_board_follow"        => "openid",
        "ewei_shop_sns_like"                => "openid",
        "ewei_shop_sns_manage"              => "openid",
        "ewei_shop_sns_member"              => "openid",
        "ewei_shop_sns_post"                => "openid",
        "ewei_shop_task_extension_join"     => "openid",
        "ewei_shop_task_join"               => "join_user",
        "ewei_shop_task_joiner"             => "task_user",
        "ewei_shop_task_log"                => "openid,from_openid",
        "ewei_shop_task_poster_qr"          => "openid",
        "ewei_shop_task_qr"                 => "openid",
        "ewei_shop_verifygoods"             => "openid",
        "ewei_shop_virtual_data"            => "openid",
        "ewei_shop_virtual_send_log"        => "openid"
    );
    
    public function checkUnionBind($openid, $unionid){
        if($openid && $unionid){
            $sql = "SELECT COUNT(*) FROM " . tablename("ewei_shop_unionid_log") . " WHERE unionid = :unionid AND openid = :openid";
            $log = pdo_fetchcolumn($sql, array(":unionid" => $unionid, ":openid" => $openid));
            if($log && $log > 0){
                return;
            } else {
                $this->updateMemberInfo($openid, $unionid);
            }
            $log_data = array(
                'openid'        => $openid,
                'unionid'       => $unionid,
                'table'         => '---',
                'field'         => '---',
                'create_time'   => time()
            );
            pdo_insert('ewei_shop_unionid_log', $log_data);
        }
    }
  
    /**
     * 检查member数据
     */
    protected function updateMemberInfo($openid, $unionid){
        global $_W;
        $open_member = m('member')->getMember($openid);
        $union_member = m('member')->getMember($unionid);
        if($open_member && $union_member){
            if($union_member['id'] != $open_member['id']){
                //做绑定操作
                $result = m('bind')->merge($open_member, $union_member);
                //合并失败
                if (empty($result['errno'])) {

                }                                
            }
        }
        //更新粉丝表（mc_mapping_fans）的unionid
        $uid = $union_member['uid'] ? $union_member['uid'] : 0;
        if(!$uid){
            $uid = $open_member['uid'] ? $open_member['uid'] : 0;
        }
        if($uid){
            pdo_update('mc_mapping_fans', array('unionid' => $unionid), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
        }
        if(($open_member || $union_member) && $openid != $unionid){
            //更新数据表openid，用unionid替换openid
            $this->updateOpenid($openid, $unionid);
        }     
    }
    
    /**
     * 将openid 替换为 unionid
     * @param string $openid
     * @param string $unionid
     */
    private function updateOpenid($openid, $unionid){
        $now = time();
        foreach($this->tables as $table => $field){
            $field_arr = explode(',', $field);
            foreach ($field_arr as $item){
                $res = pdo_update($table, array($item => $unionid), array($item => $openid)); 
                if($res){
                    $log = array(
                        'openid'        => $openid,
                        'unionid'       => $unionid,
                        'table'         => $table,
                        'field'         => $item,
                        'create_time'   => $now
                    );
                    pdo_insert('ewei_shop_unionid_log', $log);
                }
            }
        }
    }
}