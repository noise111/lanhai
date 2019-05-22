<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/8/26 下午1:58
 */
define('IN_SYS', true);
require_once '../../../framework/bootstrap.inc.php';
$url = "http://test.inebao.com/open";
/**
 * 批量同步新增物业
 */
$property_add_url = $url . '/api/property/create';
$condition = array();
$condition['action'] = 0;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_property', $condition, array('id', 'title', 'telphone'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['title']) {
            $newlist = array(
                'name'     => $v['title'],
                'contacts' => 'xxx' . time(),
                'phone'    => $v['telphone']
            );
            $result = createData($property_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                if ($data['propertyId']) {
                    $d = array(
                        'yybstatus'     => 1,
                        'yybpropertyid' => $data['propertyId']
                    );
                    pdo_update('xcommunity_property', $d, array('id' => $v['id']));
                }
            }
        }
    }
    unset($list, $newlist, $condition);
}

/**
 * 批量同步修改物业
 */
$property_up_url = $url . '/api/property/modify';
$condition = array();
$condition['action'] = 1;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_property', $condition, array('id', 'title', 'telphone'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybpropertyid']) {
            $newlist = array(
                'propertyId' => $v['yybpropertyid'],
                'name'       => $v['title'],
                'contacts'   => 'xxx' . time(),
                'phone'      => $v['telphone']
            );
            $result = createData($property_up_url, $newlist);
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybstatus' => 1,
                );
                pdo_update('xcommunity_property', $d, array('id' => $v['id']));
            }
        }
    }
    unset($list, $newlist, $condition);
}

/**
 * 批量同步新增小区
 */
$community_add_url = $url . '/api/community/merge/create';
$sql = "select t1.id, t1.title, t1.dist, t1.lng, t1.lat,t2.yybpropertyid from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid=t2.id where t1.action=0 and t1.yybstatus=0";
$list = pdo_fetchall($sql);
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybpropertyid'] && $v['title'] && $v['dist'] && $v['lng'] && $v['lat']) {
            $newlist = array(
                'name'       => $v['title'],
                'propertyId' => $v['yybpropertyid'],
                'areaName'   => $v['dist'],
                'lon'        => $v['lng'],
                'lat'        => $v['lat'],
            );
            $result = createData($community_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                if ($data['communityId']) {
                    $d = array(
                        'yybstatus'      => 1,
                        'yybcommunityid' => $data['communityId']
                    );
                    pdo_update('xcommunity_region', $d, array('id' => $v['id']));
                }
            }
        }
    }

    unset($list, $newlist);
}
/**
 * 批量同步修改小区
 */
$community_up_url = $url . '/api/community/modify';
$condition = array();
$condition['action'] = 1;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_region', $condition, array('id', 'title', 'dist', 'lng', 'lat'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybcommunityid']) {
            $newlist = array(
                'communityId' => $v['yybcommunityid'],
                'name'        => $v['title'],
                'areaName'    => $v['dist'],
                'lon'         => $v['lng'],
                'lat'         => $v['lat']
            );
            $result = createData($community_up_url, $newlist);
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybstatus' => 1,
                );
                pdo_update('xcommunity_region', $d, array('id' => $v['id']));
            }
        }
    }
    unset($list, $newlist, $condition);
}

/**
 * @param $url
 * @param 批量同步新增楼宇
 * @return mixed
 */
$build_add_url = $url . '/api/build/createByCommunity';
$sql = "select t2.buildtitle,t1.yybcommunityid,t2.id from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_build') . "t2 on t1.id=t2.regionid where t2.action=0 and t2.yybstatus=0";
$list = pdo_fetchall($sql);
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['buildtitle'] && $v['yybcommunityid']) {
            $newlist = array(
                'name'        => $v['buildtitle'],
                'communityId' => $v['yybcommunityid'],
            );
            $result = createData($build_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                if ($data['buildId']) {
                    $d = array(
                        'yybstatus'  => 1,
                        'yybbuildid' => $data['buildId']
                    );
                    pdo_update('xcommunity_build', $d, array('id' => $v['id']));
                }
            }
        }
    }
    unset($list, $newlist);
}

/**
 * 批量同步修改楼宇
 */
$build_up_url = $url . '/api/build/modify';
$condition = array();
$condition['action'] = 1;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_build', $condition, array('id', 'buildtitle', 'yybbuildid'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybbuildid']) {
            $newlist = array(
                'name'    => $v['buildtitle'],
                'buildId' => $v['yybbuildid'],
            );
            $result = createData($build_up_url, $newlist);
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybstatus' => 1,
                );
                pdo_update('xcommunity_build', $d, array('id' => $v['id']));
            }
        }
    }
    unset($list, $newlist, $condition);
}

/**
 * @param $url
 * @param 批量同步新增单元
 * @return mixed
 */
$unit_add_url = $url . '/api/unit/create';
$sql = "select t1.unit,t2.yybbuildid,t1.id from" . tablename('xcommunity_unit') . "t1 left join" . tablename('xcommunity_build') . "t2 on t1.buildid=t2.id where t1.action=0 and t1.yybstatus=0";
$list = pdo_fetchall($sql);
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybbuildid'] && $v['unit']) {
            $newlist = array(
                'name'    => $v['unit'],
                'buildId' => $v['yybbuildid'],
            );
            $result = createData($unit_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                if ($data['unitId']) {
                    $d = array(
                        'yybstatus' => 1,
                        'yybunitid' => $data['unitId']
                    );
                    pdo_update('xcommunity_unit', $d, array('id' => $v['id']));
                }
            }
        }

    }
    unset($list, $newlist);
}

/**
 * @param $url
 * @param 批量同步修改单元
 * @return mixed
 */
$unit_up_url = $url . '/api/unit/modify';
$condition = array();
$condition['action'] = 1;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_unit', $condition, array('id', 'unit', 'yybbuildid'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybunitid']) {
            $newlist = array(
                'name'   => $v['unit'],
                'unitId' => $v['yybunitid'],
            );
            $result = createData($unit_up_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybstatus' => 1,
                );
                pdo_update('xcommunity_unit', $d, array('id' => $v['id']));
            }
        }

    }
    unset($list, $newlist, $condition);
}

/**
 * @param $url
 * @param 批量同步创建房间
 * @return mixed
 */
$room_add_url = $url . '/api/room/create';
$sql = "select t1.id,t1.unit,t1.room,t2.yybunitid from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_unit') . "t2 on t1.unitid=t2.id where t1.action=0 and t1.yybstatus=0";
$list = pdo_fetchall($sql);
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['room'] && $v['yybunitid']) {
            $newlist = array(
                'name'   => $v['room'],
                'unitId' => $v['yybunitid'],
            );
            $result = createData($room_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                if ($data['roomId']) {
                    $d = array(
                        'yybstatus' => 1,
                        'yybroomid' => $data['roomId']
                    );
                    pdo_update('xcommunity_member_room', $d, array('id' => $v['id']));
                }
            }
        }

    }
    unset($list, $newlist);
}

/**
 * @param $url
 * @param 批量同步修改房间
 * @return mixed
 */
$room_up_url = $url . '/api/room/modify';
$condition = array();
$condition['action'] = 1;
$condition['yybstatus'] = 0;
$list = pdo_getall('xcommunity_member_room', $condition, array('id', 'room', 'yybroomid'));
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['yybroomid']) {
            $newlist = array(
                'name'   => $v['room'],
                'roomId' => $v['yybroomid'],
            );
            $result = createData($room_up_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybstatus' => 1,
                );
                pdo_update('xcommunity_member_room', $d, array('id' => $v['id']));
            }
        }
    }
    unset($list, $newlist, $condition);
}

/**
 * @param $url
 * @param 创建用户
 * @return mixed
 */
$member_add_url = $url . '/api/resident/create';
$sql = "select t2.status,t3.yybroomid,t4.realname,t4.mobile,t5.openid,t4.uid  from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id=t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t3.id=t2.addressid left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('mc_mapping_fans') . 't5 on t4.uid=t5.uid where t1.visit =0 and yybroomid !=0 and t3.yybstatus=0 and t1.status=1';
$list = pdo_fetchall($sql);
if (!empty($list)) {
    $newlist = array();
    foreach ($list as $v) {
        //重组数组
        if ($v['realname'] && $v['mobile'] && $v['yybroomid'] && $v['openid'] && $v['status']) {
            $newlist = array(
                'name'     => $v['realname'],
                'phone'    => $v['mobile'],
                'roomId'   => $v['yybroomid'],
                'wxOpenId' => $v['openid'],
                'type'     => $v['status']
            );
            $result = createData($member_add_url, $newlist);
            $data = $result['data'];
            if ($result['errCode'] == '100000') {
                $d = array(
                    'yybopenid'     => $data['openId'],
                );
                pdo_update('mc_members', $d, array('uid' => $v['uid']));
                $dat = array(
                    'yybresidentid' => $data['residentId'],
                    'yybstatus'     => 1
                );
                pdo_update('xcommunity_member_room', $dat, array('yybroomid' =>  $v['yybroomid']));
            }
        }
    }
    unset($list, $newlist);
}
/**
 * @param $url
 * @param 删除用户
 * @return mixed
 */
$member_delete_url = $url . '/api/resident/delete';

function createData($url, $list)
{
    $appid = "9b3b49b5254b810df92d48e3a710d086";
    $sign = sign($list);
    $result = http_post($url, json_encode($list), $appid, $sign);
    $result = @json_decode($result['content'], true);
    return $result;
}

function sign($params)
{
    $appid = "9b3b49b5254b810df92d48e3a710d086";
    $appsecret = "34ac498d12ae7ef286973b2eb06d83bd";
    $sign = md5($appid . time() . $appsecret);
    return $sign;
}

function http_post($url, $data, $appid, $sign)
{
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded', 'appId' => $appid, 'timestamp' => time(), 'sign' => $sign, 'Version' => '1.0.0');
    load()->func('communication');
    return ihttp_request($url, $data, $headers);
}