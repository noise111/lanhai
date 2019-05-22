<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/7/19 0019
 * Time: 下午 3:41
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'del');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid ";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $user = util::xquser($_SESSION['appuid']);
    $condition .= " and t2.companyid=:pid";
    $params[':pid'] = $user['pid'];
    $sql = "select t1.* from " . tablename("xcommunity_memo") . "t1 left join" . tablename('xcommunity_memo_company') . "t2 on t1.id=t2.memoid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $params);
    $list = array();
    if ($row) {
        foreach ($row as $key => $val) {
            $list[] = array(
                'title' => $val['title'],
                'desc' => date('Y-m-d H:i', $val['createtime']),
                'url' => $this->createMobileUrl('xqsys', array('op' => 'notice', 'p' => 'detail', 'id' => $val['id'])),
            );
        }
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_memo',array('id' => $id));
    $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
    util::send_result($item);
}