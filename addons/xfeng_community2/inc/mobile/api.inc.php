<?php
/**
 * API 处理文件.
 * User: lanniu
 * Date: 17/7/6
 * Time: 上午6:00
 */
global $_GPC, $_W;
$op = trim($_GPC['op']);
// 允许的api接口名称
$ops = array('verity','user');
if (!in_array($op, $ops)) {
    util::send_error('-1', 'this api not have.');
}
$api_file = MODULE_ROOT . '/inc/api/' . $op . '.api.php';
if (!is_file($api_file)) {
    util::send_error('-2', 'this api not have.');
}
require $api_file;