<?php
/*/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 上午11:50
 * 手机端管理系统小区
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','property','regionlist','regionareas');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = ' uniacid = :uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $keyword = $_GPC['keyword'];
    if ($keyword) {
        $condition .= " AND title like '%{$keyword}%'";
    }
    if ($_SESSION['apptype'] == 2) {
        //普通管理员
        $condition .= " and uid =:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3 || $_SESSION['apptype'] == 4) {
        //小区管理员
        $condition .= " and id in({$_SESSION['appregionids']})";
    }
    if ($_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "SELECT id,title,address,linkway,thumb,url,city,dist FROM " . tablename('xcommunity_region') . " WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $data = array();
    if ($list) {
        foreach ($list as $k => $v) {
            $data[] = array(
                'title' => $v['title'],
                'value' => $v['id'],
                'link' => $this->createMobileUrl('xqsys', array('op' => 'region', 'p' => 'add', 'id' => $v['id'])),
                'id' => $v['id'],
                'address' => $v['address'],
                'linkway' => $v['linkway']
            );
        }
    }
    util::send_result($data);
}
elseif($op =='add'){
    if (empty($_SESSION['appuid'])) {
        util::send_error(-1, '请先登录');
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    //添加小区
    $reside = explode('-', trim($_GPC['reside']));
    $data = array(
        'uniacid'  => $_W['uniacid'],
        'title'    => $_GPC['title'],
        'linkmen'  => $_GPC['linkmen'],
        'linkway'  => $_GPC['linkway'],
        'lng'      => $_GPC['lng'],
        'lat'      => $_GPC['lat'],
        'address'  => $_GPC['address'],
        'url'      => $_GPC['url'],
        'thumb'    => $_GPC['morepic'],
        'qq'       => $_GPC['qq'],
        'province' => $reside[0],
        'city'     => $reside[1],
        'dist'     => $reside[2],
        'keyword'  => $_GPC['keyword'],
        'pid'      => intval($_GPC['pid']),
        'area'      => $_GPC['area']
    );
    $id = intval($_GPC['id']);
    $ru = pdo_get('rule', array('name' => $_GPC['title']), array('id'));
    if (empty($ru)) {
        $rule = array(
            'uniacid' => $_W['uniacid'],
            'name'    => $_GPC['title'],
            'module'  => 'cover',
            'status'  => 1,
        );
        $result = pdo_insert('rule', $rule);
        $rid = pdo_insertid();
    }
    else {
        $rid = $ru['id'];
    }

    if ($id) {
        $data['rid'] = $ru['id'];
        pdo_update("xcommunity_region", $data, array('id' => $id));
        $regionid = $id;
        util::permlog('小区管理-修改', '修改小区ID:' . $regionid . '修改名称:' . $data['title']);
    }
    else {
        $region = pdo_fetch("SELECT id FROM" . tablename('xcommunity_region') . "WHERE uniacid='{$_W['uniacid']}' AND title='{$_GPC['title']}' AND province='{$reside[0]}' AND city='{$reside[1]}' AND dist='{$reside[2]}'");
        if ($region) {
            util::send_error(-1, '小区已存在');
            exit();
        }
        $data['rid'] = $rid;
        $data['uid'] = $_W['uid'];
        $data['status'] = 1;
        pdo_insert("xcommunity_region", $data);
        $regionid = pdo_insertid();
        util::permlog('小区管理-添加', '添加小区ID:' . $regionid . '添加名称:' . $data['title']);
        if ($user) {
            $d = array(
                'usersid'  => $_W['uid'],
                'regionid' => $regionid
            );
            pdo_insert('xcommunity_users_region', $d);
        }
        $navs = pdo_getall('xcommunity_nav', array('uniacid' => $_W['uniacid']), array('id'));
        foreach ($navs as $k => $v) {
            $d = array(
                'nid'      => $v['id'],
                'regionid' => $regionid
            );
            pdo_insert('xcommunity_nav_region', $d);
        }
        $hnavs = pdo_getall('xcommunity_housecenter', array('uniacid' => $_W['uniacid']), array('id'));
        foreach ($hnavs as $k => $v) {
            $d = array(
                'nid'      => $v['id'],
                'regionid' => $regionid
            );
            pdo_insert('xcommunity_housecenter_region', $d);
        }
        $search = pdo_getall('xcommunity_search', array('uniacid' => $_W['uniacid']), array('id'));
        foreach ($search as $key => $val) {
            $d = array(
                'sid'      => $val['id'],
                'regionid' => $regionid
            );
            pdo_insert('xcommunity_search_region', $d);
        }
        $phone = pdo_getall('xcommunity_phone', array('uniacid' => $_W['uniacid']), array('id'));
        foreach ($phone as $key => $val) {
            $d = array(
                'phoneid'  => $val['id'],
                'regionid' => $regionid
            );
            pdo_insert('xcommunity_phone_region', $d);
        }
    }
    $rules = pdo_get("rule_keyword", array('rid' => $rid), array('id'));
    $covers = pdo_get('cover_reply', array('rid' => $rid), array('id'));
    $ruleword = array(
        'rid'     => $rid,
        'uniacid' => $_W['uniacid'],
        'module'  => 'cover',
        'content' => $data['keyword'],
        'type'    => 1,
        'status'  => 1,
    );
    if (empty($rules)) {
        pdo_insert('rule_keyword', $ruleword);
    }
    else {
        pdo_update('rule_keyword', $ruleword, array('id' => $rules['id']));
    }
    $crid = $ru ? $ru['id'] : $rid;
    $entry = array(
        'uniacid'     => $_W['uniacid'],
        'multiid'     => 0,
        'rid'         => $crid,
        'title'       => $_GPC['title'],
        'description' => '',
        'thumb'       => tomedia($_GPC['pic']),
        'url'         => $this->createMobileUrl('home', array('regionid' => $regionid)),
        'do'          => 'home',
        'module'      => $this->module['name'],
    );
    if (empty($covers)) {
        pdo_insert('cover_reply', $entry);
    }
    else {
        pdo_update('cover_reply', $entry, array('rid' => $crid));
    }
    util::send_result();
}
elseif($op =='detail'){
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_region') . "WHERE uniacid=:uniacid AND id=:id", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        $areas = explode(',',$item['area']);
        $item['code'] = $areas[2];
//        $item['ptitle'] = $item['province'].'-'.$item['city'].'-'.$item['dist'];
        $item['ptitle'] = array($item['province'],$item['city'],$item['dist']);
        util::send_result($item);
    }
}
elseif ($op == 'property') {
    //$list = model_region::property_fetall();
    $regionids = $_SESSION['appregionids'];
//    echo $regionids;
    /***
     * 查小区
     */
    $condition = array();
    $condition['id'] = explode(',',$regionids);
    $regions = pdo_getall('xcommunity_region',$condition,array('pid'));
    $regions_pids = _array_column($regions, 'pid');
    $list = pdo_getall('xcommunity_property',array('id'=>$regions_pids),array('id','title'));
    foreach ($list as $k =>$v){
        $list[$k]['key'] = $v['id'];
        $list[$k]['value'] = $v['title'];
    }
    util::send_result($list);
}
elseif ($op == 'regionlist') {
    $condition = ' uniacid = :uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
//    if ($_SESSION['apptype'] == 2) {
//        //普通管理员
//        $condition .= " and uid =:uid";
//        $params[':uid'] = $_SESSION['appuid'];
//    }
    if ($_SESSION['apptype'] == 3 || $_SESSION['apptype'] == 4) {
        //小区管理员
        $condition .= " and id in({$_SESSION['appregionids']})";
    }
    if ($_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "SELECT id,title FROM " . tablename('xcommunity_region') . " WHERE $condition ";
    $list = pdo_fetchall($sql, $params);
    $data = array();
    if ($list) {
        foreach ($list as $k => $v) {
            $data[] = array(
                'value' => $v['title'],
                'key' => $v['id'],
            );
        }
    }
    util::send_result($data);
}
elseif ($op == 'regionareas'){
    $uniacid = $_SESSION['appuniacid'];
    $data = array();
    $regions = pdo_getall('xcommunity_region', array('uniacid' => $uniacid), array('id', 'title'));
    foreach($regions as $key => $val){
        $areas = pdo_getall('xcommunity_area', array('regionid' => $val['id']), array('id', 'title'));
        foreach ($areas as $k => $v) {
            $arr = util::xqset($val['id']);
            $data[$val['id']][$k]['key'] = $v['id'];
            $data[$val['id']][$k]['value'] = $v['title'].$arr['a1'];
        }
    }

    util::send_result($data);
}