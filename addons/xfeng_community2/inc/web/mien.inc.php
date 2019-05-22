<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区风采
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
    $psize = 20;
    $condition = 'uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND realname LIKE '%{$_GPC['keyword']}%'";
    }
    if ($user&&$user[type] != 1) {
        //普通管理员
        $condition .= " AND uid=:uid";
        $params[':uid'] = $_W['uid'];
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_mien') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,$params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_mien') . "WHERE $condition",$params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/mien/list');
}elseif($op == 'add'){
    $regionids = '[]';
    if (!empty($id)) {
        $regions = model_region::region_fetall();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_mien') . "WHERE id=:id", array(':id' => $id));
        $regs = pdo_getall('xcommunity_mien_region', array('mienid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);

    }
    if ($_W['isajax']) {
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        $data = array(
            'uniacid' => $_W['uniacid'],
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile'],
            'position' => $_GPC['position'],
            'image' => $_GPC['image'],
            'createtime' => TIMESTAMP,
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'allregion' => $allregion
        );
        if (empty($_GPC['id'])) {
            $data['uid'] = $_W['uid'];
            pdo_insert('xcommunity_mien', $data);
            $id = pdo_insertid();
            util::permlog('小区风采-添加','信息标题:'.$data['title']);
        } else {
            pdo_update('xcommunity_mien', $data, array('id' => $id));
            pdo_delete('xcommunity_mien_region', array('mienid' => $id));
            util::permlog('小区风采-修改','信息标题:'.$data['title']);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'mienid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_mien_region', $dat);
            }
        }else {
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'mienid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_mien_region', $dat);
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
    include $this->template('web/core/mien/add');
}elseif ($op == 'delete') {
    if ($id) {
        $item = pdo_get('xcommunity_mien',array('id'=>$id),array());
        if($item){
            if (pdo_delete('xcommunity_mien', array('id' => $id))) {
                util::permlog('小区风采-删除','信息标题:'.$item['title']);
                if (pdo_delete('xcommunity_mien_region', array('mienid' => $id))) {
                    itoast('删除成功', referer(), 'success');
                }
            }
        }

    }
}