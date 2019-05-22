<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/1/23 0023
 * Time: 下午 4:21
 */
global $_GPC,$_W;
$op = in_array(trim($_GPC['op']),array('home','detail')) ? trim($_GPC['op']) : 'home';
if($op =='home'){
    include $this->template($this->xqtpl('mien/home'));
}elseif($op =='detail'){
    include $this->template($this->xqtpl('mien/detail'));
}