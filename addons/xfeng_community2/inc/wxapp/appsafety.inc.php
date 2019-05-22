<?php
global $_GPC, $_W;
$ops = array('list','log');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list'){

}
elseif ($op == 'log'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid ";
    $params[':uniacid'] = $_SESSION['appuniacid'] ;
//    $params[':uniacid'] = 2;
    $sql = "select t1.*,t2.title,t2.card_num as dcard_num from" . tablename('xcommunity_safety_device_log') . "t1 left join" . tablename('xcommunity_safety') . "t2 on t1.safetyid = t2.id where $condition order by t1.acq_date desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v){
        $list[$k]['desc'] = $v['card_num'].','.date('Y-m-d H:i',$v['acq_date']);
    }
    util::send_result($list);
}