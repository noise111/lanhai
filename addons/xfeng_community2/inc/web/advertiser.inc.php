<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/11/21 下午3:26
 */
global $_W,$_GPC;
$op = in_array(trim($_GPC['op']), array('set', 'manage', 'finance','guard','verity','del','pay')) ? trim($_GPC['op']) : 'set';
$p = in_array(trim($_GPC['p']), array('add', 'list')) ? trim($_GPC['p']) : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$options=array();
$options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
if($op =='set'){
    if (checksubmit('submit')) {
        foreach ($_GPC['set'] as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key,'uniacid' => $_W['uniacid']), array('id'));
            $data = array(
                'xqkey' => $key,
                'value' => $val,
                'uniacid' => $_W['uniacid']
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            } else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success');
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());

    include $this->template('web/plugin/advertiser/set');
}
elseif ($op =='manage'){
    if($p == 'add'){
        $sql = "select distinct t2.id,t2.title,t1.regionid from".tablename('xcommunity_building_device')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid=t2.id where t2.uniacid=:uniacid";
        $regions = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid']));

        if($_W['isajax']){
            $data = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $_W['uid'],
                'advtitle' => trim($_GPC['advtitle']),
                'advtime' => strtotime($_GPC['advtime']),
                'advday' => intval($_GPC['advday']),
                'area' => intval($_GPC['area']),
                'price' => trim($_GPC['price']),
                'advprice' => trim($_GPC['advprice']),
                'regionid' => $_GPC['regionid'],
                'link' => trim($_GPC['link']),
                'image' => tomedia(trim($_GPC['image'])),
                'createtime' => TIMESTAMP,
                'opentime' => intval($_GPC['opentime']),
                'num' => intval($_GPC['num'])
            );
            if($user){
                if($data['advprice']>$user['balance']){
                    echo json_encode(array('content'=>'账户余额不足，请先充值'.$data['adprice']));exit();
                }
            }
            if(pdo_insert('xcommunity_plugin_adv',$data)){
                $advid = pdo_insertid();
                if ($_GPC['deviceid']) {
                    foreach ($_GPC['deviceid'] as $key => $val) {
                        $dat = array(
                            'uniacid' => $_W['uniacid'],
                            'advid' => $advid,
                            'guardid' => $val,
                            'regionid' => $_GPC['regionid'],
                            'createtime' => TIMESTAMP
                        );
                        pdo_insert('xcommunity_plugin_adv_guard', $dat);
                    }
                }
                if($user) {
                    $d = array(
                        'balance' => $user['balance'] - $data['advprice'],
                        'account' => $data['advprice'],
                    );
                    pdo_update('xcommunity_users',$d,array('id' => $user['id']));
                }
                echo json_encode(array('status'=>1));exit();
            }
        }

        include $this->template('web/plugin/advertiser/add');
    }
    elseif($p =='list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if (!empty($_GPC['title'])) {
            $condition .= " AND advtitle LIKE '%{$_GPC['title']}%'";
        }
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!empty($_GPC['birth'])) {
            $starttime = strtotime($_GPC['birth']['start']);
            $endtime = strtotime($_GPC['birth']['end']) + 86399;
            $condition .= " AND createtime >= :starttime AND createtime <= :endtime ";
            $param[':starttime'] = $starttime;
            $param[':endtime'] = $endtime;
        }
        if ($user&&$user[type] != 1) {
            //普通管理员
            $condition .= " AND uid='{$_W['uid']}'";
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_plugin_adv') . "WHERE uniacid=:uniacid $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $param[':uniacid'] = $_W['uniacid'];
        $list = pdo_fetchall($sql,$param);
        $tsql =  "SELECT COUNT(*) FROM" . tablename('xcommunity_plugin_adv') . "WHERE uniacid=:uniacid $condition";
        $total = pdo_fetchcolumn($tsql,$param);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/advertiser/list');
    }

}elseif ($op =='finance'){
//    $usersql = "select * from".tablename('xcommunity_users')."where uid=:uid";
//    $user = pdo_fetch($usersql,array(':uid' => $_W['uid']));
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                $member = pdo_get('xcommunity_plugin_adv_data', array('id' => $id), array());
                if ($member) {
                    pdo_delete('xcommunity_plugin_adv_data', array('id' => $id));
                }
            }
            util::permlog('', '批量删除广告数据统计');
            itoast('删除成功', referer(), 'success', true);
        }
    }
    if($user){
        $totalprice = $user['balance']+$user['account'];
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';

    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime = time();
    }
    if (!empty($_GPC['birth'])) {
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']) + 86399;
        $condition .= " AND t1.createtime >= :starttime AND t1.createtime <= :endtime ";
        $param[':starttime'] = $starttime;
        $param[':endtime'] = $endtime;
    }
    if ($user&&$user[type] != 1) {
        //普通管理员
        $condition .= " AND t3.uid='{$_W['uid']}'";
    }
    $sql = "SELECT t1.*,t2.nickname,t3.advtitle FROM" . tablename('xcommunity_plugin_adv_data') . "t1 left join".tablename('mc_mapping_fans')."t2 on t1.openid=t2.openid left join".tablename('xcommunity_plugin_adv')."t3 on t3.id = t1.aid WHERE t1.uniacid=:uniacid $condition group by t1.id order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $param[':uniacid'] = $_W['uniacid'];
    $list = pdo_fetchall($sql,$param);

    $tsql = "SELECT count(*) FROM" . tablename('xcommunity_plugin_adv_data') . "t1 left join".tablename('mc_mapping_fans')."t2 on t1.openid=t2.openid left join".tablename('xcommunity_plugin_adv')."t3 on t3.id = t1.aid WHERE t1.uniacid=:uniacid $condition ";
    $total = pdo_fetchcolumn($tsql,$param);
    $pager = pagination($total, $pindex, $psize);
    if(checksubmit('submit')){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'ordersn' => 'LN'.date('YmdHi').random(10, 1),
            'createtime' => TIMESTAMP,
            'price'	=> $_GPC['price'],
            'uid' => $_W['uid'],
        );
        pdo_insert('xcommunity_plugin_adv_order', $data);
        $orderid = pdo_insertid();
        header("location: " . $this->createWebUrl('advertiser', array('op'=> 'pay','orderid' => $orderid)));
    }
    include $this->template('web/plugin/advertiser/finance');
}elseif($op == 'del'){
    $id = intval($_GPC['id']);
    if($id){
        if(pdo_delete('xcommunity_plugin_adv',array('id' => $id))){
            $result = array(
                'status' => 1,
            );
            echo json_encode($result);
            exit();
        }
    }
}elseif ($op =='verity'){
    if ($_W['isajax']) {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            exit('缺少参数');
        }
        $r = pdo_update('xcommunity_plugin_adv', array('status' => 1), array('id' => $id));
        if ($r) {
            $result = array(
                'status' => 1,
            );
            echo json_encode($result);
            exit();
        }
    }
}elseif($op =='guard'){
    $regionid = intval($_GPC['regionid']);
    $guard = pdo_getall('xcommunity_building_device',array('regionid'=>$regionid),array('id','title','unit'));
    echo json_encode(array('content' => $guard));exit();

}elseif($op =='pay'){
    $orderid = intval($_GPC['orderid']);
    if($orderid){
        $order = pdo_fetch("SELECT * FROM " . tablename('xcommunity_plugin_adv_order') . " WHERE id = :id", array(':id' => $orderid));
        $log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => 'xfeng_community', 'tid' => $order['ordersn']));
        if (empty($log)) {
            $log = array(
                'uniacid' => $_W['uniacid'],
                'module' => $this->module['name'], //模块名称，请保证$this可用
                'tid' => $order['ordersn'],
                'fee' => $order['price'],
                'card_fee' => $order['price'],
                'status' => '0',
                'is_usecard' => '0',
            );
            pdo_insert('core_paylog', $log);
        }
    }
    load()->model('payment');
    load()->func('communication');
    $ps = array();
    $ps['tid'] = $log['tid'];
    $ps['user'] = $_W['uid'];
    $ps['fee'] = $log['card_fee'];
    $ps['title'] = '广告主充值';
    $setting = uni_setting($_W['uniacid'], 'payment');
    dheader('location: '.xq_alipay_build($ps, $setting['payment']['alipay']));
}
