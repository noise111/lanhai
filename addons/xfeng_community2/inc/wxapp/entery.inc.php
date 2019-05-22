<?php
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'detail'){
    $id = intval($_GPC['id']);
    $category = intval($_GPC['category']);
    $item = pdo_fetch("select t1.id,t1.address,t3.title,t1.regionid from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_region')."t3 on t3.id=t1.regionid where t1.id=:id",array(':id' => $id));
    $staff = pdo_fetch("select t1.id from".tablename('xcommunity_fee_wechat')."t1 left join".tablename('xcommunity_fee_wechat_category')."t2 on t1.id=t2.fid where openid=:openid and regionid=:regionid and t2.categoryid=:cid",array(':openid' => $_W['openid'],':regionid' => $item['regionid'],':cid' => $category));
    if (empty($staff)){
        util::send_error(-1, '没有权限，请联系管理员');
    }
    $entery = pdo_fetch("select t1.id,t1.new_num from".tablename('xcommunity_fee')."t1 where t1.roomid=:address and categoryid=:category order by t1.createtime desc",array(':address' => $id,':category' => $category));
    $name = pdo_getcolumn('xcommunity_fee_category',array('id' => $category),'title');
    $data = array();
    $data['id'] = $item['id'];
    $data['address'] = $item['address'];
    $data['title'] = $item['title'];
    $data['old_num'] = $entery['new_num'] ? $entery['new_num'] : 0;
    $data['name'] = $name;
    $data['regionid'] = $item['regionid'];
    util::send_result($data);
}
elseif ($op == 'add'){
    $fee = pdo_getcolumn('xcommunity_fee_category',array('id' => intval($_GPC['category'])),'price');
    $staff = pdo_fetch("select realname from".tablename('xcommunity_fee_wechat')."t1 left join".tablename('xcommunity_fee_wechat_category')."t2 on t1.id=t2.fid where openid=:openid and regionid=:regionid and t2.categoryid=:cid",array(':openid' => $_W['openid'],':regionid' => $_GPC['regionid'],':cid' => intval($_GPC['category'])));
    if (empty($staff)){
        util::send_error(-1, '没有权限，请联系管理员');
    }
    $price = 0;
    $num = $_GPC['number'] -$_GPC['old_num'];
    if ($num > 0){
        $price = $num * $fee;
    }
    $data = array(
        'uniacid'   => $_W['uniacid'],
        'uid'       => $_W['member']['uid'],
        'roomid'   => trim($_GPC['address']),
        'categoryid'  => intval($_GPC['category']),
        'old_num'   => trim($_GPC['old_num']),
        'new_num'    => trim($_GPC['number']),
        'status'    => 1,
        'type'      => 2,
        'createtime'    => TIMESTAMP,
        'price'     => $price,
        'readername'  => $staff['realname']
    );
    if (pdo_insert('xcommunity_fee', $data)) {
        $data = array();
        $data['content'] = '提交成功';
        util::send_result($data);
    }else{
        util::send_error(-1, '参数错误');
    }
}