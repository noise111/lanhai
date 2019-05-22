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
if(!empty($_POST)){
    $result = json_decode(json_encode($_POST),true);
    $content = json_decode($result['data'],true);
    $time = date('Ymd',time());
    $t = date('YmdHis',time());
    if($content[0]['event']==1){
        //判断上电成功
        $csid = $content[0]['csid'];
        $charge = pdo_get('xcommunity_charging_station',array('code'=>$csid));
        $pid = $content[0]['pid']+1;
        pdo_update('xcommunity_charging_socket', array('enable' => 1), array('lock' => $content[0]['pid'], 'stationid' => $charge['id']));
        pdo_update('xcommunity_charging_order', array('starttime' => $content[0]['tm']), array('logid' => $content[0]['args']));
        $openid = pdo_getcolumn('xcommunity_charging_order',array('logid' => $content[0]['args']),'openid');
        $sql = "select * from" . tablename('xcommunity_setting') . "where uniacid=:uniacid and xqkey =:xqkey";
        $set = pdo_fetch($sql, array(':uniacid' => $charge['uniacid'],':xqkey' => 'p144'));
        $p144 = $set['value'];
        $postdata = array(
            'first' => array(
                'value' => '尊敬的用户，您的充电已开始！'
            ),
            'keyword1' => array(
                'value' => $charge['address'],
            ),
            'keyword2'	=> array(
                'value' => $content[0]['csid'].'_'.$content[0]['pid'],
            ),
            'keyword3'    => array(
                'value' => $pid.'号插座',
            ),
            'keyword4'    => array(
                'value' => date('Y-m-d H:i',$content[0]['tm']),
            ),
            'remark'    => array(
                'value' => '谢谢您的使用！',
            )
        );
        load()->classs('weixin.account');
        $account = pdo_get('account',array('uniacid'=>$charge['uniacid']));
        $account_api = WeAccount::create($account['acid']);
        $status = $account_api->sendTplNotice($openid, $p144, $postdata, $url='');
    }
    if($content[0]['event']==5){
        //判断断电
        $csid = $content[0]['csid'];
        $charge = pdo_get('xcommunity_charging_station',array('code'=>$csid));
        $socket = pdo_update('xcommunity_charging_socket',array('enable' => 0),array('stationid'=>$charge['id'],'lock' => $content[0]['pid']));
        $args = $content[0]['args'];
        $order = pdo_get('xcommunity_charging_order',array('logid' => $args[0]));
        if($order['type']==1){
            //按量计费
        }elseif($order['type']==2){
            //按时计费
        }
        $sql = "select * from" . tablename('xcommunity_setting') . "where uniacid=:uniacid and xqkey =:xqkey";
        $set = pdo_fetch($sql, array(':uniacid' => $charge['uniacid'],':xqkey' => 'p145'));
        $p145 = $set['value'];
        $stime = $content[0]['tm'] - $order['starttime'];
        $xtime = $stime/3600;
        $xtime = sprintf("%.2f",$xtime);
        $postdata = array(
            'first' => array(
                'value' => '尊敬的用户，您的充电已结束！'
            ),
            'keyword1' => array(
                'value' => $charge['address'],
            ),
            'keyword2'	=> array(
                'value' => date('Y-m-d H:i',$order['starttime']).'至'.date('Y-m-d H:i',$content[0]['tm']),
            ),
            'keyword3'    => array(
                'value' => $xtime.'小时',
            ),
            'keyword4'    => array(
                'value' => $order['price'].'元',
            ),
            'remark'    => array(
                'value' => '谢谢您的使用！',
            )
        );
        load()->classs('weixin.account');
        $account = pdo_get('account',array('uniacid'=>$charge['uniacid']));
        $account_api = WeAccount::create($account['acid']);
        $status = $account_api->sendTplNotice($order['openid'], $p145, $postdata, $url='');
        pdo_update('xcommunity_charging_order', array('endtime' => $content[0]['tm'],'stime' => $stime,'power' => $args[2]), array('logid' => $args[0]));
    }
}

