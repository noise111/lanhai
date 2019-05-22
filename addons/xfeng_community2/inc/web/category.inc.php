<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台分类管理
 */


global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'add') {
    $parentid = intval($_GPC['parentid']);
    //编辑分类信息
    if (!empty($id)) {
        $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE id=:id", array(':id' => $id));
    }
    //添加分类主ID
    if (!empty($parentid)) {
        $parent = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE id=:parentid", array(':parentid' => $parentid));
    }
    //提交
    if ($_W['isajax']) {
        $type = intval($_GPC['type']);
        $data = array(
            'name' => $_GPC['catename'],
            'displayorder' => $_GPC['displayorder'],
            'description' => $_GPC['description'],
            'enabled' => 1,
            'uniacid' => $_W['uniacid'],
            'thumb' => tomedia($_GPC['thumb']),
            'url' => $_GPC['url']
        );
        $category = pdo_get('xcommunity_category',array('type'=>$type,'name'=> $data['name'],'uniacid' => $_W['uniacid']),array('id'));
        if ($id){
            $item = pdo_get('xcommunity_category',array('id' => $id,'uniacid' => $_W['uniacid']),array('name'));
            if ($item['name'] != $_GPC['catename']){
                if($category){
//                    itoast('分类已存在,请勿重复添加',$this->createWebUrl('category',array('op'=>'list','type'=>$type)),'error');exit();
                    echo json_encode(array('content'=>'分类已存在,请勿重复添加'));exit();
                }
            }
        }else{
            if($category){
//                itoast('分类已存在,请勿重复添加',$this->createWebUrl('category',array('op'=>'list','type'=>$type)),'error');exit();
                echo json_encode(array('content'=>'分类已存在,请勿重复添加'));exit();
            }
        }
        if ($type == 2){
            $data['finishtime'] = intval($_GPC['finishtime']);
        }elseif ($type == 3){
            $data['reportnum'] = intval($_GPC['reportnum']);
        }
        if (empty($parentid)) {
            if (empty($id)) {
                //添加主类
                $data['type'] = $type;
                $data['parentid'] =0;
                pdo_insert("xcommunity_category", $data);
                $id = pdo_insertid();
                util::permlog('分类-添加','信息标题:'.$data['name']);
            } else {
                //更新
                pdo_update("xcommunity_category", $data, array('id' => $id));
                util::permlog('分类-修改','信息标题:'.$data['name']);
            }
        } else {
            //添加子类
            $data['parentid'] = $parentid;
            if (empty($id)) {
                $data['type'] = $type;
                pdo_insert("xcommunity_category", $data);
            } else {
                //更新子类
                pdo_update("xcommunity_category", $data, array('id' => $id));
            }

        }
        echo json_encode(array('status'=>1));exit();
    }
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/core/category/add');
}
elseif ($op == 'list') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_category', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('分类排序更新成功！', referer(), 'success',true);
    }
    //显示全部分类信息
    $sql = "select * from" . tablename("xcommunity_category") . "where parentid=0 AND uniacid =:uniacid and type=:type order by displayorder desc ";
    $category = pdo_fetchall($sql,array(':uniacid'=>$_W['uniacid'],':type'=> intval($_GPC['type'])));
    $children = array();
    foreach ($category as $key => $value) {
        $list = pdo_fetchall("select * from" . tablename("xcommunity_category") . "where uniacid='{$_W['uniacid']}' and  parentid=:parentid order by displayorder desc  ", array(":parentid" => $value['id']));
        $children[$value['id']] = $list;
    }
    include $this->template('web/core/category/list');
}
elseif ($op == 'delete') {
    //删除分类信息
    $item = pdo_get('xcommunity_category',array('id'=> $id),array());
    if($item){
        util::permlog('分类-删除','信息标题:'.$item['name'].'id='.$id);
        pdo_delete("xcommunity_category", array('id' => $id));
        itoast('删除成功', referer(), 'success',true);
    }

}
