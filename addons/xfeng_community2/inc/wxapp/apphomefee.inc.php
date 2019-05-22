<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/8/6 0006
 * Time: 下午 5:49
 */
global $_GPC, $_W;
$ops = array('list', 'detail','pactsave');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {

}
elseif($op =='pactsave'){
    $pics = $_GPC['pics'];
    $pic = '';
    if ($pics) {
        $pics = explode(',', $pics);
        if (!empty($pics)) {
            foreach ($pics as $k => $v) {
                $pic .= $v . ',';//修改为H5上传图片
            }
        }
        $pic = ltrim(rtrim($pic, ','), ',');
    }
    $data = array();
    if (empty($item)) {
        $data['content'] = '信息不存在';
    }
    $data = array(
//        'uid'        => $_W['member']['uid'],
        'createtime' => TIMESTAMP,
        'uniacid' => intval($_W['uniacid']),
        'thumb'     => $pic,
    );
    $data['uid'] = $_SESSION['appuid'];
    pdo_insert('xcommunity_homefee_pact_save', $data);
    $data['content'] = '提交成功';
    util::send_result($data);
}