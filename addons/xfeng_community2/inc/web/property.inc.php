<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台物业团队
 */
	global $_GPC,$_W;
	$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
	if ($op == 'list') {
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

		include $this->template('web/core/property/list');
	}
	elseif ($op == 'add') {
		$id = intval($_GPC['id']);
		if ($id) {
			$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_property')."WHERE uniacid=:uniacid AND id=:id",array(":uniacid" => $_W['uniacid'],":id" => $id));
			if (empty($item)) {
				itoast('该信息不存在或已删除',referer(),'error',ture);
			}
		}
		if ($_W['isajax']) {
			$birth = $_GPC['birth'];
			$data = array(
                'uniacid' => $_W['uniacid'],
                'title' => $_GPC['title'],
                'thumb' => $_GPC['thumb'],
                'content' => htmlspecialchars_decode($_GPC['content']),
                'createtime' => TIMESTAMP,
                'telphone' => $_GPC['telphone'],
				);
            $pid = pdo_get('xcommunity_property',array('title' => $_GPC['title'],'uniacid' => $_W['uniacid']),'id');
			if ($id) {
                $ptitle = pdo_fetchcolumn("SELECT title FROM".tablename('xcommunity_property')."WHERE uniacid=:uniacid AND id=:id",array(":uniacid" => $_W['uniacid'],":id" => $id));
                if ($ptitle != $_GPC['title']){
                    if ($pid){
//                        itoast('该物业已经存在，请勿重复添加！',referer(),'error',ture);
                        echo json_encode(array('content'=>'该物业已经存在，请勿重复添加！'));exit();

                    }
                }
                $data['yybstatus'] =0;
                $data['action'] =1;
				pdo_update('xcommunity_property',$data,array("id" => $id));
                util::permlog('物业管理-修改','物业名称:'.$data['title']);
			}else{
			    if ($pid){
                    echo json_encode(array('content'=>'该物业已经存在，请勿重复添加！'));exit();
                }
                $data['uid'] = $_W['uid'];
				pdo_insert('xcommunity_property',$data);
				$id = pdo_insertid();
                util::permlog('物业管理-添加','添加物业信息ID:'.$id.'物业名称:'.$data['title']);
			}
            echo json_encode(array('status'=>1,'content'=>'操作成功'));exit();
		}
		load()->func('tpl');
        $options=array();
        $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
		include $this->template('web/core/property/add');
	}elseif ($op == 'delete') {
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
	}elseif ($op == 'rank') {
        $pindex = max(1, intval($_GPC['page']));
        $psize  = 20;
        $pid = intval($_GPC['pid']);
        $condition = 't1.uniacid=:uniacid and t1.rankid=:rankid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':rankid'] = $pid;
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!empty($_GPC['time'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']) + 86399;
            $condition .= " and t1.createtime >= :starttime and t1.createtime <= :endtime ";
            $params[':starttime'] = $starttime;
            $params[':endtime'] = $endtime;
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " and t2.realname LIKE :keyword";
            $params[":keyword"] = "%{$_GPC['keyword']}%";
        }
        $sql = "SELECT t1.*,t2.realname FROM".tablename('xcommunity_rank')."t1 left join".tablename('mc_members')."t2 on t1.uid=t2.uid WHERE $condition LIMIT ".($pindex - 1) * $psize.','.$psize;
        $list = pdo_fetchall($sql,$params);
        $total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_rank')."t1 WHERE $condition",$params);
        $pager  = pagination($total, $pindex, $psize);

        include $this->template('web/core/property/rank');
    }
	











