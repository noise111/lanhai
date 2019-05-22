<?php
/**
 * Created by myapp.
 * User: mac
 * Time: 2017/12/4 下午1:09
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'rank', 'my', 'grab', 'verity');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '非法操作');
}
/**
 * 业主报修列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t1.enable =0 and t1.type=1 ";
    $parmas[':uniacid'] = $_W['uniacid'];
    if (set('x10', $regionid) || set('p30')) {
        $condition .= " AND t1.regionid=:regionid";
        $parmas[':regionid'] = $_SESSION['community']['regionid'];
    }
    else {
        $condition .= " and t1.uid=:uid and t1.regionid=:regionid";
        $parmas[':uid'] = $_W['member']['uid'];
        $parmas[':regionid'] = $_SESSION['community']['regionid'];
    }
    $sql = "select t1.createtime,t1.status,t2.name,t1.id,t1.images,t1.regionid from " . tablename("xcommunity_report") . "t1 left join " . tablename('xcommunity_category') . "t2 on t1.cid=t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $parmas);
    $list = array();
    if ($row) {
        foreach ($row as $key => $val) {
            $images = explode(',', $val['images']);
            $list[] = array(
                'src'        => $images[0] ? tomedia($images[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'regionid'   => $val['regionid'],
                'title'      => $val['name'],
                'name'       => $val['name'],
                'status'     => $val['status'],
                'createtime' => date('Y-m-d H:i', $val['createtime']),
                'id'         => $val['id'],
                'desc'       => date('Y-m-d H:i', $val['createtime']),
                'url'        => $this->createMobileUrl('repair', array('op' => 'detail', 'id' => $val['id'])),
            );
        }
    }
//    foreach ($list as $key => $val) {
//        $list[$key]['createtime'] = date('Y-m-d H:i', $val['createtime']);
//    }
//    if ($list) {
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
//    }
//    else {
//        util::send_error(-1, '');
//    }
}
/**
 * 业主报修详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 36;
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_fetch("SELECT t1.uid,t1.status,t1.createtime,t1.content,t2.id as rid,t5.realname,t5.mobile,t1.images,t6.dealing,t6.mobile as log_mobile,t6.content as log_content,t6.images as log_images,t6.id as logid,t1.realname as trealname,t1.mobile as tmobile,t1.price,t1.regionid,t1.status,t3.address,t6.createtime as logtime,t1.address as taddress,t6.cause,t6.measure,t4.finishtime,t1.code FROM" . tablename('xcommunity_report') . "as t1 left join" . tablename('xcommunity_rank') . "t2 on t1.id = t2.rankid left join " . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_members') . "t5 on t5.uid = t1.uid left join" . tablename('xcommunity_report_log') . "t6 on t6.reportid=t1.id left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid WHERE t1.id=:id order by t6.id desc ", array(':id' => $id));
    $logs = pdo_getall('xcommunity_report_log', array('reportid' => $id), array(), 'id');
    $logid = pdo_getcolumn('xcommunity_report_log', array('reportid' => $id, 'uid' => $_W['member']['uid']), 'id');
    $img = array();
    $imgs = '';
    foreach ($logs as $k => $log) {
        $img = explode(',', $log['images']);
        foreach ($img as $key => $val) {
            $logimg[] = array(
                'src'  => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $logs[$k]['logimg'] = $logimg;
        $logs[$k]['img'] = $img;
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
            $logimages[] = array(
                'src'  => tomedia($val),
                'msrc' => tomedia($val),
            );
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
    $data['logtime'] = date('Y-m-d H:i', $item['logtime']);
    $data['logmobile'] = $item['log_mobile'];
    $data['logcontent'] = $item['log_content'];
    $data['price'] = $item['price'];
    $data['logs'] = $logs;
    $data['rid'] = $item['rid'] ? 1 : 2;
    $data['status'] = $item['status'];
    $data['address'] = $item['address'] ? $item['address'] : $item['taddress'];
    if (!empty($logs)) {
        $data['code'] = set('p75') || set('x50', $item['regionid']) ? 2 : 1;
        if ($logid) {
            $data['code'] = 1;
        }
    }
    else {
        $data['code'] = set('p75') || set('x50', $item['regionid']) ? 3 : 1;
    }
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['title'] = '报修详情';
    $data['cause'] = $item['cause'];
    $data['measure'] = $item['measure'];
    $data['show'] = set('x66', $item['regionid']) ? 1 : 0;
    if (($item['createtime'] + $item['finishtime'] * 3600) < TIMESTAMP && $item['status'] == 2) {
        $data['code'] = 4;
    }
    $data['codeStatus'] = set('p162') ? 1 : 0;
    $data['offCode'] = $item['code'];
    util::send_result($data);

}
/**
 * 业主报修添加
 */
if ($op == 'add') {
    $regionid = $_SESSION['community']['regionid'];
    if (empty($_W['member']['uid']) || empty($regionid)) {
        util::send_error(-1, '非法操作');
    }
    $pics = xtrim($_GPC['pics']);
    $cid = intval($_GPC['cid']);
    $content = trim($_GPC['content']);
    // 判断是否开启提交时间段提交
    $p168 = set('p168') ? set('p168') : 0;
    if ($p168) {
        $nowDate = date('Y-m-d', time());
        $start = set('p169');
        $end = set('p170');
        if (!empty($start) && !empty($end)) {
            $startTime = strtotime($nowDate . $start);
            $endTime = strtotime($nowDate . $end);
            if (TIMESTAMP < $startTime || TIMESTAMP > $endTime) {
                util::send_error(-1, '请在规定时间内提交报修' . $start . '~' . $end);
            }
        }
    }
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'regionid'   => $regionid,
        'type'       => 1,
        'cid'        => $cid,
        'content'    => $content,
        'createtime' => TIMESTAMP,
        'status'     => 2,
        'addressid'  => $_SESSION['community']['addressid'],
        'uid'        => $_W['member']['uid'],
        'images'     => $pics,
        'state'      => 2,
        'realname'   => $_W['member']['realname'],
        'mobile'     => $_W['member']['mobile'],
        'code'       => rand(1000, 9999)
    );

    $data['enable'] = set('x5', $regionid) || set('p24') ? 1 : 0; //0审核通过
    if (pdo_insert("xcommunity_report", $data)) {
        //兼容老版本图片
        $reportid = pdo_insertid();
        $images = explode(',', $pics);
        if ($images) {
            foreach ($images as $key => $item) {
                $dat = array(
                    'src' => $item,
                );
                pdo_insert('xcommunity_images', $dat);
                $thumbid = pdo_insertid();
                $dat = array(
                    'reportid' => $reportid,
                    'thumbid'  => $thumbid,
                );
                pdo_insert('xcommunity_report_images', $dat);
            }
        }

        // 客服消息通知
        if ($_W['container'] == 'wechat') {
            if (set('p47')) {
                $ret = util::sendnotice(set('p47'));
            }
            if (set('t3')) {
                //微信通知
                $wxcontent = array(
                    'first'    => array(
                        'value' => '新报修通知',
                    ),
                    'keyword1' => array(
                        'value' => $_W['member']['realname'],
                    ),
                    'keyword2' => array(
                        'value' => $_W['member']['mobile'],
                    ),
                    'keyword3' => array(
                        'value' => $_SESSION['community']['title'] . $_SESSION['community']['address'],
                    ),
                    'keyword4' => array(
                        'value' => $content,
                    ),
                    'keyword5' => array(
                        'value' => date('Y-m-d H:i', TIMESTAMP),
                    ),
                    'remark'   => array(
                        'value' => '请尽快联系客户。',
                    ),
                );
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$reportid}&do=repair&m=" . $this->module['name'];
                $tplid = set('t4');
                $status = util::sendtpl($regionid, $wxcontent, ' and t1.repair=1', $url, $tplid, '', 1, $reportid);
                $stat = util::sendxqtpl($regionid, $wxcontent, $data['cid'], $url, $tplid, 1, 1, $reportid);

            }
        }
        if (set('s2') && set('s5')) {
            $type = set('s1');
            if ($type == 1) {
                $type = 'wwt';
            }
            elseif ($type == 2) {
                $type = 'juhe';
                $tpl_id = set('s8');
            }
            elseif ($type == 3) {
                $type = 'aliyun_new';
                $tpl_id = set('s33');
            }
            else {
                $type = 'qhyx';
            }
            $mobile = $_W['member']['mobile'];
            if ($type == 'wwt' || $type == 'qhyx') {
                $smsg = "报修信息" . $content . ",报修人电话" . $mobile;
            }
            elseif ($type == 'juhe') {

                $smsg = urlencode("#content#=$content&#mobile#=$mobile");
            }
            else {
                $smsg = json_encode(array('content' => $content, 'tel' => $mobile));
            }
            $ret = util::sendxqsms($regionid, $smsg, $type, 1, $tpl_id, $data['cid'], $id);
        }
        else {

            if (set('x24', $regionid)) {
                $type = set('x21', $regionid);
                if ($type == 1) {
                    $type = 'wwt';
                }
                elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('x33', $regionid);
                }
                else {
                    $type = 'aliyun_new';
                    $tpl_id = set('x68', $regionid);
                }
                $mobile = $_W['member']['mobile'];
                if ($type == 'wwt') {
                    $smsg = "报修信息" . $content . ",报修人电话" . $mobile;
                }
                elseif ($type == 'juhe') {
                    $smsg = urlencode("#content#=$content&#mobile#=$mobile");
                }
                else {
                    $smsg = json_encode(array('content' => $content, 'tel' => $mobile));
                }
                $ret = util::sendxqsms($regionid, $smsg, $type, 2, $tpl_id, $data['cid'], $id);
            }
        }

        $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
        $yl = "^N1^F1\n";
        $yl .= "^B2 新报修订单\n";
        $yl .= "内容：" . $content . "\n";
        $yl .= "地址：" . $_SESSION['community']['address'] . "\n";
        $yl .= "业主：" . $_W['member']['realname'] . "\n";
        $yl .= "电话：" . $_W['member']['mobile'] . "\n";
        $yl .= "时间：" . $createtime;
        $yl .= "\n";
        $yl .= "\n";
        $yl .= "\n";
        $yl .= "\n";
        $yl .= "\n";
        $fy = array(
            'msgDetail' =>
                '
                                物业公司欢迎您报修

                            内容：' . $_GPC['content'] . '
                            -------------------------

                            地址：' . $_SESSION['community']['address'] . '
                            业主：' . $_W['member']['realname'] . '
                            电话：' . $_W['member']['mobile'] . '
                            时间：' . $createtime . '
                            ',
        );
        if (set('d2') && set('d3')) {
            $type = set('d1') == 1 ? 'yl' : 'fy';
            $content = set('d1') == 1 ? $yl : $fy;
            $result = xq_print::xqprint($type, 1, $content);

        }
        elseif (set('x27', $regionid)) {
            $type = set('x26') == 1 ? 'yl' : 'fy';
            $content = set('x26') == 1 ? $yl : $fy;
            $result = xq_print::xqprint($type, 2, $content, $regionid);
        }
        $data = array();
        $data['content'] = '报修成功';
        util::send_result($data);
    }
}
/**
 * 业主报修评价
 */
if ($op == 'rank') {
    $rankid = intval($_GPC['rankid']);
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'rankid'     => $rankid,
        'content'    => trim($_GPC['content']),
        'rank'       => intval($_GPC['status']),
        'type'       => 3,
        'uid'        => $_W['member']['uid'],
        'uniacid'    => $_W['uniacid'],
        'createtime' => TIMESTAMP
    );
    if (pdo_insert("xcommunity_rank", $data)) {
        $d = array(
            'uniacid'  => $_W['uniacid'],
            'regionid' => $regionid,
            'uid'      => $_W['member']['uid'],
            'title'    => '报修评价',
            'content'  => trim($_GPC['content'])
        );
        pdo_insert('xcommunity_user_news', $d);
        $data = array();
        $data['content'] = '评价成功';
        util::send_result($data);
    }

}
/**
 * 业主我的报修列表
 */
if ($op == 'my') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = "t1.enable =0 and t1.type=1 and t1.uid=:uid";
    $parmas[':uid'] = $_W['member']['uid'] ? $_W['member']['uid'] : 1;
    $sql = "select t1.createtime,t1.status,t2.name,t1.id,t1.code from " . tablename("xcommunity_report") . "t1 left join " . tablename('xcommunity_category') . "t2 on t1.cid=t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parmas);
    foreach ($list as $key => $val) {
        $list[$key]['createtime'] = date('Y-m-d H:i', $val['createtime']);
        $list[$key]['url'] = $this->createMobileUrl('repair', array('op' => 'detail', 'id' => $val['id']));
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['codeStatus'] = set('p162') ? 1 : 0;
    util::send_result($data);
}
/**
 * 接收员处理报修
 */
if ($op == 'grab') {
    $id = intval($_GPC['id']);
    $status = intval($_GPC['status']);
    $content = trim($_GPC['content']);
    $pics = xtrim($_GPC['pics']);
    $codeStatus = set('p162') ? 1 : 0; //是否开启闭单码验证
    $code = $_GPC['offcode'];
    $data = array();
    $sql = "select t1.*,t2.realname,t2.mobile,t3.address,t4.openid,t5.title from" . tablename('xcommunity_report') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_mapping_fans') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where t1.id=:id";
    $item = pdo_fetch($sql, array(':id' => $id));
    if ($codeStatus) {
        //是否开启闭单码验证
        if ($code != $item['code']) {
            util::send_error(-1, '闭单码错误');
        }
    }

    $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile', 'id'));
    $realname = $_W['member']['realname'] ? $_W['member']['realname'] : $staff['realname'];
    $mobile = $_W['member']['mobile'] ? $_W['member']['mobile'] : $staff['mobile'];
    if (empty($item)) {
        $data['content'] = '信息不存在';
    }
    $data = array(
        'reportid'   => $id,
        'content'    => $content,
        'uid'        => $_W['member']['uid'],
        'createtime' => TIMESTAMP,
        'dealing'    => $realname,
        'mobile'     => $mobile,
        'images'     => $pics,
        'price'      => $_GPC['price'],
        'status'     => $status,
        'cause'      => trim($_GPC['cause']),
        'measure'    => trim($_GPC['measure']),
        'rank'       => trim($_GPC['rank'])
    );
    if (pdo_insert('xcommunity_report_log', $data)) {
        $user = pdo_fetch("select t1.uid from" . tablename('xcommunity_users') . "t1 where t1.staffid=:staffid", array(':staffid' => $staff['id']));
        if (pdo_update('xcommunity_report', array('status' => $status, 'price' => $_GPC['price'], 'grabuid' => $user['uid']), array('id' => $id))) {
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
                    $grabNotice = pdo_get('xcommunity_notice', array('enable' => 1, 'staffid' => $staff['id']), array('grab_num', 'grab_ids', 'id'));
                    if ($grabNotice['grab_ids']) {
                        $garbIds = explode(',', $grabNotice['grab_ids']);
                    }
                    $garb_ids = $grabNotice['grab_ids'] ? $grabNotice['grab_ids'] : '';
                    $grab_num = $grabNotice['grab_num'] ? $grabNotice['grab_num'] : 0;
                    if ($garbIds && @in_array($id, $garbIds)) {
                    }
                    else {
                        $garb_ids .= ',' . $id;
                        $garb_ids = xtrim($garb_ids);
                        $grab_num++;
                        pdo_update('xcommunity_notice', array('grab_num' => $grab_num, 'grab_ids' => $garb_ids), array('id' => $grabNotice['id']));
                    }
                }
                elseif ($status == 3) {
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
                    'uniacid'  => $_W['uniacid'],
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
                        'uniacid'    => $_W['uniacid'],
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
                            'reportid'   => $data['reportid'],
                            'orderid'    => $orderid,
                            'uniacid'    => $_W['uniacid'],
                            'price'      => floatval($_GPC['price']),
                            'status'     => 0,
                            'createtime' => TIMESTAMP,
                            'uid'        => $item['uid'],
                            'openid'     => $item['openid'],
                        );
                        pdo_insert('xcommunity_report_order', $d);
                    }
                }
                else {
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
                    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&orderid={$orderid}&type=9&do=paycenter&m=" . $this->module['name'];
                    $ret = util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                    $dat = array(
                        'uniacid'  => $_W['uniacid'],
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
 * 接收员抢单处理报修
 */
if ($op == 'verity') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("select t2.openid,t1.realname,t1.uid from" . tablename('xcommunity_report') . "t1 left join" . tablename('mc_mapping_fans') . "t2 on t1.uid=t2.uid where t1.id=:id", array(':id' => $id));
        $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile', 'id'));
        $realname = $_W['member']['realname'] ? $_W['member']['realname'] : $staff['realname'];
        $mobile = $_W['member']['mobile'] ? $_W['member']['mobile'] : $staff['mobile'];
        $user = pdo_fetch("select t1.uid from" . tablename('xcommunity_users') . "t1 where t1.staffid=:staffid", array(':staffid' => $staff['id']));
        if (pdo_update('xcommunity_report', array('status' => 3, 'grabuid' => $user['uid']), array('id' => $id))) {
            $data = array(
                'reportid'   => $id,
                'content'    => '正在处理',
                'uid'        => $_W['member']['uid'],
                'createtime' => TIMESTAMP,
                'dealing'    => $realname,
                'mobile'     => $mobile
            );
            pdo_insert('xcommunity_report_log', $data);
            $send = pdo_fetch('select id from' . tablename('xcommunity_report_send_log') . " where reportid=:id and uid=:uid order by id asc", array(':id' => $id, ':uid' => $_W['member']['uid']));
            pdo_update('xcommunity_report_send_log', array('grabtime' => TIMESTAMP), array('id' => $send['id']));
            $t = set('t21');
            if ($t) {
                $content = array(
                    'first'    => array(
                        'value' => '尊敬的业主，您的报修已正在处理',
                    ),
                    'keyword1' => array(
                        'value' => date('Y-m-d H:i', TIMESTAMP),
                    ),
                    'keyword2' => array(
                        'value' => $item['realname'],
                    ),
                    'remark'   => array(
                        'value' => '如有相关问题，可联系处理人:' . $realname . ",联系电话:" . $mobile,
                    ),
                );
                $tplid = set('t22');
                $ret = util::sendTplNotice($item['openid'], $tplid, $content, '', $topcolor = '#FF683F');
                $dat = array(
                    'uniacid'  => $_W['uniacid'],
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
}