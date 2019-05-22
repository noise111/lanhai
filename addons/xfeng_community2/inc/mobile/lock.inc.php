<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/7/17 上午10:02
 * funciton: 扫码开门
 */

global $_W, $_GPC;
/*
 * 临时二维码开门
 */
$id = intval($_GPC['id']);
if (empty($id)) {
    itoast('设备不存在,请联系物业');
    exit();
}
$device = pdo_get('xcommunity_building_device', array('id' => $id), array());
$regionid = $device['regionid'];

if (empty($device)) {
    itoast('设备不存在,请联系物业', $this->createMobileUrl('home'), 'error');
    exit();
}
/**
 * 临时二维码开门
 *
 */
$did = intval($_GPC['did']); //二维码
if ($did) {
    $time = time();
    $open = pdo_get('xcommunity_opendoor_data', array('id' => $did), array('createtime', 'opentime', 'regionid', 'uid'));
    $regionid = $open['regionid'];
    $lasttime = $open['createtime'] + 60 * $open['opentime'];//过期时间

    if ($time > $lasttime) {
        itoast("二维码已经过期,请重新获取", $this->createMobileUrl('home'), 'error');
        exit();
    }
    //开门

    if ($device['category'] == 1) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        $resp = openLn2($device['device_code']);
    }
    if ($device['category'] == 6 || $device['category'] == 5 || $device['category'] == 4 || $device['category'] == 7 || $device['category'] == 8) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        $resp = openLn3($device['device_code']);
    }
    if ($device['category'] == 2) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        $resp = openLn4($device['device_code']);
    }
    if ($device['category'] == 9) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
        $resp = openLn6($device['appid'],$device['appkey'],$device['appsecret'],$device['device_code']);
    }
    if ($device['category'] == 10) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
        $resp = open($device['apikey'], $device['secretkey'], $device['accountid'], $device['deviceid']);
    }
}
else {
    /**
     * 检查是否有开门权限
     */
    $door = pdo_get('xcommunity_bind_door', array('uid' => $_W['member']['uid'], 'regionid' => $regionid), array('id'));

    if ($door) {
        $dev = pdo_get('xcommunity_bind_door_device', array('doorid' => $door['id'], 'deviceid' => $id), array('id'));
        if ($dev) {
            //查用户在当前小区地址信息
            $sql = "select t3.address,t2.addressid from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t3.id=t2.addressid where t1.uid=:uid and t1.regionid=:regionid";
            $user = pdo_fetch($sql, array(':uid' => $_W['member']['uid'], ':regionid' => $regionid));
            if (set('x16', $regionid) || set('p34')) {
                $sql = "select * from" . tablename('xcommunity_cost_list') . "where homenumber=:homenumber and regionid=:regionid limit 1 ";
                $params[':homenumber'] = $user['address'];
                $params[':regionid'] = $regionid;
                $cost = pdo_fetch($sql, $params);
                if ($cost['status'] == '否' || $cost['status'] == 2) {
                    itoast('因欠物业费，暂无法使用智能门禁,请联系物业办理！');
                    exit();
                }
            }
            else {
                //开门
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
                if ($device['category'] == 1) {
                    $resp = openLn2($device['device_code']);
                }
                if ($device['category'] == 6 || $device['category'] == 5 || $device['category'] == 4 || $device['category'] == 7 || $device['category'] == 8) {
                    $resp = openLn3($device['device_code']);
                }
                if ($device['category'] == 2) {
                    $resp = openLn4($device['device_code']);
                }
                if ($device['category'] == 9) {
                    require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
                    $resp = openLn6($device['appid'],$device['appkey'],$device['appsecret'],$device['device_code']);
                }
                if ($device['category'] == 10) {
                    require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                    $resp = open($device['apikey'], $device['secretkey'], $device['accountid'], $device['deviceid']);
                }
            }
        }
        else {
            //判断业主是否有微信开门权限
            itoast('您还没有使用智能门禁的权限,请联系物业办理', $this->createMobileUrl('home'));
            exit();
        }
    }
    else {
        //判断业主是否有微信开门权限
        itoast('您还没有使用智能门禁的权限,请联系物业办理', $this->createMobileUrl('home'));
        exit();
    }
}
$data = array(
    'uniacid'    => $_W['uniacid'],
    'regionid'   => $regionid,
    'uid'        => $_W['member']['uid'],
    'createtime' => TIMESTAMP,
    'type'       => $device['title'] . $device['unit'],
    'addressid'  => $user['addressid'],
    'isopen'     => $resp['code'] == 0 ? 1 : 2
);
pdo_insert('xcommunity_open_log', $data);
//开启领红包并且开门成功
//查当前小区是否有广告
$advs = pdo_getall('xcommunity_plugin_adv', array('regionid' => $regionid, 'status' => 1), array('id', 'advtime', 'advday'));
if (set('p94') && !empty($advs) && empty($resp['code'])) {
    $url = $this->createMobileUrl('red', array('deviceid' => $device['id']));
}
else {
    if ($device['openurl']) {
        $url = $device['openurl'];
    }
    else {
        $url = $this->createMobileUrl('home');
    }
}
itoast($resp['info'], $url, 'success');



