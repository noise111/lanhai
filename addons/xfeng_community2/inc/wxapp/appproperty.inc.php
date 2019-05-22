<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/8/10 0010
 * Time: 下午 2:10
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = ' uniacid = :uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        //普通管理员
        $condition .= " and uid =:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $regions = pdo_fetchall("SELECT pid FROM " . tablename('xcommunity_region') . " WHERE id in({$_SESSION['appregionids']})");
        $pids = '';
        foreach ($regions as $k => $v){
            $pids .= $v['pid'].',';
        }
        $pids = xtrim($pids);
        $condition .= " and id in({$pids})";
    }
    if ($_SESSION['apptype'] == 5 || $_SESSION['apptype'] == 4) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "SELECT * FROM " . tablename('xcommunity_property') . " WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $data = array();
    if ($list) {
        foreach ($list as $k => $v) {
            $data[] = array(
                'title' => $v['title'],
                'value' => $v['id'],
                'link' => $this->createMobileUrl('xqsys', array('op' => 'property', 'p' => 'add', 'id' => $v['id'])),
                'id' => $v['id'],
                'linkway' => $v['telphone']
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
    //添加物业
    $data = array(
        'uniacid'  => $_SESSION['appuniacid'],
        'title'    => $_GPC['title'],
        'telphone'  => $_GPC['telphone'],
        'thumb'    => $_GPC['morepic'],
        'content' => trim($_GPC['content']),
        'createtime'    => TIMESTAMP
    );
    $id = intval($_GPC['id']);
    if ($id) {
        pdo_update("xcommunity_property", $data, array('id' => $id));
        $regionid = $id;
        util::permlog('物业管理-修改', '修改物业ID:' . $regionid . '修改名称:' . $data['title']);
    }
    else {
        $region = pdo_fetch("SELECT id FROM" . tablename('xcommunity_property') . "WHERE uniacid='{$_SESSION['appuniacid']}' AND title='{$_GPC['title']}' ");
        if ($region) {
            util::send_error(-1, '物业已存在');
            exit();
        }
        $data['uid'] = $_SESSION['appuid'];
        pdo_insert("xcommunity_property", $data);
        $regionid = pdo_insertid();
        util::permlog('物业管理-添加', '添加物业ID:' . $regionid . '添加名称:' . $data['title']);
    }
    util::send_result();
}
elseif($op =='detail'){
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_property') . "WHERE uniacid=:uniacid AND id=:id", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        $item['thumb'] = explode(',', $item['thumb']);
        $item['content'] = strip_tags($item['content']);
        $pattern = '/\s/';//去除空白
        $item['content'] = preg_replace($pattern, '', $item['content']);
        foreach ($item['thumb'] as $ke => $va) {
            $item['thumb'][$ke] = tomedia($va);
        }
        util::send_result($item);
    }
}