<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/12 下午3:21
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('add', 'list', 'del', 'bind','record','import')) ? trim($_GPC['op']) : 'list';
$regions = model_region::region_fetall();
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'list') {
    if ($_W['isajax']) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'place_num' => trim($_GPC['place_num']),
            'area' => trim($_GPC['area']),
            'remark' => trim($_GPC['remark']),
            'status' => $_GPC['status'],
        );
        if (pdo_insert('xcommunity_parking', $data)) {
            echo json_encode(array('status'=>1));exit();
        }
    }
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_parking', array('id' => $id));
                pdo_delete('xcommunity_parking_record',array('parkingid' => $id));
            }
            util::permlog('', '批量删除车位');
            itoast('删除成功', referer(), 'success');
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    if (intval($_GPC['regionid'])){
        $condition .= " and t1.regionid ={$_GPC['regionid']}";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "select t1.*,t2.title from" . tablename('xcommunity_parking') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id  where $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_parking') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id  where $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/parking/list');
} elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_parking', array('id' => $id), array());
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'place_num' => trim($_GPC['place_num']),
            'area' => trim($_GPC['area']),
            'remark' => trim($_GPC['remark']),
            'status' => $_GPC['status']
        );
        if ($id) {
            pdo_update('xcommunity_parking', $data, array('id' => $id));
        } else {
            pdo_insert('xcommunity_parking', $data);
        }
        echo json_encode(array('status'=>1));exit();
    }
    include $this->template('web/core/parking/add');
} elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_parking', array('id' => $id), array());
    }
    if ($item) {
        if (pdo_delete('xcommunity_parking', array('id' => $id))) {
            pdo_delete('xcommunity_parking_record',array('parkingid' => $id));
            itoast('删除成功', referer(), 'success');
        }
    }
} elseif ($op == 'bind') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("select t1.place_num,t1.id,t1.regionid,t2.realname,t2.mobile,t2.xqcarid,t2.starttime,t2.endtime,t2.price,t2.cycle,t2.remark from".tablename('xcommunity_parking')."t1 left join".tablename('xcommunity_parking_record')."t2 on t1.id=t2.parkingid where t1.id=:id",array(':id' => $id));
        $starttime = $item['starttime'];
        $endtime = $item['endtime'];
        $xqcars = pdo_getall('xcommunity_xqcars', array('regionid' => $item['regionid']), array());
    }
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'parkingid' => intval($_GPC['parkingid']),
            'realname' => trim($_GPC['realname']),
            'mobile' => trim($_GPC['mobile']),
            'xqcarid' => intval($_GPC['xqcarid']),
            'starttime' => strtotime($_GPC['starttime']) + 86400 - 1,
            'endtime' => strtotime($_GPC['endtime']) + 86400 - 1,
            'price' => trim($_GPC['price']),
            'cycle' => intval($_GPC['cycle']),
            'remark' => trim($_GPC['remark']),
            'createtime' => TIMESTAMP
        );
        if(pdo_insert('xcommunity_parking_record',$data)){
            itoast('提交成功',$this->createWebUrl('parking'),'success');
        }
    }
    include $this->template('web/core/parking/bind');
}elseif($op =='record'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $id = intval($_GPC['id']);
    $condition = " t1.uniacid=:uniacid and t1.parkingid = :parkingid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':parkingid'] = $id;
    $sql = "select t1.*,t2.car_num from".tablename('xcommunity_parking_record')."t1 left join".tablename('xcommunity_xqcars')."t2 on t1.xqcarid = t2.id where  $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from".tablename('xcommunity_parking_record')."t1 left join".tablename('xcommunity_xqcars')."t2 on t1.xqcarid = t2.id where $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/parking/record');
}elseif($op =='import'){
    $condition = '';
    if ($user[type] == 3) {
        $condition = "and id in ({$user['regionid']})";
    }
    $regions = model_region::region_fetall($condition);
    if ($_W['isajax']) {

        $rows = model_execl::import('parking');
        if ($rows[0][0] != '车位号（必填）'){
            echo json_encode(array('content' => '文件内容不符，请重新上传'));exit();
        }
        foreach ($rows as $rownum => $col) {
            if ($rownum > 0) {
                if ($col[0]) {
                    $data = array(
                        'place_num'  => trim($col[0]),
                        'area'       => trim($col[1]),
                        'status'     => trim($col[2]),
                        'remark'     => $col[10],
                        'createtime' => TIMESTAMP,
                        'regionid'   => intval($_GPC['regionid']),
                        'uniacid'    => $_W['uniacid'],
                    );
                    pdo_insert('xcommunity_parking', $data);
                    $parkid = pdo_insertid();
                    $dat = array(
                        'uniacid' => $_W['uniacid'],
                        'parkingid' => $parkid,
                        'realname' => trim($col[3]),
                        'mobile' => trim($col[4]),
                        'starttime' => strtotime(trim($col[5])),
                        'endtime' => strtotime(trim($col[6])),
                        'price' => trim($col[7]),
                        'cycle' => trim($col[8]),
                        'remark' => trim($col[10]),
                        'createtime' => TIMESTAMP
                    );
                    if (trim($col[9])){
                        $xqcarid = pdo_getcolumn('xcommunity_xqcars',array('car_num' => trim($col[9])),'id');
                        if ($xqcarid){
                            $dat['xqcarid'] = $xqcarid;
                        }else{
                            $da = array(
                                'realname'  => trim($col[3]),
                                'mobile'       => trim($col[4]),
                                'car_num'     => trim($col[9]),
                                'createtime' => TIMESTAMP,
                                'regionid'   => intval($_GPC['regionid']),
                                'uniacid'    => $_W['uniacid'],
                                'parkingid' => $parkid
                            );
                            pdo_insert('xcommunity_xqcars',$da);
                            $dat['xqcarid'] = pdo_insertid();
                        }
                        pdo_insert('xcommunity_parking_record',$dat);
                    }
                    util::permlog('车位管理-添加', '添加车位信息ID:' . $parkid);
                }
            }
        }
        util::permlog('', '导入车位信息');
        echo json_encode(array('result' => 1, 'content' => '导入完成！'));
        exit();
    }
    include $this->template('web/core/parking/import');
}