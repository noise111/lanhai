<?php
/**
 * Created by myapp.
 * User: mac
 * Time: 2017/12/4 上午11:57
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
    $condition = 't1.uniacid=:uniacid and t2.regionid=:regionid';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':regionid'] = $_SESSION['community']['regionid'];
    $cid = intval($_GPC['cid']);
    if ($cid) {
        $condition .= " and t1.cid=:cid";
        $params[':cid'] = $cid;
    }
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and t1.content like '%{$keyword}%'";
    }
    $list = pdo_fetchall('select t1.content,t1.address,t1.introduction,t1.phone from' . tablename('xcommunity_phone') . "as t1 left join" . tablename('xcommunity_phone_region') . "as t2 on t1.id=t2.phoneid where $condition order by t1.displayorder asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    util::send_result($list);

}
elseif ($op == 'add') {
    $data = array(
        'uniacid'      => $_W['uniacid'],
        'content'      => trim($_GPC['title']),
        'cid'          => intval($_GPC['cid']),
        'phone'        => trim($_GPC['phone']),
        'address'      => trim($_GPC['address']),
        'status'       => 2,
        'introduction' => trim($_GPC['introduction']),
    );
    if (pdo_insert('xcommunity_phone', $data)) {
        util::send_result();
    }
}