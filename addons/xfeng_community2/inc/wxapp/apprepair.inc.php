<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午1:19
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'grab', 'sendLog', 'order', 'sendList', 'send');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 报修列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition['uniacid'] = $_SESSION['appuniacid'];
    $condition['type'] = 1;
    if ($_SESSION['apptype'] == 3) {
        $condition['regionid'] = explode(',',$_SESSION['appregionids']);
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
    if ($type == 2) {
        $condition['grabuid'] = $_SESSION['appuid'];
    }
    $status = intval($_GPC['status']);
    if ($status) {
        $condition['status'] = $status;
    }
    $list = pdo_getslice('xcommunity_report',$condition,array($pindex, $psize),$total, '', '', array('createtime desc'));
    $list_cids = _array_column($list, 'cid');
    $category = pdo_getall('xcommunity_category', array('id' => $list_cids), array('name'),'id');
    $row = array();
    if ($list) {
        foreach ($list as $key => $val) {
            $images = explode(',', $val['images']);
            $row[] = array(
                'src'        => $images[0] ? tomedia($images[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'title'      => $category[$val['cid']]['name'] ? $category[$val['cid']]['name'] : $category[0]['name'],
                'name'       => $category[$val['cid']]['name'] ? $category[$val['cid']]['name'] : $category[0]['name'],
                'status'     => $val['status'],
                'createtime' => date('Y-m-d H:i', $val['createtime']),
                'id'         => $val['id'],
                'desc'       => date('Y-m-d H:i', $val['createtime']),
                'url'        => $this->createMobileUrl('xqsys', array('op' => 'repair', 'p' => 'detail', 'id' => $val['id'])),
            );
        }
    }

    $data = array();
    $data['list'] = $row;
    util::send_result($data);
}
/**
 * 报修详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 36;
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_fetch("SELECT t1.uid,t1.status,t1.createtime,t1.content,t2.id as rid,t5.realname,t5.mobile,t1.images,t6.dealing,t6.mobile as log_mobile,t6.content as log_content,t6.images as log_images,t6.id as logid,t1.realname as trealname,t1.mobile as tmobile,t1.price,t1.regionid,t1.status,t3.address,t1.code FROM" . tablename('xcommunity_report') . "as t1 left join" . tablename('xcommunity_rank') . "t2 on t1.id = t2.rankid left join " . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_members') . "t5 on t5.uid = t1.uid left join" . tablename('xcommunity_report_log') . "t6 on t6.reportid=t1.id WHERE t1.id=:id order by t6.id desc ", array(':id' => $id));
    $logs = pdo_getall('xcommunity_report_log', array('reportid' => $id), array(), 'id');
    $logid = pdo_getcolumn('xcommunity_report_log', array('reportid' => $id, 'uid' => $_W['member']['uid']), 'id');
    $img = array();
    $imgs = '';
    foreach ($logs as $k => $log) {
        $img = explode(',', $log['images']);
        $logs[$k]['img'] = $img;
        foreach ($img as $key => $val) {
            $logimg[] = array(
                'src'  => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $logs[$k]['logimg'] = $logimg;
        $logs[$k]['logtime'] = date('Y-m-d H:i', $log['createtime']);
        $logs[$k]['price'] = $log['price'] == 'null' || $log['price'] == '' ? '0.00' : $log['price'];
        if ($log['images']) {
            $imgs .= $log['images'] . ',';
        }
        $logs[$k]['record'] = array(
            0 => array(
                'label' => '处理人',
                'value' => $log['dealing']
            ),
            1 => array(
                'label' => '更新时间',
                'value' => date('Y-m-d H:i', $log['createtime'])
            ),
            2 => array(
                'label' => '处理内容',
                'value' => $log['content']
            ),
        );
    }
    $data = array();
    $images = array();
    if ($item['images']) {
        $image = explode(',', $item['images']);
        foreach ($image as $key => $val) {
            $images[] = array(
                'src'  => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $data['images'] = $images;
    }
    $logimages = array();
    if ($imgs) {
        $imgs = xtrim($imgs);
        $imgss = explode(',', $imgs);
        foreach ($imgss as $key => $val) {
            $logimages[] = tomedia($val);
        }
        $data['logimages'] = $logimages;
    }
//    if ($item['log_images']) {
//        $image = explode(',', $item['log_images']);
//        foreach ($image as $key => $val) {
//            $logimages[] = tomedia($val);
//        }
//        $data['logimages'] = $logimages;
//    }
    $data['realname'] = $item['realname'] ? $item['realname'] : $item['trealname'];
    $data['mobile'] = $item['mobile'] ? $item['mobile'] : $item['tmobile'];
    $data['content'] = $item['content'];
    $data['createtime'] = date('Y-m-d H:i', $item['createtime']);
    $data['clstatus'] = $item['status'] == 1 && $item['uid'] == $_W['member']['uid'] ? 1 : 0;
    $data['dealing'] = $item['dealing'];
    $data['logmobile'] = $item['log_mobile'];
    $data['logcontent'] = $item['log_content'];
    $data['price'] = $item['price'];
    $data['logs'] = $logs;
    $data['rid'] = $item['rid'] ? 1 : 2;
    $data['status'] = $item['status'];
    $data['address'] = $item['address'];
    if (!empty($logs)) {
        $data['code'] = set('p75') || set('x50', $item['regionid']) ? 2 : 1;
        if ($logid) {
            $data['code'] = 1;
        }
    } else {
        $data['code'] = set('p75') || set('x50', $item['regionid']) ? 3 : 1;
    }
    $data['_status'] = set('p96') ? 1 : 0;
    $data['title'] = '报修详情';
    $data['apptype'] = $_SESSION['apptype'];
    $data['offcodeStatus'] = set('p162') ? set('p162') : 0;
    $data['offcode'] = $item['code'];
    util::send_result($data);
}
/**
 * 报修处理
 */
if ($op == 'grab') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 36;
    $status = intval($_GPC['status']);
    $content = trim($_GPC['content']);
    $pics = $_GPC['pics'];
    $pic = '';
    if ($pics) {
        $pics = explode(',', $pics);
        if (!empty($pics)) {
            foreach ($pics as $k => $v) {
                $pic .= $v . ',';//修改为H5上传图片
            }
        }
        $pic = ltrim(rtrim($pic, ','), ',');
    }
    $data = array();
    $sql = "select t1.*,t2.realname,t2.mobile,t3.address,t4.openid,t5.title from" . tablename('xcommunity_report') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_mapping_fans') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where t1.id=:id";
    $item = pdo_fetch($sql, array(':id' => $id));
    $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile'));
    $realname = $_W['member']['realname'] ? $_W['member']['realname'] : $staff['realname'];
    $mobile = $_W['member']['mobile'] ? $_W['member']['mobile'] : $staff['mobile'];
    if (empty($item)) {
        $data['content'] = '信息不存在';
    }
//    $offcodeStatus = set('p162', '', $item['uniacid']) ? set('p162', '', $item['uniacid']) : 0;
    $data = array(
        'reportid'   => $id,
        'content'    => $content,
        'uid'        => $_W['member']['uid'],
        'createtime' => TIMESTAMP,
        'dealing'    => $realname,
        'mobile'     => $mobile,
        'images'     => $pic,
        'price'      => $_GPC['price'],
        'status'     => $status,
        'cause'      => trim($_GPC['cause']),
        'measure'    => trim($_GPC['measure']),
        'rank'       => trim($_GPC['rank'])
    );
    if (pdo_insert('xcommunity_report_log', $data)) {
        if (pdo_update('xcommunity_report', array('status' => $status, 'price' => $_GPC['price']), array('id' => $id))) {
            if (set('t7')) {
                if ($status == 1) {
                    $content = array(
                        'first'    => array(
                            'value' => '尊敬的业主，您的报修已经完成',
                        ),
                        'keyword1' => array(
                            'value' => $item['title'] . $item['address'],
                        ),
                        'keyword2' => array(
                            'value' => $item['content'],
                        ),
                        'keyword3' => array(
                            'value' => $data['dealing'],
                        ),
                        'keyword4' => array(
                            'value' => date('Y-m-d H:i', TIMESTAMP),
                        ),
                        'remark'   => array(
                            'value' => '请到微信我的报修给我们评价，谢谢使用！',
                        ),
                    );
                } elseif ($status == 3) {
                    $content = array(
                        'first'    => array(
                            'value' => '尊敬的业主，您的报修正在处理中',
                        ),
                        'keyword1' => array(
                            'value' => $item['title'] . $item['address'],
                        ),
                        'keyword2' => array(
                            'value' => $item['content'],
                        ),
                        'keyword3' => array(
                            'value' => $data['dealing'],
                        ),
                        'keyword4' => array(
                            'value' => date('Y-m-d H:i', TIMESTAMP),
                        ),
                        'remark'   => array(
                            'value' => '请到微信我的报修给我们评价，谢谢使用！',
                        ),
                    );
                }
                $tplid = set('t8');
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$data['reportid']}&op=detail&do=repair&m=" . $this->module['name'];
                $ret = util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                $dat = array(
                    'uniacid'  => $item['uniacid'],
                    'uid'      => $item['uid'],
                    'sendtime' => TIMESTAMP,
                    'reportid' => $id,
                    'type'     => 1,
                    'grabtime' => $data['createtime']
                );
                pdo_insert('xcommunity_report_send_log', $dat);
            }
            if (set('t33') && $_GPC['price']) {
                //判断订单是否已生成
                $order = pdo_get('xcommunity_report_order', array('reportid' => $data['reportid']));
                if (!$order) {
                    //生成订单
                    $data2 = array(
                        'uniacid'    => $_SESSION['appuniacid'],
                        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
                        'price'      => floatval($_GPC['price']),
                        'createtime' => TIMESTAMP,
                        'status'     => 0,
                        'type'       => 'report',
                        'addressid'  => $item['addressid'],
                        'uid'        => $item['uid'],
                        'regionid'   => $item['regionid']
                    );
                    if (pdo_insert('xcommunity_order', $data2)) {
                        $orderid = pdo_insertid();
                        $d = array(
                            'reportid' => $data['reportid'],
                            'orderid'  => $orderid,
                        );
                        pdo_insert('xcommunity_report_order', $d);
                    }
                } else {
                    $orderid = $order['orderid'];
                }
                if ($status == 1) {
                    $content = array(
                        'first'    => array(
                            'value' => '尊敬的业主，您本次的维修费已出',
                        ),
                        'keyword1' => array(
                            'value' => $item['realname'],
                        ),
                        'keyword2' => array(
                            'value' => $item['mobile'],
                        ),
                        'keyword3' => array(
                            'value' => $_GPC['price'],
                        ),
                        'remark'   => array(
                            'value' => '请点击进行支付！',
                        ),
                    );
                    $tplid = set('t34');
                    $url = $_W['siteroot'] . "app/index.php?i={$_SESSION['appuniacid']}&c=entry&orderid={$orderid}&type=9&do=paycenter&m=" . $this->module['name'];
                    $ret = util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                    $dat = array(
                        'uniacid'  => $item['uniacid'],
                        'uid'      => $item['uid'],
                        'sendtime' => TIMESTAMP,
                        'reportid' => $id,
                        'type'     => 1,
                        'grabtime' => $data['createtime']
                    );
                    pdo_insert('xcommunity_report_send_log', $dat);
                }
            }
        }
        $data['content'] = '处理成功';
    }
    util::send_result($data);
}
/**
 * 我的派单记录
 */
if ($op == 'sendLog') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $uniacid = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 1;
    $condition['uniacid'] = $uniacid;
    $appuid = $_SESSION['appuid'] ? $_SESSION['appuid'] : 9;
    $staffid = pdo_getcolumn('xcommunity_users', array('uid' => $appuid), 'staffid');
    $openid = pdo_getcolumn('xcommunity_staff', array('id' => $staffid), 'openid');
    $member = pdo_get('mc_mapping_fans', array('openid' => $openid), array('uid'));
    $condition['uid'] = $member['uid'];
    $condition['type'] = 1;
    $logs = pdo_getslice('xcommunity_report_send_log', $condition, array($pindex, $psize), $total, '', '', array('sendtime desc'));
    $members = pdo_getall('mc_members', array('uniacid' => $uniacid), array('uid', 'realname', 'nickname', 'mobile'), 'uid');
    $list = array();
    foreach ($logs as $k => $v) {
        $list[] = array(
            'id'       => $v['id'],
            'sendtime' => date('Y-m-d H:i', $v['sendtime']),
            'grabtime' => $v['grabtime'] ? date('Y-m-d H:i', $v['grabtime']) : 0,
            'realname' => $members[$v['uid']]['realname'],
            'nickname' => $members[$v['uid']]['nickname'],
            'mobile'   => $members[$v['uid']]['mobile'],
        );
    }
    util::send_result($list);
}
/**
 * 维修费用
 */
if ($op == 'order') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $uniacid = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 1;
    $condition['uniacid'] = $uniacid;
    $reportOrders = pdo_getslice('xcommunity_report_order', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $reportOrders_oids = _array_column($reportOrders, 'orderid');
    $orders = pdo_getall('xcommunity_order', array('id' => $reportOrders_oids), array('id', 'ordersn', 'price', 'status', 'createtime', 'addressid', 'regionid', 'uid'), 'id');
    $orders_regionids = _array_column($orders, 'regionid');
    $orders_uids = _array_column($orders, 'uid');
    $orders_addrids = _array_column($orders, 'addressid');
    $members = pdo_getall('mc_members', array('uniacid' => $uniacid, 'uid' => $orders_uids), array('uid', 'realname', 'nickname', 'mobile'), 'uid');
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $uniacid, 'id' => $orders_regionids), array('id', 'title'), 'id');
    $rooms = pdo_getall('xcommunity_member_room', array('uniacid' => $uniacid, 'id' => $orders_addrids), array('id', 'address'), 'id');
    $list = array();
    foreach ($reportOrders as $k => $v) {
        $list[] = array(
            'orersn'     => $orders[$v['orderid']]['ordersn'],
            'price'      => $orders[$v['orderid']]['price'],
            'status'     => $orders[$v['orderid']]['status'],
            'createtime' => date('Y-m-d H:i', $orders[$v['orderid']]['createtime']),
            'realname'   => $members[$orders[$v['orderid']]['uid']]['realname'],
            'mobile'     => $members[$orders[$v['orderid']]['uid']]['mobile'],
            'address'    => $regions[$orders[$v['orderid']]['regionid']]['title'] . '-' . $rooms[$orders[$v['orderid']]['addressid']]['address']
        );
    }
    util::send_result($list);
}
/**
 * 获取未处理报修的接收员
 */
if ($op == 'sendList') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, 'id参数错误');
    }
    $report = pdo_get('xcommunity_report', array('id' => $id), array());
    if (empty($report)) {
        util::send_error(-1, '信息不存在或已经删除');
    }
    $category = pdo_get('xcommunity_category', array('id' => $report['cid']), array('finishtime'));
    $condition = array();
    $condition['uniacid'] = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 1;
    if ($category['finishtime'] && ($report['createtime'] + $category['finishtime'] * 3600) < TIMESTAMP) {
        $condition['enable'] = 6;
        $ctime = (TIMESTAMP - $report['createtime']) / 3600;
        $condition['minhour <='] = $ctime;
        $condition['maxhour >='] = $ctime;
    } else {
        $condition['enable'] = 1;
    }
    // 该小区下的接收员id
    $noticeRegions = pdo_getall('xcommunity_notice_region', array('regionid' => $report['regionid']), array('nid'));
    $noticeRegions_nids = _array_column($noticeRegions, 'nid');
    // 该分类下的接收员id
    $noticeCates = pdo_getall('xcommunity_notice_category', array('cid' => $report['cid']), array('nid'));
    $noticeCates_nids = _array_column($noticeCates, 'nid');
    $noticeids = array_intersect($noticeRegions_nids, $noticeCates_nids);
    $notice = pdo_getall('xcommunity_notice', $condition, array('staffid'));
    $notice_staffids = _array_column($notice, 'staffid');
    $staff = pdo_getall('xcommunity_staff', array('id' => $notice_staffids), array('openid', 'realname'));
    $list = array();
    if ($staff) {
        foreach ($staff as $k => $v) {
            if (!empty($v['openid'])) {
                $list[] = array(
                    'openid'   => $v['openid'],
                    'realname' => $v['realname']
                );
            }
        }
    }
    util::send_result($list);
}
/**
 * 报修的推送
 */
if ($op == 'send') {
    $id = intval($_GPC['id']);
    $openid = trim($_GPC['openid']);
    $uniacid = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 1;
    if (empty($id)) {
        util::send_error(-1, 'id参数错误');
    }
    // 报修详情
    $report = pdo_get('xcommunity_report', array('id' => $id), array());
    if (empty($report)) {
        util::send_error(-1, '信息不存在或已经删除');
    }
    // 报修地址
    $address = pdo_getcolumn('xcommunity_member_room', array('id' => $report['addressid']), 'address');
    // 报修小区
    $title = pdo_getcolumn('xcommunity_region', array('id' => $report['regionid']), 'title');
    // 报修人信息
    $member = pdo_get('mc_members', array('uid' => $report['uid']), array('realname', 'mobile'));
    $content = array(
        'first' => array(
            'value' => '新报修通知',
        ),
        'keyword1' => array(
            'value' => $member['realname'] ? $member['realname'] : $report['realname'],
        ),
        'keyword2' => array(
            'value' => $member['mobile'] ? $member['mobile'] : $report['mobile'],
        ),
        'keyword3' => array(
            'value' => $title . $address,
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
    $url = $_W['siteroot'] . "app/index.php?i={$uniacid}&c=entry&op=grab&id={$id}&do=repair&m=" . $this->module['name'];
    if (set('t3', '', $uniacid)) {
        $ret = util::sendTplNotice($openid, set('t4', '', $uniacid), $content, $url, '');
        $uid = pdo_getcolumn('mc_mapping_fans', array('openid' => $openid), 'uid');
        $dat = array(
            'uniacid' => $uniacid,
            'uid' => $uid,
            'sendtime' => TIMESTAMP,
            'reportid' => $id,
            'type' => 1,
        );
        pdo_insert('xcommunity_report_send_log', $dat);
        util::permlog('', '报修信息推送,推送内容:' . $report['content']);
    }
    util::send_result();
}