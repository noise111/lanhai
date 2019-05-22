<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/11/16 下午1:25
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('list', 'add', 'delete')) ? trim($_GPC['op']) : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'list') {
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_area', array('id' => $id));
            }
            util::permlog('', '批量删除小区区域');
            itoast('删除成功', referer(), 'success');
        }
    }
    $regions = model_region::region_fetall();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.title LIKE '%{$_GPC['keyword']}%'";
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t1.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
//    if ($user && $user[type] != 1) {
//        //普通管理员
//        $condition .= " AND t1.uid=:uid";
//        $params[':uid'] = $_W['uid'];
//    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "SELECT t1.title as areatitle,t1.id,t2.title FROM" . tablename('xcommunity_area') . "t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_area') . "t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id WHERE $condition",$params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/area/list');
} elseif ($op == 'add') {
    $regions = model_region::region_fetall();
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_area',array('id'=> $id),array());
    }
    if($_W['isajax']){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'title' => trim($_GPC['title']),
        );
        if(empty($id)){
            $data['uid'] = $_W['uid'];
            $item = pdo_get('xcommunity_area',array('title'=> $data['title'],'regionid'=>$data['regionid']),array());
            if(empty($item)){
                pdo_insert('xcommunity_area',$data);
            }else{
                echo json_encode(array('content'=>'区域不可重复添加'));exit();
            }

        }else{
            pdo_update('xcommunity_area',$data,array('id' => $id));
        }
        echo json_encode(array('status'=>1));exit();
    }

    include $this->template('web/core/area/add');
}elseif($op =='delete'){
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_area',array('id'=> $id),array('id'));
        if($item){
            if(pdo_delete('xcommunity_area',array('id'=>$id))){
                itoast('删除成功',referer(),'success');exit();
            }
        }
    }

}
