<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/3 上午10:02
 */
global $_W, $_GPC;
$ops = array('tx', 'del', 'list', 'cash', 'integral', 'credit', 'account', 'account_log', 'accountIntegral', 'member', 'regionIntegral', 'regionCredit', 'regionData', 'pcCredit');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 提现的列表
 */
if ($op == 'list') {
    $condition = " uniacid={$_W['uniacid']} AND type='cash' ";
    if ($user && $user[type] != 1) {
        $condition .= " AND uid=:uid";
        $parms[':uid'] = $_W['uid'];
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "SELECT * FROM" . tablename('xcommunity_order') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_order') . "WHERE $condition", $parms);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/list');
}
/**
 * 提现的删除
 */
if ($op == 'del') {
    //删除提现订单
    $id = intval($_GPC['id']);
    if (pdo_delete('xcommunity_order', array('id' => $id))) {
        $result = array(
            'status' => 1,
        );
        echo json_encode($result);
        exit();
    }
}
/**
 * 提现的审核
 */
if ($op == 'tx') {
    $id = intval($_GPC['id']);
    if ($id) {
        $r = pdo_query("UPDATE " . tablename('xcommunity_order') . "SET status = 1 WHERE id=:id", array(":id" => $id));
        echo json_encode(array('status' => 1));
        exit();
    }
}
/**
 * 提现的提交
 */
if ($op == 'cash') {

    $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_W['uid']));

    if (checksubmit('submit')) {
        if ($_GPC['cash'] <= 0) {
            itoast('输入金额不正确,请重新输入', referer(), 'error', true);
            exit();
        }
        if ($_GPC['cash'] > $users['balance']) {
            itoast('余额不足，无法提现', referer(), 'error', true);
        }

        $data = array(
            'uniacid' => $_W['uniacid'],
            'ordersn' => date('YmdHi') . random(10, 1),
            'price' => trim($_GPC['cash']),
            'type' => 'cash',
            'pay' => $_GPC['pay'],
            'createtime' => TIMESTAMP,
            'uid' => $_W['uid'],
            'category' => intval($_GPC['category'])
        );
        $r = pdo_insert('xcommunity_order', $data);
        if ($r) {
            pdo_update('xcommunity_users', array('balance -=' => trim($_GPC['cash'])), array('id' => $users['id']));
            itoast('提交成功', referer(), 'success', true);
        }
    }
    include $this->template('web/core/cash/add');
}
/**
 * 积分变更明细
 */
if ($op == 'integral') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 1;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $keyword = $_GPC['keyword'];
    if (is_numeric($keyword)) {
        $condition['mobile like'] = "%{$keyword}%";
    } else {
        $condition['realname like'] = "%{$keyword}%";
    }
    if ($_GPC['creditstatus']) {
        $condition['creditstatus'] .= intval($_GPC['creditstatus']);
    }
    if ($_GPC['export'] == 1) {
        $list1 = pdo_getall('xcommunity_credit', $condition, '', '', array('createtime desc'));
        $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        $shops_ids = _array_column($shops, NULL, 'id');
        $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'));
        $dps_ids = _array_column($dps, NULL, 'id');
        $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        $regions_ids = _array_column($regions, NULL, 'id');
        $type = array('', '物业', '商家', '超市', '活动', '账单', '报修', '充电桩', '云柜');
        $creditstatus = array('', '增加', '减少');
        foreach ($list1 as $k => $v) {
            if ($v['type'] == 1) {
                $list1[$k]['title'] = $regions_ids[$v['typeid']]['title'];
            } elseif ($v['type'] == 2) {
                $list1[$k]['title'] = $dps_ids[$v['typeid']]['sjname'];
            } elseif ($v['type'] == 3) {
                $list1[$k]['title'] = $shops_ids[$v['typeid']]['title'];
            }
            $list1[$k]['type'] = $type[$v['type']];
            $list1[$k]['creditstatus'] = $creditstatus[$v['creditstatus']];
            $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        }
        model_execl::export($list1, array(
            "title" => "积分变更明细数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '积分用途',
                    'field' => 'content',
                    'width' => 40
                ),
                array(
                    'title' => '积分数量',
                    'field' => 'credit',
                    'width' => 12
                ),
                array(
                    'title' => '积分操作',
                    'field' => 'creditstatus',
                    'width' => 12
                ),
                array(
                    'title' => '消费类型',
                    'field' => 'type',
                    'width' => 12
                ),
                array(
                    'title' => '商家/小区',
                    'field' => 'title',
                    'width' => 12
                ),
                array(
                    'title' => '操作时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_credit', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid']), array('id', 'title'));
    $shops_ids = _array_column($shops, NULL, 'id');
    $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'));
    $dps_ids = _array_column($dps, NULL, 'id');
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id', 'title'));
    $regions_ids = _array_column($regions, NULL, 'id');
    $members = pdo_getall('mc_members', array('uniacid' => $_W['uniacid']), array('uid', 'realname'));
    $members_ids = _array_column($members, NULL, 'uid');
    foreach ($list as $k => $v) {
        if ($v['type'] == 1 || $v['type'] == 5) {
            $list[$k]['title'] = $regions_ids[$v['typeid']]['title'];
        } elseif ($v['type'] == 2) {
            $list[$k]['title'] = $dps_ids[$v['typeid']]['sjname'];
        } elseif ($v['type'] == 3) {
            $list[$k]['title'] = $shops_ids[$v['typeid']]['title'];
        } elseif ($v['type'] == 9) {
            $list[$k]['title'] = $members_ids[$v['typeid']]['realname'];
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/integral_list');
}
/**
 * 余额变更明细
 */
if ($op == 'credit') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 2;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $keyword = $_GPC['keyword'];
    if (is_numeric($keyword)) {
        $condition['mobile like'] = "%{$keyword}%";
    } else {
        $condition['realname like'] = "%{$keyword}%";
    }
    if ($_GPC['creditstatus']) {
        $condition['creditstatus'] .= intval($_GPC['creditstatus']);
    }
    if ($_GPC['export'] == 1) {
        $list1 = pdo_getall('xcommunity_credit', $condition, '', '', array('createtime desc'));
        $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        $shops_ids = _array_column($shops, NULL, 'id');
        $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'));
        $dps_ids = _array_column($dps, NULL, 'id');
        $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        $regions_ids = _array_column($regions, NULL, 'id');
        $type = array('', '物业', '商家', '超市', '活动', '账单', '报修', '充电桩', '云柜');
        $creditstatus = array('', '增加', '减少');
        foreach ($list1 as $k => $v) {
            if ($v['type'] == 1 || $v['type'] == 4 || $v['type'] == 5 || $v['type'] == 6 || $v['type'] == 7 || $v['type'] == 8) {
                $list1[$k]['title'] = $regions_ids[$v['typeid']]['title'];
            } elseif ($v['type'] == 2) {
                $list1[$k]['title'] = $dps_ids[$v['typeid']]['sjname'];
            } elseif ($v['type'] == 3) {
                $list1[$k]['title'] = $shops_ids[$v['typeid']]['title'];
            }
            $list1[$k]['type'] = $type[$v['type']];
            $list1[$k]['creditstatus'] = $creditstatus[$v['creditstatus']];
            $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        }
        model_execl::export($list1, array(
            "title" => "余额变更明细数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '积分用途',
                    'field' => 'content',
                    'width' => 40
                ),
                array(
                    'title' => '积分数量',
                    'field' => 'credit',
                    'width' => 12
                ),
                array(
                    'title' => '积分操作',
                    'field' => 'creditstatus',
                    'width' => 12
                ),
                array(
                    'title' => '消费类型',
                    'field' => 'type',
                    'width' => 12
                ),
                array(
                    'title' => '商家/小区',
                    'field' => 'title',
                    'width' => 12
                ),
                array(
                    'title' => '操作时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_credit', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid']), array('id', 'title'));
    $shops_ids = _array_column($shops, NULL, 'id');
    $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'));
    $dps_ids = _array_column($dps, NULL, 'id');
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id', 'title'));
    $regions_ids = _array_column($regions, NULL, 'id');
    $members = pdo_getall('mc_members', array('uniacid' => $_W['uniacid']), array('uid', 'realname'));
    $members_ids = _array_column($members, NULL, 'uid');
    foreach ($list as $k => $v) {
        if ($v['type'] == 1 || $v['type'] == 4 || $v['type'] == 5 || $v['type'] == 6 || $v['type'] == 7 || $v['type'] == 8) {
            $list[$k]['title'] = $regions_ids[$v['typeid']]['title'];
        } elseif ($v['type'] == 2) {
            $list[$k]['title'] = $dps_ids[$v['typeid']]['sjname'];
        } elseif ($v['type'] == 3) {
            $list[$k]['title'] = $shops_ids[$v['typeid']]['title'];
        } elseif ($v['type'] == 9) {
            $list[$k]['title'] = $members_ids[$v['typeid']]['realname'];
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/credit_list');
}
/**
 * 商家账户积分余额统计
 */
if ($op == 'account') {
    //修改账号的余额、积分
    if (checksubmit('update')) {
        $type = intval($_GPC['type']);
        $id = intval($_GPC['id']);
        $credit = $_GPC['credit'];
        if ($type == 4) {
            $data = array(
                'balance +=' => $credit
            );
            $content = "系统后台:操作商家账户余额";
        } elseif ($type == 3) {
            $data = array(
                'credit +=' => $credit
            );
            $content = "系统后台:操作商家账户积分";
        }
        if (pdo_update('xcommunity_users', $data, array('id' => $id))) {
            $users = pdo_get('xcommunity_users', array('id' => $id), array('uid', 'staffid', 'id'));
            $staff = pdo_get('xcommunity_staff', array('id' => $users['staffid']), array('mobile'));
            if ($credit > 0) {
                $creditstatus = 1;
            } elseif ($credit < 0) {
                $creditstatus = 2;
            }
            $creditdata = array(
                'uid' => $users['uid'],
                'uniacid' => $_W['uniacid'],
                'realname' => $staff['mobile'],
                'mobile' => $staff['mobile'],
                'content' => $content,
                'credit' => abs($credit),
                'creditstatus' => $creditstatus,
                'createtime' => TIMESTAMP,
                'type' => 2,
                'typeid' => $users['uid'],
                'category' => $type,
                'usename' => $_W['username']
            );
            pdo_insert('xcommunity_credit', $creditdata);
        }
        itoast('修改成功', referer(), 'success');
        exit();
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['type'] = array(4, 5);
    $keyword = $_GPC['keyword'];
    if ($keyword) {
        $staffid = pdo_getcolumn('xcommunity_staff', array('realname' => $keyword), 'id');
        $condition['staffid'] = $staffid;
    }
    if ($_GPC['export'] == 1) {
        $users1 = pdo_getall('xcommunity_users', $condition, '', '', array('createtime desc'));
        $staffs = pdo_getall('xcommunity_staff', array('uniacid' => $_W['uniacid']), array('id', 'mobile', 'realname'));
        $staffs_ids = _array_column($staffs, NULL, 'id');
        $list1 = array();
        foreach ($users1 as $k => $v) {
            $list1[] = array(
                'mobile' => $staffs_ids[$v['staffid']]['mobile'],
                'id' => $v['id'],
                'realname' => $staffs_ids[$v['staffid']]['realname'],
                'balance' => $v['balance'],
                'credit' => $v['credit'],
                'type' => $v['type'] == 4 ? '超市' : '商家',
                'createtime' => date('Y-m-d H:i', $v['createtime'])
            );
        }
        model_execl::export($list1, array(
            "title" => "商家账户数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '商家',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '账户',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '余额',
                    'field' => 'balance',
                    'width' => 12
                ),
                array(
                    'title' => '积分',
                    'field' => 'credit',
                    'width' => 12
                ),
                array(
                    'title' => '类型',
                    'field' => 'type',
                    'width' => 12
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $users = pdo_getslice('xcommunity_users', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $staffs = pdo_getall('xcommunity_staff', array('uniacid' => $_W['uniacid']), array('id', 'mobile', 'realname'));
    $staffs_ids = _array_column($staffs, NULL, 'id');
    $integralTotal = 0;
    $creditTotal = 0;
    $list = array();
    foreach ($users as $k => $v) {
        $integralTotal += $v['credit'];
        $creditTotal += $v['balance'];
        $list[] = array(
            'mobile' => $staffs_ids[$v['staffid']]['mobile'],
            'id' => $v['id'],
            'realname' => $staffs_ids[$v['staffid']]['realname'],
            'balance' => $v['balance'],
            'credit' => $v['credit'],
            'type' => $v['type']
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/account_list');
}
/**
 * 商家余额变更明细
 */
if ($op == 'account_log') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 4;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $keyword = $_GPC['keyword'];
    if ($keyword) {
        $condition['realname'] = $keyword;
    }
    if ($_GPC['export'] == 1) {
        $list1 = pdo_getall('xcommunity_credit', $condition, '', '', array('createtime desc'));
        foreach ($list1 as $k => $v) {
            if ($v['creditstatus'] == 1) {
                $list1[$k]['credit'] = '+' . $v['credit'];
            } elseif ($v['creditstatus'] == 2) {
                $list1[$k]['credit'] = '-' . $v['credit'];
            }
            $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        }
        model_execl::export($list1, array(
            "title" => "商家账户变更明细数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '账户',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '收入/支出',
                    'field' => 'credit',
                    'width' => 14
                ),
                array(
                    'title' => '变更描述',
                    'field' => 'content',
                    'width' => 40
                ),
                array(
                    'title' => '操作时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_credit', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/account_log');
}
/**
 * 商家积分变更明细
 */
if ($op == 'accountIntegral') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 3;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $keyword = $_GPC['keyword'];
    if ($keyword) {
        $condition['realname'] = $keyword;
    }
    if ($_GPC['export'] == 1) {
        $list1 = pdo_getall('xcommunity_credit', $condition, '', '', array('createtime desc'));
        foreach ($list1 as $k => $v) {
            if ($v['creditstatus'] == 1) {
                $list1[$k]['credit'] = '+' . $v['credit'];
            } elseif ($v['creditstatus'] == 2) {
                $list1[$k]['credit'] = '-' . $v['credit'];
            }
            $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        }
        model_execl::export($list1, array(
            "title" => "商家账户变更明细数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '账户',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '收入/支出',
                    'field' => 'credit',
                    'width' => 14
                ),
                array(
                    'title' => '变更描述',
                    'field' => 'content',
                    'width' => 40
                ),
                array(
                    'title' => '操作时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_credit', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/account_integral');
}
/**
 * 会员的积分余额统计
 */
if ($op == 'member') {
    // 会员列表
    if ($p == 'list') {
        $condition['uniacid'] = $_W['uniacid'];
        $keyword = trim($_GPC['keyword']);
        if (is_numeric($keyword)) {
            $condition['mobile like'] = "%{$keyword}%";
        } else {
            $condition['realname like'] = "%{$keyword}%";
        }
        $membes = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid']), array('uid'));
        $membes_ids = _array_column($membes, 'uid');
        $condition['uid'] = $membes_ids;
        $pindex = max(1, intval($_GPC['page']));
        $export = intval($_GPC['export']); //是导出还是正常展示
        $psize = $export == 1 ? 1000000 : 20; //debug 临时设置导出
        $list = pdo_getslice('mc_members', $condition, array($pindex, $psize), $total, '', '', array('uid desc'));
        $fans = pdo_getall('mc_mapping_fans', array('uniacid' => $_W['uniacid']), array('uid', 'openid'));
        $fans_ids = _array_column($fans, NULL, 'uid');
        foreach ($list as $k => $v) {
            $list[$k]['openid'] = $fans_ids[$v['uid']]['openid'];
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        if ($export) {
            model_execl::export($list, array(
                "title"   => "会员数据-" . date('Y-m-d', time()),
                "columns" => array(
                    array(
                        'title' => 'UID',
                        'field' => 'uid',
                        'width' => 24
                    ),
                    array(
                        'title' => '姓名',
                        'field' => 'realname',
                        'width' => 24
                    ),
                    array(
                        'title' => '手机',
                        'field' => 'mobile',
                        'width' => 24
                    ),
                    array(
                        'title' => 'openid',
                        'field' => 'openid',
                        'width' => 20
                    ),
                    array(
                        'title' => '积分',
                        'field' => 'credit1',
                        'width' => 20
                    ),
                    array(
                        'title' => '余额',
                        'field' => 'credit2',
                        'width' => 24
                    ),
                    array(
                        'title' => '创建时间',
                        'field' => 'createtime',
                        'width' => 24
                    )
                )
            ));
            unset($list);
//            $url = $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'list', 'star' => $lastid, 'regionid' => $regionid, 'keyword' => $keyword, 'page' => $pindex++));
//            message('正在发送导出下一组！', $url, 'success');
        }

        /**
         * 计算总和
         */
        $total1 = pdo_get('mc_members', $condition, array('SUM(credit1)'));
        $total2 = pdo_get('mc_members', $condition, array('SUM(credit2)'));

        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/cash/member_list');
    }
    // 会员的积分余额操作
    if ($p == 'credit') {
        $uid = intval($_GPC['uid']);
        $type = $_GPC['type'] ? $_GPC['type'] : 'credit1';
        if (checksubmit('update')) {
            $id = intval($_GPC['id']);
            $uptype = $_GPC['uptype'];
            $credit = $_GPC['credit'];
            if ($uptype == 'credit2') {
                $data = array(
                    'credit2 +=' => $credit
                );
                $category = 2;
                $unit = '元';
                $content = "系统后台:操作会员(uid:" . $id . ")余额";
            } elseif ($uptype == 'credit1') {
                $data = array(
                    'credit1 +=' => $credit
                );
                $category = 1;
                $unit = '积分';
                $content = "系统后台:操作会员(uid:" . $id . ")积分";
            }
            if ($credit > 0) {
                $creditstatus = 1;
                $remark = "系统后台：添加" . abs($credit) . $unit .',备注:'.$_GPC['remark'];
            } elseif ($credit < 0) {
                $creditstatus = 2;
                $remark = "系统后台：减少" . abs($credit) . $unit.',备注:'.$_GPC['remark'];
            }
            if (pdo_update('mc_members', $data, array('uid' => $id))) {

                $creditdata = array(
                    'uid' => $id,
                    'uniacid' => $_W['uniacid'],
                    'realname' => $_W['username'],
                    'mobile' => $_W['username'],
                    'content' => $content,
                    'credit' => abs($credit),
                    'creditstatus' => $creditstatus,
                    'createtime' => TIMESTAMP,
                    'type' => 9,
                    'typeid' => $id,
                    'category' => $category,
                    'usename' => $_W['username']
                );
                pdo_insert('xcommunity_credit', $creditdata);
                $recorddata = array(
                    'uid' => $id,
                    'uniacid' => $_W['uniacid'],
                    'credittype' => $uptype,
                    'num' => $credit,
                    'operator' => $_W['uid'],
                    'module' => 'system',
                    'clerk_id' => $_W['uid'],
                    'store_id' => 0,
                    'clerk_type' => 2,
                    'createtime' => TIMESTAMP,
                    'remark' => $remark,
                    'real_uniacid' => $_W['uniacid']
                );
                pdo_insert('mc_credits_record', $recorddata);
            }
            itoast('修改成功', referer(), 'success');
            exit();
        }
        $member = pdo_get('mc_members', array('uid' => $uid), array('uid', 'credit1', 'credit2'));
        $condition['uniacid'] = $_W['uniacid'];
        $condition['uid'] = $uid;
        $condition['credittype'] = $type;
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition['createtime >='] = $starttime;
            $condition['createtime >='] = $endtime;
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_getslice('mc_credits_record', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/cash/member_credit');
    }
}
/**
 * 小区的积分变更明细
 */
if ($op == 'regionIntegral') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['cid'] = 1;
    $condition['category'] = 1;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_commission_log', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $membes = pdo_getall('mc_members', array('uniacid' => $_W['uniacid']), array('uid', 'realname'));
    $membes_ids = _array_column($membes, NULL, 'uid');
    $priceTotal = 0;
    foreach ($list as $k => $v) {
        $priceTotal += $v['price'];
        if ($v['type'] == 3) {
            $list[$k]['usename'] = $v['realname'];
        }else {
            $list[$k]['usename'] = $membes_ids[$v['uid']]['realname'];
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/region_integral');
}
/**
 * 小区的余额变更明细
 */
if ($op == 'regionCredit') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['cid'] = 1;
    $condition['category'] = 2;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_commission_log', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $membes = pdo_getall('mc_members', array('uniacid' => $_W['uniacid']), array('uid', 'realname'));
    $membes_ids = _array_column($membes, NULL, 'uid');
    $priceTotal = 0;
    foreach ($list as $k => $v) {
        $priceTotal += $v['price'];
        if ($v['type'] == 3) {
            $list[$k]['usename'] = $v['realname'];
        }else {
            $list[$k]['usename'] = $membes_ids[$v['uid']]['realname'];
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/region_credit');
}
/**
 * 小区的积分余额统计
 */
if ($op == 'regionData') {
    //修改小区的余额、积分
    if (checksubmit('update')) {
        $type = intval($_GPC['type']);
        $id = intval($_GPC['id']);
        $credit = $_GPC['credit'];
        if ($type == 6) {
            $data = array(
                'commission +=' => $credit
            );
            $content = "系统后台:操作小区账户余额";
            $category = 2;
        } elseif ($type == 5) {
            $data = array(
                'credit +=' => $credit
            );
            $content = "系统后台:操作小区账户积分";
            $category = 1;
        }
        if (pdo_update('xcommunity_region', $data, array('id' => $id))) {
            if ($credit > 0) {
                $creditstatus = 1;
            } elseif ($credit < 0) {
                $creditstatus = 2;
            }
            $d1 = array(
                'uniacid'    => $_W['uniacid'],
                'uid'        => $_W['uid'],
                'realname'   => $_W['username'],
                'type'       => 3,
                'regionid'   => $id,
                'createtime' => TIMESTAMP,
                'orderid'    => '',
                'good'       => '系统操作',
                'cid'        => 1,
                'price'      => $credit,
                'category' => $category,
                'creditstatus' => $creditstatus
            );
            pdo_insert('xcommunity_commission_log', $d1);
        }
        itoast('修改成功', referer(), 'success');
        exit();
    }
    $condition['uniacid'] = $_W['uniacid'];
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition['title like'] = "%{$keyword}%";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_region', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
    $integralTotal = 0;
    $creditTotal = 0;
    foreach ($list as $k => $v) {
        $integralTotal += $v['credit'];
        $creditTotal += $v['commission'];
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/region_data');
}
/**
 * 平台的余额变更明细
 */
if ($op == 'pcCredit') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['cid'] = 2;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_commission_log', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $membes = pdo_getall('mc_members', array('uniacid' => $_W['uniacid']), array('uid', 'realname'));
    $membes_ids = _array_column($membes, NULL, 'uid');
    $priceTotal = 0;
    foreach ($list as $k => $v) {
        $priceTotal += $v['price'];
        $list[$k]['usename'] = $membes_ids[$v['uid']]['realname'];
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cash/pc_credit');
}