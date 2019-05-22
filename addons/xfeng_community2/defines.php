<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/17 下午8:33
 */
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
define('MODULE_NAME','xfeng_community');
define('EARTH_RADIUS', 6378.137);//地球半径
define('PI', 3.1415926);
define('COMMUNITY_ADDON_ROOT', str_replace("\\", '/', dirname(__FILE__)) . '/');
!defined('XQ_PATH') && define('XQ_PATH', IA_ROOT . '/addons/xfeng_community/');
define('COMMUNITY_PATH', '../addons/xfeng_community/');