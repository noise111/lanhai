<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/25 0025 下午 4:02
 */
/**
 * 人员照片下发状态回调
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
require_once './function.php';
header('ContentType:application/x-www-form-urlencoded');
if (!empty($_POST)) {
    $result = json_decode(json_encode($_POST), true);
    $personGuid = $result['personGuid'];
    $state = $result['state'];
    $status = 0;
    if ($state == 3) {
        $status = 1;
    }
    if ($state == 4) {
        $status = 0;
    }
    pdo_update('xcommunity_face_users', array('status' => $status), array('guid' => $personGuid));
}
