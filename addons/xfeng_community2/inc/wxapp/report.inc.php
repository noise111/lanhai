<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/4 下午6:14
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'rank', 'grab', 'verity', 'my');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$_SESSION['community'] = model_user::mc_check('report');
/**
 * 业主投诉列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t1.enable =0 and t1.type=2 ";
    $parmas[':uniacid'] = $_W['uniacid'];
    if (set('x11', $regionid) || set('p31')) {
        $condition .= " AND t1.regionid=:regionid";
        $parmas[':regionid'] = $_SESSION['community']['regionid'];
    } else {
        $condition .= " and t1.uid=:uid and t1.regionid=:regionid";
        $parmas[':uid'] = $_W['member']['uid'];
        $parmas[':regionid'] = $_SESSION['community']['regionid'];
    }
    $sql = "select t1.createtime,t1.status,t2.name,t1.id,t1.images from " . tablename("xcommunity_report") . "t1 left join " . tablename('xcommunity_category') . "t2 on t1.cid=t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $parmas);
    $list = array();
    if ($row) {
        foreach ($row as $key => $val) {
            $images = explode(',', $val['images']);
            $list[] = array(
                'src' => $images[0] ? tomedia($images[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'title' => $val['name'],
                'name' => $val['name'],
                'status' => $val['status'],
                'createtime' => date('Y-m-d H:i', $val['createtime']),
                'id' => $val['id'],
                'desc' => date('Y-m-d H:i', $val['createtime']),
                'url' => $this->createMobileUrl('report', array('op' => 'detail', 'id' => $val['id'])),
            );
        }
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
//    foreach ($list as $key => $val) {
//        $list[$key]['createtime'] = date('Y-m-d H:i', $val['createtime']);
//    }
//    if ($list) {
//        $data = array();
//        $data['list'] = $list;
//        util::send_result($data);
//    }
//    else {
//        util::send_error(-1, '');
//    }
}
/**
 * 业主投诉详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_fetch("SELECT t1.uid,t1.status,t1.createtime,t1.content,t2.id as rid,t5.realname,t5.mobile,t1.images,t6.dealing,t6.mobile as log_mobile,t6.content as log_content,t6.images as log_images,t1.regionid,t1.status,t3.address,t6.createtime as logtime FROM" . tablename('xcommunity_report') . "as t1 left join" . tablename('xcommunity_rank') . "t2 on t1.id = t2.rankid left join " . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_members') . "t5 on t5.uid = t1.uid left join" . tablename('xcommunity_report_log') . "t6 on t6.reportid=t1.id WHERE t1.id=:id order by t6.id desc ", array(':id' => $id));
    $logs = pdo_getall('xcommunity_report_log', array('reportid' => $id), array(), 'id');
    $logid = pdo_getcolumn('xcommunity_report_log', array('reportid' => $id, 'uid' => $_W['member']['uid']), 'id');
    $img = array();
    $imgs = '';
    foreach ($logs as $k => $log) {
        $img = explode(',', $log['images']);
        for ($i = 0; $i < count($img); $i++) {
            $img[$i] = tomedia($img[$i]);
        }
        foreach ($img as $key => $val) {
            $logimg[] = array(
                'src' => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $logs[$k]['logimg'] = $logimg;
        $logs[$k]['img'] = $img;
        $logs[$k]['logtime'] = date('Y-m-d H:i', $log['createtime']);
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
                'src' => tomedia($val),
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
                'src' => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $data['logimages'] = $logimages;
    }
    $data['realname'] = $item['realname'];
    $data['mobile'] = $item['mobile'];
    $data['content'] = $item['content'];
    $data['createtime'] = date('Y-m-d H:i', $item['createtime']);
    $data['clstatus'] = $item['status'] == 1 && $item['uid'] == $_W['member']['uid'] ? 1 : 0;
    $data['dealing'] = $item['dealing'];
    $data['logmobile'] = $item['log_mobile'];
    $data['logtime'] = date('Y-m-d H:i', $item['logtime']);
    $data['logcontent'] = $item['log_content'];
    $data['logs'] = $logs;
    $data['rid'] = $item['rid'] ? 1 : 2;
    $data['status'] = $item['status'];
    $data['address'] = $item['address'];
    if (!empty($logs)) {
        $data['code'] = set('p76') || set('x51', $item['regionid']) ? 2 : 1;
        if ($logid) {
            $data['code'] = 1;
        }
    } else {
        $data['code'] = set('p76') || set('x51', $item['regionid']) ? 3 : 1;
    }
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['title'] = '建议详情';
    $data['show'] = set('x67', $item['regionid']) ? 1 : 0;
    util::send_result($data);

}
/**
 * 业主投诉添加
 */
if ($op == 'add') {
    $regionid = $_SESSION['community']['regionid'];
    $pics = xtrim($_GPC['pics']);
    $cid = intval($_GPC['cid']);
    $content = trim($_GPC['content']);
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => $regionid,
        'type' => 2,
        'cid' => $cid,
        'content' => $content,
        'createtime' => TIMESTAMP,
        'status' => 2,
        'addressid' => $_SESSION['community']['addressid'],
        'uid' => $_W['member']['uid'],
        'images' => $pics,
        'state' => 2,
        'realname' => $_W['member']['realname'],
        'mobile' => $_W['member']['mobile']
    );
    $data['enable'] = set('x6', $regionid) || set('p25') ? 1 : 0; //0审核通过
    if (pdo_insert("xcommunity_report", $data)) {
        $reportid = pdo_insertid();
        $da = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $_W['member']['uid'],
            'cid' => $cid,
            'createtime' => TIMESTAMP,
        );
        /**
         * 针对分类提交统计
         */
        $num = pdo_getcolumn('xcommunity_report_num', array('uid' => $_W['member']['uid'], 'cid' => $cid), 'num');
        if ($num) {
            pdo_update('xcommunity_report_num',array('num +='=>1),array('uid'=>$_W['member']['uid'],'cid' => $cid ));
        } else {
            $da['num'] = 1;
            pdo_insert('xcommunity_report_num', $da);
        }
        $category = pdo_get('xcommunity_category', array('id' => $cid));
        //兼容老版本图片
        $images = explode(',', $pics);
        foreach ($images as $key => $item) {
            $dat = array(
                'src' => $item,
            );
            pdo_insert('xcommunity_images', $dat);
            $thumbid = pdo_insertid();
            $dat = array(
                'reportid' => $reportid,
                'thumbid' => $thumbid,
            );
            pdo_insert('xcommunity_report_images', $dat);
        }
        // 客服消息通知
        if ($_W['container'] == 'wechat') {
            if (set('p48')) {
                util::sendnotice(set('p48'));
            }

            if (set('t5')) {
                //开启微信通知
                $wxcontent = array(
                    'first' => array(
                        'value' => '新建议通知',
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
                    'remark' => array(
                        'value' => '请尽快联系客户。',
                    ),
                );
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$reportid}&do=report&m=" . $this->module['name'];
                $tplid = set('t6');
                /**
                 * 考虑0不限制
                 */
                if ($da['num'] <= $category['reportnum'] || empty($category['reportnum'])) {
                    $status = util::sendtpl($regionid, $wxcontent, ' and t1.report=1', $url, $tplid,'', 2, $reportid);
                    $stat = util::sendxqtpl($regionid, $wxcontent, $data['cid'], $url, $tplid, 2, 2, $reportid);
                } else {
                    $condition = " t1.uniacid={$_W['uniacid']} and t2.regionid={$regionid} and t3.cid ={$data['cid']} and t1.type in(1,3) and t1.enable=7 and t1.minhour <= {$da['num']} and t1.maxhour >= {$da['num']}";
                    $sql = 'select t4.openid from' . tablename('xcommunity_notice') . "as t1 left join" . tablename('xcommunity_notice_region') . "as t2 on t1.id=t2.nid left join " . tablename('xcommunity_notice_category') . "as t3 on t1.id=t3.nid left join" . tablename('xcommunity_staff') . "t4 on t1.staffid=t4.id where $condition";
                    $notice = pdo_fetchall($sql);
                    if ($notice) {
                        foreach ($notice as $key => $item) {
                            $ret = util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');

                        }
                    }
                }
            }
        }

        if (set('s2') && set('s5')) {
            $type = set('s1');
            if ($type == 1) {
                $type = 'wwt';
                $tpl_id = '';
            } elseif ($type == 2) {
                $type = 'juhe';
                $tpl_id = set('s8');
            } elseif ($type == 3) {
                $type = 'aliyun_new';
                $tpl_id = set('s33');
            } else {
                $type = 'qhyx';
            }
            $mobile = $_W['member']['mobile'];
            if ($type == 'wwt' || $type == 'qhyx') {
                $smsg = "意见建议信息" . $content . ",意见建议人电话" . $mobile;
            } elseif ($type == 'juhe') {

                $smsg = urlencode("#content#=$content&#mobile#=$mobile");
            } else {
                $smsg = json_encode(array('content' => $content, 'tel' => $mobile));
            }
            $ret = util::sendxqsms($regionid, $smsg, $type, 1, $tpl_id, $data['cid'], $id);
        } else {
            if (set('x24', $regionid)) {
                $type = set('x21', $regionid);
                if ($type == 1) {
                    $type = 'wwt';
                } elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('x33', $regionid);
                } else {
                    $type = 'aliyun_new';
                    $tpl_id = set('x68', $regionid);
                }
                $mobile = $_W['member']['mobile'];
                if ($type == 'wwt') {
                    $smsg = "意见建议信息" . $content . ",意见建议人电话" . $mobile;
                } elseif ($type == 'juhe') {

                    $smsg = urlencode("#content#=$content&#mobile#=$mobile");
                } else {
                    $smsg = json_encode(array('content' => $content, 'tel' => $mobile));
                }
                $ret = util::sendxqsms($regionid, $smsg, $type, 2, $tpl_id, $data['cid'], $id);
            }
        }
        $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
        $yl = "^N1^F1\n";
        $yl .= "^B2 新意见建议订单\n";
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
                                物业公司欢迎您建议

                            内容：' . $_GPC['content'] . '
                            -------------------------

                            地址：' . $_SESSION['community']['address'] . '
                            业主：' . $_W['member']['realname'] . '
                            电话：' . $_W['member']['mobile'] . '
                            时间：' . $createtime . '
                            ',
        );
        if (set('d2') && set('d4')) {
            $type = set('d1') == 1 ? 'yl' : 'fy';
            $content = set('d1') == 1 ? $yl : $fy;
            xq_print::xqprint($type, 1, $content);

        } elseif (set('x28', $regionid)) {
            $type = set('x26') == 1 ? 'yl' : 'fy';
            $content = set('x26') == 1 ? $yl : $fy;
            xq_print::xqprint($type, 2, $content, $regionid);
        }
        $data = array();
        $data['content'] = '反馈成功';
        util::send_result($data);
    }
}
/**
 * 业主投诉评价
 */
if ($op == 'rank') {
    $rankid = intval($_GPC['rankid']);
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'rankid' => $rankid,
        'content' => trim($_GPC['content']),
        'rank' => intval($_GPC['status']),
        'type' => 3,
        'uid' => $_W['member']['uid'],
        'uniacid' => $_W['uniacid'],
        'createtime' => TIMESTAMP
    );
    if (pdo_insert("xcommunity_rank", $data)) {
        $d = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => $regionid,
            'uid' => $_W['member']['uid'],
            'title' => '建议评价',
            'content' => trim($_GPC['content'])
        );
        pdo_insert('xcommunity_user_news', $d);
        $data = array();
        $data['content'] = '评价成功';
        util::send_result($data);
    }

}
/**
 * 接收员处理投诉
 */
if ($op == 'grab') {
    $id = intval($_GPC['id']);
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
    $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile', 'id'));
    $realname = $_W['member']['realname'] ? $_W['member']['realname'] : $staff['realname'];
    $mobile = $_W['member']['mobile'] ? $_W['member']['mobile'] : $staff['mobile'];
    if (empty($item)) {
        $data['content'] = '信息不存在';
    }
    $data = array(
        'reportid' => $id,
        'content' => $content,
        'uid' => $_W['member']['uid'],
        'createtime' => TIMESTAMP,
        'dealing' => $realname,
        'mobile' => $mobile,
        'images' => $pic,
        'status' => $status
    );
    if (pdo_insert('xcommunity_report_log', $data)) {
        $user = pdo_fetch("select t1.uid from" . tablename('xcommunity_users') . "t1 where t1.staffid=:staffid", array(':staffid' => $staff['id']));
        if (pdo_update('xcommunity_report', array('status' => $status, 'grabuid' => $user['uid']), array('id' => $id))) {
            if (set('t9')) {
                if ($status == 1) {
                    $category = util::fetch_category_one($item['cid']);
                    $content = array(
                        'first' => array(
                            'value' => '您的意见建议已处理',
                        ),
                        'keyword1' => array(
                            'value' => $item['realname'],
                        ),
                        'keyword2' => array(
                            'value' => $category['name'],
                        ),
                        'keyword3' => array(
                            'value' => $item['content'],
                        ),
                        'keyword4' => array(
                            'value' => $data['content'],
                        ),
                        'keyword5' => array(
                            'value' => $data['dealing'],
                        ),
                        'remark' => array(
                            'value' => '请到微信我的意见建议给我们评价，谢谢使用！',
                        ),
                    );
                    $grabNotice = pdo_get('xcommunity_notice', array('enable' => 2, 'staffid' => $staff['id']), array('grab_num', 'grab_ids', 'id'));
                    if ($grabNotice['grab_ids']) {
                        $garbIds = explode(',', $grabNotice['grab_ids']);
                    }
                    $garb_ids = $grabNotice['grab_ids'] ? $grabNotice['grab_ids'] : '';
                    $grab_num = $grabNotice['grab_num'] ? $grabNotice['grab_num'] : 0;
                    if ($garbIds && @in_array($id, $garbIds)) {
                    }else {
                        $garb_ids .= ',' . $id;
                        $garb_ids = xtrim($garb_ids);
                        $grab_num++;
                        pdo_update('xcommunity_notice', array('grab_num' => $grab_num, 'grab_ids' => $garb_ids), array('id' => $grabNotice['id']));
                    }
                } elseif ($status == 3) {
                    $category = util::fetch_category_one($item['cid']);
                    $content = array(
                        'first' => array(
                            'value' => '您的意见建议正在处理中',
                        ),
                        'keyword1' => array(
                            'value' => $item['realname'],
                        ),
                        'keyword2' => array(
                            'value' => $category['name'],
                        ),
                        'keyword3' => array(
                            'value' => $item['content'],
                        ),
                        'keyword4' => array(
                            'value' => $data['content'],
                        ),
                        'keyword5' => array(
                            'value' => $data['dealing'],
                        ),
                        'remark' => array(
                            'value' => '请到微信我的意见建议给我们评价，谢谢使用！',
                        ),
                    );
                }
                $tplid = set('t10');
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$data['reportid']}&op=detail&do=report&m=" . $this->module['name'];
                util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                $dat = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $item['uid'],
                    'sendtime' => TIMESTAMP,
                    'reportid' => $id,
                    'type' => 2,
                    'grabtime' => $data['createtime']
                );
                pdo_insert('xcommunity_report_send_log', $dat);
            }

        }
        $data['content'] = '处理成功';
    }
    util::send_result($data);
}
/**
 * 接收员抢单处理投诉
 */
if ($op == 'verity') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("select t2.openid,t1.realname from" . tablename('xcommunity_report') . "t1 left join" . tablename('mc_mapping_fans') . "t2 on t1.uid=t2.uid where t1.id=:id", array(':id' => $id));
        $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile'));
        $realname = $_W['member']['realname'] ? $_W['member']['realname'] : $staff['realname'];
        $mobile = $_W['member']['mobile'] ? $_W['member']['mobile'] : $staff['mobile'];
        if (pdo_update('xcommunity_report', array('status' => 3), array('id' => $id))) {
            $data = array(
                'reportid' => $id,
                'content' => '正在处理',
                'uid' => $_W['member']['uid'],
                'createtime' => TIMESTAMP,
                'dealing' => $realname,
                'mobile' => $mobile
            );
            pdo_insert('xcommunity_report_log', $data);
            $send = pdo_fetch('select id from' . tablename('xcommunity_report_send_log') . " where reportid=:id and uid=:uid order by id asc", array(':id' => $id, ':uid' => $_W['member']['uid']));
            pdo_update('xcommunity_report_send_log', array('grabtime' => TIMESTAMP), array('id' => $send['id']));
            $t = set('t21');
            if ($t) {
                $content = array(
                    'first' => array(
                        'value' => '尊敬的业主，我们已收到您的建议',
                    ),
                    'keyword1' => array(
                        'value' => date('Y-m-d H:i', TIMESTAMP),
                    ),
                    'keyword2' => array(
                        'value' => $item['realname'],
                    ),
                    'remark' => array(
                        'value' => '如有相关问题，可联系处理人:' . $realname . ",联系电话:" . $mobile,
                    ),
                );
                $tplid = set('t22');
                $ret = util::sendTplNotice($item['openid'], $tplid, $content, '', $topcolor = '#FF683F');
                $dat = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $item['uid'],
                    'sendtime' => TIMESTAMP,
                    'reportid' => $id,
                    'type' => 1,
                    'grabtime' => $data['createtime']
                );
                pdo_insert('xcommunity_report_send_log', $dat);
            }

        }
    }
}
/**
 * 业主我的投诉列表
 */
if ($op == 'my') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = "t1.enable =0 and t1.type=2 and t1.uid=:uid";
    $parmas[':uid'] = $_W['member']['uid'];
    $sql = "select t1.createtime,t1.status,t2.name,t1.id from " . tablename("xcommunity_report") . "t1 left join " . tablename('xcommunity_category') . "t2 on t1.cid=t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parmas);
    foreach ($list as $key => $val) {
        $list[$key]['createtime'] = date('Y-m-d H:i', $val['createtime']);
        $list[$key]['url'] = $this->createMobileUrl('report', array('op' => 'detail', 'id' => $val['id']));
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}