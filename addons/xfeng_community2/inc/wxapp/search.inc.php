<?php
/**
 * Created by myapp.
 * User: mac
 * Time: 2017/12/4 下午1:00
 */
global $_GPC, $_W;
$ops = array('list', 'detail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$_SESSION['community'] = model_user::mc_check('phone');
if($op =='list'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    $list = pdo_fetchall('select t1.icon,t1.sname,t1.surl from'.tablename('xcommunity_search')."as t1 left join".tablename('xcommunity_search_region')."as t2 on t1.id=t2.sid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.status = 1 LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':uniacid' => $_W['uniacid'],':regionid'=> $regionid));
    foreach ($list as $key => $val){
        $list[$key]['icon'] = tomedia($val['icon']);
    }
    util::send_result($list);
}