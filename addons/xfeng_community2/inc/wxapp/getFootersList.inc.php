<?php
/**
 * 底部菜单的列表
 */
global $_GPC, $_W;
$regionid = $_SESSION['community']['regionid'];
$condition = " t1.uniacid=:uniacid and t1.enable=1 and t2.regionid=:regionid ";
$params[':uniacid'] = $_W['uniacid'];
$params[':regionid'] = $regionid;
$sql = "select t1.title,t1.type,t1.icon,t1.click_icon,t1.url from" . tablename('xcommunity_footmenu') . "t1 left join" . tablename('xcommunity_footmenu_region') . "t2 on t1.id=t2.fid where $condition order by t1.displayorder asc";
$list = pdo_fetchall($sql, $params);
$num = util::readnotice($regionid);
foreach ($list as $key => $val) {
    if ($val['type'] == 2) {
        //内置公告
        $list[$key]['num'] = $num;
    }
    $list[$key]['icon'] = tomedia($val['icon']);
    $list[$key]['clickIcon'] = tomedia($val['click_icon']);
}
if (!empty($list)) {
    util::send_result($list);
} else {
    util::send_result();
}
