<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/4/10 0010
 * Time: 下午 3:21
 */
global $_GPC, $_W;
$ops = array('list');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$regionid = $_SESSION['community']['regionid'];
if($op == 'list'){
    $type = intval($_GPC['type']);
    $adv = pdo_fetchall("select t1.thumb from".tablename('xcommunity_slide')."t1 left join".tablename('xcommunity_slide_region')."t2 on t1.id = t2.sid where t1.uniacid=:uniacid and t1.type =:type and t1.status=1 and t2.regionid=:regionid",array(':uniacid' => $_W['uniacid'],':regionid' => $regionid,':type' => $type));
    foreach ($adv as $k => $v){
        $adv[$k]['thumb'] = tomedia($v['thumb']);
    }
    $data = array();
    $data['adv'] = $adv;
    util::send_result($data);
}