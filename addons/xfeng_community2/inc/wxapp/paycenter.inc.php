<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/7/17 上午10:02
 */

//查询功能支付方式
//type =1 超市 type =2 物业费  type=3 商家 type=4 小区活动 type=5 无人超市 type=7 充电桩 type=8 直通车 type=9 报修维修费 type=10 云柜 11智慧停车 12商家充值积分
//查询支付接口参数信息type=1 和小区有关，物业费、小区活动、报修维修费
//type =4 小区超市 type=3 周边商家 type=7 充电桩 type=8 直通车
global $_GPC, $_W;
//查当前订单信息
$orderid = intval($_GPC['orderid']);
if (empty($orderid)) {
    util::send_error(-1, '非法操作');
}
$type = intval($_GPC['type']);
$set = util::payset($type);
$t = intval($_GPC['t']);
if (empty($set)) {
    util::send_error(-1, '暂无设置支付方式');
}
// 商家名称
$accountname = $_W['account']['name'];
if ($type == 3 || $type == 1 || $type == 8) {
    $order = pdo_fetch("SELECT * FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $orderid));
    $price = $order['price'];
    // 商品编号
    $sql = 'SELECT `goodsid` FROM ' . tablename('xcommunity_order_goods') . " WHERE `orderid` = :orderid";
    $goodsId = pdo_fetchcolumn($sql, array(':orderid' => $orderid));

    // 商品名称
    if ($type == 1 || $type == 8) {
        $sql = "SELECT t1.title,t2.id FROM " . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.shopid = t2.id WHERE t1.id = :id";
        $paytype = $type == 1 ? 4 : 8;
        $goods = pdo_fetch($sql, array(':id' => $goodsId));
        $params['title'] = $goods['title'];
        $userid = $goods['id'];
    }
    elseif ($type == 3) {
        $paytype = 3;
        if ($t) {
            //商家线下扫码支付
            $dp = pdo_get('xcommunity_dp', array('id' => $order['dpid']));
            $params['title'] = $dp['sjname'].$_W['member']['mobile'] . $_W['member']['realname'];
            $userid = $dp['id'];
            $accountname = $dp['sjname'];
        }
        else {
            //商家在线支付
            $sql = "SELECT t1.title,t2.id,t2.sjname FROM " . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.dpid = t2.id WHERE t1.id = :id";
            $goods = pdo_fetch($sql, array(':id' => $goodsId));
            $params['title'] = $goods['sjname'].$goods['title'].$_W['member']['mobile'] . $_W['member']['realname'];
            $userid = $goods['id'];
            $accountname = $goods['sjname'];
        }
    }
}
if ($type == 4 || $type == 2 || $type == 9 || $type == 10) {
    $order = pdo_fetch("SELECT * FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $orderid));
    $price = $order['price'];
    if ($type == 4) {
        $params['title'] = $_SESSION['community']['address'] . '-' . '活动定金' . $_W['member']['mobile'] . $_W['member']['realname'];
    }
    elseif ($type == 2) {
        $params['title'] = $_SESSION['community']['address'] . '-' . '物业相关费用' . $_W['member']['mobile'] . $_W['member']['realname'];
    }
    elseif ($type == 9) {
        $params['title'] = $_SESSION['community']['address'] . '-' . '维修费' . $_W['member']['mobile'] . $_W['member']['realname'];
    }
    elseif ($type == 9) {
        $params['title'] = $_SESSION['community']['address'] . '-' . '云柜' . $_W['member']['mobile'] . $_W['member']['realname'];
    }
    $userid = $_SESSION['community']['regionid'];
    $paytype = 1;
    $accountname = $_SESSION['community']['title'];
}
if ($type == 5) {
    $params['title'] = '无人超市';
    $order = pdo_fetch("SELECT * FROM " . tablename('xcommunity_supermark_order') . " WHERE id = :id", array(':id' => $orderid));
    $price = $order['price'];
    $super = pdo_get('xcommunity_supermark', array('device_code' => $order['code']), array('uid'));
    $userid = $super['uid'];
}
if ($type == 7) {
    $order = pdo_fetch("SELECT * FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $orderid));
    $price = $order['price'];

    if ($t) {
        //充值
        $charging = pdo_get('xcommunity_charging_throw', array('status' => 1), array('id'));
        $params['title'] = '充电桩余额充值';
    }
    else {
        $sql = "select t3.id from" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('xcommunity_charging_station') . "t2 on t1.code=t2.code left join" . tablename('xcommunity_charging_throw') . "t3  on t3.id = t2.tid where t1.orderid=:orderid ";
        $charging = pdo_fetch($sql, array(':orderid' => $orderid));
        $params['title'] = $_SESSION['community']['address'] . '充电桩' . $_W['member']['mobile'] . $_W['member']['realname'];
    }

    $userid = $charging['id'];
    $paytype = 7;
    $accountname = $_SESSION['community']['title'];
}
if ($type == 11) {
    $order = pdo_get('xcommunity_order', array('id' => $orderid));
    $price = $order['price'];
    $parkOrder = pdo_get('xcommunity_parks_order', array('orderid' => $orderid));
    if ($parkOrder['category'] == 1) {
        $params['title'] = '月租车延期-' . $parkOrder['carno'];
    }
    if ($parkOrder['category'] == 2) {
        $params['title'] = '临时车缴费-' . $parkOrder['carno'];
    }
    $userid = $parkOrder['regionid'];
    $paytype = 11;
    $accountname = $_SESSION['community']['title'];
}
if ($order['status'] != '0') {
    util::send_error(-1, '抱歉，您的订单已经付款或是被关闭，请重新进入付款！');
}
$params['tid'] = $order['ordersn'];
$params['user'] = $_W['member']['uid'];
$params['fee'] = $price;
$params['ordersn'] = $order['ordersn'];
$params['virtual'] = $order['goodstype'] == 2 ? true : false;
$params['module'] = $this->module['name'];
$params['userid'] = $userid;
$params['accountname'] = $accountname;
$params['credit2'] = $_W['member']['credit2'];
$params['type'] = $paytype;
$params['pay_type'] = $type;
$log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
if (empty($log)) {
    $log = array(
        'uniacid'    => $_W['uniacid'],
        'acid'       => $_W['acid'],
        'openid'     => $_W['member']['uid'],
        'module'     => $this->module['name'], //模块名称，请保证$this可用
        'tid'        => $params['tid'],
        'fee'        => $params['fee'],
        'card_fee'   => $params['fee'],
        'status'     => '0',
        'is_usecard' => '0',
    );
    pdo_insert('core_paylog', $log);
}
if ($set['wechat']) {
    //借用授权
    $wechat = pdo_get('xcommunity_wechat', array('userid' => $userid, 'type' => $paytype), array());
    if ($wechat) {
        if ($wechat['appid'] && $wechat['appsecret']) {
            $code = trim($_GPC['code']);
            $result = authwechat($wechat['appid'], $wechat['appsecret'], $type, $orderid, $code,$t);
            if (empty($code)) {
                util::send_result(array('status' => 0, 'url' => $result));
                exit();
            }
            else {
                $params['payopenid'] = $result;
            }
        }
    }
}
if ($set['sub']) {
    //借用授权
    $wechat = pdo_get('xcommunity_service_data', array('userid' => $userid, 'type' => $paytype), array());
    if ($wechat) {
        if ($wechat['appid'] && $wechat['appsecret']) {
            $code = trim($_GPC['code']);
            $result = authwechat($wechat['appid'], $wechat['appsecret'], $type, $orderid, $code,$t);

            if (empty($code)) {
                util::send_result(array('status' => 0, 'url' => $result));
                exit();
            }
            else {
                $params['payopenid'] = $result;
            }
        }
    }
}

if ($set) {
    if ($set['type'] == 4 || $set['type'] == 2 || $set['type'] == 9 || $set['type'] == 11) {
        if ($set['wechat']) {
            //微信支付
            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
        }
        if ($set['sub']) {
            //微信支付
            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
        }
        if ($set['swiftpass']) {
            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
        }
        if ($set['balance']) {
            if ($set['type'] == 4) {
                //小区活动
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 2) {
                //物业费
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 9) {
                //报修付款
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 11) {
                // 智能车禁
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }

        }
        if ($set['delivery']) {
            if ($set['type'] == 4) {
                //小区活动
                $set['url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 2) {
                //物业费
                $set['url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 9) {
                //报修付款
                $set['url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 11) {
                // 智能车禁
                $set['url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
        }
        if ($set['hsyunfu']) {
            $sign = trim($_GPC['sign']);
            if (empty($sign)) {
                $url = authhsyunfu($type, $orderid);
                util::send_result(array('status' => 0, 'url' => $url));
                exit();
            }
            else {
                $params['_appid'] = trim($_GPC['appid']);
                $params['_openid'] = trim($_GPC['openid']);
            }

            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }

        }
        if ($set['chinaums']) {
            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
        }
        if ($set['alipay']) {
            if ($set['type'] == 4) {
                //小区活动
                if (set('x45', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 2) {
                //物业费
                if (set('x44', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 9) {
                //报修付款
                if (set('x79', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 11) {
                // 智能车禁
                if (set('x83', $_SESSION['community']['regionid'])) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
        }
    }
    else {
        if ($set['wechat']) {
            if ($set['type'] == 1) {
                //超市
                if (set('p46')) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 10) {
                //云柜
//                if (set('p137')) {
                    //开启独立支付
                    $set['wechat_url'] = $this->createMobileUrl('cash', array('op' => 'wechat', 'type' => 9, 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['wechat_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
//                }
            }
        }
        if ($set['sub']) {
            if ($set['type'] == 1) {
                //超市
                if (set('p46')) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 3) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 10) {
                //云柜
//                if (set('p137')) {
                    //开启独立支付
                    $set['wechat_sub_url'] = $this->createMobileUrl('cash', array('op' => 'sub', 'type' => 9, 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['wechat_sub_url'] = url('mc/cash/wechat', array('params' => base64_encode(json_encode($params))));
//                }
            }
        }
        if ($set['swiftpass']) {
            if ($set['type'] == 1) {
                //超市
                if (set('p46')) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 3) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }

            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['swiftpass_url'] = '';
                }
            }
            if ($set['type'] == 10) {
                //城乡直通车
//                if (set('p137')) {
                    //开启独立支付
                    $set['swiftpass_url'] = $this->createMobileUrl('cash', array('op' => 'swiftpass', 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['swiftpass_url'] = '';
//                }
            }
        }
        if ($set['balance']) {
            if ($set['type'] == 1) {
                //超市
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));

            }
            if ($set['type'] == 8) {
                //城乡直通车
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 10) {
                //云柜
                $set['balance_url'] = url('mc/cash/credit', array('params' => base64_encode(json_encode($params))));
            }
        }
        if ($set['delivery']) {
            if ($set['type'] == 1) {
                //超市
                $set['delivery_url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                $set['delivery_url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                $set['delivery_url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 8) {
                //城乡直通车
                $set['delivery_url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }
            if ($set['type'] == 10) {
                //云柜
                $set['delivery_url'] = url('mc/cash/delivery', array('params' => base64_encode(json_encode($params))));
            }

        }
        if ($set['hsyunfu']) {
            $sign = trim($_GPC['sign']);
            if (empty($sign)) {
                authhsyunfu($type, $orderid);
                util::send_result(array('status' => 0, 'url' => $url));
                exit();
            }
            else {
                $params['_appid'] = trim($_GPC['appid']);
                $params['_openid'] = trim($_GPC['openid']);
            }
            if ($set['type'] == 1) {
                //超市
                if (set('p46')) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['hsyunfu_url'] = '';
                }
            }
            if ($set['type'] == 10) {
                //云柜
//                if (set('p137')) {
                    //开启独立支付
                    $set['hsyunfu_url'] = $this->createMobileUrl('cash', array('op' => 'hsyunfu', 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['hsyunfu_url'] = '';
//                }
            }
        }
        if ($set['chinaums']) {

            if ($set['type'] == 1) {
                //超市

                if (set('p46')) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['chinaums_url'] = '';
                }
            }
            if ($set['type'] == 10) {
                //云柜
//                if (set('p137')) {
                    //开启独立支付
                    $set['chinaums_url'] = $this->createMobileUrl('cash', array('op' => 'chinaums', 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['chinaums_url'] = '';
//                }
            }
        }
        if ($set['alipay']) {
            if ($set['type'] == 1) {
                //超市
                if (set('p46')) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 3 || $set['type'] == 6) {
                //团购/店铺支付码
                if (set('p45')) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 5) {
                //无人超市

            }
            if ($set['type'] == 7) {
                //充电桩
                if (set('x80')) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = '';
                }
            }
            if ($set['type'] == 8) {
                //城乡直通车
                if (set('p137')) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 1, 'params' => base64_encode(json_encode($params))));
                }
                else {
                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
                }
            }
            if ($set['type'] == 10) {
                //云柜
//                if (set('p137')) {
                    //开启独立支付
                    $set['alipay_url'] = $this->createMobileUrl('cash', array('op' => 'alipay', 'type' => 9, 'params' => base64_encode(json_encode($params))));
//                }
//                else {
//                    $set['alipay_url'] = url('mc/cash/alipay', array('params' => base64_encode(json_encode($params))));
//                }
            }
        }
    }

}
$data = array();
$data['set'] = $set;
$data['good'] = $params;
util::send_result($data);
function authhsyunfu($type, $orderid)
{
    global $_W;
    $callback = app_url('paycenter', array('orderid' => $orderid, 'type' => $type));
    $url = "https://wex.hsyunfu.com/oauth/wex/login.do?ret_url=" . urlencode($callback);
//    header('Location: ' . $url);
//    exit();
    return $url;

}

function authwechat($appid, $appsecret, $type, $orderid, $code,$t)
{
    global $_W;

    if (!empty($code)) {
        load()->func('communication');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code ";
        $res = ihttp_get($url);
        $res = @json_decode($res['content'], true);
        $payopenid = $res['openid'];
        return $payopenid;
    }
    else {
        $url = app_url('paycenter', array('orderid' => $orderid, 'type' => $type,'t'=>$t));
        $callback = urlencode($url);
        $url1 = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$callback}&response_type=code&scope=snsapi_base&state=1#wechat_redirect";

//        header('Location: ' . $url1);
//        exit();
        return $url1;
    }
}