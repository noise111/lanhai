<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区常用查询
 */
global $_W, $_GPC;
$ops = array('add', 'delete', 'list', 'set');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 便民查询的添加
 */
if ($op == 'add') {
    $regionids = '[]';
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_search') . "WHERE id=:id", array(':id' => $id));
        if (empty($item)) {
            itoast('信息不存在或已被删除', referer(), 'error',ture);
        }
        $regs = pdo_getall('xcommunity_search_region', array('sid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);
    }
    if ($_W['isajax']) {
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        if ($allregion == 1){

        }else{
            if(empty($birth['province'])){
                echo json_encode(array('content'=>'必须选择省市区和小区'));exit();
            }
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'sname' => $_GPC['sname'],
            'surl' => $_GPC['surl'],
            'icon' => $_GPC['icon'],
            'status' => $_GPC['status'],
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'allregion' => $allregion
        );
        if (empty($_GPC['id'])) {
            $data['uid'] = $_W['uid'];
            pdo_insert("xcommunity_search", $data);
            $id = pdo_insertid();
            util::permlog('便民查询-添加','信息标题:'.$_GPC['sname']);
        } else {
            pdo_update("xcommunity_search", $data, array('id' => $id));
            pdo_delete('xcommunity_search_region', array('sid' => $id));
            util::permlog('便民查询-修改','信息标题:'.$_GPC['sname']);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'sid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_search_region', $dat);
            }
        }else {
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'sid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_search_region', $dat);
            }
        }
        echo json_encode(array('status'=>1));exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/plugin/search/add');
}
/**
 * 便民查询的列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " uniacid={$_W['uniacid']} ";
    if ($user['type'] == 3 || $user[type] == 2) {
        $condition .= " AND uid = {$_W['uid']}";
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_search') . "WHERE $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_search') . "WHERE $condition");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/search/list');
}
/**
 * 便民查询的删除
 */
if ($op == 'delete') {
    if ($id) {
        $item =pdo_get('xcommunity_search',array('id'=> $id),array());
        if($item){
            if (pdo_delete("xcommunity_search", array('id' => $id))) {
                util::permlog('便民查询-删除','信息标题:'.$_GPC['sname']);
                pdo_delete('xcommunity_search_region', array('phoneid' => $id));
                    itoast('删除成功', referer(), 'success',ture);

            }
        }

    }
}
/**
 * 便民查询的状态修改
 */
if ($op == 'set') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
    }
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);

    if (in_array($type, array('status'))) {
        $data = ($data == 1 ? '0' : '1');
        pdo_update("xcommunity_search", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
    die(json_encode(array("result" => 0)));
}