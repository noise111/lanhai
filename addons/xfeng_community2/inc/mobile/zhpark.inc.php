<?php
/**
 * Created by njxcommunity.com.
 * User: 蓝牛科技
 * Time: 2017/10/13 上午10:19
 */
global $_GPC,$_W;
$community = 'community' . $_W['uniacid'];
if ($_W['setting'][$community]['styleid'] == 'default') {
    if($op == 'index'){
        $title = '充值缴费';
        if(empty($_W['member']['uid'])){
            itoast('非法访问');exit();
        }
        $member = pdo_get('xcommunity_stop_park_cars',array('uid'=>$_W['member']['uid']),array('id','car_no','park_id'));
        if(empty($member)){
            $url = $this->createMobileUrl('zhpark',array('op'=> 'bind'));
            header('Location:'.$url);exit();
        }
        if($member['park_id']){
//    $park = pdo_get('xcommunity_stop_park',array('id' => $member['park_id']),array('park_id'));
            $sql = "select t2.company_id,t2.access_secret,t2.sign_key,t1.park_id from".tablename('xcommunity_stop_park')."t1 left join".tablename('xcommunity_stop_park_setting')."t2 on t1.parkid=t2.id where t1.id=:id";
            $park = pdo_fetch($sql,array(':id' => $member['park_id']));

            $token = getAccessToken($park['company_id'],$park['access_secret'],$park['sign_key']);

            $car = getOneCar($park['sign_key'],$park['park_id'],$token,$member['car_no']);
            if(empty($car)){
                itoast('网络异常',referer(),'error');exit();
            }
            $end_date = strtotime($car['valid_date'])+86400*30;
            $end_date = date('Y-m-d',$end_date);
        }

        include $this->template($this->xqtpl('zhpark/index'));
    }elseif($op == 'record'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 40;
        $condition = ' uniacid =:uniacid and uid=:uid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $_W['member']['uid'];

        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park_order") . " WHERE  $condition ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park_order") . " WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);


        include $this->template($this->xqtpl('zhpark/record'));
    }elseif($op == 'bind'){
        //绑定车辆
        $title = '绑定车辆';
        $member = pdo_get('xcommunity_stop_park_cars',array('uid'=>$_W['member']['uid']),array('id','car_no'));
        $p = in_array(trim($_GPC['p']),array('bind','unbid')) ? trim($_GPC['op']) : '';
        if($_W['isajax']){
            if($p =='bind'){
                $car_no = strtoupper(trim($_GPC['car_no']));
                $item = pdo_get('xcommunity_stop_park_cars',array('car_no'=> $car_no),array('id'));
                if(empty($item)){
                    echo json_encode(array('status' => 1,'content' => '系统中不存在该车牌号'));exit();
                }
                if(pdo_update('xcommunity_stop_park_cars',array('uid' => $_W['member']['uid'],'openid' => $_W['fans']['from_user']),array('id'=>$item['id']))){
                    echo json_encode(array('status' => 2,'content' => '绑定成功','carid' => $item['id']));exit();
                }

            }elseif($p =='unbind'){

            }
        }
        include $this->template($this->xqtpl('zhpark/bind'));
    }elseif($op =='detail'){
        include $this->template($this->xqtpl('zhpark/detail'));

    }elseif($op =='list'){
        include $this->template($this->xqtpl('zhpark/list'));

    }
}
else {
    $op = in_array(trim($_GPC['op']), array('list', 'detail','index')) ? trim($_GPC['op']) : 'index';
    $member = model_user::mc_check();
    if ($op == 'list') {
        include $this->template($this->xqtpl('plugin/zhpark/list'));
    }
    elseif ($op == 'detail') {
        include $this->template($this->xqtpl('plugin/zhpark/detail'));
    }
    elseif ($op == 'index') {
        include $this->template($this->xqtpl('plugin/zhpark/index'));
    }
}