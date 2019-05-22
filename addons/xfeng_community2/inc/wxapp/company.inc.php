<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/20 下午1:35
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if($op =='detail'){
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    $data = array();
    $data['hstatus'] = set('p96') ? 1 : 0;
    $about = htmlspecialchars_decode(set('p90'));
    $pid = pdo_getcolumn('xcommunity_region', array('id' => $regionid), 'pid');
    $company = pdo_get('xcommunity_property', array('id' => $pid), array('content'));
    $data['reason'] = $about ? $about : $company['content'];
    util::send_result($data);

}