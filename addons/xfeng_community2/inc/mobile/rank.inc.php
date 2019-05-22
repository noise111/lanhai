<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/15 0015
 * Time: 下午 2:40
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list','detail','grab')) ? trim($_GPC['op']) : 'list';
if($op =='detail'){
    include $this->template($this->xqtpl('rank/detail'));
}
elseif ($op == 'grab'){

    include $this->template($this->xqtpl('rank/grab'));

}