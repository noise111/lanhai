<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/9/22 下午10:40
 */
require '../../framework/bootstrap.inc.php';

//$i = intval($_GET['i']);
//查配置
$sets = pdo_getall('xcommunity_stop_park_set', array(), array());

if($sets){
    foreach ($sets as $key => $set){
        if ($set['status']) {
            $sql = "select id,valid_date,openid,car_no,month_fee from".tablename('xcommunity_stop_park_cars')."where uniacid=:uniacid and uid !=''";
            $cars = pdo_fetchall($sql,array(':uniacid' => $set['uniacid']));
//            print_r($cars);exit();
            //$cars = pdo_getall('lanniu_stop_park_cars', $condition, array('id', 'valid_date', 'openid', 'car_no'));
            $time = strtotime(date('Ymd'));
            if($cars){
                foreach ($cars as $k => $v) {
                    $count = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_stop_park_car_record') . "where car_no=:car_no", array(':car_no' => $v['car_no']));

                    if ($count < $set['remind_num']) {
                        if (($time < $v['valid_date'] + $set['arrears_num'] * 86400) || ($time > $v['valid_date'] + $set['expire_num'] * 86400)) {
                            $record = pdo_get('xcommunity_stop_park_car_record', array('car_no' => $v['car_no']), array('createtime'));
                            if (TIMESTAMP > $record['createtime'] + $set['tjtime'] * 60*60) {
                                $url = $_SERVER['HTTP_HOST']."/app/index.php?i={$set['uniacid']}&c=entry&op=index&do=zhpark&m=xfeng_community";
                                $content = array(
                                    'first' => array(
                                        'value' => '您好，车牌号'.$v['car_no'].'月费即将到期或已欠费',
                                    ),
                                    'keyword1' => array(
                                        'value' => $v['month_fee'].'元/月',
                                    ),
                                    'keyword2' => array(
                                        'value' => date('Y-m-d', $v['valid_date']).'到期',
                                    ),
                                    'remark' => array(
                                        'value' => '点开查看详情,即可立即在线续费',
                                    )
                                );
                                load()->classs('weixin.account');
                                $account_api = WeAccount::create($set['uniacid']);
                                $status = $account_api->sendTplNotice($v['openid'], $set['car_tpl'], $content, $url);

                                $dat = array(
                                    'uniacid' => $set['uniacid'],
                                    'createtime' => TIMESTAMP,
                                    'car_no' => $v['car_no']
                                );
                                pdo_insert('xcommunity_stop_park_car_record', $dat);


                            }

                        }
                    }

                }
            }

        }
    }
}


