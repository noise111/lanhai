<?php
/**
 * Created by xqms.cn.
 * User: 蓝牛科技
 * Time: 2017/8/28 下午10:59
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('repair', 'report', 'member', 'sms', 'wechat','cost','business','shop','open')) ? trim($_GPC['op']) : 'repair';
$id = intval($_GPC['id']);
$user = util::xquser($_W['uid']);
$regions = model_region::region_fetall();
if($op =='repair'){
    $regions = model_region::region_fetall();
    $condition = "t2.type = 1 and t2.uniacid=:uniacid";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and t2.regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t2.regionid in({$user['regionid']})";
    }
    $xqday = intval($_GPC['xqday']);
    if($xqday == 1){
        $starttime = time() - 86400 * 7;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 2){
        $starttime = time() - 86400 * 30;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 3){
        $starttime = time() - 86400 * 90;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 4){
        $starttime = time() - 86400 * 180;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 5){
        $starttime = time() - 86400 * 365;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    $total = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition",$param);
    $total1 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 1 ",$param);
    $total2 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 2 ",$param);
    $total3 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 3 ",$param);
    $ranksql = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 3 ";
    $ranktotal = pdo_fetchcolumn($ranksql,$param);
    $sql1 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 3 and t1.rank= 1";
    $ranktotal1 = pdo_fetchcolumn($sql1,$param);
    $sql2 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 3 and t1.rank= 2";
    $ranktotal2 = pdo_fetchcolumn($sql2,$param);
    $sql3 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 3 and t1.rank= 3";
    $ranktotal3 = pdo_fetchcolumn($sql3,$param);
    include $this->template('web/core/data/repair_data');
}
elseif ($op == 'report'){

    $condition = "t2.type = 2 and t2.uniacid=:uniacid";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and t2.regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t2.regionid in({$user['regionid']})";
    }
    $xqday = intval($_GPC['xqday']);
    if($xqday == 1){
        $starttime = time() - 86400 * 7;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 2){
        $starttime = time() - 86400 * 30;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 3){
        $starttime = time() - 86400 * 90;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 4){
        $starttime = time() - 86400 * 180;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    if($xqday == 5){
        $starttime = time() - 86400 * 365;
        $condition .= " and t2.createtime >{$starttime} ";
    }
    $total = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition",$param);
    $total1 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 1 ",$param);
    $total2 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 2 ",$param);
    $total3 = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."t2 where $condition and status = 3 ",$param);
    $ranksql = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 4 ";
    $ranktotal = pdo_fetchcolumn($ranksql,$param);
    $sql1 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 4 and t1.rank= 1";
    $ranktotal1 = pdo_fetchcolumn($sql1,$param);
    $sql2 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 4 and t1.rank= 2";
    $ranktotal2 = pdo_fetchcolumn($sql2,$param);
    $sql3 = "select count(*) from".tablename('xcommunity_rank')."t1 left join".tablename('xcommunity_report')."t2 on t2.id = t1.rankid where $condition and t1.type = 4 and t1.rank= 3";
    $ranktotal3 = pdo_fetchcolumn($sql3,$param);
    include $this->template('web/core/data/report_data');
}
elseif($op =='member'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = " t1.uniacid=:uniacid";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and t1.regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_member')."t1 left join".tablename('mc_members')."t2 on t1.uid = t2.uid where $condition AND t1.createtime >= :createtime AND t1.createtime <= :endtime and t2.mobile = '' ORDER BY t1.createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $listtwo = pdo_fetchall("select * from".tablename('xcommunity_member')."t1 left join".tablename('mc_members')."t2 on t1.uid = t2.uid where $condition AND t1.createtime >= :createtime AND t1.createtime <= :endtime and t2.mobile != '' ORDER BY t1.createtime ASC", $param);
        $list[$j]['zhnum'] = count($listtwo);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit = $hit1 = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);
            $hit1[] = intval($row['zhnum']);
        }
    }
    include $this->template('web/core/data/member_data');
}
elseif($op == 'open'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_open_log')."where $condition AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit = $hit1 = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);
        }
    }

    include $this->template('web/core/data/open_data');
}
elseif($op == 'sms'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid and cid=2";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 1 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $listtwo = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 2 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['fnum'] = count($listtwo);
        $listthree = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 3 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['cnum'] = count($listthree);
        $listfour = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 4 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['znum'] = count($listfour);
        $listfive = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 5 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['rnum'] = count($listfive);
        $listsix = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 6 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['snum'] = count($listsix);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit1 = $hit2=$hit3 = $hit4 = $hit5 = $hit6 = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit1[] = intval($row['gnum']);
            $hit2[] = intval($row['fnum']);
            $hit3[] = intval($row['cnum']);
            $hit4[] = intval($row['znum']);
            $hit5[] = intval($row['rnum']);
            $hit6[] = intval($row['snum']);
        }
    }
    include $this->template('web/core/data/sms_data');
}
elseif($op == 'wechat'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid and cid=1";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 1 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $listthree = pdo_fetchall("select * from".tablename('xcommunity_send_log')."where $condition and type = 3 AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['cnum'] = count($listthree);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit1 = $hit3  = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit1[] = intval($row['gnum']);
            $hit3[] = intval($row['cnum']);

        }
    }

    include $this->template('web/core/data/wechat_data');
}
elseif($op == 'cost'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid and status=1";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_order')."where $condition and type = 'pfree' AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit1 = $hit3  = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);

        }
    }

    include $this->template('web/core/data/cost_data');
}
elseif($op == 'business'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid and status in(1,2,3)";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_order')."where $condition and type = 'business' AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit1 = $hit3  = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);

        }
    }

    include $this->template('web/core/data/business_data');
}
elseif($op == 'shop'){
    $xqday = $_GPC['xqday'] ? intval($_GPC['xqday']) : 7;
    $starttime = strtotime(date('Y-m-d')) - ($xqday - 2) * 86400 ;
    $endtime = strtotime(date('Y-m-d'))+86400 ;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j=0;
    $condition = "uniacid=:uniacid and status in(1,2,3)";
    $param[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if($regionid){
        $condition .= " and regionid=:regionid";
        $param[':regionid'] = $regionid;
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and regionid in({$user['regionid']})";
    }
    while($e >= $s){
        $param[':createtime'] = $e-86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from".tablename('xcommunity_order')."where $condition and type = 'shopping' AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $list[$j]['createtime'] =  $e-86400;
        $j++;
        $e = $e-86400;
    }
    $day = $hit1 = $hit3  = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);

        }
    }

    include $this->template('web/core/data/shop_data');
}