<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台幻灯
 */
global $_W, $_GPC;
$ops = array('list', 'add', 'delete');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 首页幻灯的列表
 */
if ($op == 'list') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_slide', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('排序更新成功！', 'refresh', 'success',ture);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid=:uniacid and type= 1';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if ($user[type] == 2 || $user[type] == 3) {
        $condition .= " and uid = {$_W['uid']}";
    }
    if(intval($_GPC['regionid'])){
        $condition .=" and t2.regionid=:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    //显示所有小区
    $regions = model_region::region_fetall();
    $sql = "select t1.* from".tablename('xcommunity_slide')."t1 left join".tablename('xcommunity_slide_region')."t2 on t1.id=t2.sid where $condition group by t1.id  ORDER BY t1.displayorder DESC, t1.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from".tablename('xcommunity_slide')."t1 left join".tablename('xcommunity_slide_region')."t2 on t1.id=t2.sid where $condition ";
    $total = pdo_fetchcolumn($tsql,$params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/slide/list');
}
/**
 * 首页幻灯的添加修改
 */
if ($op == 'add') {
    $id = intval($_GPC['id']);
    $regionids = '[]';
    if (!empty($id)) {
        $regions = model_region::region_fetall();
        $item = pdo_fetch("SELECT * FROM " . tablename("xcommunity_slide") . " WHERE id = :id", array(':id' => $id));
        $starttime =  $item['starttime'];
        $endtime = $item['endtime'];
        if (empty($item)) {
            itoast('抱歉，广告不存在或是已经删除！', '', 'error');
        }
        $regs = pdo_getall('xcommunity_slide_region', array('sid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);
    }
//    if ($_W['ispost']) {
    if ($_W['isajax']) {
        if (empty($_GPC['title'])) {
            itoast('标题不能为空，请输入标题！');
        }
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        $starttime = strtotime($birth['start']);
        $endtime = strtotime($birth['end']);
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $startdate = strtotime($_GPC['sale']['start']);
        $enddate = strtotime($_GPC['sale']['end']);
        if (!empty($startdate) && $startdate == $enddate) {
            $enddate = $enddate + 86400 - 1;
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'url' => $_GPC['url'],
            'displayorder' => intval($_GPC['displayorder']),
            'type' => 1,
            'status' => intval($_GPC['status']),
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'createtime' => TIMESTAMP,
            'allregion' => $allregion,
            'starttime' => $starttime,
            'endtime' => $endtime,
        );
        if (!empty($_GPC['thumb'])) {
            $data['thumb'] = $_GPC['thumb'];
            load()->func('file');
            file_delete($_GPC['thumb-old']);
        }
        if (empty($id)) {
            $data['uid'] = $_W['uid'];
            pdo_insert("xcommunity_slide", $data);
            $id = pdo_insertid();
            util::permlog('幻灯片-添加','信息标题:'.$data['title']).'信息ID'.$idd;
        } else {
            pdo_update("xcommunity_slide", $data, array('id' => $id));
            pdo_delete('xcommunity_slide_region', array('sid' => $id));
            util::permlog('幻灯片-修改','信息标题:'.$data['title'].'信息ID'.$id);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'sid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_slide_region', $dat);
            }
        }else {
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'sid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_slide_region', $dat);
            }
        }
        echo json_encode(array('status'=>1));exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/core/slide/add');
}
/**
 * 首页幻灯的删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id, thumb,title FROM " . tablename("xcommunity_slide") . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        itoast('抱歉，信息不存在或是已经被删除！');
    }
    if (!empty($row['thumb'])) {
        load()->func('file');
        file_delete($row['thumb']);
    }
    if (pdo_delete("xcommunity_slide", array('id' => $id))) {
        util::permlog('幻灯片-删除','信息标题:'.$row['title']);
        if (pdo_delete('xcommunity_slide_region', array('sid' => $id))) {
            itoast('删除成功', referer(), 'success',ture);
        }
    }
    itoast('删除成功！', referer(), 'success',ture);
}
	