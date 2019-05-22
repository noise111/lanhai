<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/1/20 下午1:58
 */
global $_GPC,$_W;
$op = in_array(trim($_GPC['op']),array('confirm','detail','home')) ? trim($_GPC['op']) : 'home';
if($op =='home'){
    include $this->template($this->xqtpl('supermark/home'));
}elseif($op =='confirm'){
    include $this->template($this->xqtpl('supermark/confirm'));
}