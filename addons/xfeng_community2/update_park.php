<?php
/**
 * Created by njxcommunity.com.
 * User: 蓝牛科技
 * Time: 2017/9/22 下午10:39
 */

require '../../framework/bootstrap.inc.php';
require '../../addons/xfeng_community/model.php';
$i = intval($_GET['i']);
$start = strtotime(date('Ymd')) + 60*60;//1点
$end = strtotime(date('Ymd')) + 68*60*8;//2点
$times = TIMESTAMP;//当前的时间戳
if ($times >= $start && $times <= $end) {
//更新停车场
    $companies = pdo_getall('xcommunity_stop_park_setting', array(), array());
    if ($companies) {
        foreach ($companies as $key => $val) {
            $token = getAccessToken($val['company_id'], $val['access_secret'], $val['sign_key'], $i);
            $parks = getParks($val['sign_key'], $token);
            if($parks){
                foreach ($parks as $k => $v) {
                    $park = pdo_get('xcommunity_stop_park', array('park_id' => $v['park_id']), array('id'));
                    $data = array(
                        'uniacid' => $val['uniacid'],
                        'parkid' => $val['id'],
                        'park_id' => $v['park_id'],
                        'park_name' => $v['park_name'],
                        'city' => $v['city'],
                        'total_spaces' => $v['total_spaces'],
                        'remain_spaces' => $v['remain_spaces'],
                        'company_id' => $val['company_id'],
                        'createtime' => TIMESTAMP
                    );
                    if ($park) {
                        pdo_update('xcommunity_stop_park', $data, array('id' => $park['id']));
                    } else {
                        pdo_insert('xcommunity_stop_park', $data);
                    }
                }
            }

        }
    }
//更新月租车
    $parks = pdo_getall('xcommunity_stop_park',array() , array());
    if ($parks) {
        foreach ($parks as $key => $val) {
            $setting = pdo_get('xcommunity_stop_park_setting', array('company_id' => $val['company_id']), array());
            $token = getAccessToken($val['company_id'], $setting['access_secret'], $setting['sign_key'], $i);
            $last_time = $val['last_time'] ? $val['last_time'] : date('Y-m-d H:i:s',TIMESTAMP);
            $cars = getAllCars($setting['sign_key'], $val['park_id'], $token, $last_time);
            pdo_update('xcommunity_stop_park', array('last_time' => $last_time), array('id' => $val['id']));
            if ($cars) {
                foreach ($cars as $k => $v) {
                    $car = pdo_get('xcommunity_stop_park_cars', array('card_no' => $v['card_no']), array('id'));
                    $data = array(
                        'uniacid' => $val['uniacid'],
                        'id_card' => $val['id_card'],
                        'card_no' => $v['card_no'],
                        'card_name' => $v['card_name'],
                        'issued_by' => $v['issued_by'],
                        'issued_date' => strtotime($v['issued_date']),
                        'renew_date' => strtotime($v['renew_date']),
                        'start_date' => strtotime($v['start_date']),
                        'valid_date' => strtotime($v['valid_date']),
                        'deposit' => $v['deposit'],
                        'month_fee' => $v['month_fee'],
                        'car_no' => $v['car_no'],
                        'car_type' => $v['car_type'],
                        'car_pos' => $v['car_pos'],
                        'realname' => $v['name'],
                        'address' => $v['address'],
                        'phone' => $v['phone'],
                        'mobile' => $v['mobile'],
                        'remark' => $v['remark'],
                        'card_status' => $v['card_status'],
                        'park_id' => $val['id'],
                        'createtime' => TIMESTAMP
                    );
                    if ($car) {
                        pdo_update('xcommunity_stop_park_cars', $data, array('id' => $car['id']));
                    } else {
                        pdo_insert('xcommunity_stop_park_cars', $data);
                    }
                }
            }

        }
    }
}




