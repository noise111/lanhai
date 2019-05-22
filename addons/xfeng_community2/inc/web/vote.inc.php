<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台问卷调查
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'add';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'add') {
    $regionids = '[]';
    if ($id){
        $regions = model_region::region_fetall();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_vote') . "WHERE id=:id", array(':id' => $id));
        $regs = pdo_getall('xcommunity_vote_region', array('voteid' => $id), array('regionid'));
        $regionid =array();
        foreach ($regs as $key => $val) {
            $regionid[] = $val['regionid'];
        }
        $regionids = json_encode($regionid);
        $starttime =  $item['starttime'];
        $endtime = $item['endtime'];
    }

    if ($_W['isajax']) {
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $birth = $_GPC['birth'];
        $allregion = intval($_GPC['allregion']);
        if ($allregion == 1){

        }
//        $content = htmlspecialchars_decode($_GPC['content']);
        $content = $_GPC['content'];
        $data = array(
            'uniacid'    => $_W['uniacid'],
            'title'      => $_GPC['title'],
            'starttime'  => $starttime,
            'endtime'    => $endtime,
            'thumb'      => $_GPC['thumb'],
            'content'    => $content,
            'createtime' => TIMESTAMP,
            'province' => $birth['province'],
            'city' => $birth['city'],
            'dist' => $birth['district'],
            'allregion' => $allregion
        );
        if (empty($_GPC['id'])) {
            $data['uid'] = $_W['uid'];
            $data['enable'] = 0;
            pdo_insert('xcommunity_vote', $data);
            $id = pdo_insertid();
            util::permlog('问卷调查-添加', '信息标题:' . $data['title']);
        }
        else {
            pdo_update('xcommunity_vote', $data, array('id' => $id));
            pdo_delete('xcommunity_vote_region', array('voteid' => $id));
            util::permlog('问卷调查-修改', '信息标题:' . $data['title']);
        }
        if ($allregion == 1){
            $regions = model_region::region_fetall();
            foreach ($regions as $k => $v){
                $dat = array(
                    'voteid' => $id,
                    'regionid' => $v['id'],
                );
                pdo_insert('xcommunity_vote_region', $dat);
            }
        }else {
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'voteid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_vote_region', $dat);
            }
        }
        echo json_encode(array('status'=>1));exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/plugin/vote/add');
}
elseif ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 'uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    if ($user[type] == 2 || $user[type] == 3) {
        //普通管理员
        $condition .= " AND uid=:uid";
        $params[':uid'] = $_W['uid'];
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_vote') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        $isadd = pdo_get('xcommunity_vote_question', array('voteid' => $v['id']), array('id'));
        if ($isadd) {
            $list[$k]['isadd'] = 1;
        }
        else {
            $list[$k]['isadd'] = 0;
        }
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_vote') . "WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/vote/list');
}
elseif ($op == 'delete') {
    if ($id) {
        $item = pdo_get('xcommunity_vote', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_vote', array('id' => $id))) {
                util::permlog('问卷调查-删除', '信息标题:' . $item['title']);
                $questions = pdo_fetchall('select id from' . tablename('xcommunity_vote_question') . 'where voteid=:voteid', array(':voteid' => $id));
                foreach ($questions as $k => $v) {
                    pdo_delete('xcommunity_vote_options', array('questionid' => $v['id']));
                }
                if (pdo_delete('xcommunity_vote_question', array('voteid' => $id))) {

                }
                itoast('删除成功', referer(), 'success');
            }
        }

    }
}
elseif ($op == 'question') {
    if ($p == 'add') {
        if ($id) {
            $item = pdo_get('xcommunity_vote', array('id' => $id), array('title'));
            $list = pdo_fetchall("select * from " . tablename('xcommunity_vote_question') . " where voteid=:voteid order by sort asc", array(':voteid' => $id));
            $nums = count($list);
            foreach ($list as $k => $v) {
                $list[$k]['num'] = $v['sort'] - 1;
                $list[$k]['options'] = pdo_fetchall("select * from " . tablename('xcommunity_vote_options') . " where questionid=:questionid order by sort asc", array(':questionid' => $v['id']));
            }
        }
        if (checksubmit('submit')) {
            $question = $_GPC['timu'];
            $options = $_GPC['list'];

            for ($i = 0; $i < count($question); $i++) {
                $num = $i + 1;
                $data = array(
                    'voteid'     => $id,
                    'title'      => trim($question[$i]['title']),
                    'remark'     => trim($question[$i]['i_desc']),
                    'maxnum'     => trim($question[$i]['i_max']),
                    'type'       => intval($question[$i]['type']),
                    'sort'       => $num,
                    'createtime' => TIMESTAMP
                );
//                $ques = pdo_get('xcommunity_vote_question',array('id' => $question[$i]['id']),array('id'));
                if ($question[$i]['id']) {
                    pdo_update('xcommunity_vote_question', $data, array('id' => $question[$i]['id']));

                    $quesid = $question[$i]['id'];
                }
                else {
                    pdo_insert('xcommunity_vote_question', $data);
                    $quesid = pdo_insertid();
                }

                if (!empty($options[$i])) {
                    for ($j = 0; $j < count($options[$i]); $j++) {
                        $dat = array(
                            'questionid' => intval($quesid),
                            'title'      => trim($options[$i][$j]),
                            'sort'       => $j,
                            'createtime' => TIMESTAMP
                        );
//                        $quest = pdo_get('xcommunity_vote_options',array('id' => $_GPC['lists'][$i][$j]),array('id'));
                        if ($_GPC['lists'][$i][$j]) {
                            pdo_update('xcommunity_vote_options', $dat, array('id' => $_GPC['lists'][$i][$j]));
                        }
                        else {
                            pdo_insert('xcommunity_vote_options', $dat);
                        }
                    }
                }
            }
            itoast('更新成功', referer(), 'success');
        }
        include $this->template('web/plugin/vote/question_add');
    }
    elseif ($p == 'isenable') {
        if ($_W['isajax']) {
            if ($id) {
                $enable = pdo_get('xcommunity_vote', array('id' => $id), array('enable'));
            }
            if ($enable['enable'] == 0) {
                echo json_encode(array('status' => 1, 'id' => $id));
                exit();
            }
            elseif ($enable['enable'] == 1) {
                echo json_encode(array('status' => 2, 'content' => '该问卷已发布'));
                exit();
            }
        }
    }
    elseif ($p == 'list') {
        $voteid = intval($_GPC['voteid']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't2.voteid = :voteid';
        $params[':voteid'] = $voteid;
        if (!empty($_GPC['keyword'])) {
            $condition .= " and  t4.title LIKE '%{$_GPC['keyword']}%'";
        }
        if ($_GPC['export'] == 1) {
            $tsql = "SELECT t1.content,t1.optionsid,t3.nickname,t4.title,t4.type,t5.address,t8.realname,t8.mobile,t2.enable FROM" . tablename('xcommunity_vote_user_answer') . "t1 left join" . tablename('xcommunity_vote_user') . "t2 on t1.userid=t2.id left join" . tablename('mc_mapping_fans') . " t3 on t2.uid=t3.uid left join" . tablename('xcommunity_vote_question') . "t4 on t1.questionid=t4.id left join".tablename('xcommunity_member_room')."t5 on t2.addressid=t5.id left join".tablename('xcommunity_member_bind')."t6 on t6.addressid=t5.id left join".tablename('xcommunity_member')."t7 on t7.id=t6.memberid left join".tablename('mc_members')."t8 on t8.uid=t7.uid WHERE $condition ORDER BY t1.createtime desc ";
            $tlist = pdo_fetchall($tsql, $params);
            if ($tlist) {
                foreach ($tlist as $k => $v) {
                    if ($v['type'] == 1 || $v['type'] == 2) {
                        $options = pdo_get('xcommunity_vote_options', array('id' => $v['optionsid']), array('title'));
                        $tlist[$k]['otitle'] = $options['title'];
                    }
                    $tlist[$k]['answer'] = $tlist[$k]['otitle'] ? $tlist[$k]['otitle'] : $v['content'];
                }

            }
            model_execl::export($tlist, array(
                "title" => "调查数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '问题标题',
                        'field' => 'title',
                        'width' => 40
                    ),
                    array(
                        'title' => '用户地址',
                        'field' => 'address',
                        'width' => 20
                    ),
                    array(
                        'title' => '用户姓名',
                        'field' => 'realname',
                        'width' => 20
                    ),
                    array(
                        'title' => '用户电话',
                        'field' => 'mobile',
                        'width' => 12
                    ),
                    array(
                        'title' => '用户昵称',
                        'field' => 'nickname',
                        'width' => 18
                    ),
                    array(
                        'title' => '用户回答',
                        'field' => 'answer',
                        'width' => 30
                    )

                )
            ));
        }
        $sql = "SELECT t1.content,t1.optionsid,t3.nickname,t4.title,t4.type,t5.address,t8.realname,t8.mobile,t2.enable FROM" . tablename('xcommunity_vote_user_answer') . "t1 left join" . tablename('xcommunity_vote_user') . "t2 on t1.userid=t2.id left join" . tablename('mc_mapping_fans') . " t3 on t2.uid=t3.uid left join" . tablename('xcommunity_vote_question') . "t4 on t1.questionid=t4.id left join".tablename('xcommunity_member_room')."t5 on t2.addressid=t5.id left join".tablename('xcommunity_member_bind')."t6 on t6.addressid=t5.id left join".tablename('xcommunity_member')."t7 on t7.id=t6.memberid left join".tablename('mc_members')."t8 on t8.uid=t7.uid WHERE $condition ORDER BY t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        if ($list) {
            foreach ($list as $k => $v) {
                if ($v['type'] == 1 || $v['type'] == 2) {
                    $options = pdo_get('xcommunity_vote_options', array('id' => $v['optionsid']), array('title'));
                    $list[$k]['otitle'] = $options['title'];
                }
            }
        }

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_vote_user_answer') . "t1 left join" . tablename('xcommunity_vote_user') . "t2 on t1.userid=t2.id left join" . tablename('mc_mapping_fans') . " t3 on t2.uid=t3.uid left join" . tablename('xcommunity_vote_question') . "t4 on t1.questionid=t4.id left join".tablename('xcommunity_member_room')."t5 on t2.addressid=t5.id left join".tablename('xcommunity_member_bind')."t6 on t6.addressid=t5.id left join".tablename('xcommunity_member')."t7 on t7.id=t6.memberid left join".tablename('mc_members')."t8 on t8.uid=t7.uid WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/vote/question_list');
    }
}
elseif ($op == 'change') {
    if ($_W['isajax']) {
        $enable = $_GPC['enable'];
        if ($enable == 0) {
            $data['enable'] = 1;
        }
        elseif ($enable == 1) {
            $data['enable'] = 0;
        }
        $res = pdo_update('xcommunity_vote', $data, array('id' => $_GPC['id']));
        if ($res) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
elseif ($op == 'show') {
    $voteid = intval($_GPC['voteid']);
    if ($voteid) {
        $list = pdo_getall('xcommunity_vote_question', array('voteid' => $voteid), array('id','title','type'), '', array('sort asc'));
        $rows = array();
        foreach ($list as $k => $v){
            if($v['type']== 1 || $v['type'] == 2){
                $list[$k]['options'] = pdo_getall('xcommunity_vote_options',array('questionid'=>$v['id']),array());
                $answers = array();
                $list[$k]['total'] = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_vote_user')."where voteid=:voteid",array(':voteid'=>$voteid));
                foreach ($list[$k]['options'] as $key => $val){
                    if($v['type'] == 1){
                        //单选
                        $list[$k]['options'][$key]['num'] = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_vote_user_answer')."where optionsid=:optionsid and type = 1",array(':optionsid'=>$val['id']));
                    }
                    if($v['type']==2){
                        $list[$k]['options'][$key]['num'] = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_vote_user_answer')."where optionsid=:optionsid and type = 2",array(':optionsid'=>$val['id']));
                    }
                    $list[$k]['options'][$key]['scale'] =($list[$k]['options'][$key]['num'] && $list[$k]['total']) ? round($list[$k]['options'][$key]['num']/$list[$k]['total'],2)*100 : 0;
                }

            }

            if($v['type'] == 3) {
                $list[$k]['tk_answers'] = pdo_fetchall("select content from" . tablename('xcommunity_vote_user_answer') . "where questionid=:questionid and type = 3", array(':questionid' => $v['id']));
            }

        }
       //print_r($list);exit();
    }
    include $this->template('web/plugin/vote/show');
}
elseif($op =='show_tk'){
    $questionid = intval($_GPC['questionid']);
    if($questionid){
        $question = pdo_get('xcommunity_vote_question', array('id' => $questionid), array('id','title','type'));
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $params[':questionid'] = $questionid;
        $sql = "SELECT content,createtime FROM" . tablename('xcommunity_vote_user_answer') . "WHERE questionid=:questionid and type = 3 order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_vote_user_answer') . "WHERE questionid=:questionid and type = 3 ", $params);
        $pager = pagination($total, $pindex, $psize);
    }
    include $this->template('web/plugin/vote/show_tk');
}
/**
 * 基本配置
 */
if($op =='setting'){
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/plugin/vote/setting');
}