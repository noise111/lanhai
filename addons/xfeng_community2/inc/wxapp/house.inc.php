<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/27 下午5:25
 */
global $_GPC, $_W;
$ops = array('list', 'build', 'unit', 'room', 'rooms');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $regionid = intval($_GPC['regionid']) ?  intval($_GPC['regionid']):$_SESSION['community']['regionid'];
    //2种情况，一种有区域，选择区域楼栋单元房号，一种没有区域，选择楼栋单元房号
    $arr = util::xqset($regionid);
    $data = array();
    $area = (set('p55') && set('p36')) || set('x17', $regionid) ? 1 : 0;
    if ($area) {
        $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('id', 'title'));
        foreach ($areas as $k => $v) {
            $areas[$k]['key'] = $v['id'];
            $areas[$k]['value'] = $v['title'].$arr['a1'];
        }
        $data['areas'] = $areas;
        foreach ($areas as $k => $v) {
            $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid, 'areaid' => $v['id']), array('buildtitle', 'id'));
            sortArrByField($builds, 'buildtitle');
            foreach ($builds as $key => $val) {
                $builds[$key]['key'] = $val['id'];
                $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
            }
            $data['builds'][$v['id']] = $builds;

            foreach ($builds as $key => $val) {
                $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
                sortArrByField($units, 'unit');
                foreach ($units as $kk => $va) {
                    $units[$kk]['key'] = $va['id'];
                    $units[$kk]['value'] = $va['unit'].$arr['c1'];
                }
                $data['units'][$val['id']] = $units;

                foreach ($units as $i => $item) {
//                    $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $regionid, 'build' => $val['buildtitle'], 'area' => $v['title']), array('room', 'id'));
//                    $rooms = pdo_fetchall('select room,id from'.tablename('xcommunity_member_room')."where regionid=:regionid and build=:build and area=:area order by id asc",array(':regionid'=>$regionid,':build'=>$val['buildtitle'],':area' => $v['title']));
                    $rooms = pdo_fetchall('select room,id from' . tablename('xcommunity_member_room') . "where unitid=:unitid order by id asc", array(':unitid' => $item['id']));
                    sortArrByField($rooms, 'room');
                    foreach ($rooms as $j => $r) {
                        $rooms[$j]['key'] = $r['id'];
                        $rooms[$j]['value'] = $r['room'].$arr['d1'];
                    }
                    $data['rooms'][$item['id']] = $rooms;
                }
            }
        }
        util::send_result($data);
    }
    else {

        $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid), array('buildtitle', 'id'));
        sortArrByField($builds, 'buildtitle');
        foreach ($builds as $key => $val) {
            $builds[$key]['key'] = $val['id'];
            $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
        }
        $data['builds'] = $builds;
        foreach ($builds as $key => $val) {
            $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
            sortArrByField($units, 'unit');
            foreach ($units as $kk => $va) {
                $units[$kk]['key'] = $va['id'];
                $units[$kk]['value'] = $va['unit'].$arr['c1'];
            }
            $data['units'][$val['id']] = $units;

            foreach ($units as $i => $item) {
//                    $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $regionid, 'build' => $val['buildtitle']), array('room', 'id'));
                $rooms = pdo_fetchall('select room,id from' . tablename('xcommunity_member_room') . "where regionid=:regionid and build=:build and unit=:unit order by id asc", array(':regionid' => $regionid, ':build' => $val['buildtitle'], ':unit' => $item['unit']));
                sortArrByField($rooms, 'room');
                foreach ($rooms as $j => $r) {
                    $rooms[$j]['key'] = $r['id'];
                    $rooms[$j]['value'] = $r['room'].$arr['d1'];
                }
                $data['rooms'][$item['id']] = $rooms;

            }
        }


        util::send_result($data);
    }
}
elseif ($op == 'unit') {
    $condition = '';
    $params = array();
    $data = array();
    $buildid = trim($_GPC['buildid']);
    $regionid = intval($_GPC['regionid']);
    if ($buildid) {
        $condition .= " buildid=:buildid";
        $params[':buildid'] = $buildid;
    }
    if ($regionid) {
        $condition .= " and regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    $sql = "select * from" . tablename('xcommunity_unit') . "where $condition";
    $units = pdo_fetchall($sql, $params);
    $data['units'] = $units;
    util::send_result($data);
}
elseif ($op == 'room') {
    $condition = '';
    $params = array();
    $data = array();
    $buildid = trim($_GPC['buildid']);
    $regionid = intval($_GPC['regionid']);
    if ($buildid) {
        $condition .= " build=:buildid";
        $params[':buildid'] = $buildid;
    }
    if ($regionid) {
        $condition .= " and regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    $sql = "select * from" . tablename('xcommunity_member_room') . "where $condition";
    $rooms = pdo_fetchall($sql, $params);
    $data['rooms'] = $rooms;
    util::send_result($data);
}
elseif ($op == 'rooms') {
    $regionid = intval($_GPC['regionid']);
    //2种情况，一种有区域，选择区域楼栋单元房号，一种没有区域，选择楼栋单元房号
    $data = array();
    $area = set('p36') || set('x17', $regionid) ? 1 : 0;
    $arr = util::xqset($regionid);
    $data['area'] = $area;
    $data['arr'] = $arr;
    if ($regionid) {
        if ($area) {
            $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('id', 'title'));
            foreach ($areas as $k => $v) {
                $areas[$k]['key'] = $v['id'];
                $areas[$k]['value'] = $v['title'].$arr['a1'];
            }
            $data['areas'] = $areas;
            foreach ($areas as $k => $v) {
                $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid, 'areaid' => $v['id']), array('buildtitle', 'id'));
                foreach ($builds as $key => $val) {
                    $builds[$key]['key'] = $val['id'];
                    $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
                }
                $data['builds'][$v['id']] = $builds;
                foreach ($builds as $key => $val) {
                    $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
                    foreach ($units as $kk => $va) {
                        $units[$kk]['key'] = $va['id'];
                        $units[$kk]['value'] = $va['unit'].$arr['c1'];
                    }
                    $data['units'][$val['id']] = $units;
                    foreach ($units as $i => $item) {
                        $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $regionid, 'build' => $val['buildtitle'], 'area' => $v['title']), array('room', 'id'));
                        foreach ($rooms as $j => $r) {
                            $rooms[$j]['key'] = $r['id'];
                            $rooms[$j]['value'] = $r['room'].$arr['d1'];
                        }
                        $data['rooms'][$item['id']] = $rooms;
                    }
                }
            }
        }
        else {
            $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid), array('buildtitle', 'id', 'areaid'));
            foreach ($builds as $key => $val) {
                $builds[$key]['key'] = $val['id'];
                $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
            }
            foreach ($builds as $key => $val) {
                if ($val['areaid']) {
                    $areatitle = pdo_getcolumn('xcommunity_area', array('id' => $val['areaid']), 'title');
                    $builds[$key]['buildtitle'] = $areatitle . $arr[a1] . $val['buildtitle'];
                }
                $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
                foreach ($units as $kk => $va) {
                    $units[$kk]['key'] = $va['id'];
                    $units[$kk]['value'] = $va['unit'].$arr['c1'];
                }
                $data['units'][$val['id']] = $units;
                foreach ($units as $i => $item) {
                    $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $regionid, 'build' => $val['buildtitle']), array('room', 'id'));
                    foreach ($rooms as $j => $r) {
                        $rooms[$j]['key'] = $r['id'];
                        $rooms[$j]['value'] = $r['room'].$arr['d1'];
                    }
                    $data['rooms'][$item['id']] = $rooms;
                }
            }
            $data['builds'] = $builds;
        }
    }
    else {
        $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        if ($area) {
            foreach ($regions as $j => $region) {
                $regions[$j]['key'] = $region['id'];
                $regions[$j]['value'] = $region['title'];
                $areas = pdo_getall('xcommunity_area', array('regionid' => $region['id']), array('id', 'title'));
                foreach ($areas as $k => $v) {
                    $areas[$k]['key'] = $v['id'];
                    $areas[$k]['value'] = $v['title'].$arr['a1'];
                }
                $data['areas'][$region['id']] = $areas;
                foreach ($areas as $k => $v) {
                    $builds = pdo_getall('xcommunity_build', array('regionid' => $region['id'], 'areaid' => $v['id']), array('buildtitle', 'id'));
                    foreach ($builds as $key => $val) {
                        $builds[$key]['key'] = $val['id'];
                        $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
                        $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
                        foreach ($units as $i => $item) {
                            $units[$i]['key'] = $item['id'];
                            $units[$i]['value'] = $item['unit'].$arr['c1'];
                            $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $region['id'], 'build' => $val['buildtitle'], 'area' => $v['title']), array('room', 'id'));
                            foreach ($rooms as $h => $room) {
                                $rooms[$h]['key'] = $room['id'];
                                $rooms[$h]['value'] = $room['room'].$arr['d1'];
                                $members = pdo_fetch("select realname,mobile from" . tablename('xcommunity_member_log') . " where addressid=:addressid and status=1", array(':addressid' => $room['id']));
                                $data['members'][$room['id']] = $members;
                            }
                            $data['rooms'][$item['id']] = $rooms;
                        }
                        $data['units'][$val['id']] = $units;
                    }
                    $data['builds'][$v['id']] = $builds;
                }
            }
        }
        else {
            foreach ($regions as $j => $region) {
                $regions[$j]['key'] = $region['id'];
                $regions[$j]['value'] = $region['title'];
                $builds = pdo_getall('xcommunity_build', array('regionid' => $region['id']), array('buildtitle', 'id', 'areaid'));
                foreach ($builds as $key => $val) {
                    if ($val['areaid']) {
                        $areatitle = pdo_getcolumn('xcommunity_area', array('id' => $val['areaid']), 'title');
                        $builds[$key]['buildtitle'] = $areatitle . $arr[a1] . $val['buildtitle'];
                    }
                    $builds[$key]['key'] = $val['id'];
                    $builds[$key]['value'] = $val['buildtitle'].$arr['b1'];
                    $units = pdo_getall('xcommunity_unit', array('buildid' => $val['id']), array('unit', 'id'));
                    foreach ($units as $i => $item) {
                        $units[$i]['key'] = $item['id'];
                        $units[$i]['value'] = $item['unit'].$arr['c1'];
                        $rooms = pdo_getall('xcommunity_member_room', array('unit' => $item['unit'], 'regionid' => $region['id'], 'build' => $val['buildtitle']), array('room', 'id'));
                        foreach ($rooms as $h => $room) {
                            $rooms[$h]['key'] = $room['id'];
                            $rooms[$h]['value'] = $room['room'].$arr['d1'];
                            $members = pdo_fetch("select realname,mobile from" . tablename('xcommunity_member_log') . " where addressid=:addressid and status=1", array(':addressid' => $room['id']));
                            $data['members'][$room['id']] = $members;
                        }
                        $data['rooms'][$item['id']] = $rooms;
                    }
                    $data['units'][$val['id']] = $units;
                }
                $data['builds'][$region['id']] = $builds;
            }
        }
        $data['regions'] = $regions;
    }
    util::send_result($data);
}

