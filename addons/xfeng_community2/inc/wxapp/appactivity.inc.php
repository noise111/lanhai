<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午1:28
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'del', 'order', 'orderDetail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 小区活动的列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid ";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        $condition .= " and t2.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $row = pdo_fetchall("select distinct t1.id ,t1.* from" . tablename('xcommunity_activity') . "t1 left join" . tablename('xcommunity_activity_region') . "t2 on t1.id=t2.activityid where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $list = array();
    foreach ($row as $k => $val) {
        $res = pdo_getall('xcommunity_res', array('aid' => $val['id']), array('num'));
        $total = 0;
        if ($res) {
            foreach ($res as $key => $item) {
                $total += $item['num'];
            }
        }
        $list[] = array(
            'title'     => $val['title'],
            'id'        => $val['id'],
            'picurl'    => tomedia($val['picurl']),
            'enddate'   => date('Y-m-d', $val['enddate']),
            'total'     => $total,
            'price'     => $val['price'],
            'url'       => $this->createMobileUrl('xqsys', array('op' => 'activity', 'p' => 'detail', 'id' => $val['id'])),
            'starttime' => date('Y/m/d', $val['starttime']),
            'endtime'   => date('Y/m/d', $val['endtime']),
            'desc'      => '活动时间:' . date('Y/m/d', $val['starttime']) . '至' . date('Y/m/d', $val['endtime']),
            'src'       => $val['picurl'] ? tomedia($val['picurl']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',

        );
    }
    util::send_result($list);
}
/**
 * 小区活动的详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $detail = pdo_get('xcommunity_activity', array('id' => $id), array());

        $regions = pdo_fetchall("select t1.title,t1.id from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_activity_region') . "t2 on t1.id=t2.regionid where t2.activityid=:activityid", array(":activityid" => $detail['id']));
        $region = '';
        $all = array();
        foreach ($regions as $k => $v) {
            $region .= $v['title'] . ',';
            $all[] = $v['id'];
        }
        $region = xtrim($region);
        $pics = array(tomedia($detail['picurl']));
        $data = array(
            'title'      => $detail['title'],
            'picurl'     => tomedia($detail['picurl']),
            'content'    => strip_tags($detail['content']),
            'createtime' => date('Y-m-d H:i', $detail['createtime']),
            'regions'    => $regions,
            'starttime'  => date('Y年m月d日', $detail['starttime']),
            'endtime'    => date('Y年m月d日', $detail['endtime']),
            'enddate'    => date('Y年m月d日', $detail['enddate']),
            'number'     => $detail['number'],
            'num'        => $detail['num'],
            'price'      => $detail['price'],
            'status'     => $detail['status'],
            'region'     => $region,
            'pics'       => $pics,
            'list'       => $all
        );
        util::send_result($data);

    }
}
/**
 * 添加小区活动
 */
if ($op == 'add') {
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'title'      => $_GPC['title'],
        'starttime'  => str_date($_GPC['starttime']),
        'endtime'    => str_date($_GPC['endtime']),
        'enddate'    => str_date($_GPC['enddate']) + 86400 - 1,
        'picurl'     => $_GPC['morepic'],
        'number'     => !empty($_GPC['number']) ? $_GPC['number'] : '1',
        'content'    => htmlspecialchars_decode($_GPC['content']),
        'status'     => $_GPC['status'],
        'createtime' => TIMESTAMP,
        'price'      => $_GPC['price'],
        'num'        => trim(intval($_GPC['num']))
    );
    $id = intval($_GPC['id']);
    if (empty($_GPC['id'])) {
        $data['uid'] = $_SESSION['appuid'];
        pdo_insert('xcommunity_activity', $data);
        $id = pdo_insertid();
        util::permlog('小区活动-添加', '信息标题:' . $data['title']);
    } else {
        pdo_update('xcommunity_activity', $data, array('id' => $id));
        pdo_delete('xcommunity_activity_region', array('activityid' => $id));
        util::permlog('小区活动-修改', '信息标题:' . $data['title']);
    }
    $regionids = explode(',', xtrim($_GPC['regionid']));
    foreach ($regionids as $key => $value) {
        $dat = array(
            'activityid' => $id,
            'regionid'   => $value,
        );
        pdo_insert('xcommunity_activity_region', $dat);
    }
    util::send_result();
}
/**
 * 删除小区活动
 */
if ($op == 'del') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_activity', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_activity', array('id' => $id))) {
                util::permlog('小区活动-删除', '信息标题:' . $item['title']);
                pdo_delete('xcommunity_activity_region', array('activityid' => $id));
                util::send_result();

            }
        }

    }
}
/**
 * 活动报名订单
 */
if ($op == 'order') {
    $activityid = intval($_GPC['activityid']);
    $condition = array();
    $condition['uniacid'] = $_SESSION['appuniacid'];
    $condition['aid'] = $activityid;
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $detail = pdo_get('xcommunity_activity', array('id' => $activityid), array('price'));
    $res = pdo_getslice('xcommunity_res', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $res_uids = _array_column($res, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $res_uids), array('realname', 'mobile'));
    $list = array();
    foreach ($res as $k => $v) {
        $list[] = array(
            'id'         => $v['id'],
            'realname'   => $v['truename'],
            'mobile'     => $v['mobile'],
            'num'        => $v['num'],
            'address'    => $v['address'],
            'status'     => $v['status'],
            'price'      => $detail['price'],
            'createtime' => date('Y-m-d H:i', $v['createtime'])
        );
    }
    util::send_result($list);
}
/**
 * 活动报名信息详情
 */
if ($op == 'orderDetail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_get('xcommunity_res', array('id' => $id), array());
    if (empty($item)) {
        util::send_error(-1, '该信息不存在或已经删除');
    }
    $activity = pdo_get('xcommunity_activity', array('id' => $item['aid']), array('price', 'title'));
    $data = array(
        'id'         => $item['id'],
        'realname'   => $item['truename'],
        'mobile'     => $item['mobile'],
        'num'        => $item['num'],
        'address'    => $item['address'],
        'status'     => $item['status'],
        'title'      => $activity['title'],
        'price'      => $activity['price'],
        'createtime' => date('Y-m-d H:i', $item['createtime'])
    );
    util::send_result($data);
}