<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/11/28 下午3:19
 */
defined('IN_IA') or exit('Access Denied');
isetcookie('__session', '', -10000);

$forward = $_GPC['forward'];
if (empty($forward)) {
    $forward = './?refersh';
}
if(file_exists(IA_ROOT.'/xiaoqu')){
    $url = $_W['siteroot'].'/xiaoqu';
    header('Location:' . $url);
}else{
    header('Location:' . url('user/login'));
}
