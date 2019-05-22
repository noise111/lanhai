<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/19 下午8:03
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('build', 'unit', 'room','category')) ? trim($_GPC['op']) : 'build';
if ($op == 'build') {
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $builds = pdo_getall('xcommunity_build', array('regionid' => $regionid), array('buildtitle', 'id','areaid'));
        foreach($builds as $k => $v){
            if (set('p55')){
                if (set('p36')){
                    $area = set('p37');
                }
            }else{
                if (set('x17',$regionid)){
                    $area = set('x46',$regionid);
                }
            }
            if ($v['areaid']){
                $areatitle = pdo_getcolumn('xcommunity_area',array('id' => $v['areaid']),'title');
                $builds[$k]['buildtitle'] = $areatitle.$area.$v['buildtitle'];
                if ($areatitle){
                    $builds[$k]['buildtitle'] = $areatitle.$area.$v['buildtitle'];
                }
            }
        }
        echo json_encode($builds);
        exit();
    }
}
elseif ($op == 'unit') {
    $buildid = $_GPC['buildid'];
    if ($buildid) {
        $units = pdo_getall('xcommunity_unit', array('buildid' => $buildid), array('unit', 'id'));
        echo json_encode($units);
        exit();
    }
}
elseif ($op == 'room') {
    $build = $_GPC['build'];
    $unit = $_GPC['unit'];
    $regionid = intval($_GPC['regionid']);
    $condition = '';
    if ($regionid) {
        $condition .= "regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    if ($build) {
        $condition .= " and buildid=:build";
        $params[':build'] = $build;
    }
    if ($unit) {
        $condition .= " and unitid=:unit";
        $params[':unit'] = $unit;
    }
    $sql = "select * from".tablename('xcommunity_member_room')."where $condition";
    $rooms = pdo_fetchall($sql,$params);
    echo json_encode($rooms);exit();
}elseif ($op == 'category') {
    $regionid = intval($_GPC['regionid']);
    $type = intval($_GPC['type']);
    if ($regionid) {
        $units = pdo_getall('xcommunity_balance_category', array('type' => $type,'regionid'=>$regionid), array('category', 'id'));
        echo json_encode($units);
        exit();
    }
}