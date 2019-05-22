<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/3/7 0007
 * Time: 上午 10:50
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('add', 'list', 'del', 'log', 'line', 'verify')) ? trim($_GPC['op']) : 'list';
$p = in_array(trim($_GPC['p']), array('add', 'list', 'del', 'display', 'send')) ? trim($_GPC['p']) : 'list';
$regions = model_region::region_fetall();
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'list') {
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_safety', array('id' => $id));
            }
            $idss = ltrim(rtrim(implode(',', $_GPC['ids']), ','), ',');
            util::permlog('安防管理-巡更分布', '批量删除巡更点id' . $idss);
            itoast('删除成功', referer(), 'success');
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    if (intval($_GPC['regionid'])) {
        $condition .= " and t1.regionid ={$_GPC['regionid']}";
    }
    if (!empty($_GPC['title'])) {
        $condition .= " and t1.title LIKE '%{$_GPC['title']}%'";
    }
    if (!empty($_GPC['device_code'])) {
        $condition .= " and t1.device_code LIKE '%{$_GPC['device_code']}%'";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "select t1.*,t2.title as rtitle,t3.title as atitle,t4.buildtitle,t5.unit from" . tablename('xcommunity_safety') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join" . tablename('xcommunity_area') . "t3 on t3.id = t1.areaid left join" . tablename('xcommunity_build') . "t4 on t1.buildid=t4.id left join" . tablename('xcommunity_unit') . "t5 on t1.unitid=t5.id where $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_safety') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join" . tablename('xcommunity_area') . "t3 on t3.id = t1.areaid left join" . tablename('xcommunity_build') . "t4 on t1.buildid=t4.id left join" . tablename('xcommunity_unit') . "t5 on t1.unitid=t5.id where $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/safety/list');
}
elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_safety', array('id' => $id), array());
        $areas = pdo_getall('xcommunity_area', array('id' => $item['areaid']), array('id', 'title'));
        $builds = pdo_getall('xcommunity_build', array('id' => $item['buildid']), array('id', 'buildtitle'));
        $units = pdo_getall('xcommunity_unit', array('id' => $item['unitid']), array('id', 'unit'));
        $arr = util::xqset($item['regionid']);
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'regionid'    => intval($_GPC['regionid']),
            'areaid'      => intval($_GPC['area']),
            'buildid'     => intval($_GPC['build']),
            'unitid'      => intval($_GPC['unit']),
            'title'       => trim($_GPC['title']),
            'device_code' => trim($_GPC['device_code']),
            'lng'         => $_GPC['baidumap']['lng'],
            'lat'         => $_GPC['baidumap']['lat'],
            'distance'    => intval($_GPC['distance']),
            'enable'      => intval($_GPC['enable']),
            'remark'      => trim($_GPC['remark']),
            'createtime'  => TIMESTAMP,
            'card_num'    => trim($_GPC['card_num'])
        );
        if ($id) {
            if (pdo_update('xcommunity_safety', $data, array('id' => $id))) {

            }
        }
        else {
            if (pdo_insert('xcommunity_safety', $data)) {

            }
        }
        echo json_encode(array('status'=>1));exit();
    }
    include $this->template('web/plugin/safety/add');
}
elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_safety', array('id' => $id), array());
    }
    if ($item) {
        if (pdo_delete('xcommunity_safety', array('id' => $id))) {
            util::permlog('安防管理-巡更分布', '删除巡更点id' . $id);
            itoast('删除成功', referer(), 'success');
        }
    }
}
/**
 * 巡更记录
 */
if ($op == 'log') {
    if ($p == 'list') {

    }
    /**
     * 巡更记录列表
     */
    if ($p == 'display') {
        if (checksubmit('pldelete')) {
            $safetyid = intval($_GPC['safetyid']);
            if ($safetyid) {
                $device_code = pdo_getcolumn('xcommunity_safety', array('id' => $safetyid), 'device_code');
                $r = pdo_delete('xcommunity_safety_device_log', array('device_code' => $device_code));
                if ($r) {
                    util::permlog('安防管理-巡更分布', '一键清空记录');
                    itoast('删除成功', referer(), 'success');
                }
            }
        }
        $id = intval($_GPC['id']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
//        $condition['safetyid'] = $id;
        $safety = pdo_get('xcommunity_safety', array('id' => $id), array('title', 'card_num', 'device_code'));
        $condition['device_code'] = $safety['device_code'];
        if ($_GPC['export'] == 1) {
            $logs1 = pdo_getall('xcommunity_safety_device_log', $condition, array());
            $list1 = array();
            foreach ($logs1 as $k => $v) {
                $list1[] = array(
                    'id' => $v['id'],
                    'title' => $safety['title'],
                    'card_num' => $v['card_num'],
                    'acq_date' => date('Y-m-d H:i', $v['acq_date']),
                    'createtime' => date('Y-m-d H:i', $v['createtime']),
                    'dcard_num' => $safety['card_num'],
                    'device_code' => $safety['device_code'],
                    'status' => $v['card_num'] == $safety['card_num'] ? '正常' : '异常'
                );
            }
            model_execl::export($list1, array(
                "title"   => "设备巡更记录数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => 'ID',
                        'field' => 'id',
                        'width' => 12
                    ),
                    array(
                        'title' => '巡更点名称',
                        'field' => 'title',
                        'width' => 12
                    ),
                    array(
                        'title' => '设备编号',
                        'field' => 'device_code',
                        'width' => 20
                    ),
                    array(
                        'title' => '卡号',
                        'field' => 'card_num',
                        'width' => 12
                    ),
                    array(
                        'title' => '巡检时间',
                        'field' => 'acq_date',
                        'width' => 12
                    ),
//                    array(
//                        'title' => '同步时间',
//                        'field' => 'createtime',
//                        'width' => 12
//                    ),
                    array(
                        'title' => '状态',
                        'field' => 'status',
                        'width' => 12
                    ),
                )
            ));
        }
        $logs = pdo_getslice('xcommunity_safety_device_log', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
        $list = array();
        foreach ($logs as $k => $v) {
            $list[] = array(
                'id' => $v['id'],
                'title' => $safety['title'],
                'card_num' => $v['card_num'],
                'acq_date' => $v['acq_date'],
                'createtime' => $v['createtime'],
                'dcard_num' => $safety['card_num'],
                'device_code' => $safety['device_code']
            );
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/safety/log_display');
    }
    /**
     * 同步巡更记录
     */
    if ($p == 'send') {
        $time = $_GPC['birth'];
        $starttime = strtotime($time['start']);
        $endtime = strtotime($time['end']);
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $id = intval($_GPC['id']);
        $device = pdo_get('xcommunity_safety', array('id' => $id), array('device_code'));
        load()->func('communication');
        $pindex = max(1, intval($_GPC['page']));
        $psize = 50;
        $count = 0;
        $getCount = max(0, intval($_GPC['record']));//已获取数量
        $start = max(0, intval($_GPC['start']));//起始条数
        $data = array(
            'sn'        => $device['device_code'],
            'starttime' => date('Ymdhms', $starttime),
            'endtime'   => date('Ymdhms', $endtime),
            'start'     => $start,
            'limit'     => $psize
        );
        $params = json_encode($data);
        $result = http_post('http://122.114.58.8:8018/cp/djj/listPatrol.ext', $params);
        $result = @json_decode($result['content'], true);
        $list = $result['list'];
        //"acq_date":"2017-06-30 11:09:20","card_num":"12345AB","longitude":121.123567, 		"latitude":37.123456}
        //循环插入到数据表
        foreach ($list as $k => $v) {
            $dat = array(
                'uniacid'    => $_W['uniacid'],
                'safetyid'   => $id,
                'lng'        => $v['longitude'],
                'lat'        => $v['latitude'],
                'acq_date'   => strtotime($v['acq_date']),
                'card_num'   => $v['card_num'],
                'createtime' => TIMESTAMP,
                'uid'        => $_W['uid']
            );
            pdo_insert('xcommunity_safety_device_log', $dat);
        }
        $count = $result['count'];//总数量
        $getCount += $psize;
        $start += $psize;
        if ($getCount == $count) {
            echo json_encode(array('status' => 'end'));
            exit();
        }
        if (empty($list)) {
            echo json_encode(array('status' => 'emp'));
            exit();
        }
        echo json_encode(array('count' => $count, 'getCount' => $getCount, 'start' => $start));
        exit();
    }
}
elseif ($op == 'line') {
    if ($p == 'list') {
        if (checksubmit('del')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_safety_line', array('id' => $id));
                }
                $idss = ltrim(rtrim(implode(',', $_GPC['ids']), ','), ',');
                util::permlog('安防管理-巡更路线', '批量删除巡更路线id' . $idss);
                itoast('删除成功', referer(), 'success');
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = " t1.uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid ={$_GPC['regionid']}";
        }
        if (!empty($_GPC['title'])) {
            $condition .= " and t1.title LIKE '%{$_GPC['title']}%'";
        }
        if ($user[type] == 3) {
            //普通管理员
            $condition .= " and t1.regionid in({$user['regionid']})";
        }
        $sql = "select t1.*,t2.title as rtitle from" . tablename('xcommunity_safety_line') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id where $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from" . tablename('xcommunity_safety') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id where $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/safety/line_list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        $safetys = pdo_getall('xcommunity_safety', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        if ($id) {
            $item = pdo_get('xcommunity_safety_line', array('id' => $id));
            $cids = pdo_getall('xcommunity_safety_line_category', array('lineid' => $id), array('cid'));
            $cid = '';
            foreach ($cids as $key => $val) {
                $cid .= $val['cid'] . ',';
            }
            $cid = ltrim(rtrim($cid, ","), ',');
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'regionid'   => intval($_GPC['regionid']),
                'title'      => trim($_GPC['title']),
                'starttime'  => strtotime($_GPC['starttime']),
                'endtime'    => strtotime($_GPC['endtime']),
                'status'     => intval($_GPC['status']),
                'enable'     => intval($_GPC['enable']),
                'remark'     => trim($_GPC['remark']),
                'createtime' => TIMESTAMP
            );
            if ($id) {
                if (pdo_update('xcommunity_safety_line', $data, array('id' => $id))) {
                    pdo_delete('xcommunity_safety_line_category', array('lineid' => $id));
                }
            }
            else {
                pdo_insert('xcommunity_safety_line', $data);
                $id = pdo_insertid();
            }
            foreach ($_GPC['cid'] as $key => $value) {
                $d = array(
                    'uniacid' => $_W['uniacid'],
                    'lineid'  => $id,
                    'cid'     => $value,
                );
                pdo_insert('xcommunity_safety_line_category', $d);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/safety/line_add');
    }
    elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_safety_line', array('id' => $id), array());
        }
        if ($item) {
            if (pdo_delete('xcommunity_safety_line', array('id' => $id))) {
                util::permlog('安防管理-巡更路线', '删除巡更路线id' . $id);
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
elseif ($op == 'verify') {
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable', 'enable_line'))) {
        $data = ($data == 1 ? '2' : '1');
        if ($type == 'enable') {
            pdo_update("xcommunity_safety", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        }
        elseif ($type == 'enable_line') {
            pdo_update("xcommunity_safety_line", array('enable' => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}