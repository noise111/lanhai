<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Order_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    
    public function get_list(){
        global $_W;
        global $_GPC;
        $type = isset($_GPC['type']) ? $_GPC['type'] : 1;
        $pindex = max(1, intval($_GPC['page']));
		$psize = 10;
        $offset = ($pindex - 1) * $psize;
        $stores = $this->getMerchStore();
        $select = "SELECT o.id, o.ordersn, o.price, o.paytype, o.storeid, o.verifystoreid, o.openid, o.saleropenid, 
                   o.agentid, o.addressid, o.createtime, o.city_express_state, o.expresscom, o.express, 
                   ol.storeid as b_storeid, ol.storebenefit, ol.saleropenid as b_saleropenid, ol.salerbenefit ";
        $from = " FROM " .tablename("ewei_shop_order") . " o " .
                " LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id WHERE o.uniacid = {$_W['uniacid']} AND o.status = 3 "; 
                      
        $condition = "";
        switch ($type){
            //零售商订单
            case 1:
                $condition = " AND (o.storeid IN($stores) OR o.verifystoreid IN($stores) OR ol.storeid IN($stores)) ";
                break;
            //线上订单（线上支付线上发货，addressid为0）
            case 2:
                $condition = " AND o.addressid > 0 AND o.agentid = {$_W['agentid']}";
                break;
            case 3:
                $condition = "";
                break;
        }
        $order_by = " ORDER BY o.createtime DESC LIMIT $offset, $psize ";
        $sql = $select . $from . $condition . $order_by;
        $list = pdo_fetchall($sql);
        foreach($list as &$order){
            if(!empty($order['saleropenid'])){
                $saler = m('member')->getMemberBase($order['saleropenid']);
                $order['saler_nickname'] = $saler['nickname'];
                $order['saler_avatar'] = $saler['avatar'];
            } else {
                $order['saler_nickname'] = "";
                $order['saler_avatar'] = "";
            }
            $customer = m('member')->getMemberBase($order['openid']);
            $order['customer_nickname'] = $customer['nickname'];
            $order['customer_avatar'] = $customer['avatar'];
            $storeid = empty($order['storeid']) ? $order['verifystoreid'] : $order['storeid'];
            $storeid = empty($storeid) ? $order['b_storeid'] : $storeid;
            $store = m('store')->getStoreInfo($storeid);
            $order['storename'] = $store['storename'];
            $order["dispatchname"] = (empty($order["addressid"]) ? "自提" : $order["dispatchname"]);
            if(empty($order["dispatchname"])) {
                $order["dispatchname"] = "快递";
            }
            if($order["city_express_state"] == 1) {
                $order["dispatchname"] = "同城配送";
            }
            if($order['expresscom']){
                $order['dispatchname'] = $order['expresscom'];
            }
            if($order["isverify"] == 1) {
                $order["dispatchname"] = "线下核销";
            }
            
            if($order['paytype'] == '4'){
                $order['paystr'] = "门店支付";
            } else if($order['paytype'] == '11' || $order['paytype'] == '1'){
                $order['paystr'] = "线上余额支付";
            } else if($order['paytype'] == '21'){
                $order['paystr'] = "线上微信支付";
            } else if($order['paytype'] == '3'){
                $order['paystr'] = "货到付款";
            } else {
                $order['paystr'] = "";
            }
            
            if($order['paytype'] == '4'){
                $order['ordertype'] = "收银台支付";
            } else {
                $order['ordertype'] = "平台支付";
            }
            $order['createtime'] = date("Y-m-d H:i", $order['createtime']);
            $sql = "SELECT GROUP_CONCAT(DISTINCT g.title) AS title, SUM(g.onlinehomeget) AS profit FROM " . tablename("ewei_shop_order_goods") . " og " . 
                    "LEFT JOIN " . tablename("ewei_shop_order") . " o ON og.orderid = o.id " . 
                    "LEFT JOIN " . tablename("ewei_shop_goods") . " g ON g.id = og.goodsid " .
                    "WHERE o.id = {$order['id']} GROUP BY og.goodsid ";
            $goods_info = pdo_fetch($sql);
            $order['title'] = $goods_info['title'];
            $order['profit'] = $goods_info['profit']; 
            
        }
        
        $total_select = "SELECT COUNT(o.id) ";
        $total = pdo_fetchcolumn($total_select . $from . $condition . $order_by);
        app_json(array('list' => $list, 'total' => $total));
    }
    
}