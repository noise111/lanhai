<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/7/19 0019
 * Time: 上午 9:48
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
$user = util::xquser($_W['uid']);
$regions = model_region::region_fetall();
if ($op == 'list') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $export = intval($_GPC['export']); //是导出还是正常展示
        $psize = $export == 1 ? 1000000 : 20; //debug 临时设置导出
        $condition = "t1.uniacid=:uniacid and t2.realname <> '' and t2.mobile <> ''";
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = trim($_GPC['keyword']);
        if ($user && $user[type] == 3) {
            //普通管理员
            $condition .= " and t1.regionid in({$user['regionid']})";
        }
        if(intval($_GPC['regionid'])){
            $condition .= " and t1.regionid=:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if($keyword){
            $condition .= " and (t2.mobile like :keyword or t2.realname like :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }
        $total1 = 0;
        $total2 = 0;
        $sql = "SELECT t1.*,t2.realname,t4.openid,t2.mobile,t3.title,t2.credit1,t2.credit2 FROM" . tablename('xcommunity_member') . "t1 LEFT JOIN".tablename('mc_members')."t2 on t1.uid=t2.uid LEFT JOIN".tablename('xcommunity_region')."t3 on t1.regionid=t3.id LEFT JOIN".tablename('mc_mapping_fans')."t4 on t1.uid=t4.uid WHERE $condition order by t1.uid desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $key => $val){
            $list[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);
        }
        if ($export) {
            model_execl::export($list, array(
                "title"   => "会员数据-" . date('Y-m-d', time()),
                "columns" => array(
                    array(
                        'title' => 'UID',
                        'field' => 'uid',
                        'width' => 24
                    ),
                    array(
                        'title' => '姓名',
                        'field' => 'realname',
                        'width' => 24
                    ),
                    array(
                        'title' => '手机',
                        'field' => 'mobile',
                        'width' => 24
                    ),
                    array(
                        'title' => 'openid',
                        'field' => 'openid',
                        'width' => 20
                    ),
                    array(
                        'title' => '小区',
                        'field' => 'title',
                        'width' => 20
                    ),
                    array(
                        'title' => '积分',
                        'field' => 'credit1',
                        'width' => 20
                    ),
                    array(
                        'title' => '余额',
                        'field' => 'credit2',
                        'width' => 24
                    ),
                    array(
                        'title' => '创建时间',
                        'field' => 'createtime',
                        'width' => 24
                    )
                )
            ));
            unset($list);
//            $url = $this->createWebUrl('guard', array('op' => 'comb', 'p' => 'list', 'star' => $lastid, 'regionid' => $regionid, 'keyword' => $keyword, 'page' => $pindex++));
//            message('正在发送导出下一组！', $url, 'success');
        }
        $lists= pdo_fetchall("SELECT distinct t1.uid,t2.credit1,t2.credit2 FROM" . tablename('xcommunity_member') . "t1 LEFT JOIN".tablename('mc_members')."t2 on t1.uid=t2.uid LEFT JOIN".tablename('xcommunity_region')."t3 on t1.regionid=t3.id LEFT JOIN".tablename('mc_mapping_fans')."t4 on t1.uid=t4.uid WHERE $condition", $params);
        foreach ($lists as $k => $v){
            $total1 += $v['credit1'];
            $total2 += $v['credit2'];
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_member') . "t1 LEFT JOIN".tablename('mc_members')."t2 on t1.uid=t2.uid LEFT JOIN".tablename('xcommunity_region')."t3 on t1.regionid=t3.id LEFT JOIN".tablename('mc_mapping_fans')."t4 on t1.uid=t4.uid WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        if ($user && $user[type] == 2) {
            //普通管理员
            $list = array();
            $total1 = 0;
            $total2 = 0;
            $total = 0;
        }
        include $this->template('web/core/address/list');
    }
}
elseif ($op == 'address') {
    if ($operation == 'list') {
        if ($_W['ispost']) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_member_address', array('id' => $id));
                }
                itoast('删除成功', referer(), 'success', ture);
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = trim($_GPC['keyword']);
        if($keyword){
            $condition .= " and (mobile=:keyword or realname=:keyword)";
            $params[':keyword'] = $keyword;
        }
        $address = trim($_GPC['address']);
        if($address){
            $condition .= " and address like :address";
            $params[':address'] = "%{$address}%";
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_member_address') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_member_address') . "WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/address/address/list');
    }
    elseif ($operation == 'del') {
        $id = $_GPC['id'];
        if ($id) {
            $item = pdo_get('xcommunity_member_address', array('id' => $id), array('id'));
            if ($item) {
                if (pdo_delete('xcommunity_member_address', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                }
            }
        }
    }
    elseif ($operation == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_member_address', array('id' => $id), array());
            $birth = explode(' ', $item['city']);
            $item['province'] = $birth[0];
            $item['city'] = $birth[1];
            $item['dist'] = $birth[2];
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth']['province'] . ' ' . $_GPC['birth']['city'] . ' ' . $_GPC['birth']['district'];
            $data = array(
                'realname'   => $_GPC['realname'],
                'mobile'     => $_GPC['mobile'],
                'createtime' => TIMESTAMP,
                'address'    => $_GPC['address'],
                'city'       => $birth,
            );
            if (!empty($id)) {
                pdo_update('xcommunity_member_address', $data, array('id' => $id));
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/core/address/address/add');
    }
}
elseif ($op == 'search') {
    if ($_W['isajax']) {
        $words = trim($_GPC['words']);
        $condition = "t1.uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        if ($words) {
            $condition .= " and (t1.mobile like :keyword or t1.realname like :keyword or t1.nickname like :keyword)";
            $params[':keyword'] = "%{$words}%";
        }
        $item = pdo_fetch("select t2.openid from" . tablename('mc_members') . "t1 left join".tablename('mc_mapping_fans')."t2 on t1.uid=t2.uid where $condition order by t1.uid desc", $params);
        echo json_encode(array('err_code' => 0,'openid' => $item['openid']));exit();
    }
}
elseif ($op == 'log') {
    $uid = intval($_GPC['uid']);
    $type = intval($_GPC['type']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = "uniacid=:uniacid and uid=:uid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $uid;
    if ($type == 1){
        $condition .= " and credittype=:credittype";
        $params[':credittype'] = 'credit1';
    }elseif ($type == 2){
        $condition .= " and credittype=:credittype";
        $params[':credittype'] = 'credit2';
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " and createtime between '{$starttime}' and '{$endtime}'";
    }
    $sql = "SELECT * FROM" . tablename('mc_credits_record') . " WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('mc_credits_record') . " WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/address/log');
}
elseif ($op == 'set') {

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
        include $this->template('web/core/address/credit/set');

}