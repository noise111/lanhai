<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/12 下午11:30
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'one', 'build', 'unit', 'room', 'area', 'fet', 'rank', 'areas');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    //获取小区列表接口
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(200, intval($_GPC['psize']));
    $condition = ' uniacid = :uniacid and status=1';
    $params[':uniacid'] = $_W['uniacid'];
    $keyword = $_GPC['keyword'];
    $lng = $_GPC['lng'];
    $lat = $_GPC['lat'];
    if ($keyword) {
        $condition .= " AND title like '%{$keyword}%'";
    }
    if (set('p8')) {
        if ($lng && $lat) {
            $range = set('p9') ? set('p9') : 5;
            $point = util::squarePoint($lng, $lat, $range);
            $condition .= " AND lat<>0 AND lat >= '{$point['right-bottom']['lat']}' AND lat <= '{$point['left-top']['lat']}' AND lng >= '{$point['left-top']['lng']}' AND lng <= '{$point['right-bottom']['lng']}'";
        }
    }
    $sql = "SELECT id,title,address,linkway,thumb,url,city,dist,lat,lng FROM " . tablename('xcommunity_region') . " WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);

    $arr = array();
    foreach ($list as $key => $value) {
        $user = pdo_get('xcommunity_member', array('regionid' => $value['id'], 'uid' => $_W['member']['uid'], 'enable' => 1), array('id'));
        $url = $value['url'] ? $value['url'] : $this->createMobileUrl('register', array('op' => 'guide', 'regionid' => $value['id'], 'memberid' => $user['id'], 'title' => $value['title']));
        $arr[] = array(
            'id' => $value['id'],
            'title' => $value['title'],
            'address' => $value['address'],
            'linkway' => $value['linkway'],
            'thumb' => $value['thumb'],
            'url' => $url,
            'city' => $value['city'],
            'dist' => $value['dist'],
            'lat' => $value['lat'],
            'lng' => $value['lng'],
            'enable' => $user ? 1 : 0, //已绑定

        );
    }
    if ($arr) {
        if ($lng && $lat) {
            $min = -1;
            $count = count($arr);
            foreach ($arr as &$row) {
                $row['distance'] = util::GetDistance($lat, $lng, $row['lat'], $row['lng']);
                if ($min < 0 || $row['distance'] < $min) {
                    $min = $row['distance'];
                }
            }
            unset($row);
            $temp = array();
            for ($i = 0; $i < $count; $i++) {
                foreach ($arr as $j => $row) {
                    if (empty($temp['distance']) || $row['distance'] < $temp['distance']) {
                        $temp = $row;
                        $h = $j;
                    }
                }
                if (!empty($temp)) {
                    $juli = floor($temp['distance']) / 1000;
                    $rows[] = array(
                        'dis' => $temp['distance'],
                        'juli' => sprintf('%.1f', (float)$juli) . 'km',
                        'id' => $temp['id'],
                        'title' => $temp['title'],
                        'address' => $temp['address'],
                        'linkway' => $temp['linkway'],
                        'thumb' => tomedia($temp['thumb']),
                        'url' => $temp['url'],
                        'city' => $temp['city'],
                        'dist' => $temp['dist'],
                        'enable' => $temp['enable'],
                        'key' => $temp['id'],
                        'value' => $temp['title']
                    );
                    unset($arr[$h]);
                    $temp = array();

                }
            }
        } else {
            $rows = $arr;
        }

    }
    $type = intval($_GPC['type']);
    if ($type) {
        if ($rows) {
            foreach ($rows as $k => $v) {
                $arrs[] = array($v['title'], $v['id']);
            }
        }

    }
    if (empty($type) && $rows) {
        //按距离排
        sortArrByField($rows, 'dis');
        if ($rows) {
            $arrs = $rows;
        }
    }

    $data = array();
    $data['list'] = $arrs ? $arrs : 0;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);


} elseif ($op == 'one') {
    //获取一条小区信息
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    if (empty($regionid)) {
        util::send_error(-1, '参数错误');
    }
    $sql = "select t1.qq,t1.linkway,t1.thumb,t2.content,t2.id as pid,t2.telphone from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid = t2.id where t1.id=:id";
    $item = pdo_fetch($sql, array(':id' => $regionid));
    $item['thumb'] = tomedia($item['thumb']);

    $item['linkwayurl'] = "tel:" . $item['linkway'];
    $item['telphoneurl'] = "tel:" . $item['telphone'];
    $item['qqurl'] = "http://wpa.qq.com/msgrd?v=3&amp;uin=" . $item['qq'] . "&amp;site=qq&amp;menu=yes";

//    $item['content'] = strip_tags($item['content']);
    util::send_result($item);

}
if ($op == 'build') {
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid), array('buildtitle', 'id'));
        echo json_encode($builds);
        exit();
    }
} elseif ($op == 'unit') {
    $buildtitle = $_GPC['build'];
    if ($buildtitle) {
        $units = pdo_getall('xcommunity_unit', array('buildtitle' => $buildtitle), array('unit', 'id'));
        echo json_encode($units);
        exit();
    }
} elseif ($op == 'room') {
    $build = $_GPC['build'];
    $unit = $_GPC['unit'];
    $regionid = intval($_GPC['regionid']);
    $condition = '';
    if ($regionid) {
        $condition .= "regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    if ($build) {
        $condition .= " and build=:build";
        $params[':build'] = $build;
    }
    if ($unit) {
        $condition .= " and unit=:unit";
        $params[':unit'] = $unit;
    }
    $sql = "select * from" . tablename('xcommunity_member_room') . "where $condition";
    $rooms = pdo_fetchall($sql, $params);
    echo json_encode($rooms);
    exit();
} elseif ($op == 'area') {
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('title', 'id'));
        foreach ($areas as $k => $v) {
            $data[] = array(
                'text' => $v['title'],
                'value' => $v['id']
            );
        }
        util::send_result($data);
    }
} elseif ($op == 'fet') {
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    if (empty($regionid)) {
        util::send_error(-1, '参数错误');
    }
    $sql = "select t1.* from" . tablename('xcommunity_mien') . "t1 left join" . tablename('xcommunity_mien_region') . "t2 on t1.id= t2.mienid where t2.regionid=:regionid";
    $list = pdo_fetchall($sql, array(':regionid' => $regionid));
    foreach ($list as $k => $v) {
        $list[$k]['image'] = tomedia($v['image']);
        $list[$k]['url'] = "tel:" . $v['mobile'];
    }
    $data = array();
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['list'] = $list;
    util::send_result($data);
}
/**
 * 小区的评价
 */
if ($op == 'rank') {
    $uid = $_W['member']['uid'];
    $rank = pdo_getall('xcommunity_rank', array('uid' => $uid, 'type' => 5), array(), '', array('createtime desc'));
    $time = strtotime("+3 month", $rank[0]['createtime']);
    if (TIMESTAMP < $time) {
        util::send_error(-1, '评价未满3个月');
    }
    $pid = intval($_GPC['pid']);
    $content = trim($_GPC['content']);
    $num = intval($_GPC['num']);
    $status = intval($_GPC['status']);
    $data = array(
        'uniacid' => $_W['uniacid'],
        'type' => 5,
        'uid' => $_W['member']['uid'],
        'rankid' => $pid,
        'rank' => $num,
        'createtime' => TIMESTAMP,
        'content' => $content,
        'status' => $status
    );
    if (pdo_insert('xcommunity_rank', $data)) {
        util::send_result();
    }

} elseif ($op == 'areas') {
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $_W['uniacid']), array('id'));
    $areas = array();
    foreach ($regions as $k => $v) {
        $areas[$v['id']] = pdo_getall('xcommunity_area', array('regionid' => $v['id']), array('id', 'title'));
    }
    util::send_result($areas);
}
