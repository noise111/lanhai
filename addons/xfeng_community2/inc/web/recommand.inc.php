<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/4 下午3:32
 */
global $_W, $_GPC;
$ops = array('list', 'house', 'activity', 'market', 'change');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$user = util::xquser($_W['uid']);
$regions = model_region::region_fetall();
/**
 * 商品的推荐
 */
if ($op == 'list') {
    if (checksubmit('submit')) {
        if (!empty($_GPC['sort'])) {
            foreach ($_GPC['sort'] as $key => $val) {
                pdo_update('xcommunity_goods', array('sort' => $val), array('id' => $key));
            }
            util::permlog('', '首页商品推荐设置');
            itoast('更新成功！', 'refresh', 'success', ture);
        }
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    if (!empty($_GPC['keyword'])) {
        $condition = " AND title LIKE '%{$_GPC['keyword']}%'";
    } else {
        $condition = ' and isrecommand=1 and type <> 4';
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE uniacid = '{$_W['uniacid']}' $condition  order by sort desc limit " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('xcommunity_goods') . " WHERE uniacid = '{$_W['uniacid']}' and type = 1 $condition");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/recommand/recommand');
}
/**
 * 租赁的推荐
 */
if ($op == 'house') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition['category'] = intval($_GPC['category']);
    }
    if ($_GPC['status'] != '') {
        $condition['status'] = intval($_GPC['status']);
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime) && $starttime == $endtime) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    if ($_GPC['regionid']) {
        $condition['regionid'] = $_GPC['regionid'];
    } else {
        if ($user && $user['type'] == 3) {
            //小区管理员
            $condition['regionid'] = $regionids;
        }
    }
    $houses = pdo_getslice('xcommunity_houselease', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $houses_uids = _array_column($houses, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $houses_uids), array('realname', 'mobile', 'uid'), 'uid');
    $list = array();
    foreach ($houses as $k =>$v) {
        $list[] = array(
            'id' => $v['id'],
            'title' => $v['title'],
            'way' => $v['way'],
            'realname' => $v['realname'] ? $v['realname'] : $members[$v['uid']]['realname'],
            'mobile' => $v['mobile'] ? $v['mobile'] : $members[$v['uid']]['mobile'],
            'createtime' => $v['createtime'],
            'status' => $v['status'],
            'recommand' => $v['recommand'],
        );
    }
    include $this->template('web/plugin/recommand/house');
}
/**
 * 集市推荐
 */
if ($op == 'market') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['black'] = 0;
    if (!empty($_GPC['category'])) {
        $condition['category'] = intval($_GPC['category']);
    }
    if ($_GPC['status'] != '') {
        $condition['status'] = intval($_GPC['status']);
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime) && $starttime == $endtime) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    if ($_GPC['regionid']) {
        $condition['regionid'] = $_GPC['regionid'];
    } else {
        if ($user && $user['type'] == 3) {
            //小区管理员
            $condition['regionid'] = $regionids;
        }
    }
    $categories = util::fetchall_category(4);
    $fleds = pdo_getslice('xcommunity_fled', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $fleds_uids = _array_column($fleds, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $fleds_uids), array('realname', 'mobile', 'uid'), 'uid');
    $list = array();
    foreach ($fleds as $k =>$v) {
        $list[] = array(
            'id' => $v['id'],
            'title' => $v['title'],
            'realname' => $v['realname'] ? $v['realname'] : $members[$v['uid']]['realname'],
            'mobile' => $v['mobile'] ? $v['mobile'] : $members[$v['uid']]['mobile'],
            'createtime' => $v['createtime'],
            'type' => $v['type'],
            'recommand' => $v['recommand'],
            'zprice' => $v['zprice'],
        );
    }
    include $this->template('web/plugin/recommand/market');
}
/**
 * 活动推荐
 */
if ($op == 'activity') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition['title like'] = "%{$_GPC['keyword']}%";
    }
    if ($user&&$user['type'] != 1) {
        //普通管理员
        $condition['uid'] = $_W['uid'];
    }
    $categories = util::fetchall_category(4);
    $activitys = pdo_getslice('xcommunity_activity', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $list = array();
    foreach ($activitys as $k =>$v) {
        $list[] = array(
            'id' => $v['id'],
            'title' => $v['title'],
            'createtime' => $v['createtime'],
            'starttime' => $v['starttime'],
            'endtime' => $v['endtime'],
            'price' => $v['price'],
            'recommand' => $v['recommand']
        );
    }
    include $this->template('web/plugin/recommand/activity');
}
/**
 * 切换推荐
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    $type = intval($_GPC['type']);
    $data = intval($_GPC['data']);
    if ($type == 1) {
        $tab = 'xcommunity_houselease';
    }
    if ($type == 2) {
        $tab = 'xcommunity_fled';
    }
    if ($type == 3) {
        $tab = 'xcommunity_activity';
    }
    $data = ($data == 1 ? '0' : '1');
    if ($tab) {
        $r = pdo_update($tab, array('recommand' => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        if ($r) {
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
    die(json_encode(array("result" => 0)));
}