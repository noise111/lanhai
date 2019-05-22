<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/9 0009
 * Time: 下午 4:25
 */
global $_GPC, $_W;
$ops = array('set', 'list', 'little', 'log', 'code', 'qrlist', 'qrdel', 'download', 'manage', 'payapi');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 柜子的参数设置
 */
if ($op == 'set') {
    if (checksubmit('submit')) {
        foreach ($_GPC['counter'] as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key, 'uniacid' => $_W['uniacid']), array('id'));
            $data = array(
                'xqkey' => $key,
                'value' => $val,
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
    include $this->template('web/plugin/counter/set');
}
/**
 * 柜子的列表
 */
if ($op == 'list') {
    /**
     * 柜子的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $list = pdo_fetchall("SELECT t1.*,t2.title as rtitle,t3.buildtitle FROM" . tablename('xcommunity_counter_main') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_build') . "t3 on t1.buildid=t3.id WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_counter_main') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_build') . "t3 on t1.buildid=t3.id WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/counter/list');
    }
    /**
     * 柜子的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        $regions = model_region::region_fetall();
        if ($id) {
            $item = pdo_get('xcommunity_counter_main', array('id' => $id));
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array('id', 'buildtitle', 'areaid'));
            $arr = util::xqset($item['regionid']);
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
            $area = _array_column($areas, NULL, 'id');
            foreach ($builds as $k => $v) {
                if ($area[$v['areaid']]['title']) {
                    $builds[$k]['buildtitle'] = $area[$v['areaid']]['title'] . $arr[a1] . $v['buildtitle'] . $arr[b1];
                } else {
                    $builds[$k]['buildtitle'] = $v['buildtitle'] . $arr[b1];
                }
            }
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'buildid' => intval($_GPC['buildid']),
                'title' => trim($_GPC['title']),
                'device' => $_GPC['device'],
                'createtime' => TIMESTAMP,
                'appid' => trim($_GPC['appid']),
                'secret' => trim($_GPC['secret']),
                'type' => intval($_GPC['type']),
                'status' => intval($_GPC['status']),
                'amount' => intval($_GPC['amount']),
                'stat' => intval($_GPC['stat']),
                'rule' => intval($_GPC['rule']),
                'price' => trim($_GPC['price'])
            );
            if ($id) {
                pdo_update('xcommunity_counter_main', $data, array('id' => $id));
            } else {
                pdo_insert('xcommunity_counter_main', $data);
                $mid = pdo_insertid();
                for ($i = 0; $i < $data['amount']; $i++) {
                    $dat = array(
                        'uniacid' => $_W['uniacid'],
                        'deviceid' => $mid,
                        'lock' => $i,
                        'enable' => 0,
                        'createtime' => TIMESTAMP
                    );
                    pdo_insert('xcommunity_counter_little', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/counter/add');
    }
    /**
     * 柜子的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数', referer(), 'error');
            }
            $item = pdo_fetch("SELECT id,title FROM" . tablename('xcommunity_counter_main') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if (empty($item)) {
                itoast('该柜子不存在或已被删除', referer(), 'error', ture);
            }
            pdo_delete("xcommunity_counter_little", array('deviceid' => $id));
            pdo_delete("xcommunity_counter_log", array('deviceid' => $id));
            $r = pdo_delete("xcommunity_counter_main", array('id' => $id));
            if ($r) {
                util::permlog('智能货柜-删除', '删除货柜名称:' . $item['title']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    /**
     * 柜子获取楼宇
     */
    if ($p == 'build') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $arr = util::xqset($regionid);
            $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid), array('id', 'buildtitle', 'areaid'));
            foreach ($builds as $k => $v) {
                if ($v['areaid']) {
                    $area = pdo_getcolumn('xcommunity_area', array('id' => $v['areaid']), 'title');
                    $builds[$k]['buildtitle'] = $area . $arr[a1] . $v['buildtitle'] . $arr[b1];
                } else {
                    $builds[$k]['buildtitle'] = $v['buildtitle'] . $arr[b1];
                }
            }
            echo json_encode($builds);
            exit();
        }
    }
    /**
     * 柜子的二维码
     */
    if ($p == 'qr') {
        $id = intval($_GPC['id']);
        $item = pdo_fetch("select t1.title,t2.title as rtitle,t1.status from" . tablename('xcommunity_counter_main') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where t1.id=:id", array(':id' => $id));
        $time = TIMESTAMP;
        if ($item['status'] == 1) {
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=index&do=counter&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
        } elseif ($item['status'] == 2) {
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=list&do=counter&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
        }
        $temp = $item['rtitle'] . "-" . $item['title'] . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/counter/" . $_W['uniacid'] . "/" . $item['rtitle'] . "/";
        $qr = createQr($url, $temp, $tmpdir);
        echo $qr;
        exit();
    }
    /**
     * 柜子批量生成二维码
     */
    if ($p == 'qrpl') {
        $list = pdo_fetchall("SELECT t1.*,t2.title as rtitle FROM" . tablename('xcommunity_counter_main') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id WHERE t1.uniacid=:uniacid order by t1.createtime desc ", array(':uniacid' => $_W['uniacid']));
        foreach ($list as $k => $v) {
            $time = TIMESTAMP;
            if ($v['status'] == 1) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&op=index&do=counter&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
            } elseif ($v['status'] == 2) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&op=list&do=counter&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
            }
            $temp = $v['rtitle'] . "-" . $v['title'] . ".png";
            $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/counter/" . $_W['uniacid'] . "/" . $v['rtitle'] . "/";
            $qr = createQr($url, $temp, $tmpdir);
        }
        itoast('更新成功', referer(), 'success');
    }
}
/**
 * 柜子的小柜子
 */
if ($op == 'little') {
    $deviceid = intval($_GPC['deviceid']);
    /**
     * 柜子的小柜子列表
     */
    if ($p == 'list') {
        $condition = 't1.uniacid=:uniacid and t1.deviceid=:deviceid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':deviceid'] = $deviceid;
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $list = pdo_fetchall("SELECT t1.*,t2.device FROM" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id WHERE $condition order by t1.lock asc", $params);
        include $this->template('web/plugin/counter/little_list');
    }
    /**
     * 小柜子的修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_counter_little', array('id' => $id));
        }
        if ($_W['isajax']) {
            if ($id) {
                pdo_update('xcommunity_counter_little', array('title' => trim($_GPC['title'])), array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/counter/little_add');
    }
}
/**
 * 柜子的开柜记录
 */
if ($op == 'log') {
    if ($p == 'list') {
        $id = intval($_GPC['id']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid and t1.deviceid=:deviceid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':deviceid'] = $id;
        $list = pdo_fetchall("SELECT t1.*,t2.realname,t3.lock,t4.title FROM" . tablename('xcommunity_counter_log') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_counter_little') . "t3 on t1.littleid=t3.id left join" . tablename('xcommunity_counter_main') . "t4 on t1.deviceid=t4.id WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_counter_log') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_counter_little') . "t3 on t1.littleid=t3.id left join" . tablename('xcommunity_counter_main') . "t4 on t1.deviceid=t4.id WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/counter/log');
    }
}
/**
 * 柜子的开柜码
 */
if ($op == 'code') {
    $deviceid = intval($_GPC['deviceid']);
    $condition = 't1.uniacid=:uniacid and t1.deviceid=:deviceid';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':deviceid'] = $deviceid;
    $list = pdo_fetchall("SELECT t1.* FROM" . tablename('xcommunity_counter_code') . "t1 WHERE $condition order by t1.createtime desc", $params);
    include $this->template('web/plugin/counter/code');
}
/**
 * 二维码的列表
 */
if ($op == 'qrlist') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 'uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $reside = $_GPC['reside'];
    if (!empty($reside)) {
        if ($reside['province']) {
            $condition .= " AND province = :province";
            $params[':province'] = $reside['province'];
        }
        if ($reside['city']) {
            $condition .= " AND city = :city";
            $params[':city'] = $reside['city'];
        }
        if ($reside['district']) {
            $condition .= " AND dist = :dist";
            $params[':dist'] = $reside['district'];
        }
    }
    if ($user && $user[type] == 2) {
        //普通管理员
        $condition .= " AND uid='{$_W['uid']}'";
    }
    if ($user && $user[type] == 3) {
        //普通管理员
        $condition .= " AND id in({$user['regionid']})";
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_region') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "SELECT count(*) FROM" . tablename('xcommunity_region') . "WHERE $condition";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/counter/qrlist');
}
/**
 * 删除批量生成二维码
 */
if ($op == 'qrdel') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_region', array('id' => $id));
        if (empty($item)) {
            message('信息不存在或已删除', referer(), 'error');
        }
    }
    load()->func('file');
    $tmpdir = MODULE_ROOT . '/data/qrcode/counter/' . $item['uniacid'] . '/' . $item['title'];
    rmdirs($tmpdir);
    itoast('删除成功', referer(), 'success');
}
/**
 * 柜子的二维码下载
 */
if ($op == 'download') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_region', array('id' => $id));
        $dir = MODULE_ROOT . '/data/qrcode/counter/' . $_W['uniacid'] . "/" . $item['title'] . "/";
        $data = list_dir($dir);
        $path = "./" . $item['title'] . ".zip";
        download($data, $path);
    }
}
/**
 * 柜子管理员
 */
if ($op == 'manage') {
    /**
     * 柜子管理员列表
     */
    if ($p == 'list') {
        $condition = " uniacid=:uniacid";
        $parms[':uniacid'] = $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if (!empty($_GPC['type'])) {
            $condition .= " and type=:type";
            $parms[':type'] = intval($_GPC['type']);
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_counter_notice') . " WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_counter_notice') . " WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/counter/manage_list');
    }
    /**
     * 柜子管理员添加修改
     * type:1超级管理员2快递管理员
     */
    if ($p == 'add') {
        $type = intval($_GPC['type']);
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_counter_notice', array('id' => $id), array());
            $regs = pdo_getall('xcommunity_counter_littleid', array('nid' => $id), array('littleid'));
            $littleids = explode(',', $item['littleid']);
        }
        if ($_W['isajax']) {
            $littles = $_GPC['littles'];
            $littleid = implode(',', $littles);
            $data = array(
                'uniacid' => $_W['uniacid'],
                'enable' => $_GPC['enable'],
                'type' => $type,
                'realname' => trim($_GPC['realname']),
                'mobile' => trim($_GPC['mobile']),
                'openid' => trim($_GPC['openid']),
                'littleid' => $littleid
            );
            if (empty($item['id'])) {
                $notice = pdo_getcolumn('xcommunity_counter_notice', array('uniacid' => $_W['uniacid'], 'openid' => $data['openid']), 'id');
                if ($notice) {
                    echo json_encode(array('content' => '该粉丝的openid已经在管理员中'));
                    exit();
                }
                pdo_insert('xcommunity_counter_notice', $data);
                $id = pdo_insertid();
            } else {
                pdo_update('xcommunity_counter_notice', $data, array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $littles = pdo_fetchall("select t1.lock,t2.title as mtitle,t1.id,t1.title from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where t1.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        include $this->template('web/plugin/counter/manage_add');
    }
    /**
     * 柜子管理员删除
     */
    if ($p == 'del') {
        $notice = pdo_get('xcommunity_counter_notice', array('id' => $id), array());
        if ($notice) {
            if (pdo_delete('xcommunity_counter_notice', array('id' => $id))) {
                util::permlog('货柜管理员-删除', '信息标题:' . $notice['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    /**
     * 柜子管理员状态
     */
    if ($p == 'verify') {
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_counter_notice", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}
/**
 * 云柜的支付接口配置
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
        $item = pdo_get('xcommunity_alipayment', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type' => 9,
                'account' => $_GPC['account'],
                'partner' => $_GPC['partner'],
                'secret' => $_GPC['secret'],
                'userid' => $tid
            );
            if ($item) {
                pdo_update('xcommunity_alipayment', $data, array('userid' => $tid, 'type' => 9));
                util::permlog('', '修改支付宝ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_alipayment', $data);
                $id = pdo_insertid();
                util::permlog('', '添加支付宝ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/counter/payapi/alipay');
    }
    /**
     * 支付接口的配置-微信
     */
    if ($p == 'wechat') {
        $item = pdo_get('xcommunity_wechat', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'appid' => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'mchid' => $_GPC['mchid'],
                'apikey' => $_GPC['apikey'],
                'type' => 9,
                'userid' => $tid
            );
            if ($item) {
                pdo_update('xcommunity_wechat', $data, array('userid' => $tid, 'type' => 9));
                util::permlog('', '修改借用支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_wechat', $data);
                $id = pdo_insertid();
                util::permlog('', '添加借用支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/counter/payapi/wechat');
    }
    /**
     * 支付接口的配置-服务商
     */
    if ($p == 'sub') {
        $item = pdo_get('xcommunity_service_data', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'sub_id' => $_GPC['sub_id'],
                'apikey' => $_GPC['apikey'],
                'appid' => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'sub_mch_id' => $_GPC['sub_mch_id'],
                'type' => 9,
                'userid' => $tid
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
        include $this->template('web/plugin/counter/payapi/sub');
    }
    /**
     * 支付接口的配置-威富通
     */
    if ($p == 'swiftpass') {
        $item = pdo_get('xcommunity_swiftpass', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type' => 9,
                'account' => trim($_GPC['account']),
                'secret' => trim($_GPC['secret']),
                'appid' => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid' => $tid
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
        include $this->template('web/plugin/counter/payapi/swiftpass');
    }
    /**
     * 支付接口的配置-华商云付
     */
    if ($p == 'hsyunfu') {
        $item = pdo_get('xcommunity_hsyunfu', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type' => 9,
                'account' => trim($_GPC['account']),
                'secret' => trim($_GPC['secret']),
                'appid' => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid' => $tid
            );
            if ($item) {
                pdo_update('xcommunity_hsyunfu', $data, array('userid' => $tid, 'type' => 9));
                util::permlog('', '修改华商云付微信支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_hsyunfu', $data);
                $id = pdo_insertid();
                util::permlog('', '添加华商云付微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/counter/payapi/hsyunfu');
    }
    /**
     * 支付接口的配置-银联
     */
    if ($p == 'chinaums') {
        $item = pdo_get('xcommunity_chinaums', array('userid' => $tid, 'type' => 9), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type' => 9,
                'mid' => $_GPC['mid'],
                'tid' => $_GPC['ctid'],
                'instmid' => $_GPC['instmid'],
                'msgsrc' => $_GPC['msgsrc'],
                'msgsrcid' => $_GPC['msgsrcid'],
                'secret' => $_GPC['secret'],
                'userid' => $tid
            );
            if ($item) {
                pdo_update('xcommunity_chinaums', $data, array('userid' => $tid, 'type' => 9));
                util::permlog('', '修改银联ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_chinaums', $data);
                $id = pdo_insertid();
                util::permlog('', '添加银联ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/counter/payapi/chinaums');
    }
}