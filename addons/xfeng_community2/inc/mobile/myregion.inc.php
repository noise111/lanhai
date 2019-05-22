<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端我的小区
 */
global $_W,$_GPC;

$member = model_user::mc_check();
include $this->template($this->xqtpl('core/member/myregion'));
