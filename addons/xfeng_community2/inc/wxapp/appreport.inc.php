<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午1:24
 */
global $_GPC, $_W;
$ops = array('applist', 'detail', 'grab', 'sendLog');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 投诉列表
 */
if ($op == 'applist') {

    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition['uniacid'] = $_SESSION['appuniacid'];
    $condition['type'] = 2;
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
 * 投诉详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_fetch("SELECT t1.uid,t1.status,t1.createtime,t1.content,t2.id as rid,t5.realname,t5.mobile,t1.images,t6.dealing,t6.mobile as log_mobile,t6.content as log_content,t6.images as log_images,t1.regionid,t1.status,t3.address FROM" . tablename('xcommunity_report') . "as t1 left join" . tablename('xcommunity_rank') . "t2 on t1.id = t2.rankid left join " . tablename('xcommunity_member_room') . "t3 on t3.id = t1.addressid left join" . tablename('mc_members') . "t5 on t5.uid = t1.uid left join" . tablename('xcommunity_report_log') . "t6 on t6.reportid=t1.id WHERE t1.id=:id order by t6.id desc ", array(':id' => $id));
    $logs = pdo_getall('xcommunity_report_log', array('reportid' => $id), array(), 'id');
    $logid = pdo_getcolumn('xcommunity_report_log', array('reportid' => $id, 'uid' => $_W['member']['uid']), 'id');
    $img = array();
    $imgs = '';
    foreach ($logs as $k => $log) {
        $img = explode(',', $log['images']);
        for ($i = 0; $i < count($img); $i++) {
            $img[$i] = tomedia($img[$i]);
        }
        $logs[$k]['img'] = $img;
        foreach ($img as $key => $val) {
            $logimg[] = array(
                'src' => tomedia($val),
                'msrc' => tomedia($val),
            );
        }
        $logs[$k]['logimg'] = $logimg;
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
    $data['realname'] = $item['realname'];
    $data['mobile'] = $item['mobile'];
    $data['content'] = $item['content'];
    $data['createtime'] = date('Y-m-d H:i', $item['createtime']);
    $data['clstatus'] = $item['status'] == 1 && $item['uid'] == $_W['member']['uid'] ? 1 : 0;
    $data['dealing'] = $item['dealing'];
    $data['logmobile'] = $item['log_mobile'];
    $data['logcontent'] = $item['log_content'];
    $data['logs'] = $logs;
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
    $data['_status'] = set('p96') ? 1 : 0;
    $data['title'] = '建议详情';
    util::send_result($data);
}
/**
 * 投诉处理
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
    $staff = pdo_get('xcommunity_staff', array('openid' => $_W['fans']['from_user']), array('realname', 'mobile'));
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
        if (pdo_update('xcommunity_report', array('status' => $status), array('id' => $id))) {
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
                } elseif ($status == 1) {
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
                    'uniacid' => $item['uniacid'],
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
 * 我的派单记录
 */
if ($op == 'sendLog') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $uniacid = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 1;
    $condition['uniacid'] = $uniacid;
    $appuid  = $_SESSION['appuid'] ? $_SESSION['appuid'] : 9;
    $staffid = pdo_getcolumn('xcommunity_users', array('uid' => $appuid), 'staffid');
    $openid = pdo_getcolumn('xcommunity_staff', array('id' => $staffid), 'openid');
    $member = pdo_get('mc_mapping_fans', array('openid' => $openid), array('uid'));
    $condition['uid'] = $member['uid'];
    $condition['type'] = 2;
    $logs = pdo_getslice('xcommunity_report_send_log', $condition, array($pindex, $psize), $total, '', '', array('sendtime desc'));
    $members = pdo_getall('mc_members', array('uniacid' => $uniacid), array('uid', 'realname', 'nickname', 'mobile'), 'uid');
    $list = array();
    foreach ($logs as $k => $v) {
        $list[] = array(
            'id' => $v['id'],
            'sendtime'  => date('Y-m-d H:i', $v['sendtime']),
            'grabtime'  => $v['grabtime'] ? date('Y-m-d H:i', $v['grabtime']) : 0,
            'realname' => $members[$v['uid']]['realname'],
            'nickname' => $members[$v['uid']]['nickname'],
            'mobile' => $members[$v['uid']]['mobile'],
        );
    }
    util::send_result($list);
}