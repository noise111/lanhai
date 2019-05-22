<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/12 下午10:22
 */
global $_W,$_GPC;
$op = in_array(trim($_GPC['op']),array('add','list','del','park','bind','import')) ? trim($_GPC['op']) : 'list';
$regions = model_region::region_fetall();
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if($op == 'list'){
    if($_W['isajax']){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'parkingid' => intval($_GPC['parkingid']),
            'realname' => trim($_GPC['realname']),
            'mobile' => trim($_GPC['mobile']),
            'car_num' => trim($_GPC['car_num'])
        );
        if(pdo_insert('xcommunity_xqcars',$data)){
            echo json_encode(array('status'=>1));exit();
        }
    }
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_xqcars', array('id' => $id));
            }
            util::permlog('','批量删除车');
            itoast('删除成功', referer(), 'success');
        }
    }
    if(checksubmit('wxmovecar')){
        $con = " t1.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if (empty($_GPC['ids'])){
            itoast('请先勾选车辆！', referer(), 'error');exit();
        }
        if ($_GPC['ids']) {
            $idss = implode(',',$_GPC['ids']);
            $ids = ltrim(rtrim($idss,','),',');
            $con .= " and t1.id in({$ids})";
        }
        $sql = "select t1.mobile,t1.car_num,t1.regionid,t5.uid,t5.openid,t6.title,t6.linkway from".tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('xcommunity_member_bind')."t3 on t2.id=t3.addressid left join".tablename('xcommunity_member')."t4 on t3.memberid=t4.id left join".tablename('mc_mapping_fans')."t5 on t4.uid=t5.uid left join".tablename('xcommunity_region')."t6 on t2.regionid=t6.id where $con order by t1.createtime desc";
        $members = pdo_fetchall($sql, $par);
        $url = '';
        if(set('t23')){
            foreach($members as $k => $v){
                $tplid = set('t24');
                $content = array(
                    'first' => array(
                        'value' => '您好，你有一条挪车提醒!',
                    ),
                    'keyword1' => array(
                        'value' => '系统',
                    ),
                    'keyword2' => array(
                        'value' => '您的爱车('.$v['car_num'].')挡住路啦，麻烦您给挪一下呗',
                    ),
                    'remark' => array(
                        'value' => '如有疑问，请咨询.'.$v['linkway'],
                    ),
                );
                if (!empty($v['openid'])) {
                    $ret = util::sendTplNotice($v['openid'], $tplid, $content, $url, '');
                    $d = array(
                        'uniacid' => $_W['uniacid'],
                        'sendid' => $v['id'],
                        'uid' => $v['uid'],
                        'type' => 7,
                        'cid' => 1,
                        'regionid'  => $v['regionid']
                    );
                    if (empty($ret['errcode'])) {
                        $d['status'] = 1;
                        pdo_insert('xcommunity_send_log', $d);
                    } else {
                        $d['status'] = 2;
                        pdo_insert('xcommunity_send_log', $d);
                    }
                }
            }
        }
        util::permlog('车辆管理-批量微信挪车',  '车辆ID:' . $ids);
        itoast('发送成功', referer(), 'success',true);
    }
    if(checksubmit('smsmovecar')){
        $con = " t1.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if (empty($_GPC['ids'])){
            itoast('请先勾选车辆！', referer(), 'error');exit();
        }
        if ($_GPC['ids']) {
            $idss = implode(',',$_GPC['ids']);
            $ids = ltrim(rtrim($idss,','),',');
            $con .= " and t1.id in({$ids})";
        }
        $sql = "select t1.mobile,t1.car_num,t1.regionid,t5.uid,t5.openid,t6.title,t6.linkway from".tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('xcommunity_member_bind')."t3 on t2.id=t3.addressid left join".tablename('xcommunity_member')."t4 on t3.memberid=t4.id left join".tablename('mc_mapping_fans')."t5 on t4.uid=t5.uid left join".tablename('xcommunity_region')."t6 on t1.regionid=t6.id where $con order by t1.createtime desc";
        $members = pdo_fetchall($sql, $par);
        if (set('s2') && set('s20')) {
            $type = set('s1');
            if($type ==1){
                $type =='wwt';
            }elseif($type ==2){
                $type = 'juhe';
                $tpl_id = set('s19');
            }else{
                $type = 'aliyun_new';
                $tpl_id = set('s26');
            }
            foreach ($members as $k => $member) {
                if ($type == 'wwt') {
                    $smsg = "您的爱车(".$member['car_num'].")挡住路啦，麻烦您给挪一下呗。如有疑问，请咨询." . $member['linkway'];
                } elseif ($type == 'juhe') {
                    $phone = $member['linkway'];
                    $car_num = $member['car_num'];
                    $smsg = urlencode("#phone#=$phone&#car_num#=$car_num");
                }else{
                    $smsg = json_encode(array('car_num' => $member['car_num'], 'linkway' => $member['linkway']));
                }
                $content = sms::send($member['mobile'], $smsg, $type, '', 1, $tpl_id);
                $d = array(
                    'uniacid' => $_W['uniacid'],
                    'sendid' => $member['id'],
                    'uid' => $member['uid'],
                    'type' => 7,
                    'cid' => 2,
                    'status' => 1,
                    'regionid' => $member['regionid'],
                );
                pdo_insert('xcommunity_send_log', $d);
            }
        } else {
            foreach ($members as $k => $member) {
                $type = set('x21', $member['regionid']);
                if($type ==1){
                    $type ='wwt';
                }elseif($type ==2){
                    $type = 'juhe';
                    $tpl_id = set('x60',$member['regionid']);
                }else{
                    $type = 'aliyun_new';
                    $tpl_id = set('x74',$member['regionid']);
                }
                if ($type == 'wwt') {
                    $smsg = "您的爱车(".$member['car_num'].")挡住路啦，麻烦您给挪一下呗，如有疑问，请咨询。" . $member['linkway'];
                } elseif ($type == 'juhe') {
                    $phone = $member['linkway'];
                    $smsg = urlencode("#phone#=$phone");
                }else{
                    $smsg = json_encode(array('phone' => $member['linkway']));
                }
                $content = sms::send($member['mobile'], $smsg, $type, $member['regionid'], 2, $tpl_id);
                $d = array(
                    'uniacid' => $_W['uniacid'],
                    'sendid' => $member['id'],
                    'uid' => $member['uid'],
                    'type' => 7,
                    'cid' => 2,
                    'status' => 1,
                    'regionid' => $member['regionid'],
                    'createtime' => TIMESTAMP
                );
                pdo_insert('xcommunity_send_log', $d);
            }
        }
        util::permlog('车辆管理-批量短信挪车', '车辆ID:' . $ids);
        itoast('发送成功', referer(), 'success',true);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    if (intval($_GPC['regionid'])){
        $condition .= " and t1.regionid ={$_GPC['regionid']}";
    }
    if (!empty($_GPC['car_num'])){
        $condition .= " and t1.car_num='{$_GPC['car_num']}'";
    }
    if (!empty($_GPC['realname'])){
        $condition .= " and t1.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['mobile'])){
        $condition .= " and t1.mobile LIKE '%{$_GPC['mobile']}%'";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "select t1.*,t2.title,t3.place_num from".tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking')."t3 on t3.id = t1.parkingid where $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking')."t3 on t3.id = t1.parkingid where $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/xqcar/list');
}
elseif($op == 'add'){
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_xqcars',array('id' => $id),array());
        $parks = pdo_getall('xcommunity_parking',array('regionid' => $item['regionid']),array());
    }
    if($_W['isajax']){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'parkingid' => intval($_GPC['parkingid']),
            'realname' => trim($_GPC['realname']),
            'mobile' => trim($_GPC['mobile']),
            'car_num' => trim($_GPC['car_num'])
        );
        if($id){
            pdo_update('xcommunity_xqcars',$data,array('id'=>$id));
        }else{
            pdo_insert('xcommunity_xqcars',$data);
        }
        echo json_encode(array('status'=>1));exit();
    }
    include $this->template('web/core/xqcar/add');
}elseif ($op == 'del'){
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_xqcars',array('id' => $id),array());
    }
    if($item){
        if(pdo_delete('xcommunity_xqcars',array('id'=> $id))){
            itoast('删除成功',referer(),'success');
        }
    }
}elseif ($op =='park'){
    if($_W['isajax']){
        $regionid = intval($_GPC['regionid']);
        $parks = pdo_getall('xcommunity_parking',array('regionid' => $regionid),array('id','place_num'));
        if($parks){
            echo json_encode($parks);exit();
        }
    }
}elseif($op =='bind'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        itoast('缺少参数',referer(),'error');
    }
    $item = pdo_get('xcommunity_xqcars',array('id'=>$id),array('regionid','addressid'));
    if($item){
        $sql = "select t1.buildtitle,t1.id,t2.title from".tablename('xcommunity_build')."t1 left join".tablename('xcommunity_area')."t2 on t1.areaid = t2.id where t1.regionid=:regionid";
        $builds = pdo_fetchall($sql,array(':regionid'=>$item['regionid']));
    }
    if(checksubmit('submit')){
        if(pdo_update('xcommunity_xqcars',array('addressid'=> intval($_GPC['addressid'])),array('id'=> $id))){
            itoast('绑定成功',$this->createWebUrl('xqcar'),'success');
        }
    }
    include $this->template('web/core/xqcar/bind');
}elseif($op =='import'){
    $condition = '';
    if ($user[type] == 3) {
        $condition = "and id in ({$user['regionid']})";
    }
    $regions = model_region::region_fetall($condition);
    if ($_W['isajax']) {

        $rows = model_execl::read('car');
        if ($rows[0][0] != '姓名' || $rows[0][1] != '手机' || $rows[0][2] != '车牌' || $rows[0][3] != '车位'){
            echo json_encode(array('content' => '文件内容不符，请重新上传'));exit();
        }
        foreach ($rows as $rownum => $col) {
            if ($rownum > 0) {
                if ($col[2]) {
                    $data = array(
                        'realname'  => trim($col[0]),
                        'mobile'       => trim($col[1]),
                        'car_num'     => trim($col[2]),
                        'createtime' => TIMESTAMP,
                        'regionid'   => intval($_GPC['regionid']),
                        'uniacid'    => $_W['uniacid'],
                    );
                    $parkingid = pdo_getcolumn('xcommunity_parking',array('regionid' => $_GPC['regionid'],'place_num' => trim($col[3])),'id');
                    if ($parkingid){
                        $data['parkingid'] = $parkingid;
                    }else{
                        $dat = array(
                            'place_num'  => trim($col[3]),
                            'area'       => '',
                            'status'     => trim($col[9]),
                            'remark'     => trim($col[10]),
                            'createtime' => TIMESTAMP,
                            'regionid'   => intval($_GPC['regionid']),
                            'uniacid'    => $_W['uniacid'],
                        );
                        pdo_insert('xcommunity_parking', $dat);
                        $parkingid = pdo_insertid();
                        $data['parkingid'] = $parkingid;
                    }
                    $addrid = pdo_getcolumn('xcommunity_member_room',array('address' => trim($col[4])),'id');
                    if ($addrid){
                        $data['addressid'] = $addrid;
                    }
                    pdo_insert('xcommunity_xqcars', $data);
                    $carid = pdo_insertid();
                    $da = array(
                        'uniacid' => $_W['uniacid'],
                        'parkingid' => $parkingid,
                        'realname' => trim($col[0]),
                        'mobile' => trim($col[1]),
                        'xqcarid' => $carid,
                        'starttime' => strtotime(trim($col[5])),
                        'endtime' => strtotime(trim($col[6])),
                        'price' => trim($col[7]),
                        'cycle' => trim($col[8]),
                        'remark' => trim($col[10]),
                        'createtime' => TIMESTAMP
                    );
                    pdo_insert('xcommunity_parking_record', $da);
                    util::permlog('车辆管理-添加', '添加车辆信息ID:' . $carid);
                }
            }

        }
        util::permlog('', '导入车位信息');
        echo json_encode(array('result' => 1, 'content' => '导入完成！'));
        exit();
    }
    include $this->template('web/core/xqcar/import');
}