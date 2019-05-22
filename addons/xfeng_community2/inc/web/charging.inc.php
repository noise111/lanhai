<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/22 0022
 * Time: 下午 2:12
 */
global $_W, $_GPC;
$ops = array('cloud', 'setting', 'set', 'station', 'socket', 'price', 'order', 'qr', 'qrpl', 'download', 'throw', 'payapi', 'rules', 'credit', 'change', 'slide', 'comm', 'fault', 'qrlist', 'qrdel', 'creditdel', 'users', 'creditLog', 'data', 'manage', 'chongdian');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 充电桩的设置、充值说明
 */
if ($op == 'cloud' || $op == 'setting' || $op == 'set' || $op == 'chongdian') {
    if (checksubmit('submit')) {
        $set = $_GPC['set'];
        foreach ($set as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key, 'uniacid' => $_W['uniacid']), array('id'));
            $value = $key == 'p153' ? htmlspecialchars_decode($val) : $val;
            $data = array(
                'xqkey'   => $key,
                'value'   => $value,
                'uniacid' => $_W['uniacid']
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            } else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success');
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/plugin/charging/set');
}
/**
 * 充电桩的管理
 */
if ($op == 'station') {
    /**
     * 充电桩的列表
     */
    if ($p == 'list') {
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $id => $displayorder) {
                pdo_update('xcommunity_charging_station', array('displayorder' => $displayorder), array('id' => $id));
            }
            itoast('排序更新成功！', referer(), 'success', true);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition['title'] = "%{$_GPC['keyword']}%";
        }
        $chargings = pdo_getslice('xcommunity_charging_station', $condition, array($pindex, $psize), $total, '', '', array('displayorder asc', 'id desc'));
        // 小区
        $chargings_rids = _array_column($chargings, 'regionid');
        $regions = pdo_getall('xcommunity_region', array('id' => $chargings_rids), array('id', 'title'), 'id');
        // 运营商
        $chargings_tids = _array_column($chargings, 'tid');
        $throws = pdo_getall('xcommunity_charging_throw', array('id' => $chargings_tids), array('id', 'title'), 'id');
        $list = array();
        foreach ($chargings as $k => $v) {
            $list[] = array(
                'id'      => $v['id'],
                'title'   => $v['title'],
                'rtitle'  => $regions[$v['regionid']]['title'],
                'ttitle'  => $throws[$v['tid']]['title'],
                'code'    => $v['code'],
                'address' => $v['address'],
                'line'    => $v['line'],
                'enable'  => $v['enable']
            );
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/charging/list');
    }
    /**
     * 充电桩的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_charging_station', array('id' => $id));
            $advpic = unserialize($item['advpic']);
        }
        if ($_W['isajax']) {
            $advpics = array(
                'advone' => intval($_GPC['advone']),
                'advtwo' => intval($_GPC['advtwo']),
                'picone' => $_GPC['picone'],
                'pictwo' => $_GPC['pictwo']
            );
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'regionid'   => intval($_GPC['regionid']),
                'line'       => intval($_GPC['line']),
                'title'      => trim($_GPC['title']),
                'code'       => $_GPC['code'],
                'tid'        => intval($_GPC['tid']),
                'createtime' => TIMESTAMP,
                'address'    => trim($_GPC['address']),
                'lng'        => $_GPC['baidumap']['lng'],
                'lat'        => $_GPC['baidumap']['lat'],
                'remark'     => $_GPC['remark'],
                'type'       => intval($_GPC['type']),
                'appid'      => trim($_GPC['appid']),
                'appsecret'  => trim($_GPC['appsecret']),
                'zscode'     => $_GPC['zscode'],
                'advpic'     => serialize($advpics)
            );

            if ($id) {
                pdo_update('xcommunity_charging_station', $data, array('id' => $id));
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_charging_station', $data);
                $mid = pdo_insertid();
                for ($i = 0; $i < $_GPC['line']; $i++) {
                    $dat = array(
                        'uniacid'    => $_W['uniacid'],
                        'stationid'  => $mid,
                        'lock'       => $i,
                        'createtime' => TIMESTAMP
                    );
                    pdo_insert('xcommunity_charging_socket', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $regions = model_region::region_fetall();
        $throws = pdo_getall('xcommunity_charging_throw', array('uniacid' => $_W['uniacid']));
        $options = array();
        $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
        include $this->template('web/plugin/charging/add');
    }
    /**
     * 充电桩的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数', referer(), 'error');
            }
            $item = pdo_fetch("SELECT id,title FROM" . tablename('xcommunity_charging_station') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if (empty($item)) {
                itoast('该信息不存在或已被删除', referer(), 'error', ture);
            }
            $r = pdo_delete("xcommunity_charging_station", array('id' => $id));
            pdo_delete("xcommunity_charging_socket", array('stationid' => $id));
            if ($r) {
                util::permlog('充电桩-删除', '删除充电桩名称:' . $item['title']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
/**
 * 充电桩的插座
 */
if ($op == 'socket') {
    $stationid = intval($_GPC['stationid']);
    if ($p == 'list') {
        $condition = 't1.uniacid=:uniacid and t1.stationid=:stationid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':stationid'] = $stationid;
        $list = pdo_fetchall("SELECT t1.* FROM" . tablename('xcommunity_charging_socket') . "t1 WHERE $condition order by t1.lock asc", $params);
        include $this->template('web/plugin/charging/socket_list');
    }
    /**
     * 手动释放
     */
    if ($p == 'close') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_update('xcommunity_charging_socket', array('enable' => 0), array('id' => $id))) {
                itoast('修改成功', referer(), 'success');
            }
        }

    }
}
/**
 * 充电桩的费用（已废弃）
 */
if ($op == 'price') {
    $throws = pdo_getall('xcommunity_charging_throw', array('uniacid' => $_W['uniacid']));
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['tid'])) {
            $condition .= " AND t1.tid = :tid";
            $params[':tid'] = intval($_GPC['tid']);
        }
        $list = pdo_fetchall("SELECT t1.*,t2.title as ttitle FROM" . tablename('xcommunity_charging_price') . "t1 left join" . tablename('xcommunity_charging_throw') . "t2 on t1.tid=t2.id WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_charging_price') . "t1 left join" . tablename('xcommunity_charging_throw') . "t2 on t1.tid=t2.id WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        $regions = model_region::region_fetall();
        include $this->template('web/plugin/charging/price_list');
    } elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_charging_price', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'tid'        => intval($_GPC['tid']),
//                'cdtime'   => $_GPC['cdtime'],
                'type'       => intval($_GPC['type']),
                'price'      => trim($_GPC['price']),
                'createtime' => TIMESTAMP
            );
            if ($_GPC['type'] == 1) {
                $data['cdtime'] = $_GPC['cdtime'];
            } elseif ($_GPC['type'] == 2) {
                $data['power'] = $_GPC['power'];
            }
            if ($id) {
                pdo_update('xcommunity_charging_price', $data, array('id' => $id));
            } else {
                pdo_insert('xcommunity_charging_price', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $regions = model_region::region_fetall();
        include $this->template('web/plugin/charging/price_add');
    } elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数', referer(), 'error');
            }
            $item = pdo_fetch("SELECT id FROM" . tablename('xcommunity_charging_price') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if (empty($item)) {
                itoast('该信息不存在或已被删除', referer(), 'error', ture);
            }
            $r = pdo_delete("xcommunity_charging_price", array('id' => $id));
            if ($r) {
                util::permlog('充电桩价格-删除', '删除充电桩价格id:' . $item['id']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
/**
 * 充电桩的二维码
 */
if ($op == 'qr') {
    $id = intval($_GPC['id']);
    $time = TIMESTAMP;
    $item = pdo_get('xcommunity_charging_socket', array('id' => $id));
    $code = pdo_getcolumn('xcommunity_charging_station', array('id' => $item['stationid']), 'code');
//    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=list&do=charging&m=" . $this->module['name'] . "#/detail/" . $id;//二维码内容
    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&socketid={$id}&op=detail&do=charging&m=" . $this->module['name'] . "&t=" . $time;
    $lock = $item['lock'] + 1;
    $temp = $code . "-" . $lock . ".png";
    $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/charging/" . $_W['uniacid'] . "/" . $code . "/";
    $image = createQr($url, $temp, $tmpdir);
    echo $image;
    exit();
}
/**
 * 充电桩批量生成插座二维码
 */
if ($op == 'qrpl') {
    $list = pdo_fetchall("SELECT t1.* FROM" . tablename('xcommunity_charging_station') . "t1 WHERE t1.uniacid=:uniacid order by t1.createtime desc ", array(':uniacid' => $_W['uniacid']));
    foreach ($list as $k => $v) {
        $sockets = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_charging_socket') . "t1 WHERE uniacid=:uniacid and stationid=:stationid order by t1.lock asc", array(':uniacid' => $_W['uniacid'], ':stationid' => $v['id']));
        foreach ($sockets as $key => $val) {
            $time = TIMESTAMP;
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&socketid={$val['id']}&op=detail&do=charging&m=" . $this->module['name'] . "&t=" . $time;
            $lock = $val['lock'] + 1;
            $temp = $v['code'] . "-" . $lock . ".png";
            $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/charging/" . $_W['uniacid'] . "/" . $v['code'] . "/";
            $qr = createQr($url, $temp, $tmpdir);
        }
    }
    itoast('更新成功', $this->createWebUrl('charging', array('op' => 'qrlist')), 'success');
}
/**
 * 充电桩插座二维码下载
 */
if ($op == 'download') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_charging_station', array('id' => $id));
    }
    $dir = MODULE_ROOT . '/data/qrcode/charging/' . $_W['uniacid'] . '/' . $item['code'] . "/";
    $data = list_dir($dir);
    $path = "./" . time() . ".zip"; //最终生成的文件名（含路径）
    download($data, $path);
}
/**
 * 充电桩的运营商
 */
if ($op == 'throw') {
    /**
     * 充电桩运营商的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = trim($_GPC['keyword']);
        if ($keyword) {
            $condition .= " and (t1.mobile like :keyword or t1.realname like :keyword or t1.title like :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }
        $list = pdo_fetchall("SELECT t1.* FROM" . tablename('xcommunity_charging_throw') . "t1 WHERE $condition order by t1.status desc ,t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_charging_price') . "t1 WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/charging/throw/list');
    }
    /**
     * 运营商的添加
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_charging_throw', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => trim($_GPC['title']),
                'realname'   => trim($_GPC['realname']),
                'mobile'     => trim($_GPC['mobile']),
                'address'    => trim($_GPC['address']),
                'createtime' => TIMESTAMP,
                'status'     => 0,
                'desc'       => $_GPC['desc']
            );
            if ($id) {
                pdo_update('xcommunity_charging_throw', $data, array('id' => $id));
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_charging_throw', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/charging/throw/add');
    }
    /**
     * 运营商的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数', referer(), 'error');
            }
            $item = pdo_fetch("SELECT id FROM" . tablename('xcommunity_charging_throw') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if (empty($item)) {
                itoast('该信息不存在或已被删除', referer(), 'error', ture);
            }
            $r = pdo_delete("xcommunity_charging_throw", array('id' => $id));
            if ($r) {
                pdo_delete("xcommunity_alipayment", array('userid' => $id, 'type' => 7));
                pdo_delete("xcommunity_wechat", array('userid' => $id, 'type' => 7));
                pdo_delete("xcommunity_service_data", array('userid' => $id, 'type' => 7));
                pdo_delete("xcommunity_swiftpass", array('userid' => $id, 'type' => 7));
                pdo_delete("xcommunity_hsyunfu", array('userid' => $id, 'type' => 7));
                pdo_delete("xcommunity_chinaums", array('userid' => $id, 'type' => 7));
                util::permlog('充电桩价格-删除', '删除充电桩投放id:' . $item['id']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
/**
 * 充电桩的支付接口配置
 */
if ($op == 'payapi') {
    $tid = intval($_GPC['tid']);
    /**
     * 支付接口的配置
     */
    if ($p == 'list') {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
        include $this->template('web/plugin/charging/payapi/list');
    }
    /**
     * 支付接口的配置-支付宝
     */
    if ($p == 'alipay') {
        $item = pdo_get('xcommunity_alipayment', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type'    => 7,
                'account' => $_GPC['account'],
                'partner' => $_GPC['partner'],
                'secret'  => $_GPC['secret'],
                'userid'  => $tid
            );
            if ($item) {
                pdo_update('xcommunity_alipayment', $data, array('userid' => $tid, 'type' => 7));
                util::permlog('', '修改支付宝ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_alipayment', $data);
                $id = pdo_insertid();
                util::permlog('', '添加支付宝ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/alipay');
    }
    /**
     * 支付接口的配置-微信
     */
    if ($p == 'wechat') {
        $item = pdo_get('xcommunity_wechat', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'appid'     => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'mchid'     => $_GPC['mchid'],
                'apikey'    => $_GPC['apikey'],
                'type'      => 7,
                'userid'    => $tid
            );
            if ($item) {
                pdo_update('xcommunity_wechat', $data, array('userid' => $tid, 'type' => 7));
                util::permlog('', '修改借用支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_wechat', $data);
                $id = pdo_insertid();
                util::permlog('', '添加借用支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/wechat');
    }
    /**
     * 支付接口的配置-服务商
     */
    if ($p == 'sub') {
        $item = pdo_get('xcommunity_service_data', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'sub_id'     => $_GPC['sub_id'],
                'apikey'     => $_GPC['apikey'],
                'appid'      => $_GPC['appid'],
                'appsecret'  => $_GPC['appsecret'],
                'sub_mch_id' => $_GPC['sub_mch_id'],
                'type'       => 7,
                'userid'     => $tid
            );
            if ($item) {
                pdo_update('xcommunity_service_data', $data, array('id' => $id));
                util::permlog('', '修改子商户ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_service_data', $data);
                $id = pdo_insertid();
                util::permlog('', '添加子商户ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/sub');
    }
    /**
     * 支付接口的配置-威富通
     */
    if ($p == 'swiftpass') {
        $item = pdo_get('xcommunity_swiftpass', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'type'        => 7,
                'account'     => trim($_GPC['account']),
                'secret'      => trim($_GPC['secret']),
                'appid'       => trim($_GPC['appid']),
                'appsecret'   => trim($_GPC['appsecret']),
                'userid'      => $tid,
                'private_key' => trim($_GPC['private_key']),
                'banktype'    => intval($_GPC['banktype'])
            );
            if ($item) {
                pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                util::permlog('', '修改威富通微信支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_swiftpass', $data);
                $id = pdo_insertid();
                util::permlog('', '添加威富通微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/swiftpass');
    }
    /**
     * 支付接口的配置-华商云付
     */
    if ($p == 'hsyunfu') {
        $item = pdo_get('xcommunity_hsyunfu', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => 7,
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid'    => $tid
            );
            if ($item) {
                pdo_update('xcommunity_hsyunfu', $data, array('userid' => $tid, 'type' => 7));
                util::permlog('', '修改华商云付微信支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_hsyunfu', $data);
                $id = pdo_insertid();
                util::permlog('', '添加华商云付微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/hsyunfu');
    }
    /**
     * 支付接口的配置-银联
     */
    if ($p == 'chinaums') {
        $item = pdo_get('xcommunity_chinaums', array('userid' => $tid, 'type' => 7), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => 7,
                'mid'      => $_GPC['mid'],
                'tid'      => $_GPC['ctid'],
                'instmid'  => $_GPC['instmid'],
                'msgsrc'   => $_GPC['msgsrc'],
                'msgsrcid' => $_GPC['msgsrcid'],
                'secret'   => $_GPC['secret'],
                'userid'   => $tid
            );
            if ($item) {
                pdo_update('xcommunity_chinaums', $data, array('userid' => $tid, 'type' => 7));
                util::permlog('', '修改银联ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_chinaums', $data);
                $id = pdo_insertid();
                util::permlog('', '添加银联ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/charging/payapi/chinaums');
    }
}
/**
 * 充电记录
 */
if ($op == 'order') {
    /**
     * 充电列表
     */
    if ($p == 'list') {
        $station = pdo_getall('xcommunity_charging_station', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['regionid'])) {
            $condition .= " AND t1.regionid = :regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t2.realname LIKE :keyword or t2.nickname LIKE :keyword)";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
        }
        if (!empty($_GPC['chargingid'])) {
            $condition .= " AND t4.id = :chargingid";
            $params[':chargingid'] = intval($_GPC['chargingid']);
        }
        if ($user && $user['type'] == 3) {
            $condition .= ' and t4.regionid in(:regionid) ';
            $params[':regionid'] = $user['regionid'];
        }
        if ($_GPC['type']) {
            $condition .= ' and t1.type=:type ';
            $params[':type'] = intval($_GPC['type']);
        }
        if ($_GPC['export'] == 1) {
            $list1 = pdo_fetchall("SELECT t1.*,t2.nickname,t5.title,t3.ordersn,t6.title as rtitle FROM" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_order') . "t3 on t3.id=t1.orderid left join" . tablename('xcommunity_charging_station') . "t4 on t1.code=t4.code left join" . tablename('xcommunity_region') . "t5 on t4.regionid=t5.id left join" . tablename('xcommunity_region') . "t6 on t1.regionid=t6.id WHERE $condition order by t1.createtime desc", $params);
            $lists = array();
            foreach ($list1 as $k => $v) {
                $lists[] = array(
                    'ordersn'    => $v['ordersn'],
                    'title'      => $v['title'] ? $v['title'] : $v['rtitle'],
                    'realname'   => $v['realname'] ? $v['realname'] : $v['nickname'],
                    'price'      => $v['price'],
                    'type'       => $v['type'] == 1 ? '按量计费' : '按时计费',
                    'cdtime'     => $v['cdtime'] . '分钟',
                    'stime'      => $v['stime'] ? $v['stime'] . '分钟' : '0分钟',
                    'starttime'  => date('Y-m-d H:i', $v['starttime']),
                    'endtime'    => date('Y-m-d H:i', $v['endtime']),
                    'power'      => $v['power'] . 'W',
                    'code'       => $v['code'] . '_' . ($v['socket'] + 1),
                    'status'     => $v['status'] == 1 ? '已支付' : '未付款',
                    'createtime' => date('Y-m-d H:i', $v['createtime']),
                );
            }
            model_execl::export($lists, array(
                "title"   => "充电数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '订单号',
                        'field' => 'ordersn',
                        'width' => 18
                    ),
                    array(
                        'title' => '小区',
                        'field' => 'title',
                        'width' => 12
                    ),
                    array(
                        'title' => '姓名',
                        'field' => 'realname',
                        'width' => 12
                    ),
                    array(
                        'title' => '费用',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '计费类型',
                        'field' => 'type',
                        'width' => 12
                    ),
                    array(
                        'title' => '应充电时间',
                        'field' => 'cdtime',
                        'width' => 12
                    ),
                    array(
                        'title' => '实际充电',
                        'field' => 'stime',
                        'width' => 12
                    ),
                    array(
                        'title' => '开始时间',
                        'field' => 'starttime',
                        'width' => 18
                    ),
                    array(
                        'title' => '结束时间',
                        'field' => 'endtime',
                        'width' => 18
                    ),
                    array(
                        'title' => '功率',
                        'field' => 'power',
                        'width' => 12
                    ),
                    array(
                        'title' => '充电桩编号',
                        'field' => 'code',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 'status',
                        'width' => 12
                    ),
                    array(
                        'title' => '下单时间',
                        'field' => 'createtime',
                        'width' => 20
                    ),
                )
            ));
        }
        $list = pdo_fetchall("SELECT t1.*,t2.nickname,t5.title,t3.ordersn FROM" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_order') . "t3 on t3.id=t1.orderid left join" . tablename('xcommunity_charging_station') . "t4 on t1.code=t4.code left join" . tablename('xcommunity_region') . "t5 on t4.regionid=t5.id  WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        if ($list) {
            foreach ($list as $k => $v) {
                $totalprice += $v['price'];
            }
        }
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_order') . "t3 on t3.id=t1.orderid left join" . tablename('xcommunity_charging_station') . "t4 on t1.code=t4.code left join" . tablename('xcommunity_region') . "t5 on t4.regionid=t5.id  WHERE $condition order by t1.createtime desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        $regions = model_region::region_fetall();
        include $this->template('web/plugin/charging/order_list');
    }
    /**
     * 删除记录
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_fetch("SELECT id,orderid FROM" . tablename('xcommunity_charging_order') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($item)) {
            itoast('该信息不存在或已被删除', referer(), 'error', ture);
        }
        pdo_delete("xcommunity_order", array('id' => $item['orderid']));
        $r = pdo_delete("xcommunity_charging_order", array('id' => $id));
        if ($r) {
            util::permlog('充电桩记录-删除', '删除充电桩记录id:' . $item['id']);
            $result = array(
                'status' => 1,
            );
            itoast('删除成功', referer(), 'success');
            exit();
        }
    }
    /**
     * 根据小区筛选充电桩
     */
    if ($p == 'station') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $list = pdo_getall('xcommunity_charging_station', array('regionid' => $regionid), array('id', 'title'));
            echo json_encode($list);
            exit();
        }
    }
}
/**
 * 计费规则
 */
if ($op == 'rules') {
    $tid = intval($_GPC['tid']);
    $rules = pdo_get('xcommunity_charging_throw', array('id' => $tid));
    $quanrule = iunserializer($rules['quanrule']);
    $timerule = iunserializer($rules['timerule']);
    if ($_W['isajax']) {
        $data = array(
            'quanbill' => intval($_GPC['quanbill']),
            'quanrule' => iserializer($_GPC['quanrule']),
            'timebill' => intval($_GPC['timebill']),
            'timerule' => iserializer($_GPC['timerule'])
        );
        pdo_update('xcommunity_charging_throw', $data, array('id' => $tid));
        echo json_encode(array('status' => 1));
        exit();
    }
    $minStatus = set('p164') ? set('p164') : 0;
    include $this->template('web/plugin/charging/throw/rules');
}
/**
 * 充值记录
 */
if ($op == 'credit') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.type='chargrecharge' and t1.uniacid=:uniacid ";
    if ($user && $user['type'] == 3) {
        $condition .= ' and t1.regionid in(:regionid) ';
        $params[':regionid'] = $user['regionid'];
    }
    $sql = "select t1.*,t2.realname,t2.mobile,t2.nickname,t3.title from" . tablename('xcommunity_order') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $params[':uniacid'] = $_W['uniacid'];
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 where $condition";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/charging/recharge_order');
}
/**
 * 切换总运营商
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    if ($id) {
        $throw = pdo_get('xcommunity_charging_throw', array('id' => $id), array('id'));
        if (empty($throw)) {
            echo json_encode(array('content' => '信息不存在或已删除'));
            exit();
        }
        /**
         * 切换总运营商为普通运营商
         */
        pdo_update('xcommunity_charging_throw', array('status' => 0), array('status' => 1, 'uniacid' => $_W['uniacid']));
        //根据请求设置
        if (pdo_update('xcommunity_charging_throw', array('status' => 1), array('id' => $id))) {
            echo json_encode(array('content' => '切换成功'));
            exit();
        }
    }
}
/**
 * 幻灯设置
 */
if ($op == 'slide') {
    /**
     * 幻灯的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid and type= 8';
        $params[':uniacid'] = $_W['uniacid'];
        if ($user[type] == 2 || $user[type] == 3) {
            $condition .= " and uid = {$_W['uid']}";
        }
        $sql = "select t1.* from" . tablename('xcommunity_slide') . "t1 where $condition group by t1.id  ORDER BY t1.displayorder DESC, t1.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from" . tablename('xcommunity_slide') . "t1 where $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 幻灯的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if (!empty($id)) {
            $item = pdo_fetch("SELECT * FROM " . tablename("xcommunity_slide") . " WHERE id = :id", array(':id' => $id));
            $starttime = !empty($item['starttime']) ? date('Y-m-d', $item['starttime']) : date('Y-m-d', TIMESTAMP);
            $endtime = !empty($item['endtime']) ? date('Y-m-d', $item['endtime']) : date('Y-m-d', TIMESTAMP);
            if (empty($item)) {
                itoast('抱歉，幻灯不存在或是已经删除！', '', 'error');
            }
        }
        if ($_W['isajax']) {
            $starttime = strtotime($_GPC['birthtime']['start']);
            $endtime = strtotime($_GPC['birthtime']['end']);
            if (!empty($starttime) && $starttime == $endtime) {
                $endtime = $endtime + 86400 - 1;
            }
            $startdate = strtotime($_GPC['sale']['start']);
            $enddate = strtotime($_GPC['sale']['end']);
            if (!empty($startdate) && $startdate == $enddate) {
                $enddate = $enddate + 86400 - 1;
            }
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'title'        => $_GPC['title'],
                'url'          => $_GPC['url'],
                'displayorder' => intval($_GPC['displayorder']),
                'type'         => 8,
                'status'       => intval($_GPC['status']),
                'createtime'   => TIMESTAMP,
                'starttime'    => $starttime,
                'endtime'      => $endtime,
            );
            if (!empty($_GPC['thumb'])) {
                $data['thumb'] = $_GPC['thumb'];
                load()->func('file');
                file_delete($_GPC['thumb-old']);
            }
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert("xcommunity_slide", $data);
                $id = pdo_insertid();
                util::permlog('幻灯片-添加', '信息标题:' . $data['title']) . '信息ID' . $id;
            } else {
                pdo_update("xcommunity_slide", $data, array('id' => $id));
                util::permlog('幻灯片-修改', '信息标题:' . $data['title'] . '信息ID' . $id);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        load()->func('tpl');
    }
    /**
     * 幻灯的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        $row = pdo_fetch("SELECT id, thumb,title FROM " . tablename("xcommunity_slide") . " WHERE id = :id", array(':id' => $id));
        if (empty($row)) {
            itoast('抱歉，信息不存在或是已经被删除！');
        }
        if (pdo_delete("xcommunity_slide", array('id' => $id))) {
            util::permlog('幻灯片-删除', '信息标题:' . $row['title']);
        }
        itoast('删除成功！', referer(), 'success', ture);
    }
    include $this->template('web/plugin/charging/set');
}
/**
 * 分成统计
 */
if ($op == 'comm') {
    echo "正在开发中。。。";
}
/**
 * 上报的故障
 */
if ($op == 'fault') {
    /**
     * 上报故障的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $sql = "select t1.*,t3.title,t2.lock from" . tablename('xcommunity_charging_fault') . "t1 left join" . tablename('xcommunity_charging_socket') . "t2 on t1.socketid=t2.id left join" . tablename('xcommunity_charging_station') . "t3 on t2.stationid=t3.id where $condition group by t1.id  ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from" . tablename('xcommunity_charging_fault') . "t1 left join" . tablename('xcommunity_charging_socket') . "t2 on t1.socketid=t2.id left join" . tablename('xcommunity_charging_station') . "t3 on t2.stationid=t3.id where $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/charging/fault/list');
    }
    /**
     * 上报故障的处理
     */
    if ($p == 'grab') {
        if ($id) {
            $item = pdo_fetch("select t1.*,t3.title,t2.lock from" . tablename('xcommunity_charging_fault') . "t1 left join" . tablename('xcommunity_charging_socket') . "t2 on t1.socketid=t2.id left join" . tablename('xcommunity_charging_station') . "t3 on t2.stationid=t3.id where t1.id=:id", array(':id' => $id));
            if ($item['pics']) {
                $pics = explode(',', $item['pics']);
            }
            if (empty($item)) {
                itoast('抱歉，该信息不存在或是已经删除！', '', 'error');
            }
        }
        if ($_W['isajax']) {
            if (empty($id)) {
                echo json_encode(array('content' => '参数错误'));
                exit();
            } else {
                pdo_update("xcommunity_charging_fault", array('status' => intval($_GPC['status'])), array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/charging/fault/grab');
    }
    /**
     * 上报故障的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        $row = pdo_fetch("SELECT id FROM " . tablename("xcommunity_charging_fault") . " WHERE id = :id", array(':id' => $id));
        if (empty($row)) {
            itoast('抱歉，信息不存在或是已经被删除！');
        }
        if (pdo_delete("xcommunity_charging_fault", array('id' => $id))) {
            util::permlog('充电桩上报故障-删除', '信息标题:' . $row['title']);
        }
        itoast('删除成功！', referer(), 'success', ture);
    }
}
/**
 * 二维码的列表
 */
if ($op == 'qrlist') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $list = pdo_fetchall("SELECT t1.*,t2.title as rtitle,t3.title as ttitle FROM" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_charging_throw') . "t3 on t1.tid=t3.id WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_charging_station') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_charging_throw') . "t3 on t1.tid=t3.id WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/charging/qrlist');
}
/**
 * 删除批量生成二维码
 */
if ($op == 'qrdel') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_charging_station', array('id' => $id));
        if (empty($item)) {
            message('信息不存在或已删除', referer(), 'error');
        }
    }
    load()->func('file');
    $tmpdir = MODULE_ROOT . '/data/qrcode/charging/' . $item['uniacid'] . '/' . $item['code'];
    rmdirs($tmpdir);
    itoast('删除成功', referer(), 'success');
}
/**
 * 充值记录的删除
 */
if ($op == 'creditdel') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_order', array('id' => $id, 'type' => 'chargrecharge'), array());
    if ($item) {
        if (pdo_delete('xcommunity_order', array('id' => $id, 'type' => 'chargrecharge'))) {
            util::permlog('充电桩充值订单-删除', '订单号:' . $item['ordersn']);
            itoast('删除成功', referer(), 'success', ture);
        }
    }
}
/**
 * 充电桩的用户管理-余额
 */
if ($op == 'users') {
    $condition['uniacid'] = $_W['uniacid'];
    $condition['chargecredit <>'] = '0.00';
    if ($_GPC['keyword']) {
        $condition['realname like'] = "%{$_GPC['keyword']}%";
    }
    if ($_GPC['export'] == 1) {
        $list1 = pdo_getall('mc_members', $condition, array());
        $fans = pdo_getall('mc_mapping_fans', array('uniacid' => $_W['uniacid']));
        $fans_ids = _array_column($fans, NULL, 'uid');
        $creditTotal = 0;
        $lists = array();
        foreach ($list1 as $k => $v) {
            $lists[] = array(
                'uid'          => $v['uid'],
                'openid'       => $fans_ids[$v['uid']]['openid'],
                'realname'     => $v['realname'],
                'mobile'       => $v['mobile'],
                'chargecredit' => $v['chargecredit']
            );
        }
        model_execl::export($lists, array(
            "title"   => "充电桩用户数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => 'uid',
                    'field' => 'uid',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => 'openid',
                    'field' => 'openid',
                    'width' => 40
                ),
                array(
                    'title' => '充电桩余额',
                    'field' => 'chargecredit',
                    'width' => 12
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('mc_members', $condition, array($pindex, $psize), $total, '', '', array('uid desc'));
    $fans = pdo_getall('mc_mapping_fans', array('uniacid' => $_W['uniacid']));
    $fans_ids = _array_column($fans, NULL, 'uid');
    $creditTotal = 0;
    foreach ($list as $k => $v) {
        $list[$k]['openid'] = $fans_ids[$v['uid']]['openid'];
        $creditTotal += $v['chargecredit'];
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/charging/users_list');
}
/**
 * 充电桩余额变更明细
 */
if ($op == 'creditLog') {
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 7;
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $keyword = $_GPC['keyword'];
    if (is_numeric($keyword)) {
        $condition['mobile like'] = "%{$keyword}%";
    } else {
        $condition['realname like'] = "%{$keyword}%";
    }
    if ($_GPC['creditstatus']) {
        $condition['creditstatus'] .= intval($_GPC['creditstatus']);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_getslice('xcommunity_credit', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array());
    $regions_ids = _array_column($regions, NULL, 'id');
    foreach ($list as $k => $v) {
        $list[$k]['title'] = $regions_ids[$v['typeid']]['title'];
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/charging/credit_log');
}
/**
 * 充电桩数据统计
 */
if ($op == 'data') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition['uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition['title like'] = "%{$_GPC['keyword']}%";
    }
    $condi['uniacid'] = $_W['uniacid'];
    $condi['status'] = 1;
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    } else {
        $starttime = strtotime(date('Y-m-d', TIMESTAMP));
        $endtime = $starttime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condi['createtime >='] = $starttime;
        $condi['createtime <='] = $endtime;
    }
    $stations = pdo_getslice('xcommunity_charging_station', $condition, array($pindex, $psize), $total, '', '', array('displayorder asc', 'id desc'));
    $orders = pdo_getall('xcommunity_charging_order', $condi, array('code', 'price', 'stime', 'power'));
    $list = array();
    $priceTotals = 0;
    foreach ($stations as $key => $val) {
        $numTotal = 0;
        $priceTotal = 0;
        $timeTotal = 0;
        $powerTotal = 0;
        foreach ($orders as $k => $v) {
            if ($val['code'] == $v['code']) {
                $numTotal++;
                $priceTotal += $v['price'];
                $timeTotal += $v['stime'];
                $powerTotal += $v['power'];
                $priceTotals += $v['price'];
            }
        }
        $list[] = array(
            'id'         => $val['id'],
            'title'      => $val['title'],
            'numTotal'   => $numTotal,
            'priceTotal' => $priceTotal,
            'timeTotal'  => $timeTotal,
            'powerTotal' => $powerTotal
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/charging/data');
}
/**
 * 充电桩接收员
 */
if ($op == 'manage') {
    /**
     * 充电桩接收员列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition['uniacid'] = $_W['uniacid'];
        $list = pdo_getslice('xcommunity_charging_notice', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/charging/manage_list');
    }
    /**
     * 充电桩接收员添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_charging_notice', array('id' => $id), array());
            $chargingids = explode(',', $item['chargingid']);
        }
        if ($_W['isajax']) {
            $chargingid = implode(',', $_GPC['chargings']);
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'enable'     => $_GPC['enable'],
                'realname'   => trim($_GPC['realname']),
                'mobile'     => trim($_GPC['mobile']),
                'openid'     => trim($_GPC['openid']),
                'chargingid' => $chargingid
            );
            if (empty($item['id'])) {
                $notice = pdo_getcolumn('xcommunity_charging_notice', array('uniacid' => $_W['uniacid'], 'openid' => $data['openid']), 'id');
                if ($notice) {
                    echo json_encode(array('content' => '该粉丝的openid已经在管理员中'));
                    exit();
                }
                pdo_insert('xcommunity_charging_notice', $data);
                $id = pdo_insertid();
            } else {
                pdo_update('xcommunity_charging_notice', $data, array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $chargings = pdo_getall('xcommunity_charging_station', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        include $this->template('web/plugin/charging/manage_add');
    }
    /**
     * 充电桩接收员删除
     */
    if ($p == 'del') {
        $notice = pdo_get('xcommunity_charging_notice', array('id' => $id), array());
        if ($notice) {
            if (pdo_delete('xcommunity_charging_notice', array('id' => $id))) {
                util::permlog('充电桩接收员-删除', '信息标题:' . $notice['realname']);
                itoast('删除成功', referer(), 'success', ture);
            }
        }
    }
    /**
     * 充电桩接收员状态
     */
    if ($p == 'verify') {
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_charging_notice", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}