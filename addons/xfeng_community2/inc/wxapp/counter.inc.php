<?php
//echo MODULE_ROOT;
require_once IA_ROOT . '/addons/xfeng_community/plugin/mijia/function.php';
global $_GPC, $_W;
$ops = array('getCounters', 'usersManage', 'little', 'openCounterCode', 'getManageCounter', 'openCounter');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$regionid = $_SESSION['community']['regionid'];
/**
 * 柜子列表，分按小区查，按管理员查 type=1 超市， type=2 用户 ，type = 3 管理员
 */
if ($op == 'getCounters') {
    $type = intval($_GPC['type']);
    $condition = "t1.uniacid=:uniacid and t1.online=1";
    $params[':uniacid'] = $_W['uniacid'];
    $limit = "";
    if ($type == 1) {
        //超市
        $condition .= " and regionid=:regionid and t1.enable=0 and t2.status=1";
    }
    if ($type == 2) {
        //普通粉丝用户
        $condition .= " and t2.status=2 and t2.id=:id";
        $params[':id'] = $_GPC['id'];
    }
    if ($type == 3) {
        //管理员
        $openid = $_W['fans']['from_users'];
        $item = pdo_getall('xcommunity_counter_notice', array('openid' => $openid));
        $condition .= " and t2.status=2 and t1.id in(:id)";
        $params[':id'] = $item['littleid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = max(20, intval($_GPC['psize']));
        $limit = " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall("select t1.*,t2.title as mtitle,t2.device from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where $condition" . $limit, $params);
    $data = array();
    util::send_result($data);
}
/**
 * 判断是否是管理员
 */
if ($op == 'usersManage') {
    $openid = $_W['fans']['from_users'];
    $item = pdo_getall('xcommunity_counter_notice', array('openid' => $openid));
    if ($item) {
        //管理员
    } else {
        //不是管理员
    }
}
/**
 * 获取超市小柜子
 */
if ($op == 'little') {
    $list = pdo_fetchall("select t1.*,t2.title as mtitle,t2.device from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where t1.enable=0 and t1.uniacid=:uniacid and t2.status=1", array(':uniacid' => $_W['uniacid']));
    $data = array();
    foreach ($list as $k => $v) {
        if ($v['title']) {
            $title = $v['mtitle'] . '~' . $v['title'];
        } else {
            $title = $v['mtitle'] . '~' . ($v['lock'] + 1) . "号柜";
        }
        if ($v['online'] == 1) {
            $data[] = array(
                'key' => $v['id'],
                'value' => $title
            );
        }
    }
    util::send_result($data);
}
/**
 * 投递员和用户使用开柜码开柜
 */
if ($op == 'openCounterCode') {
    $code = intval($_GPC['code']);
    $deviceid = intval($_GPC['deviceid']);
    $type = '';
    $openid = $_W['openid'];
    $notice = pdo_fetch("select id from" . tablename('xcommunity_shop_wechat') . "where enable=1 and type=1 and openid=:openid", array(':openid' => $openid));
    if ($notice) {
        $type = 1;
    } else {
        $type = 2;
    }
    $item = pdo_fetch("select t1.status,t3.lock,t4.device,t1.orderid,t1.code,t2.littleid,t2.price,t5.buildtitle,t6.title as rtitle,t4.title from" . tablename('xcommunity_counter_code') . "t1 left join" . tablename('xcommunity_order') . "t2 on t1.orderid=t2.id left join" . tablename('xcommunity_counter_little') . "t3 on t2.littleid=t3.id left join" . tablename('xcommunity_counter_main') . "t4 on t3.deviceid=t4.id left join" . tablename('xcommunity_build') . "t5 on t4.buildid=t5.id  left join" . tablename('xcommunity_region') . "t6 on t6.id=t4.regionid where t1.code=:code and t1.deviceid=:deviceid", array(':code' => $code, ':deviceid' => $deviceid));
    if (!$item) {
        util::send_error(-2, '开柜码错误');
    }
    $arr = util::xqset($item['regionid']);
    $address = $item['rtitle'] . '-' . $item['buildtitle'] . $arr[b1];
    $status = 0;
    $time = TIMESTAMP;
    $did = $item['device'] . '-' . ($item['lock'] + 1);
    if ($type == 1) {
        if ($item['status'] == 1) {
            util::send_error(-3, '管理员已经使用过该码');
        } elseif ($item['status'] == 2) {
            util::send_error(-4, '用户已经使用过该码');
        } else {
            $status = 1;
            $sql = "select t1.*,t4.realname as address_realname,t4.mobile as address_telephone,t2.realname,t2.mobile,t2.address,t5.title,t1.regionid,t4.uid,t5.title,t6.openid,t1.addressid,t2.city from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t1.regionid=t5.id left join" . tablename('mc_mapping_fans') . "t6 on t6.uid = t1.uid where t1.id=:orderid";
            $params[':orderid'] = $item['orderid'];
            $order = pdo_fetch($sql, $params);
            //获取商品信息
            $goods = pdo_fetchall("SELECT g.*, o.total,o.price as orderprice FROM " . tablename('xcommunity_order_goods') . " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid=:orderid", array(':orderid' => $item['orderid']));
            $order['goods'] = $goods;
            $title = '';
            $count = count($goods);
            if ($count == 1) {
                foreach ($goods as $key => $value) {
                    $title = $value['title'];
                }
            } else {
                foreach ($goods as $key => $value) {
                    $title .= $value['title'] . ',';
                }
            }
            if (set('t15')) {
                $content = array(
                    'first' => array(
                        'value' => '您购买的物品已经送达！',
                    ),
                    'keyword1' => array(
                        'value' => $item['price'] . '元',
                    ),
                    'keyword2' => array(
                        'value' => $title,
                    ),
                    'keyword3' => array(
                        'value' => $order['realname'] . ',' . $order['mobile'] . ',' . $address,
                    ),
                    'keyword4' => array(
                        'value' => $order['ordersn'],
                    ),
                    'keyword5' => array(
                        'value' => $item['title'] . '-开柜码：' . $item['code'],
                    ),
                    'remark' => array(
                        'value' => '有任何问题请随时与我们联系，谢谢。',
                    ),
                );
                $tplid = set('t16');
                util::sendTplNotice($order['openid'], $tplid, $content, $url = '');
                $d = array(
                    'uniacid' => $_W['uniacid'],
                    'sendid' => $item['id'],
                    'uid' => $item['uid'],
                    'type' => 6,
                    'cid' => 1,
                    'status' => 1,
                    'createtime' => TIMESTAMP,
                    'regionid' => $item['regionid']
                );
                pdo_insert('xcommunity_send_log', $d);
            }
        }
    } elseif ($type == 2) {
        if ($item['status'] == 0) {
            util::send_error(-5, '该柜子暂未使用');
        } elseif ($item['status'] == 2) {
            util::send_error(-4, '您已经使用过该码');
        } else {
            $status = 2;
            pdo_update('xcommunity_counter_little', array('enable' => 0), array('id' => $item['littleid']));
        }
    }
    $info = getDeviceInfo($did);//online
    $openinfo = OpenDevice($did);//suc
    if ($info == 'online' && $openinfo == 'suc') {

    } else {
        util::send_error(-6, '设备离线或开柜失败');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'orderid' => $item['orderid'],
        'deviceid' => $deviceid,
        'littleid' => $item['littleid'],
        'uid' => $_W['member']['uid'],
        'openid' => $_W['openid'],
        'type' => $type,
        'createtime' => TIMESTAMP,
        'status' => 1
    );
    pdo_insert('xcommunity_counter_log', $data);
    pdo_update('xcommunity_counter_code', array('status' => $status));
    util::send_result();
}
/**
 * 管理员开柜列表
 */
if ($op == 'getManageCounter') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't4.openid=:openid';
    $params[':openid'] = $_W['openid'];
    $list = pdo_fetchall("select t1.id,t1.lock,t2.title,t1.title as little from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id left join" . tablename('xcommunity_counter_littleid') . "t3 on t1.id=t3.littleid left join" . tablename('xcommunity_counter_notice') . "t4 on t4.id=t3.nid where $condition", $params);
    if (empty($list)) {
        $list = array();
    }
    util::send_result($list);
}
/**
 * 管理员开柜
 */
if ($op == 'openCounter') {
    //管理员类型type：1超级管理员2普通管理员3用户
    $type = intval($_GPC['type']);
    $littleid = intval($_GPC['littleid']);
    $item = pdo_fetch("select t1.lock,t2.device,t2.id as deviceid,t1.enable,t2.stat,t2.rule,t2.price from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where t1.id=:littleid", array(':littleid' => $littleid));
    $data = array(
        'uniacid' => $_W['uniacid'],
        'deviceid' => $item['deviceid'],
        'littleid' => $littleid,
        'uid' => $_W['member']['uid'],
        'openid' => $_W['openid'],
        'createtime' => TIMESTAMP,
        'status' => 1
    );
    if ($type == 3) {
        $log = pdo_fetch("select id,status from" . tablename('xcommunity_counter_log') . " where deviceid=:deviceid and littleid=:littleid and uid=:uid order by createtime desc", array(':deviceid' => $item['deviceid'], ':littleid' => $littleid, 'uid' => $_W['member']['uid']));
        if ($item['enable'] != 0 && $log['status'] != 2){
            util::send_error(-7, '该设备别人正在使用');
        }
        if ($item['stat'] == 1) {
            $dat = array(
                'uniacid'    => $_W['uniacid'],
                'uid'        => $_W['member']['uid'],
                'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
                'price'      => $item['price'],
                'status'     => 0,
                'createtime' => TIMESTAMP,
                'regionid'   => $regionid,
                'type'       => 'counter',
                'littleid'  => $littleid,
            );
            if ($item['rule'] == 1 || ($item['rule'] == 2 && $log['status'] != 2)) {
                pdo_insert('xcommunity_order', $dat);
                $orderid = pdo_insertid();
                $url = $this->createMobileUrl('paycenter', array('type' => 10, 'orderid' => $orderid));
                util::send_result(array('url' => $url));
            }else{
                $data['type'] = 2;
            }
        } else {
            $data['type'] = 2;
        }
    }
    $did = $item['device'] . '-' . ($item['lock'] + 1);
    $info = getDeviceInfo($did);//online
    $openinfo = OpenDevice($did);//suc
    if ($info == 'online' && $openinfo == 'suc') {
        if ($type == 1) {
            $data['type'] = 3;
        } elseif ($type == 2) {
            $data['type'] = 4;
            if ($item['enable'] == 0) {
                pdo_update('xcommunity_counter_little', array('enable' => 3), array('id' => $littleid));
            } elseif ($item['enable'] == 3) {
                pdo_update('xcommunity_counter_little', array('enable' => 0), array('id' => $littleid));
            }
        }
        pdo_insert('xcommunity_counter_log', $data);
        util::send_result();
    } else {
        util::send_error(-6, '设备离线或开柜失败');
    }
}