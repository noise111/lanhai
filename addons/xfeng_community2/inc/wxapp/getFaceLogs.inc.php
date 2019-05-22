<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/24 0024 下午 9:14
 */
global $_GPC, $_W;
$ops = array('list', 'detail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 人脸上传记录的列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    /**
     * 模拟数据
     */
//    $list = array(
//        array(
//            'id' => 1,
//            'realname' => '测试',
//            'createtime' => '2018-12-24 21:10'
//        ),
//    );
//    if ($pindex != 1) {
//        $list = array();
//    }
//    util::send_result($list);// 模拟数据
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['uid'] = $_W['member']['uid'];
    $logs = pdo_getslice('xcommunity_face_uploadlogs', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $list = array();
    foreach ($logs as $k => $v) {
        $list[] = array(
            'id' => $v['id'],
            'realname' => $v['realname'],
            'createtime' => date('Y-m-d H:i', $v['createtime'])
        );
    }
    util::send_result($list);
}
/**
 * 记录的详情
 */
if ($op == 'detail') {
//    /**
//     * 模拟数据
//     */
//    $data = array();
//    $data['id'] = 1;
//    $data['realname'] = '测试';
//    $data['createtime'] = '2018-12-24 21:20';
//    $data['images'] = "http://sxw.njlanniu.cn/attachment/images/1/2018/09/d777hdi8ge6hGfIgJdgIgGmi999888.jpg,http://sxw.njlanniu.cn/attachment/images/1/2018/09/d777hdi8ge6hGfIgJdgIgGmi999888.jpg";
//    $data['devices'] = '设备1,设备2,设备3';
//    util::send_result($data);
    $id = intval($_GPC['id']);
    if (empty($id)) {
        send_error(-1, '参数错误');
    }
    $item = pdo_get('xcommunity_face_uploadlogs', array('id' => $id), array());
    $deviceids = explode(',', $item['deviceids']);
    $devices = pdo_getall('xcommunity_building_device', array('id' => $deviceids), array('title'));
    $deviceTitle = '';
    foreach ($devices as $k => $v) {
        $deviceTitle .= $v['title'] . ',';
    }
    $data = array();
    $data['id'] = $item['id'];
    $data['realname'] = $item['realname'];
    $data['createtime'] = date('Y-m-d H:i', $item['createtime']);
    $data['images'] = $item['images'];
    $data['devices'] = xtrim($deviceTitle);
    util::send_result($data);
}