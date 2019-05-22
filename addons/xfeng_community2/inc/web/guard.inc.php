<?php
/*/**
 * Created by 小区秘书.
 * User: 蓝牛科技
 * Date: 16/8/31
 * Time: 下午5:12
 * Function:楼宇管理
 */
global $_GPC, $_W;
$ops = array('add', 'list', 'delete', 'qrcreate', 'group', 'comb', 'device', 'log', 'qrpl', 'download', 'open', 'member', 'opentime', 'set', 'sub', 'pldel', 'data', 'change', 'plopen', 'pltime', 'perLower', 'face', 'faceLogs', 'faceDel', 'faceAdd', 'faceUploads', 'faceDevice');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
/**
 * 智能门禁的设备管理
 */
if ($op == 'list') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_building_device', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('排序更新成功！', 'refresh', 'success', ture);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if ($user['type'] == 3) {
        //普通管理员
        $condition .= " AND  t1.regionid in({$user['regionid']})";
    }
    if ($_GPC['regionid']) {
        $condition .= " AND  t1.regionid={$_GPC['regionid']}";
    }
    if ($_GPC['type']) {
        $condition .= " AND  t1.type={$_GPC['type']}";
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t1.device_code LIKE :keyword or t1.title LIKE :keyword or t1.unit LIKE :keyword)";
        $keyword = trim($_GPC['keyword']);
        $params[':keyword'] = "%{$keyword}%";
    }
    if ($_GPC['doorstatus']) {
        $condition .= " AND  t1.doorstatus={$_GPC['doorstatus']}";
    }
    $sql = "select t1.*,t2.title as rtitle from " . tablename("xcommunity_building_device") . "as t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where  t1.uniacid =:uniacid $condition order by t1.displayorder asc,t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $params[':uniacid'] = $_W['uniacid'];
    $list = pdo_fetchall($sql, $params);
    $units = pdo_getall('xcommunity_unit', array('uniacid' => $_W['uniacid']), array('id', 'unit'));
    $units_ids = _array_column($units, NULL, 'id');
    load()->func('communication');
    foreach ($list as $key => $item) {
        $list[$key]['unittitle'] = $item['unit'];
        $list[$key]['unit'] = $units_ids[$item['unitid']]['unit'];
        $times = strtotime('-1 year');
        $list[$key]['devicestatus'] = $times < $item['createtime'] ? '正常' : '已过期';
    }
    $tsql = "select count(*) from " . tablename("xcommunity_building_device") . "as t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where  t1.uniacid =:uniacid $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/core/guard/list');
}
/**
 * 智能门禁的设备添加修改
 */
if ($op == 'add') {
    $regions = model_region::region_fetall();
    if ($id) {
        $item = pdo_get('xcommunity_building_device', array('id' => $id), array());
        if ($item['photos']) {
            $photos = explode(',', $item['photos']);
        }
        $arr = util::xqset($item['regionid']);
        if ($arr[a]) {
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
        }
        if ($arr[b]) {
            $condition = " t1.regionid=:regionid";
            $param[':regionid'] = $item['regionid'];
            $condition .= " and t1.areaid=:areaid";
            $param[':areaid'] = $item['areaid'] ? $item['areaid'] : 0;
            $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where $condition", $param);
        }
        if ($arr[c]) {
            $condition_unit = " buildid=:buildid";
            $param_unit[':buildid'] = $item['buildid'];
            $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where $condition_unit", $param_unit);
        }
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'title'       => $_GPC['title'],
            'device_code' => $_GPC['device_code'],
            'type'        => intval($_GPC['type']),
            'openurl'     => $_GPC['openurl'],
            'regionid'    => intval($_GPC['regionid']),
            'device_gprs' => $_GPC['device_gprs'],
            'createtime'  => TIMESTAMP,
            'lng'         => $_GPC['baidumap']['lng'],
            'lat'         => $_GPC['baidumap']['lat'],
            'thumb'       => $_GPC['thumb'],
            'category'    => intval($_GPC['category']),
            'status'      => 1,
            'photos'      => $_GPC['photos'] ? implode(',', $_GPC['photos']) : ''
        );
        if (!empty($_GPC['photos'])) {
            $data['photos'] = implode(',', $_GPC['photos']);
        }
        if ($_GPC['category'] == 9) {
            $data['appid'] = $_GPC['appid'];
            $data['appkey'] = $_GPC['appkey'];
            $data['appsecret'] = $_GPC['appsecret'];
        }
        if ($_GPC['category'] == 10) {
            $data['username'] = trim($_GPC['username']);
            $data['password'] = trim($_GPC['password']);
            $data['apikey'] = trim($_GPC['apikey']);
            $data['secretkey'] = trim($_GPC['secretkey']);
        }
        if ($data['type'] == 1) {
            $data['areaid'] = $_GPC['area'];
            $data['buildid'] = $_GPC['build'];
            $data['unitid'] = $_GPC['unit'];
        }
        if ($_GPC['category'] == 10) {
            // 人脸设备的创建
            require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
            $regionid = $data['regionid'];
            if ($regionid) {
                $region = pdo_get('xcommunity_region', array('id' => $regionid), array('title', 'accountid', 'villageid'));

                $result = getVillage($data['apikey'], $data['secretkey'], $region['villageid'], $region['accountid']);
                if (empty($region['accountid']) || empty($result)) {
                    $accountid = getAccountid($data['apikey'], $data['secretkey'], $data['username'], md5($data['password'])); //登录物业
                    if($accountid){
                        pdo_update('xcommunity_region', array('accountid' => $accountid), array('id' => $regionid));
                    }

                }
                else {
                    $accountid = $region['accountid'];
                }
                if (empty($region['villageid']) || empty($result)) {
                    $villageid = addVillage($data['apikey'], $data['secretkey'], $region['title'], $accountid);//添加小区
                    if($villageid){
                        pdo_update('xcommunity_region', array('villageid' => $villageid), array('id' => $regionid));
                    }

                }
                else {
                    $villageid = $region['villageid'];
                }
                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                $deviceid = addDhDevice($data['apikey'], $data['secretkey'], $data['device_code'], $data['title'], $accountid, $villageid);
                $data['deviceid'] = $deviceid ? $deviceid : $item['deviceid'];

            }
        }
        if ($id) {
            pdo_update('xcommunity_building_device', $data, array('id' => $id));
            util::permlog('智能门禁-修改', '区域名称:' . $data['title']);


        }
        else {
            if ($_GPC['category'] == 9) {
                // 人脸设备的创建
                require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
                addDevice($_GPC['appid'], $_GPC['appkey'], $_GPC['appsecret'], $_GPC['device_code'], $_GPC['title']);
            }

//            if ($_GPC['category'] == 10) {
//                // 人脸设备的创建
//                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
//                getAccountid($data['apikey'], $data['secretkey'], $data['username'], $data['password']); //登录物业
//                addVillage($data['apikey'], $data['secretkey'], $region['title'], $accountid);//添加小区
//                $deviceid = addDhDevice($data['apikey'], $data['secretkey'], $data['device_code'], $data['title'], $accountid, $villageid);
//                $data['deviceid'] = $deviceid;
//            }

            $data['uid'] = $_W['uid'];
            $data['accountid'] = $accountid;
            $data['doorstatus'] = $deviceid ? 1 : 0;
            pdo_insert('xcommunity_building_device', $data);
            $id = pdo_insertid();
            util::permlog('智能门禁-添加', '区域名称:' . $data['title']);
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    $options = array();
    $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
    include $this->template('web/core/guard/add');
}
/**
 * 智能门禁的设备删除
 */
if ($op == 'delete') {
    if ($id) {
        $item = pdo_get('xcommunity_building_device', array('id' => $id), array());
        if ($item) {
            if ($item['category'] == 9) {
                require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
                deleteDevice($item['appid'], $item['appkey'], $item['appsecret'], $item['device_code']);
            }
            if ($item['category'] == 10) {
                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                $region = pdo_get('xcommunity_region', array('id' => $item['regionid']));
                delDevice($item['apikey'], $item['secretkey'], $item['deviceid'], $region['villageid'], $item['accountid']);
            }
            $r = pdo_delete('xcommunity_building_device', array('id' => $id));
            if ($r) {
                util::permlog('智能门禁-删除', '区域名称:' . $item['title']);
                itoast('操作成功', $this->createWebUrl('guard', array('op' => 'list', 'regionid' => $regionid)), 'success', true);
            }
        }

    }
}
/**
 * 智能门禁的设备的二维码
 */
if ($op == 'qrcreate') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.*,t2.title as rtitle from " . tablename("xcommunity_building_device") . "as t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where  t1.id =:id", array(':id' => $id));
    $time = TIMESTAMP;
    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&do=lock&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
    $type = $item['type'] == 2 ? '大门' : '单元门';
    $temp = $type . "-" . $item['title'] . "-" . $item['unit'] . ".png";
    $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/guard/" . $_W['uniacid'] . "/" . $item['rtitle'] . "/";
    $qr = createQr($url, $temp, $tmpdir);
    echo $qr;
    exit();
}
/**
 * 智能门禁--门禁分组
 */
if ($op == 'group') {
    $p = in_array($_GPC['p'], array('add', 'list', 'del', 'cat', 'open')) ? $_GPC['p'] : 'list';
    /**
     * 门禁的分组列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user && $user[type] != 1) {
            //普通管理员
            $condition .= " AND t1.uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT t1.*,t2.title as regiontitle FROM" . tablename('xcommunity_guard_group') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_guard_group') . "t1 WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/guard/group/list');
    }
    /**
     * 门禁分组的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_guard_group', array('id' => $id), array());
            $build = pdo_getall('xcommunity_guard_group_build', array('groupid' => $id), array('buildid'));
            $buildids = array();
            foreach ($build as $key => $val) {
                $buildids[] = $val['buildid'];
            }
            $category = pdo_getall('xcommunity_guard_group_category', array('groupid' => $id), array('categoryid'));
            $categoryids = array();
            foreach ($category as $k => $v) {
                $categoryids[] = $v['categoryid'];
            }
            $categories = pdo_getall('xcommunity_building_device', array('regionid' => $item['regionid']), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'title'    => trim($_GPC['title']),
                'remark'   => $_GPC['remark'],
                'status'   => intval($_GPC['status'])
            );
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_guard_group', $data);
                $groupid = pdo_insertid();
            }
            else {
                pdo_update('xcommunity_guard_group', $data, array('id' => $id));
                $groupid = $id;
                pdo_delete('xcommunity_guard_group_build', array('groupid' => $id));
                pdo_delete('xcommunity_guard_group_category', array('groupid' => $id));
            }
            if ($groupid) {
                $categories = $_GPC['categoryid'];
                foreach ($categories as $k => $v) {
                    $da = array(
                        'uniacid'    => $_W['uniacid'],
                        'groupid'    => $groupid,
                        'categoryid' => $v,
                    );
                    pdo_insert('xcommunity_guard_group_category', $da);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/guard/group/add');
    }
    /**
     * 门禁分组的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        $item = pdo_get('xcommunity_guard_group', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_guard_group', array('id' => $id))) {
                if (pdo_delete('xcommunity_guard_group_build', array('groupid' => $id)) || pdo_delete('xcommunity_guard_group_category', array('groupid' => $id))) {
                    itoast('删除成功', referer(), 'success');
                }
            }
        }
    }
    /**
     * 门禁分组--小区下的设备
     */
    if ($p == 'cat') {
        $regionid = intval($_GPC['regionid']);
        $list = pdo_getall('xcommunity_building_device', array('regionid' => $regionid), array('title', 'id'), '', 'displayorder asc');
        echo json_encode($list);
    }
    /**
     * 门禁权限下发
     */
    if ($p == 'open') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_guard_group', array('id' => $id), array());
            $arr = util::xqset($item['regionid']);
            if ($arr[a]) {
                $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
            }
            if (!$arr[a] && $arr[b]) {
                $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array('id', 'buildtitle'));
            }
            $count = count(pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'regionid' => $item['regionid'], 'visit' => 0)));
        }
        /**
         * 下发微信开门和呼叫手机号
         */
        if (checksubmit('submit')) {
            $areaid = intval($_GPC['area']);
            $buildid = intval($_GPC['build']);
            $unitid = intval($_GPC['unit']);
            $type = intval($_GPC['type']);
            $category = pdo_getall('xcommunity_guard_group_category', array('groupid' => $id), array('categoryid'));
            $category_ids = _array_column($category, 'categoryid');
            $devices = pdo_getall('xcommunity_building_device', array('id' => $category_ids), array());
            if (empty($devices)) {
                echo json_encode(array('content' => '该门禁分组无绑定门禁'));
                exit();
            }
            $url = $this->createWebUrl('guard', array('op' => 'perLower', 'star' => $lastid, 'areaid' => $areaid, 'buildid' => $buildid, 'unitid' => $unitid, 'type' => $type, 'regionid' => $item['regionid'], 'id' => $id));
            message('正在下发！', $url, 'success');
        }

        include $this->template('web/core/guard/group/open');
    }
}
/**
 * 智能门禁发卡管理
 */
if ($op == 'comb') {
    $p = in_array($_GPC['p'], array('add', 'list', 'del', 'cat', 'black', 'update', 'cloud', 'delete', 'pladd', 'edit', 'pldown', 'pldowns')) ? $_GPC['p'] : 'list';
    /**
     * 智能门禁发卡列表
     */
    if ($p == 'list') {
        // 批量删除
        if (checksubmit('pldel')) {
            if ($_GPC['ids']) {
                $ids = implode(',', $_GPC['ids']);
            }
            $url = $this->createWebUrl('guard', array('op' => 'pldel', 'star' => 0, 'ids' => $ids));
            message('正在删除！', $url, 'success');
        }
        // 一键下发
        if (checksubmit('pldown')) {
            if ($_GPC['ids']) {
                $ids = implode(',', $_GPC['ids']);
            }
            $url = $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'pldown', 'star' => 0, 'ids' => $ids));
            message('正在下发！', $url, 'success');
        }
        $pindex = max(1, intval($_GPC['page']));
        $export = intval($_GPC['export']); //是导出还是正常展示
        $psize = $export == 1 ? 1000000 : 20; //debug 临时设置导出
        /**
         * 查卡信息
         */
//        $cards = pdo_getall('xcommunity_building_device_cards',array('uniacid'=>$_W['uniacid'],'type'=>1));
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $star = intval($_GPC['star']);
        $condition['id >'] = $star;
        $regionid = intval($_GPC['regionid']);
        if ($regionid) {
            $condition['regionid'] = $regionid;
            $arr = util::xqset($_GPC['regionid']);
            if ($_GPC['buildid']) {
                $con = array();
                $con['regionid'] = $regionid;
                $con['buildid'] = intval($_GPC['buildid']);
                if ($_GPC['unitid']) {
                    $con['unitid'] = intval($_GPC['unitid']);
                }
                $condition_unit = " buildid=:buildid";
                $param_unit[':buildid'] = intval($_GPC['buildid']);
                $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where $condition_unit order by id asc ", $param_unit);
                foreach ($units as $k => $v) {
                    $units[$k]['unit'] = $v['unit'] . $arr[c1];
                }
                $rooms = pdo_getall('xcommunity_member_room', $con, array('id', 'address'), 'id');
                $address = _array_column($rooms, 'address');
                $condition['address'] = $address;
            }
            $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where t1.regionid=:regionid order by t1.id asc ", array(':regionid' => $_GPC['regionid']));
            foreach ($builds as $k => $v) {
                $builds[$k]['title'] = $v['title'] ? $v['title'] . $arr[a1] . $v['buildtitle'] . $arr[b1] : $v['buildtitle'] . $arr[b1];
            }
        }
        if ($user['type'] == 3) {
            //普通管理员
            $condition['regionid'] = explode(',', $user['regionid']);
        }
        $keyword = trim($_GPC['keyword']);
        if (!empty($keyword)) {
            if (is_numeric($keyword)) {
                $condition['mobile LIKE'] = "%" . $keyword . "%";
            }
            else {
                $condition['address LIKE'] = "%" . $keyword . "%";
            }
        }
        if (!empty($_GPC['cardno'])) {
            $condition['cardno LIKE'] = "%" . $_GPC['cardno'] . "%";
        }
        $status = intval($_GPC['status']);
        if ($status) {
            $condition['status'] = $status;
        }
        $cards = pdo_getslice('xcommunity_building_device_cards', $condition, array($pindex, $psize), $total, array('id', 'cardno', 'use', 'mobile', 'address', 'status', 'enddate', 'createtime', 'regionid', 'device_code'), 'id', array('id DESC'));
        $pager = pagination($total, $pindex, $psize);
        /**
         * 查卡绑定的门禁
         */
        $cards_ids = _array_column($cards, 'id');
        $cards_cateryies = pdo_getall('xcommunity_building_device_cards_category', array('cardid' => $cards_ids), array('categoryid', 'cardid'));
        $cards_cateryies_ids = _array_column($cards_cateryies, 'categoryid');
        /**
         * 查门禁信息
         */
        $devices = pdo_getall('xcommunity_building_device', array('id' => $cards_cateryies_ids), array('title', 'id'), 'id');
//        $params[':uniacid'] = $_W['uniacid'];
        /**
         * 查小区信息
         */
        $cards_regionids = _array_column($cards, 'regionid');
        $regionss = pdo_getall('xcommunity_region', array('id' => $cards_regionids), array('title', 'id'), 'id');
        $list = array();
        foreach ($cards as $val) {
            // 发卡绑定的多个门禁
            $deviceTitle = '';
            foreach ($cards_cateryies as $k => $v) {
                if ($v['cardid'] == $val['id']) {
                    $deviceTitle .= $devices[$v['categoryid']]['title'] . '/';
                }
            }
            $deviceTitle = ltrim(rtrim($deviceTitle, '/'), '/');
            $deviceCodes = unserialize($val['device_code']);
            $codes = '';
            foreach ($deviceCodes as $k => $v) {
                $codes .= $v['device_code'] . ',';
            }
            $codes = xtrim($codes);
            $list[] = array(
                'id'          => $val['id'],
                'cardno'      => $val['cardno'],
                'use'         => $val['use'],
                'mobile'      => $val['mobile'],
                'address'     => $val['address'],
                'status'      => $val['status'],
                'enddate'     => date('Y-m-d H:i', $val['enddate']),
                'createtime'  => date('Y-m-d H:i', $val['createtime']),
                'region'      => $regionss[$val['regionid']]['title'],
                'device'      => $deviceTitle,
                'device_code' => $codes
            );
            $lastid = $val['id'];
        }
        if ($export) {
            model_execl::export($list, array(
                "title"   => "卡号数据-" . date('Y-m-d', time()),
                "columns" => array(
                    array(
                        'title' => '小区名称',
                        'field' => 'region',
                        'width' => 24
                    ),
                    array(
                        'title' => '门禁名称',
                        'field' => 'device',
                        'width' => 24
                    ),
                    array(
                        'title' => '卡号',
                        'field' => 'cardno',
                        'width' => 12
                    ),
                    array(
                        'title' => '手机',
                        'field' => 'mobile',
                        'width' => 20
                    ),
                    array(
                        'title' => '房号',
                        'field' => 'address',
                        'width' => 20
                    ),
                    array(
                        'title' => '设备号',
                        'field' => 'device_code',
                        'width' => 20
                    ),
                    array(
                        'title' => '截止时间',
                        'field' => 'enddate',
                        'width' => 24
                    ),
                    array(
                        'title' => '添加时间',
                        'field' => 'createtime',
                        'width' => 24
                    )
                )
            ));
            unset($list);
//            $url = $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'list', 'star' => $lastid, 'regionid' => $regionid, 'keyword' => $keyword, 'page' => $pindex++));
//            message('正在发送导出下一组！', $url, 'success');
        }

        include $this->template('web/core/guard/comb/list');
    }
    /**
     * 发卡添加的模版
     */
    if ($p == 'add') {
        $enddate = date('Y-m-d H:i:s', strtotime('+1year'));
        include $this->template('web/core/guard/comb/add');
    }
    /**
     * 删除发卡
     */
    if ($p == 'del') {
        $cardid = intval($_GPC['id']);
        $condition['id'] = $cardid;
        $card = pdo_get('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id', 'regionid', 'roomid','cardids'));
        if ($card['regionid']) {
            $region = pdo_get('xcommunity_region', array('id' => $card['regionid']));
        }
        //$room = pdo_get('xcommunity_member_room', array('id' => $card['roomid']));
        $devices = unserialize($card['device_code']);
        foreach ($devices as $val) {
            if ($val['category'] == 10) {
                //笛虎设备
                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                $cards = explode(',',$card['cardids']);
                foreach ($cards as $v){
                    $content = explode('-',$v);
                    delCard($val['apikey'], $val['secretkey'], $val['deviceid'], $content[1], $content[0], $region['accountid']);
                }
            }
            else {
                $list[0] = array(
                    'content'    => $card['cardno'],
                    'expiretime' => ''
                );
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
                $res1 = delCards($val['device_code'], $list, "1");
                //删除黑名单
                $res2 = delCards($val['device_code'], $list, "2");
            }

        }
        pdo_update('xcommunity_building_device_cards', array('status' => 2), array('id' => $cardid));
        util::permlog('发卡管理-删除下发卡号', '卡号:' . $card['cardno'] . '(' . $card['address'] . ')');
        itoast('删除成功', referer(), 'success');
    }
    /**
     * 加入黑名单
     */
    if ($p == 'black') {
        $id = intval($_GPC['id']);
        $condition['id'] = $id;
        $card = pdo_get('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id'));
        $devices = unserialize($card['device_code']);
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        foreach ($devices as $val) {
            $cards[0] = array(
                'content'    => $card['cardno'],
                'expiretime' => ''
            );
            $result = addBlackCards($val['device_code'], $cards);
        }
        if ($result['success'] == 1) {
            pdo_update('xcommunity_building_device_cards', array('use' => 2), array('id' => $id));
            util::permlog('发卡管理-加入黑名单', '卡号:' . $item['cardno'] . '(' . $item['address'] . ')');
            echo json_encode(array('status' => 1));
            exit();
        }
        else {
            echo json_encode(array('content' => $result['message']));
            exit();
        }
    }
    /**
     * 恢复白名单
     */
    if ($p == 'update') {
        $id = intval($_GPC['id']);
        $condition['id'] = $id;
        $card = pdo_get('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id'));
        $devices = unserialize($card['device_code']);
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        foreach ($devices as $val) {
            $cards[0] = array(
                'content'    => $card['cardno'],
                'expiretime' => ''
            );
            $result = addWhiteCards($val['device_code'], $cards);
        }
        if ($result['success'] == 1) {
            pdo_update('xcommunity_building_device_cards', array('use' => 1, 'status' => 1), array('id' => $id));
            util::permlog('发卡管理-恢复白名单', '卡号:' . $item['cardno'] . '(' . $item['address'] . ')');
            echo json_encode(array('status' => 1));
            exit();
        }
        else {
            echo json_encode(array('content' => $result['message']));
            exit();
        }
    }
    /**
     * 手动同步
     */
    if ($p == 'cloud') {
        $id = intval($_GPC['id']);
        $condition['id'] = $id;
        $card = pdo_get('xcommunity_building_device_cards', $condition, array());
        $devices = unserialize($card['device_code']);
        if ($card['regionid']) {
            $region = pdo_get('xcommunity_region', array('id' => $card['regionid']));
        }
        $room = pdo_get('xcommunity_member_room', array('id' => $card['roomid']));
        $cardids = '';
        foreach ($devices as $key => $val) {
            if ($val['category'] == 10) {
                //笛虎设备
                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                //$xroom = $room['area'].$room['build'].$room['room'];
                //addMember($val['apikey'], $val['secretkey'], $val['deviceid'], $room, 'xxx', '321084198610262222', '18851000500', $region['accountid']);
                //$result = addCard($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, '1234', $card['cardno'], $region['accountid']); //登录物业
//                $content = $result['cardid'];
//                $cardids .= $content['cardid'].',';
                $xroom = strlen($room['room']) == 4 ? $room['room'] : '0'.$room['room'];
                $result = addCard($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, '1234', $card['cardno'], $region['accountid']); //登录物业
                $content = $result['cardid'];
                $cardids .= $content['cardid'].'-'.$content['roomid'].',';

            }
            else {
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
                $cards[0] = array(
                    'content'    => $card['cardno'],
                    'expiretime' => ''
                );
                $result = addWhiteCards($val['device_code'], $cards);
            }
//                $result = addWhiteCards($val['device_code'], $cards);
        }
        if ($result['success'] == 1) {
            pdo_update('xcommunity_building_device_cards', array('status' => 1, 'cardids' => $cardids), array('id' => $id));
            util::permlog('发卡管理-手动同步', '卡号:' . $card['cardno'] . '(' . $item['address'] . ')');
            echo json_encode(array('status' => 1));
            exit();
        }
        else {
            echo json_encode($result);
            exit();
        }
    }
    /**
     * 强制删除卡号
     */
    if ($p == 'delete') {
        $cardid = intval($_GPC['id']);
        $condition['id'] = $cardid;
        $card = pdo_get('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id', 'regionid', 'roomid','cardids'));
        if ($card['regionid']) {
            $region = pdo_get('xcommunity_region', array('id' => $card['regionid']));
        }
        //$room = pdo_get('xcommunity_member_room', array('id' => $card['roomid']));
        $devices = unserialize($card['device_code']);

        foreach ($devices as $val) {
            if ($val['category'] == 10) {
                //笛虎设备
                require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                $cards = explode(',',$card['cardids']);
                foreach ($cards as $v){
                    $content = explode('-',$v);
                    delCard($val['apikey'], $val['secretkey'], $val['deviceid'], $content[1], $content[0], $region['accountid']);
                }
            }
            else {
                $list[0] = array(
                    'content'    => $card['cardno'],
                    'expiretime' => ''
                );
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
                $res1 = delCards($val['device_code'], $list, "1");
                //删除黑名单
                $res2 = delCards($val['device_code'], $list, "2");
            }

        }
        /**
         * 删除卡
         */
        pdo_delete('xcommunity_building_device_cards', array('id' => $cardid));
        pdo_delete('xcommunity_building_device_cards_category', array('cardid' => $cardid));
        util::permlog('发卡管理-强制删除', '卡号:' . $card['cardno'] . '(' . $card['address'] . ')');
        itoast('删除成功', referer(), 'success');
    }
    /**
     * 批量发卡
     */
    if ($p == 'pladd') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $deviceids = explode(',', xtrim($_GPC['ids']));
            if (empty($_GPC['ids'])) {
                echo json_encode(array('content' => '操作失败,必选选择门禁'));
                exit();
            }
            $rows = model_execl::read('guard');
            $sql = "select device_code from" . tablename('xcommunity_building_device') . "where id in (" . implode(',', $deviceids) . ") ";
            $devices = pdo_fetchall($sql);
            require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
            foreach ($rows as $rownum => $col) {
                //执行录入系统
                if ($rownum > 0) {
                    if ($col[2] || $col[3]) {
                        $data = array(
                            'regionid'    => intval($_GPC['regionid']),
                            'cardno'      => $col[2],
                            'type'        => 1,
                            'createtime'  => TIMESTAMP,
                            'uid'         => $_W['uid'],
                            'uniacid'     => $_W['uniacid'],
                            'oper'        => 1,
                            'use'         => 1,
                            'enddate'     => strtotime($_GPC['enddate']) + 86400 - 1,
                            'mobile'      => $col[3],
                            'address'     => $col[4],
                            'device_code' => serialize($devices),
                        );
                        $item = pdo_get('xcommunity_building_device_cards', array('cardno' => $col[2], 'regionid' => $data['regionid'], 'mobile' => $data['mobile'], 'address' => $data['address']));
                        if (empty($item)) {
                            //远程下发到设备
//                            $cards[0] = array(
//                                'content'    => $col[2],
//                                'expiretime' => ''
//                            );
//                            foreach ($devices as $key => $val) {
//                                $result = addWhiteCards($val['device_code'], $cards);
//                            }
                            $data['status'] = $result['success'] == 1 ? 1 : 2;
                            $r = pdo_insert('xcommunity_building_device_cards', $data);
                            if ($r) {
                                $cardid = pdo_insertid();
                                foreach ($deviceids as $k => $v) {
                                    $da = array(
                                        'uniacid'    => $_W['uniacid'],
                                        'cardid'     => $cardid,
                                        'categoryid' => $v,
                                    );
                                    pdo_insert('xcommunity_building_device_cards_category', $da);
                                }
                                util::permlog('发卡管理-批量发卡', '卡号:' . $data['cardno'] . '(' . $data['address'] . ')');
                            }
                            else {
                                echo json_encode(array('content' => '操作失败,请检查数据库'));
                                exit();
                            }
                        }
                        else {
//                            $cards[0] = array(
//                                'content'    => $col[2],
//                                'expiretime' => ''
//                            );
//                            foreach ($devices as $key => $val) {
//                                $result = addWhiteCards($val['device_code'], $cards);
//                            }
                            $data['status'] = $result['success'] == 1 ? 1 : 2;
                            pdo_delete('xcommunity_building_device_cards_category', array('cardid' => $item['id']));
                            $r = pdo_update('xcommunity_building_device_cards', $data, array('id' => $item['id']));
                            if ($r) {
                                $cardid = $item['id'];
                                foreach ($deviceids as $k => $v) {
                                    $da = array(
                                        'uniacid'    => $_W['uniacid'],
                                        'cardid'     => $cardid,
                                        'categoryid' => $v,
                                    );
                                    pdo_insert('xcommunity_building_device_cards_category', $da);
                                }
                                util::permlog('发卡管理-批量发卡修改', '卡号:' . $data['cardno'] . '(' . $data['address'] . ')');
                            }
                        }

                    }
                }
            }
            echo json_encode(array('content' => '操作成功,发卡成功'));
            exit();
        }
        $enddate = date('Y-m-d H:i:s', strtotime('+1year'));
        include $this->template('web/core/guard/comb/pladd');
    }
    /**
     * 发卡编辑
     */
    if ($p == 'edit') {
        if ($id) {
            $item = pdo_get('xcommunity_building_device_cards', array('id' => $id));
            if ($item['regionid']) {
                $region = pdo_get('xcommunity_region', array('id' => $item['regionid']));
            }
            $arr = util::xqset($item['regionid']);
            $addr = pdo_get('xcommunity_member_room', array('regionid' => $item['regionid'], 'address' => $item['address']));
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
            $condition = " regionid={$item['regionid']}";
            if ($addr['areaid']) {
                $condition .= " and areaid={$addr['areaid']}";
            }
            $builds = pdo_fetchall("select id,buildtitle from" . tablename('xcommunity_build') . " where $condition");
            $units = pdo_getall('xcommunity_unit', array('buildid' => $addr['buildid']), array('id', 'unit'));
            $rooms = pdo_getall('xcommunity_member_room', array('buildid' => $addr['buildid'], 'unitid' => $addr['unitid']), array('id', 'room'));
            $categories = pdo_getall('xcommunity_building_device', array('regionid' => $item['regionid'], 'category' => array(4, 7, 8, 10)));
            $cate = pdo_getall('xcommunity_building_device_cards_category', array('cardid' => $id), array('categoryid'));
            $categoryids = array();
            if ($cate) {
                foreach ($cate as $k => $v) {
                    $categoryids[] = $v['categoryid'];
                }
            }
            $enddate = date('Y-m-d H:i:s', $item['enddate']);
        }
        //$enddate = date('Y-m-d H:i:s', strtotime('+1year'));
        if ($_W['isajax']) {
            $deviceids = $_GPC['deviceids'];
            if (empty($deviceids)) {
                echo json_encode(array('content' => '操作失败,必选选择门禁'));
                exit();
            }
            $cardno = trim($_GPC['cardno']);
            $sql = "select device_code,category,apikey,secretkey,deviceid from" . tablename('xcommunity_building_device') . "where id in (" . implode(',', $deviceids) . ") ";
            $devices = pdo_fetchall($sql);
            $room = pdo_get('xcommunity_member_room', array('id' => $_GPC['addressid']), array('address', 'room', 'roomid','area','build','regionid'));
            $cardids = '';
            foreach ($devices as $key => $val) {
                if ($val['category'] == 10) {
                    //笛虎设备
                    require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
                    $xroom = strlen($room['room']) == 4 ? $room['room'] : '0'.$room['room'];
                    $result = addCard($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, '1234', $cardno, $region['accountid']); //登录物业
                    $content = $result['cardid'];
                    $cardids .= $content['cardid'].'-'.$content['roomid'].',';

                }
                else {
                    require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
                    $cards[0] = array(
                        'content'    => $cardno,
                        'expiretime' => ''
                    );
                    $result = addWhiteCards($val['device_code'], $cards);
                }
//                $result = addWhiteCards($val['device_code'], $cards);
            }
            $room = pdo_get('xcommunity_member_room', array('id' => $_GPC['addressid']), array('address'));
            $data = array(
                'regionid'    => intval($_GPC['regionid']),
                'cardno'      => trim($_GPC['cardno']),
                'type'        => 1,
                'createtime'  => TIMESTAMP,
                'uid'         => $_W['uid'],
                'uniacid'     => $_W['uniacid'],
                'oper'        => 1,
                'use'         => 1,
                'status'      => $result['success'] == 1 ? 1 : 2,
                'enddate'     => strtotime($_GPC['enddate']),
                'mobile'      => $_GPC['mobile'],
                'roomid'      => intval($_GPC['addressid']),
                'address'     => $room['address'],
                'device_code' => serialize($devices),
                'cardids'     => xtrim($cardids)
            );
            pdo_update('xcommunity_building_device_cards', $data, array('id' => $id));
            util::permlog('发卡管理-编辑发卡', '卡号:' . $data['cardno'] . '(' . $data['address'] . ')');
            if (pdo_delete('xcommunity_building_device_cards_category', array('cardid' => $id))) {
                $cardid = $id;
                foreach ($deviceids as $k => $v) {
                    $da = array(
                        'uniacid'    => $_W['uniacid'],
                        'cardid'     => $cardid,
                        'categoryid' => $v,
                    );
                    pdo_insert('xcommunity_building_device_cards_category', $da);
                }
                echo json_encode(array('content' => '操作成功,发卡成功'));
                exit();
            }
            else {
                echo json_encode(array('content' => '操作失败,请检查数据库'));
                exit();
            }
        }
        include $this->template('web/core/guard/comb/edit');
    }
    /**
     * 一键下发
     */
    if ($p == 'pldown') {
        if ($_GPC['ids']) {
            $ids = explode(',', $_GPC['ids']);
        }
        $psize = 50;
        $star = intval($_GPC['star']);
        $condition = array();
        $condition['id >'] = $star;
        $condition['uniacid'] = $_W['uniacid'];
        if ($ids) {
            $condition['id'] = $ids;
        }
        $list = pdo_getall('xcommunity_building_device_cards', $condition, array('cardno', 'device_code', 'id'), 'id', array('id ASC'), $psize);
        if (empty($list)) {
            message('下发完成', $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'list')), 'success');
            exit();
        }
        require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
        foreach ($list as $k => $v) {
            $deviceCodes = unserialize($v['device_code']);
            $cards[0] = array(
                'content'    => $v['cardno'],
                'expiretime' => ''
            );
            foreach ($deviceCodes as $key => $val) {
                $result = addWhiteCards($val['device_code'], $cards);
            }
            if ($result['success'] == 1) {
                pdo_update('xcommunity_building_device_cards', array('status' => 1), array('id' => $v['id']));
            }
        }
        foreach ($list as $row) {
            $lastid = $row['id'];
        }
        $url = $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'pldown', 'star' => $lastid, 'ids' => $_GPC['ids']));
        message('正在下发！', $url, 'success');
    }
}
/**
 * 智能门禁--发卡获取该小区下的发卡设备
 */
if ($op == 'device') {
    $regionid = intval($_GPC['regionid']);
    $devices = pdo_getall('xcommunity_building_device', array('regionid' => $regionid, 'category' => array(4, 7, 8, 10)), array());
    echo json_encode($devices);
    exit();
}
/**
 * 智能门禁--刷卡记录
 */
if ($op == 'log') {
    $p = in_array($_GPC['p'], array('send', 'list')) ? $_GPC['p'] : 'list';
    /**
     * 刷卡记录的列表
     */
    if ($p == 'list') {
        // 批量删除刷卡记录
        if (checksubmit('pldel')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                $logs = pdo_getall('xcommunity_building_device_log', array('id' => $ids), array());
                if ($logs) {
                    pdo_delete('xcommunity_building_device_log', array('id' => $ids));
                }
                util::permlog('', '批量删除刷卡记录');
                itoast('删除成功', referer(), 'success', true);
            }
        }
        // 一键清空刷卡记录
        if (checksubmit('clear')) {
            $logs = pdo_getall('xcommunity_building_device_log', array('uniacid' => $_W['uniacid']), array());
            if ($logs) {
                pdo_delete('xcommunity_building_device_log', array('uniacid' => $_W['uniacid']));
            }
            util::permlog('', '一键清空刷卡记录');
            itoast('删除成功', referer(), 'success', true);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition['uniacid'] = $_W['uniacid'];
        $logs = pdo_getslice('xcommunity_building_device_log', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
        $devices = pdo_getall('xcommunity_building_device', array('uniacid' => $_W['uniacid']), array('id', 'device_code', 'title', 'unit', 'unitid', 'regionid'));
        $devices_code = _array_column($devices, NULL, 'device_code');
        $devices_regionid = _array_column($devices, 'regionid');
        $units = pdo_getall('xcommunity_unit', array('uniacid' => $_W['uniacid']), array('id', 'unit'));
        $units_list = _array_column($units, NULL, 'id');
        $regions = pdo_getall('xcommunity_region', array('id' => $devices_regionid), array('id', 'title'));
        $regions_list = _array_column($regions, NULL, 'id');
        $list = array();
        foreach ($logs as $k => $v) {
            $list[] = array(
                'id'          => $v['id'],
                'cardno'      => $v['cardno'],
                'sktime'      => $v['sktime'],
                'type'        => $v['type'],
                'createtime'  => $v['createtime'],
                'regiontitle' => $regions_list[$devices_code[$v['device_code']]['regionid']]['title'],
                'unit'        => $units_list[$devices_code[$v['device_code']]['unitid']]['unit'] ? $units_list[$devices_code[$v['device_code']]['unitid']]['unit'] . "单元" : "",
                'devicetitle' => $devices_code[$v['device_code']]['title']
            );
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/guard/log/list');
    }
    /**
     * 刷卡记录--同步
     */
    if ($p == 'send') {
        //获取设备号 刷卡记录bug
        $devices = pdo_getall('xcommunity_building_device', array('uniacid' => $_W['uniacid']), array('device_code'));
        print_r($devices);
        load()->func('communication');
        $psize = 50;
        $start = 0;
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        foreach ($devices as $device) {
            $data = array(
                'identity'  => $device['device_code'],
                'starttime' => date('YmdHis', $starttime),
                'endtime'   => date('YmdHis', $endtime),
                'start'     => $start,
                'limit'     => $psize
            );
            print_r($data);
            $param = json_encode($data);
            $result = http_post('http://122.114.58.8:8018/cp/mj/queryOpenRecords.ext', $param);
            print_r($result);
            exit();
            $result = @json_decode($result['content'], true);
            $list = $result['list'];
            if ($list) {
                //循环插入到数据表
                $sql = 'insert into ' . tablename('xcommunity_building_device_log') . ' ("unaicid","device_code","cardno","sktime","createtime","uid") values';
                $uniacid = $_W['uniacid'];
                $devices_code = $device['evices_code'];
                foreach ($list as $k => $v) {
                    $cardno = $v['cardno'];
                    $sktime = $v['time'];
                    $time = TIMESTAMP;
                    $uid = $_W['uid'];
                    $sql = $sql . "($uniacid,$devices_code,$cardno,$sktime,$time,$uid),";
//                $dat = array(
//                    'uniacid' => $_W['uniacid'],
//                    'device_code' => $v['identity'],
//                    'cardno' => $v['cardNo'],
//                    'sktime' => $v['time'],
//                    'type' => $v['type'],
//                    'createtime' => TIMESTAMP,
//                    'uid' => $_W['uid']
//                );
//                pdo_insert('xcommunity_building_device_log', $dat);

                }
                print_r($sql);
                exit();
                pdo_query($sql);
            }

        }


        $start += $psize;
//        $getCount += $psize;
//        if ($result['isFinish'] == 1) {
//            echo json_encode(array('status' => 'end'));
//            exit();
//        }
//        if (empty($list)) {
//            echo json_encode(array('status' => 'emp'));
//            exit();
//        }

    }

}
/**
 * 智能门禁--批量生成设备的二维码
 */
if ($op == 'qrpl') {
    $list = pdo_fetchall("select t1.*,t2.title as rtitle from " . tablename("xcommunity_building_device") . "as t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where  t1.uniacid =:uniacid order by t1.displayorder asc", array(':uniacid' => $_W['uniacid']));
    foreach ($list as $k => $v) {
        $time = TIMESTAMP;
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&do=lock&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
        $type = $v['type'] == 2 ? '大门' : '单元门';
        $temp = $type . "-" . $v['title'] . "-" . $v['unit'] . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/guard/" . $_W['uniacid'] . "/" . $v['rtitle'] . "/";
        $qr = createQr($url, $temp, $tmpdir);
    }
    itoast('更新成功', $this->createWebUrl('guard', array('op' => 'list')), 'success');
}
/**
 * 智能门禁的二维码下载
 */
if ($op == 'download') {
    $filename = MODULE_ROOT . "/data/qrcode/guard/guard.zip";
    $path = MODULE_ROOT . '/data/qrcode/guard';
    download($filename, $path);
}
/**
 * 智能门禁的开门记录
 */
if ($op == 'open') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_open_log', array('id' => $id));
            }
            util::permlog('', '批量删除开门记录');
            itoast('删除成功', referer(), 'success', ture);
        }
    }
//    $regionid = intval($_GPC['regionid']);
    $condition = ' t1.uniacid=:uniacid ';
    $params[':uniacid'] = $_W['uniacid'];
//    if ($regionid) {
//        $condition .= ' and t1.regionid=:regionid';
//        $params[':regionid'] = $regionid;
//    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if ($_GPC['mobile']) {
        $condition .= " and t3.mobile like '%{$_GPC['mobile']}%'";
    }
    if ($_GPC['realname']) {
        $condition .= " and t3.realname like '%{$_GPC['realname']}%'";
    }
    if ($_GPC['idcard']) {
        $condition .= " and t3.idcard like '%{$_GPC['idcard']}%'";
    }
    if ($_GPC['export'] == 1) {
        $sql = "select t1.id,t4.title,t3.realname,t3.mobile,t1.type,t1.createtime,t5.address,t1.isopen from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc";
        $xqlist = pdo_fetchall($sql, $params);
        if (empty($xqlist)) {
            itoast('暂无数据导出', referer(), 'error');
            exit();
        }
        foreach ($xqlist as $k => $val) {
            $xqlist[$k]['cctime'] = date('Y-m-d H:i', $val['createtime']);
            $xqlist[$k]['isopen'] = $val['isopen'] == 1 ? '成功' : '失败';
        }
        model_execl::export($xqlist, array(
            "title"   => "开门数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '小区名称',
                    'field' => 'title',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '地址',
                    'field' => 'address',
                    'width' => 12
                ),
                array(
                    'title' => '门禁位置',
                    'field' => 'type',
                    'width' => 18
                ),
                array(
                    'title' => '开门状态',
                    'field' => 'cctime',
                    'width' => 18
                ),
                array(
                    'title' => '开门时间',
                    'field' => 'cctime',
                    'width' => 18
                ),
            )
        ));
    }
    //显示访客记录信息
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;

    $sql = "select t1.id,t4.title,t3.realname,t3.mobile,t1.type,t1.createtime,t5.address,t5.area,t5.build,t5.unit,t5.room,t1.isopen from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $sql = "select count(*) from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc";
    $total = pdo_fetchcolumn($sql, $params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/core/guard/open');
}
/**
 * 智能门禁--用户管理
 */
if ($op == 'member') {
    // 批量开通门禁
    if (checksubmit('openup')) {
        $con['uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $con['id'] = $_GPC['ids'];
        }
        if ($user[type] == 3) {
            //普通管理员
            $con['regionid'] = $user['regionid'];
        }
        $members = pdo_getall('xcommunity_member', $con, array('status', 'open_status', 'id', 'regionid', 'uid'));
        $members_ids = _array_column($members, 'id');
        pdo_update('xcommunity_member', array('open_status' => 1, 'open_door' => 1), array('id' => $members_ids));
        util::permlog('', '一键批量开通业主门禁');
        itoast('审核成功', referer(), 'success', ture);
    }
    // 批量关闭门禁
    if (checksubmit('closeup')) {
        $con['uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $con['id'] = $_GPC['ids'];
        }
        if ($user[type] == 3) {
            //普通管理员
            $con['regionid'] = $user['regionid'];
        }
        $members = pdo_getall('xcommunity_member', $con, array('status', 'open_status', 'id', 'regionid', 'uid'));
        $members_ids = _array_column($members, 'id');
        pdo_update('xcommunity_member', array('open_status' => 0, 'open_door' => 0), array('id' => $members_ids));
        util::permlog('', '一键批量关闭业主门禁');
        itoast('关闭成功', referer(), 'success', ture);
    }
    // 授权门禁
    if (checksubmit('update')) {
        $door = pdo_get('xcommunity_bind_door', array('uid' => $_GPC['uid'], 'regionid' => intval($_GPC['regionid'])), array('id'));
        $id = $door['id'];
        $groupid = intval($_GPC['groupid']);
        $data = array(
            'regionid' => intval($_GPC['regionid']),
            'uid'      => $_GPC['uid']
        );
        //分组id
        //$oldid = pdo_getcolumn('xcommunity_member', array('uid' => $_GPC['uid']), 'groupid');
        if ($id) {
            pdo_update('xcommunity_bind_door', $data, array('id' => $id));
            pdo_delete('xcommunity_bind_door_device', array('doorid' => $id));
//            if ($oldid) {
//                //删除分组
//                pdo_delete('xcommunity_bind_door_device', array('groupid' => $oldid, 'doorid' => $id));
//            }
        }
        else {
            $data['uniacid'] = $_W['uniacid'];
            pdo_insert('xcommunity_bind_door', $data);
            $id = pdo_insertid();
        }
        pdo_update('xcommunity_member', array('groupid' => $groupid, 'open_door' => 1), array('uid' => $_GPC['uid']));
        $d = array();
        if ($groupid) {
            $devices = pdo_getall('xcommunity_guard_group_category', array('groupid' => $groupid), array('categoryid'));
            foreach ($devices as $k => $v) {
                //门禁套餐
                $dat = array(
                    'doorid'   => $id,
                    'deviceid' => $v['categoryid'],
                    'groupid'  => $groupid
                );
                pdo_insert('xcommunity_bind_door_device', $dat);
                $d[] = $v['categoryid'];
            }
        }
//        $dev = pdo_get('xcommunity_building_device',array('id'=>intval($_GPC['deviceid'])),array('category','deviceid','apikey','secretkey'));
//        if($dev['category']==10){
//            //判断是笛虎设备
//            // 人脸设备的创建
//            require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
//            addMember($dev['apikey'], $dev['secretkey'], $dev['deviceid'], '101','xxx','xxxx','18851000500'); //登录物业
//        }

        foreach ($_GPC['deviceid'] as $key => $value) {
            //单独授权
            if (!in_array($value, $d)) {
                $dat = array(
                    'doorid'   => $id,
                    'deviceid' => $value,
                );
                pdo_insert('xcommunity_bind_door_device', $dat);
            }

        }
        util::permlog('', '业主UID:' . $_GPC['uid'] . '绑定门禁');
        itoast("绑定成功", referer(), 'success', true);
    }
    $condition = " t2.uniacid =:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t1.realname LIKE '%{$_GPC['keyword']}%' OR t1.mobile LIKE '%{$_GPC['keyword']}%' OR t6.address LIKE '%{$_GPC['keyword']}%')";
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " AND t2.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
        $arr = util::xqset($_GPC['regionid']);
        if ($_GPC['buildid']) {
            $regionid = intval($_GPC['regionid']);
            $condition .= " AND t6.buildid =:buildid";
            $params[':buildid'] = intval($_GPC['buildid']);
        }
        $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where t1.regionid=:regionid order by t1.id asc ", array(':regionid' => $_GPC['regionid']));
        foreach ($builds as $k => $v) {
            $builds[$k]['title'] = $v['title'] ? $v['title'] . $arr[a1] . $v['buildtitle'] . $arr[b1] : $v['buildtitle'] . $arr[b1];
        }
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t2.regionid in({$user['regionid']})";
    }
    $authstatus = intval($_GPC['authstatus']);
    if ($authstatus) {
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " and t2.opentime between {$starttime} and {$endtime}";
        }
    }
    $open_door = intval($_GPC['open_door']);
    if ($open_door) {
        $condition .= " and t2.open_door=:open_door";
        $params[':open_door'] = $open_door;
    }
//显示业主信息
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select distinct t2.idcard,t2.contract,t2.id,t1.uid,t1.realname,t1.mobile,t2.createtime,t2.remark,t2.status,t2.enable,t2.open_status,t2.regionid,t2.id,t3.title,t4.openid,t2.open_door,t2.voicestatus from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('mc_mapping_fans') . "t4 on t4.uid=t2.uid left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id left join " . tablename('xcommunity_member_room') . " t6 on t6.id = t5.addressid where $condition and t5.id <> '' order by t2.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $key => $val) {
        $con = 't1.memberid=:memberid';
        $par[":memberid"] = $val['id'];
        $bsql = "select t1.status as bstatus,t2.address,t1.id,t1.createtime as bcreatetime,t1.enable from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where $con order by t1.createtime desc ";
        $binds = pdo_fetchall($bsql, $par);
        $list[$key]['bind'] = $binds;
    }
    $tsql = "select count(distinct t2.uid) from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('mc_mapping_fans') . "t4 on t4.uid=t2.uid left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id left join " . tablename('xcommunity_member_room') . " t6 on t6.id = t5.addressid where $condition and t5.id <> '' order by t2.createtime desc ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/member/list');
}
/**
 * 智能门禁--用户管理--设置开门时间
 */
if ($op == 'opentime') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_member', array('id' => $id), array('opentime'));
        $opentime = !empty($item['opentime']) ? date('Y-m-d', $item['opentime']) : date('Y-m-d', TIMESTAMP);
    }

    if (checksubmit('submit')) {
        if (pdo_update('xcommunity_member', array('opentime' => strtotime($_GPC['opentime'])), array('id' => $id))) {
            itoast('修改成功', referer(), 'success');
        }
    }
    include $this->template('web/core/guard/member/opentime');
}
/**
 * 智能门禁--基本设置
 */
if ($op == 'set') {
    if (checksubmit('submit')) {
        foreach ($_GPC['set'] as $key => $val) {
            $sql = "select * from" . tablename('xcommunity_setting') . "where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            if ($key == 'p49') {
                $val = htmlspecialchars_decode($val);
            }
            $data = array(
                'xqkey'   => $key,
                'value'   => $val,
                'uniacid' => $_W['uniacid']
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/core/guard/set');
}
/**
 * 智能门禁发卡的添加
 */
if ($op == 'sub') {
    $deviceids = $_GPC['deviceids'];
    $regionid = intval($_GPC['regionid']);
    $roomid = intval($_GPC['addressid']);
    if ($regionid) {
        $region = pdo_get('xcommunity_region', array('id' => $regionid));
    }
    if (empty($deviceids)) {
        echo json_encode(array('content' => '操作失败,必须选择门禁'));
        exit();
    }
    $cardno = trim($_GPC['cardno']);
    if (empty($cardno)) {
        echo json_encode(array('content' => '操作失败,卡号必填'));
        exit();
    }
    $room = pdo_get('xcommunity_member_room', array('id' => $roomid), array('address', 'room', 'roomid','area','build'));
    //查询绑定的业主
    if($roomid){
        $bind = pdo_getall('xcommunity_member_bind',array('addressid'=>$roomid),array('memberid'));

        $bind_memberids = _array_column($bind,'memberid');

        $members = pdo_getall('xcommunity_member',array('id'=>$bind_memberids),'uid');

        $members_uids= _array_column($members,'uid');

        $users = pdo_getall('mc_members',array('uid'=>$members_uids),array('realname','mobile'));

    }
    $sql = "select device_code,category,apikey,secretkey,deviceid from" . tablename('xcommunity_building_device') . "where id in (" . implode(',', $deviceids) . ") ";
    $devices = pdo_fetchall($sql);
    $cardids = '';
    foreach ($devices as $key => $val) {
        if ($val['category'] == 10) {
            //笛虎设备
            require_once IA_ROOT . '/addons/xfeng_community/plugin/dihu/function.php';
            //$xroom = $room['area'].$room['build'].$room['room'];
            $xroom = strlen($room['room']) == 4 ? $room['room'] : '0'.$room['room'];

            $result = addCard($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, '1234', $cardno, $region['accountid']); //登录物业
            $content = $result['cardid'];
            //$roomid = addMember($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, 'xxx', '13888888888', '321099999999992112', $region['accountid']);
            foreach ($users as $v){
                addMember($val['apikey'], $val['secretkey'], $val['deviceid'], $xroom, $v['realname'],'321099999999992112',$v['mobile'], $region['accountid']);
            }
            $cardids .= $content['cardid'].'-'.$content['roomid'].',';


        }
        else {
            require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
            $cards[0] = array(
                'content'    => $cardno,
                'expiretime' => ''
            );
            $result = addWhiteCards($val['device_code'], $cards);
        }


    }

    $data = array(
        'regionid'    => $regionid,
        'cardno'      => trim($_GPC['cardno']),
        'type'        => 1,
        'createtime'  => TIMESTAMP,
        'uid'         => $_W['uid'],
        'uniacid'     => $_W['uniacid'],
        'oper'        => 1,
        'use'         => 1,
        'status'      => $result['success'] == 1 ? 1 : 2,
        'enddate'     => strtotime($_GPC['enddate']),
        'mobile'      => $_GPC['mobile'],
        'roomid'      => intval($_GPC['addressid']),
        'address'     => $room['address'],
        'device_code' => serialize($devices),
        'cardids'     => xtrim($cardids)
    );
    $r = pdo_insert('xcommunity_building_device_cards', $data);
    if ($r) {
        $cardid = pdo_insertid();
        foreach ($deviceids as $k => $v) {
            $da = array(
                'uniacid'    => $_W['uniacid'],
                'cardid'     => $cardid,
                'categoryid' => $v,
            );
            pdo_insert('xcommunity_building_device_cards_category', $da);
        }
        util::permlog('发卡管理-添加卡', '卡号:' . $data['cardno'] . '(' . $data['address'] . ')');
        echo json_encode(array('content' => '操作成功,发卡成功'));
        exit();
    }
    else {
        echo json_encode(array('content' => '操作失败,请检查数据库'));
        exit();
    }
}
/**
 * 智能门禁--发卡管理--批量删除
 */
if ($op == 'pldel') {
    if ($_GPC['ids']) {
        $ids = explode(',', $_GPC['ids']);
    }
    $psize = 10;
    $star = intval($_GPC['star']);
    $condition = array();
    $condition['id >'] = $star;
    $condition['uniacid'] = $_W['uniacid'];
    if ($ids) {
        $condition['id'] = $ids;
    }
    $list = pdo_getall('xcommunity_building_device_cards', $condition, array('cardno', 'device_code', 'id', 'use'), 'id', array('id ASC'), $psize);
    if (empty($list)) {
        message('删除完成', $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'list')), 'success');
        exit();
    }
    require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/menjin/function.php';
    foreach ($list as $k => $v) {
        $deviceCodes = unserialize($v['device_code']);
        $cards[0] = array(
            'content'    => $v['cardno'],
            'expiretime' => ''
        );
        $use = "1";
        if ($v['use'] == 1) {
            $use = "1";
        }
        if ($v['use'] == 2) {
            $use = "2";
        }
        foreach ($deviceCodes as $key => $val) {
            $result = delCards($val['device_code'], $cards, $use);
        }
//        if ($result['success'] == 1) {
//
//        }
        pdo_delete('xcommunity_building_device_cards_category', array('cardid' => $v['id']));
        pdo_delete('xcommunity_building_device_cards', array('id' => $v['id']));
    }
    foreach ($list as $row) {
        $lastid = $row['id'];
    }
    $url = $this->createWebUrl('guard', array('op' => 'pldel', 'star' => $lastid, 'ids' => $_GPC['ids']));
    message('正在删除！', $url, 'success');
}
/**
 * 智能门禁--开门统计
 */
if ($op == 'data') {
    $condition = ' t1.uniacid=:uniacid ';
    $params[':uniacid'] = $_W['uniacid'];
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.id in({$user['regionid']})";
    }
    else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.id =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    $condi = '';
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    $stime = strtotime(date('Y-m-d', TIMESTAMP));
    $etime = strtotime(date('Y-m-d', TIMESTAMP)) + 86400 - 1;
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condi .= " and createtime between {$starttime} and {$endtime}";
    }
    else {
        $condi .= " and createtime between {$stime} and {$etime}";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select t1.id,t1.title from" . tablename('xcommunity_region') . "t1 where $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $list_ids = _array_column($list, NULL, 'id');
    foreach ($list as $k => $v) {
        $list[$k]['suctotal'] = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_open_log') . " where regionid=:regionid and isopen=1 $condi", array(':regionid' => $v['id']));
        $list[$k]['errtotal'] = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_open_log') . " where regionid=:regionid and isopen=2 $condi", array(':regionid' => $v['id']));
        $list[$k]['total'] = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_open_log') . " where regionid=:regionid $condi", array(':regionid' => $v['id']));
    }
    $sql = "select count(*) from" . tablename('xcommunity_region') . "t1 where $condition ";
    $total = pdo_fetchcolumn($sql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/open_data');
}
/**
 * 切换设备显示状态
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    $status = intval($_GPC['status']);
    $status = $status == 2 || $status == 0 ? 1 : 2;
    if ($id) {
        if (pdo_update('xcommunity_building_device', array('status' => $status), array('id' => $id))) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
/**
 * 一键开通大门
 */
if ($op == 'pldoor') {
    if (checksubmit('plopen')) {

        //批量开通大门
        $con = " t2.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $ids = implode(',', $_GPC['ids']);
            $con .= " and t2.id in({$ids})";
        }
        if ($user['type'] == 3) {
            //普通管理员
            $con .= " and t2.regionid in({$user['regionid']})";
        }

        $sql = "select t2.status,t2.open_status,t2.id,t2.regionid,t2.uid from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc ";
        $members = pdo_fetchall($sql, $par);
        foreach ($members as $k => $v) {
            $device = pdo_get('xcommunity_building_device', array('regionid' => $v['regionid']));
            if ($device) {
                pdo_update('xcommunity_member', array('open_status' => 1, 'open_door' => 1), array('id' => $v['id']));
                $door = pdo_get('xcommunity_bind_door', array('uid' => $v['uid'], 'regionid' => $v['regionid']), array('id'));
                if (empty($door)) {
                    $data = array(
                        'regionid' => $v['regionid'],
                        'uid'      => $v['uid']
                    );

                    $data['uniacid'] = $_W['uniacid'];
                    pdo_insert('xcommunity_bind_door', $data);
                    $id = pdo_insertid();
                }
                else {
                    $id = $door['id'];
                    //pdo_delete('xcommunity_bind_door_device', array('doorid' => $id));
                }
                $devices = pdo_getall('xcommunity_building_device', array('regionid' => $v['regionid'], 'type' => 2), array('id'));
                foreach ($devices as $key => $value) {
                    $item = pdo_get('xcommunity_bind_door_device', array('doorid' => $id, 'deviceid' => $value['id']), array('id'));
                    if (empty($item)) {
                        $dat = array(
                            'doorid'   => $id,
                            'deviceid' => $value['id'],
                        );
                        pdo_insert('xcommunity_bind_door_device', $dat);
                    }

                }
            }

        }
        util::permlog('', '批量审核业主开门');
        itoast('审核成功', referer(), 'success', ture);
    }
}
/**
 * 批量修改到期时间
 */
if ($op == 'pltime') {
    if ($_W['isajax']) {
        $ids = $_GPC['ids'];
        $enddate = strtotime($_GPC['enddate']);
        if ($ids) {
            pdo_update('xcommunity_building_device_cards', array('enddate' => $enddate), array('id' => $ids));
            echo json_encode(array('status' => 1));
            exit();
        }
        else {
            echo json_encode(array('content' => '请先勾选要修改的'));
            exit();
        }
    }
}
/**
 * 权限下发
 */
if ($op == 'perLower') {
    $id = intval($_GPC['id']);
    $category = pdo_getall('xcommunity_guard_group_category', array('groupid' => $id), array('categoryid'));
    $category_ids = _array_column($category, 'categoryid');
    $devices = pdo_getall('xcommunity_building_device', array('id' => $category_ids), array());
    if (!$devices) {
        message('该门禁分组无绑定门禁', $this->createWebUrl('guard', array('op' => 'group', 'p' => 'add', 'id' => $id)), 'error');
        exit();
    }
    $areaid = intval($_GPC['areaid']);
    $buildid = intval($_GPC['buildid']);
    $unitid = intval($_GPC['unitid']);
    $regionid = intval($_GPC['regionid']);
    $type = intval($_GPC['type']);
    $psize = 50;
    $star = intval($_GPC['star']);
    $condition = array();
    $condition['id >'] = $star;
    $condition['uniacid'] = $_W['uniacid'];
    $condition['regionid'] = $regionid;
    if ($areaid) {
        $condition['areaid'] = $areaid;
    }
    if ($buildid) {
        $condition['buildid'] = $buildid;
    }
    if ($unitid) {
        $condition['unitid'] = $unitid;
    }
    $rooms = pdo_getall('xcommunity_member_room', $condition, array('id', 'room'), 'id', array('id ASC'), $psize);
    if (empty($rooms)) {
        message('全部绑定完成', $this->createWebUrl('guard', array('op' => 'member')), 'success');
        exit();
    }
    $rooms_ids = _array_column($rooms, 'id');
    $rooms_list = _array_column($rooms, NULL, 'id');
    $binds = pdo_getall('xcommunity_member_bind', array('uniacid' => $_W['uniacid'], 'addressid' => $rooms_ids), array('id', 'memberid', 'addressid'));
    $binds_ids = _array_column($binds, 'memberid');
    $binds_list = _array_column($binds, NULL, 'memberid');
    $members = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'id' => $binds_ids), array());
    $members_uids = _array_column($members, 'uid');
    $doors = pdo_getall('xcommunity_bind_door', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid, 'uid' => $members_uids), array('uid', 'id'));
    $doors_ids = _array_column($doors, NULL, 'uid');
    //开启手机号下发
    if ($type == 1) {
        $users = pdo_getall('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $members_uids), array('uid', 'mobile'));
        $users_ids = _array_column($users, NULL, 'uid');
    }
    foreach ($members as $k => $v) {
        if (!$doors_ids[$v['uid']]) {
            $data1 = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => $regionid,
                'uid'      => $v['uid']
            );
            pdo_insert('xcommunity_bind_door', $data1);
            $doorid = pdo_insertid();
        }
        else {
            $doorid = $doors_ids[$v['uid']]['id'];
        }
        if ($devices) {
            // 删除之前的门禁
            pdo_delete('xcommunity_bind_door_device', array('doorid' => $doorid));
            foreach ($devices as $key => $val) {
                $data2 = array(
                    'doorid'   => $doorid,
                    'deviceid' => $val['id'],
                    'groupid'  => $id
                );
                pdo_insert('xcommunity_bind_door_device', $data2);
                // 下发呼叫手机
                if ($type == 1) {
                    $mobile = $users_ids[$v['uid']]['mobile'];
                    $address = $rooms_list[$binds_list[$v['id']]['addressid']]['room'];
                    $data = array(
                        "identity" => $val['device_code'],
                        "flag"     => 1,
                        "list"     => array(array('abbrnum' => $room['room'], 'tel' => array($mobile)))
                    );
                    $params = json_encode($data);
                    $result = http_post('http://122.114.58.8:8018/cp/ly/setAbbrnum.ext', $params);
                    $result = @json_decode($result['content'], true);
                    if ($result['success'] == 1) {
                        pdo_update('xcommunity_member', array('voicestatus' => 1), array('id' => $v['id']));
                    }
                }
            }
        }

    }
    foreach ($rooms as $row) {
        $lastid = $row['id'];
    }
    $url = $this->createWebUrl('guard', array('op' => 'perLower', 'star' => $lastid, 'areaid' => $areaid, 'buildid' => $buildid, 'unitid' => $unitid, 'type' => $type, 'regionid' => $regionid, 'id' => $id));
    message('正在下发下一组', $url, 'success');
}
/**
 * 人脸门禁人员授权
 */
if ($op == 'face') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    if ($_GPC['keyword']) {
        $condition['realname like'] = "%{$_GPC['keyword']}%";
    }
    $users = pdo_getslice('xcommunity_face_users', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $list = array();
    foreach ($users as $k => $v) {
        $imgs = explode(',', $v['images']);
        $images = array();
        foreach ($imgs as $key => $value) {
            $images[] = explode('|', $value)[1];
        }
        $list[] = array(
            'id'         => $v['id'],
            'uid'        => $v['uid'],
            'realname'   => $v['realname'],
            'guid'       => $v['guid'],
            'mobile'     => $v['mobile'],
            'idcard'     => $v['idcard'],
            'status'     => $v['status'],
            'createtime' => $v['createtime'],
            'images'     => $images
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/face');
}
/**
 * 人脸门禁识别记录
 */
if ($op == 'faceLogs') {
    $type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    if ($type == 1) {
        $condition['personguid <>'] = "STRANGERBABY";
    }
    if ($type == 2) {
        $condition['personguid'] = "STRANGERBABY";
    }
    if ($_GPC['keyword']) {
        $condition['devicekey like'] = "%{$_GPC['keyword']}%";
    }
    $logs = pdo_getslice('xcommunity_face_logs', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
    $devices = pdo_getall('xcommunity_building_device', array('category' => 9, 'uniacid' => $_W['uniacid']), array('title', 'device_code', 'id'), 'device_code');
    $list = array();
    foreach ($logs as $k => $v) {
        $subData = json_decode($v['subdata'], true);
        $list[] = array(
            'id'         => $v['id'],
            'personguid' => $v['personguid'],
            'devicekey'  => $v['devicekey'],
            'photourl'   => $v['photourl'],
            'recmode'    => $v['recmode'],
            'createtime' => $v['createtime'],
            'realname'   => $subData['name'],
            'title'      => $devices[$v['devicekey']]['title']
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/face_logs');
}
/**
 * 人员的删除
 */
if ($op == 'faceDel') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_face_users', array('id' => $id), array());
        $deviceid = explode(',', $item['deviceids']);
        $device = pdo_get('xcommunity_building_device', array('id' => $deviceid), array('appid', 'appkey', 'appsecret', 'device_code'));
        $appid = $device['appid'];
        $appkey = $device['appkey'];
        $appsecret = $device['appsecret'];
        require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
        $res = deletePerson($appid, $appkey, $appsecret, $item['guid']);
        $r = pdo_delete('xcommunity_face_users', array('id' => $id));
        if ($r) {
            util::permlog('人脸授权-删除', '姓名:' . $item['realname']);
            itoast('操作成功', referer(), 'success', true);
        }
    }
}
/**
 * 人员的添加
 */
if ($op == 'faceAdd') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_face_users', array('id' => $id), array());
        $pics = explode(',', $item['images']);
        $piclist = array();
        $personGuids = array();
        foreach ($pics as $k => $v) {
            $piclist[] = explode('|', $v)[1];
            $personGuids[] = explode('|', $v)[0];
        }
        $deviceid = explode(',', $item['deviceids']);
    }
    if ($_W['isajax']) {
        require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
        $mobile = trim($_GPC['mobile']);
        $deviceids = $_GPC['deviceids'];
        if (empty($deviceids)) {
            echo json_encode(array('content' => '请勾选设备'));
            exit();
        }
        $uid = pdo_getcolumn('mc_members', array('uniacid' => $_W['uniacid'], 'mobile' => $mobile), 'uid');
        $devices = pdo_getall('xcommunity_building_device', array('id' => $deviceids), array('appid', 'appkey', 'appsecret', 'device_code'));
        $appid = $devices[0]['appid'];
        $appkey = $devices[0]['appkey'];
        $appsecret = $devices[0]['appsecret'];
        $images = $_GPC['images'];
        $data = array(
            'uniacid'    => $_W['uniacid'],
            'uid'        => $uid ? $uid : 0,
            'realname'   => trim($_GPC['realname']),
            'mobile'     => trim($_GPC['mobile']),
            'idcard'     => trim($_GPC['idcard']),
            'deviceids'  => implode(',', $deviceids),
            'createtime' => TIMESTAMP
        );
        if ($id) {
            pdo_update('xcommunity_face_users', $data, array('id' => $id));
            $guid = $item['guid'];
            // 更新人员
            if ($guid) {
                $resupdate = updatePerson($appid, $appkey, $appsecret, $guid, $data['realname'], $data['idcard'], $data['mobile']);
            }
            // 删除人脸照片
            if ($personGuids) {
                foreach ($personGuids as $k => $v) {
                    deletePersonImage($appid, $appkey, $appsecret, $v, $guid);
                }
            }
            // 删除授权的设备
            $devicess = pdo_getall('xcommunity_building_device', array('id' => $deviceid), array('device_code'));
            $deviceKeys = '';
            foreach ($devicess as $k => $v) {
                $deviceKeys .= $v['device_code'] . ',';
            }
            if ($deviceKeys) {
                plDeletePersonDevice($appid, $appkey, $appsecret, $guid, xtrim($deviceKeys));
            }
        }
        else {
            $result = addPerson($appid, $appkey, $appsecret, $_GPC['realname']);
            if ($result['code'] = "GS_SUS200") {
                $guid = $result['data']['guid'];// 人员guid
                $data['guid'] = $guid;
                pdo_insert('xcommunity_face_users', $data);
                $id = pdo_insertid();
            }
        }
        // 上传照片
        $img = '';
        foreach ($images as $k => $v) {
//            $image = addPersonImage($appid, $appkey, $appsecret, $guid, base64_encode(tomedia($v)));
            $image = addPersonImageUrl($appid, $appkey, $appsecret, $guid, tomedia($v));
            if ($image['code'] == "GS_SUS600") {
                $img .= $image['data']['guid'] . "|" . tomedia($v) . ',';
            }
        }
        // 人脸的上传记录
        $dat = array(
            'uniacid'    => $_W['uniacid'],
            'uid'        => $uid,
            'realname'   => $_GPC['realname'],
            'createtime' => TIMESTAMP,
            'images'     => implode(',', $images),
            'deviceids'  => $data['deviceids']
        );
        pdo_insert('xcommunity_face_uploadlogs', $dat);
        $status = 0;
        // 授权设备
        foreach ($devices as $k => $v) {
            $res = addPersonDevice($appid, $appkey, $appsecret, $guid, $v['device_code']);
            if ($res['code'] == "GS_SUS204") {
                // 授权成功
            }
        }
        pdo_update('xcommunity_face_users', array('images' => xtrim($img)), array('id' => $id));
        echo json_encode(array('status' => 1));
        exit();
    }
    $categories = pdo_getall('xcommunity_building_device', array('uniacid' => $_W['uniacid'], 'category' => 9), array('id', 'title'));
    $options = array();
    $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
    include $this->template('web/core/guard/face_add');
}
/**
 * 人脸门禁上传记录
 */
if ($op == 'faceUploads') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $uid = intval($_GPC['uid']);
    if ($uid) {
        $condition['uid'] = $uid;
    }
    $users = pdo_getslice('xcommunity_face_uploadlogs', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $devices = pdo_getall('xcommunity_building_device', array('uniacid' => $_W['uniacid']), array('id', 'title'), 'id');
    $list = array();
    foreach ($users as $k => $v) {
        $imgs = explode(',', $v['images']);
        $deviceTitle = '';
        foreach (explode(',', $v['deviceids']) as $ke => $va) {
            if ($va != 'on') {
                $deviceTitle .= $devices[$va]['title'] . ',';
            }
        }
        $list[] = array(
            'id'          => $v['id'],
            'realname'    => $v['realname'],
            'uid'         => $v['uid'],
            'createtime'  => $v['createtime'],
            'images'      => $imgs,
            'deviceTitle' => xtrim($deviceTitle)
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/face_uploads');
}
/**
 * 人脸门禁的授权
 */
if ($op == 'faceDevice') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_face_users', array('id' => $id), array('id', 'deviceids', 'guid', 'realname'));
        if (empty($item)) {
            itoast('数据不存在或已经删除', referer(), 'error');
        }
        $deviceids = explode(',', $item['deviceids']);
    }
    // 对人员进行追加设备授权
    if (checksubmit('submit')) {
        $ids = $_GPC['ids'];
        if ($ids) {
            require_once IA_ROOT . '/addons/xfeng_community/plugin/wotu/function.php';
            $devices = pdo_getall('xcommunity_building_device', array('category' => 9, 'uniacid' => $_W['uniacid']), array('appid', 'appkey', 'appsecret', 'device_code', 'id'), 'id');
            $dids = $item['deviceids'];
            foreach ($ids as $k => $v) {
                if (!in_array($v, $deviceids)) {
                    $res = addPersonDevice($devices[$v]['appid'], $devices[$v]['appkey'], $devices[$v]['appsecret'], $item['guid'], $devices[$v]['device_code']);
                    if ($res['code'] == "GS_SUS204") {
                        // 授权成功
                        $dids .= ',' . $v;
                    }
                }
            }
            if ($dids != $item['deviceids']) {
                pdo_update('xcommunity_face_users', array('deviceids' => $dids), array('id' => $id));
            }
            itoast('授权成功', referer(), 'success');
        }
        else {
            itoast('请勾选设备', referer(), 'error');
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['category'] = 9;
    if ($_GPC['keyword']) {
        $condition['device_code like'] = "%{$_GPC['keyword']}%";
    }
    $list = pdo_getslice('xcommunity_building_device', $condition, array($pindex, $psize), $total, '', '', array('displayorder asc', 'id desc'));
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/guard/face_device');
}