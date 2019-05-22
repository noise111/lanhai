<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/6/12 0012
 * Time: 下午 5:23
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']), array('list','index','manage')) ? trim($_GPC['op']) : 'index';
/**
 * 管理员
 */
if ($op == 'manage') {
    include $this->template($this->xqtpl('plugin/counter/manage'));
}
/**
 * 超市
 */
if ($op == 'index') {
    include $this->template($this->xqtpl('plugin/counter/index'));
}
/**
 * 用户存放
 */
if ($op == 'list') {
    include $this->template($this->xqtpl('plugin/counter/list'));
}