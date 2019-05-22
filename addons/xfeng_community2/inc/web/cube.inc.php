<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/4 下午2:58
 */
global $_W, $_GPC;
$ops = array('list', 'add', 'delete', 'show');
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
 * 魔方推荐的列表
 */
if ($op == 'list') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_slide', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('排序更新成功！', 'refresh', 'success',true);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid=:uniacid and type= 6';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if ($user&&$user[type] != 1) {
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
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cube/list');
}
/**
 * 魔方推荐的添加修改
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
            itoast('抱歉，信息不存在或是已经删除！', '', 'error',true);
        }
        $regs = pdo_getall('xcommunity_slide_region', array('sid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);
    }
    if ($_W['isajax']) {
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        $starttime = strtotime($_GPC['birthtime']['start']);
        $endtime = strtotime($_GPC['birthtime']['end']);
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
            'url' => $_GPC['url'],
            'type' => 6,
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'createtime' => TIMESTAMP,
            'status' => intval($_GPC['status']) ? intval($_GPC['status']) : 1,
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
            util::permlog('魔方推荐-添加','添加魔方ID:'.$id);
        } else {
            pdo_update("xcommunity_slide", $data, array('id' => $id));
            pdo_delete('xcommunity_slide_region', array('sid' => $id));
            util::permlog('魔方推荐-修改','修改魔方ID:'.$id);
        }
        if ($allregion == 1){
            $regions = pdo_getall('xcommunity_region',array('uniacid' => $_W['uniacid']),array('id'));
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
    include $this->template('web/core/cube/add');
}
/**
 * 魔方推荐的删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id, thumb FROM " . tablename("xcommunity_slide") . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        itoast('抱歉，广告不存在或是已经被删除！');
    }
    if (!empty($row['thumb'])) {
        load()->func('file');
        file_delete($row['thumb']);
    }
    if (pdo_delete("xcommunity_slide", array('id' => $id))) {
        if (pdo_delete('xcommunity_slide_region', array('sid' => $id))) {
            itoast('删除成功', referer(), 'success',true);
        }
    }
    util::permlog('魔方推荐-删除','删除魔方ID:'.$id);
    itoast('删除成功！', referer(), 'success',true);
}
/**
 * 魔方推荐的规格
 */
if($op == 'show'){
    include $this->template('web/core/cube/show');
}
