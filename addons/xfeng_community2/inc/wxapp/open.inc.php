<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/21 上午9:14
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'lock', 'share', 'getShareData');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if (empty($_W['member']['uid'])) {
    util::send_error(-1, '缺uid');
    exit();
}
/**
 * 获取开门列表
 */
if ($op == 'list') {
    $regionid = $_SESSION['community']['regionid'];
    $uid = $_W['member']['uid'];
    $type = intval($_GPC['type']);
    if (empty($_SESSION['community']['open_status'])) {
        util::send_result(array('status' => 1, 'content' => '您暂未开通智能门禁'));
        exit();
    }
    if (empty($_SESSION['community']['open_status']) && ($_SESSION['community']['status'] == 1 || $_SESSION['community']['status'] == 2) && empty($_SESSION['community']['visit'])) {
        //util::send_result(-1, '您的微信开门功能正在审核中');
        util::send_result(array('status' => 1, 'content' => '您的微信开门功能正在审核中'));
        exit();
    }
    if (empty($_SESSION['community']['open_status']) && ($_SESSION['community']['status'] == 3) && empty($_SESSION['community']['visit'])) {
        util::send_result(array('status' => 1, 'content' => '请您携带身份证和租赁合同来物业中心办理审核'));
//        util::send_error(-1, '请您携带身份证和租赁合同来物业中心办理审核。');
        exit();
    }
    if ($_SESSION['community']['visit'] == 1) {
        util::send_result(array('status' => 2, 'content' => '您还没有注册为小区住户，请您完成注册后使用该功能。'));
//        util::send_error(-2, '您还没有注册为小区住户，请您完成注册后使用该功能。');
        exit();
    }
    if ($_SESSION['community']['opentime']) {
        if (TIMESTAMP > $_SESSION['community']['opentime']) {
//            util::send_error(-1, '您的门禁已到期');
            util::send_result(array('status' => 1, 'content' => '您的门禁已到期'));
            exit();
        }
    }

    if (set('x16', $regionid) || set('p34')) {
        $cost = pdo_get('xcommunity_cost_list', array('homenumber' => $_SESSION['community']['address'], 'regionid' => $regionid));
        if ($cost['status'] == '否' || $cost['status'] == 2) {
            util::send_result(array('status' => 1, 'content' => '请缴纳物业费后，在使用智能开门'));
            exit();
        }
    }
    $lng = $_GPC['lng'];
    $lat = $_GPC['lat'];
    $condition = '';
    if (set('p86')) {
        $range = set('p86') ? set('p87') : 20;
        $point = util::squarePoint($lng, $lat, $range);
        $condition['lat <>'] = 0;
        $condition['lat >'] = $point['right-bottom']['lat'];
        $condition['lat <='] = $point['left-top']['lat'];
        $condition['lng >='] = $point['left-top']['lng'];
        $condition['lng <='] = $point['right-bottom']['lng'];
    }
    $data = array();
    if (set('x15', $regionid) || set('p33')) {
        //授权验证开门
        //查询开门的小区
        $doors = pdo_getall('xcommunity_bind_door', array('uid' => $uid), array('regionid', 'id'));
        $doors_regionids = _array_column($doors, 'regionid');
        $doors_ids = _array_column($doors, 'id');
        //查询小区列表
        $regions = pdo_getall('xcommunity_region', array('id' => $doors_regionids), array('id', 'title'), 'id');

        //查对应的小区用户绑定的设备id
        $doors_devices = pdo_getall('xcommunity_bind_door_device', array('doorid' => $doors_ids), array('deviceid'));
        $doors_deviceids = _array_column($doors_devices, 'deviceid');
        //查询当前用户的开门权限
        $condition['id'] = $doors_deviceids;
        $condition['status'] = 1;
        if ($type == 3) {
            $condition['category'] = 9;
        }
        $devices = pdo_getall('xcommunity_building_device', $condition, array('id', 'title', 'unit', 'regionid', 'thumb', 'photos'), '', array('displayorder ASC'));
    } else {
        //默认开门
        //查询小区列表
        $regions = pdo_getall('xcommunity_region', array('id' => $regionid), array('id', 'title'), 'id');
        $condition['type'] = 2;
        $condition['status'] = 1;
        $condition['regionid'] = $regionid;
        if ($type == 3) {
            $condition['category'] = 9;
        }
        $devices = pdo_getall('xcommunity_building_device', $condition, array('id', 'title', 'unit', 'regionid', 'thumb', 'photos'), '', array('displayorder ASC'));
    }
    /* 重新组装新的数组*/
    $list = array();
    $newRegions = array();
    foreach ($devices as $device) {
        $device['thumb'] = $device['thumb'] ? tomedia($device['thumb']) : MODULE_URL . 'template/mobile/default2/static/images/icon/icon-rental.png';
        // 门禁的幻灯图
        $photo = array();
        if ($device['photos']) {
            $photos = explode(',', $device['photos']);
            foreach ($photos as $key => $val) {
                $photo[] = tomedia($val);
            }
        }
        $device['photos'] = $photo;
        if ($device['regionid'] = $regions[$device['regionid']]['id']) {
            $list[$device['regionid']][] = $device;
            $newRegions[$device['regionid']] = array(
                'id' => $device['regionid'],
                'title' => $regions[$device['regionid']]['title']
            );
        }
    }
    $data['list'] = array(
        'regions' => $newRegions,
        'devices' => $list
    );
    if ($newRegions) {
        //如需增加头部导航验证，需要同时修改H5请求后，获取返回的数据格式改变
        $data['hstatus'] = set('p96') ? 1 : 0;
        $data['regionid'] = $_SESSION['community']['regionid'];
        util::send_result($data);
    } else {
//        util::send_error(-1, '您暂未开通智能门禁');
        util::send_result(array('status' => 1, 'content' => '您暂未开通智能门禁'));
    }
}
/**
 * 生成临时开门二维码
 */
if ($op == 'share') {
//    $type = intval($_GPC['type']);
    $deviceid = intval($_GPC['deviceid']);//设备id
    //二维码开门数据
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => $_SESSION['community']['regionid'],
        'opentime' => $_GPC['opentime'],
//        'type'       => $type,
        'createtime' => TIMESTAMP,
        'uid' => $_W['member']['uid']
    );
    pdo_insert('xcommunity_opendoor_data', $data);
    $did = pdo_insertid();
    $data = array();
    $data['url'] = $this->createMobileUrl('opendoor', array('op' => 'share', 'deviceid' => $deviceid, 'did' => $did));
    util::send_result($data);
}

/**
 * 获取分享开门数据
 */
if ($op == 'getShareData') {
    $deviceid = intval($_GPC['deviceid']);//设备id
    $did = intval($_GPC['did']);
    $device = pdo_fetch("SELECT t1.*,t2.title as rtitle FROM" . tablename('xcommunity_building_device') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE t1.id=:id", array(':id' => $deviceid));
    $door = $device['unit'] ? $device['title'] . $device['unit'] : $device['title'];;
    $_share = array(
        'title' => $_W['member']['realname'] . '邀请你微信开门',
        'desc' => $device['rtitle'] . $door . '临时开门链接',
        'imgUrl' => tomedia($_W['fans']['tag']['avatar']),
        'link' => $_W['siteroot'] . 'app' . substr($this->createMobileUrl('lock', array('id' => $deviceid, 'did' => $did)), 1)
    );
    $data = array();
    $data['url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$deviceid}&did={$did}&do=lock&m=" . MODULE_NAME;//二维码内容
    $data['share'] = $_share;
    util::send_result($data);
}
/**
 * 业主请求开门
 */
if ($op == 'lock') {
    $id = intval($_GPC['id']);
    $device = pdo_get('xcommunity_building_device', array('id' => $id));
    if (empty($device)) {
        util::send_error(-1, '此门暂不支持智能门禁');
        exit();
    }
//    $resp = util::auth($device['device_code']);
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
        if ($device['doorstatus'] == 1) {
            require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
            $resp = openLn6($device['appid'], $device['appkey'], $device['appsecret'], $device['device_code']);
        } else {
            $resp = array(
                'code' => 1,
                'info' => '设备离线',
                'status' => 'no'
            );
        }
    }
    if ($device['category'] == 10) {
//        if ($device['doorstatus'] == 1) {
            require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
            $resp = open($device['apikey'], $device['secretkey'], $device['accountid'], $device['deviceid']);
//        } else {
//            $resp = array(
//                'code' => 1,
//                'info' => '设备离线',
//                'status' => 'no'
//            );
//        }
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => $device['regionid'],
        'uid' => $did ? $open['uid'] : $_W['member']['uid'],
        'createtime' => TIMESTAMP,
        'type' => $device['title'] . $device['unit'],
        'addressid' => $_SESSION['community']['addressid'],
        'isopen' => $resp['code'] == 0 ? 1 : 2
    );
    pdo_insert('xcommunity_open_log', $data);
    $data = array();
    $data['content'] = $resp['info'];
    //开启领红包并且开门成功
    //查当前小区是否有广告
    $advs = pdo_getall('xcommunity_plugin_adv', array('regionid' => $_SESSION['community']['regionid'], 'status' => 1), array('id', 'advtime', 'advday'));
    if (set('p94') && !empty($advs) && empty($resp['code'])) {
        $url = $this->createMobileUrl('red', array('deviceid' => $device['id']));
    } else {
        if ($device['openurl']) {
            $url = $device['openurl'];
        } else {
            $url = $this->createMobileUrl('home');
        }
    }
    $data['url'] = $url;
    util::send_result($data);
}