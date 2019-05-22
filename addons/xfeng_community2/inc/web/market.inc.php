<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/20 上午12:33
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('detail', 'list','delete','verify','toblack','set','black')) ? trim($_GPC['op']) : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
$id = intval($_GPC['id']);
if($op =='list'){
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_fled', array('id' => $id));
            }
            util::permlog('','批量删除信息');
            itoast('删除成功', referer(), 'success',ture);
        }
    }
    $condition = 't1.uniacid=:uniacid and t1.black=0';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition .= " AND t1.category = '{$_GPC['category']}'";
    }
    if (!empty($_GPC['status'])) {
        $condition .= " AND t1.status = '{$_GPC['status']}'";
    }
    if ($user[type] == 3) {
        //小区管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    } else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if(checksubmit('plverity')){
        $sql = "select t1.*,t4.realname,t4.mobile from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('mc_members')."t4 on t4.uid=t1.uid where $condition order by t1.createtime desc ";
        $xqlist = pdo_fetchall($sql,$params);
        foreach ($xqlist as $k => $v){
            $enable = $v['enable'] == 1 ? 0 : 1;
            pdo_update('xcommunity_fled',array('enable' => $enable),array('id'=>$v['id']));
        }
        util::permlog('','批量审核信息');
        itoast('审核成功',referer(),'success',ture);
    }
    $categories = util::fetchall_category(4);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select t1.*,t4.realname,t4.mobile,t5.name as cname from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('mc_members')."t4 on t4.uid=t1.uid left join".tablename('xcommunity_category')."t5 on t1.category=t5.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $tsql = "select count(*) from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('mc_members')."t4 on t4.uid=t1.uid where $condition";
    $total = pdo_fetchcolumn($tsql,$params);

    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/market/list');
}
elseif($op =='detail'){
    $sql = "select t1.images,t1.title,t1.zprice,t1.createtime,t4.realname,t4.mobile from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t2.id=t1.addressid left join".tablename('mc_members')."t4 on t4.uid=t1.uid left join".tablename('xcommunity_category')."t5 on t1.category=t5.id where t1.id=:id";
    $item = pdo_fetch($sql,array(':id'=>$id));
    if (!$item) {
        itoast('该信息不存在');
    }
    if($item['images']){
        $images = explode(',',$item['images']);
    }
    include $this->template('web/plugin/market/detail');
}
elseif ($op == 'verify') {
    //审核用户
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable'))) {
        $data = ($data == 0 ? '1' : '0');
        pdo_update("xcommunity_fled", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
elseif ($op == 'toblack') {
    if ($id) {
        pdo_query("UPDATE " . tablename('xcommunity_fled') . "SET black =1 WHERE id=:id", array(':id' => $id));
        util::permlog('','二手商品ID:'.$id.'加入黑名单');
        echo json_encode(array('state' => 1));
    }
}
elseif ($op == 'delete') {
    $id = intval($_GPC['id']);
    if ($_W['isajax']) {
        if (empty($id)) {
            exit('缺少参数');
        }
        $item = pdo_get('xcommunity_fled',array('id'=> $id),array());
        if($item){
            $r = pdo_delete("xcommunity_fled", array('id' => $id));
            util::permlog('二手市场-删除','信息标题:'.$item['title']);
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
    include $this->template('web/plugin/market/set');
}
elseif ($op == 'black') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $condition = '';
        if ($user[type] == 3) {
            //普通管理员
            $condition .= " and r.id in({$user['regionid']})";
        }
        $list = pdo_fetchall("SELECT f.*,r.title,t3.mobile FROM" . tablename('xcommunity_fled') . "as f left join " . tablename('xcommunity_region') . "as r on f.regionid = r.id left join".tablename('mc_members')."t3 on t3.uid = f.uid WHERE f.uniacid='{$_W['uniacid']}' AND f.black = 1 $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fled') . "as f left join " . tablename('xcommunity_region') . "as r on f.regionid = r.id left join".tablename('mc_members')."t3 on t3.uid = f.uid WHERE f.uniacid='{$_W['uniacid']}' AND f.black = 1 $condition ");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/market/black');
    }
    elseif ($p == 'delete') {
        if ($_W['isajax']) {
            if (empty($id)) {
                exit('缺少参数');
            }
            $r = pdo_delete('xcommunity_fled', array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($p == 'toblack') {
        if ($id) {
            pdo_query("UPDATE " . tablename('xcommunity_fled') . "SET black =0 WHERE id=:id", array(':id' => $id));
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}