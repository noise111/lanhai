<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区投诉信息
 */

global $_GPC, $_W;
$ops = array('add', 'delete', 'list', 'manage', 'verify', 'send', 'post', 'display', 'pmanage', 'sendlog');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
load()->model('mc');
/**
 * 投诉的列表
 */
if ($op == 'list') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_report', array('id' => $id));
            }
            util::permlog('', '批量删除建议信息');
            itoast('删除成功', referer(), 'success', ture);
        }
    }

    $regions = model_region::region_fetall();
    $categories = util::fetchall_category(3);
    //搜索
    $condition = ' t1.uniacid =:uniacid and t1.type=2 and t1.state=2';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition .= " AND t1.cid = :category";
        $params[':category'] = $_GPC['category'];
    }
    $status = intval($_GPC['status']);
    if (!empty($status)) {
        $condition .= " AND t1.status = :status";
        $params[':status'] = $status;
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
    }

    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    } else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t3.realname like :keyword or t3.mobile like :keyword or t2.address like :keyword)";
        $params[':keyword'] = $_GPC['keyword'];
    }
    if ($_GPC['export'] == 1 || checksubmit('plverity')) {
        $sql = "select t3.realname,t3.mobile,t5.title,t2.address,t4.name as cate,t1.*,t6.rank,t8.content as rank_content,t1.createtime as rtime from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('xcommunity_rank') . "t6 on t6.rankid = t1.id left join" . tablename('xcommunity_rank') . "t8 on t8.rankid = t1.id where $condition order by t1.id desc ";
        $xqlist = pdo_fetchall($sql, $params);

        if (checksubmit('plverity')) {
            foreach ($xqlist as $k => $v) {
                $enable = $v['enable'] == 1 ? 0 : 1;
                pdo_update('xcommunity_report', array('enable' => $enable), array('id' => $v['id']));
            }
            itoast('审核成功', referer(), 'success', ture);
        }
        if ($_GPC['export'] == 1) {

            foreach ($xqlist as $k => $v) {
                $log = pdo_fetch("select t7.dealing,t7.content,t7.createtime from" . tablename('xcommunity_report_log') . "t7 where t7.reportid=:id order by t7.createtime desc", array(':id' => $v['id']));
                $xqlist[$k]['dealing'] = $log['dealing'];
                $xqlist[$k]['report_content'] = $log['content'];
                $xqlist[$k]['gtime'] = $log['createtime'];
                if ($v['status'] == 1) {
                    $xqlist[$k]['xqstatus'] = '已处理';
                } elseif ($v['status'] == 2) {
                    $xqlist[$k]['xqstatus'] = '未处理';
                } elseif ($v['status'] == 3) {
                    $xqlist[$k]['xqstatus'] = '处理中';
                }
                $xqlist[$k]['ggtime'] = $log['createtime'] ? date('Y-m-d H:i', $log['createtime']) : '';
                $xqlist[$k]['rrtime'] = date('Y-m-d H:i', $v['rtime']);
                $xqlist[$k]['address'] = $v['title'] . $v['address'];
                $logs = pdo_getall('xcommunity_report_log', array('reportid' => $v['id']), array('content'));
                $log_content = '';
                foreach ($logs as $kk => $vv) {
                    $log_content .= $vv['content'] . '|';
                }
                $xqlist[$k]['log_content'] = $log_content;
                $sends = pdo_fetchall("select t1.*,t2.realname,t2.nickname,t2.mobile from" . tablename('xcommunity_report_send_log') . "t1 left join" . tablename('mc_members') . "t2 on t2.uid=t1.uid where t1.uniacid =:uniacid and t1.type=2 and t1.reportid=:id", array('uniacid' => $_W['uniacid'], ':id' => $v['id']));
                $send_log = '';
                foreach ($sends as $key => $val) {
                    $send_log .= $val['realname'] . '(' . date('Y-m-d H:i',$val['sendtime']) . ")|";
                }
                $xqlist[$k]['send_log'] = rtrim($send_log, '|');
            }

            model_execl::export($xqlist, array(
                "title" => "意见建议数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
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
                        'title' => '建议类型',
                        'field' => 'cate',
                        'width' => 12
                    ),
                    array(
                        'title' => '地址',
                        'field' => 'address',
                        'width' => 40
                    ),
                    array(
                        'title' => '投诉内容',
                        'field' => 'content',
                        'width' => 40
                    ),
                    array(
                        'title' => '评价',
                        'field' => 'rank_content',
                        'width' => 20
                    ),
                    array(
                        'title' => '处理情况',
                        'field' => 'xqstatus',
                        'width' => 25
                    ),
                    array(
                        'title' => '处理结果',
                        'field' => 'log_content',
                        'width' => 25
                    ),
                    array(
                        'title' => '处理人',
                        'field' => 'dealing',
                        'width' => 12
                    ),
                    array(
                        'title' => '时间',
                        'field' => 'rrtime',
                        'width' => 16
                    ),
                    array(
                        'title' => '接单时间',
                        'field' => 'ggtime',
                        'width' => 16
                    ),
                    array(
                        'title' => '推送信息',
                        'field' => 'send_log',
                        'width' => 50
                    ),
                )
            ));
        }

    }
    //显示报修记录
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select t3.realname,t3.mobile,t5.title,t2.address,t4.name as cate,t1.createtime,t1.status,t1.enable,t1.id,t6.rank,t1.state from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('xcommunity_rank') . "t6 on t6.rankid = t1.id where $condition order by t1.id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('xcommunity_rank') . "t6 on t6.rankid = t1.id where $condition order by t1.id desc ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/report/list');

}
/**
 * 投诉的处理
 */
if ($op == 'add') {
    //报修详情
    if (empty($id)) {
        itoast('非法操作', referer(), 'error', ture);
        exit();
    }
    $state = intval($_GPC['xx']);
    if ($state) {
        $sql = "select t1.images,t1.realname,t1.mobile,t1.categoryid,t5.title,t1.address,t4.name as cate,t1.createtime,t1.status,t1.enable,t1.id,t1.content,t1.price from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where t1.id=:id";
    } else {
        $sql = "select t3.realname,t3.mobile,t5.title,t2.address,t4.name as cate,t1.createtime,t1.status,t1.enable,t1.id,t1.content,t6.rank,t6.content as rank_content,t7.openid from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('xcommunity_rank') . "t6 on t6.rankid = t1.id left join" . tablename('mc_mapping_fans') . "t7 on t7.uid = t1.uid where t1.id=:id";
    }
    $item = pdo_fetch($sql, array(':id' => $id));
    if ($item['images']) {
        $images = explode(',', $item['images']);
    }
    if (empty($item)) {
        itoast('信息不存在，或已删除', referer(), 'error', ture);
        exit();
    }
    $imgs = pdo_fetchall("select * from" . tablename('xcommunity_report_images') . "as t1 left join" . tablename('xcommunity_images') . "as t2 on t1.thumbid =t2.id where t1.reportid=:reportid and t2.src !=''", array(':reportid' => $item['id']));

    $lsql = "select t1.*,t4.realname,t4.mobile as tmobile from" . tablename('xcommunity_report_log') . "t1 left join" . tablename('xcommunity_report') . "t2 on t1.reportid=t2.id left join" . tablename('xcommunity_users') . "t3 on t1.uid=t3.uid left join" . tablename('xcommunity_staff') . "t4 on t3.staffid=t4.id where t1.reportid=:reportid order by t1.createtime desc";
    $logs = pdo_fetchall($lsql, array(':reportid' => $item['id']));
    foreach ($logs as $k => $v) {
        $logs[$k]['thumbs'] = explode(',', $v['images']);
    }
    if ($_W['isajax']) {
        //获取报修表单提交的数据
        $thumbs = '';
        if (!empty($_GPC['thumbs'])) {
            $thumbs = implode(',', $_GPC['thumbs']);
        }
        $data = array(
            'reportid' => $_GPC['reportid'],
            'content' => $_GPC['content'],
            'createtime' => TIMESTAMP,
            'uid' => $_W['uid'],
            'images' => $thumbs,
            'remark' => $_GPC['remark']
        );
        if (empty($user)) {
            $data['dealing'] = $_GPC['dealing'];
            $data['mobile'] = $_GPC['mobile'];
        }
        if (pdo_insert('xcommunity_report_log', $data)) {
            pdo_query("update " . tablename('xcommunity_report') . "set status = :status where id=:id", array(':status' => intval($_GPC['status']), ':id' => intval($_GPC['reportid'])));
        }
        util::permlog('建议信息-处理', '内容:' . $item['content']);
        if (set('p53')) {
            $content = $_GPC['status'] == 3 ? '您的建议正在处理中' : '您的建议已处理';
            util::app_send($item['uid'], $content);
        }
        if (set('t9')) {
            if ($_GPC['status'] == 1) {
                //模板消息通知
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$item['id']}&op=detail&do=report&m=" . $this->module['name'];
                $content = array(
                    'first' => array(
                        'value' => '尊敬的业主，您的提交的建议，我们已经处理',
                    ),
                    'keyword1' => array(
                        'value' => $item['realname'],
                    ),
                    'keyword2' => array(
                        'value' => $item['cate'],
                    ),
                    'keyword3' => array(
                        'value' => $item['content'],
                    ),
                    'keyword4' => array(
                        'value' => $_GPC['content'],
                    ),
                    'keyword5' => array(
                        'value' => $_GPC['dealing'],
                    ),
                    'remark' => array(
                        'value' => '请到微信我的意见给我们评价，谢谢使用！',
                    ),
                );
            } elseif ($_GPC['status'] == 3) {
                //模板消息通知
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$item['id']}&op=detail&do=report&m=" . $this->module['name'];
                $content = array(
                    'first' => array(
                        'value' => '尊敬的业主，您的提交的建议，我们正在处理中',
                    ),
                    'keyword1' => array(
                        'value' => $item['realname'],
                    ),
                    'keyword2' => array(
                        'value' => $item['cate'],
                    ),
                    'keyword3' => array(
                        'value' => $item['content'],
                    ),
                    'keyword4' => array(
                        'value' => $_GPC['content'],
                    ),
                    'keyword5' => array(
                        'value' => $_GPC['dealing'],
                    ),
                    'remark' => array(
                        'value' => '请到微信我的意见给我们评价，谢谢使用！',
                    ),
                );
            }
            $tplid = set('t10');
            util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
            $dat = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $item['uid'],
                'sendtime' => TIMESTAMP,
                'reportid' => intval($_GPC['reportid']),
                'type' => 2,
                'grabtime' => $data['createtime']
            );
            pdo_insert('xcommunity_report_send_log', $dat);
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/core/report/add');
}
/**
 * 投诉的删除
 */
if ($op == 'delete') {
    if (empty($id)) {
        itoast('非法操作', referer(), 'error');
        exit();
    }
    $item = pdo_get('xcommunity_report', array('id' => $id), array());
    if (empty($item)) {
        itoast('信息不存在，或已删除', referer(), 'error', ture);
        exit();
    }
    if (pdo_delete("xcommunity_report", array('id' => $id))) {
        util::permlog('小区建议-删除', '删除信息内容' . $item['content']);
        itoast('删除成功！', referer(), 'success', ture);
    }
}
/**
 * 投诉的小区接收员
 */
if ($op == 'manage') {
    $operation = in_array($_GPC['operation'], array('list', 'add', 'del')) ? $_GPC['operation'] : 'list';
    $d = '';
    if ($user) {
        if ($user['type'] == 2) {
            $d = " and uid ={$_W['uid']}";
        } elseif ($user['type'] == 3) {
            $d = " and id={$user['pid']}";
        }
    }
    $properties = model_region::property_fetall($d);
    if ($operation == 'add') {
        //报修分类
        $categories = util::fetchall_category(3);
        $id = intval($_GPC['id']);
        $regionids = '[]';
        if ($id) {
            $sql = "select t1.*,t2.realname,t3.title,t3.pid,t2.id as staffid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid= t2.id left join" . tablename('xcommunity_department') . 't3 on t2.departmentid=t3.id left join' . tablename('xcommunity_property') . "t4 on t4.id = t3.pid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error', ture);
            }
            $regions = model_region::region_fetall();
            $regs = pdo_getall('xcommunity_notice_region', array('nid' => $id), array('regionid'));
//            $regionid = '';
//            foreach ($regs as $key => $val) {
//                $regionid .= $val['regionid'] . ',';
//            }
//            $regionids = ltrim(rtrim($regionid, ","), ',');
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
            $cids = pdo_getall('xcommunity_notice_category', array('nid' => $id), array('cid'));
            $cid = '';
            foreach ($cids as $key => $val) {
                $cid .= $val['cid'] . ',';
            }
            $cid = ltrim(rtrim($cid, ","), ',');
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $data = array(
                'uniacid' => $_W['uniacid'],
                'staffid' => $_GPC['staffid'],
                'enable' => 2,
                'type' => intval($_GPC['type']),
                'province' => $birth['province'],
                'city' => $birth['city'],
                'dist' => $birth['district'],
            );
            if ($id) {
                pdo_update('xcommunity_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_notice_region', array('nid' => $id));
                pdo_delete('xcommunity_notice_category', array('nid' => $id));
                util::permlog('', '建议接收员信息修改,信息ID:' . $id);
            } else {
                $data['uid'] = $_W['uid'];
                $staf = pdo_get('xcommunity_notice', array('staffid' => $data['staffid'], 'enable' => 2), array('id'));
                if ($staf) {
                    echo json_encode(array('content' => '接收员已添加'));
                    exit();
                }
                if (pdo_insert('xcommunity_notice', $data)) {
                    $id = pdo_insertid();
                    util::permlog('', '建议接收员信息添加,信息ID:' . $id);
                }
            }
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'nid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_notice_region', $dat);
            }
            foreach ($_GPC['cid'] as $key => $value) {
                $d = array(
                    'nid' => $id,
                    'cid' => $value,
                );
                pdo_insert('xcommunity_notice_category', $d);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/report/manage_add');
    } elseif ($operation == 'list') {
        $condition = " t1.uniacid='{$_W['uniacid']}' AND t1.enable = 2";
        if ($user['type'] == 2 || $user['type'] == 3) {
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }

        $sql = "select t1.*,t2.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where $condition";
        $list = pdo_fetchall($sql);

        include $this->template('web/core/report/manage_list');
    } elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_notice', array('id' => $id));
            if ($r) {
                util::permlog('', '报修接收员信息删除,信息ID:' . $id);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }

        }
    }
}
/**
 * 投诉的审核
 */
if ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable'))) {
        $data = ($data == 0 ? '1' : '0');
        pdo_update("xcommunity_report", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
/**
 * 投诉的推送
 */
if ($op == 'send') {
    $id = intval($_GPC['id']);

    $report = pdo_fetch("select t1.*,t2.reportnum from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.cid=t2.id where t1.id=:id", array(":id" => $id));
    $num = pdo_getcolumn('xcommunity_report_num', array('uid' => $report['uid'], 'cid' => $report['cid']), 'num');
    if ($num > $report['reportnum'] && $report['reportnum'] > 0) {
        $sql = "select distinct t5.openid,t5.openid,t5.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_notice_region') . "t2 on t1.id = t2.nid left join" . tablename('xcommunity_notice_category') . "t3 on t1.id = t3.nid left join" . tablename('xcommunity_staff') . "t5 on t5.id = t1.staffid where t1.uniacid=:uniacid and t3.cid=:cid and t2.regionid=:regionid and t1.enable=7 and t1.minhour <= {$num} and t1.maxhour >= {$num}";
    } else {
        $sql = "select distinct t5.openid,t5.openid,t5.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_notice_region') . "t2 on t1.id = t2.nid left join" . tablename('xcommunity_notice_category') . "t3 on t1.id = t3.nid left join" . tablename('xcommunity_staff') . "t5 on t5.id = t1.staffid where t1.uniacid=:uniacid and t3.cid=:cid and t2.regionid=:regionid and t1.enable=2";
    }
    $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':regionid' => $report['regionid'], ':cid' => $report['cid']));
    foreach ($list as $k => $v) {
        if (!$v['openid'])
            unset($list[$k]);
    }
    if (checksubmit('submit')) {
        $tsql = "select t1.createtime,t1.content,t4.realname,t4.mobile,t5.title,t2.address from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join " . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where t1.id=:id";
        $report = pdo_fetch($tsql, array(':id' => intval($_GPC['reportid'])));

        $content = array(
            'first' => array(
                'value' => '新建议通知',
            ),
            'keyword1' => array(
                'value' => $report['realname'],
            ),
            'keyword2' => array(
                'value' => $report['mobile'],
            ),
            'keyword3' => array(
                'value' => $report['title'] . $report['address'],
            ),
            'keyword4' => array(
                'value' => $report['content'],
            ),
            'keyword5' => array(
                'value' => date('Y-m-d H:i', $report['createtime']),
            ),
            'remark' => array(
                'value' => '请尽快联系客户。',
            ),
        );
        $reportid = intval($_GPC['reportid']);
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$reportid}&do=report&m=" . $this->module['name'];
        if (set('t5')) {
            $ret = util::sendTplNotice(trim($_GPC['openid']), set('t6'), $content, $url, '');
            $uid = pdo_getcolumn('mc_mapping_fans', array('openid' => $_GPC['openid']), 'uid');
            $dat = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'sendtime' => TIMESTAMP,
                'reportid' => $reportid,
                'type' => 2,
            );
            pdo_insert('xcommunity_report_send_log', $dat);
            $result = json_decode($ret['content'], true);
            $error_code = $result['errcode'];
            if (empty($error_code)) {
                itoast('推送成功', referer(), 'success', ture);
                exit();
            }
        }
        util::permlog('', '建议信息推送,信息ID:' . $reportid);
    }
    include $this->template('web/core/report/send');
}
/**
 * 手工投诉的添加
 */
if ($op == 'post') {
    $regions = model_region::region_fetall();
//报修分类
    $categories = util::fetchall_category(3);
    if ($_W['isajax']) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'cid' => intval($_GPC['cid']),
            'realname' => trim($_GPC['realname']),
            'mobile' => trim($_GPC['mobile']),
            'address' => trim($_GPC['address']),
            'content' => trim($_GPC['content']),
//            'images' => trim($_GPC['images']),
            'createtime' => TIMESTAMP,
            'state' => 1,
            'type' => 2,
            'status' => 2
        );
        if (@is_array($_GPC['images'])) {
            $data['images'] = implode(',', $_GPC['images']);
        }
        if (pdo_insert('xcommunity_report', $data)) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
    include $this->template('web/core/report/post');
}
/**
 * 手工投诉的列表
 */
if ($op == 'display') {
    //显示报修记录
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_report', array('id' => $id));
            }
            util::permlog('', '批量删除报修信息');
            itoast('删除成功', referer(), 'success', ture);
        }
    }

    $regions = model_region::region_fetall();
    $categories = util::fetchall_category(3);
    //搜索
    $condition = ' t1.uniacid =:uniacid and t1.type=2 and t1.state = 1';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition .= " AND t1.cid = :category";
        $params[':category'] = $_GPC['category'];
    }
    $status = intval($_GPC['status']);
    if (!empty($status)) {
        $condition .= " AND t1.status = :status";
        $params[':status'] = $status;
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
    }

    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    } else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t1.realname like :keyword or t1.mobile like :keyword or t1.address like :keyword)";
        $params[':keyword'] = $_GPC['keyword'];
    }
    if ($_GPC['export'] == 1) {

        $sql = "select t8.title,t1.*,t6.name as category,t7.content as rank_content,t1.createtime as rtime from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_category') . "t6 on t1.cid=t6.id left join" . tablename('xcommunity_rank') . "t7 on t7.rankid = t1.id left join" . tablename('xcommunity_region') . "t8 on t8.id = t1.regionid where $condition group by t1.id order by t1.id desc ";
        $xqlist = pdo_fetchall($sql, $params);
        if ($_GPC['export'] == 1) {

            foreach ($xqlist as $k => $v) {
                $log = pdo_fetch("select t7.dealing,t7.content,t7.createtime from" . tablename('xcommunity_report_log') . "t7 where t7.reportid=:id order by t7.createtime desc", array(':id' => $v['id']));
                $xqlist[$k]['dealing'] = $log['dealing'];
                $xqlist[$k]['report_content'] = $log['content'];
                $xqlist[$k]['gtime'] = $log['createtime'];
                if ($v['status'] == 1) {
                    $xqlist[$k]['xqstatus'] = '已处理';
                } elseif ($v['status'] == 2) {
                    $xqlist[$k]['xqstatus'] = '未处理';
                } elseif ($v['status'] == 3) {
                    $xqlist[$k]['xqstatus'] = '处理中';
                }
                $xqlist[$k]['ggtime'] = $log['createtime'] ? date('Y-m-d H:i', $log['createtime']) : '';
                $xqlist[$k]['rrtime'] = date('Y-m-d H:i', $v['rtime']);
                $xqlist[$k]['address'] = $v['title'] . $v['address'];
                $xqlist[$k]['category'] = $v['categoryid'] == 1 ? '质保内' : '质保外';
                $logs = pdo_getall('xcommunity_report_log', array('reportid' => $v['id']), array('content'));
                $log_content = '';
                foreach ($logs as $kk => $vv) {
                    $log_content .= $vv['content'] . '|';
                }
                $xqlist[$k]['log_content'] = $log_content;
            }
            model_execl::export($xqlist, array(
                "title" => "意见报修数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
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
                        'title' => '地址',
                        'field' => 'address',
                        'width' => 18
                    ),
                    array(
                        'title' => '报修内容',
                        'field' => 'content',
                        'width' => 40
                    ),
                    array(
                        'title' => '质保情况',
                        'field' => 'category',
                        'width' => 40
                    ),
                    array(
                        'title' => '评价',
                        'field' => 'rank_content',
                        'width' => 20
                    ),
                    array(
                        'title' => '处理情况',
                        'field' => 'xqstatus',
                        'width' => 25
                    ),
                    array(
                        'title' => '处理结果',
                        'field' => 'log_content',
                        'width' => 25
                    ),
                    array(
                        'title' => '处理人',
                        'field' => 'dealing',
                        'width' => 12
                    ),
                    array(
                        'title' => '时间',
                        'field' => 'rrtime',
                        'width' => 16
                    ),
                    array(
                        'title' => '接单时间',
                        'field' => 'ggtime',
                        'width' => 16
                    ),
                )
            ));
        }

    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select t5.title,t4.name as cate,t1.createtime,t1.status,t1.id,t1.realname,t1.address,t1.mobile,t1.state from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where $condition order by t1.id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where $condition order by t1.id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/report/display');
}
/**
 * 投诉的分管接收员
 */
if ($op == 'pmanage') {
    $operation = in_array($_GPC['operation'], array('list', 'add', 'del')) ? $_GPC['operation'] : 'list';
    $d = '';
    if ($user) {
        if ($user['type'] == 2) {
            $d = " and uid ={$_W['uid']}";
        } elseif ($user['type'] == 3) {
            $d = " and id={$user['pid']}";
        }
    }
    $properties = model_region::property_fetall($d);
    if ($operation == 'add') {
        //报修分类
        $categories = util::fetchall_category(3);
        $regionids = '[]';
        if ($id) {
            $sql = "select t1.*,t2.realname,t3.title,t3.pid,t2.id as staffid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid= t2.id left join" . tablename('xcommunity_department') . 't3 on t2.departmentid=t3.id left join' . tablename('xcommunity_property') . "t4 on t4.id = t3.pid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error', ture);
            }
            $regions = model_region::region_fetall();
            $regs = pdo_getall('xcommunity_notice_region', array('nid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
            $cids = pdo_getall('xcommunity_notice_category', array('nid' => $id), array('cid'));
            $cid = '';
            foreach ($cids as $key => $val) {
                $cid .= $val['cid'] . ',';
            }
            $cid = ltrim(rtrim($cid, ","), ',');
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $data = array(
                'uniacid' => $_W['uniacid'],
                'enable' => 7,
                'type' => intval($_GPC['type']),
                'province' => $birth['province'],
                'city' => $birth['city'],
                'dist' => $birth['district'],
                'staffid' => intval($_GPC['staffid']),
                'minhour' => intval($_GPC['minhour']),
                'maxhour' => intval($_GPC['maxhour']),
            );

            if ($id) {
                pdo_update('xcommunity_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_notice_region', array('nid' => $id));
                pdo_delete('xcommunity_notice_category', array('nid' => $id));
                util::permlog('', '投诉分管接收员信息修改,信息ID:' . $id);
            } else {
                $data['uid'] = $_W['uid'];
                $staf = pdo_get('xcommunity_notice', array('staffid' => $data['staffid'], 'enable' => 7), array('id'));
                if ($staf) {
                    echo json_encode(array('content' => '接收员已添加'));
                    exit();
                }
                if (pdo_insert('xcommunity_notice', $data)) {
                    $id = pdo_insertid();
                    util::permlog('', '投诉分管接收员信息添加,信息ID:' . $id);
                }
            }
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'nid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_notice_region', $dat);
            }
            foreach ($_GPC['cid'] as $key => $value) {
                $d = array(
                    'nid' => $id,
                    'cid' => $value,
                );
                pdo_insert('xcommunity_notice_category', $d);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/report/pmanage/add');
    } elseif ($operation == 'list') {
        $condition = " t1.uniacid='{$_W['uniacid']}' AND t1.enable = 7";
        if ($user['type'] == 2 || $user['type'] == 3) {
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }
        $sql = "select t1.*,t2.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where $condition";
        $list = pdo_fetchall($sql);
        include $this->template('web/core/report/pmanage/list');
    } elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_notice', array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }

        }
    }
}
/**
 * 投诉的推送日志
 */
if ($op == 'sendlog') {
    $id = intval($_GPC['id']);
    $condition = ' t1.uniacid =:uniacid and t1.type=2 and t1.reportid=:id';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':id'] = $id;
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select t1.*,t2.realname,t2.nickname,t2.mobile from" . tablename('xcommunity_report_send_log') . "t1 left join" . tablename('mc_members') . "t2 on t2.uid=t1.uid where $condition order by t1.sendtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_report_send_log') . "t1 left join" . tablename('mc_members') . "t2 on t2.uid=t1.uid where $condition";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/report/sendlog');
}