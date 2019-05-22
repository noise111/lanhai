<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/7 0007
 * Time: 上午 11:32
 */
global $_GPC,$_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$user = util::xquser($_W['uid']);
if ($op == 'property'){
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize  = 20;
        $condition = 'uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        if ($user&&$user[type] == 3) {
            //普通管理员
            $sql = "select pid from".tablename('xcommunity_region')." where id in({$user['regionid']})";
            $regions = pdo_fetchall($sql);
            $pids = '';
            foreach ($regions as $k => $v){
                $pids .= $v['pid'].',';
            }
            $pids = rtrim(ltrim($pids,','),',');
            $condition .= " AND id in({$pids})";
        }
        if ($user&&in_array($user[type],array(2,4,5))) {
            //普通管理员
            $condition .= " AND uid='{$_W['uid']}'";
        }
        $list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_property')."WHERE $condition LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_property')."WHERE $condition",$params);
        $pager  = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/property/list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT * FROM".tablename('xcommunity_property')."WHERE uniacid=:uniacid AND id=:id",array(":uniacid" => $_W['uniacid'],":id" => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除',referer(),'error',ture);
            }
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => $_GPC['title'],
                'thumb' => $_GPC['thumb'],
                'content' => htmlspecialchars_decode($_GPC['content']),
                'createtime' => TIMESTAMP,
                'telphone' => $_GPC['telphone'],
                'mark'  => $_GPC['mark'],
                'company'  => $_GPC['company'],
                'realname'  => $_GPC['realname'],
                'mobile'  => $_GPC['mobile'],
                'qq'  => $_GPC['qq'],
                'manager'  => $_GPC['manager'],
                'manager_mobile'  => $_GPC['manager_mobile'],
                'address'  => $_GPC['address'],
                'reg_address'  => $_GPC['reg_address'],
                'code'  => $_GPC['code'],
                'staff'  => $_GPC['staff'],
                'reg_content'  => $_GPC['reg_content'],
                'type'  => $_GPC['type'],
                'license'  => $_GPC['license'],
                'total'  => $_GPC['total'],
            );
            $pid = pdo_get('xcommunity_property',array('title' => $_GPC['title'],'uniacid' => $_W['uniacid']),'id');
            if ($id) {
                $ptitle = pdo_fetchcolumn("SELECT title FROM".tablename('xcommunity_property')."WHERE uniacid=:uniacid AND id=:id",array(":uniacid" => $_W['uniacid'],":id" => $id));
                if ($ptitle != $_GPC['title']){
                    if ($pid){
                        itoast('该物业已经存在，请勿重复添加！',referer(),'error',ture);
                    }
                }
                pdo_update('xcommunity_property',$data,array("id" => $id));
                util::permlog('物业管理-修改','物业名称:'.$data['title']);
            }else{
                if ($pid){
                    itoast('该物业已经存在，请勿重复添加！',referer(),'error',ture);
                }
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_property',$data);
                $id = pdo_insertid();
                util::permlog('物业管理-添加','添加物业信息ID:'.$id.'物业名称:'.$data['title']);
            }
            itoast('提交成功',referer(), 'success',ture);
        }
        load()->func('tpl');
        include $this->template('web/plugin/rank/property/add');
    }
    elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数',referer(),'error');
            }
            $item = pdo_fetch("SELECT id,thumb,title FROM".tablename('xcommunity_property')."WHERE uniacid=:uniacid AND id=:id",array(':id' => $id,':uniacid'=>$_W['uniacid']));
            if (empty($item)) {
                itoast('该物业不存在或已被删除',referer(),'error',ture);
            }
            $r = pdo_delete("xcommunity_property",array('id' => $id));
            if ($r) {
                util::permlog('物业信息-删除','删除物业名称:'.$item['title']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);exit();
            }
        }
    }
}
elseif($op == 'category'){
    if($p == 'list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize  = 20;
        $condition = 'uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }

        $list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_rank_category')."WHERE $condition LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_rank_category')."WHERE $condition",$params);
        $pager  = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/category/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT * FROM".tablename('xcommunity_rank_category')."WHERE uniacid=:uniacid AND id=:id",array(":uniacid" => $_W['uniacid'],":id" => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除',referer(),'error',ture);
            }
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => $_GPC['title'],
                'content' => htmlspecialchars_decode($_GPC['content']),
                'createtime' => TIMESTAMP,
            );
            if ($id) {
                pdo_update('xcommunity_rank_category',$data,array("id" => $id));
            }else{
                pdo_insert('xcommunity_rank_category',$data);
            }
            itoast('提交成功',referer(), 'success',ture);
        }
        include $this->template('web/plugin/rank/category/add');
    }
    elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数',referer(),'error');
            }
            $item = pdo_fetch("SELECT id,title FROM".tablename('xcommunity_rank_category')."WHERE uniacid=:uniacid AND id=:id",array(':id' => $id,':uniacid'=>$_W['uniacid']));
            if (empty($item)) {
                itoast('该信息不存在或已被删除',referer(),'error',ture);
            }

            $r = pdo_delete("xcommunity_rank_category",array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);exit();
            }
        }
    }
}
elseif($op == 'manage'){
    if($p == 'list'){
        $condition = " t1.uniacid=:uniacid";
        $parms[':uniacid'] =  $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title as ptitle FROM" . tablename('xcommunity_rank_notice') . "t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_rank_notice') . "t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/manage/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        $d = '';
        if ($user) {
            if ($user&&$user[type] == 3) {
                //普通管理员
                $sql = "select pid from".tablename('xcommunity_region')." where id in({$user['regionid']})";
                $regions = pdo_fetchall($sql);
                $pids = '';
                foreach ($regions as $k => $v){
                    $pids .= $v['pid'].',';
                }
                $pids = rtrim(ltrim($pids,','),',');
                $d .= " AND id in({$pids})";
            }
            if ($user&&in_array($user[type],array(2,4,5))) {
                //普通管理员
                $d .= " AND uid='{$_W['uid']}'";
            }
        }
        $properties = model_region::property_fetall($d);
        if ($id) {
            $item = pdo_get('xcommunity_rank_notice', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'enable'    => $_GPC['enable'],
                'type'      => $_GPC['type'],
                'realname'  => trim($_GPC['realname']),
                'mobile' => trim($_GPC['mobile']),
                'openid'    => trim($_GPC['openid']),
                'pid'       => intval($_GPC['pid'])
            );
            if (empty($item['id'])) {
                pdo_insert('xcommunity_rank_notice', $data);
            }
            else {
                pdo_update('xcommunity_rank_notice', $data, array('id' => $id));
            }
            itoast('添加成功', $this->createWebUrl('rank', array('op' => 'manage')), 'success', true);
        }
        include $this->template('web/plugin/rank/manage/add');
    }
    elseif($p == 'del'){
        $id = intval($_GPC['id']);
        $notice = pdo_get('xcommunity_rank_notice',array('id'=> $id),array());
        if($notice){
            if (pdo_delete('xcommunity_rank_notice', array('id' => $id))) {
                util::permlog('物业接收员-删除','信息标题:'.$notice['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($p == 'verify') {
        //审核用户
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_rank_notice", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}
elseif($op == 'mark'){
    $d = '';
    if ($user) {
        if ($user&&$user[type] == 3) {
            //普通管理员
            $sql = "select pid from".tablename('xcommunity_region')." where id in({$user['regionid']})";
            $regions = pdo_fetchall($sql);
            $pids = '';
            foreach ($regions as $k => $v){
                $pids .= $v['pid'].',';
            }
            $pids = rtrim(ltrim($pids,','),',');
            $d .= " AND id in({$pids})";
        }
        if ($user&&in_array($user[type],array(2,4,5))) {
            //普通管理员
            $d .= " AND uid='{$_W['uid']}'";
        }
    }
    $properties = model_region::property_fetall($d);
    if($p == 'list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize  = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
        }
        if ($_GPC['export'] == 1) {
            $sql = "SELECT t1.*,t2.title as ptitle FROM".tablename('xcommunity_rank_property')."t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition order by t1.createtime desc";
            $xqlist = pdo_fetchall($sql, $params);
            if ($_GPC['export'] == 1) {
                foreach ($xqlist as $k => $v) {
                    $xqlist[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
                }
                model_execl::export($xqlist, array(
                    "title" => "评分管理数据-" . date('Y-m-d-H-i', time()),
                    "columns" => array(
                        array(
                            'title' => 'ID',
                            'field' => 'id',
                            'width' => 12
                        ),
                        array(
                            'title' => '打分标题',
                            'field' => 'title',
                            'width' => 20
                        ),
                        array(
                            'title' => '物业名称',
                            'field' => 'ptitle',
                            'width' => 40
                        ),
                        array(
                            'title' => '评分',
                            'field' => 'mark',
                            'width' => 40
                        ),
                        array(
                            'title' => '创建时间',
                            'field' => 'createtime',
                            'width' => 20
                        ),
                    )
                ));
            }

        }
        $list = pdo_fetchall("SELECT t1.*,t2.title as ptitle FROM".tablename('xcommunity_rank_property')."t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition order by t1.createtime desc LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_rank_property')."t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition",$params);
        $pager  = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/mark/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        $categorys = pdo_getall('xcommunity_rank_category',array('uniacid' => $_W['uniacid']),array('id','title'));
        foreach ($categorys as $key => $val){
            if ($id){
                $categorys[$key]['mark'] = pdo_getcolumn('xcommunity_rank_mark',array('rid' => $id,'cid' => $val['id']),'mark');
            }else{
                $categorys[$key]['mark'] = '';
            }
        }
        if ($id) {
            $item = pdo_fetch("select t1.*,t2.mark as pmark from".tablename('xcommunity_rank_property')."t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id where t1.id=:id", array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'title'    => $_GPC['title'],
                'pid'       => intval($_GPC['pid']),
                'createtime'    => TIMESTAMP
            );
            if (empty($item['id'])) {
                pdo_insert('xcommunity_rank_property', $data);
                $id = pdo_insertid();
            }
            else {
                pdo_update('xcommunity_rank_property', $data, array('id' => $id));
                pdo_delete('xcommunity_rank_mark',array('rid' => $id));
            }
            $pmark = pdo_getcolumn('xcommunity_property',array('id' => intval($_GPC['pid'])),'mark');
            $base = $_GPC['pmark'] ? intval($_GPC['pmark']) :  $pmark;
            $mark = $_GPC['mark'];
            $nums = '';
            foreach ($mark as $k => $v){
                $dat = array(
                    'rid'   => $id,
                    'cid'   => $k,
                    'mark'  => $v
                );
                pdo_insert('xcommunity_rank_mark',$dat);
                $nums += $v;
            }
            pdo_update('xcommunity_rank_property', array('mark' => $nums+$base), array('id' => $id));
            pdo_update('xcommunity_property', array('mark' => $nums+$base), array('id' => intval($_GPC['pid'])));
            itoast('添加成功', $this->createWebUrl('rank', array('op' => 'mark')), 'success', true);
        }
        include $this->template('web/plugin/rank/mark/add');
    }
    elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($_W['isajax']) {
            if (empty($id)) {
                itoast('缺少参数',referer(),'error');
            }
            $item = pdo_fetch("SELECT id,title FROM".tablename('xcommunity_rank_property')."WHERE uniacid=:uniacid AND id=:id",array(':id' => $id,':uniacid'=>$_W['uniacid']));
            if (empty($item)) {
                itoast('该物业不存在或已被删除',referer(),'error',ture);
            }

            $r = pdo_delete("xcommunity_rank_property",array('id' => $id));
            if ($r) {
                util::permlog('物业评价-删除','删除评价名称:'.$item['title']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);exit();
            }
        }
    }
    elseif($p == 'send'){
        $id = intval($_GPC['id']);
        if($id){
            $list = pdo_fetchall("select t1.id,t1.realname from".tablename('xcommunity_rank_notice')."t1 left join".tablename('xcommunity_rank_property')."t2 on t1.pid=t2.pid where t2.id=:id",array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $item = pdo_fetch("SELECT t1.*,t2.title as ptitle FROM" . tablename('xcommunity_rank_property') . "t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE t1.id=:id",array(':id' => $id));
            $notice = pdo_get('xcommunity_rank_notice',array('id' => intval($_GPC['manageid']),'enable' => 1));
            if ($notice['type'] == 1 || $notice['type'] == 3){
                $content = array(
                    'first'    => array(
                        'value' => '物业评价通知',
                    ),
                    'name' => array(
                        'value' => "物业：".$item['ptitle']."--评价",
                    ),
                    'result' => array(
                        'value' => $item['title']."评价分：".$item['mark']."（点击查看证书）",
                    ),
                );
                if (set('t25')) {
                    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=detail&id={$item['id']}&do=rank&m=" . $this->module['name'];
                    $ret = util::sendTplNotice($notice['openid'], set('t26'), $content, $url, '');
                    $result = json_decode($ret['content'], true);
                    $error_code = $result['errcode'];
                }
                util::permlog('', '评价信息推送,推送内容:' . $content['result']);
            }
            if($notice['type'] == 2 || $notice['type'] == 3){
                if (set('s2')) {
                    $type = set('s1');
                    if($type ==1){
                        $type ='wwt';
                    }elseif($type ==2){
                        $type = 'juhe';
                        $tpl_id = set('x64');
                    }else{
                        $type = 'aliyun_new';
                        $tpl_id = set('s28');
                    }
                    $api = 1;
                    if ($type == 'wwt') {
                        $smsg = '新物业评价通知,标题:' . $item['title'] . ',人员姓名:' . $notice['realname'] . ',评价分:' . $item['mark'];
                    } elseif ($type == 'juhe') {
                        $title = $item['title'];
                        $realname = $notice['realname'];
                        $mark = $item['mark'];
                        $smsg = urlencode("#title#=$title&#realname#=$realname&#mark#=mark&");
                    }else{
                        $smsg = json_encode(array('title' => $item['title'],'realname' => $notice['realname'],'mark' => $item['mark']));
                    }
                    if ($notice['mobile']) {
                        $re = sms::send($notice['mobile'], $smsg, $type, '', $api, $tpl_id);
                    }
                }
            }
            itoast('推送成功', referer(), 'success', true);
        }

        include $this->template('web/plugin/rank/mark/send');
    }
    elseif($p == 'change'){
        if ($_W['isajax']){
            $pid = intval($_GPC['pid']);
            $mark = pdo_getcolumn('xcommunity_property',array('id' => $pid),'mark');
            echo json_encode(array('mark' => $mark));exit();
        }
    }
}
elseif($op == 'region'){
    $id = intval($_GPC['id']);
    if ($p == 'add') {
        if (empty($id)) {
            $uid = $user['uuid'] && $user['uuid'] != 1 ? $user['uuid'] : $_W['uid'];
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_region') . "WHERE uniacid=:uniacid and uid=:uid", array(':uid' => $uid, ':uniacid' => $_W['uniacid']));
            $xquser = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "as u left join" . tablename('xcommunity_users_group') . "as g on u.groupid = g.id WHERE u.uid=:uid", array(':uid' => $uid));
            if ($xquser) {
                $groupid = $xquser['groupid'];
                $maxaccount = $xquser['maxaccount'];
            }
            if ($groupid) {
                if ($total >= $maxaccount) {
                    itoast("已经达到添加小区上限", $this->createWebUrl('region', array('op' => 'list')), 'success', ture);
                    exit();
                }
            }
            //兼容云平台小区限制
            $u = pdo_get('select maxregion from' . tablename('users') . "t1 left join" . tablename('users_group') . "t2 on t1.groupid=t2.id where t1.uid=:uid", array(':uid' => $_W['uid']));
            if (!empty($u)) {
                if ($total >= $u['maxregion']) {
                    itoast("已经达到添加小区上限", $this->createWebUrl('region', array('op' => 'list')), 'success', ture);
                    exit();

                }
            }
        }
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_region') . "WHERE uniacid=:uniacid AND id=:id", array(":id" => $id, ":uniacid" => $_W['uniacid']));
            $project = pdo_get('xcommunity_region_data',array("regionid" => $id));
            $project_city = explode(' ',$project['project_city']);
            if (empty($item)) {
                itoast('不存在该小区信息或已删除', referer(), 'error');
            }
        }
        $d = '';
        if ($user && $_W['uid'] != 1) {
            if ($user['type'] == 2) {
                $d = " and uid ={$_W['uid']}";
            }
            elseif($user['type'] == 3) {
                $d = " and id={$user['pid']}";
            }
        }
        $propertys = model_region::property_fetall($d);
        if (checksubmit('submit')) {
            $reside = $_GPC['reside'];
            $project_city = $_GPC['project_city']['province'].' '.$_GPC['project_city']['city'].' '.$_GPC['project_city']['district'];
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'title'    => $_GPC['title'],
                'linkmen'  => $_GPC['linkmen'],
                'linkway'  => $_GPC['linkway'],
                'lng'      => $_GPC['baidumap']['lng'],
                'lat'      => $_GPC['baidumap']['lat'],
                'address'  => $_GPC['address'],
                'url'      => $_GPC['url'],
                'thumb'    => $_GPC['thumb'],
                'qq'       => $_GPC['qq'],
                'province' => $reside['province'],
                'city'     => $reside['city'],
                'dist'     => $reside['district'],
                'pic'      => $_GPC['pic'],
                'keyword'  => $_GPC['keyword'],
                'pid'      => intval($_GPC['pid']),

            );
            $da = array(
                'uniacid'  => $_W['uniacid'],
                'project_city'  => $project_city,
                'project'  => $_GPC['project'],
                'pact'  => $_GPC['pact'],
                'manager'  => $_GPC['manager'],
                'manager_mobile'  => $_GPC['manager_mobile'],
                'project_square'  => $_GPC['project_square'],
                'house_total'  => $_GPC['house_total'],
                'build_total'  => $_GPC['build_total'],
                'other_time'  => strtotime($_GPC['other_time']),
                'income'  => $_GPC['income'],
                'house_square'  => $_GPC['house_square'],
                'house_pfee'  => $_GPC['house_pfee'],
                'house_nsquare'  => $_GPC['house_nsquare'],
                'house_npfee'  => $_GPC['house_npfee'],
                'park_price'  => $_GPC['park_price'],
                'park_fee'  => $_GPC['park_fee'],
                'lift_total'  => $_GPC['lift_total'],
                'lift_ttotal'  => $_GPC['lift_ttotal'],
                'psquare'  => $_GPC['psquare'],
                'park_total'  => $_GPC['park_total'],
                'park_common'  => $_GPC['park_common'],
                'camera_total'  => $_GPC['camera_total'],
                'alarm'  => $_GPC['alarm'],
                'plug'  => $_GPC['plug'],
                'pname'  => $_GPC['pname'],
                'owner_total'  => $_GPC['owner_total'],
                'owner_name'  => $_GPC['owner_name'],
                'owner_mobile'  => $_GPC['owner_mobile'],
                'water'  => $_GPC['water'],
                'electric'  => $_GPC['electric'],
                'type'  => $_GPC['type'],
            );
            $ru = pdo_get('rule', array('name' => $_GPC['title']), array('id'));
            if (empty($ru)) {
                $rule = array(
                    'uniacid' => $_W['uniacid'],
                    'name'    => $_GPC['title'],
                    'module'  => 'cover',
                    'status'  => 1,
                );
                $result = pdo_insert('rule', $rule);
                $rid = pdo_insertid();
            }
            else {
                $rid = $ru['id'];
            }
            if ($id) {
                $data['rid'] = $ru['id'];
                pdo_update("xcommunity_region", $data, array('id' => $id));
                $regionid = $id;
                util::permlog('小区管理-修改', '修改小区ID:' . $regionid . '修改名称:' . $data['title']);
            }
            else {
                $region = pdo_fetch("SELECT id FROM" . tablename('xcommunity_region') . "WHERE uniacid='{$_W['uniacid']}' AND title='{$_GPC['title']}' AND province='{$reside['province']}' AND city='{$reside['city']}' AND dist='{$reside['dist']}'");
                if ($region) {
                    itoast('该小区已经存在,无需在添加.', referer(), 'error', ture);
                }
                $data['rid'] = $rid;
                $data['uid'] = $_W['uid'];
                $data['status'] = 1;
                pdo_insert("xcommunity_region", $data);
                $regionid = pdo_insertid();
                util::permlog('小区管理-添加', '添加小区ID:' . $regionid . '添加名称:' . $data['title']);
                if ($user) {
                    $d = array(
                        'usersid'  => $_W['uid'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_users_region', $d);
                }
                //主页导航
                $navs = pdo_getall('xcommunity_nav', array('uniacid' => $_W['uniacid']), array('id'));
                foreach ($navs as $k => $v) {
                    $d = array(
                        'nid'      => $v['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_nav_region', $d);
                }
                //住户中心
                $hnavs = pdo_getall('xcommunity_housecenter', array('uniacid' => $_W['uniacid']), array('id'));
                foreach ($hnavs as $k => $v) {
                    $d = array(
                        'nid'      => $v['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_housecenter_region', $d);
                }
                //便民查询
                $search = pdo_getall('xcommunity_search', array('uniacid' => $_W['uniacid']), array('id'));
                foreach ($search as $key => $val) {
                    $d = array(
                        'sid'      => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_search_region', $d);
                }
                //便民号码
                $phone = pdo_getall('xcommunity_phone', array('uniacid' => $_W['uniacid']), array('id'));
                foreach ($phone as $key => $val) {
                    $d = array(
                        'phoneid'  => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_phone_region', $d);
                }
                //首页公告
                $announcements = pdo_getall('xcommunity_announcement',array('status'=>1 ,'uniacid'=>$_W['uniacid']),array());
                foreach ($announcements as $key => $val) {
                    $d = array(
                        'aid'  => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_announcement_region', $d);
                }
                //首页幻灯/魔方推荐/首页广告
                $slides = pdo_getall('xcommunity_slide',array('uniacid'=>$_W['uniacid']),array());
                foreach ($slides as $key => $val) {
                    $d = array(
                        'sid'  => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_slide_region', $d);
                }
            }
            $rules = pdo_get("rule_keyword", array('rid' => $rid), array('id'));
            $covers = pdo_get('cover_reply', array('rid' => $rid), array('id'));
            $ruleword = array(
                'rid'     => $rid,
                'uniacid' => $_W['uniacid'],
                'module'  => 'cover',
                'content' => $data['keyword'],
                'type'    => 1,
                'status'  => 1,
            );
            if (empty($rules)) {
                pdo_insert('rule_keyword', $ruleword);
            }
            else {
                pdo_update('rule_keyword', $ruleword, array('id' => $rules['id']));
            }
            $crid = $ru ? $ru['id'] : $rid;
            $entry = array(
                'uniacid'     => $_W['uniacid'],
                'multiid'     => 0,
                'rid'         => $crid,
                'title'       => $_GPC['title'],
                'description' => '',
                'thumb'       => tomedia($_GPC['pic']),
                'url'         => $this->createMobileUrl('home', array('regionid' => $regionid)),
                'do'          => 'home',
                'module'      => $this->module['name'],
            );
            if (empty($covers)) {
                pdo_insert('cover_reply', $entry);
            }
            else {
                pdo_update('cover_reply', $entry, array('rid' => $crid));
            }
            $pro = pdo_getcolumn('xcommunity_region_data',array('regionid' => $regionid),'id');
            if ($pro){
                pdo_update('xcommunity_region_data', $da, array('regionid' => $regionid));
            }else{
                $da['regionid'] = $regionid;
                pdo_insert('xcommunity_region_data',$da);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        load()->func('tpl');
        include $this->template('web/plugin/rank/region/add');
    }
    elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $reside = $_GPC['reside'];
        if (!empty($reside)) {
            if ($reside['province']) {
                $condition .= " AND t1.province = :province";
                $params[':province'] = $reside['province'];
            }
            if ($reside['city']) {
                $condition .= " AND t1.city = :city";
                $params[':city'] = $reside['city'];
            }
            if ($reside['district']) {
                $condition .= " AND t1.dist = :dist";
                $params[':dist'] = $reside['district'];
            }
        }
        if (intval($_GPC['pid'])) {
            $condition .= " and t2.id =:pid";
            $params[':pid'] = intval($_GPC['pid']);
        }
        if ($user&&$user[type] == 2 ) {
            //普通管理员
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }
        if ($user&&$user[type] == 3) {
            //普通管理员
            $condition .= " AND t1.id in({$user['regionid']})";
        }
        $sql = "select t1.*,t2.title as ptitle,t1.title as rtitle from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid = t2.id where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid = t2.id where $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        load()->func('tpl');
        load()->func('communication');
        include $this->template('web/plugin/rank/region/list');
    }
    elseif ($p == 'delete') {
        //小区删除
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_region') . "WHERE id=:id AND uniacid=:uniacid", array(":id" => $id, ":uniacid" => $_W['uniacid']));
            if (empty($item)) {
                itoast("不存在该小区信息或已删除", referer(), 'error');
            }
            if (pdo_delete('xcommunity_region', array('id' => $id))) {
                pdo_delete('xcommunity_area',array('regionid' => $id));
                pdo_delete('xcommunity_build',array('regionid' => $id));
                pdo_delete('xcommunity_unit',array('regionid' => $id));
                util::permlog('小区管理-删除', '删除小区ID:' . $id . '小区名称:' . $item['title']);
                pdo_delete('xcommunity_member', array('regionid' => $id));
                pdo_delete('xcommunity_xqcars', array('regionid' => $id));
                $parking = pdo_getall('xcommunity_parking',array('regionid' => $id),array('id'));
                foreach($parking as $k => $v){
                    pdo_delete('xcommunity_parking_record',array('parkingid' => $v['id']));
                }
                pdo_delete('xcommunity_parking', array('regionid' => $id));
                pdo_delete('xcommunity_region_data', array('regionid' => $id));
                itoast('删除成功', referer(), 'success', ture);
            }
        }

    }
}
elseif ($op == 'report'){
    if ($p == 'list'){
        if (checksubmit('add')){
            $reportid = intval($_GPC['reportid']);
            $delay = intval($_GPC['delaytime']);
            $starttime = pdo_getcolumn('xcommunity_report',array('id' => $reportid),'createtime');
            $delaytime = $delay * 86400 + $starttime;
            pdo_update('xcommunity_report',array('delaytime' => $delaytime),array('id' => $reportid));
        }
        $regions = model_region::region_fetall();
        $categories = util::fetchall_category(3);
        //搜索
        $condition = ' t1.uniacid =:uniacid and t1.type=2 and t1.status=2';
//        $condition .= ' and t1.state=2 ';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['category'])) {
            $condition .= " AND t1.cid = :category";
            $params[':category'] = $_GPC['category'];
        }
        $status = intval($_GPC['status']);
        if (!empty($status)) {
            $condition .= " AND t1.status = :status";
            $params[':status'] = $status;
        }
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
        }

        if ($user[type] == 3) {
            //普通管理员
            $condition .= " and t1.regionid in({$user['regionid']})";
        } else {
            if ($_GPC['regionid']) {
                $condition .= " and t1.regionid =:regionid";
                $params[':regionid'] = $_GPC['regionid'];
            }
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t3.realname like :keyword or t3.mobile like :keyword or t2.address like :keyword)";
            $params[':keyword'] = $_GPC['keyword'];
        }
        $ftime = TIMESTAMP - 5*86400;
        $condition .= " and (t1.delaytime < :ftime or t1.createtime < :ftime) ";
        $params[':ftime'] = $ftime;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select t3.realname,t3.mobile,t5.title,t2.address,t4.name as cate,t1.createtime,t1.status,t1.enable,t1.id,t6.rank,t1.state,t1.delaytime from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join".tablename('xcommunity_rank')."t6 on t6.rankid = t1.id where $condition order by t1.id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_category') . "t4 on t4.id = t1.cid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where $condition order by t1.id desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/report/list');
    }
    elseif ($p == 'send'){
        $id = intval($_GPC['id']);
        if (checksubmit('submit')){
            $pros = $_GPC['pro'];
            $pro = implode(',',$pros);
            $notices = $_GPC['notice'];
            $notice = implode(',',$notices);
            $pmanage = pdo_fetchall('select openid from'.tablename('xcommunity_rank_notice')." where id in(:id)",array(':id' => $pro));
            $nmanage = pdo_fetchall('select openid from'.tablename('xcommunity_rank_report_notice')." where id in(:id)",array(':id' => $notice));
            $tsql = "select t1.createtime,t1.content,t4.realname,t4.mobile,t5.title,t2.address from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join " . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where t1.id=:id";
            $report = pdo_fetch($tsql, array(':id' => $id));
            $content = array(
                'first' => array(
                    'value' => '未处理投诉通知',
                ),
                'keyword1' => array(
                    'value' => $report['realname'],
                ),
                'keyword2' => array(
                    'value' => $report['mobile'],
                ),
                'keyword3' => array(
                    'value' => $report['title'] . $report['address'],
                ),
                'keyword4' => array(
                    'value' => $report['content'],
                ),
                'keyword5' => array(
                    'value' => date('Y-m-d H:i', $report['createtime']),
                ),
                'remark' => array(
                    'value' => '请尽快联系客户。',
                ),
            );
            $reportid = intval($_GPC['reportid']);
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$id}&do=rank&m=" . $this->module['name'];
            if (set('t5')) {
                foreach ($pmanage as $k => $v){
                    util::sendTplNotice($v['openid'], set('t6'), $content, $url, '');
                }
                foreach ($nmanage as $ke => $va){
                    util::sendTplNotice($va['openid'], set('t6'), $content, $url, '');
                }
            }
            util::permlog('', '评分投诉信息推送,信息ID:' . $id);
            itoast('推送成功', referer(), 'success', ture);exit();
        }
        $item = pdo_fetch("select t1.regionid,t2.pid from".tablename('xcommunity_report')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid=t2.id where t1.id=:id",array(':id' => $id));
        $pros = pdo_fetchall('select * from'.tablename('xcommunity_rank_notice')." where uniacid=:uniacid and pid=:pid",array(':uniacid' => $_W['uniacid'],':pid' => $item['pid']));
        $notices = pdo_fetchall('select t1.* from'.tablename('xcommunity_rank_report_notice')."t1 left join".tablename('xcommunity_rank_report_notice_property')."t2 on t2.nid=t1.id where t1.uniacid=:uniacid and t2.pid=:pid",array(':uniacid' => $_W['uniacid'],':pid' => $item['pid']));
        include $this->template('web/plugin/rank/report/send');
    }
}
elseif ($op == 'grab'){
    if ($p == 'list'){
        $condition = " t1.uniacid=:uniacid";
        $parms[':uniacid'] =  $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.* FROM" . tablename('xcommunity_rank_report_log') . "t1 WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        foreach ($list as $k => $v){
            $list[$k]['pics'] = explode(',',$v['images']);
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_rank_report_log') . "t1 WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/grab/list');
    }
}
elseif ($op == 'notice'){
    if($p == 'list'){
        $condition = " t1.uniacid=:uniacid";
        $parms[':uniacid'] =  $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title as ptitle FROM" . tablename('xcommunity_rank_report_notice') . "t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_rank_report_notice') . "t1 left join".tablename('xcommunity_property')."t2 on t1.pid=t2.id WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/rank/notice/list');
    }
    elseif($p == 'add'){
        $id = intval($_GPC['id']);
        $d = '';
        if ($user) {
            if ($user&&$user[type] == 3) {
                //普通管理员
                $sql = "select pid from".tablename('xcommunity_region')." where id in({$user['regionid']})";
                $regions = pdo_fetchall($sql);
                $pids = '';
                foreach ($regions as $k => $v){
                    $pids .= $v['pid'].',';
                }
                $pids = rtrim(ltrim($pids,','),',');
                $d .= " AND id in({$pids})";
            }
            if ($user&&in_array($user[type],array(2,4,5))) {
                //普通管理员
                $d .= " AND uid='{$_W['uid']}'";
            }
        }
        $properties = model_region::property_fetall($d);
        if ($id) {
            $item = pdo_get('xcommunity_rank_report_notice', array('id' => $id), array());
            $pid = pdo_getall('xcommunity_rank_report_notice_property', array('nid' => $id), array('pid'));
            $pids = array();
            foreach ($pid as $key => $val) {
                $pids[] = $val['pid'];
            }
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'enable'    => $_GPC['enable'],
                'type'      => $_GPC['type'],
                'realname'  => trim($_GPC['realname']),
                'mobile' => trim($_GPC['mobile']),
                'openid'    => trim($_GPC['openid']),
//                'pid'       => intval($_GPC['pid'])
            );
            if (empty($item['id'])) {
                pdo_insert('xcommunity_rank_report_notice', $data);
                $id = pdo_insertid();
            }
            else {
                pdo_update('xcommunity_rank_report_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_rank_report_notice_property', array('nid' => $id));
            }
            $pids = $_GPC['pid'];
            foreach ($pids as $k => $v) {
                $da = array(
                    'nid'    => $id,
                    'pid' => $v,
                );
                pdo_insert('xcommunity_rank_report_notice_property', $da);
            }
            itoast('添加成功', $this->createWebUrl('rank', array('op' => 'notice')), 'success', true);
        }
        include $this->template('web/plugin/rank/notice/add');
    }
    elseif($p == 'del'){
        $id = intval($_GPC['id']);
        $notice = pdo_get('xcommunity_rank_report_notice',array('id'=> $id),array());
        if($notice){
            if (pdo_delete('xcommunity_rank_report_notice', array('id' => $id))) {
                util::permlog('房管接收员-删除','信息标题:'.$notice['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($p == 'verify') {
        //审核用户
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_rank_report_notice", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}