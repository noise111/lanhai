<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2018/1/6 下午10:15
 */
global $_GPC, $_W;
$ops = array('list', 'rush');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $deviceid = intval($_GPC['deviceid']);
    $sql = "select t1.image,t1.link,t1.advtitle,t1.advtime,t1.advday from" . tablename('xcommunity_plugin_adv') . "t1 left join" . tablename('xcommunity_plugin_adv_guard') . "t2 on t1.id = t2.advid where t2.guardid=:guardid and t2.regionid=:regionid and t1.status=1 and t1.uniacid=:uniacid ";
    $slides = pdo_fetchall($sql, array(':guardid' => $deviceid, ':regionid' => $_SESSION['community']['regionid'], ':uniacid' => $_W['uniacid']));
    $list = array();
    foreach ($slides as $k => $v) {
        $endtime = $v['advtime'] + $v['advday'] * 60 * 60 * 24;
        if ($endtime > time() && time() > $v['advtime']) {
            $list[] = array(
                'url' => $v['link'],
                'img' => tomedia($v['image']),
                'title' => $v['advtitle']
            );
        }
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);

} elseif ($op == 'rush') {
    $deviceid = intval($_GPC['deviceid']);
    $guard = pdo_fetchall("select advid from" . tablename('xcommunity_plugin_adv_guard') . "where guardid =:guardid ", array(':guardid' => $deviceid), 'advid');
    foreach ($guard as $key => $val) {
        $advid .= $val['advid'] . ',';
    }
    $advid = rtrim($advid, ",");
    $advid = ltrim($advid, ',');
    $slides = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_plugin_adv') . "WHERE uniacid=:uniacid and status = 1 and id in({$advid})", array(':uniacid' => $_W['uniacid']));
    shuffle($slides);
    $data = array_shift($slides);
    $price = randomFloat(0, $data['price']);
    $credit = sprintf("%.2f", $price);
    load()->model('mc');
    $aid = $data['id'];
    $sql = "select * from" . tablename('xcommunity_plugin_adv_data') . "where openid=:openid order by createtime desc limit 1";
    $dat = pdo_fetchall($sql, array(':openid' => $_W['fans']['from_user']));
    $time = TIMESTAMP - 60 * $data['opentime'] - $dat[0]['createtime'];
    $adv = pdo_get('xcommunity_plugin_adv', array('id' => $aid), array('num'));
    $tsql = "select count(*) from" . tablename('xcommunity_plugin_adv_data') . "where openid=:openid and aid=:aid order by createtime ";
    $total = pdo_fetchcolumn($tsql, array(':openid' => $_W['fans']['from_user'], ':aid' => $aid));
    if ($total < $adv['num']) {
        if (TIMESTAMP > $dat[0]['createtime'] + 60 * $data['opentime']) {
            $d = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $_W['member']['uid'],
                'price' => $credit,
                'openid' => $_W['fans']['from_user'],
                'createtime' => TIMESTAMP,
                'aid' => $aid
            );
            pdo_insert('xcommunity_plugin_adv_data', $d);
            $result = mc_credit_update($_W['member']['uid'], 'credit2', $credit, array($_W['member']['uid'], '微信开门领取的红包'));
            if ($result) {
                $uid = $data['uid'];
                pdo_query("update " . tablename('xcommunity_users') . "set balance= balance-{$credit} where uid=:uid", array(':uid' => $uid));
                $data['content'] = '恭喜领取了' . $credit . '元，已发放到你的余额。';
            }
        } else {
            $data['content'] = '你已领取红包,请过' . $data['opentime'] . '分钟在领取';
        }
    } else {
        $data['content'] = '你已领满' . $adv['num'] . '个红包';
    }
    util::send_result($data);
    exit();
}