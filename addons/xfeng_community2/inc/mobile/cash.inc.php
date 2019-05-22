<?php
/**
 * Created by 小区秘书.
 * User: 蓝牛科技
 * Date: 2016/11/14
 * Time: 下午7:33
 * Function:
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;

$type = in_array($_GPC['op'], array('wechat', 'sub', 'alipay', 'cmbc', 'chinaums', 'swiftpass', 'hsyunfu','syswechat')) ? $_GPC['op'] : '';
$paytype = intval($_GPC['type']);
$moduels = uni_modules();
$params = @json_decode(base64_decode($_GPC['params']), true);
if (empty($params) || !array_key_exists($params['module'], $moduels)) {
    itoast('访问错误.');
}
if (empty($type)) {
    itoast('支付方式错误,请联系商家', '', 'error');
}
if (!empty($type)) {
    $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
    $pars = array();
    $pars[':uniacid'] = $_W['uniacid'];
    $pars[':module'] = $params['module'];
    $pars[':tid'] = $params['tid'];
    $log = pdo_fetch($sql, $pars);
    if (!empty($log) && ($type != 'credit' && !empty($_GPC['notify'])) && $log['status'] != '0') {
        itoast('这个订单已经支付成功, 不需要重复支付.');
    }
    $update_card_log = array(
        'is_usecard' => '0',
        'card_type'  => '0',
        'card_id'    => '0',
        'card_fee'   => $log['fee'],
        'type'       => $type,
    );
    pdo_update('core_paylog', $update_card_log, array('plid' => $log['plid']));
    $log['is_usecard'] = '0';
    $log['card_type'] = '0';
    $log['card_id'] = '0';
    $log['card_fee'] = $log['fee'];

    $moduleid = pdo_fetchcolumn("SELECT mid FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $params['module']));
    $moduleid = empty($moduleid) ? '000000' : sprintf("%06d", $moduleid);

    $record = array();
    $record['type'] = $type;
    if (empty($log['uniontid'])) {
        $record['uniontid'] = $log['uniontid'] = date('YmdHis') . $moduleid . random(8, 1);
    }
    if ($type != 'delivery') {
        $coupon_id = intval($_GPC['coupon_id']);
        $coupon_info = pdo_get('coupon', array('uniacid' => $_W['uniacid'], 'id' => $coupon_id));
        $coupon_info['fee'] = $log['card_fee'];
        if (!empty($coupon_info)) {
            $extra = iunserializer($coupon_info['extra']);
            if ($coupon_info['type'] == COUPON_TYPE_DISCOUNT) {
                $coupon_info['fee'] = sprintf("%.2f", ($log['fee'] * ($extra['discount'] / 100)));
            }
            elseif ($coupon_info['type'] == COUPON_TYPE_CASH) {
                if ($log['fee'] >= $extra['least_cost'] * 0.01) {
                    $coupon_info['fee'] = sprintf("%.2f", ($log['fee'] - $extra['reduce_cost'] / 100));
                }
            }
            if (!empty($_GPC['code']) && !empty($_GPC['coupon_id'])) {
                $record['is_usecard'] = 1;
                $record['card_fee'] = $coupon_info['fee'];
                $record['encrypt_code'] = trim($_GPC['code']);
                if (COUPON_TYPE == WECHAT_COUPON) {
                    $record['card_type'] = 1;
                    $record['card_id'] = $coupon_info['id'];
                }
                else {
                    $record['card_type'] = 2;
                    $record['card_id'] = $coupon_info['id'];
                }
            }
        }
    }
    if (empty($log)) {
        itoast('系统支付错误, 请稍后重试.');
    }
    else {
        pdo_update('core_paylog', $record, array('plid' => $log['plid']));
        if (!empty($log['uniontid']) && $record['card_fee']) {
            $log['card_fee'] = $record['card_fee'];
            $log['card_id'] = $record['card_id'];
            $log['card_type'] = $record['card_type'];
            $log['is_usecard'] = $record['is_usecard'];
        }
    }
    $ps = array();
    $ps['tid'] = $log['plid'];
    $ps['uniontid'] = $log['uniontid'];
    $ps['user'] = $_W['fans']['from_user'];
    $ps['fee'] = $log['card_fee'];
    $ps['title'] = $params['title'];
    $ps['userid'] = $params['userid'];
    $ps['payopenid'] = $params['payopenid'];
    $ps['type'] = $params['type'];
    if ($type == 'alipay') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['pay_type'] = $params['pay_type'];
            $tag['type'] = $params['type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        load()->model('payment');
        load()->func('communication');
        //支付接口 $paytype = 1 小区活动/缴费 $paytype == 2 超市/商家
        $api = pdo_get('xcommunity_alipayment', array('userid' => $params['userid']), array());
        if (empty($api)) {
            itoast('没有设置支付接口', referer(), 'error');
            exit();
        }
        $alipay = array(
            'switch'  => 1,
            'account' => $api[account],
            'partner' => $api[partner],
            'secret'  => $api[secret],
        );
        $ret = alipay_build($ps, $alipay);
        if ($ret['url']) {
            echo '<script type="text/javascript" src="../payment/alipay/ap.js"></script><script type="text/javascript">_AP.pay("' . $ret['url'] . '")</script>';
            exit();
        }
    }
    if ($type == 'wechat') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['_type'] = 'wechat';
            $tag['pay_type'] = $params['pay_type'];
            $tag['type'] = $params['type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        $ps['title'] = urlencode($params['title']);
        load()->model('payment');
        load()->func('communication');
        $sl = base64_encode(json_encode($ps));
        $auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
        $url = $this->createMobileUrl('pay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'type' => 'wechat'));
        header("location:" . $url);
        exit();
    }
    if ($type == 'syswechat') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $params['uid'];
            $tag['userid'] = $params['userid'];
            $tag['_type'] = 'syswechat';
            $tag['pay_type'] = '11';
            $tag['type'] = $params['type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        $ps['title'] = urlencode($params['title']);
        load()->model('payment');
        load()->func('communication');
        $sl = base64_encode(json_encode($ps));
        $auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
        $url = $this->createMobileUrl('pay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'type' => 'syswechat'));
        header("location:" . $url);
        exit();
    }
    if ($type == 'sub') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['_type'] = 'sub';
            $tag['pay_type'] = $params['pay_type'];
            $tag['type'] = $params['type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        $ps['title'] = urlencode($params['title']);
        load()->model('payment');
        load()->func('communication');
        $sl = base64_encode(json_encode($ps));
        $auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
        $url = $this->createMobileUrl('pay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'type' => 'sub'));
        header("location:" . $url);
        exit();
    }
    if ($type == 'cmbc') {
        if (!empty($plid)) {
            pdo_update('core_paylog', array('openid' => $_W['member']['uid']), array('plid' => $plid));
        }
        load()->model('payment');
        load()->func('communication');
        //支付接口
        $ap = pdo_get('xcommunity_pay_api', array('uid' => $params['uid'], 'paytype' => 5, 'type' => 3), array('pay'));
        if ($ap) {
            $api = $ap;
        }
        else {
            if ($paytype == 1) {
                $api = pdo_get('xcommunity_pay_api', array('cid' => $params['cid'], 'paytype' => 5), array('pay'));
            }
            elseif ($paytype == 2) {
                $api = pdo_get('xcommunity_pay_api', array('userid' => $params['uid'], 'paytype' => 5), array('pay'));
            }
        }
        $pay = unserialize($api['pay']);
        $cmbc = array(
            'platformId'       => $pay[cmbc][platformId],
            'merchantNo'       => $pay[cmbc][merchantNo],
            'merchantName'     => $pay[cmbc][merchantName],
            'amount'           => $ps['fee'] * 100,
            'defaultTradeType' => 'H5_WXJSAPI',
            'orderInfo'        => $ps['title'],
            'merchantSeq'      => $ps['uniontid'],
            'redirectUrl'      => $this->creatMobileUrl('home'),
            'notifyUrl'        => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/payment/cmbc/notifyUrl.php',
            'transDate'        => date("Ymd"),
            'transTime'        => date("Ymdhis"),
        );
        $data = json_encode($cmbc);
        $sy_cert = IA_ROOT . '/attachment/' . $this->module['name'] . '/cert_2/' . $_W['uniacid'] . '/' . $pay[cmbc][sy_cert];
        print_r(file_get_contents($sy_cert));
        $sign = md5(file_get_contents($sy_cert));
        echo $sign;
        exit();
        $content = array(
            'sign' => $sign,
            'body' => $d
        );


        exit();

    }
    if ($type == 'chinaums') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['pay_type'] = $params['pay_type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        $api = pdo_get('xcommunity_chinaums', array('userid' => $params['userid'], 'type' => intval($params['type'])), array());
        if (empty($api)) {
            itoast('没有设置支付接口', referer(), 'error');
            exit();
        }
        $mid = trim($api['mid']);
        $tid = trim($api['tid']);
        $instMid = trim($api['instmid']);
        $msgSrc = trim($api['msgsrc']);
        $msgSrcId = trim($api['msgsrcid']);//
        $key = trim($api['secret']);
        $requestTimestamp = date('Y-m-d H:i:s', TIMESTAMP);
        $merOrderId = $msgSrcId . trim($params['tid']);
        $attachedData = $log['uniontid'];
        $d1 = array(
            'mid'              => $mid,
            'tid'              => $tid,
            'instMid'          => $instMid,
            'msgSrc'           => $msgSrc,
            'requestTimestamp' => date('Y-m-d H:i:s', TIMESTAMP),
            'merOrderId'       => $merOrderId,
            'totalAmount'      => $params['fee'] * 100,
            'notifyUrl'        => $_W['siteroot'] . "addons/xfeng_community/payment/chinaums/notify.php",
            'attachedData'     => $attachedData,
            'returnUrl'        => app_url('home')
        );
        $sign = createSign2(array_filter($d1), $key);
        $data = createChinaums($mid, $tid, $instMid, $msgSrc, $merOrderId, $params['fee'], $attachedData);
        $url = 'https://qr.chinaums.com/netpay-portal/webpay/pay.do?' . $data . "&sign=" . $sign;
        header("location: " . $url);
        exit();
    }
    if ($type == 'swiftpass') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['pay_type'] = $params['pay_type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        //支付接口 $paytype = 1 小区活动/缴费 $paytype == 2 超市/商家
        $api = pdo_get('xcommunity_swiftpass', array('userid' => $params['userid'], 'type' => intval($params['type'])), array());
        if (empty($api)) {
            itoast('没有设置支付接口', referer(), 'error');
            exit();
        }
        $key = $api['secret'];
        $data = array(
            'service'       => 'pay.weixin.jspay',
            'mch_id'        => $api['account'],
            'is_raw'        => '1',
            'out_trade_no'  => $params['tid'],
            'body'          => $params['title'],
            'total_fee'     => $params['fee'] * 100,
            'mch_create_ip' => $_W['clientip'],
            'notify_url'    => $_W['siteroot'] . "addons/xfeng_community/payment/swiftpass/notify.php",
            'nonce_str'     => getRandom(),
            'sub_appid'     => $api['appid'],
            'sub_openid'    => $_W['openid'] ? $_W['openid'] : $params['payopenid'],
        );
        if($api['banktype']==2){
            /**
             * 中信
             */
            $data['sign_type'] = 'RSA_1_256';
            $data['sign'] = createRSASign($data,$api['private_key'],$data['sign_type']);
        }
        else{
            /**
             * 农商行/兴业银行
             */
            $data['sign'] = createSign($data, $key);
        }
        $sl = base64_encode(json_encode($data));
        $auth = sha1($sl . $_W['uniacid'] . $key);
        $url = $this->createMobileUrl('swiftpay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'key' => $key));
        header("location:" . $url);
        exit();
    }
    if ($type == 'hsyunfu') {
        if (!empty($log['plid'])) {
            $tag = array();
            $tag['acid'] = $_W['acid'];
            $tag['uid'] = $_W['member']['uid'];
            $tag['userid'] = $params['userid'];
            $tag['pay_type'] = $params['pay_type'];
            pdo_update('core_paylog', array('openid' => $_W['fans']['from_user'], 'tag' => iserializer($tag)), array('plid' => $log['plid']));
        }
        //支付接口 $paytype = 1 小区活动/缴费 $paytype == 2 超市/商家
        $api = pdo_get('xcommunity_hsyunfu', array('userid' => $params['userid']), array());
        if (empty($api)) {
            itoast('没有设置支付接口', referer(), 'error');
            exit();
        }

        $service = 'pay.weixin.jspay';
        $hsid = $api['account'];
        $is_raw = '1';
        $out_trade_no = $params['tid'];
        $body = $params['title'];//
        $total_fee = strval($params['fee'] * 100);
        $mch_create_ip = $_W['clientip'];
        $notify_url = $_W['siteroot'] . "addons/xfeng_community/payment/hsyunfu/notify.php";
        $nonce_str = getRandom();
        $key = $api['secret'];
        $data = array(
            'service'       => $service,
            'hsid'          => $hsid,
            'is_raw'        => $is_raw,
            'out_trade_no'  => $out_trade_no,
            'body'          => $body,
            'total_fee'     => $total_fee,
            'mch_create_ip' => $mch_create_ip,
            'notify_url'    => $notify_url,
            'nonce_str'     => $nonce_str,
            'sub_appid'     => $params['_appid'],
            'sub_openid'    => $params['_payopenid'],

        );
        $data['sign'] = createSign($data, $key);
        $sl = base64_encode(json_encode($data));
        $auth = sha1($sl . $_W['uniacid'] . $key);
        $url = $this->createMobileUrl('hsyunfupay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'key' => $key));
        header("location:" . $url);
        exit();
    }
}
