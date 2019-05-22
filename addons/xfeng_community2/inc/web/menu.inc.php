<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/3 下午10:40
 */
global $_W,$_GPC;
$op = in_array(trim($_GPC['op']),array('add','list')) ? $_GPC['op'] : 'list';
if($op == 'list'){
    $list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_menu')."WHERE  pcate = 0 order by displayorder asc ");
    $children = array();
    if($list){
        foreach ($list as $key => $value) {
            $sql  = "select * from".tablename("xcommunity_menu")."where  pcate='{$value['id']}' order by displayorder asc";
            $li = pdo_fetchall($sql);
            $children[$value['id']] = $li;

        }
    }

    if($_W['isajax']){
        $id = intval($_GPC['id']);
        $status = intval($_GPC['status']);
        pdo_query('update '.tablename('xcommunity_menu')."set status=:status where id=:id",array(':status'=>$status,':id'=>$id));
    }
    include $this->template('web/core/menu/list');
}elseif($op =='add'){
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_menu',array('id'=>$id),array());
    }
    if(checksubmit('submit')){
        $data= array(
          'title' => $_GPC['title'],
            'displayorder' => $_GPC['displayorder'],
        );
        if($id){
            if(pdo_update('xcommunity_menu',$data,array('id'=> $id))){
                itoast('修改成功',$this->createWebUrl('menu'),'success');
            }
        }
    }
    util::permlog('','修改后台菜单');
    include $this->template('web/core/menu/add');
}

