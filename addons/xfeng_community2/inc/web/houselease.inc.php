<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区租赁信息
 */

global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
if ($op == 'list') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_houselease', array('id' => $id));
                util::permlog('租赁信息-删除', '删除租赁信息ID:' . $id);
            }
            itoast('删除成功', referer(), 'success', ture);
        }
    }
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
        //小区管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if ($_GPC['export'] == 1 || checksubmit('plverity')) {
        $sql = "select t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid where $condition order by t1.createtime desc ";
        $xqlist = pdo_fetchall($sql, $params);
        if (checksubmit('plverity')) {
            foreach ($xqlist as $k => $v) {
                $enable = $v['enable'] == 1 ? 0 : 1;
                pdo_update('xcommunity_houselease', array('enable' => $enable), array('id' => $v['id']));
            }
            itoast('审核成功', referer(), 'success', ture);
            util::permlog('', '批量审核租赁信息');
        }
        if ($_GPC['export'] == 1) {
            foreach ($xqlist as $k => $v) {
                $xqlist[$k]['s'] = $v['status'] == 1 ? '已成交' : '未成交';
                $xqlist[$k]['cctime'] = date('Y-m-d H:i', $v['createtime']);
            }
            model_execl::export($xqlist, array(
                "title"   => "租赁服务数据-" . date('Y-m-d-H-i', time()),
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
                        'title' => '标题',
                        'field' => 'title',
                        'width' => 20
                    ),
                    array(
                        'title' => '是否成交',
                        'field' => 's',
                        'width' => 12
                    ),
                    array(
                        'title' => '发布时间',
                        'field' => 'cctime',
                        'width' => 18
                    ),
                )
            ));
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select t1.*,t2.realname as t_realname,t2.mobile as t_mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);

    $tsql = "select count(*) from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid where $condition order by t1.createtime desc ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);

    load()->func('tpl');
    include $this->template('web/plugin/houselease/list');
}
elseif ($op == 'add') {
    //编辑
    if ($id) {
        $sql = "select t1.*,t2.realname as t_realname,t2.mobile as t_mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t2.uid = t1.uid where t1.id=:id ";
        $item = pdo_fetch($sql, array(':id' => $id));
        if($item['images']){
            $images = explode(',',$item['images']);
        }
    }
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/plugin/houselease/add');
}
elseif ($op == 'delete') {
    //删除
    if ($id) {
        if (pdo_delete("xcommunity_houselease", array('id' => $id))) {
            util::permlog('租赁信息-删除', '删除租赁信息ID:' . $id);
            itoast('房屋租赁信息删除成功。', referer(), 'success', ture);
        }
    }

}
elseif ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable'))) {
        $data = ($data == 0 ? '1' : '0');
        pdo_update("xcommunity_houselease", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
elseif ($op == 'edit') {
    $regions = model_region::region_fetall();
    $id = intval($_GPC['id']);
    if ($id) {
        $sql = "select * from" . tablename('xcommunity_houselease') . "where id=:id";
        $item = pdo_fetch($sql, array('id' => $id));
        $house_model = explode(' ',$item['house_model']);
        preg_match('/\d+/',$house_model[0],$a1);
        $item['model_room'] = $a1[0];
        preg_match('/\d+/',$house_model[1],$a2);
        $item['model_hall'] = $a2[0];
        preg_match('/\d+/',$house_model[0],$a3);
        $item['model_toilet'] = $a3[0];
        $house_floor = explode(' ',$item['house_floor']);
        preg_match('/\d+/',$house_floor[0],$b1);
        $item['floor_layer'] = $b1[0];
        preg_match('/\d+/',$house_floor[1],$b2);
        $item['floor_number'] = $b2[0];
        $checktime = strtotime($item['checktime']);
        $images = pdo_fetchall("select src from" . tablename('xcommunity_images') . "t1 left join" . tablename('xcommunity_houselease_images') . "t2 on t1.id = t2.thumbid where t2.houseid=:houseid", array(':houseid' => $item['id']));
        foreach ($images as $key => $val) {
            $piclist .= $val['src'] . ',';
        }
        $piclist = xtrim($piclist);
        $piclist = explode(',', $piclist);
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid'      => $_W['uniacid'],
            'regionid'     => intval($_GPC['regionid']),
            'category'     => intval($_GPC['category']),
            'way'          => $_GPC['way'],
            'model_room'   => $_GPC['model_room'],
            'model_hall'   => $_GPC['model_hall'],
            'model_toilet' => $_GPC['model_toilet'],
            'model_area'   => $_GPC['model_area'],
            'floor_layer'  => $_GPC['floor_layer'],
            'floor_number' => $_GPC['floor_number'],
            'fitment'      => $_GPC['fitment'],
            'house'        => $_GPC['house'],
            'allocation'   => implode(',', $_GPC['allocation']),
            'price_way'    => $_GPC['price_way'],
            'price'        => $_GPC['price'],
            'checktime'    => $_GPC['checktime'],
            'title'        => $_GPC['title'],
            'content'      => htmlspecialchars_decode($_GPC['content']),
            'displayorder' => intval($_GPC['displayorder']),
            'realname'     => $_GPC['realname'],
            'mobile'       => $_GPC['mobile'],
            'createtime'   => TIMESTAMP,
            'house_floor'  => $_GPC['floor_layer'] . '层 共' . $_GPC['floor_number'] . '层',
            'house_model'  => $_GPC['model_room'] . '室' . $_GPC['model_hall'] . '厅' . $_GPC['model_toilet'] . '卫',
            'house_aspect' => $_GPC['house_aspect']
        );

        if ($_GPC['thumbs']) {
            $data['images'] = implode(',', $_GPC['thumbs']);
        }
        if (empty($id)) {
            pdo_insert('xcommunity_houselease', $data);
            $houseid = pdo_insertid();
            util::permlog('租赁信息-添加', '添加租赁信息ID:' . $houseid);
        }
        else {
            pdo_update('xcommunity_houselease', $data, array('id' => $id));
            pdo_delete('xcommunity_houselease_images', array('houseid' => $id));
            $houseid = $id;
            util::permlog('租赁信息-修改', '修改租赁信息ID:' . $houseid);
        }
        if($_GPC['thumbs']) {
            foreach ($_GPC['thumbs'] as $key => $item) {
                pdo_insert('xcommunity_images', array('src' => $item));
                $thumbid = pdo_insertid();
                $dat = array(
                    'houseid' => $houseid,
                    'thumbid' => $thumbid,
                );
                pdo_insert('xcommunity_houselease_images', $dat);
            }
        }
        echo json_encode(array('status'=>1));exit();
    }

    include $this->template('web/plugin/houselease/edit');
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
    include $this->template('web/plugin/houselease/set');
}