<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');
if (!empty($input)) {
    $obj = json_decode($input, true);
    file_put_contents('../../../addons/xfeng_community/charging/test1.txt', $obj);
}
else {
    exit('fail');
}


