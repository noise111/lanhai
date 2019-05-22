<?php
/**
 * 小程序接口定义
 * http://s.we7.cc/index.php?c=wiki&do=view&id=1&list=2
 * @author 白开水浇花
 * @url
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/addons/xfeng_community/defines.php';
require COMMUNITY_ADDON_ROOT . 'class/autoload.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
class xfeng_communityModuleWxapp extends WeModuleWxapp
{
    // 接口文件以 xxx.inc.php 合名放到 inc/wxapp 目录下
    // 和 web mobile 端开发一样
    // 兼容低版本不能正确 require wxapp下inc文件
    public function __call($name, $arguments)
    {
        $name = substr($name, 6);
        $file = IA_ROOT . '/addons/' . $this->modulename . '/inc/wxapp/' . $name . '.inc.php';
        if (is_file($file)) {
            require $file;
            exit;
        }
        $file = IA_ROOT . '/addons/' . $this->modulename . '/inc/wxapp/' . lcfirst($name) . '.inc.php';
        if (is_file($file)) {
            require $file;
            exit;
        }
        trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
    }
}