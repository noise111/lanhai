<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/15 0015
 * Time: 下午 2:42
 */
global $_GPC, $_W;
$ops = array('list', 'detail','grab');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list'){

}
elseif($op == 'detail'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.mark,t1.id,t2.company,t2.realname,t2.reg_address,t1.createtime,t2.title from".tablename('xcommunity_rank_property')."t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE t1.id=:id",array(':id' => $id));
    $item['thumb'] = "../addons/".$this->module['name']."/template/mobile/default2/static/img/property_rank.png";
    $item['createtime'] = date('Y年m月d日');
    util::send_result($item);
}
elseif ($op == 'grab'){
    $id = intval($_GPC['id']);
    $pics = xtrim($_GPC['pics']);
    $data = array(
        'uniacid'   => $_W['uniacid'],
        'reportid'  => $id,
        'uid'       => $_W['member']['uid'],
        'content'   => trim($_GPC['content']),
        'images'    => $pics,
        'createtime'    => TIMESTAMP
    );
    pdo_insert('xcommunity_rank_report_log',$data);
    util::send_result();
}