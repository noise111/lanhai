<?php
/**
 * 微小区模块
 *
 * [蓝牛科技] Copyright (c) 2013 njzhsq.com
 */
/**
 * 后台小区二手交易
 */


global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
$user = util::xquser($_W['uid']);
$regions = model_region::region_fetall();
load()->model('mc');
if ($op == 'list') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_fled', array('id' => $id));
            }
            util::permlog('','批量删除二手市场信息');
            itoast('删除成功', referer(), 'success',ture);
        }
    }
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['category'])) {
        $condition .= " AND t1.category = '{$_GPC['category']}'";
    }
    if (!empty($_GPC['status'])) {
        $condition .= " AND t1.status = '{$_GPC['status']}'";
    }
    if ($user&&$user[type] != 1) {
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
        util::permlog('','批量审核二手商品');
        itoast('审核成功',referer(),'success',ture);
    }
    $categories = util::fetchall_category(4);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "select t1.*,t4.realname,t4.mobile from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('mc_members')."t4 on t4.uid=t1.uid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $tsql = "select count(*) from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('mc_members')."t4 on t4.uid=t1.uid where $condition";
    $total = pdo_fetchcolumn($tsql,$params);

    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/fled/list');
} elseif ($op == 'delete') {
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
} elseif ($op == 'detail') {

    $sql = "select t1.title,t1.zprice,t1.createtime,t4.realname,t4.mobile from".tablename('xcommunity_fled')."t1 left join".tablename('xcommunity_member_room')."t2 on t2.id=t1.addressid left join".tablename('mc_members')."t4 on t4.uid=t1.uid where t1.id=:id";
    $item = pdo_fetch($sql,array(':id'=>$id));
    if (!$item) {
        itoast('该商品不存在');
    }
    $tsql = "select * from".tablename('xcommunity_fled_images')."t1 left join".tablename('xcommunity_images')."t2 on t1.thumbid = t2.id where t1.fledid=:fledid and t2.src !=''";
    $imgs = pdo_fetchall($tsql,array(":fledid"=> $id));

    include $this->template('web/fled/detail');
} elseif ($op == 'toblack') {
    if ($id) {
        pdo_query("UPDATE " . tablename('xcommunity_fled') . "SET black =1 WHERE id=:id", array(':id' => $id));
        util::permlog('','二手商品ID:'.$id.'加入黑名单');
        echo json_encode(array('state' => 1));
    }
} elseif ($op == 'category') {
    $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_category') . "WHERE uniacid=:uniacid AND type =4", array(':uniacid' => $_W['uniacid']));
    if (checksubmit('submit')) {
        $count = count($_GPC['names']);
        // print_r($count);exit();
        for ($i = 0; $i < $count; $i++) {
            $ids = $_GPC['ids'];
            $id = trim(implode(',', $ids), ',');
            $data = array(
                'name' => $_GPC['names'][$i],
                'uniacid' => $_W['uniacid'],
                'type' => 4,
            );
            if ($ids[$i]) {
                $r = pdo_update("xcommunity_category", $data, array('id' => $ids[$i]));
            } else {
                $r = pdo_insert("xcommunity_category", $data);
            }
        }
        itoast('提交成功',referer(),ture);

    }

    include $this->template('web/fled/category');
} elseif ($op == 'del') {
    if ($id) {
        pdo_delete("xcommunity_category", array('id' => $id));
        itoast('删除成功。', referer(), 'success',ture);
    }

} elseif ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('enable'))) {
        $data = ($data == 0 ? '1' : '0');
        pdo_update("xcommunity_fled", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}


	