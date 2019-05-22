<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 下午5:12
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'detail') {
    $member = pdo_get('xcommunity_stop_park_cars', array('car_no' => trim($_GPC['license'])), array('id', 'car_no', 'park_id'));
    if (empty($member)) {
        util::send_error(-1, '暂未开通智能停车');
        exit();
    }
    if ($member['park_id']) {
        $sql = "select t2.company_id,t2.access_secret,t2.sign_key,t1.park_id from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id";
        $park = pdo_fetch($sql, array(':id' => $member['park_id']));
        $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
        $car = getOneCar($park['sign_key'], $park['park_id'], $token, $member['car_no']);
        if (empty($car)) {
            util::send_error(-1, '网络异常');
            exit();
        }
        $end_date = strtotime($car['valid_date']) + 86400 * 30;
        $end_date = date('Y-m-d', $end_date);
        $data = array();
        $data['car_no'] = trim($_GPC['license']);
        $data['valid_date'] = $car['valid_date'];
        $data['end_date'] = $end_date;
        $data['month_fee'] = $car['month_fee'];
        util::send_result($data);
    }
}