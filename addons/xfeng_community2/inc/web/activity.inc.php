<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区活动
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'add') {
    $regionids = '[]';
    if (!empty($id)) {
        $regions = model_region::region_fetall();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_activity') . "WHERE id=:id", array(':id' => $id));
        $starttime = $item['starttime'];
        $endtime = $item['endtime'];
        $enddate = $item['enddate'];
        $regs = pdo_getall('xcommunity_activity_region', array('activityid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);

    }
    if ($_W['isajax']) {
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
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
            'title' => $_GPC['title'],
            'starttime' => $starttime,
            'endtime' => $endtime,
            'enddate' => strtotime($_GPC['enddate'])+ 86400 - 1,
            'picurl' => $_GPC['picurl'],
            'number' => !empty($_GPC['number']) ? $_GPC['number'] : '1',
            'content' => htmlspecialchars_decode($_GPC['content']),
            'status' => $_GPC['status'],
            'createtime' => TIMESTAMP,
            'price' => $_GPC['price'],
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'num' => trim(intval($_GPC['num'])),
            'allregion' => $allregion
        );
        if (empty($_GPC['id'])) {
            $data['uid'] = $_W['uid'];
            pdo_insert('xcommunity_activity', $data);
            $id = pdo_insertid();
            util::permlog('小区活动-添加','信息标题:'.$data['title']);
        } else {
            pdo_update('xcommunity_activity', $data, array('id' => $id));
            pdo_delete('xcommunity_activity_region', array('activityid' => $id));
            util::permlog('小区活动-修改','信息标题:'.$data['title']);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'activityid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_activity_region', $dat);
            }
        }else{
            $regionids = explode(',',$_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'activityid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_activity_region', $dat);
            }
        }
        if(set('p53')){
            $regionids = implode(',',$_GPC['regionid']);
            $sql = "select * from".tablename('xcommunity_member')."where regionid in({$regionids}) group by uid";
            $users = pdo_fetchall($sql);
            foreach ($users as $key => $val){
                util::app_send($val['uid'],$data['title']);
            }

        }
        echo json_encode(array('status'=>1));exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/plugin/activity/add');
}
elseif ($op == 'list') {
    //批量删除
    if (checksubmit('delete')) {
        $ids = $_GPC['id'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_activity', array('id' => $id));
            }
            util::permlog('','批量删除小区活动信息');
            itoast('删除成功', referer(), 'success');
        }
    }
    // AJAX是否置顶
    if ($_W['isajax'] && $_GPC['id']) {
        $data = array();
        $data['status'] = intval($_GPC['status']);
        if (pdo_update('xcommunity_activity', $data, array('id' => $id)) !== false) {
            util::permlog('小区活动-置顶','置顶商品ID:'.$id);
            exit('success');
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 'uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    if ($user&&$user[type] != 1) {
        //普通管理员
        $condition .= " AND uid=:uid";
        $params[':uid'] = $_W['uid'];
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_activity') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_activity') . "WHERE $condition",$params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/activity/list');
}
elseif ($op == 'delete') {
    if ($id) {
        $item = pdo_get('xcommunity_activity',array('id'=>$id),array());
        if($item){
            if (pdo_delete('xcommunity_activity', array('id' => $id))) {
                util::permlog('小区活动-删除','信息标题:'.$item['title']);
                if (pdo_delete('xcommunity_activity_region', array('activityid' => $id))) {
                    itoast('删除成功', referer(), 'success');
                }
            }
        }

    }
}
elseif ($op == 'order') {
    $aid = intval($_GPC['id']);
    if (checksubmit('delete')) {
        if($_GPC['ids']){
            $ids = implode("','", $_GPC['ids']);
        }

        pdo_delete('xcommunity_res', " id  IN  ('" . $ids . "')");
        util::permlog('','批量删除活动预约订单');
        itoast('删除成功！', referer(), 'success');
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.aid=:activityid";
    $params[':activityid'] = intval($_GPC['id']);
    if($_GPC['keyword']){
        $condition .=" and (t3.mobile=:keyword or t3.realname=:keyword)";
        $params[':keyword'] = $_GPC['keyword'];
    }
    //导出用户
    if (checksubmit('export')) {
        $ttsql = "select t1.*,t2.title,t3.realname,t3.mobile as mmobile,t2.price from".tablename('xcommunity_res')."t1 left join".tablename('xcommunity_activity')."t2 on t2.id = t1.aid left join".tablename('mc_members')."t3 on t3.uid = t1.uid where $condition ";
        $ttlist = pdo_fetchall($ttsql,$params);
        foreach ($ttlist as $k => $v){
            $ttlist[$k]['s'] = $v['status'] ? '已支付' : '未支付';
            $ttlist[$k]['cctime'] = date('Y-m-d H:i',$v['createtime']);
        }
        model_execl::export($ttlist, array(
            "title" => "用户数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '活动标题',
                    'field' => 'title',
                    'width' => 16
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 14
                ),
                array(
                    'title' => '电话',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '人数',
                    'field' => 'num',
                    'width' => 12
                ),
                array(
                    'title' => '报名时间',
                    'field' => 'cctime',
                    'width' => 12
                ),
                array(
                    'title' => '支付状态',
                    'field' => 's',
                    'width' => 12
                ),

            )
        ));

    }
    $sql = "select t1.*,t2.title,t3.realname,t3.mobile as mmobile,t2.price from".tablename('xcommunity_res')."t1 left join".tablename('xcommunity_activity')."t2 on t2.id = t1.aid left join".tablename('mc_members')."t3 on t3.uid = t1.uid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $tsql = "select count(*) from".tablename('xcommunity_res')."t1 left join".tablename('xcommunity_activity')."t2 on t2.id = t1.aid left join".tablename('mc_members')."t3 on t3.uid = t1.uid where $condition ";
    $total = pdo_fetchcolumn($tsql,$params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/plugin/activity/order');
}
elseif($op == 'manage'){
    $p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
    $d='';
    if ($user) {
        if ($user['type'] == 2) {
            $d = " and uid ={$_W['uid']}";
        }
        elseif ($user['type'] == 3) {
            $d = " and id={$user['pid']}";
        }
    }
    $properties = model_region::property_fetall($d);
    if ($p == 'list'){
        $condition = " t1.uniacid='{$_W['uniacid']}' AND t1.enable = 5";
        if ($user['type'] == 2 || $user['type'] == 3) {
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }
        $sql = "select t1.*,t2.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where $condition";
        $list = pdo_fetchall($sql);
        include $this->template('web/plugin/activity/manage_list');
    }
    elseif ($p == 'add'){
        $regionids = '[]';
        if ($id) {
            $sql = "select t1.*,t2.realname,t3.title,t3.pid,t2.id as staffid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid= t2.id left join" . tablename('xcommunity_department') . 't3 on t2.departmentid=t3.id left join' . tablename('xcommunity_property') . "t4 on t4.id = t3.pid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error', ture);
            }
            $regions = model_region::region_fetall();
            $regs = pdo_getall('xcommunity_notice_region', array('nid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'enable'   => 5,
                'type'     => intval($_GPC['type']),
                'province' => $birth['province'],
                'city'     => $birth['city'],
                'dist'     => $birth['district'],
                'staffid'  => intval($_GPC['staffid'])
            );
            if ($id) {
                pdo_update('xcommunity_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_notice_region', array('nid' => $id));
                util::permlog('', '活动接收员信息修改,信息ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                $staf = pdo_get('xcommunity_notice', array('staffid' => $data['staffid'], 'enable' => 5), array('id'));
                if ($staf) {
                    echo json_encode(array('content'=>'接收员已添加'));exit();
                }
                if (pdo_insert('xcommunity_notice', $data)) {
                    $id = pdo_insertid();
                    util::permlog('', '活动接收员信息添加,信息ID:' . $id);
                }
            }
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $k => $val) {
                $dat = array(
                    'nid' => $id,
                    'regionid' => $val,
                );
                pdo_insert('xcommunity_notice_region', $dat);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/activity/manage_add');
    }
    elseif ($p == 'del'){
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_notice', array('id' => $id));
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