<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/21 0021 下午 2:37
 */
global $_GPC, $_W;
require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
//$id = intval($_GPC['id']);
$deviceids = $_GPC['deviceids'];
$thumb = xtrim($_GPC['thumb']);
$uid = $_W['member']['uid'];
$devices = pdo_getall('xcommunity_building_device', array('id' => explode(',', $deviceids)), array('appid', 'appkey', 'appsecret', 'device_code'));
$appid = $devices[0]['appid'];
$appkey = $devices[0]['appkey'];
$appsecret = $devices[0]['appsecret'];
$member = pdo_get('xcommunity_face_users', array('uid' => $uid), array('guid', 'id', 'images', 'deviceids'));
// 人员录入
if ($member['guid']) {
    $guid = $member['guid'];
    $memberid = $member['id'];
    $images = explode(',', $member['images']);
    // 更新人员
    if ($guid) {
        $resupdate = updatePerson($appid, $appkey, $appsecret, $guid, $_W['member']['realname'], '', $_W['member']['mobile']);
    }
    // 删除人脸照片
    $personGuids = array();
    if ($images) {
        foreach ($images as $k => $v) {
            $personGuids[] = explode('|', $v)[0];
        }
        if ($personGuids) {
            foreach ($personGuids as $k => $v) {
                deletePersonImage($appid, $appkey, $appsecret, $v, $guid);
            }
        }
    }
    // 删除授权的设备
    $deviceid = explode(',', $item['deviceids']);
    $devicess = pdo_getall('xcommunity_building_device', array('id' => $deviceid), array('device_code'));
    $deviceKeys = '';
    foreach ($devicess as $k => $v) {
        $deviceKeys .= $v['device_code'] . ',';
    }
    if ($deviceKeys) {
        plDeletePersonDevice($appid, $appkey, $appsecret, $guid, xtrim($deviceKeys));
    }
} else {
    $result = addPerson($appid, $appkey, $appsecret, $_W['member']['realname']);
    if ($result['code'] = "GS_SUS200") {
        $guid = $result['data']['guid'];// 人员guid
        if ($uid) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'uid'        => $uid,
                'guid'       => $guid,
                'realname'   => $_W['member']['realname'],
                'mobile'     => $_W['member']['mobile'],
                'createtime' => TIMESTAMP,
                'status'     => 0,
                'deviceids'  => $deviceids
            );
            pdo_insert('xcommunity_face_users', $data);
            $memberid = pdo_insertid();
        }
    }
}
// 照片注册
$thumbs = explode(',', $thumb);
$img = '';
foreach ($thumbs as $k => $v) {
    $image = addPersonImageUrl($appid, $appkey, $appsecret, $guid, tomedia($v));
    if ($image['code'] == "GS_SUS600") {
        $img .= $image['data']['guid'] . "|" . tomedia($v) . ',';
    }
}
pdo_update('xcommunity_face_users', array('images' => xtrim($img), 'realname' => $_W['member']['realname'], 'mobile' => $_W['member']['mobile']), array('id' => $memberid));
// 人脸的上传记录
$dat = array(
    'uniacid' => $_W['uniacid'],
    'uid' => $uid,
    'realname' => $_W['member']['realname'],
    'createtime' => TIMESTAMP,
    'images' => $thumb,
    'deviceids' => $deviceids
);
pdo_insert('xcommunity_face_uploadlogs', $dat);
// 人员设备授权
foreach ($devices as $k => $v) {
    $res = addPersonDevice($appid, $appkey, $appsecret, $guid, $v['device_code']);
}
if ($res['code'] != "GS_SUS204") {
    util::send_error(-1, '设备授权失败');

}
util::send_result();