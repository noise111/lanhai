<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */

global $_W,$_GPC;
$op = in_array(trim($_GPC['op']),array('set','sys','qwt','xqset','field','xqshare','register')) ? trim($_GPC['op']) : 'set';

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
$options=array();
$options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
if($op == 'set'){

    include $this->template('web/core/set/set');
}elseif($op =='sys'){
    load()->func('tpl');

    include $this->template('web/core/set/sys');
}elseif($op =='qwt'){
    load()->func('tpl');
    include $this->template('web/core/set/qwt');
}elseif($op =='xqset'){
    load()->func('tpl');
    $tel = $set['p23']['value'];
    include $this->template('web/core/set/xqset');
}elseif($op =='field'){
    load()->func('tpl');
    include $this->template('web/core/set/field');
}elseif($op =='xqshare'){
    load()->func('tpl');
    include $this->template('web/core/set/xqshare');
}elseif($op =='register'){
    load()->func('tpl');
    include $this->template('web/core/set/register');
}
