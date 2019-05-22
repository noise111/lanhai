<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/1/23 0023
 * Time: 下午 4:22
 */
global $_GPC, $_W;
$ops = array('home');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $regionid = intval($_GPC['regionid']);
    $li = pdo_fetchall("select t1.* from" . tablename('xcommunity_mien') . "as t1 left join" . tablename('xcommunity_mien_region') . "as t2 on t1.id = t2.mienid where t1.uniacid=:uniacid and t2.regionid=:regionid order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':regionid' => $regionid));

    foreach ($li as $k => $val) {
        $list[] = array(
            'realname' => $val['realname'],
            'id' => $val['id'],
            'image' => tomedia($val['image']),
            'mobile'    => $val['mobile'],
            'position'  => $val['position'],
            'createtime' => date('Y-m-d', $val['enddate']),
            'url' => $this->createMobileUrl('mien', array('op' => 'detail', 'id' => $val['id'])),
        );
    }
    if ($list) {
        $data = array();
        $data['list'] = $list;
        util::send_result($data);
    } else {
        util::send_error(-1, '');
    }
}