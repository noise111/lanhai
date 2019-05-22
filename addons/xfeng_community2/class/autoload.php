<?php
/**
 * Created by LanNiu.
 * User: zhoufeng
 * Time: 2017/6/17 上午9:44
 */
function xfeng_community_autoLoad($class_name)
{
    $file = MODULE_ROOT . "/class/" . strtolower($class_name) . ".class.php";
    if (is_file($file)) {
        require_once $file;
    }
    return false;
}

spl_autoload_register('xfeng_community_autoLoad');
