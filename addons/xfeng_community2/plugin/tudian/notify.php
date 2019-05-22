<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/7/4 上午10:13
 * 充电桩充电完成回调
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
require_once './function.php';
if (!empty($_POST)) {
    $result = json_decode(json_encode($_POST), true);
    $content = json_decode($result['data'], true);
    $time = date('Ymd', time());
    $t = date('YmdHis', time());
    $event = $content[0]['event'];//回调状态
    /**
     * 查询充电桩信息
     */
    $csid = $content[0]['csid'];
    $sql = "select t1.*,t2.quanrule from" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_charging_throw') . "t2 on t1.tid=t2.id where t1.code=:code";
    $charge = pdo_fetch($sql, array(':code' => $csid));
    $uniacid = $charge['uniacid'];
    /**
     * 上电成功
     */
    if ($event == 1) {
        /**
         * 查询订单信息
         */
        $args = $content[0]['args'];
        $order = pdo_get('xcommunity_charging_order', array('logid' => $args,'status'=>1));
        if($order){
            $pid = $content[0]['pid'] + 1;
            /**
             * 修改插座状态为占用
             */
            pdo_update('xcommunity_charging_socket', array('enable' => 1), array('lock' => $content[0]['pid'], 'stationid' => $charge['id']));
            /**
             * 修改充电开始时间
             */
            pdo_update('xcommunity_charging_order', array('starttime' => $content[0]['tm']), array('logid' => $args));
            $tplid = set('p144', '', $uniacid);
            $first = '尊敬的用户，您的充电已开始！';
            $k1 = $charge['address'];
            $k2 = $content[0]['csid'] . '_' . $content[0]['pid'];
            $k3 = $pid . '号插座';
            $k4 = date('Y-m-d H:i', $content[0]['tm']);
            $remark = '谢谢您的使用';
            $openid = $order['openid'];
            $status = sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark);
            echo 'ok';
            die;
        }

    }
    /**
     * 远程断电
     */
    if ($event == 5) {
        /**
         * 查询订单信息
         */
        $args = $content[0]['args'];
        $order = pdo_get('xcommunity_charging_order', array('logid' => $args[0]));
        if($order){
            //判断断电
            /**
             * 获取断电后充电详情
             */
            unset($result);
            $result = getPowerDetail($charge['appid'], $charge['appsecret'], $order['cuid']);
            $cuid = explode("_", $order['cuid']);
            $csid = strlen($cuid[0]) == 6 ? $cuid[0] : '00' . $cuid[0];
            $cuid = $csid . '_' . $cuid[1] . '_' . $cuid[2];
            $result = $result['cu'][$cuid];
            $powers = $result['powers'];
            $power = $powers[0];//随机功率
            $usetime = sprintf("%.2f", ($result['sec'] / 60));
            $endtime = $result['end'];
            $starttime = $result['beg'];
            $dat['endtime'] = $endtime;
            $dat['stime'] = $usetime;
            $dat['power'] = $power;
            if ($order['type'] == 1) {
                //按量计费
                $rules = unserialize($charge['quanrule']);
                $fee = 0;//计费费用
                $minfee = 0;//计费费用
                $mintime = 0;// 最低消费时间
                if ($power <= $rules[1]['power']) {
                    $fee = $rules[1]['price'];
                    $minfee = $rules[1]['minprice'] ? $rules[1]['minprice'] : 0;
                    $mintime = $rules[1]['mintime'] ? $rules[1]['mintime'] : 0;
                } elseif ($power > $rules[1]['power'] && $power <= $rules[2]['power']) {
                    $fee = $rules[2]['price'];
                    $minfee = $rules[2]['minprice'] ? $rules[2]['minprice'] : 0;
                    $mintime = $rules[2]['mintime'] ? $rules[2]['mintime'] : 0;
                } elseif ($power > $rules[2]['power'] && $power <= $rules[3]['power']) {
                    $fee = $rules[3]['price'];
                    $minfee = $rules[3]['minprice'] ? $rules[3]['minprice'] : 0;
                    $mintime = $rules[3]['mintime'] ? $rules[3]['mintime'] : 0;
                }
                if ($minfee > 0) {
                    if ($usetime <= ($mintime * 60)) {
                        $sjfee = $minfee;
                    } else {
                        $difftime = $usetime - $mintime;
                        $sjfee = $minfee + $difftime * ($fee / 60);
                    }
                } else {
                    $sjfee = $usetime * ($fee / 60);//实际消费费用
                }
                $sjfee = $usetime * ($fee / 60);//实际消费费用
                $sjfee = sprintf("%.2f", $sjfee);
                $dat['price'] = $sjfee;
                pdo_update('mc_members', array('chargecredit -=' => $dat['price']), array('uid' => $order['uid']));//修改用户余额
                $member = pdo_get('mc_members', array('uid' => $order['uid']), array('realname', 'mobile'));
                $ordesn = pdo_getcolumn('xcommunity_order', array('id' => $order['orderid']), 'ordersn');
                $creditdata = array(
                    'uid' => $order['uid'],
                    'uniacid' => $order['uniacid'],
                    'realname' => $member['realname'],
                    'mobile' => $member['mobile'],
                    'content' => "订单号：" . $ordesn . ",充电桩:" . $order['code'] . "_" . ($order['socket'] + 1),
                    'credit' => $dat['price'],
                    'creditstatus' => 2,
                    'createtime' => TIMESTAMP,
                    'type' => 7,
                    'typeid' => $order['regionid'],
                    'category' => 7,
                    'usename' => $member['realname']
                );
                pdo_insert('xcommunity_credit', $creditdata);
                pdo_update('xcommunity_order', array('price' => $sjfee, 'status' => 1), array('id' => $order['orderid']));//修改订单金额
            }
            else {
                $sjfee = $order['price'];
            }
            /**
             * 修改充电完成后的信息
             */
            pdo_update('xcommunity_charging_order', $dat, array('logid' => $args[0]));
            /**
             * 修改插座状态为空闲
             */
            $socket = pdo_update('xcommunity_charging_socket', array('enable' => 0), array('stationid' => $charge['id'], 'lock' => $content[0]['pid']));
            $tplid = set('p145', '', $uniacid);
            $first = '尊敬的用户，您的充电已结束！';
            $k1 = $charge['address'];
            $k2 = date('Y-m-d H:i', $order['starttime']) . '至' . date('Y-m-d H:i', $endtime);
            $k3 = $usetime . '分钟';
            $k4 = $sjfee . '元';
            $remark = '谢谢您的使用';
            $openid = $order['openid'];
            sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark);
            echo 'ok';
            die;
        }

    }
    /**
     * 插座维护
     */
    if ($event == 7) {

    }

}
function sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark)
{
    load()->classs('weixin.account');
    $account = pdo_get('account', array('uniacid' => $uniacid));
    $account_api = WeAccount::create($account['acid']);
    $data = array(
        'first' => array(
            'value' => $first
        ),
        'keyword1' => array(
            'value' => $k1,
        ),
        'keyword2' => array(
            'value' => $k2,
        ),
        'keyword3' => array(
            'value' => $k3,
        ),
        'keyword4' => array(
            'value' => $k4,
        ),
        'remark' => array(
            'value' => $remark,
        )
    );
    $status = $account_api->sendTplNotice($openid, $tplid, $data, $url = '');
    return $status;
}

