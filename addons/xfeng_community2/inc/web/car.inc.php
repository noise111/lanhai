<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区拼车
 */


global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
load()->model('mc');
if ($op == 'list') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_carpool', array('id' => $id));
            }
            util::permlog('','批量删除拼车信息');
            itoast('删除成功', referer(), 'success',true);
        }
    }
    $condition = 'uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['type'])) {
        $condition .= " AND type = '{$_GPC['type']}'";
    }
    if ($user[type] == 3) {
        //小区管理员
        $condition .= " and regionid in({$user['regionid']})";
    } else {
        if ($_GPC['regionid']) {
            $condition .= " and regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if(checksubmit('plverity')){
        $xqlist = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_carpool') . "WHERE  $condition AND black = 0  ", $params);
        foreach ($xqlist as $k => $v){
            $enable = $v['enable'] == 1 ? 0 : 1;
            pdo_update('xcommunity_carpool',array('enable' => $enable),array('id'=>$v['id']));
        }
        util::permlog('','批量审核拼车商品');
        itoast('审核成功',referer(),'success',true);

    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_carpool') . "WHERE  $condition AND black = 0  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_carpool') . "WHERE $condition AND black = 0", $params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/plugin/car/list');
}
elseif ($op == 'delete') {
    if ($_W['isajax']) {
        if (empty($id)) {
            exit('缺少参数');
        }
        $item = pdo_get('xcommunity_carpool',array('id' => $id),array());
        if($item){
            $r = pdo_delete("xcommunity_carpool", array('id' => $id));
            util::permlog('小区拼车-删除','信息标题:'.$item['title']);
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }

    }
}
elseif ($op == 'toblack') {
    if ($id) {
        pdo_query("UPDATE " . tablename('xcommunity_carpool') . "SET black =1 WHERE id=:id", array(':id' => $id));
        util::permlog('','商品ID:'.$id.'加入黑名单');
        echo json_encode(array('state' => 1));
    }
}
elseif ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable'))) {
        $data = ($data == 0 ? '1' : '0');
        pdo_update("xcommunity_carpool", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        util::permlog('','商品ID:'.$id.'审核');
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
elseif ($op == 'set' || $op == 'desc') {
    if(checksubmit('submit')){
        foreach ($_GPC['set'] as $key => $val){
            $sql = "select * from".tablename('xcommunity_setting')."where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            if($key =='p49'){
                $val = htmlspecialchars_decode($val);
            }
            if($key =='p166'){
                $val = htmlspecialchars_decode($val);
            }
            $data = array(
                'xqkey' => $key,
                'value' => $val,
                'uniacid' => $_W['uniacid']
            );
            if($item){
                pdo_update('xcommunity_setting',$data,array('id' => $item['id'],'uniacid' => $_W['uniacid']));
            }else{
                pdo_insert('xcommunity_setting',$data);
            }
        }
        itoast('操作成功',referer(),'success',ture);
    }
    $set = pdo_getall('xcommunity_setting',array('uniacid' => $_W['uniacid']),array(),'xqkey',array());
    include $this->template('web/plugin/car/set');
}
elseif ($op == 'black') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $condition = '';
        if ($user[type] == 3) {
            //普通管理员
            $condition .= " and r.id in({$user['regionid']})";
        }
        $list = pdo_fetchall("SELECT f.*,r.title,t3.mobile FROM" . tablename('xcommunity_carpool') . "as f left join " . tablename('xcommunity_region') . "as r on f.regionid = r.id left join".tablename('mc_members')."t3 on t3.uid = f.uid WHERE f.uniacid='{$_W['uniacid']}' AND f.black = 1 $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_carpool') . "as f left join " . tablename('xcommunity_region') . "as r on f.regionid = r.id left join".tablename('mc_members')."t3 on t3.uid = f.uid WHERE f.uniacid='{$_W['uniacid']}' AND f.black = 1 $condition ");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/car/black');
    }
    elseif ($p == 'delete') {
        if ($_W['isajax']) {
            if (empty($id)) {
                exit('缺少参数');
            }
            $r = pdo_delete('xcommunity_carpool', array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($p == 'toblack') {
        if ($id) {
            pdo_query("UPDATE " . tablename('xcommunity_carpool') . "SET black =0 WHERE id=:id", array(':id' => $id));
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}