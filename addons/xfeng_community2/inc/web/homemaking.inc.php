<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区家政信息
 */
global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
load()->model('mc');
$categories = util::fetchall_category(1);
$options=array();
$options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
if ($op == 'list') {
    //删除
    if ($_W['ispost']) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_homemaking', array('id' => $id));
            }
            util::permlog('', '批量删除家政信息');
            itoast('删除成功', referer(), 'success', ture);
        }
    }
    $regions = model_region::region_fetall();
    //搜索
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition .= " AND t1.category = '{$_GPC['category']}'";
    }
    if ($_GPC['status'] != '') {
        $condition .= " AND t1.status = '{$_GPC['status']}'";
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);

    if (!empty($starttime) && $starttime == $endtime) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    } else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if ($_GPC['export'] == 1) {
        $sql = "select t5.title,t1.*,t4.realname,t4.mobile,t2.address from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id  left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where $condition order by t1.createtime desc ";
        $xqlist = pdo_fetchall($sql, $params);
        foreach ($xqlist as $k => $v) {
            $xqlist[$k]['addr'] = $v['title'] . $v['address'];
        }

        model_execl::export($xqlist, array(
            "title" => "家政服务数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '地址',
                    'field' => 'addr',
                    'width' => 25
                ),
                array(
                    'title' => '服务时间',
                    'field' => 'servicetime',
                    'width' => 20
                ),
                array(
                    'title' => '说明',
                    'field' => 'content',
                    'width' => 50
                ),
                array(
                    'title' => '申请时间',
                    'field' => 'cctime',
                    'width' => 12
                ),
            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where $condition order by t1.createtime desc ";

    $total = pdo_fetchcolumn($tsql, $params);

    $pager = pagination($total, $pindex, $psize);

    load()->func('tpl');
    include $this->template('web/plugin/homemaking/list');
}
elseif ($op == 'add') {
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
    }
    //编辑
    $sql = "select t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where t1.id=:id order by t1.createtime desc ";
    $item = pdo_fetch($sql, array(':id' => $id));

    if (empty($item)) {
        itoast('信息不存在或已删除', referer(), 'error', ture);
    }

    if ($_W['isajax']) {
        $data = array(
            'status' => $_GPC['status'],
            'remark' => $_GPC['remark']
        );
        pdo_update("xcommunity_homemaking", $data, array('id' => $id));
        if (set('p53')) {
            $content = $_GPC['status'] == 1 ? '您的家政服务已完成' : '您的家政服务已取消';
            util::app_send($item['uid'], $content);
        }
        util::permlog('', '修改家政服务状态' . '家政信息ID:' . $id);
        echo json_encode(array('status'=>1));exit();
    }

    include $this->template('web/plugin/homemaking/add');
}
elseif ($op == 'delete') {
    if ($id) {
        //删除
        if (pdo_delete("xcommunity_homemaking", array('id' => $id))) {
            util::permlog('家政信息-删除', '删除家政信息ID:' . $id);
            itoast('家政服务信息删除成功。', referer(), 'success', ture);
        }

    }

}
elseif ($op == 'category') {
    $p = in_array($_GPC['p'], array('list', 'add')) ? $_GPC['p'] : 'list';
    if ($p == 'add') {
        $regionids = '[]';
        $parentid = intval($_GPC['parentid']);
        //编辑分类信息
        if (!empty($id)) {
            $regions = model_region::region_fetall();
            $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE id=:id", array(':id' => $id));
            $regs = pdo_getall('xcommunity_category_region', array('cid' => $id), array('regionid'));
            $regionid =array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
        }
        //添加分类主ID
        if (!empty($parentid)) {
            $parent = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE id=:parentid", array(':parentid' => $parentid));
        }
        //提交
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            $data = array(
                'name' => $_GPC['catename'],
                'parentid' => 0,
                'displayorder' => $_GPC['displayorder'],
                'description' => $_GPC['description'],
                'enabled' => 1,
                'uniacid' => $_W['uniacid'],
                'type' => 1,
                'thumb' => tomedia($_GPC['thumb']),
                'url' => $_GPC['url'],
                'province' => $birth['province'],
                'city' => $birth['city'],
                'dist' => $birth['district'],
                'allregion' => $allregion
            );
            $cate = pdo_get('xcommunity_category',array('type'=>1,'name'=> $data['name'],'uniacid' => $_W['uniacid']),array('id'));
            if ($id){
                $item = pdo_get('xcommunity_category',array('id' => $id,'uniacid' => $_W['uniacid']),array('name'));
                if ($item['name'] != $_GPC['catename']){
                    if($cate){
                        echo json_encode(array('content'=>'分类已存在,请勿重复添加'));exit();
                    }
                }
            }else{
                if($cate){
                    echo json_encode(array('content'=>'分类已存在,请勿重复添加'));exit();
                }
            }
            if (empty($parentid)) {
                if (empty($id)) {
                    //添加主类
                    pdo_insert("xcommunity_category", $data);
                    $id = pdo_insertid();
                    util::permlog('分类-添加', '信息标题:' . $data['name']);
                } else {
                    //更新
                    $data['displayorder'] = $_GPC['displayorder'];
                    $data['name'] = $_GPC['catename'];
                    $data['description'] = $_GPC['description'];
                    pdo_update("xcommunity_category", $data, array('id' => $id));
                    pdo_delete('xcommunity_category_region', array('cid' => $id));
                    util::permlog('分类-修改', '信息标题:' . $data['name']);
                }
            } else {
                //添加子类
                if (empty($id)) {
                    $data['parentid'] = $parentid;
                    pdo_insert("xcommunity_category", $data);
                    $id = pdo_insertid();
                } else {
                    //更新子类
                    $data['parentid'] = $parentid;
                    $data['displayorder'] = $_GPC['displayorder'];
                    $data['name'] = $_GPC['catename'];
                    $data['description'] = $_GPC['description'];
                    pdo_update("xcommunity_category", $data, array('id' => $id));
                    pdo_delete('xcommunity_category_region', array('cid' => $id));
                }

            }
            if ($allregion == 1){
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v){
                    $dat = array(
                        'cid' => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_category_region', $dat);
                }
            }else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'cid' => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_category_region', $dat);
                }
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/homemaking/category_add');
    } elseif ($p == 'list') {
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $id => $displayorder) {
                pdo_update('xcommunity_category', array('displayorder' => $displayorder), array('id' => $id));
            }
            itoast('分类排序更新成功！', referer(), 'success', true);
        }
        //显示全部分类信息
        $sql = "select * from" . tablename("xcommunity_category") . "where parentid=0 AND uniacid =:uniacid and type=1 order by displayorder desc ";
        $category = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        include $this->template('web/plugin/homemaking/category_list');
    }


}
elseif ($op == 'del') {
    if ($id) {
        pdo_delete("xcommunity_category", array('id' => $id));
        itoast('删除成功。', referer(), 'success', ture);
    }

}
elseif ($op == 'manage') {
    $operation = in_array($_GPC['operation'], array('list', 'add', 'del')) ? $_GPC['operation'] : 'list';
    $companies = model_region::company_fetall();
    if ($operation == 'add') {
        //报修分类
        $regionids = '[]';
        if ($id) {
            $sql = "select t1.*,t2.realname,t3.title,t3.pid,t2.id as staffid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid= t2.id left join" . tablename('xcommunity_department') . 't3 on t2.departmentid=t3.id left join' . tablename('xcommunity_property') . "t4 on t4.id = t3.pid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error');
            }
            $regions = model_region::region_fetall();
            $regs = pdo_getall('xcommunity_notice_region', array('nid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }

            $regionids = json_encode($regionid);
            $xqcategories = pdo_getall('xcommunity_notice_category', array('nid' => $id), array('cid'));
            $cids = array();
            foreach ($xqcategories as $key => $v) {
                $cids[] = $v['cid'];
            }

        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $data = array(
                'uniacid' => $_W['uniacid'],
                'enable' => 3,
                'type' => intval($_GPC['type']),
                'province' => $birth['province'],
                'city' => $birth['city'],
                'dist' => $birth['district'],
            );
            if ($id) {
                pdo_update('xcommunity_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_notice_region', array('nid' => $id));
                pdo_delete('xcommunity_notice_category', array('nid' => $id));

                util::permlog('家政接收员-修改', '修改报修接收员ID:' . $id);
            } else {
                $data['staffid'] = $_GPC['userid'];
                $data['uid'] = $_W['uid'];
                if (pdo_insert('xcommunity_notice', $data)) {
                    $id = pdo_insertid();
                    util::permlog('家政接收员-添加', '添加报修接收员ID:' . $id);
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

            foreach ($_GPC['cid'] as $key => $value) {
                $d = array(
                    'nid' => $id,
                    'cid' => $value,
                );
                pdo_insert('xcommunity_notice_category', $d);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/homemaking/manage_add');
    } elseif ($operation == 'list') {
        $condition = " t1.uniacid='{$_W['uniacid']}' AND t1.enable = 3";
        if ($user['type'] == 2 || $user['type'] == 3) {
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }
        $sql = "select t1.*,t2.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where $condition";
        $list = pdo_fetchall($sql);
        include $this->template('web/plugin/homemaking/manage_list');
    } elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_notice', array('id' => $id));
            util::permlog('家政接收员-删除', '删除报修接收员ID:' . $id);
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
elseif ($op == 'edit') {
    $cid = intval($_GPC['id']);
    $item = pdo_get('xcommunity_homemaking_list', array('cid' => $cid), array());
    $piclist = '';
    if ($item['service']) {
        $piclist = explode(',', $item['service']);
    }
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $_W['uid'],
            'cid' => $cid,
            'price' => htmlspecialchars_decode($_GPC['price']),
            'content' => htmlspecialchars_decode($_GPC['content'])
        );
        if (@is_array($_GPC['service'])) {
            $data['service'] = implode(',', $_GPC['service']);
        }
        if ($item) {
            pdo_update('xcommunity_homemaking_list', $data, array('id' => $item['id']));
        } else {
            pdo_insert('xcommunity_homemaking_list', $data);
        }
        itoast('修改成功',$this->createWebUrl('homemaking',array('op'=> 'category')),'success');
    }

    include $this->template('web/plugin/homemaking/edit');
}
elseif ($op == 'set') {
    if(checksubmit('submit')){
        foreach ($_GPC['set'] as $key => $val){
            $sql = "select * from".tablename('xcommunity_setting')."where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            if($key =='p49'){
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
    include $this->template('web/plugin/homemaking/set');
}