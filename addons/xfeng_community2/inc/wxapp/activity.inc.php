<?php

global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$member = model_user::mc_check();
/**
 * 小区活动的列表
 */
if ($op == 'list') {
    $regionid = $_SESSION['community']['regionid'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    // 该小区的活动
    $region_aids = pdo_getall('xcommunity_activity_region', array('regionid' => $regionid), array('activityid'));
    $activityids = _array_column($region_aids, 'activityid');
    $condition['id'] = $activityids;
    $row = pdo_getslice('xcommunity_activity', $condition, array($pindex, $psize), $total, array(), '', array('status desc', 'createtime desc'));
    $res = pdo_getall('xcommunity_res', array('aid' => $activityids), array('aid', 'id'));
    $list = array();
    foreach ($row as $k => $val) {
//        $res = pdo_getcolumn('xcommunity_res', array('aid' => $val['id']), 'count(num)');
        $resTotal = 0;
        foreach ($res as $r) {
            if ($r['aid'] == $val['id']) {
                $resTotal++;
            }
        }
        $list[] = array(
            'title'     => $val['title'],
            'id'        => $val['id'],
            'picurl'    => tomedia($val['picurl']),
            'enddate'   => date('Y-m-d', $val['enddate']),
            'total'     => $resTotal,
            'price'     => $val['price'],
            'url'       => $this->createMobileUrl('activity', array('op' => 'detail', 'id' => $val['id'])),
            'starttime' => date('Y/m/d', $val['starttime']),
            'endtime'   => date('Y/m/d', $val['endtime']),
            'desc'      => '活动时间:' . date('Y/m/d', $val['starttime']) . '至' . date('Y/m/d', $val['endtime']),
            'src'       => $val['picurl'] ? tomedia($val['picurl']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',

        );
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
/**
 * 小区活动的详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $detail = pdo_get('xcommunity_activity', array('id' => $id), array('title', 'picurl', 'content', 'starttime', 'id', 'num', 'enddate', 'endtime', 'number', 'price'));
        $starttime = date('Y-m-d', $detail['starttime']);

        $picurl = tomedia($detail['picurl']);

        $res = pdo_getall('xcommunity_res', array('aid' => $detail['id'], 'status' => 1), array('num'));
//        $total = 0;
//        if ($res) {
//            foreach ($res as $k => $v) {
//                if (is_array($v)) {
//                    $total += $v['num'];
//                }
//            }
//        }
        $my = pdo_get('xcommunity_res', array('uid' => $_W['member']['uid'], 'aid' => $detail['id'], 'status' => 1), array('id'));
        $s1 = $my ? 1 : 0; //已报名
        if (($detail['enddate'] > TIMESTAMP)) {
            $s2 = 1;
        }
        if (TIMESTAMP > $detail['starttime'] && TIMESTAMP < $detail['endtime']) {
            $s3 = 1;
        }
        $s4 = count($res) < $detail['num'] ? 1 : 0;
        $regions = pdo_fetchall("select t1.title,t1.id from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_activity_region') . "t2 on t1.id=t2.regionid where t2.activityid=:activityid", array(":activityid" => $detail['id']));
        $regionids = '';
        foreach ($regions as $k => $v) {
            $regiontitle .= $v['title'] . ',';
            $regionids .= $v['id'] . ',';
        }
        $regiontitle = rtrim(ltrim($regiontitle, ','), ',');
        $data = array(
            'title'       => $detail['title'],
            'picurl'      => $picurl,
            'content'     => $detail['content'],
            'starttime'   => $starttime,
            'region'      => $_SESSION['community']['title'],
            'activityid'  => $detail['id'],
            'total'       => $total,
            'num'         => $detail['num'],
            'createtime'  => TIMESTAMP,
            'enddate'     => (int)$detail['enddate'],
            'number'      => $detail['number'],
            'regionids'   => $regionids,
            'price'       => $detail['price'],
            'regiontitle' => $regiontitle,
            'hstatus'     => set('p96') ? 1 : 0
        );
        if ($s1) {
            $data['status'] = 1;//已报名
        }
        else {
            if (empty($s1) && $s2 && $s4) {
                $data['status'] = 2;//未报名
            }
            else {
                $data['status'] = 3;//报名结束
            }

        }
        util::send_result($data);

    }
}
/**
 * 小区活动的报名
 */
if ($op == 'add') {
    $regionid = $_SESSION['community']['regionid'];
    $realname = trim($_GPC['realname']);
    $num = intval(trim($_GPC['num']));
    $tel = trim($_GPC['tel']);
    $addressid = trim($_GPC['address']);
    $activityid = intval($_GPC['activityid']);
    $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_activity') . "WHERE id=:activityid", array(':activityid' => $activityid));
    if (empty($item)) {
        util::send_error(-1, '信息不存在');
        exit();
    }
    if ($num > $item['num']) {
        $data = array();
        $data['status'] = 1;
        $data['content'] = '最大报名数' . $item['num'] . '人';
        util::send_result($data);
        exit();
    }
    $title = pdo_getcolumn('xcommunity_region', array('id' => $regionid), 'title');
    $address = pdo_getcolumn('xcommunity_member_room', array('id' => $addressid), 'address');
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'num'        => $num,
        'aid'        => $activityid,
        'createtime' => TIMESTAMP,
        'status'     => $item['price'] != '0.00' && $item['price'] != '0' ? 0 : 1,
        'truename'   => $realname,
        'mobile'     => $tel,
        'address'    => $title . $address
    );
    if (pdo_insert('xcommunity_res', $data)) {
        $resid = pdo_insertid();
        $t17 = set('t17');
        if ($t17) {
            $content = array(
                'first'    => array(
                    'value' => '活动预约报名',
                ),
                'keyword1' => array(
                    'value' => $_GPC['realname'],
                ),
                'keyword2' => array(
                    'value' => $_GPC['tel'],
                ),
                'keyword3' => array(
                    'value' => date('Y-m-d H:i'),
                ),
                'keyword4' => array(
                    'value' => $item['title'],
                ),
                'remark'   => array(
                    'value' => '',
                ),
            );
            $t18 = set('t18');
            $tplid = $t18;
            $notice = pdo_fetchall("select t1.type,t2.openid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id left join" . tablename('xcommunity_notice_region') . "t3 on t1.id=t3.nid where t1.uniacid=:uniacid and t1.enable=5 and t3.regionid=:regionid", array(':uniacid' => $_W['uniacid'], ':regionid' => $regionid));
            foreach ($notice as $k => $v) {
                if ($v['type'] == 1 || $v['type'] == 3) {
                    util::sendTplNotice($v['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                }
            }
        }
        if (empty($data['status'])) {
            $dat = array(
                'uniacid'    => $_W['uniacid'],
                'uid'        => $_W['member']['uid'],
                'createtime' => TIMESTAMP,
                'price'      => $item['price'] * $data['num'],
                'type'       => 'activity',
                'regionid'   => $_SESSION['community']['regionid'],
                'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
            );
            $d = array(
                'goodsid'    => $activityid,
                'price'      => $item['price'],
                'total'      => $data['num'],
                'createtime' => TIMESTAMP,
                'uniacid'    => $_W['uniacid']
            );
            if (pdo_insert('xcommunity_order', $dat)) {
                $orderid = pdo_insertid();
                pdo_update('xcommunity_res', array('orderid' => $orderid), array('id' => $resid));
                $d['orderid'] = $orderid;
                if (pdo_insert('xcommunity_order_goods', $d)) {
                    $url = $this->createMobileUrl('paycenter', array('type' => 4, 'orderid' => $orderid));
                    $data = array();
                    $data['status'] = 1;
                    $data['url'] = $url;
                    util::send_result($data);
                    exit();
                }
            }
        }
        else {
            $data = array();
            $data['status'] = 2;
            $data['content'] = '预约成功';
            util::send_result($data);

        }
    }

}
