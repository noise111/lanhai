<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 下午2:06
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'grab', 'grabDetail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$_SESSION['community'] = model_user::mc_check();
if ($op == 'list') {
    //查家政子类 家政主类ID=1
    $categories = util::fetchall_category(1, 1);
    foreach ($categories as $k => $v) {
        $categories[$k]['url'] = $this->createMobileUrl('homemaking', array('op' => 'detail', 'cid' => $v['id']));
    }
    $data = array();
    $data['list'] = $categories;

    $swipers = pdo_fetchall("select t1.* from" . tablename('xcommunity_slide') . "t1 left join" . tablename('xcommunity_slide_region') . "t2 on t1.id=t2.sid where t1.type=3 and t1.uniacid=:uniacid and t1.status=1 and t2.regionid=:regionid and t1.starttime<=:nowtime and t1.endtime>=:nowtime", array(':uniacid' => $_W['uniacid'], ':regionid' => $_SESSION['community']['regionid'], ':nowtime' => time()));
    foreach ($swipers as $k => $v) {
        $swipers[$k]['img'] = tomedia($v['thumb']);
    }
    $data['swiper'] = $swipers;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
elseif ($op == 'detail') {
    $cid = intval($_GPC['id']);
    $item = pdo_get('xcommunity_homemaking_list', array('cid' => $cid), array());
    $piclist = '';
    if ($item['service']) {
        $piclist = explode(',', $item['service']);
    }
    $arr = array();
    foreach ($piclist as $k => $val) {
//        $arr[] = tomedia($val);
        $arr[] = array(
            'src'  => tomedia($val),
            'msrc' => tomedia($val),
        );
    }
    $data = array();
    $data['images'] = $arr;
    $data['price'] = $item['price'];
    $data['content'] = $item['content'];
    $data['cid'] = $item['cid'];
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['title'] = '家政预约';
    $data['address'] = $_SESSION['community']['address'];
    $data['mobile'] = $_W['member']['mobile'];

    util::send_result($data);
}
elseif ($op == 'add') {
    $servicetime = trim($_GPC['servicetime']);
    $content = trim($_GPC['content']);
    $cid = intval($_GPC['cid']);
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'uid'         => $_W['member']['uid'],
        'regionid'    => $regionid,
        'category'    => $cid,
        'servicetime' => $servicetime,
        'createtime'  => TIMESTAMP,
        'status'      => 0,
        'content'     => $content,
        'addressid'   => $_SESSION['community']['addressid'],
    );
    if (pdo_insert('xcommunity_homemaking', $data)) {
        // 客服消息通知
        $homemarkid = pdo_insertid();
        util::sendnotice(set('p50'));
        if (set('t17')) {
            $content = array(
                'first'    => array(
                    'value' => '您好，有一条新的家政服务预约。',
                ),
                'keyword1' => array(
                    'value' => $_W['member']['realname']
                ),
                'keyword2' => array(
                    'value' => $_W['member']['mobile']
                ),
                'keyword3' => array(
                    'value' => $data['servicetime']
                ),
                'keyword4' => array(
                    'value' => $_GPC['content'],
                ),
                'remark'   => array(
                    'value' => '请尽快联系客户。',
                ),
            );
            $tplid = set('t18');
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$homemarkid}&do=homemaking&m=" . $this->module['name'];
            $ret = util::sendtpl($regionid, $content, ' and t1.homemaking=1', $url, $tplid);
            util::sendxqtpl($regionid, $content, $data['category'], $url, $tplid, 3);
        }
        $data = array();
        $data['content'] = '预约成功';
        util::send_result($data);
    }
}
elseif ($op == 'grab') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where t1.id=:id order by t1.createtime desc ", array(':id' => $id));
    if ($item) {
        $data = array(
            'status' => $_GPC['status'],
            'remark' => $_GPC['remark']
        );
        pdo_update("xcommunity_homemaking", $data, array('id' => $id));
        if (set('p53')) {
            $content = $_GPC['status'] == 1 ? '您的家政服务已完成' : '您的家政服务已取消';
            util::app_send($item['uid'], $content);
        }
        util::permlog('', '修改家政服务状态' . '家政信息ID:' . $id);
        util::send_result($item);
    }
}
/**
 * 小区家政处理的详情
 */
if ($op == 'grabDetail') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room,t6.name,t2.address from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid left join" . tablename('xcommunity_category') . "t6 on t6.id = t1.category where t1.id=:id", array(':id' => $id));
    $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
    $item['hstatus'] = set('p96') ? 1 : 0;
    $item['address'] = $item['title'] . $item['address'];
    util::send_result($item);
}

