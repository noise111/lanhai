<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 * 地址
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list','detail','add')) ? trim($_GPC['op']) : 'list';
if($op =='list'){
    include $this->template($this->xqtpl('core/address/list'));
}elseif($op =='add'){
    include $this->template($this->xqtpl('core/address/add'));
}
