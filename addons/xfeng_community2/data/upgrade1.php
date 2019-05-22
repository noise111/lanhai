<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/26 下午11:42
 */
//更新数据 适用更新8.0升级9.0 出现问题后，解决方法
$sql = "select * from".tablename('xcommunity_room')."where 1";
$rooms = pdo_fetchall($sql);
//    print_r($rooms);exit();
foreach ($rooms as $k => $room){
    $data = array(
        'area'       => '',
        'build'      => $room['build'],
        'unit'       => $room['unit'],
        'room'       => '',
        'square'     => $room['square'],
        'address'    => $room['room'] ? $room['room'] : $addr,
        'createtime' => TIMESTAMP,
        'regionid'   => intval($room['regionid']),
        'uniacid'    => $room['uniacid'],
        'code'       => $room['code']
    );
    if(pdo_insert('xcommunity_member_room',$data)){
        $d = array(
            'uniacid'  => $room['uniacid'],
            'regionid' => intval($room['regionid']),
            'realname' => trim($room['realname']),
            'mobile'   => trim($room['mobile']),
            'status'   => 1,
            'addressid' => pdo_insertid()
        );
        pdo_insert('xcommunity_member_log', $d);

    }
}
$sql = "select * from" . tablename('xcommunity_member') . "where 1 limit 1,3000";
$members = pdo_fetchall($sql);

////
foreach ($members as $key => $member) {
    $bind = pdo_get('xcommunity_member_bind', array('memberid' => $member['id']), array());
//       $d = array(
//                    'uniacid' => $_W['uniacid'],
//                    'regionid' => intval($val['regionid']),
//                    'realname' => $val['realname'],
//                    'mobile' => $val['mobile'],
//                    'addressid' => $bind['addressid'],
//                    'status' => 1
//                );
//                pdo_insert('xcommunity_member_log',$d);

    if (empty($bind)) {
        $dat = array(
            'createtime' => $member['createtime'],
            'regionid'   => $member['regionid'],
            'area'       => $member['area'],
            'build'      => $member['build'],
            'unit'       => $member['unit'],
            'room'       => $member['room'],
            'address'    => $member['address'],
            'code'       => $member['code'],
            'square'     => $member['square'],
            'enable'     => $member['enable'],
            'uniacid'    => $member['uniacid'],
        );
        pdo_insert('xcommunity_member_room', $dat);
        $roomid = pdo_insertid();
        $data = array(
            'memberid'   => $member['id'],
            'createtime' => $member['createtime'],
            'status'     => $member['status'],
            'enable'     => $member['enable'],
            'uniacid'    => $member['uniacid'],
            'addressid'  => $roomid
        );
        pdo_insert('xcommunity_member_bind', $data);
        $d = array(
            'uniacid'   => $_W['uniacid'],
            'regionid'  => intval($member['regionid']),
            'realname'  => $member['realname'],
            'mobile'    => $member['mobile'],
            'addressid' => $roomid,
            'status'    => 1
        );
        pdo_insert('xcommunity_member_log', $d);
    }else{
        $dat = array(
            'createtime' => $member['createtime'],
            'regionid' => $member['regionid'],
            'area' => $member['area'],
            'build' => $member['build'],
            'unit' => $member['unit'],
            'room' => $member['room'],
            'address' => $member['address'],
            'code' => $member['code'],
            'square' => $member['square'],
            'enable' => $member['enable'],
            'uniacid' => $member['uniacid'],
        );
        pdo_insert('xcommunity_member_room', $dat);
        $roomid = pdo_insertid();
//            $bind = pdo_get('xcommunity_member_bind',array('memberid' => $member['id']),array('id','addressid'));
        if(empty($bind['addressid'])){
            $data = array(
                'memberid' => $member['id'],
                'createtime' => $member['createtime'],
                'status' => $member['status'],
                'enable' => $member['enable'],
                'uniacid' => $member['uniacid'],
                'addressid' => $roomid
            );
            pdo_insert('xcommunity_member_bind', $data);
        }
    }



}
unset($members);
//提取未注册用户

//        pdo_query("delete from".tablename('xcommunity_member_room'));
pdo_query("delete from".tablename('xcommunity_bind_door_device'));
pdo_query("delete from".tablename('xcommunity_announcement_region'));
pdo_query("delete from".tablename('xcommunity_activity_region'));
pdo_query("delete from".tablename('xcommunity_phone_region'));
pdo_query("delete from".tablename('xcommunity_search_region'));
pdo_query("delete from".tablename('xcommunity_report_images'));
pdo_query("delete from".tablename('xcommunity_rank'));
pdo_query("delete from".tablename('xcommunity_report_log'));
pdo_query("delete from".tablename('xcommunity_slide_region'));
pdo_query("delete from".tablename('xcommunity_nav'));
pdo_query("delete from".tablename('xcommunity_nav_region'));
pdo_query("delete from".tablename('xcommunity_houselease_images'));
pdo_query("delete from".tablename('xcommunity_fled_images'));
pdo_query("delete from".tablename('xcommunity_coupon_order'));
pdo_query("delete from".tablename('xcommunity_setting'));
pdo_query("delete from".tablename('xcommunity_goods_region'));
pdo_query('update '.tablename('xcommunity_region')."set status=1 where 1");
pdo_query('update '.tablename('xcommunity_announcement')."set enable=1 where 1");

$member_table = 'xcommunity_member';
$member_sql = "select * from".tablename($member_table)."where 1";
$members = pdo_fetchall($member_sql);
foreach ($members as $key => $member){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $member['openid']),array('uid'));
    if($fans['uid']){
        //更新uid,uniacid
        if(empty($member['uniacid'])){
            pdo_update('xcommunity_member',array('uid'=>$fans['uid'],'uniacid' => $member['weid']),array('id'=> $member['id']));
        }

//                //更新会员的姓名和手机号码
        pdo_update('mc_members',array('realname'=> $member['realname'],'mobile' => $member['mobile']),array('uid' => $fans['uid']));
        //更新地址表
        $memberid = $member['id'];
        $address = array(
            'memberid' => $memberid,
            'area' => $member['area'],
            'build' => $member['build'],
            'unit'=> $member['unit'],
            'room' => $member['room'],
            'status' => 1,
            'enable' => 1,
            'address' => $member['address'],
            'createtime' => $member['createtime'],
        );
        pdo_insert('xcommunity_member_room',$address);
    }
}
unset($fans);
unset($members);

$xqregions = pdo_fetchall('select * from'.tablename('xcommunity_region')."where 1");
foreach ($xqregions as $k => $region){
    if(empty($region['uniacid'])){
        pdo_update('xcommunity_region',array('uniacid'=> $region['weid']),array('id'=> $region['id']));
    }
}
unset($xqregions);
$dps = pdo_fetchall('select * from'.tablename('xcommunity_dp')."where 1");
foreach ($dps as $k => $dp){
    if(empty($dp['uniacid'])){
        pdo_update('xcommunity_dp',array('uniacid'=> $dp['weid']),array('id'=> $dp['id']));
    }
}
unset($dps);
$carts = pdo_fetchall('select * from'.tablename('xcommunity_cart')."where 1");
foreach ($carts as $k => $cart){
    if(empty($cart['uniacid'])){
        pdo_update('xcommunity_cart',array('uniacid'=> $cart['weid']),array('id'=> $cart['id']));
    }
}
unset($carts);
$cids = pdo_fetchall('select * from'.tablename('xcommunity_category')."where 1");
foreach ($cids as $k => $cid){
    if(empty($cid['uniacid'])){
        pdo_update('xcommunity_category',array('uniacid'=> $cid['weid']),array('id'=> $cid['id']));
    }
}
unset($cids);
$costs = pdo_fetchall('select * from'.tablename('xcommunity_cost')."where 1");
foreach ($costs as $k => $cost){
    if(empty($cost['uniacid'])){
        pdo_update('xcommunity_cost',array('uniacid'=> $cost['weid']),array('id'=> $cost['id']));
    }
}
unset($costs);
$costlists = pdo_fetchall('select * from'.tablename('xcommunity_cost_list')."where 1");
foreach ($costlists as $k => $costlist){
    if(empty($costlist['uniacid'])){
        pdo_update('xcommunity_cost_list',array('uniacid'=> $costlist['weid']),array('id'=> $costlist['id']));
    }
}
unset($costlists);
$ordergoods = pdo_fetchall('select * from'.tablename('xcommunity_order_goods')."where 1");
foreach ($ordergoods as $k => $ordergood){
    if(empty($ordergood['uniacid'])){
        pdo_update('xcommunity_order_goods',array('uniacid'=> $ordergood['weid']),array('id'=> $ordergood['id']));
    }
}
unset($ordergoods);
$pros = pdo_fetchall('select * from'.tablename('xcommunity_property')."where 1");
foreach ($pros as $k => $pro){
    if(empty($pro['uniacid'])){
        pdo_update('xcommunity_property',array('uniacid'=> $pro['weid']),array('id'=> $pro['id']));
    }
}
unset($ordergoods);
////业主绑定手机开门数据
//
$doors = pdo_fetchall("select * from".tablename('xcommunity_bind_door')."where 1");
foreach ($doors as $k => $door){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $door['openid']),array('uid'));
    if(empty($door['uid'])){
        pdo_update('xcommunity_bind_door',array('uid'=> $fans['uid']),array('id'=> $door['id']));
    }

    $devices = explode(',',$door['deviceid']);
    if($devices !='N;' && !empty($devices)){
        foreach ($devices as $key => $value) {
            $dat = array(
                'doorid' => $door['id'],
                'deviceid' => $value,
            );
            pdo_insert('xcommunity_bind_door_device', $dat);
        }
    }

}
unset($doors);

//公告数据转化

$notices = pdo_fetchall('select * from'.tablename('xcommunity_announcement')."where 1");
foreach ($notices as $k => $n){
    if(empty($n['uniacid'])){
        pdo_update('xcommunity_announcement',array('uniacid'=> $n['weid']),array('id'=> $n['id']));
    }

    $regions = unserialize($n['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'aid' => $n['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_announcement_region', $dat);
        }
    }
}
unset($notices);

//小区活动

$activities = pdo_fetchall('select * from'.tablename('xcommunity_activity')."where 1");
foreach ($activities as $k => $activity){
    if(empty($activity['uniacid'])){
        pdo_update('xcommunity_activity',array('uniacid'=> $activity['weid']),array('id'=> $activity['id']));
    }

    $regions = unserialize($activity['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'activityid' => $activity['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_activity_region', $dat);
        }
    }
}
unset($activities);

//便民号码

$phones = pdo_fetchall('select * from'.tablename('xcommunity_phone')."where 1");
foreach ($phones as $k => $phone){
    if (empty($phone['uniacid'])){
        pdo_update('xcommunity_phone',array('uniacid'=> $phone['weid']),array('id'=> $phone['id']));
    }

    $regions = unserialize($phone['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'phoneid' => $phone['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_phone_region', $dat);
        }
    }
}
unset($phones);
//商品转化
$goods = pdo_fetchall('select * from'.tablename('xcommunity_goods')."where 1");
foreach ($goods as $k => $good){
    if (empty($good['uniacid'])){
        pdo_update('xcommunity_goods',array('uniacid'=> $good['weid']),array('id'=> $good['id']));
    }

    $regions = unserialize($good['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'gid' => $good['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_goods_region', $dat);
        }
    }
}
////便民查询
//
$searchs = pdo_fetchall('select * from'.tablename('xcommunity_search')."where 1");
foreach ($searchs as $k => $search){
    if(empty($search['uniacid'])){
        pdo_update('xcommunity_search',array('uniacid'=> $search['weid']),array('id'=> $search['id']));
    }

    $regions = unserialize($search['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'sid' => $search['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_search_region', $dat);
        }
    }
}
unset($searchs);

//小区报修建议

$reports = pdo_fetchall("select * from".tablename('xcommunity_report')."where 1");
foreach ($reports as $k => $report){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $report['openid']),array('uid'));

    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$report['regionid']));

    pdo_update('xcommunity_report',array('uniacid'=> $report['weid'],'addressid' => $addr['id']),array('id'=> $report['id']));


    //处理图片
    $images = explode(',',$report['images']);
    if($images !='N;'&&!empty($images)) {
        foreach ($images as $key => $image) {
            $dat = array(
                'reportid' => $report['id'],
                'thumbid' => $image,
            );
            pdo_insert('xcommunity_report_images', $dat);
        }
    }
    //评价
    $rank = array(
        'uniacid' => $report['weid'],
        'type'=> $report['type'] == 1 ? 3:4,
        'content' => $report['comment'],
        'rankid' => $report['id'],
        'createtime' => $report['createtime'],
        'uid' => $fans['uid'],
        'rank' => $report['rank'],
    );
    pdo_insert('xcommunity_rank',$rank);
    //处理记录
    $sum = 0;
    if(!empty($report['grabimages'])){
        $images = pdo_fetchall("select * from".tablename('xcommunity_images')."where id({$report['grabimages']})");
        $count = count($images);
        for($i = 0; $i < $count; $i++){
            $sum .= $images[$i]['src'];
        }
        $sum = substr($sum,1);
    }
    $log = array(
        'reportid' => $report['id'],
        'content' => $report['resolve'],
        'createtime' => $report['resolvetime'],
        'dealing' => $report['dealing'],
        'images' => $sum
    );
    pdo_insert('xcommunity_report_log',$log);
}
unset($reports);

//小区幻灯

$slides = pdo_fetchall('select * from'.tablename('xcommunity_slide')."where 1");
foreach ($slides as $k => $s){
    if(empty($s['uniacid'])){
        pdo_update('xcommunity_slide',array('uniacid'=> $s['weid']),array('id'=> $s['id']));
    }

    $regions = unserialize($s['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'sid' => $s['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_slide_region', $dat);
        }
    }
}
unset($slides);
//小区导航

$navs = pdo_fetchall('select * from'.tablename('xcommunity_nav')."where 1");
foreach ($navs as $k => $nav){
    $regions = unserialize($nav['regionid']);
    if($regions !='N;'&&!empty($regions)) {
        foreach ($regions as $key => $regionid) {
            $dat = array(
                'nid' => $nav['id'],
                'regionid' => $regionid,
            );
            pdo_insert('xcommunity_nav_region', $dat);
        }
    }
}
unset($navs);
//小区家政

$homemakings = pdo_fetchall('select * from'.tablename('xcommunity_homemaking')."where 1");
foreach ($homemakings as $k => $homemaking){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $homemaking['openid']),array('uid'));
//    if(empty($homemaking['addressid'])){
    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$homemaking['regionid']));
    pdo_update('xcommunity_homemaking',array('uniacid'=> $homemaking['weid'],'addressid' => $addr['id']),array('id'=> $homemaking['id']));
//    }

}
unset($homemakings);
////小区租赁
//
$houselease = pdo_fetchall('select * from'.tablename('xcommunity_houselease')."where 1");
foreach ($houselease as $k => $house){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $house['openid']),array('uid'));
//    if(empty($house['uniacid'])){
    pdo_update('xcommunity_houselease',array('uniacid'=> $house['weid'],'uid' => $fans['uid']),array('id'=> $house['id']));
//    }
    $images = explode(',',$house['images']);
    if($images !='N;'&&!empty($images)) {
        foreach ($images as $key => $image) {
            $dat = array(
                'houseid' => $house['id'],
                'thumbid' => $image,
            );
            pdo_insert('xcommunity_houselease_images', $dat);
        }
    }
}
unset($houselease);
//小区二手

$fleds = pdo_fetchall('select * from'.tablename('xcommunity_fled')."where 1");
foreach ($fleds as $k => $fled){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $fled['openid']),array('uid'));
//    if(empty($fled['addressid'])){
    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$fled['regionid']));
    pdo_update('xcommunity_fled',array('uniacid'=> $fled['weid'],'addressid' => $addr['id']),array('id'=> $fled['id']));
//    }


    $images = explode(',',$fled['images']);
    if($images !='N;'&&!empty($images)) {
        foreach ($images as $key => $image) {
            $dat = array(
                'fledid' => $fled['id'],
                'thumbid' => $image,
            );
            pdo_insert('xcommunity_fled_images', $dat);
        }
    }
}
unset($fleds);
////小区拼车
//
$cars = pdo_fetchall('select * from'.tablename('xcommunity_carpool')."where 1");
foreach ($cars as $k => $car){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $car['openid']),array('uid'));
    if(empty($car['uid'])){
        pdo_update('xcommunity_carpool',array('uniacid'=> $car['weid'],'uid' => $fans['uid']),array('id'=> $car['id']));
    }


}
unset($cars);
////开门记录
$opens = pdo_fetchall('select * from'.tablename('xcommunity_open_log')."where 1");
foreach ($opens as $k => $open){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $open['openid']),array('uid'));
    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$open['regionid']));


    pdo_update('xcommunity_open_log',array('uid' => $fans['uid'],'addressid' => $addr['id']),array('id'=> $open['id']));

}
unset($opens);
//超市订单

$orders = pdo_fetchall('select * from'.tablename('xcommunity_order')."where type='shopping'");
foreach ($orders as $k => $order){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $order['from_user']),array('uid'));

    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$order['regionid']));

    pdo_update('xcommunity_order',array('uniacid'=> $order['weid'],'uid' => $fans['uid'],'addressid' => $addr['id']),array('id'=> $order['id']));



}
unset($orders);
//缴费
$aorders = pdo_fetchall('select * from'.tablename('xcommunity_order')."where type='pfree'");
foreach ($aorders as $k => $aorder){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $aorder['from_user']),array('uid'));

    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$aorder['regionid']));

    pdo_update('xcommunity_order',array('uniacid'=> $aorder['weid'],'uid' => $fans['uid'],'addressid' => $addr['id']),array('id'=> $aorder['id']));
    $d = array(
        'uniacid' => $aorder['weid'],
        'goodsid' => $aorder['pid'],
        'orderid' => $aorder['id'],
        'total' => '',
        'price' => $aorder['price'],
        'createtime' => TIMESTAMP,
    );
    pdo_insert('xcommunity_order_goods', $d);


}
unset($aorders);
//活动
$corders = pdo_fetchall('select * from'.tablename('xcommunity_order')."where type='activity'");
foreach ($corders as $k => $corder){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $corder['from_user']),array('uid'));

    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$corder['regionid']));

    pdo_update('xcommunity_order',array('uniacid'=> $corder['weid'],'uid' => $fans['uid'],'addressid' => $addr['id']),array('id'=> $corder['id']));



}
unset($corders);
////商家订单
//
$borders = pdo_fetchall('select * from'.tablename('xcommunity_order')."where type='business'");
foreach ($borders as $k => $border){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $border['from_user']),array('uid'));

    $addr =pdo_fetch("select t1.id from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member')."t2 on t1.memberid = t2.id where t2.uid =:uid and t2.regionid=:regionid",array(':uid'=> $fans['uid'],':regionid'=>$border['regionid']));
    pdo_update('xcommunity_order',array('uniacid'=> $order['weid'],'uid' => $fans['uid'],'addressid' => $addr['id']),array('id'=> $border['id']));


    //券号
    $coupon = array(
        'orderid' => $border['id'],
        'couponsn' => $border['couponsn'],
        'createtime' => $border['createtime'],
        'status' => $border['enable'],
        'usetime' => $border['usertime'],
    );
    pdo_insert('xcommunity_coupon_order',$coupon);

}
unset($borders);
//小区活动预约转化

$res = pdo_fetchall('select * from'.tablename('xcommunity_res')."where 1");
foreach ($res as $k => $r){
    $fans = pdo_get('mc_mapping_fans',array('openid'=> $r['uid']),array('uid'));
//    if(empty($r['uid'])){
    pdo_update('xcommunity_res',array('uniacid'=> $r['weid'],'uid' => $fans['uid']),array('id'=> $r['id']));
//    }

}
unset($res);
$cost = pdo_fetchall("select * from".tablename('xcommunity_cost_list')."where 1");
if($cost){
    foreach ($cost as $k => $val){

        $addr = strFilter($val['homenumber']);
        pdo_update('xcommunity_cost_list',array('address'=>$addr),array('id'=> $val['id']));

    }
}
unset($cost);
/*索引*/
pdo_query("ALTER TABLE ".tablename('xcommunity_member')." ADD index(uid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_member')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_member_address')." ADD index(memberid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_building_device')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_report')." ADD index(addressid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_report')." ADD index(cid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_report')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_report_log')." ADD index(reportid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_report_log')." ADD index(uid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_rank')." ADD index(rankid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_users')." ADD index(staffid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_notice')." ADD index(staffid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_staff')." ADD index(departmentid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_department')." ADD index(pid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_notice_region')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_notice_category')." ADD index(cid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_notice_category')." ADD index(nid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_homemaking')." ADD index(addressid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_houselease')." ADD index(uid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_cost')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_cost_list')." ADD index(homenumber) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_order')." ADD index(addressid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_category')." ADD index(regionid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_res')." ADD index(aid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_res')." ADD index(uid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_fled')." ADD index(addressid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_order_goods')." ADD index(orderid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_order_goods')." ADD index(goodsid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_coupon_order')." ADD index(orderid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_users')." ADD index(uid) ;");

pdo_query("ALTER TABLE ".tablename('xcommunity_wechat_notice')." ADD index(staffid) ;");
pdo_query("ALTER TABLE ".tablename('xcommunity_member_log')." ADD index(addressid) ;");