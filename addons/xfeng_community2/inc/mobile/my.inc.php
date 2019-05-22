<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/21 下午2:32
 */
global $_GPC, $_W;
$member = model_user::mc_check();
include $this->template($this->xqtpl('plugin/my'));