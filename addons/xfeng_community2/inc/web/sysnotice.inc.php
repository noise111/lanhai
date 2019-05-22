<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/3 下午5:30
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if($op == 'list'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select * from " . tablename("xcommunity_announcement") . "where  uniacid = {$_W['uniacid']} and status = 1 order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_announcement") . "where  uniacid = {$_W['uniacid']} and status = 1 ");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/sysnotice/list');
}elseif ($op == 'add'){
    $regionids = '[]';
    if (!empty($id)) {
        $regions = model_region::region_fetall();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_announcement') . "WHERE id=:id", array(':id' => $id));
        $regs = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);

    }
    //添加公告
//    if (checksubmit('submit')) {
    if ($_W['isajax']) {
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        if ($allregion == 1){

        }else{
            if(empty($birth['province'])){
                itoast('必须选择省市区和小区',referer(),'error');exit();
            }
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'createtime' => TIMESTAMP,
            'reason' => htmlspecialchars_decode($_GPC['reason']),
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'status' => 1,
            'enable' => 1,//1显示2隐藏
            'allregion' => $allregion
        );
        if (empty($id)) {
            $data['uid'] = $_W['uid'];
            pdo_insert("xcommunity_announcement", $data);
            $id = pdo_insertid();
            util::permlog('首页-公告-添加','信息标题:'.$data['title']);
        } else {
            pdo_update("xcommunity_announcement", $data, array('id' => $id));
            pdo_delete('xcommunity_announcement_region', array('aid' => $id));
            util::permlog('首页-公告-修改','信息标题:'.$data['title']);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'aid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_announcement_region', $dat);
            }
        }else {
            $regionids = explode(',', $_GPC['regionids']);
//        print_r($regionids);exit();
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'aid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_announcement_region', $dat);
            }
        }
        echo json_encode(array('status'=>1));exit();
//        itoast('提交成功', referer(), 'success',true);
    }
    include $this->template('web/core/sysnotice/add');
} elseif ($op == 'delete') {
    //删除公告
    if ($id) {
        $item = pdo_get('xcommunity_announcement',array('id'=>$id),array());

        if (pdo_delete("xcommunity_announcement", array('id' => $id))) {
            util::permlog('首页-公告-删除','信息标题:'.$item['title']);
            if (pdo_delete('xcommunity_announcement_region', array('aid' => $id))) {

            }
            itoast('删除成功', referer(), 'success',true);
        }
    }
}

