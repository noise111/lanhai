<?php

/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/28 下午8:44
 */
class model_shop
{
    // 购物车表
    public static $TABLE_CART = "xcommunity_cart";
    public static $TABLE_ADDRESS = "xcommunity_member_address";

    //获取购物车商品数量
    static function getCartTotal($regionid, $type)
    {
        global $_W;
        $cartotal = pdo_fetchcolumn("select count(*) from " . tablename(self::$TABLE_CART) . " where uniacid = :uniacid and uid=:uid and regionid=:regionid and type=:type", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':regionid' => $regionid, ':type' => $type));
        return empty($cartotal) ? 0 : $cartotal;
    }

    static function setOrderStock($id = '', $minus = true)
    {
        $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,g.total as goodstotal,o.total,g.sold FROM " . tablename('xcommunity_order_goods') . " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id "
            . " WHERE o.orderid='{$id}'");
        foreach ($goods as $item) {
            if ($minus) {

                $data = array();
                if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) {
                    $data['total'] = $item['goodstotal'] - $item['total'];
                    $data['sold'] = $item['sold'] + $item['total'];
                }

                pdo_update('xcommunity_goods', $data, array('id' => $item['id']));
            }
            else {

                $data = array();
                if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) {
                    $data['total'] = $item['goodstotal'] + $item['total'];
                }

                pdo_update('xcommunity_goods', $data, array('id' => $item['id']));
            }
        }
    }

    //获取超市地址
    static function getAddressOne()
    {
        global $_W;
        return pdo_get(self::$TABLE_ADDRESS, array('uid' => $_W['member']['uid'], 'enable' => 1, 'uniacid' => $_W['uniacid']), array('id', 'address'));

    }

    static function getAllAddress()
    {
        global $_W;
        return pdo_getall(self::$TABLE_ADDRESS, array('uid' => $_W['member']['uid'], 'uniacid' => $_W['uniacid']), array('id', 'address', 'enable'));

    }

    //业主获取订单
    static function getOrderTotal($status, $regionid, $uid, $type)
    {
        global $_W;
        $condition = " uniacid=:uniacid and status=:status and enable=1";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':status'] = $status;
        if ($regionid) {
            $condition .= " and regionid=:regionid";
            $params[':regionid'] = $regionid;
        }
        if ($uid) {
            $condition .= " and uid=:uid";
            $params[':uid'] = $uid;
        }
        if ($type) {
            $condition .= " and type=:type";
            $params[':type'] = $type;
        }
        $sql = "select count(*) from " . tablename('xcommunity_order') . " where $condition";
        $ordertotal = pdo_fetchcolumn($sql, $params);

        return empty($ordertotal) ? 0 : $ordertotal;
    }

    //手机端管理获取订单
    static function getappOrderTotal($status, $uid, $apptype, $shopids)
    {
        global $_W;
        $condition = " t1.uniacid=:uniacid and t1.status=:status and t1.type='shopping' and t1.enable=1";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':status'] = $status;
//        if ($uid && $apptype != 1) {
//            $condition .= " and t6.shopid=:shopid";
//            $params[':uid'] = $uid;
//        }
        $condition .= " and t6.shopid in({$shopids})";
        $sql = "select distinct t1.id from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ";
        $orders = pdo_fetchall($sql, $params);
        $ordertotal = count($orders);
        return empty($ordertotal) ? 0 : $ordertotal;
    }
}