<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Asset_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    
    public function credit_log(){
        global $_W;
        global $_GPC;
        
    }
    
    public function benefit_log(){
        global $_W;
        global $_GPC;
        $pindex = max(1, $_GPC['page']);
        $psize = 10;
        $offset = ($pindex - 1) * $psize;
        $select = "SELECT o.id, o.price, o.openid, o.ordersn, o.createtime, o.saleropenid, o.storeid, o.verifystoreid, o.addressid, o.paytype, 
                  ol.agentbenefit, ol.saleropenid, ol.salerbenefit "; 
        $sql = " FROM " . tablename("ewei_shop_order") . " o " . 
               " LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
               " WHERE ol.agentid = {$_W['agentid']} AND o.uniacid = {$_W['uniacid']} ORDER BY o.createtime DESC ";
        $limit = " LIMIT $offset, $psize";
        $list = pdo_fetchall($select . $sql . $limit);
        foreach($list as &$log){
            $customer = m('member')->getMemberBase($log['openid']);
            $log['customer_nickname'] = $customer['nickname'];
            $log['customer_avatar'] = $customer['avatar'];
            $storeid = empty($log['storeid']) ? $log['verifystoreid'] : $log['storeid'];
            if($storeid){
                $store = pdo_get("ewei_shop_store", array('id' => $storeid), array('storename'));
                $log['storename'] = $store['storename'];
            } else {
                $log['storename'] = '';
            }
            if($log['paytype'] == 4){
                $log['order_type'] = '门店收银订单';
            } else {
                if($log['addressid']){
                    $log['order_type'] = '线上发货订单';
                } else {
                    $log['order_type'] = '线上自提订单';
                }
            }
            //利润
            $profit_sql = "SELECT GROUP_CONCAT(DISTINCT g.title) AS title, SUM(g.onlinehomeget) AS profit FROM " . tablename("ewei_shop_order_goods") . " og " . 
                   "LEFT JOIN " . tablename("ewei_shop_order") . " o ON og.orderid = o.id " . 
                   "LEFT JOIN " . tablename("ewei_shop_goods") . " g ON g.id = og.goodsid " .
                   "WHERE o.id = {$log['id']} GROUP BY og.goodsid ";
            $goods_info = pdo_fetch($profit_sql);
            $log['title'] = $goods_info['title'];
            $log['profit'] = $goods_info['profit']; 
            
            if(!empty($log['saleropenid'])){
                $saler = m('store')->getSalerInfo(array('openid' => $log['saleropenid']));
                $saler_member = m("member")->getMemberBase($log['saleropenid']);
                $log['saler_realname'] = $saler_member['realname'];
                $log['saler_nickname'] = $saler['salername'];
                $log['saler_avatar'] = $saler_member['avatar'];
            } else {
                $log['saler_realname'] = "";
                $log['saler_nickname'] = "";
                $log['saler_avatar'] = "";
            }
            $log['createtime'] = date("Y-m-d H:i", $log['createtime']);
        }
        
        $total_select = "SELECT COUNT(o.id) ";
        $total = pdo_fetchcolumn($total_select . $sql);
        app_json(array('list' => $list, 'total' => $total));
    }
}