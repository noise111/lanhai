<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/25 0025
 * Time: 下午 2:07
 */
require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/cp/function.php';
global $_W, $_GPC;
$ops = array('set', 'rule', 'list', 'device', 'camera', 'cars', 'log', 'order', 'payapi', 'passlog', 'parking');
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
 * 智能车禁基本设置
 */
if ($op == 'set') {
    if (checksubmit('submit')) {
        $set = $_GPC['set'];
        foreach ($set as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key, 'uniacid' => $_W['uniacid']), array('id'));
            $data = array(
                'xqkey'   => $key,
                'value'   => $val,
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
    include $this->template('web/plugin/parks/set');
}
/**
 * 收费标准
 */
if ($op == 'rule') {
    /**
     * 月租车计费标准列表
     */
    if ($p == 'list') {
        if (checksubmit('submit')) {
            $id = intval($_GPC['id']);
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => trim($_GPC['title']),
                'rules'      => serialize($rule),
                'category'   => 1,
                'createtime' => TIMESTAMP,
                'price'      => trim($_GPC['price']),
                'uid'        => $_W['uid']
            );
            if ($id) {
                pdo_update('xcommunity_parks_rules', $data, array('id' => $id));
            } else {
                pdo_insert('xcommunity_parks_rules', $data);
            }
            itoast('提交成功', referer(), 'success');
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 1;
        //根据权限查询
        if ($user && $user['type'] != 1) {
            $condition['uid'] = $_W['uid'];
        }
        $list = pdo_getslice('xcommunity_parks_rules', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $pager = pagination($total, $pindex, $psize);
        //收费标准
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 1;
    }
    /**
     * 临时车计费标准列表
     */
    if ($p == 'shortList') {
        // 计费标准
        if (checksubmit('submit')) {
            $id = intval($_GPC['id']);
            $type = intval($_GPC['type']);
            // 二段式收费
            if ($type == 1) {
                $rule = array(
                    'start1'      => $_GPC['start1'],// 开始时间
                    'freeTime1'   => $_GPC['freeTime1'],// 免费时间
                    'firstPark1'  => $_GPC['firstPark1'],// 首停单位
                    'firstPrice1' => $_GPC['firstPrice1'],// 首停收费
                    'parkUnit1'   => $_GPC['parkUnit1'],// 停车单位
                    'price1'      => $_GPC['price1'],// 停车收费
                    'maxPrice1'   => $_GPC['maxPrice1'],// 封顶费用
                    'start2'      => $_GPC['start2'],
                    'freeTime2'   => $_GPC['freeTime2'],
                    'firstPark2'  => $_GPC['firstPark2'],
                    'firstPrice2' => $_GPC['firstPrice2'],
                    'parkUnit2'   => $_GPC['parkUnit2'],
                    'price2'      => $_GPC['price2'],
                    'maxPrice2'   => $_GPC['maxPrice2'],
                    'oneType'     => $_GPC['oneType'],// 跨时段按时段一收费 1是 0否
                );
            }
            // 二十四小时计费
            if ($type == 2) {
                $price = array();
                for ($i = 1; $i <= 24; $i++) {
                    $hour = 'price' . $i;
                    $price[] = $_GPC[$hour];
                }
                $rule = array(
                    'freeTime'   => $_GPC['freeTime'],// 免费时间
                    'maxPrice'   => $_GPC['maxPrice'],// 封顶费用
                    'nightPrice' => $_GPC['nightPrice'],// 过夜费
                    'firstUnit'  => $_GPC['firstUnit'],// 首停单位
                    'firstPrice' => $_GPC['firstPrice'],// 首停收费
                    'freeType'   => $_GPC['freeType'],// 停车时间包含免费时间 1是 0否
                    'nightType'  => $_GPC['nightType'],// 封顶收费包含过夜时间 1是 0否
                    'dayType'    => $_GPC['dayType'],// 是否启用多次进出按天封顶 1是 0否
                    'dayMode'    => $_GPC['dayMode'],// 多次进出按天封顶方式 1停车24小时 0:0-24点收费
                    'maxPrices'  => $_GPC['maxPrices'],//多次停车每日收费限制金额
                    'price'      => $price,// 收费金额(24个小时)
                );
            }
            // 记次收费
            if ($type == 3) {
                $rule = array(
                    'price' => $_GPC['price']// 金额
                );
            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => trim($_GPC['title']),
                'type'       => $type,
                'category'   => 2,
                'rules'      => serialize($rule),
                'createtime' => TIMESTAMP,
                'uid'        => $_W['uid']
            );
            if (empty($id)) {
                pdo_insert('xcommunity_parks_rules', $data);
            }
            itoast('提交成功', referer(), 'success');
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 2;
        //根据权限查询
        if ($user && $user['type'] != 1) {
            $condition['uid'] = $_W['uid'];
        }
        $rules = pdo_getslice('xcommunity_parks_rules', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $parks = getParksList();
        $list = array();
        foreach ($rules as $k => $v) {
            $rule = unserialize($v['rules']);
            $list[] = array(
                'id'        => $v['id'],
                'title'     => $v['title'],
                'parkTitle' => $parks[$v['id']]['title'],
                'pid'       => $v['pid'],
                'enable'    => $v['enable'],
                'type'      => $v['type']
            );
        }
        $pager = pagination($total, $pindex, $psize);
        $hours = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);
    }
    /**
     * 计费标准的编辑
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_rules', array('id' => $id), array());
            if (empty($item)) {
                itoast('数据不存在', referer(), 'error');
            }
            $rules = unserialize($item['rules']);
        }
        if (checksubmit('submit')) {
            // 二段式
            if ($item['type'] == 1) {
                $rule = array(
                    'start1'      => $_GPC['start1'],// 开始时间
                    'freeTime1'   => $_GPC['freeTime1'],// 免费时间
                    'firstPark1'  => $_GPC['firstPark1'],// 首停单位
                    'firstPrice1' => $_GPC['firstPrice1'],// 首停收费
                    'parkUnit1'   => $_GPC['parkUnit1'],// 停车单位
                    'price1'      => $_GPC['price1'],// 停车收费
                    'maxPrice1'   => $_GPC['maxPrice1'],// 封顶费用
                    'start2'      => $_GPC['start2'],
                    'freeTime2'   => $_GPC['freeTime2'],
                    'firstPark2'  => $_GPC['firstPark2'],
                    'firstPrice2' => $_GPC['firstPrice2'],
                    'parkUnit2'   => $_GPC['parkUnit2'],
                    'price2'      => $_GPC['price2'],
                    'maxPrice2'   => $_GPC['maxPrice2'],
                    'oneType'     => $_GPC['oneType'],// 跨时段按时段一收费 1是 0否
                );
            }
            // 二十四小时计费
            if ($item['type'] == 2) {
                $price = array();
                for ($i = 1; $i <= 24; $i++) {
                    $hour = 'price' . $i;
                    $price[] = $_GPC[$hour];
                }
                $rule = array(
                    'freeTime'   => $_GPC['freeTime'],// 免费时间
                    'maxPrice'   => $_GPC['maxPrice'],// 封顶费用
                    'nightPrice' => $_GPC['nightPrice'],// 过夜费
                    'firstUnit'  => $_GPC['firstUnit'],// 首停单位
                    'firstPrice' => $_GPC['firstPrice'],// 首停收费
                    'freeType'   => $_GPC['freeType'],// 停车时间包含免费时间 1是 0否
                    'nightType'  => $_GPC['nightType'],// 封顶收费包含过夜时间 1是 0否
                    'dayType'    => $_GPC['dayType'],// 是否启用多次进出按天封顶 1是 0否
                    'dayMode'    => $_GPC['dayMode'],// 多次进出按天封顶方式 1停车24小时 0:0-24点收费
                    'maxPrices'  => $_GPC['maxPrices'],//多次停车每日收费限制金额
                    'price'      => $price,// 收费金额
                );
            }
            // 记次收费
            if ($item['type'] == 3) {
                $rule = array(
                    'price' => $_GPC['price']// 金额
                );
            }
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'title'    => trim($_GPC['title']),
                'category' => 2,
                'rules'    => serialize($rule)
            );
            if ($id) {
                pdo_update('xcommunity_parks_rules', $data, array('id' => $id));
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_parks_rules', $data);
            }
            itoast('提交成功', referer(), 'success');
        }
        $parks = getParksList();
        $hours = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);
    }
    /**
     * 计费标准的删除
     */
    if ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_get('xcommunity_parks_rules', array('id' => $id), array());
        if (empty($item)) {
            itoast('数据不存在', referer(), 'error');
        }
        if (pdo_delete('xcommunity_parks_rules', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
    /**
     * 测试临时车收费计算
     */
    if ($p == 'test') {
        if ($_W['isajax']) {
            $intoTime = strtotime($_GPC['into']);
            $outTime = strtotime($_GPC['out']);
            $ruleId = intval($_GPC['ruleId']);
            /**计算费用**/
            $totalPrice = startRule($ruleId, $intoTime, $outTime);
            echo json_encode(array('status' => 1, 'price' => $totalPrice));
            exit();
        }
    }
    include $this->template('web/plugin/parks/rule');
}
/**
 * 智能车禁车场管理
 */
if ($op == 'list') {
    /**
     * 车场列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition['title like'] = "%{$_GPC['keyword']}%";
        }
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['id'] = $user['parkids'];
            }
        }
        $list = pdo_getslice('xcommunity_parks', $condition, array($pindex, $psize), $total, array(), '', array('createtime DESC'));
        $regionids = _array_column($list, 'regionid');
        $regions = pdo_getall('xcommunity_region', array('id' => $regionids), array('title', 'id'), 'id');
        $list_ruleids = _array_column($list, 'rule_id');
        $rules = pdo_getall('xcommunity_parks_rules', array('id' => $list_ruleids), array('title', 'id'), 'id');
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/parks/parks_list');
    }
    /**
     * 车场的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        $regions = model_region::region_fetall();
        if ($id) {
            $item = pdo_get('xcommunity_parks', array('id' => $id));
        }
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $data = array(
                'uniacid'        => $_W['uniacid'],
                'regionid'       => $regionid,
                'title'          => trim($_GPC['title']),
                'type'           => intval($_GPC['type']),
                'createtime'     => TIMESTAMP,
                'management'     => trim($_GPC['management']),
                'exitus'         => intval($_GPC['exitus']),
                'cars_num'       => intval($_GPC['cars_num']),
                'password'       => md5(trim($_GPC['password'])),
                'month_type'     => intval($_GPC['month_type']),
                'month_num'      => intval($_GPC['month_num']),
                'rule_id'        => intval($_GPC['ruleId']),
                'leave_space'    => trim($_GPC['leave_space']),
                'temrule_id'     => intval($_GPC['temrule_id']),
                'short_num'      => intval($_GPC['over_month_num']) - intval($_GPC['month_num']),
                'over_month_num' => intval($_GPC['over_month_num']),
                'over_short_num' => intval($_GPC['over_short_num']),
                'qr_status'      => intval($_GPC['qr_status'])
            );
            if ($id) {
                pdo_update('xcommunity_parks', $data, array('id' => $id));
            } else {
                pdo_insert('xcommunity_parks', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        //月租车收费标准
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 1;
        //根据权限查询
        if ($user && $user['type'] == 1) {
            $condition['uid'] = $_W['uid'];
        }
        $mon_rules = pdo_getall('xcommunity_parks_rules', $condition);
        //临时车收费标准
        $cond = array();
        $cond['uniacid'] = $_W['uniacid'];
        $cond['category'] = 2;
        //根据权限查询
        if ($user && $user['type'] == 1) {
            $cond['uid'] = $_W['uid'];
        }
        $tem_rules = pdo_getall('xcommunity_parks_rules', $cond);
        include $this->template('web/plugin/parks/parks_add');
    }
    /**
     * 车场的删除
     */
    if ($p == 'delete') {
        if ($id) {
            $item = pdo_get('xcommunity_parks', array('id' => $id), array());
            if ($item) {
                pdo_delete('xcommunity_parks_setting', array('parkid' => $item['id']));
                pdo_delete('xcommunity_parks_device', array('parkid' => $item['id']));
                pdo_delete("xcommunity_alipayment", array('userid' => $id, 'type' => 10));
                pdo_delete("xcommunity_wechat", array('userid' => $id, 'type' => 10));
                pdo_delete("xcommunity_service_data", array('userid' => $id, 'type' => 10));
                pdo_delete("xcommunity_swiftpass", array('userid' => $id, 'type' => 10));
                pdo_delete("xcommunity_hsyunfu", array('userid' => $id, 'type' => 10));
                pdo_delete("xcommunity_chinaums", array('userid' => $id, 'type' => 10));
                if (pdo_delete("xcommunity_parks", array('id' => $item['id']))) {
                    util::permlog('车场数据-删除', '删除车场:' . $item['title']);
                    itoast('删除成功', referer(), 'success');
                }
            }
        } else {
            itoast('参数id错误', referer(), 'error');
        }
    }
    /**
     * 车场的基本配置
     */
    if ($p == 'set') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks', array('uniacid' => $_W['uniacid'], 'id' => $id), array());
            $set = unserialize($item['setting']);
        }
        if ($_W['isajax']) {
            $content = array(
                'send'        => intval($_GPC['send']),// 自动推送缴费
                'rule'        => intval($_GPC['rule']),// 延期规则
                'expire_num'  => intval($_GPC['expire_num']),// 到期日之前
                'arrears_num' => intval($_GPC['arrears_num']),// 欠费后日期
                'tjtime'      => intval($_GPC['tjtime']),// 推送间隔时间
                'remind_num'  => intval($_GPC['remind_num']),// 最多提醒次数
                'opentime'    => intval($_GPC['opentime']),// 超时开闸时间（临时车）
            );
            $content = serialize($content);
            $r = pdo_update('xcommunity_parks', array('setting' => $content), array('id' => $id));
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/parks/parks_set');
    }
    /**
     * 缴费二维码
     */
    if ($p == 'qr') {
        $pid = intval($_GPC['id']);
        $carModelId = intval($_GPC['carModelId']);
        $qrId = intval($_GPC['qrId']);
        $carmeraId = intval($_GPC['carmeraId']);
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&pid={$pid}&carModelId={$carModelId}&qrId={$qrId}&carmeraId={$carmeraId}&do=fee&m=" . $this->module['name'];//二维码内容
        $temp = $pid . $carModelId . $qrId . $carmeraId . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/files/qr/parks/" . $_W['uniacid'] . "/";
        $qrImg = createQr($url, $temp, $tmpdir);
        echo json_encode(array('qrImg' => $qrImg));
        exit();
    }
}
/**
 * 智能车禁车场设备
 */
if ($op == 'device') {
    /**
     * 设备列表
     */
    $parkid = intval($_GPC['parkid']);
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition['title like'] = "%{$_GPC['keyword']}%";
        }
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['parkid'] = $user['parkids'];
            }
        }
        $list = pdo_getslice('xcommunity_parks_device', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $pager = pagination($total, $pindex, $psize);
        $parks = getParksList();
        include $this->template('web/plugin/parks/device_list');
    }
    /**
     * 设备的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_device', array('id' => $id));
        }
        if ($_W['isajax']) {
            $type = intval($_GPC['type']);
            $list = array();
            // 禁止临时车进出设置
            if ($type == 1) {
                $list = array(
                    array('idx' => "1", 'autoopen' => "0"),
                    array('idx' => "2", 'autoopen' => "0")
                );
            }
            // 临时车进出不收费设置
            if ($type == 3) {
                $list = array(
                    array('idx' => "1", 'autoopen' => "1"),
                    array('idx' => "2", 'autoopen' => "1")
                );
            }
            // 临时车收费设置
            if ($type == 2) {
                $list = array(
                    array('idx' => "1", 'autoopen' => "1"),
                    array('idx' => "2", 'autoopen' => "2")
                );
            }
            $identity = trim($_GPC['identity']);
            $device = pdo_get('xcommunity_parks_device', array('identity' => $identity, 'uniacid' => $_W['uniacid']), array('id'));
            if (empty($device) || $id) {
                $data = array(
                    'uniacid'    => $_W['uniacid'],
                    'category'   => intval($_GPC['category']),
                    'parkid'     => intval($_GPC['pid']),
                    'appid'      => trim($_GPC['appid']),
                    'appsecret'  => trim($_GPC['appsecret']),
                    'identity'   => $identity,
                    'createtime' => TIMESTAMP,
                    'title'      => trim($_GPC['title']),
//                    'setting'    => serialize($setting),
                    'cardid'     => trim($_GPC['cardid']),
                    'exit_type'  => intval($_GPC['exit_type']),
                    'type'       => $type,
                    'enable'     => 2
                );
                if ($id) {
                    pdo_update('xcommunity_parks_device', $data, array('id' => $id));
                } else {
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_parks_device', $data);
                }
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/cp/function.php';
                setCamera($data['identity'], $list);// 设置摄像机参数
                echo json_encode(array('status' => 1));
                exit();
            } else {
                echo json_encode(array('content' => '设备已存在'));
                exit();
            }
        }
        $parks = getParksList();
        include $this->template('web/plugin/parks/device_add');
    }
    /**
     * 设备的删除
     */
    if ($p == 'delete') {
        if ($id) {
            $item = pdo_get('xcommunity_parks_device', array('id' => $id), array());
            if ($item) {
                if (pdo_delete("xcommunity_parks_device", array('id' => $item['id']))) {
                    util::permlog('车场数据-删除', '删除车场设备:' . $item['identity']);
                    itoast('删除成功', referer(), 'success');
                }
            }
        } else {
            itoast('参数id错误', referer(), 'error');
        }
    }
}
/**
 * 车道管理
 */
if ($op == 'camera') {
    /**
     * 车道列表
     */
    if ($p == 'list') {
        // 车场设备开闸
        if (checksubmit('open')) {
            $id = intval($_GPC['id']);
            $type = intval($_GPC['type']);
            if ($id) {
                $item = pdo_get('xcommunity_parks_camera', array('id' => $id), array());
                if (empty($item)) {
                    itoast('抱歉，数据不存在或是已经删除！', '', 'error');
                }
                $device = pdo_get('xcommunity_parks_device', array('id' => $item['deviceid']), array('identity'));
                $park = pdo_get('xcommunity_parks_parks', array('pid' => $item['pid']), array('password', 'pid'));
                $list = array(
                    array('idx' => (string)$type, 'open' => "1")
                );
                $condition = [];
                $condition['uniacid'] = $_W['uniacid'];
                $log = pdo_getslice('xcommunity_parks_log', $condition, array(1, 1), $total, array(), '', array('id DESC'));
                $result = openPole($device['identity'], $list);
                if ($result['success']) {
                    //开闸成功
                    $data = array(
                        'uniacid'    => $_W['uniacid'],
                        'uid'        => $_W['uid'],
                        'username'   => $_W['username'],
                        'identity'   => $device['identity'],
                        'idx'        => $type,
                        'cameraid'   => $id,
                        'createtime' => TIMESTAMP,
                        'pid'        => $park['id'],
                        'ip'         => CLIENT_IP,
                        'carno'      => $log[0]['carno']
                    );
                    pdo_insert('xcommunity_parks_openlog', $data);
                    itoast('开闸成功', referer(), 'success');
                } else {
                    itoast('开闸失败', referer(), 'success');
                }
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = [];
        $condition['uniacid'] = $_W['uniacid'];
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['pid'] = $user['parkids'];
            }
        }
        $devices = pdo_getslice('xcommunity_parks_camera', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $parks = getParksList();
        $list = array();
        foreach ($devices as $k => $v) {
            $list[] = array(
                'id'           => $v['id'],
                'title'        => $v['title'],
                'installtime'  => $v['installtime'],
                'service_life' => $v['service_life'],
                'service_num'  => $v['service_num'],
                'createtime'   => $v['createtime'],
                'parkTitle'    => $parks[$v['pid']]['title'],
                'type'         => $v['type']
            );
        }
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 车道的添加编辑
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_camera', array('id' => $id), array());
            if (empty($item)) {
                itoast('抱歉，数据不存在或是已经删除！', '', 'error');
            }
            $devices = pdo_getall('xcommunity_parks_device', array('parkid' => $item['pid']), array('id', 'title'));
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'title'        => trim($_GPC['title']),
                'installtime'  => strtotime($_GPC['installtime']),
                'service_life' => intval($_GPC['service_life']),
                'service_num'  => intval($_GPC['service_num']),
                'content'      => htmlspecialchars_decode($_GPC['content']),
                'createtime'   => TIMESTAMP,
                'deviceid'     => intval($_GPC['deviceid']),
                'type'         => intval($_GPC['type']),
                'pid'          => intval($_GPC['pid'])
            );
            if ($id) {
                $result = pdo_update('xcommunity_parks_camera', $data, array('id' => $id));
            } else {
                $result = pdo_insert('xcommunity_parks_camera', $data);
            }
            if ($result) {
                itoast('提交成功', referer(), 'success');
            }
        }
        $parks = getParksList();
    }
    /**
     * 车道的删除
     */
    if ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_get('xcommunity_parks_camera', array('id' => $id), array());
        if (empty($item)) {
            itoast('数据不存在', referer(), 'error');
        }
        if (pdo_delete('xcommunity_parks_camera', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
    /**
     * 车道参数的查看
     */
    if ($p == 'detail') {
        if ($_W['isajax']) {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_parks_camera', array('id' => $id), array());
            }
            echo json_encode($item);
            exit();
        }
    }
    /**
     * 设备的列表
     */
    if ($p == 'deviceList') {
        if ($_W['isajax']) {
            $pid = intval($_GPC['pid']);
            $list = array();
            if ($pid) {
                $list = pdo_getall('xcommunity_parks_device', array('parkid' => $pid), array('id', 'title'));
            }
            echo json_encode($list);
            exit();
        }
    }
    include $this->template('web/plugin/parks/camera');
}
/**
 *智能车禁车辆管理
 */
if ($op == 'cars') {
    /**
     * 车辆列表
     */
    if ($p == 'list') {
//        if (checksubmit('del')) {
//            $ids = $_GPC['ids'];
//            if (!empty($ids)) {
//                foreach ($ids as $key => $id) {
//                    pdo_delete('xcommunity_parks_cars', array('id' => $id));
//                }
//                util::permlog('','批量删除车');
//                itoast('删除成功', referer(), 'success');
//            }
//        }
        if(checksubmit('wxmovecar')){
            $ids = $_GPC['ids'];
            $condition = array();
            $condition['uniacid'] = $_W['uniacid'];
            if (empty($ids)){
                itoast('请先勾选车辆！', referer(), 'error');exit();
            }
            if ($ids) {
                $condition['id'] = $ids;
            }
            $cars = pdo_getall('xcommunity_parks_cars', $condition, array('id', 'carno', 'parkid'), 'id');
            // 车辆绑定用户
            $cars_ids = _array_column($cars, 'id');
            $carsUser = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
            // 用户的粉丝信息
            $uids = _array_column($carsUser, 'uid');
            $memberFans = pdo_getall('mc_mapping_fans', array('uid' => $uids), array('openid', 'uid'), 'uid');
            // 车场的信息
            $parkids = _array_column($cars, 'parkid');
            $parks = pdo_getall('xcommunity_parks', array('id' => $parkids), array('id', 'regionid'), 'id');
            // 小区的信息
            $regionids = _array_column($parks, 'regionid');
            $regions = pdo_getall('xcommunity_region', array('id' => $regions), array('id', 'linkway'), 'id');
            $url = '';
            if(set('t23')){
                $tplid = set('t24');
                foreach($cars as $k => $v){
                    $content = array(
                        'first' => array(
                            'value' => '您好，你有一条挪车提醒!',
                        ),
                        'keyword1' => array(
                            'value' => '系统',
                        ),
                        'keyword2' => array(
                            'value' => '您的爱车('.$v['carno'].')挡住路啦，麻烦您给挪一下呗',
                        ),
                        'remark' => array(
                            'value' => '如有疑问，请咨询.'.$regions[$parks[$v['parkid']]['regionid']]['linkway'],
                        ),
                    );
                    $openid = $memberFans[$carsUser[$v['id']]['uid']]['openid'];
                    if (!empty($openid)) {
                        $ret = util::sendTplNotice($openid, $tplid, $content, $url, '');
                        $d = array(
                            'uniacid' => $_W['uniacid'],
                            'sendid' => $v['id'],
                            'uid' => $carsUser[$v['id']]['uid'],
                            'type' => 9,
                            'cid' => 1,
                            'regionid'  => $parks[$v['parkid']]['regionid']
                        );
                        if (empty($ret['errcode'])) {
                            $d['status'] = 1;
                            pdo_insert('xcommunity_send_log', $d);
                        } else {
                            $d['status'] = 2;
                            pdo_insert('xcommunity_send_log', $d);
                        }
                    }
                }
            }
            util::permlog('车辆管理-批量微信挪车',  '车辆ID:' . $ids);
            itoast('发送成功', referer(), 'success',true);
        }
        if(checksubmit('smsmovecar')){
            $ids = $_GPC['ids'];
            $condition = array();
            $condition['uniacid'] = $_W['uniacid'];
            if (empty($ids)){
                itoast('请先勾选车辆！', referer(), 'error');exit();
            }
            if ($ids) {
                $condition['id'] = $ids;
            }
            $cars = pdo_getall('xcommunity_parks_cars', $condition, array('id', 'carno', 'parkid', 'mobile', 'realname'), 'id');
            // 车辆绑定用户
            $cars_ids = _array_column($cars, 'id');
            $carsUser = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
            // 车场的信息
            $parkids = _array_column($cars, 'parkid');
            $parks = pdo_getall('xcommunity_parks', array('id' => $parkids), array('id', 'regionid'), 'id');
            // 小区的信息
            $regionids = _array_column($parks, 'regionid');
            $regions = pdo_getall('xcommunity_region', array('id' => $regionids), array('id', 'linkway'), 'id');
            if (set('s2') && set('s20')) {
                $type = set('s1');
                if($type ==1){
                    $type =='wwt';
                }elseif($type ==2){
                    $type = 'juhe';
                    $tpl_id = set('s19');
                }else{
                    $type = 'aliyun_new';
                    $tpl_id = set('s26');
                }
                foreach ($cars as $k => $member) {
                    $linkway = $regions[$parks[$member['parkid']]['regionid']]['linkway'];
                    if ($type == 'wwt') {
                        $smsg = "您的爱车(".$member['carno'].")挡住路啦，麻烦您给挪一下呗。如有疑问，请咨询." . $linkway;
                    } elseif ($type == 'juhe') {
                        $car_num = $member['carno'];
                        $smsg = urlencode("#phone#=$linkway&#car_num#=$car_num");
                    }else{
                        $smsg = json_encode(array('car_num' => $member['carno'], 'linkway' => $linkway));
                    }
                    $content = sms::send($member['mobile'], $smsg, $type, '', 1, $tpl_id);
                    $d = array(
                        'uniacid' => $_W['uniacid'],
                        'sendid' => $member['id'],
                        'uid' => $carsUser[$member['id']]['uid'],
                        'type' => 9,
                        'cid' => 2,
                        'status' => 1,
                        'regionid' => $parks[$member['parkid']]['regionid'],
                    );
                    pdo_insert('xcommunity_send_log', $d);
                }
            } else {
                foreach ($cars as $k => $member) {
                    $regionid = $parks[$member['parkid']]['regionid'];
                    $linkway = $regions[$parks[$member['parkid']]['regionid']]['linkway'];
                    $type = set('x21', $regionid);
                    if($type ==1){
                        $type ='wwt';
                    }elseif($type ==2){
                        $type = 'juhe';
                        $tpl_id = set('x60',$regionid);
                    }else{
                        $type = 'aliyun_new';
                        $tpl_id = set('x74',$regionid);
                    }
                    if ($type == 'wwt') {
                        $smsg = "您的爱车(".$member['carno'].")挡住路啦，麻烦您给挪一下呗，如有疑问，请咨询。" . $linkway;
                    } elseif ($type == 'juhe') {
                        $car_num = $member['carno'];
                        $smsg = urlencode("#phone#=$linkway&#car_num#=$car_num");
                    }else{
                        $smsg = json_encode(array('car_num' => $member['carno'], 'linkway' => $linkway));
                    }
                    $content = sms::send($member['mobile'], $smsg, $type, $member['regionid'], 2, $tpl_id);
                    $d = array(
                        'uniacid' => $_W['uniacid'],
                        'sendid' => $member['id'],
                        'uid' => $carsUser[$member['id']]['uid'],
                        'type' => 9,
                        'cid' => 2,
                        'status' => 1,
                        'regionid' => $regionid,
                        'createtime' => TIMESTAMP
                    );
                    pdo_insert('xcommunity_send_log', $d);
                }
            }
            util::permlog('车辆管理-批量短信挪车', '车辆ID:' . $ids);
            itoast('发送成功', referer(), 'success',true);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = array(1,3);
        if (!empty($_GPC['keyword'])) {
            $condition['realname like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['parkid'])) {
            $condition['parkid'] = intval($_GPC['parkid']);
        }
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['parkid'] = $user['parkids'];
            }
        }
        $list = pdo_getslice('xcommunity_parks_cars', $condition, array($pindex, $psize), $total, array(), '', array('id DESC', 'createtime DESC'));
        $list_pids = _array_column($list, 'parkid');
        $parkss = pdo_getall('xcommunity_parks', array('id' => $list_pids), array('title', 'id'), 'id');
        $pager = pagination($total, $pindex, $psize);
        $parks = getParksList();
        include $this->template('web/plugin/parks/cars_list');
    }
    /**
     * 车辆的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id));
            if ($item) {
                $regionid = pdo_getcolumn('xcommunity_parks', array('id' => $item['parkid']), 'regionid');
                $parkings = pdo_getall('xcommunity_parking', array('regionid' => $regionid), array('id', 'place_num'));
            }
        }
        if ($_W['isajax']) {
            $carno = $_GPC['carno'];
            $endtime = date('YmdHis', strtotime($_GPC['endtime'])); //到期时间
            $pid = intval($_GPC['parkid']);
            $mobile = trim($_GPC['mobile']);
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'parkid'     => $pid,// 车场的id
                'createtime' => TIMESTAMP,
                'type'       => intval($_GPC['type']),// 1月租车,3免费车
                'rule_id'    => intval($_GPC['ruleId']),
                'carno'      => $carno,
                'remark'     => trim($_GPC['remark']),
                'verity'     => 1,
                'parking_id' => intval($_GPC['parking_id'])
            );
            if ($id) {
                if ($carno != $item['carno']) {
                    $data['enable'] = 0;
                }
                pdo_update('xcommunity_parks_cars', $data, array('id' => $id));
            } else {
                $car = pdo_get('xcommunity_parks_cars', array('parkid' => $data['parkid'], 'carno' => $carno), array('id'));
                if (empty($car)) {
                    $carlist = array();
                    $carlist[] = array(
                        'content'    => $carno,
                        'expiretime' => $endtime
                    );
                    // 同步下发车场所有设备白名单
                    $devices = pdo_getall('xcommunity_parks_device', array('parkid' => $pid), array('identity'));
                    if ($devices) {
                        foreach ($devices as $device) {
                            $result = addWhiteCarnos($device['identity'], $carlist);
                        }
                    }
                    $data['endtime'] = strtotime($_GPC['endtime']);
                    $data['enable'] = 1;
                    $data['status'] = 1;
                    $data['realname'] = trim($_GPC['realname']);
                    $data['mobile'] = $mobile;
                    pdo_insert('xcommunity_parks_cars', $data);
                    // 给用户绑定车辆
                    $id = pdo_insertid();
                    $userUid = pdo_getcolumn('mc_members', array('uniacid' => $_W['uniacid'], 'mobile' => $mobile), 'uid');
                    if ($userUid) {
                        $userCar = pdo_get('xcommunity_parks_usercars', array('uid' => $userUid, 'carid' => $id), array('id'));
                        if (empty($userCar)) {
                            $carData = array(
                                'uniacid'    => $_W['uniacid'],
                                'carid'      => $id,
                                'uid'        => $userUid,
                                'enable'     => 0,
                                'createtime' => TIMESTAMP,
                                'parkid'     => $pid,
                                'type'       => 1
                            );
                            pdo_insert('xcommunity_parks_usercars', $carData);
                        }
                    }
                } else {
                    echo json_encode(array('content' => '该车场已存在该车辆'));
                    exit();
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $parks = getParksList();
        //收费标准
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 1;
        //根据权限查询
        if ($user && $user['type'] != 1) {
            $condition['uid'] = $_W['uid'];
        }
        $rules = pdo_getall('xcommunity_parks_rules', $condition);
        include $this->template('web/plugin/parks/cars_add');
    }
    /**
     * 车辆的删除-删除设备终端车辆信息
     */
    if ($p == 'del') {
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id));
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']));
            $carlist = array();
            $carlist[] = array(
                'content'    => $item['carno'],
                'expiretime' => ''
            );
            foreach ($list as $k => $v) {
                delCarnos($v['identity'], $carlist, 1);
            }
            pdo_update('xcommunity_parks_cars', array('enable' => 2), array('id' => $id));
            util::permlog('车辆数据-删除设备终端信息', '删除车辆信息:' . $item['carno']);
            itoast('删除设备终端车辆信息成功', referer(), 'success');
        } else {
            itoast('参数id错误', referer(), 'error');
        }
    }
    /**
     * 车辆的删除-删除小区车辆信息
     */
    if ($p == 'delete') {
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id), array());
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']));
            $carlist = array();
            $carlist[] = array(
                'content'    => $item['carno'],
                'expiretime' => ''
            );
            foreach ($list as $k => $v) {
                delCarnos($v['identity'], $carlist, 1);
            }
            if ($item) {
                if (pdo_delete("xcommunity_parks_cars", array('id' => $item['id']))) {
                    util::permlog('车辆数据-删除', '删除车辆:' . $item['carno']);
                    itoast('删除成功', referer(), 'success');
                }
            }
        } else {
            itoast('参数id错误', referer(), 'error');
        }
    }
    /**
     * 车辆的手动延期
     */
    if ($p == 'update') {
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id), array());
            if (empty($item)) {
                itoast('抱歉，数据不存在或是已经删除！', '', 'error');
            }
            $park = pdo_get('xcommunity_parks', array('id' => $item['parkid']), array('rule_id'));
            $rule_id = $item['rule_id'] ? $item['rule_id'] : $park['rule_id'];
            $rule = pdo_get('xcommunity_parks_rules', array('id' => $rule_id), array('price'));
        }
        if ($_W['isajax']) {
            // 查询车场的设备
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']), array('id', 'parkid', 'identity'));
            //续费类型
            $type = intval($_GPC['type']);
            if ($type == 1) {
                //按套餐
                $end = strtotime("+" . $_GPC['month'] . " month", $item['endtime']);
                $endtime = date('YmdHis', strtotime("+" . $_GPC['month'] . " month", $item['endtime']));
                if ($_GPC['month'] == 12) {
                    $day = 365;
                } else {
                    $day = 30 * $_GPC['month'];
                }
                $price = $_GPC['price'] * $_GPC['month'];
            } else {
                //按天
                $end = strtotime($_GPC['endtime']);
                $endtime = date('YmdHis', strtotime($_GPC['endtime'])); //到期时间
                $day = ceil((strtotime($_GPC['endtime']) - $item['endtime']) / 86400);
                $price = ceil(($_GPC['price'] / 30) * $day);
            }
            //下发白名单
            $carlist = array();
            $carlist[] = array(
                'content'    => $item['carno'],
                'expiretime' => $endtime
            );
            foreach ($list as $k => $v) {
                $result = addWhiteCarnos($v['identity'], $carlist);
            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
                'carno'      => $item['carno'],
                'num'        => $day,
                'price'      => $price,
                'category'   => 1,
                'type'       => 2,
                'status'     => 1,
                'enable'     => 1,
                'createtime' => TIMESTAMP,
                'paytype'    => $_GPC['paytype'],
                'parkid'     => $item['parkid']
            );
            $r = pdo_insert('xcommunity_parks_order', $data);
            if ($r) {
                pdo_update('xcommunity_parks_cars', array('endtime' => $end, 'enable' => 1), array('id' => $id));
                echo json_encode(array('status' => 1));
                exit();
            } else {
                echo json_encode(array('content' => '延期失败'));
                exit();
            }
        }
        include $this->template('web/plugin/parks/cars_update');
    }
    /**
     * 车辆的手动同步
     */
    if ($p == 'change') {
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id), array());
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']));
            $carlist = array();
            $carlist[] = array(
                'content'    => $item['carno'],
                'expiretime' => ''
            );
            foreach ($list as $k => $v) {
                $content = addWhiteCarnos($v['identity'], $carlist);
            }
            pdo_update('xcommunity_parks_cars', array('enable' => 1), array('id' => $id));
            itoast('同步车辆信息成功', referer(), 'success');
        }
    }
    /**
     * 车辆的黑白名单修改
     */
    if ($p == 'use') {
        if ($id) {
            $use = intval($_GPC['use']);
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id), array());
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']));
            $carlist = array();
            $carlist[] = array(
                'content'    => $item['carno'],
                'expiretime' => ''
            );
            foreach ($list as $k => $v) {
                if ($use == 1) {
                    addWhiteCarnos($v['identity'], $carlist);
                } elseif ($use == 2) {
                    addBlackCarnos($v['identity'], $carlist);
                }
            }
            if ($use == 1) {
                $title = '白名单添加成功';
            } elseif ($use == 2) {
                $title = '黑名单添加成功';
            }
            pdo_update('xcommunity_parks_cars', array('status' => $use), array('id' => $id));
            itoast($title, referer(), 'success');
        }
    }
    /**
     * 车牌号批量导入
     */
    if ($p == 'import') {
        if (checksubmit('submit')) {
            $rows = model_execl::read('cars');
            $pid = $_GPC['pid'];
            $devices = pdo_getall('xcommunity_parks_device', array('parkid' => $pid), array());
            foreach ($rows as $rownum => $col) {
                if ($rownum > 0) {
                    if ($col[2]) {
                        $carno = $col[2];
                        $endtime = date('YmdHis', strtotime($col[3])); //到期时间
                        $remark = $col[4];
                        $data = array(
                            'uniacid'    => $_W['uniacid'],
                            'parkid'     => $pid,// 车场的id
                            'createtime' => TIMESTAMP,
                            'type'       => 1,// 1月租车,3免费车
                            'rule_id'    => intval($_GPC['ruleId']),
                            'carno'      => $carno,
                            'remark'     => $remark,
                        );
                        $car = pdo_get('xcommunity_parks_cars', array('parkid' => $pid, 'carno' => $carno), array('id'));
                        if (empty($car)) {
                            //循环车辆接口
                            $carlist = array();
                            $carlist[0] = array(
                                'content'    => $carno,
                                'expiretime' => $endtime
                            );
                            // 同步下发车场所有设备白名单
                            $devices = pdo_getall('xcommunity_parks_device', array('parkid' => $pid), array('identity'));
                            if ($devices) {
                                foreach ($devices as $device) {
                                    $result = addWhiteCarnos($device['identity'], $carlist);
                                }
                            }
                            $data['endtime'] = strtotime($col[3]);
                            $data['enable'] = 1;
                            $data['status'] = 1;
                            $data['realname'] = $col[0];
                            $data['mobile'] = $col[1];
                            pdo_insert('xcommunity_parks_cars', $data);
                            $carid = pdo_insertid();
                            // 给用户绑定车辆
                            $userUid = pdo_getcolumn('mc_members', array('uniacid' => $_W['uniacid'], 'mobile' => $col[1]), 'uid');
                            if ($userUid) {
                                $userCar = pdo_get('xcommunity_parks_usercars', array('uid' => $userUid, 'carid' => $carid), array('id'));
                                if (empty($userCar)) {
                                    $carData = array(
                                        'uniacid'    => $_W['uniacid'],
                                        'carid'      => $carid,
                                        'uid'        => $userUid,
                                        'enable'     => 0,
                                        'createtime' => TIMESTAMP,
                                        'parkid'     => $pid,
                                        'type'       => 1
                                    );
                                    pdo_insert('xcommunity_parks_usercars', $carData);
                                }
                            }
                        } else {
                            pdo_update('xcommunity_parks_cars', $data, array('id' => $car['id']));
                        }
                    }
                }
            }
            itoast('提交成功', referer(), 'success');
        }
        $parks = getParksList();
        //收费标准
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['category'] = 1;
        //根据权限查询
        if ($user && $user['type'] != 1) {
            $condition['uid'] = $_W['uid'];
        }
        $rules = pdo_getall('xcommunity_parks_rules', $condition);
        include $this->template('web/plugin/parks/cars_import');
    }
    /**
     * 同步车辆下发
     */
    if ($p == 'cloud') {
        $psize = 50;
        $star = intval($_GPC['star']);
        $condition = array();
        $condition['id >'] = $star;
        $condition['type '] = 1;//月租车
        $condition['status'] = 1;//白名单
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['parkid'] = $user['parkids'];
            }
        }
        //查询车辆
        $list = pdo_getall('xcommunity_parks_cars', $condition, array(), 'id', array('id ASC'), $psize);
        if (empty($list)) {
            $url2 = $this->createWebUrl('parks', array('op' => 'cars'));
            message('全部下发成功！', $url2, 'success');
        }
        $list_pids = array_column($list, 'parkid');
        foreach ($list as $val) {
            $endtime = date('YmdHis', $val['endtime']); //到期时间
            $carlist[0] = array(
                'content'    => $val['carno'],
                'expiretime' => $endtime
            );
            // 同步下发车场所有设备白名单
            $devices = pdo_getall('xcommunity_parks_device', array('parkid' => $val['parkid']), array('identity'));
            if ($devices) {
                foreach ($devices as $device) {
                    $result = addWhiteCarnos($device['identity'], $carlist);
                }
            }
        }
        foreach ($list as $row) {
            $lastid = $row['id'];
        }
        $url = $this->createWebUrl('parks', array('op' => 'cars', 'p' => 'cloud', 'star' => $lastid));
        message('正在同步下一组,等待中！', $url, 'success');
    }
    /**
     * 审核车辆
     */
    if ($p == 'verity') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_cars', array('id' => $id), array());
            if ($item) {
                pdo_update('xcommunity_parks_cars', array('verity' => 1), array('id' => $id));
                itoast('审核成功', referer(), 'success');
            }
        } else {
            itoast('参数id错误', referer(), 'error');
        }
    }
    /**
     * 临时车
     */
    if ($p == 'shortList') {
        if(checksubmit('wxmovecar')){
            $ids = $_GPC['ids'];
            $condition = array();
            $condition['uniacid'] = $_W['uniacid'];
            if (empty($ids)){
                itoast('请先勾选车辆！', referer(), 'error');exit();
            }
            if ($ids) {
                $condition['id'] = $ids;
            }
            $cars = pdo_getall('xcommunity_parks_cars', $condition, array('id', 'carno', 'parkid'), 'id');
            // 车辆绑定用户
            $cars_ids = _array_column($cars, 'id');
            $carsUser = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
            // 用户的粉丝信息
            $uids = _array_column($carsUser, 'uid');
            $memberFans = pdo_getall('mc_mapping_fans', array('uid' => $uids), array('openid', 'uid'), 'uid');
            // 车场的信息
            $parkids = _array_column($cars, 'parkid');
            $parks = pdo_getall('xcommunity_parks', array('id' => $parkids), array('id', 'regionid'), 'id');
            // 小区的信息
            $regionids = _array_column($parks, 'regionid');
            $regions = pdo_getall('xcommunity_region', array('id' => $regions), array('id', 'linkway'), 'id');
            $url = '';
            if(set('t23')){
                $tplid = set('t24');
                foreach($cars as $k => $v){
                    $content = array(
                        'first' => array(
                            'value' => '您好，你有一条挪车提醒!',
                        ),
                        'keyword1' => array(
                            'value' => '系统',
                        ),
                        'keyword2' => array(
                            'value' => '您的爱车('.$v['carno'].')挡住路啦，麻烦您给挪一下呗',
                        ),
                        'remark' => array(
                            'value' => '如有疑问，请咨询.'.$regions[$parks[$v['parkid']]['regionid']]['linkway'],
                        ),
                    );
                    $openid = $memberFans[$carsUser[$v['id']]['uid']]['openid'];
                    if (!empty($openid)) {
                        $ret = util::sendTplNotice($openid, $tplid, $content, $url, '');
                        $d = array(
                            'uniacid' => $_W['uniacid'],
                            'sendid' => $v['id'],
                            'uid' => $carsUser[$v['id']]['uid'],
                            'type' => 9,
                            'cid' => 1,
                            'regionid'  => $parks[$v['parkid']]['regionid']
                        );
                        if (empty($ret['errcode'])) {
                            $d['status'] = 1;
                            pdo_insert('xcommunity_send_log', $d);
                        } else {
                            $d['status'] = 2;
                            pdo_insert('xcommunity_send_log', $d);
                        }
                    }
                }
            }
            util::permlog('车辆管理-批量微信挪车',  '车辆ID:' . $ids);
            itoast('发送成功', referer(), 'success',true);
        }
        if(checksubmit('smsmovecar')){
            $ids = $_GPC['ids'];
            $condition = array();
            $condition['uniacid'] = $_W['uniacid'];
            if (empty($ids)){
                itoast('请先勾选车辆！', referer(), 'error');exit();
            }
            if ($ids) {
                $condition['id'] = $ids;
            }
            $cars = pdo_getall('xcommunity_parks_cars', $condition, array('id', 'carno', 'parkid', 'mobile', 'realname'), 'id');
            // 车辆绑定用户
            $cars_ids = _array_column($cars, 'id');
            $carsUser = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
            // 车场的信息
            $parkids = _array_column($cars, 'parkid');
            $parks = pdo_getall('xcommunity_parks', array('id' => $parkids), array('id', 'regionid'), 'id');
            // 小区的信息
            $regionids = _array_column($parks, 'regionid');
            $regions = pdo_getall('xcommunity_region', array('id' => $regionids), array('id', 'linkway'), 'id');
            if (set('s2') && set('s20')) {
                $type = set('s1');
                if($type ==1){
                    $type =='wwt';
                }elseif($type ==2){
                    $type = 'juhe';
                    $tpl_id = set('s19');
                }else{
                    $type = 'aliyun_new';
                    $tpl_id = set('s26');
                }
                foreach ($cars as $k => $member) {
                    $linkway = $regions[$parks[$member['parkid']]['regionid']]['linkway'];
                    if ($type == 'wwt') {
                        $smsg = "您的爱车(".$member['carno'].")挡住路啦，麻烦您给挪一下呗。如有疑问，请咨询." . $linkway;
                    } elseif ($type == 'juhe') {
                        $car_num = $member['carno'];
                        $smsg = urlencode("#phone#=$linkway&#car_num#=$car_num");
                    }else{
                        $smsg = json_encode(array('car_num' => $member['carno'], 'linkway' => $linkway));
                    }
                    $content = sms::send($member['mobile'], $smsg, $type, '', 1, $tpl_id);
                    $d = array(
                        'uniacid' => $_W['uniacid'],
                        'sendid' => $member['id'],
                        'uid' => $carsUser[$member['id']]['uid'],
                        'type' => 9,
                        'cid' => 2,
                        'status' => 1,
                        'regionid' => $parks[$member['parkid']]['regionid'],
                    );
                    pdo_insert('xcommunity_send_log', $d);
                }
            } else {
                foreach ($cars as $k => $member) {
                    $regionid = $parks[$member['parkid']]['regionid'];
                    $linkway = $regions[$parks[$member['parkid']]['regionid']]['linkway'];
                    $type = set('x21', $regionid);
                    if($type ==1){
                        $type ='wwt';
                    }elseif($type ==2){
                        $type = 'juhe';
                        $tpl_id = set('x60',$regionid);
                    }else{
                        $type = 'aliyun_new';
                        $tpl_id = set('x74',$regionid);
                    }
                    if ($type == 'wwt') {
                        $smsg = "您的爱车(".$member['carno'].")挡住路啦，麻烦您给挪一下呗，如有疑问，请咨询。" . $linkway;
                    } elseif ($type == 'juhe') {
                        $car_num = $member['carno'];
                        $smsg = urlencode("#phone#=$linkway&#car_num#=$car_num");
                    }else{
                        $smsg = json_encode(array('car_num' => $member['carno'], 'linkway' => $linkway));
                    }
                    $content = sms::send($member['mobile'], $smsg, $type, $member['regionid'], 2, $tpl_id);
                    $d = array(
                        'uniacid' => $_W['uniacid'],
                        'sendid' => $member['id'],
                        'uid' => $carsUser[$member['id']]['uid'],
                        'type' => 9,
                        'cid' => 2,
                        'status' => 1,
                        'regionid' => $regionid,
                        'createtime' => TIMESTAMP
                    );
                    pdo_insert('xcommunity_send_log', $d);
                }
            }
            util::permlog('车辆管理-批量短信挪车', '车辆ID:' . $ids);
            itoast('发送成功', referer(), 'success',true);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 2;
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        $list = pdo_getslice('xcommunity_parks_cars', $condition, array($pindex, $psize), $total, array(), '', array('id DESC', 'createtime DESC'));
        $list_pids = _array_column($list, 'parkid');
        $parkss = pdo_getall('xcommunity_parks', array('id' => $list_pids), array('title', 'id'), 'id');
        $pager = pagination($total, $pindex, $psize);
        $parks = getParksList();
        include $this->template('web/plugin/parks/cars_shortlist');
    }
}
/**
 * 数据记录
 */
if ($op == 'log') {
    /**
     * 在场车辆
     */
    if ($p == 'onlog') {
        // 修改车牌号
        if (checksubmit('submit')) {
            $id = intval($_GPC['id']);
            if ($id) {
                pdo_update('xcommunity_parks_log', array('carno' => trim($_GPC['carno'])), array('id' => $id));
                itoast('修改成功', referer(), 'success');
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = [];
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 1;
        $condition['open'] = 1;
        $cond = [];
        $cond['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['pid'])) {
            $condition['pid'] = intval($_GPC['pid']);
        } else {
            // 车场的权限
            if ($user && $user['type'] == 3) {
                if ($user['parkids']) {
                    $condition['pid'] = $user['parkids'];
                }
            }
        }
        //入场时间
        $starttime = $_GPC['starttime'];
        if (!empty($starttime)) {
            $condition['starttime >='] = strtotime($starttime['start']);
            $condition['starttime <='] = strtotime($starttime['end']) + 86399;
        }
        $starttime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 60);
        $starttime['end'] = date('Y-m-d', TIMESTAMP);
        
        $list = pdo_getslice('xcommunity_parks_log', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $parks = getParksList();

        $over_month_num = 0;//剩余月租车位数
        $over_short_num = 0;//剩余临时车车位数
        foreach ($parks as $park) {
            $over_month_num += $park['over_month_num'];
            $over_short_num += $park['over_short_num'];
        }
        // 月租车信息
        $parks_carnos = _array_column($list, 'carno');
        $cars = pdo_getall('xcommunity_parks_cars', array('carno' => $parks_carnos), array('id', 'carno', 'realname', 'mobile'), 'carno');
        // 临时车绑定用户信息
        $cars_ids = _array_column($cars, 'id');
        $userCars = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
        $uids = _array_column($userCars, 'uid');
        $members = pdo_getall('mc_members', array('uid' => $uids), array('nickname', 'uid'), 'uid');
        // 计费规则
        $parkRuls = pdo_getall('xcommunity_parks_rules',array('uniacid'=>$_W['uniacid']), array('title','id'),'id');
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 车辆进出记录
     */
    if ($p == 'passlog') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = [];
        $condition['uniacid'] = $_W['uniacid'];
        $condition['idx'] = 1;
        $condition['open'] = 1;
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['pid'])) {
            $condition['pid'] = intval($_GPC['pid']);
        } else {
            // 车场的权限
            if ($user && $user['type'] == 3) {
                if ($user['parkids']) {
                    $condition['pid'] = $user['parkids'];
                }
            }
        }
        //入场时间
        $starttime = $_GPC['starttime'];
        if (!empty($starttime)) {
            $condition['starttime >='] = strtotime($starttime['start']);
            $condition['starttime <='] = strtotime($starttime['end']) + 86399;
        }
        $starttime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 60);
        $starttime['end'] = date('Y-m-d', TIMESTAMP);
        $rows = pdo_getslice('xcommunity_parks_log', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $parks = getParksList();
        // 月租车信息
        $parks_carnos = _array_column($rows, 'carno');
        $cars = pdo_getall('xcommunity_parks_cars', array('carno' => $parks_carnos), array('id', 'carno', 'realname', 'mobile'), 'carno');
        // 临时车绑定用户信息
        $cars_ids = _array_column($cars, 'id');
        $userCars = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
        $uids = _array_column($userCars, 'uid');
        $members = pdo_getall('mc_members', array('uid' => $uids), array('nickname', 'uid'), 'uid');
        // 计费规则
        $parkRuls = pdo_getall('xcommunity_parks_rules',array('uniacid'=>$_W['uniacid']), array('title','id'),'id');
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 手动放行记录
     */
    if ($p == 'openlog') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = [];
        $condition['uniacid'] = $_W['uniacid'];
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['pid'] = $user['parkids'];
            }
        }
        $open_logs = pdo_getslice('xcommunity_parks_openlog', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        // 月租车信息
        $parks_carnos = _array_column($open_logs, 'carno');
        $cars = pdo_getall('xcommunity_parks_cars', array('carno' => $parks_carnos), array('id', 'carno', 'realname', 'mobile'), 'carno');
        // 临时车绑定用户信息
        $cars_ids = _array_column($cars, 'id');
        $userCars = pdo_getall('xcommunity_parks_usercars', array('carid' => $cars_ids), array('carid', 'uid'), 'carid');
        $uids = _array_column($userCars, 'uid');
        $members = pdo_getall('mc_members', array('uid' => $uids), array('nickname', 'uid'), 'uid');
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 月租车开闸记录
     */
    if ($p == 'moncarlog') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = [];
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 1;
        $condition['idx'] = 2;
        $condition['open'] = 1;
        // 车场的权限
        if ($user && $user['type'] == 3) {
            if ($user['parkids']) {
                $condition['pid'] = $user['parkids'];
            }
        }
        $mon_logs = pdo_getslice('xcommunity_parks_log', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
        $cond = [];
        $cond['uniacid'] = $_W['uniacid'];
        $parks = getParksList();
        $pager = pagination($total, $pindex, $psize);
    }
    /*
 * 删除记录
 */
    if ($p == 'del') {
        $type = trim($_GPC['type']);
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        //在线车辆记录
        if ($type == 'onlog') {
            $item = pdo_get('xcommunity_parks_log', array('id' => $id), array());
            if (empty($item)) {
                itoast('数据不存在', referer(), 'error');
            }
            $result = pdo_delete('xcommunity_parks_log', array('id' => $id));
        }
        //车辆进出记录
        if ($type == 'passlog') {
            $item = pdo_get('xcommunity_parks_log', array('id' => $id), array());
            if (empty($item)) {
                itoast('数据不存在', referer(), 'error');
            }
            $result = pdo_delete('xcommunity_parks_log', array('id' => $id));
        }
        //手动放行记录
        if ($type == 'openlog') {
            $item = pdo_get('xcommunity_parks_openlog', array('id' => $id), array());
            if (empty($item)) {
                itoast('数据不存在', referer(), 'error');
            }
            $result = pdo_delete('xcommunity_parks_openlog', array('id' => $id));
        }
        //月租车开闸记录
        if ($type == 'moncarlog') {
            $item = pdo_get('xcommunity_parks_log', array('id' => $id), array());
            if (empty($item)) {
                itoast('数据不存在', referer(), 'error');
            }
            $result = pdo_delete('xcommunity_parks_log', array('id' => $id));
        }
        if ($result) {
            itoast('删除成功', referer(), 'success');
        }
    }
    include $this->template('web/plugin/parks/log');
}
/**
 *智能车禁缴费记录
 */
if ($op == 'order') {
    /**
     * 月租车缴费记录列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $category = $_GPC['category'] ? $_GPC['category'] : 1;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 1;
        $condition['display'] = 1;
        $condition['category'] = $category;
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['pid'])) {
            $condition['parkid'] = intval($_GPC['pid']);
        } else {
            // 车场的权限
            if ($user && $user['type'] == 3) {
                if ($user['parkids']) {
                    $condition['parkid'] = $user['parkids'];
                }
            }
        }
        //支付时间
        $starttime = $_GPC['starttime'];
        if (!empty($starttime)) {
            $condition['createtime >='] = strtotime($starttime['start']);
            $condition['createtime <='] = strtotime($starttime['end']) + 86399;
        }
        $starttime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 60);
        $starttime['end'] = date('Y-m-d', TIMESTAMP);
        // 缴费记录列表
        $orders = pdo_getslice('xcommunity_parks_order', $condition, array($pindex, $psize), $total, array(), '', array('createtime DESC'));
        $parks = getParksList();
        $list = array();
        foreach ($orders as $k => $v) {
            $list[] = array(
                'id'         => $v['id'],
                'ordersn'    => $v['ordersn'],
                'carno'      => $v['carno'],
                'price'      => $v['price'],
                'num'        => $v['num'],
                'type'       => $v['type'],
                'enable'     => $v['enable'],
                'intotime'   => $v['intotime'],
                'outtime'    => $v['outtime'],
                'rodtype'    => $v['rodtype'],
                'status'     => $v['status'],
                'paytype'    => $v['paytype'],
                'createtime' => $v['createtime'],
                'title'      => $parks[$v['parkid']]['title'],
            );
        }
        $pager = pagination($total, $pindex, $psize);

    }
    /**
     * 缴费记录的删除
     */
    if ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_get('xcommunity_parks_order', array('id' => $id), array());
        if (empty($item)) {
            itoast('数据不存在', referer(), 'error');
        }
        if ($item['status'] == 0) {
            $result = pdo_delete('xcommunity_parks_order', array('id' => $id));
        } else {
            $result = pdo_update('xcommunity_parks_order', array('display' => 2), array('id' => $id));
        }
        if ($result) {
            itoast('删除成功', referer(), 'success');
        }
    }
    /**
     * 月租车缴费记录的同步
     */
    if ($p == 'change') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_parks_order', array('id' => $id), array());
            if (empty($item)) {
                itoast('抱歉，数据不存在或是已经删除！', '', 'error');
            }
            $list = pdo_getall('xcommunity_parks_device', array('parkid' => $item['parkid']), array('id', 'parkid', 'identity'));
            $enable = $item['enable'];
            if ($list) {
                $enable = 1;
                foreach ($list as $k => $v) {
                    $result = addWhiteCarnos($v['identity'], array($item['carno']));
                    if (empty($result['success'])) {
                        $enable = 2;
                    }
                }
            }
            if (pdo_update('xcommunity_parks_order', array('enable' => $enable), array('id' => $id))) {
                itoast('同步成功', referer(), 'success');
            } else {
                itoast('抱歉，同步失败！', referer(), 'error');
            }
        }
    }
    /**
     * 临时车缴费记录列表
     */
    if ($p == 'short') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $category = $_GPC['category'] ? $_GPC['category'] : 2;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 1;
        $condition['display'] = 1;
        $condition['category'] = $category;
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['pid'])) {
            $condition['parkid'] = intval($_GPC['pid']);
        } else {
            // 车场的权限
            if ($user && $user['type'] == 3) {
                if ($user['parkids']) {
                    $condition['parkid'] = $user['parkids'];
                }
            }
        }
        //支付时间
        $starttime = $_GPC['starttime'];
        if (!empty($starttime)) {
            $condition['createtime >='] = strtotime($starttime['start']);
            $condition['createtime <='] = strtotime($starttime['end']) + 86399;
        }
        $starttime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 60);
        $starttime['end'] = date('Y-m-d', TIMESTAMP);
        // 缴费记录列表
        $orders = pdo_getslice('xcommunity_parks_order', $condition, array($pindex, $psize), $total, array(), '', array('createtime DESC'));
        $parks = getParksList();
        $list = array();
        foreach ($orders as $k => $v) {
            $list[] = array(
                'id'         => $v['id'],
                'ordersn'    => $v['ordersn'],
                'carno'      => $v['carno'],
                'price'      => $v['price'],
                'num'        => $v['num'],
                'type'       => $v['type'],
                'enable'     => $v['enable'],
                'intotime'   => $v['intotime'],
                'outtime'    => $v['outtime'],
                'rodtype'    => $v['rodtype'],
                'status'     => $v['status'],
                'paytype'    => $v['paytype'],
                'createtime' => $v['createtime'],
                'title'      => $parks[$v['parkid']]['title'],
            );
        }
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 超时未支付订单列表
     */
    if ($p == 'payoff') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 0;
        $condition['category'] = array(1, 2);// 月租车、临时车
//    $condition['createtime <'] = time() - 86400;
        if (!empty($_GPC['keyword'])) {
            $condition['carno like'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['pid'])) {
            $condition['parkid'] = intval($_GPC['pid']);
        } else {
            // 车场的权限
            if ($user && $user['type'] == 3) {
                if ($user['parkids']) {
                    $condition['parkid'] = $user['parkids'];
                }
            }
        }
        //支付时间
        $starttime = $_GPC['starttime'];
        if (!empty($starttime)) {
            $condition['createtime >='] = strtotime($starttime['start']);
            $condition['createtime <='] = strtotime($starttime['end']) + 86399;
        }
        $starttime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 60);
        $starttime['end'] = date('Y-m-d', TIMESTAMP);
        $cars = pdo_getslice('xcommunity_parks_order', $condition, array($pindex, $psize), $total, array(), '', array('createtime DESC'));
        $parks = getParksList();
        $list = array();
        foreach ($cars as $k => $v) {
            $list[] = array(
                'id'         => $v['id'],
                'ordersn'    => $v['ordersn'],
                'carno'      => $v['carno'],
                'price'      => $v['price'],
                'num'        => $v['num'],
                'type'       => $v['type'],
                'enable'     => $v['enable'],
                'intotime'   => $v['intotime'],
                'outtime'    => $v['outtime'],
                'rodtype'    => $v['rodtype'],
                'status'     => $v['status'],
                'paytype'    => $v['paytype'],
                'createtime' => $v['createtime'],
                'title'      => $parks[$v['parkid']]['title'],
                'category'   => $v['category']
            );
        }
        $pager = pagination($total, $pindex, $psize);
    }
    include $this->template('web/plugin/parks/order_list');
}
/**
 * 智能车禁的支付接口配置
 */
if ($op == 'payapi') {
    $tid = intval($_GPC['tid']);
    /**
     * 支付接口的配置
     */
    if ($p == 'list') {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
        include $this->template('web/plugin/parks/payapi/list');
    }
    /**
     * 支付接口的配置-支付宝
     */
    if ($p == 'alipay') {
        $item = pdo_get('xcommunity_alipayment', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type'    => 11,
                'account' => $_GPC['account'],
                'partner' => $_GPC['partner'],
                'secret'  => $_GPC['secret'],
                'userid'  => $tid
            );
            if ($item) {
                pdo_update('xcommunity_alipayment', $data, array('userid' => $tid, 'type' => 11));
                util::permlog('', '修改支付宝ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_alipayment', $data);
                $id = pdo_insertid();
                util::permlog('', '添加支付宝ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/parks/payapi/alipay');
    }
    /**
     * 支付接口的配置-微信
     */
    if ($p == 'wechat') {
        $item = pdo_get('xcommunity_wechat', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'appid'     => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'mchid'     => $_GPC['mchid'],
                'apikey'    => $_GPC['apikey'],
                'type'      => 11,
                'userid'    => $tid
            );
            if ($item) {
                pdo_update('xcommunity_wechat', $data, array('userid' => $tid, 'type' => 11));
                util::permlog('', '修改借用支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_wechat', $data);
                $id = pdo_insertid();
                util::permlog('', '添加借用支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/parks/payapi/wechat');
    }
    /**
     * 支付接口的配置-服务商
     */
    if ($p == 'sub') {
        $item = pdo_get('xcommunity_service_data', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'sub_id'     => $_GPC['sub_id'],
                'apikey'     => $_GPC['apikey'],
                'appid'      => $_GPC['appid'],
                'appsecret'  => $_GPC['appsecret'],
                'sub_mch_id' => $_GPC['sub_mch_id'],
                'type'       => 11,
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
        include $this->template('web/plugin/parks/payapi/sub');
    }
    /**
     * 支付接口的配置-威富通
     */
    if ($p == 'swiftpass') {
        $item = pdo_get('xcommunity_swiftpass', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => 11,
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid'    => $tid
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
        include $this->template('web/plugin/parks/payapi/swiftpass');
    }
    /**
     * 支付接口的配置-华商云付
     */
    if ($p == 'hsyunfu') {
        $item = pdo_get('xcommunity_hsyunfu', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => 11,
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid'    => $tid
            );
            if ($item) {
                pdo_update('xcommunity_hsyunfu', $data, array('userid' => $tid, 'type' => 11));
                util::permlog('', '修改华商云付微信支付ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_hsyunfu', $data);
                $id = pdo_insertid();
                util::permlog('', '添加华商云付微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/parks/payapi/hsyunfu');
    }
    /**
     * 支付接口的配置-银联
     */
    if ($p == 'chinaums') {
        $item = pdo_get('xcommunity_chinaums', array('userid' => $tid, 'type' => 11), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => 11,
                'mid'      => $_GPC['mid'],
                'tid'      => $_GPC['ctid'],
                'instmid'  => $_GPC['instmid'],
                'msgsrc'   => $_GPC['msgsrc'],
                'msgsrcid' => $_GPC['msgsrcid'],
                'secret'   => $_GPC['secret'],
                'userid'   => $tid
            );
            if ($item) {
                pdo_update('xcommunity_chinaums', $data, array('userid' => $tid, 'type' => 11));
                util::permlog('', '修改银联ID:' . $item['id']);
            } else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_chinaums', $data);
                $id = pdo_insertid();
                util::permlog('', '添加银联ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/plugin/parks/payapi/chinaums');
    }
}
/**
 * 智能车禁车辆通行记录
 */
if ($op == 'passlog') {
    /**
     * 车辆通行记录列表
     */
    $parkid = intval($_GPC['parkid']);
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid=:uniacid ';
        $params[':uniacid'] = $_W['uniacid'];
        $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_parks_passlog') . " WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_parks_passlog') . "WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/parks/passlog');
    }
}
/**
 * 获取车场的车位
 */
if ($op == 'parking') {
    if ($_W['isajax']) {
        $parkid = intval($_GPC['parkid']);
        $regionid = pdo_getcolumn('xcommunity_parks', array('id' => $parkid), 'regionid');
        $parkings = pdo_getall('xcommunity_parking', array('regionid' => $regionid), array('id', 'place_num'));
        echo json_encode($parkings);
        exit();
    }
}