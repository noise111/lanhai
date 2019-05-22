<?php
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'record', 'recharge', 'addFault', 'getSlides');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 充电装列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    //是否开启强制绑定小区
    if (set('p146')) {
        $condition .= " and t1.regionid=:regionid";
        $params[':regionid'] = $_SESSION['community']['regionid'];
    }
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and t1.title like '%{$keyword}%'";
    }
    $lng = $_GPC['lng'];
    $lat = $_GPC['lat'];
    $list = pdo_fetchall("select t1.*,t2.title as rtitle from" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition order by t1.displayorder asc,t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $k => $item) {
        $distance = util::GetDistance($lat, $lng, $item['lat'], $item['lng']);;
        $list[$k]['dis'] = $distance;
        $juli = floor($distance) / 1000;
        $list[$k]['juli'] = sprintf('%.1f', (float)$juli) . 'km';
        //插座空
        $kx_num = pdo_fetchcolumn("select count(id) from" . tablename('xcommunity_charging_socket') . "where stationid=:stationid and enable=0", array(':stationid' => $item['id']));
        $kx_num = $kx_num ? $kx_num : $item['line'];
        $list[$k]['num'] = $item['enable'] == 0 ? 0 : $kx_num;
    }
    sortArrByField($list, 'displayorder');
    sortArrByField($list, 'dis');

    /**
     * 查所有的插座
     */
    $station_ids = _array_column($list, 'id');
    $sockets = pdo_getall('xcommunity_charging_socket', array('stationid' => $station_ids), array('lock', 'stationid', 'id', 'enable'), '', 'lock ASC');
    $socket_ids = array();
    foreach ($sockets as $k => $val) {
        $socket_ids[$val['stationid']][] = array(
            'lock'   => $val['lock'] + 1,
            'id'     => $val['id'],
            'enable' => $val['enable'],
        );
    }
    $data = array();
    $data['list'] = $list;
    $data['sockets'] = $socket_ids;
    util::send_result($data);
}
/**
 * 充电装详情
 */
if ($op == 'detail') {
    $socketid = intval($_GPC['socketid']);
    $regionid = $_SESSION['community']['regionid'];
    $sql = "select t1.*,t2.enable as senable,t2.lock,t2.id as sid,t3.title as rtitle,t4.quanbill,t4.quanrule,t4.timebill,t4.timerule,t4.desc from" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_charging_socket') . "t2 on t1.id=t2.stationid left join" . tablename('xcommunity_region') . "t3 on t3.id=t1.regionid left join" . tablename('xcommunity_charging_throw') . "t4 on t4.id = t1.tid where t2.id=:id";
    $item = pdo_fetch($sql, array(':id' => $socketid));
    $item['lock'] = $item['lock'] + 1;
    $item['nowtime'] = date('Y-m-d H:i');
    $item['address'] = $item['address'] ? $item['address'] : $item['rtitle'];
    $item['quanrules'] = iunserializer($item['quanrule']);
    $item['timerules'] = iunserializer($item['timerule']);
    $member = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('chargecredit'));
    $item['credit'] = $member['chargecredit'] ? $member['chargecredit'] : 0;
    $item['code'] = $item['zscode'] ? $item['zscode'] : $item['code'];
    $item['content'] = $item['desc'] ? $item['desc'] : '提示：如发生过载引起意外中断、用户主动停止充电、插头被拔出等情况，剩余充电金额不予退还。';
    // 广告区域的图片
    $advpic = unserialize($item['advpic']);
    $picones = array();
    if ($advpic['picone']) {
        $picone = $advpic['picone'];
        foreach ($picone as $k => $v) {
            $picones[] = tomedia($v);
        }
    }
    $pictwos = array();
    if ($advpic['pictwo']) {
        $picpictwo = $advpic['pictwo'];
        foreach ($picpictwo as $k => $v) {
            $pictwos[] = tomedia($v);
        }
    }
    $item['picone'] = $picones;
    $item['pictwo'] = $pictwos;
    $item['advone'] = $advpic['advone'] ? $advpic['advone'] : 0;
    $item['advtwo'] = $advpic['advtwo'] ? $advpic['advtwo'] : 0;
    $item['enable'] = intval($item['enable']);
    util::send_result($item);
//    $item = pdo_get('xcommunity_charging_socket', array());
}
/**
 * 充电记录
 */
if ($op == 'record') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " uniacid=:uniacid and uid=:uid and status=1";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $_W['member']['uid'];
    $row = pdo_fetchall("select * from" . tablename('xcommunity_charging_order') . "where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $list = array();
    foreach ($row as $key => $val) {
        $list[] = array(
            'id'         => $val['id'],
            'price'      => sprintf("%.2f", $val['price']),
            'cdtime'     => sprintf("%.2f", $val['cdtime']),
            'stime'      => sprintf("%.2f", $val['stime']),
            'createtime' => date('Y年m月d日 H:i', $val['createtime']),
        );
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
/**
 * type=2按时计费 type=1 按量计费
 */
if ($op == 'add') {
    $socketid = intval($_GPC['socketid']);
    $regionid = $_SESSION['community']['regionid'];
    $priceid = intval($_GPC['priceid']);
    $item = pdo_fetch("select t1.id,t1.code,t2.lock,t1.type,t1.appid,t1.appsecret,t1.enable from" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_charging_socket') . "t2 on t1.id=t2.stationid where t2.id=:id", array(':id' => $socketid));
    if ($item['enable'] == 1) {

    } else {
        util::send_error(-1, '设备离线！');
    }
    $type = intval($_GPC['type']);
    $price = $type == 1 ? 0 : trim($_GPC['price']) / 100;
    $cdtime = $type == 1 ? trim($_GPC['cdtime']) * 60 : trim($_GPC['cdtime']);
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
        'price'      => $price,
        'status'     => 0,
        'createtime' => TIMESTAMP,
        'regionid'   => $regionid,
        'type'       => 'charging',
    );
    $r = pdo_insert('xcommunity_order', $data);
    if ($r) {
        unset($data);
        $orderid = pdo_insertid();
        $dat = array(
            'uniacid'    => $_W['uniacid'],
            'orderid'    => $orderid,
            'regionid'   => $regionid,
            'uid'        => $_W['member']['uid'],
            'openid'     => $_W['openid'],
            'code'       => $item['code'],
            'socket'     => $item['lock'],
            'cdtime'     => $cdtime,
            'price'      => $price,
            'status'     => 0,
            'createtime' => TIMESTAMP,
            'type'       => $type
        );
        pdo_insert('xcommunity_charging_order', $dat);
        $data = array();
        if ($type == 1) {
            //按量
            if ($item['type'] == 1) {
                //按量处理 上电  途电
                $pid = $item['code'] . '_' . (string)$item['lock'];
                $seconds = $cdtime * 60;
                require_once IA_ROOT . '/addons/xfeng_community/plugin/tudian/function.php';
                $cuid = getPowerUp($item['appid'], $item['appsecret'], $pid, $seconds);
                if ($cuid) {
                    $logids = explode('_', $cuid);
                    $logid = $logids[2];
                    $d = array('status' => 1, 'cuid' => $cuid, 'logid' => $logid);
                    pdo_update('xcommunity_charging_order',$d , array('orderid' => $orderid));

                }
            }elseif ($item['type'] == 2){
                // 按量 威威充
                require_once IA_ROOT . '/addons/xfeng_community/plugin/avive/charge/function.php';
                $pushtime = $cdtime;
                $socket = $item['lock'] + 1;
                $result = pushPower($item['code'], $socket, $pushtime);
                if($result['status'] == 'OK') {
                    //插座显示为充电中
                    pdo_update('xcommunity_charging_order', array('status' => 1), array('orderid' => $orderid));
                }
            }

        }
        if ($type == 2) {
            pdo_update('xcommunity_charging_order', array('isedit' => 1), array('orderid' => $orderid));
            //按时处理
            $url = $this->createMobileUrl('paycenter', array('type' => 7, 'orderid' => $orderid));
            $data['url'] = $url;
        }
        util::send_result($data);
    }


}
/**
 * 余额充值
 */
if ($op == 'recharge') {
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
        'price'      => trim($_GPC['price']),
        'status'     => 0,
        'createtime' => TIMESTAMP,
        'regionid'   => $regionid,
        'type'       => 'chargrecharge',
        'openid'     => $_W['openid']
    );
    pdo_insert('xcommunity_order', $data);
    $orderid = pdo_insertid();
    $url = $this->createMobileUrl('paycenter', array('type' => 7, 't' => 1, 'orderid' => $orderid));
    $data = array();
    $data['url'] = $url;
    util::send_result($data);
}
/**
 * 上报充电桩插座故障
 */
if ($op == 'addFault') {
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'socketid'   => intval($_GPC['socketid']),
        'fault'      => trim($_GPC['fault']),
        'content'    => trim($_GPC['content']),
        'pics'       => $_GPC['pics'],
        'createtime' => time()
    );
    $socket = pdo_get('xcommunity_charging_socket', array('id' => intval($_GPC['socketid'])), array('stationid'));
    $station = pdo_get('xcommunity_charging_station', array('id' => intval($socket['stationid'])), array('title'));
    if (pdo_insert('xcommunity_charging_fault', $data)) {
        if (set('t36')) {
            $content = array(
                'first' => array(
                    'value' => '充电桩故障通知',
                ),
                'keyword1' => array(
                    'value' => $station['title'],
                ),
                'keyword2' => array(
                    'value' => $_W['member']['realname'],
                ),
                'keyword3' => array(
                    'value' => trim($_GPC['content']),
                ),
                'keyword4' => array(
                    'value' => date('Y-m-d H:i'),
                ),
                'remark' => array(
                    'value' => '请尽快联系客户。',
                ),
            );
            $url = '';
            $tplid = set('t37');
            $notices = pdo_getall('xcommunity_charging_notice', array('uniacid' => $_W['uniacid'], 'enable' => 1), array('openid', 'chargingid'));
            foreach ($notices as $k => $v) {
                if (in_array($socket['stationid'], explode(',', $v['chargingid']))) {
                    util::sendTplNotice($v['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                }
            }
        }
        util::send_result();
    }
    else {
        util::send_error(-1);
    }

}
/**
 * 获取幻灯
 */
if ($op == 'getSlides') {
    $list = array();
    $list = pdo_fetchall("select url,thumb from".tablename('xcommunity_slide')."where uniacid=:uniacid and type=8 and status=1 and starttime<=:nowtime and endtime>=:nowtime",array(':uniacid' => $_W['uniacid'],':nowtime' => time()));
    foreach ($list as $k => $v) {
        $list[$k]['img'] = tomedia($v['thumb']);
    }
    util::send_result($list);
}