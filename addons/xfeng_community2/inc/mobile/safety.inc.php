<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/7/2 0002
 * Time: 上午 10:42
 */
global $_W, $_GPC;

$member = model_user::mc_check('charging');

include $this->template($this->xqtpl('plugin/safety/log'));
