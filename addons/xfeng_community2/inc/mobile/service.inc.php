<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 下午5:42
 */
global $_W,$_GPC;
$_W['page']['title'] = $_SESSION['community']['title'];
include $this->template($this->xqtpl('core/service/home'));