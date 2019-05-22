<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/21 下午2:44
 */
global $_GPC,$_W;
$xqkey = trim($_GPC['xqkey']);
$status = intval($_GPC['status']);
$regionid = intval($_GPC['regionid']);
if (empty($xqkey)) {
    return FALSE;
}
$condition = "xqkey='{$xqkey}' and uniacid={$_W['uniacid']}";
if($regionid){
    $condition .=" and regionid={$regionid}";
}
$sql = "select * from".tablename('xcommunity_setting')."where $condition";
$item = pdo_fetch($sql);
$data = array(
    'xqkey' => $xqkey,
    'value' => $status,
    'regionid' => $regionid,
    'uniacid' => $_W['uniacid']
);
if($item){
    pdo_update('xcommunity_setting',$data,array('id'=> $item['id']));
}else{
    pdo_insert('xcommunity_setting',$data);
}