<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/14 0014
 * Time: 上午 10:24
 */
global $_GPC,$_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'company';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'company'){
    if ($p == 'list'){
        $list = pdo_getall('xcommunity_express_company', array('uniacid' => $_W['uniacid']), array(), '', 'createtime desc');
        include $this->template('web/plugin/express/company/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        if ($id) {
            $editData = pdo_get('xcommunity_express_company', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'company' => $_GPC['companyName'],
                'phone' => $_GPC['companyTel'],
                'logo' => $_GPC['picture'],
                'createtime' => time(),
            );
            $company = pdo_get('xcommunity_express_company', array('uniacid' =>$_W['uniacid'], 'company' => $_GPC['companyName']));
            if (!empty($id)) {
                $result = pdo_update('xcommunity_express_company', $data, array('id' => $id));
            } else {
                if(!empty($company)) {
                    echo json_encode(array('content'=>'公司已存在！'));exit();
                }
                $result = pdo_insert('xcommunity_express_company', $data);
            }
            if (!empty($result)) {
                echo json_encode(array('status'=>1));exit();
            }
        }
        include $this->template('web/plugin/express/company/add');
    }
    elseif($p == 'delete'){
        $id = intval($_GPC['id']);
        if ($id) {
            $result = pdo_delete('xcommunity_express_company', array('id' => $id));
            if (!empty($result)) {
                itoast('删除成功', referer(), 'error');
            }
        }
    }
}
elseif($op == 'type'){
    if ($p == 'list'){
        $list = pdo_getall('xcommunity_express_type', array('uniacid' => $_W['uniacid']), array(), '', 'createtime desc');
        include $this->template('web/plugin/express/type/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        if ($id) {
            $type = pdo_get('xcommunity_express_type', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type' => $_GPC['type'],
                'content' => $_GPC['content'],
                'createtime' => TIMESTAMP,
            );
            if (!empty($id)) {
                $result = pdo_update('xcommunity_express_type', $data, array('id' => $id));
            } else {
                $result = pdo_insert('xcommunity_express_type', $data);
            }
            if ($result) {
                echo json_encode(array('status'=>1));exit();
            }
        }
        include $this->template('web/plugin/express/type/add');
    }
    elseif($p == 'delete'){
        $id = intval($_GPC['id']);
        if ($id) {
            $result = pdo_delete('xcommunity_express_type', array('id' => $id));
            if (!empty($result)) {
                itoast('删除成功', referer(), 'error');
            }
        }
    }
}
elseif($op == 'collect'){
    if ($p == 'list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' t1.uniacid =:uniacid';
        $params['uniacid'] = $_W['uniacid'];
        $list = pdo_fetchall("SELECT t1.*,t2.company FROM " . tablename("xcommunity_express_collecting") . "t1 left join" . tablename('xcommunity_express_company') . " t2 on t2.id = t1.companyid WHERE $condition ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $tsql = 'SELECT COUNT(*) FROM ' . tablename("xcommunity_express_collecting") . " t1 WHERE $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/express/collect/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        $companys = pdo_getall('xcommunity_express_company', array('uniacid' => $_W['uniacid']));
        if ($id) {
            $collecting = pdo_get('xcommunity_express_collecting', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'name' => trim($_GPC['name']),
                'openid' => trim($_GPC['openid']),
                'mobile' => trim($_GPC['mobile']),
                'companyid' => $_GPC['companyid'],
                'createtime' => TIMESTAMP
            );
            if ($id) {
                if(pdo_update('xcommunity_express_collecting', $data, array('id' => $id))) {

                }
            } else {
                $data['uniacid'] = $_W['uniacid'];
                if(pdo_insert('xcommunity_express_collecting', $data)) {

                }
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/express/collect/add');
    }
    elseif($p == 'del'){
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_get('xcommunity_express_collecting', array('id' => $id), array());
        if (empty($item)) {
            itoast('数据不存在', referer(), 'error');
        }
        if (pdo_delete('xcommunity_express_collecting', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
}
elseif ($op == 'save'){
    if ($p == 'list'){
        $list = pdo_getall('xcommunity_express_save', array('uniacid' => $_W['uniacid']), array(), '', 'createtime desc');
        include $this->template('web/plugin/express/save/list');
    }
    elseif($p == 'edit'){
        $id = intval($_GPC['id']);
        if ($id) {
            $save = pdo_get('xcommunity_express_save', array('id' => $id));
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'company' => $_GPC['companyName'],
                'phone' => $_GPC['companyTel'],
                'logo' => $_GPC['picture'],
                'createtime' => time(),
            );
            $company = pdo_get('xcommunity_express_save', array('uniacid' =>$_W['uniacid'], 'company' => $_GPC['companyName']));
            if(!empty($company)) {
                itoast('公司已存在！', referer(),'error');exit();
            }
            if (!empty($id)) {
                $result = pdo_update('xcommunity_express_save', $data, array('id' => $id));
            } else {
                $result = pdo_insert('xcommunity_express_save', $data);
            }
            itoast('提交成功！', $this->createWebUrl('express',array('op' => 'save')),'success');exit();
        }
        include $this->template('web/plugin/express/save/edit');
    }
    elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $result = pdo_delete('xcommunity_express_save', array('id' => $id));
            if (!empty($result)) {
                itoast('删除成功', referer(), 'error');
            }
        }
    }
}
elseif($op == 'price'){
    if ($p =='list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' t1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $sql = "select t1.*,t2.company from " . tablename("xcommunity_express_piecework") . "t1 left join " . tablename('xcommunity_express_company') . " t2 on t2.id=t1.companyid where  $condition ORDER BY t1.companyid ASC,t1.typeid ASC,t1.mailaddress ASC,t1.receiveaddress LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_express_piecework") . "t1 left join " . tablename('xcommunity_express_company') . " t2 on t2.id=t1.companyid where $condition ", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/express/price/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        $companys = pdo_getall('xcommunity_express_company', array('uniacid' => $_W['uniacid']));
        if ($id) {
            $price = pdo_get('xcommunity_express_piecework', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'mailaddress' => $_GPC['mailaddress'],
                'receiveaddress' => $_GPC['receiveaddress'],
                'price' => $_GPC['price'],
                'overprice' => $_GPC['overprice'],
                'companyid' => $_GPC['companyid'],
            );
            if (!empty($id)) {
                $result = pdo_update('xcommunity_express_piecework', $data, array('id' => $id));
            } else {
                $data['uniacid'] = $_W['uniacid'];
                $result = pdo_insert('xcommunity_express_piecework', $data);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/express/price/add');
    }
    elseif($p == 'delete'){
        $id = intval($_GPC['id']);
        if ($id) {
            $result = pdo_delete('xcommunity_express_piecework', array('id' => $id));
            if (!empty($result)) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
elseif($op == 'parcel'){
    if ($p == 'list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' t1.uniacid=:uniacid and t2.status=1';
        $params[':uniacid'] = $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        //筛选
        if ($_GPC['sendphone']) {//寄件人电话
            $condition .= " AND t2.phone = {$_GPC['sendphone']}";
        }
        if ($_GPC['phone']) {//收件人电话
            $condition .= " AND t1.phone = {$_GPC['phone']}";
        }
        if ($_GPC['status']) {//物流状态
            $condition .= " AND t1.status = {$_GPC['status']}";
        }
        $birth = $_GPC['birth'];
        $starttime = strtotime($birth['start']);
        $endtime = strtotime($birth['end']);
        if ($starttime) {
            $condition .= " AND t1.createtime >= :start AND t1.createtime <= :end ";
            $params[':start'] = $starttime;
            $params[':end'] = $endtime;
        }
//        $company = pdo_getall('xcommunity_express_company', array('uniacid' => $_W['uniacid']));
        $sql = "SELECT t1.*,t2.realname,t2.phone as sendphone ,t2.area as sendaddress,t2.address as sendaddress_detail FROM " . tablename("xcommunity_express_parcel") . " t1 left join" . tablename('xcommunity_express_linkman') . " t2 on t2.uid = t1.uid where  $condition ORDER BY t1.status,t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_express_parcel") . " t1 left join" . tablename('xcommunity_express_linkman') . " t2 on t2.openid = t1.openid where $condition ", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/express/parcel/list');
    }
    elseif ($p == 'edit') {
        $parcel = pdo_get('xcommunity_express_parcel', array('id' => $_GPC['id']));
        $linkman = pdo_get('xcommunity_express_linkman', array('uniacid' => $_W['uniacid'], 'openid' => $parcel['openid']));
        if(checksubmit('submit')) {
            $data = array(
                'waybillcode' => $_GPC['waycode'],
                'status' => 2,
                'price' => $_GPC['price']
            );
            $par = pdo_get('xcommunity_express_parcel', array('waybillcode' => $_GPC['waycode']));
            if($par) {
                itoast('运单号已存在', referer(),'error');exit();
            }
            if(pdo_update('xcommunity_express_parcel', $data, array('id' => $_GPC['id']))) {
                itoast('处理成功！', $this->createWebUrl('express',array('op' => 'parcel')),'success');
            }
        }
        include $this->template('web/plugin/express/parcel/edit');
    }
}
elseif($op == 'tpl'){
    if (checksubmit('submit')) {
        foreach ($_GPC['tpl'] as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key,'uniacid'=>$_W['uniacid']), array('id'));
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
        itoast('操作成功', referer(), 'success',true);
        util::permlog('','修改模板消息库');
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/plugin/express/tpl');
}