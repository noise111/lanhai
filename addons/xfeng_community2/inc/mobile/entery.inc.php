<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/3 0003
 * Time: 下午 3:55
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list','add')) ? trim($_GPC['op']) : 'list';
if($op =='list'){
    include $this->template($this->xqtpl('core/entery/list'));
}elseif($op =='add'){
    include $this->template($this->xqtpl('core/entery/add'));
}