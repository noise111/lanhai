<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/14 0014
 * Time: 上午 9:27
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list','detail')) ? trim($_GPC['op']) : 'list';
if($op =='list'){
    include $this->template($this->xqtpl('plugin/article/list'));
}elseif($op =='detail'){
    include $this->template($this->xqtpl('plugin/article/detail'));
}