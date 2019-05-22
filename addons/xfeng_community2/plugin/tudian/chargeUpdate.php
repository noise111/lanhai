<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/10 下午1:35
 */
/**
 * 实时监测功率、查询按量计费
 */
require_once './function.php';
require '../../../../framework/bootstrap.inc.php';
$sql = "select t1.*,t3.quanrule,t4.chargecredit,t2.appid,t2.appsecret from".tablename('xcommunity_charging_order')."t1 left join".tablename('xcommunity_charging_station')."t2 on t2.code=t1.code left join".tablename('xcommunity_charging_throw')."t3 on t3.id=t2.tid left join".tablename('mc_members')."t4 on t4.uid=t1.uid where t1.type=1 and t1.endtime=0 and t1.status=1";
$orders = pdo_fetchall($sql);
if($orders){
    foreach ($orders as $order){
        if(empty($order['power'])){
            //按量计费
            if($order['chargecredit'] <=0){
                //判断是否欠费,欠费断电
                getPowerDown($order['appid'], $order['appsecret'], $order['cuid']);
                $message = array(
                    'msgtype' => 'text',
                    'text' => array('content' => '您已欠费，请先充值后，在充电。'),
                    'touser' => $order['openid'],
                );
                $account_api = WeAccount::create();
                $status = $account_api->sendCustomNotice($message);
                exit();
            }
            /**
             * 需要判断当前用户余额是否大于用户选择的时间
             */
            //查功率
            $result = getPowerDetail($order['appid'], $order['appsecret'], $order['cuid']);
            $cuid = split("_", $order['cuid']);
            $csid = strlen($cuid[0]) == 6 ? $cuid[0] : '00' . $cuid[0];
            $cuid = $csid . '_' . $cuid[1] . '_' . $cuid[2];
            $result = $result['cu'][$cuid];
            $powers = $result['powers'];
            $power = $powers[0];//随机功率
            $usetime = sprintf("%.2f",($result['sec'] / 60));
            $rules = unserialize($order['quanrule']);
            $fee = 0;//计费费用
            if ($power <= $rules[1]['power']) {
                $fee = $rules[1]['price'];
            }
            elseif ($power > $rules[1]['power'] && $power <= $rules[2]['power']) {
                $fee = $rules[2]['price'];
            }
            elseif ($power > $rules[2]['power'] && $power <= $rules[3]['power']) {
                $fee = $rules[3]['price'];
            }
            $sjfee = $usetime * ($fee/60);//实际消费费用
            if ($order['chargecredit'] < $sjfee) {
                //修改实际充电时间
                $seconds = ($order['chargecredit'] / $fee) * 3600-$result['sec'];
                $content = updatePower($order['appid'], $order['appsecret'], $order['cuid'], $seconds);
            }
            pdo_update('xcommunity_charging_order', array('power'=>$power), array('id' => $order['id']));
        }
    }
}

