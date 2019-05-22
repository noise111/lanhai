<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台菜单设置
 */
	global $_W,$_GPC;
	$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
	$id = intval($_GPC['id']);
	$regions = model_region::region_fetall();
	if (empty($regions)) {
		itoast('请先添加小区',$this->createWebUrl('region',array('op' => 'add')),'error',ture);
	}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
	if ($op == 'list') {
		$pindex = max(1, intval($_GPC['page']));
		$psize  = 10;
		$list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_nav')."WHERE  uniacid='{$_W['uniacid']}' AND pcate = 0 order by displayorder asc LIMIT ".($pindex - 1) * $psize.','.$psize);
		$children = array();
		foreach ($list as $key => $value) {
			$sql  = "select * from".tablename("xcommunity_nav")."where uniacid='{$_W['uniacid']}' and  pcate='{$value['id']}' order by displayorder asc";
			$li = pdo_fetchall($sql);

			$children[$value['id']] = $li;
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_nav')."WHERE uniacid='{$_W['uniacid']}' AND pcate = 0 ");
		$pager  = pagination($total, $pindex, $psize);
		if (!empty($_GPC['displayorder'])) {
			foreach ($_GPC['displayorder'] as $id => $displayorder) {
				pdo_update('xcommunity_nav', array('displayorder' => $displayorder), array('id' => $id));
			}
			itoast('排序更新成功！', 'refresh', 'success',ture);
		}
		include $this->template('web/core/nav/list');
	}
	elseif ($op == 'add') {
        $regions = model_region::region_fetall();
		if ($id) {
			$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_nav')."WHERE id=:id",array(":id" => $id));
			$regs = pdo_getall('xcommunity_nav_region',array('nid' => $id),array('regionid'));
			foreach ($regs as $key =>$val){
			    $regionid .=$val['regionid'].',';
            }
            $regionid = ltrim(rtrim($regionid, ","),',');
		}
		if ($_W['isajax']) {
			if (empty($_GPC['url'])) {
				$url = '#';
			}else{
				$url = $_GPC['url'];
			}
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'displayorder' => $_GPC['displayorder'],
				'title' => $_GPC['title'],
				'url' => $url, 
				'status' => 1,
				'icon' => $_GPC['icon'],
				'bgcolor' => $_GPC['bgcolor'],
				'thumb' => $_GPC['thumb'],
			);
			if ($id) {
				$insert['pcate'] = $item['pcate'];
				pdo_update('xcommunity_nav',$insert,array('id' => $id));
                pdo_delete('xcommunity_nav_region',array('nid' => $id));
                util::permlog('菜单管理-修改','修改菜单名称:'.$insert['title']);
			}else{
				$insert['pcate'] = $_GPC['pcate'];
				pdo_insert('xcommunity_nav',$insert);
				$id = pdo_insertid();
                util::permlog('菜单管理-添加','添加菜单名称:'.$insert['title']);
			}
            $regionids = explode(',',$_GPC['regionids']);
            foreach ($regionids as $key => $value){
                $dat = array(
                    'nid' => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_nav_region',$dat);
            }
            echo json_encode(array('status'=>1));exit();
		}
		load()->func('tpl');
        $options=array();
        $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
		include $this->template('web/core/nav/add');
	}
	elseif ($op == 'cover') {
	//设置入口信息
		if (empty($id)) {
			itoast('缺少参数',referer(),'error',ture);
		}
		$nav = pdo_fetch("SELECT * FROM".tablename('xcommunity_nav')."WHERE id=:id AND uniacid=:uniacid",array(':uniacid' => $_W['uniacid'],':id' => $id));
		if (empty($nav)) {
			itoast('该菜单不存在或已删除',referer(),'error',ture);
		}
		$reply = pdo_fetch("SELECT id,rid,title,thumb,description FROM".tablename('cover_reply')."WHERE do='{$nav['do']}' AND uniacid=:uniacid",array(':uniacid' => $_W['uniacid']));
		if ($reply) {
			$k = pdo_fetchall("SELECT content FROM".tablename('rule_keyword')."WHERE rid=:rid",array(':rid' => $reply['rid']));
			$kds = '';
			foreach ($k as $key => $value) {
				$kds[]= $value['content'];
			}
			$kds = implode('|',$kds);
		}
		if (checksubmit('submit')) {
			if(trim($_GPC['keywords']) == '') {
				itoast('必须输入触发关键字.');
			}
			$keywords = explode('|',$_GPC['keywords']);
			$rule = array(
				'uniacid' => $_W['uniacid'],
				'name' => $_GPC['title'],
				'module' => 'cover', 
				'status' => 1,
			);
			if (!empty($reply)) {
				$rid = $reply['rid'];
				$result = pdo_update('rule',$rule,array('id' => $rid));
			}else{
				$result = pdo_insert('rule', $rule);
				$rid = pdo_insertid();
			}
			if (!empty($rid)) {
				$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
				$pars = array();
				$pars[':rid'] = $rid;
				$pars[':uniacid'] = $_W['uniacid'];
				pdo_query($sql, $pars);
				$rowtpl = array(
					'rid' => $rid,
					'uniacid' => $_W['uniacid'],
					'module' => 'cover',
					'status' => $rule['status'],
					'displayorder' => $rule['displayorder'],
				);
				foreach($keywords as $kw) {
					$krow = $rowtpl;
					$krow['type'] = 1;
					$krow['content'] = $kw;
					pdo_insert('rule_keyword', $krow);
				}
				$url = murl('entry', array('do' => $nav['do'], 'm' => $this->module['name']));
				$entry = array(
					'uniacid' => $_W['uniacid'],
					'multiid' => $multiid,
					'rid' => $rid,
					'title' => $_GPC['title'],
					'description' => $_GPC['description'],
					'thumb' => $_GPC['thumb'],
					'url' => $url,
					'do' => $nav['do'],
					'module' => $this->module['name'],
				);
				if (empty($reply['id'])) {
					pdo_insert('cover_reply', $entry);
				} else {
					pdo_update('cover_reply', $entry, array('id' => $reply['id']));
				}
				itoast('封面保存成功！', 'refresh', 'success',ture);
			} else {
				itoast('封面保存失败, 请联系网站管理员！');
			}
		}
		load()->func('tpl');
	include $this->template('web/core/nav/cover');
}
    elseif($op == 'delete'){
        if (empty($id)) {
            exit("缺少参数");
        }
        /*
        判断是否为主菜单
        */
        $navs = pdo_getall("xcommunity_nav",array('pcate' => $id,'uniacid' => $_W['uniacid']),array('id','title'));
        foreach ($navs as $key => $nav) {
            pdo_delete('xcommunity_nav',array('id' => $nav['id']));
            util::permlog('菜单管理-删除','菜单名称:'.$nav['title']);
        }
        if (pdo_delete('xcommunity_nav',array('id' => $id))) {
            $result = array(
                    'status' => 1,
                );
            echo json_encode($result);exit();
        }
    }
    elseif($op == 'set'){
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数',referer(),'error');
        }
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        $data = ($data==1? 0:1);
        pdo_query("UPDATE ".tablename('xcommunity_nav')."SET isshow = '{$data}' WHERE id=:id",array(":id" => $id ));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
    elseif($op == 'verify'){
		$id = intval($_GPC['id']);
		$type = $_GPC['type'];
		$data = intval($_GPC['data']);
		if (in_array($type, array('status','view','add','show'))) {
			$data = ($data==1?'0':'1');
			pdo_update("xcommunity_nav", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
	}











