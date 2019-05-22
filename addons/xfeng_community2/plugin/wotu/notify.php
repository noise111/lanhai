<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/25 0025 下午 1:49
 */
/**
 * 识别回调
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
require_once './function.php';
header('ContentType:application/x-www-form-urlencoded');
if (!empty($_POST)) {
    $result = json_decode(json_encode($_POST), true);
    $devices = pdo_getall('xcommunity_building_device', array(), array(), 'device_code');
    $photoUrl = $result['photoUrl'];// 现场照url
    $personGuid = $result['personGuid'];// 人员guid，陌生人为STRANGERBABY，人证比对为IDCARDBABY
    $recMode = $result['recMode'];// 识别模式，1:刷脸，2:刷卡，3:双重认证， 4:人证比对
    $resData = str_replace("\\", '', $result['data']);// 人脸附属信息
    $showTime = $result['showTime'];// 识别记录时间(日期格式)
    $deviceKey = $result['deviceKey'];// 设备序列号
    $guid = $result['guid'];
    $userGuid = $result['userGuid'];
    $type = $result['type'];// 识别出的人员类型，0：时间段内，1：时间段外，2：陌生人

    $data = array(
        'uniacid'    => $devices[$deviceKey]['uniacid'],
        'personguid' => $personGuid,
        'devicekey'  => $deviceKey,
        'photourl'   => $photoUrl,
        'type'       => $type,
        'recmode'    => $recMode,
        'createtime' => strtotime($showTime),
        'subdata'    => $resData
    );
    pdo_insert('xcommunity_face_logs', $data);
}