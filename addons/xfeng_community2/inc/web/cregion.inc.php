<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/28 下午8:51
 */
global $_GPC, $_W;
if ($_W['isajax']) {
    $condition ='';
    if ($_GPC['province']) {
        $condition .= " and province='{$_GPC['province']}' ";
    }
    if ($_GPC['city']) {
        $condition .= " and city='{$_GPC['city']}'";
    }
    if ($_GPC['dist']) {
        $condition .= " and dist='{$_GPC['dist']}'";
    }
    //判断是否是操作员
    $user = util::xquser($_W['uid']);
    if (($user['type'] == 3 || $user['type'] == 4)&&$_W['uid']!=1) {
        $condition .= " and id in({$user['regionid']})";
    }
    if($_GPC['province'] || $_GPC['city'] || $_GPC['dist']){
        $regions = model_region::region_fetall($condition);
        echo json_encode($regions);
    }
}