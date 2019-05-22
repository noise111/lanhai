<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/3/31 下午3:35
 */
global $_W,$_GPC;
if(empty($_W['member']['mobile'])){
    $url = $this->createMobileUrl('mc');
    header("Location:$url");exit();
}
if(checksubmit('submit')){
    if(checksubmit()) {
        $fee = floatval($_GPC['fee']);
        $backtype = trim($_GPC['backtype']);
        $back= floatval($_GPC['back']);
        if (empty($fee) || $fee <= 0) {
            message('请选择充值金额', referer(), 'error');
        }
        $chargerecord = array(
            'uid' => $_W['member']['uid'],
            'openid' => $_W['openid'],
            'uniacid' => $_W['uniacid'],
            'tid' => date('YmdHi').random(8, 1),
            'fee' => $fee,
            'type' => 'credit',
            'tag' => $back,
            'backtype' => $backtype,
            'status' => 0,
            'createtime' => TIMESTAMP,
        );
        if (!pdo_insert('mc_credits_recharge', $chargerecord)) {
            message('创建充值订单失败，请重试！', $this->createMobileUrl('home'), 'error');
        }
        $params = array(
            'tid' => $chargerecord['tid'],
            'ordersn' => $chargerecord['tid'],
            'title' => '会员余额充值',
            'fee' => $chargerecord['fee'],
            'user' => $_W['member']['uid'],
            'module'  => $this->module['name']
        );


        $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        $pars[':module'] = $params['module'];
        $pars[':tid'] = $params['tid'];
        $log = pdo_fetch($sql, $pars);
        if (!empty($log) && $log['status'] == '1') {
            message('这个订单已经支付成功, 不需要重复支付.');
        }
        $log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
        if (empty($log)) {
            $log = array(
                'uniacid'    => $_W['uniacid'],
                'acid'       => $_W['acid'],
                'openid'     => $_W['member']['uid'],
                'module'     => $this->module['name'],
                'tid' => $params['tid'],
                'fee'        => $params['fee'],
                'card_fee'   => $params['fee'],
                'status'     => '0',
                'is_usecard' => '0',
            );
            pdo_insert('core_paylog', $log);
        }

        $api = pdo_get('xcommunity_swiftpass', array('userid' => 1,'type'=>2), array());
        if (empty($api)) {
            itoast('没有设置支付接口', referer(), 'error');
            exit();
        }
        $service = 'pay.weixin.jspay';
        $mch_id = $api['account'];
        $is_raw = '1';
        $out_trade_no = $params['tid'];
        $body = $params['title'];//
        $total_fee = $params['fee'] * 100;
        $mch_create_ip = $_W['clientip'];
        $notify_url = $_W['siteroot'] . "addons/xfeng_community/payment/swiftpass/notify.php";
        $nonce_str = getRandom();
        $key = $api['secret'];
        $data = array(
            'service'       => $service,
            'mch_id'        => $mch_id,
            'is_raw'        => $is_raw,
            'out_trade_no'  => $out_trade_no,
            'body'          => $body,
            'total_fee'     => $total_fee,
            'mch_create_ip' => $mch_create_ip,
            'notify_url'    => $notify_url,
            'nonce_str'     => $nonce_str,
            'sub_appid'     => $api['appid'],
            'sub_openid'    => $params['payopenid'] ? $params['payopenid'] : $_W['openid'],
        );
        $data['sign'] = createSign($data, $key);
        $sl = base64_encode(json_encode($data));
        $auth = sha1($sl . $_W['uniacid'] . $key);
        $url = $this->createMobileUrl('swiftpay', array('i' => $_W['uniacid'], 'auth' => $auth, 'ps' => $sl, 'key' => $key));
        header("location:" . $url);
        exit();
        exit();
    }
}
include $this->template($this->xqtpl('recharge'));