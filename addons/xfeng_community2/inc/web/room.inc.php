<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/9/28 下午2:57
 */
global $_W, $_GPC;
$ops = array('add', 'del', 'list', 'import', 'qr', 'region', 'show', 'smssend', 'qrpl', 'download', 'qrlist', 'qrdel');
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
 * 房屋的列表
 */
if ($op == 'list') {
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                $item = pdo_get('xcommunity_member_room', array('id' => $id), array());
                if ($item) {
                    pdo_delete('xcommunity_member_log', array('addressid' => $item['id']));
                    if (pdo_delete("xcommunity_member_room", array('id' => $item['id']))) {
                        util::permlog('用户数据-删除', '删除用房号:' . $item['room']);
                    }
                }
            }
            itoast('删除成功', referer(), 'success');
        }
    }

//    $condition = 't1.uniacid=:uniacid';
//    $params[':uniacid'] = $_W['uniacid'];
//    if (!empty($_GPC['keyword'])) {
//        $condition .= " AND t1.address LIKE :keyword";
//        $params[':keyword'] = "%{$_GPC['keyword']}%";
//    }
//    if (intval($_GPC['regionid'])) {
//        $condition .= " and t1.regionid=:regionid";
//        $params[':regionid'] = intval($_GPC['regionid']);
//    }
//    if ($user[type] == 3) {
//        //普通管理员
//        $condition .= " and t1.regionid in({$user['regionid']})";
//    }
//    $build = trim($_GPC['build']);
//    $area = trim($_GPC['area']);
//    if ($build) {
//        $condition .= " and t1.build=:build";
//        $params[':build'] = $build;
//    }
//    if ($area) {
//        $condition .= " and t1.area=:area";
//        $params[':area'] = $area;
//    }
//    $enble = intval($_GPC['enable']);
//    if ($enble) {
//        $enble = $enble == 2 ? 0 : 1;
//        $condition .= " and t1.enable=:enable";
//        $params[':enable'] = $enble;
//    }
//    if ($_GPC['export'] == 1 || checksubmit('plcode')) {
//        $sql1 = "select t1.id,t1.address,t2.realname,t2.mobile,t3.title,t1.area,t1.enable,t1.license,t1.area,t1.build,t1.unit,t1.room ,t1.code,t1.square,t1.floor_num from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id=t2.addressid left join" . tablename('xcommunity_region') . "t3 on t3.id=t1.regionid where $condition";
//        $list1 = pdo_fetchall($sql1, $params);
//        if (checksubmit('plcode')) {
//            foreach ($list1 as $key => $val) {
//                $code = rand(10000000, 99999999);
//
//                pdo_update('xcommunity_member_room', array('code' => $code), array('id' => $val['id']));
//
//            }
//            util::permlog('', '批量重置房号注册码');
//            itoast('批量修改成功', referer(), 'success', ture);
//        }
//        if ($_GPC['export'] == 1) {
//            foreach ($list1 as $k => $v) {
//                if ($v['enable'] == 1) {
//                    $list1[$k]['enable'] = '住户已绑定';
//                } else {
//                    $list1[$k]['enable'] = '住户未绑定';
//                }
//            }
//            model_execl::export($list1, array(
//                "title"   => "房号数据-" . date('Y-m-d-H-i', time()),
//                "columns" => array(
//                    array(
//                        'title' => '房号ID(勿改)',
//                        'field' => 'id',
//                        'width' => 10
//                    ),
//                    array(
//                        'title' => '姓名',
//                        'field' => 'realname',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '手机号码',
//                        'field' => 'mobile',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '区域',
//                        'field' => 'area',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '楼栋',
//                        'field' => 'build',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '单元',
//                        'field' => 'unit',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '室',
//                        'field' => 'room',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '地址',
//                        'field' => 'address',
//                        'width' => 20
//                    ),
//                    array(
//                        'title' => '车牌',
//                        'field' => 'license',
//                        'width' => 20
//                    ),
//                    array(
//                        'title' => '面积',
//                        'field' => 'square',
//                        'width' => 18
//                    ),
//                    array(
//                        'title' => '小区名称',
//                        'field' => 'title',
//                        'width' => 18
//                    ),
//                    array(
//                        'title' => '房屋状态',
//                        'field' => 'enable',
//                        'width' => 12
//                    ),
//                    array(
//                        'title' => '注册码',
//                        'field' => 'code',
//                        'width' => 18
//                    ),
//                    array(
//                        'title' => '楼层',
//                        'field' => 'floor_num',
//                        'width' => 12
//                    ),
//                )
//            ));
//        }
//    }
//
//    $pindex = max(1, intval($_GPC['page']));
//    $psize = 30;
//    $sql = "select t2.title,t1.area,t1.build,t1.unit,t1.room,t1.square,t1.code,t1.id as addressid,t1.address,t1.enable,t1.floor_num from " . tablename("xcommunity_member_room") . " t1 left join" . tablename('xcommunity_region') . " t2 on t1.regionid = t2.id where $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
//    $list = pdo_fetchall($sql, $params);
//    $total = pdo_fetchcolumn("select count(*) from " . tablename('xcommunity_member_room') . " t1 left join" . tablename('xcommunity_region') . " t2 on t1.regionid = t2.id where $condition ", $params);
//    $pager = pagination($total, $pindex, $psize);
    $pindex = max(1, intval($_GPC['page']));
    $psize = $_GPC['export'] == 1 ? 1000000 : 20;

    $condition = [];
    $condition['uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition['address like'] = "%{$_GPC['keyword']}%";
    }
    if (intval($_GPC['regionid'])) {
        $condition['regionid'] = intval($_GPC['regionid']);
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition['regionid'] = explode(',', $user['regionid']);
    }
    $enble = intval($_GPC['enable']);
    if ($enble) {
        $enble = $enble == 2 ? 0 : 1;
        $condition['enable'] = $enble;
    }
    $build = trim($_GPC['build']);
    $area = trim($_GPC['area']);
    if ($build) {
        $condition['build'] = $build;
    }
    if ($area) {
        $condition['area'] = $area;
    }
    $list = pdo_getslice('xcommunity_member_room', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
    $list_regionids = array_unique(_array_column($list, 'regionid'));
    $_regions = pdo_getall('xcommunity_region', array('id' => $list_regionids), array('id', 'title'), 'id');
    if (checksubmit('plcode')) {
        foreach ($list as $key => $val) {
            $code = rand(10000000, 99999999);
            pdo_update('xcommunity_member_room', array('code' => $code), array('id' => $val['id']));
        }
        util::permlog('', '批量重置房号注册码');
        itoast('批量修改成功', referer(), 'success', ture);
    }
    if ($_GPC['export'] == 1) {
        $list_roomids = array_unique(_array_column($list, 'id'));
        $logs = pdo_getall('xcommunity_member_log', array('addressid' => $list_roomids),array('addressid','realname','mobile'),'addressid');
        $list_export = array();
        foreach ($list as $val) {
            $list_export[] = array(
                'enable'    => $val['enable'] == 1 ? '住户已绑定' : '住户未绑定',
                'id'        => $val['id'],
                'realname'  => $logs[$val['id']]['realname'],
                'mobile'    => $logs[$val['id']]['mobile'],
                'area'      => $val['area'],
                'build'     => $val['build'],
                'unit'      => $val['unit'],
                'room'      => $val['room'],
                'address'   => $val['address'],
                'square'    => $val['square'],
                'license'   => $val['license'],
                'title'     => $_regions[$val['regionid']]['title'],
                'code'      => $val['code'],
                'floor_num' => $val['floor_num']
            );
        }
        model_execl::export($list_export, array(
            "title"   => "房号数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '房号ID(勿改)',
                    'field' => 'id',
                    'width' => 10
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号码',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '区域',
                    'field' => 'area',
                    'width' => 12
                ),
                array(
                    'title' => '楼栋',
                    'field' => 'build',
                    'width' => 12
                ),
                array(
                    'title' => '单元',
                    'field' => 'unit',
                    'width' => 12
                ),
                array(
                    'title' => '室',
                    'field' => 'room',
                    'width' => 12
                ),
                array(
                    'title' => '地址',
                    'field' => 'address',
                    'width' => 20
                ),
                array(
                    'title' => '车牌',
                    'field' => 'license',
                    'width' => 20
                ),
                array(
                    'title' => '面积',
                    'field' => 'square',
                    'width' => 18
                ),
                array(
                    'title' => '小区名称',
                    'field' => 'title',
                    'width' => 18
                ),
                array(
                    'title' => '房屋状态',
                    'field' => 'enable',
                    'width' => 12
                ),
                array(
                    'title' => '注册码',
                    'field' => 'code',
                    'width' => 18
                ),
                array(
                    'title' => '楼层',
                    'field' => 'floor_num',
                    'width' => 12
                ),
            )
        ));
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/room/list');
}
/**
 * 房屋的添加
 */
if ($op == 'add') {
    $p91 = set('p91') ? set('p91') : 0;
    $id = intval($_GPC['id']);
    $addressid = intval($_GPC['addressid']);
    if ($addressid) {
        $sql = "select * from" . tablename('xcommunity_member_room') . "where id=:addressid ";
        $item = pdo_fetch($sql, array(':addressid' => $addressid));
        $arr = util::xqset($item['regionid']);
        $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
        if ($item['areaid']) {
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid'], 'areaid' => $item['areaid']), array('id', 'buildtitle'));
        } else {
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array('id', 'buildtitle'));
        }
        $units = pdo_getall('xcommunity_unit', array('regionid' => $item['regionid'], 'buildid' => $item['buildid']), array('id', 'unit'));
    }
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        $addr = '';
        $condition = '';
        $xqzd = util::xqzd($regionid);
        $area = trim($_GPC['area']);
        $build = trim($_GPC['build']);
        $unit = trim($_GPC['unit']);
        $areaid = trim($_GPC['areaid']);
        $buildid = trim($_GPC['buildid']);
        $unitid = trim($_GPC['unitid']);
        if ($area) {
            $addr .= $_GPC['area'] . $xqzd['a'];
            $condition .= " and area=:area";
            $params[':area'] = $_GPC['area'];
        }
        if ($build) {
            $addr .= $_GPC['build'] . $xqzd['b'];
            $condition .= " and build=:build";
            $params[':build'] = $_GPC['build'];
        }
        if ($unit) {
            $addr .= $_GPC['unit'] . $xqzd['c'];
            $condition .= " and unit=:unit";
            $params[':unit'] = $_GPC['unit'];
        }
        if ($_GPC['room']) {
            $addr .= $_GPC['room'] . $xqzd['d'];
            $condition .= " and room=:room";
            $params[':room'] = $_GPC['room'];
        }
        if ($_GPC['address']) {
            $condition .= " and address=:address";
            $params[':address'] = $_GPC['address'];
        }
        if (empty($addressid)) {
            $sql = "select id from " . tablename('xcommunity_member_room') . "where regionid=:regionid and uniacid=:uniacid $condition";
            $params[':regionid'] = $regionid;
            $params[':uniacid'] = intval($_W['uniacid']);
            $room = pdo_fetch($sql, $params);
            if ($room) {
                echo json_encode(array('content' => '房号已存在，无需添加'));
                exit();
            }
        }
        if (empty($areaid) && empty($buildid) && empty($unitid)) {
            if ($area) {
                $_area = pdo_get('xcommunity_area', array('regionid' => $regionid, 'title' => $area), array('id'));
                $areaid = $_area['id'];
                if (empty($_area)) {
                    $d = array(
                        'uniacid'  => $_W['uniacid'],
                        'regionid' => $regionid,
                        'title'    => $area,
                        'uid'      => $_W['uid']
                    );
                    pdo_insert('xcommunity_area', $d);
                    $areaid = pdo_insertid();
                }
            } else {
                $areaid = '';
            }
            if ($build) {
                $condition = "regionid=:regionid and buildtitle=:buildtitle";
                $par[':regionid'] = $regionid;
                $par[':buildtitle'] = $build;
                if ($area) {
                    $condition .= " and areaid=:areaid";
                    $par[':areaid'] = $areaid;
                } else {
                    $condition .= " and areaid=:areaid";
                    $par[':areaid'] = '';
                }
                $sql = "select * from" . tablename('xcommunity_build') . "where $condition";
                $_build = pdo_fetch($sql, $par);

                if (empty($_build)) {
                    $dat = array(
                        'uniacid'    => $_W['uniacid'],
                        'regionid'   => $regionid,
                        'buildtitle' => $build
                    );
                    if ($area) {
                        $dat['areaid'] = $areaid;
                    } else {
                        $dat['areaid'] = '';
                    }
                    pdo_insert('xcommunity_build', $dat);
                    $buildid = pdo_insertid();
                } else {
                    $buildid = $_build['id'];
                }
            }
            if ($unit) {
                $_unit = pdo_get('xcommunity_unit', array('buildid' => $buildid, 'unit' => $unit, 'regionid' => $regionid), array('id'));
                if (empty($_unit)) {
                    $data = array(
                        'uniacid'  => $_W['uniacid'],
                        'buildid'  => $buildid,
                        'unit'     => $unit,
                        'uid'      => $_W['uid'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_unit', $data);
                    $unitid = pdo_insertid();
                } else {
                    $unitid = $_unit['id'];
                }
            }
        }
        if (empty($area) && empty($build) && empty($unit)) {
            $addr = '';
            if ($areaid) {
                $area = pdo_getcolumn('xcommunity_area', array('uniacid' => $_W['uniacid'], 'id' => $areaid), 'title');
                $addr .= $area . $xqzd['a'];
            }
            if ($buildid) {
                $build = pdo_getcolumn('xcommunity_build', array('uniacid' => $_W['uniacid'], 'id' => $buildid), 'buildtitle');
                $addr .= $build . $xqzd['b'];
            }
            if ($unitid) {
                $unit = pdo_getcolumn('xcommunity_unit', array('uniacid' => $_W['uniacid'], 'id' => $unitid), 'unit');
                $addr .= $unit . $xqzd['c'];
                $addr .= $_GPC['room'] . $xqzd['d'];
            }
        }
        $data = array(
            'area'       => $area ? $area : 0,
            'build'      => $build,
            'unit'       => $unit,
            'room'       => $_GPC['room'],
            'square'     => $_GPC['square'],
            'address'    => $_GPC['address'] ? trim($_GPC['address']) : $addr,
            'createtime' => TIMESTAMP,
            'regionid'   => intval($_GPC['regionid']),
            'uniacid'    => $_W['uniacid'],
            'code'       => rand(10000000, 99999999),
            'areaid'     => $areaid,
            'buildid'    => $buildid,
            'unitid'     => $unitid,
            'floor_num'  => $_GPC['floor_num']
        );

        if (empty($addressid)) {
            $d = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'realname' => trim($_GPC['realname']),
                'mobile'   => trim($_GPC['mobile']),
                'status'   => 1
            );
            if ($room['id']) {
                $d['addressid'] = $room['id'];
                pdo_insert('xcommunity_member_log', $d);
            } else {
                if (pdo_insert('xcommunity_member_room', $data)) {
                    $d['addressid'] = pdo_insertid();
                    pdo_insert('xcommunity_member_log', $d);
                    util::permlog('房号管理-添加', '添加房号信息ID:' . $d['addressid']);
                }
            }

        } else {
            $data['yybstatus'] = 0;
            $data['action'] = 1;
            pdo_update('xcommunity_member_room', $data, array('id' => $addressid));
            util::permlog('房号管理-修改', '修改房号信息ID:' . $addressid);
        }
        $unit_num = pdo_fetchcolumn('select count(id) from' . tablename('xcommunity_unit') . " where uniacid=:uniacid and buildid=:buildid ", array(':uniacid' => $_W['uniacid'], ':buildid' => $buildid));
        $room_num = pdo_fetchcolumn('select count(id) from' . tablename('xcommunity_member_room') . " where areaid=:areaid and buildid=:buildid", array(':areaid' => $areaid, ':buildid' => $buildid));
        pdo_update('xcommunity_build', array('unit_num' => $unit_num, 'room_num' => $room_num), array('id' => $buildid));
        echo json_encode(array('status' => 1));
        exit();

    }
    include $this->template('web/core/room/add');
}
/**
 * 房屋的删除
 */
if ($op == 'del') {
    //删除
    if ($id) {
        $item = pdo_get('xcommunity_member_room', array('id' => $id), array());
        if ($item) {
            pdo_delete('xcommunity_member_log', array('addressid' => $item['id']));
            pdo_delete('xcommunity_member_bind', array('addressid' => $item['id']));
            pdo_delete('xcommunity_member_family', array('addressid' => $item['id']));
            if (pdo_delete("xcommunity_member_room", array('id' => $item['id']))) {
                util::permlog('用户数据-删除', '删除用房号:' . $item['room']);
                itoast('删除成功', referer(), 'success');
            }
        }

    }
}
/**
 * 房屋的导入
 */
if ($op == 'import') {
    //房号导入
    $condition = '';
    if ($user[type] == 3) {
        $condition = "and id in ({$user['regionid']})";
    }
    $regions = model_region::region_fetall($condition);
    if ($_W['isajax']) {
        $rows = model_execl::read('room');
        if (trim($rows[0][1]) != '姓名' || trim($rows[0][2]) != '手机号码') {
            echo json_encode(array('content' => '文件内容不符，请重新上传'));
            exit();
        }
        $regionid = intval($_GPC['regionid']);
        foreach ($rows as $rownum => $col) {
            $arr = util::xqzd(intval($_GPC['regionid']));
            if ($rownum > 0) {
                if ($col[6] || $col[7]) {
                    //判断区域有没添加
                    if (trim($col[3])) {
                        $area = pdo_get('xcommunity_area', array('regionid' => $regionid, 'title' => trim($col[3])), array('id'));
                        $areaid = $area['id'];
                        if (empty($area)) {
                            $d = array(
                                'uniacid'  => $_W['uniacid'],
                                'regionid' => $regionid,
                                'title'    => trim($col[3]),
                                'uid'      => $_W['uid']
                            );
                            pdo_insert('xcommunity_area', $d);
                            $areaid = pdo_insertid();
                        }
                    }
                    if (trim($col[4])) {
                        $condition = "regionid=:regionid and buildtitle=:buildtitle";
                        $par[':regionid'] = $regionid;
                        $par[':buildtitle'] = trim($col[4]);
                        if (trim($col[3])) {
                            $condition .= " and areaid=:areaid";
                            $par[':areaid'] = $areaid;
                        } else {
                            $condition .= " and areaid=:areaid";
                            $par[':areaid'] = '';
                        }
                        $sql = "select * from" . tablename('xcommunity_build') . "where $condition";
                        $build = pdo_fetch($sql, $par);
                        $buildid = $build['id'];
                        if (empty($build)) {
                            $dat = array(
                                'uniacid'    => $_W['uniacid'],
                                'regionid'   => $regionid,
                                'buildtitle' => trim($col[4]),
                                'areaid'     => $areaid
                            );
                            pdo_insert('xcommunity_build', $dat);
                            $buildid = pdo_insertid();
                        }
                    }
                    if ($col[5]) {
                        $unit = pdo_get('xcommunity_unit', array('buildid' => $buildid, 'unit' => trim($col[5]), 'regionid' => $regionid), array('id'));
                        $unitid = $unit['id'];
                        if (empty($unit)) {
                            $data = array(
                                'uniacid'  => $_W['uniacid'],
                                'buildid'  => $buildid,
                                'unit'     => trim($col[5]),
                                'uid'      => $_W['uid'],
                                'regionid' => $regionid
                            );
                            pdo_insert('xcommunity_unit', $data);
                            $unitid = pdo_insertid();
                        }
                    }
                    $addr = '';
                    $arr = util::xqzd($regionid);
                    $condition = '';
                    if ($col[3]) {
                        $addr .= $col[3] . $arr['a'];
                        $condition .= " and area=:area";
                        $params[':area'] = $col[3];
                    }
                    if ($col[4]) {
                        $addr .= $col[4] . $arr['b'];
                        $condition .= " and build=:build";
                        $params[':build'] = $col[4];
                    }
                    if ($col[5]) {
                        $addr .= $col[5] . $arr['c'];
                        $condition .= " and unit=:unit";
                        $params[':unit'] = $col[5];
                    }
                    if ($col[6]) {
                        $addr .= $col[6] . $arr['d'];
                        $condition .= " and room=:room";
                        $params[':room'] = $col[6];
                    }
                    if ($col[7]) {
                        $condition .= " and address=:address";
                        $params[':address'] = $col[7];
                    }
                    $data = array(
                        'area'       => trim($col[3]) ? trim($col[3]) : 0,
                        'build'      => trim($col[4]),
                        'unit'       => trim($col[5]),
                        'room'       => trim($col[6]),
                        'square'     => sprintf("%.2f", $col[9]),
                        'license'    => $col[8] ? $col[8] : 0,
                        'address'    => $col[7] ? trim($col[7]) : $addr,
                        'createtime' => TIMESTAMP,
                        'regionid'   => $regionid,
                        'uniacid'    => $_W['uniacid'],
                        'code'       => $col[12] ? $col[12] : rand(10000000, 99999999),
                        'areaid'     => $areaid,
                        'buildid'    => $buildid,
                        'unitid'     => $unitid,
                        'floor_num'  => $col[13] ? $col[13] : 0
                    );

                    $roomid = $col[0];
                    if ($roomid) {
                        //修改
                        pdo_update('xcommunity_member_room', $data, array('id' => $roomid));
                    } else {
                        $sql = "select id from " . tablename('xcommunity_member_room') . "where regionid=:regionid and uniacid=:uniacid $condition";
                        $params[':regionid'] = $regionid;
                        $params[':uniacid'] = intval($_W['uniacid']);
                        $room = pdo_fetch($sql, $params);
                        if (empty($room)) {
                            pdo_insert('xcommunity_member_room', $data);
                            $addressid = pdo_insertid();
                            $unit_num = pdo_fetchcolumn('select count(id) from' . tablename('xcommunity_unit') . " where uniacid=:uniacid and buildid=:buildid and regionid=:regionid", array(':uniacid' => $_W['uniacid'], ':buildid' => $buildid, 'regionid' => $regionid));
                            $room_num = pdo_fetchcolumn('select count(id) from' . tablename('xcommunity_member_room') . " where areaid=:areaid and buildid=:buildid", array(':areaid' => $areaid, ':buildid' => $buildid));
                            pdo_update('xcommunity_build', array('unit_num' => $unit_num, 'room_num' => $room_num), array('id' => $buildid));
                        } else {
                            $addressid = $room['id'];
                        }
                        $d = array(
                            'uniacid'   => $_W['uniacid'],
                            'regionid'  => $regionid,
                            'realname'  => $col[1] ? $col[1] : 'xxxxxxxxx',
                            'mobile'    => $col[2] ? $col[2] : 'xxxxxxxxx',
                            'addressid' => $addressid,
                            'status'    => 1
                        );
                        pdo_insert('xcommunity_member_log', $d);
                        util::permlog('房号管理-添加', '添加房号信息ID:' . $addressid);
                        if ($col[8]) {
                            $t = array(
                                'uniacid'  => $_W['uniacid'],
                                'regionid' => $regionid,
                                'realname' => $col[1],
                                'mobile'   => $col[2],
                                'car_num'  => $col[8]
                            );
                            $car = pdo_get('xcommunity_xqcars', array('car_num' => $col[8]));
                            if (empty($car)) {
                                pdo_insert('xcommunity_xqcars', $t);
                            }
                        }
                    }

                }
            }

        }
        util::permlog('', '导入房号信息');
        echo json_encode(array('result' => 1, 'content' => '导入完成！'));
        exit();
    }

    include $this->template('web/core/room/import');
}
/**
 * 房屋的预留信息二维码
 */
if ($op == 'qr') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.*,t2.address,t3.title from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('xcommunity_region') . "t3 on t3.id=t2.regionid where t1.id=:id ", array(':id' => $id));
    $time = TIMESTAMP;
    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&do=bind&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
    $temp = $item['title'] . "-" . strFilter($item['address']) . "-" . str_replace('/', '', $item['realname']) . ".png";
    $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/room/" . $_W['uniacid'] . "/" . $item['title'] . "/" . strFilter($item['address']) . "/";
    $qr = createQr($url, $temp, $tmpdir);
    echo $qr;
    exit();
}
/**
 * 获取小区注册字段
 */
if ($op == 'region') {
    $regionid = intval($_GPC['regionid']);
    $arr = util::xqset($regionid);
    $arr = json_encode($arr);
    echo $arr;
}
/**
 * 房屋的预留信息
 */
if ($op == 'show') {
    $p = in_array(trim($_GPC['p']), array('list', 'add', 'del')) ? trim($_GPC['p']) : 'list';
    /**
     * 房屋的预留信息列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 30;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $addressid = intval($_GPC['addressid']);
        if ($addressid) {
            $condition .= " and t1.addressid=:addressid";
            $params[':addressid'] = $addressid;
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t2.address LIKE :keyword or t1.realname LIKE :keyword or t1.mobile LIKE :keyword)";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t2.regionid=:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user[type] == 3) {
            //普通管理员
            $condition .= " and t2.regionid in({$user['regionid']})";
        }
        if ($_GPC['export'] == 1) {
            $ttsql = "select t1.*,t2.address,t3.title from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('xcommunity_region') . "t3 on t3.id=t2.regionid where $condition order by t1.id desc ";
            $ttlist = pdo_fetchall($ttsql, $params);
            model_execl::export($ttlist, array(
                "title"   => "房号数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
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
                        'width' => 18
                    ),
                )
            ));

        }
        $sql = "select t1.*,t2.address,t3.title from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('xcommunity_region') . "t3 on t3.id=t2.regionid where $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/room/show/list');
    }
    /**
     * 房屋预留信息的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_member_log', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_member_log', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
    /**
     * 房屋预留信息的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        $addressid = intval($_GPC['addressid']);
        if (empty($addressid)) {
            if (empty($id)) {
                itoast('缺少参数');
            }
        }
        if ($id) {
            $item = pdo_get('xcommunity_member_log', array('id' => $id), array());
        }
        if ($addressid) {
            $regionid = pdo_getcolumn('xcommunity_member_room', array('id' => $addressid), 'regionid');
        }
        if ($_W['isajax']) {
            $mobile = trim($_GPC['mobile']);
            $realname = trim($_GPC['realname']);
            $condition['mobile'] = $mobile;
            $condition['addressid'] = $addressid;
            $log = pdo_get('xcommunity_member_log', $condition, array('id'));
            if (empty($log) || $mobile == $item['mobile']) {
                $data = array(
                    'realname' => $realname,
                    'mobile'   => $mobile,
                    'uniacid'  => $_W['uniacid'],
                    'status'   => intval($_GPC['status']),
                );
                if ($id) {

                    pdo_update('xcommunity_member_log', $data, array('id' => $id));
                } else {
                    $data['regionid'] = $regionid;
                    $data['addressid'] = $addressid;
                    pdo_insert('xcommunity_member_log', $data);
                }
                echo json_encode(array('status' => 1));
                exit();
            } else {
                echo json_encode(array('content' => '手机号已存在，请更换手机号'));
                exit();
            }

        }
        include $this->template('web/core/room/show/add');
    }
}
/**
 * 房屋的注册码发送
 */
if ($op == 'smssend') {

    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.address LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
//        if (intval($_GPC['regionid'])) {
//            $condition .= " and t1.regionid=:regionid";
//            $params[':regionid'] = intval($_GPC['regionid']);
//        }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }

    $enble = intval($_GPC['enable']);
    if ($enble) {
        $enble = $enble == 2 ? 0 : 1;
        $condition .= " and t1.enable=:enable";
        $params[':enable'] = $enble;
    }
    if ($_GPC['ids']) {
        $ids = implode(',', $_GPC['ids']);
        $condition .= " and t1.id in({$ids})";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;
    $sql2 = "select t1.id,t1.code,t2.mobile,t2.id as uid,t1.regionid from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id=t2.addressid left join" . tablename('xcommunity_region') . "t3 on t3.id=t1.regionid where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list2 = pdo_fetchall($sql2, $params);
    $tsql = "select t1.id,t1.code,t2.mobile,t2.id as uid,t1.regionid from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id=t2.addressid left join" . tablename('xcommunity_region') . "t3 on t3.id=t1.regionid where $condition ";
    $ttotal = pdo_fetchall($tsql, $params);
    $total = count($ttotal);
    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
    if (set('s2') && set('s3')) {
        $type = set('s1');
        if ($type == 1) {
            $type == 'wwt';
        } elseif ($type == 2) {
            $type = 'juhe';
            $tpl_id = set('s12');
        } else {
            $type = 'aliyun_new';
            $tpl_id = set('s24');
        }
        foreach ($list2 as $k => $v) {
            $item = pdo_get('xcommunity_send_log', array('sendid' => $v['id']), array());
            if (empty($item)) {
                $record++;//已发送记录
                $account = $_W['account']['name'];
                if ($type == 'wwt') {
                    $smsg = "您好,您在本小区注册码" . $v['code'] . "，请尽快到我们的公众号" . $account . "上注册使用在线功能哦。";
                } elseif ($type == 'juhe') {
                    $code = $v['code'];
                    $smsg = urlencode("#code#=$code&#account#=$account");
                } else {
                    $smsg = json_encode(array('code' => $v['code'], 'account' => $account));
                }
                $content = sms::send($v['mobile'], $smsg, $type, '', 1, $tpl_id);
                $ok++;//成功发送
                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'sendid'     => $v['id'],
                    'uid'        => $v['uid'],
                    'type'       => 2,
                    'cid'        => 2,
                    'status'     => 1,
                    'createtime' => TIMESTAMP,
                    'regionid'   => $v['regionid']
                );
                pdo_insert('xcommunity_send_log', $d);
            }

        }
    } else {

        foreach ($list2 as $k => $val) {
            $item = pdo_get('xcommunity_send_log', array('sendid' => $val['id']), array());
            if (empty($item)) {
                $record++;//已发送记录
                $type = set('x21', $val['regionid']);
                if ($type == 1) {
                    $type = 'wwt';
                } elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('x37', $val['regionid']);
                } else {
                    $type = 'aliyun_new';
                    $tpl_id = set('x72', $val['regionid']);
                }
                $account = $_W['account']['name'];
                if ($type == 'wwt') {
                    $smsg = "您好,您在本小区注册码" . $val['code'] . "，请尽快到我们的公众号" . $account . "上注册使用在线功能哦。";
                } elseif ($type == 'juhe') {
                    $code = $val['code'];
                    $smsg = urlencode("#code#=$code&#account#=$account");
                } else {
                    $smsg = json_encode(array('code' => $val['code'], 'account' => $account));
                }
                $content = sms::send($val['mobile'], $smsg, $type, $val['regionid'], 2, $tpl_id);
                $ok++;//成功发送
                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'sendid'     => $val['id'],
                    'uid'        => $val['uid'],
                    'type'       => 2,
                    'cid'        => 2,
                    'status'     => 1,
                    'createtime' => TIMESTAMP,
                    'regionid'   => $val['regionid']
                );
                pdo_insert('xcommunity_send_log', $d);
            }

        }
    }
    if ($ok >= $total || empty($list2)) {
        echo json_encode(array('status' => 'end'));
        exit();
    } else {
        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }
    util::permlog('', '批量发送房号注册码');


}
/**
 * 房屋批量生成二维码
 */
if ($op == 'qrpl') {
    $rooms = pdo_fetchall("select t1.*,t2.address,t3.title from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('xcommunity_region') . "t3 on t3.id=t2.regionid where t1.uniacid=:uniacid order by t1.id desc", array(':uniacid' => $_W['uniacid']));
    foreach ($rooms as $k => $v) {
        $time = TIMESTAMP;
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&do=bind&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
        $temp = $v['title'] . "-" . strFilter($v['address']) . "-" . $v['realname'] . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/room/" . $_W['uniacid'] . "/" . $v['title'] . "/" . strFilter($v['address']) . "/";
        $qr = createQr($url, $temp, $tmpdir);
    }
    itoast('更新成功', $this->createWebUrl('room', array('op' => 'qrlist')), 'success');
}
/**
 * 房屋的二维码下载
 */
if ($op == 'download') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_region', array('id' => $id));
        $path = MODULE_ROOT . '/data/qrcode/room/' . $_W['uniacid'] . "/" . $item['title'] . "/";
        $zip = MODULE_ROOT . '/data/qrcode/room/' . $item['title'] . ".zip";
        download2($path, $zip);
    }
}
/**
 *房屋预留信息二维码列表
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
    include $this->template('web/core/room/qrlist');
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
    $tmpdir = MODULE_ROOT . '/data/qrcode/room/' . $item['uniacid'] . '/' . $item['title'];
    rmdirs($tmpdir);
    /**
     * 删除关联的压缩包
     */
    load()->func('file');
    $zip = MODULE_ROOT . '/data/qrcode/room/' . $item['title'] . ".zip";
    file_delete($zip);
    itoast('删除成功', referer(), 'success');
}