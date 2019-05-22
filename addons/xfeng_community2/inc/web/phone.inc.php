<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区电话信息
 */
global $_GPC, $_W;
$id = intval($_GPC['id']);
$ops = array('add', 'delete', 'list', 'post', 'apply');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
/**
 * 便民号码的列表
 */
if ($op == 'list') {
    //批量删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_phone', array('id' => $id));
            }
            util::permlog('', '批量删除便民号码');
            itoast('删除成功', referer(), 'success', ture);
        }
    }
    //排序
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_phone', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('排序更新成功！', 'refresh', 'success', ture);
    }
    //常用号码显示
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = '';
    if ($user && $user[type] != 1) {
        $condition .= " AND uid='{$_W['uid']}'";
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND content LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }


    $sql = "select * from " . tablename("xcommunity_phone") . "where uniacid = '{$_W['uniacid']}' and status=1 $condition order by displayorder asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_phone") . "where  uniacid = '{$_W['uniacid']}'  and status=1 $condition ", $params);
    $pager = pagination($total, $pindex, $psize);


    include $this->template('web/plugin/phone/list');
}
/**
 * 便民号码的删除
 */
if ($op == 'delete') {
    //常用号码删除
    if ($id) {
        $item = pdo_get('xcommunity_phone', array('id' => $id), array());
        if ($item) {
            if (pdo_delete("xcommunity_phone", array('id' => $id))) {
                util::permlog('便民号码-删除', '删除号码:' . $item['content']);
                pdo_delete('xcommunity_phone_region', array('phoneid' => $id));
                itoast('删除成功', referer(), 'success', ture);
            }
        }

    }
}
/**
 * 便民号码的添加
 */
if ($op == 'add') {
    $categories = util::fetchall_category(8);
    $regionids = '[]';
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_phone') . "WHERE id=:id", array(':id' => $id));
        $regs = pdo_getall('xcommunity_phone_region', array('phoneid' => $id), array('regionid'));
        $regionid = array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);
    }
    if ($_W['isajax']) {
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        if ($allregion == 1) {

        } else {
            if (empty($birth['province'])) {
                echo json_encode(array('content' => '必须选择省市区和小区'));
                exit();
            }
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'phone' => $_GPC['phone'],
            'content' => $_GPC['content'],
            'displayorder' => intval($_GPC['displayorder']),
            'thumb' => $_GPC['thumb'],
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'address' => trim($_GPC['address']),
            'introduction' => trim($_GPC['introduction']),
            'cid' => intval($_GPC['cid']),
            'status' => 1,
            'allregion' => $allregion
        );
        if ($id) {
            pdo_update('xcommunity_phone', $data, array('id' => $id));
            pdo_delete('xcommunity_phone_region', array('phoneid' => $id));
            util::permlog('便民号码-修改', '修改号码ID:' . $id);
        } else {
            $data['uid'] = $_W['uid'];
            pdo_insert('xcommunity_phone', $data);
            $id = pdo_insertid();
            util::permlog('便民号码-添加', '添加号码ID:' . $id);
        }
        if ($allregion == 1) {
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v) {
                $dat = array(
                    'phoneid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_phone_region', $dat);
            }
        } else {
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'phoneid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_phone_region', $dat);
            }
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/plugin/phone/add');
}
/**
 * 便民号码的导入
 */
if ($op == 'post') {
    $regionids = '[]';
    if (checksubmit('submit')) {
        $rows = model_execl::import('phone');
        if ($rows[0][0] != '排序' || $rows[0][1] != '号码' || $rows[0][2] != '图片路径' || $rows[0][3] != '描述' || $rows[0][4] != '分类') {
            itoast('文件内容不符，请重新上传', referer(), 'error', ture);
        }
        foreach ($rows as $rownum => $col) {
            if ($rownum > 0) {
                if ($col[1]) {
                    if ($col[4]) {
                        $cid = pdo_getcolumn('xcommunity_category', array('name' => $col[4]), 'id');
                        if (empty($cid)) {
                            $dat = array(
                                'name' => $col[4],
                                'parentid' => 0,
                                'enabled' => 1,
                                'uniacid' => $_W['uniacid'],
                                'type' => 8,
                            );
                            pdo_insert("xcommunity_category", $dat);
                            $cid = pdo_insertid();
                        }
                    }
                    $data = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $_W['uid'],
                        'displayorder' => $col[0],
                        'phone' => $col[1],
                        'thumb' => $col[2],
                        'content' => $col[3],
                        'cid' => $cid
                    );
                    pdo_insert('xcommunity_phone', $data);
                    $id = pdo_insertid();
                    $regionids = explode(',', $_GPC['regionids']);
                    foreach ($regionids as $key => $value) {
                        $dat = array(
                            'phoneid' => $id,
                            'regionid' => $value,
                        );
                        pdo_insert('xcommunity_phone_region', $dat);
                    }
                }
            }
        }
        itoast('导入成功', referer(), 'success', ture);
        util::permlog('', '导入便民号码信息');
    }
    include $this->template('web/plugin/phone/post');
}
/**
 * 便民号码的申请
 */
if ($op == 'apply') {
    $p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
    /**
     * 便民号码申请的列表
     */
    if ($p == 'list') {
        if (checksubmit('delete')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_phone', array('id' => $id));
                }
                util::permlog('', '批量删除便民号码');
                itoast('删除成功', referer(), 'success', ture);
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename("xcommunity_phone") . "where uniacid ={$_W['uniacid']} and status=2 order by displayorder asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_phone") . "where  uniacid ={$_W['uniacid']} and status=2 ");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/phone/apply');
    }
    /**
     * 便民号码申请审核
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_phone') . "WHERE id=:id", array(':id' => $id));
            if (!empty($item)) {
                pdo_update('xcommunity_phone', array('status' => 1), array('id' => $id));
                pdo_delete('xcommunity_phone_region', array('phoneid' => $id));
                foreach ($regions as $k => $v) {
                    $dat = array(
                        'phoneid' => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_phone_region', $dat);
                }
                itoast('审核成功', referer(), 'success', ture);
            }
        }
    }
    /**
     * 便民号码申请的查看
     */
    if ($p == 'detail') {
        if ($id) {
            $item = pdo_fetch("SELECT t1.*,t2.name FROM" . tablename('xcommunity_phone') . "t1 left join".tablename('xcommunity_category')."t2 on t1.cid=t2.id WHERE t1.id=:id and t2.type=8", array(':id' => $id));
        }else{
            message('该信息不存在！');
        }
        include $this->template('web/plugin/phone/apply_detail');
    }
}