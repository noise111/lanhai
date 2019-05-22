<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台二维码管理
 */
global $_W,$_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
load()->model('account');
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if($op == 'check_scene_str') {
	$scene_str = trim($_GPC['scene_str']);
	$is_exist = pdo_fetchcolumn('SELECT id FROM ' . tablename('qrcode') . ' WHERE uniacid = :uniacid AND acid = :acid AND scene_str = :scene_str AND model = 2', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':scene_str' => $scene_str));
	if(!empty($is_exist)) {
		exit('repeat');
	}
	exit('success');
}

if($op == 'list') {
	$wheresql = " WHERE uniacid = :uniacid AND acid = :acid AND type = 'scene' and (enable=1 or enable =2)";
	$param = array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']);
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$wheresql .= " AND name LIKE '%{$keyword}%'";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
//	$sql = "select t1.* from".tablename('qrcode')."t1 left join".tablename('xcommunity_region')."t2 on t2.keyword=t1.keyword $wheresql and t2.id !='' ORDER BY t1.id DESC ";
    $sql = "select * from".tablename('qrcode')." $wheresql ORDER BY id DESC ";
	$list = pdo_fetchall($sql,$param);
	if (!empty($list)) {
		foreach ($list as $index => &$qrcode) {
			$qrcode['showurl'] = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrcode['ticket']);
			$qrcode['endtime'] = $qrcode['createtime'] + $qrcode['expire'];
			if (TIMESTAMP > $qrcode['endtime']) {
				$qrcode['endtime'] = '<font color="red">已过期</font>';
			}else{
				$qrcode['endtime'] = date('Y-m-d H:i:s',$qrcode['endtime']);
			}
			if ($qrcode['model'] == 2) {
				$qrcode['modellabel']="永久";
				$qrcode['expire']="永不";
				$qrcode['endtime'] = '<font color="green">永不</font>';
			} else {
				$qrcode['modellabel']="临时";
			}
		}
	}
//    $tsql = "select t1.* from".tablename('qrcode')."t1 left join".tablename('xcommunity_dp')."t2 on t2.sjname=t1.keyword $wheresql and t2.id !='' ORDER BY t1.id DESC ";
//    $dplist = pdo_fetchall($tsql,$param);
//    if (!empty($dplist)) {
//        foreach ($dplist as $index => &$qrcode) {
//            $qrcode['showurl'] = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrcode['ticket']);
//            $qrcode['endtime'] = $qrcode['createtime'] + $qrcode['expire'];
//            if (TIMESTAMP > $qrcode['endtime']) {
//                $qrcode['endtime'] = '<font color="red">已过期</font>';
//            }else{
//                $qrcode['endtime'] = date('Y-m-d H:i:s',$qrcode['endtime']);
//            }
//            if ($qrcode['model'] == 2) {
//                $qrcode['modellabel']="永久";
//                $qrcode['expire']="永不";
//                $qrcode['endtime'] = '<font color="green">永不</font>';
//            } else {
//                $qrcode['modellabel']="临时";
//            }
//        }
//    }
//		pdo_query("UPDATE ".tablename('qrcode')." SET status = '0' WHERE uniacid = '{$_W['uniacid']}' AND model = '1' AND createtime < '{$_W['timestamp']}' - expire");
	include $this->template('web/core/qr/list');
}

if($op == 'del') {
	if ($_GPC['scgq']) {
		$list = pdo_fetchall("SELECT id FROM ".tablename('qrcode')." WHERE uniacid = :uniacid AND acid = :acid AND status = '0' AND type='scene'", array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']), 'id');
		if (!empty($list)) {
			pdo_query("DELETE FROM ".tablename('qrcode')." WHERE id IN (".implode(',', array_keys($list)).")");
			pdo_query("DELETE FROM ".tablename('qrcode_stat')." WHERE qid IN (".implode(',', array_keys($list)).")");
		}
		itoast('执行成功<br />删除二维码：'.count($list), referer(),'success');
	}else{
		$id = $_GPC['id'];
		pdo_delete('qrcode', array('id' =>$id, 'uniacid' => $_W['uniacid']));
		pdo_delete('qrcode_stat',array('qid' => $id, 'uniacid' => $_W['uniacid']));
		itoast('删除成功',referer(),'success',ture);
	}
}

if($op == 'add') {
	$enable = intval($_GPC['enable']);
	// if ($o == 'region') {
    $regions = model_region::region_fetall();
	// }else{
		$dps = pdo_fetchall("SELECT * FROM".tablename('xcommunity_dp')."WHERE uniacid=:uniacid",array(':uniacid' => $_W['uniacid']));
	// }

	load()->func('communication');
	if(checksubmit('submit')){
		$barcode = array(
			'expire_seconds' => '',
			'action_name' => '',
			'action_info' => array(
				'scene' => array(),
			),
		);
		$qrctype = intval($_GPC['qrc-model']);
		$acid = intval($_W['acid']);
		$uniacccount = WeAccount::create($acid);
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$update = array(
				'keyword' => trim($_GPC['keyword']),
				'name' => trim($_GPC['scene-name'])
			);
			pdo_update('qrcode', $update, array('uniacid' => $_W['uniacid'], 'id' => $id));
			itoast('恭喜，更新带参数二维码成功！', $this->createWebUrl('qr',array('op' => 'list')), 'success',ture);
		}
	
		if ($qrctype == 1) {
			$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE acid = :acid AND model = '1' ORDER BY qrcid DESC LIMIT 1", array(':acid' => $acid));
			$barcode['action_info']['scene']['scene_id'] = !empty($qrcid) ? ($qrcid + 1) : 100001;
			$barcode['expire_seconds'] = intval($_GPC['expire-seconds']);
			$barcode['action_name'] = 'QR_SCENE';
			$result = $uniacccount->barCodeCreateDisposable($barcode);
		} else if ($qrctype == 2) {
			$scene_str = trim($_GPC['scene_str']) ? trim($_GPC['scene_str'])  : itoast('场景值不能为空');
			$is_exist = pdo_fetchcolumn('SELECT id FROM ' . tablename('qrcode') . ' WHERE uniacid = :uniacid AND acid = :acid AND scene_str = :scene_str AND model = 2', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':scene_str' => $scene_str));
			if(!empty($is_exist)) {
				itoast("场景值:{$scene_str}已经存在,请更换场景值");
			}
			$barcode['action_info']['scene']['scene_str'] = $scene_str;
			$barcode['action_name'] = 'QR_LIMIT_STR_SCENE';
			$result = $uniacccount->barCodeCreateFixed($barcode);
		} else {
			itoast('抱歉，此公众号暂不支持您请求的二维码类型！');
		}

		if(!is_error($result)) {
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $acid,
				'qrcid' => $barcode['action_info']['scene']['scene_id'],
				'scene_str' => $barcode['action_info']['scene']['scene_str'],
				'keyword' => $_GPC['keyword'],
				'name' => $_GPC['scene-name'],
				'model' => $_GPC['qrc-model'],
				'ticket' => $result['ticket'],
				'url' => $result['url'],
				'expire' => $result['expire_seconds'],
				'createtime' => TIMESTAMP,
				'status' => '1',
				'type' => 'scene',
                'enable' => intval($_GPC['enable'])
			);
//			print_r($insert);exit();
			if(empty($insert['keyword'])){
			    itoast('二维码关键字为空,请点击小区管理或者商家管理，进行填写，在生成二维码');exit();
            }
			pdo_insert('qrcode', $insert);
			itoast('恭喜，生成带参数二维码成功！', $this->createWebUrl('qr',array('op' => 'list')), 'success',ture);
		} else {
			itoast("公众平台返回接口错误. <br />错误代码为: {$result['errorcode']} <br />错误信息为: {$result['itoast']}");
		}
	}

	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE uniacid = {$_W['uniacid']} AND id = '{$id}'");
//	$r = substr($row['keyword'],0,1) ;
//	if($id){
//		if ($r == 'r') {
//			$o = 'region';
//		}else{
//			$o = 'business';
//		}
//	}
	
	include $this->template('web/core/qr/add');
}

if($op == 'extend') {
	load()->func('communication');
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$qrcrow = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE uniacid = :uniacid AND id = :id LIMIT 1", array(':uniacid' => $_W['uniacid'], ':id' => $id));
		$update = array();
		if ($qrcrow['model'] == 1) {
			$uniacccount = WeAccount::create($qrcrow['acid']);
			$barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
			$barcode['expire_seconds'] = 2592000;
			$barcode['action_name'] = 'QR_SCENE';
			$result = $uniacccount->barCodeCreateDisposable($barcode);
			if(is_error($result)) {
				itoast($result['itoast'], '', 'error');
			}
			$update['ticket'] = $result['ticket'];
			$update['url'] = $result['url'];
			$update['expire'] = $result['expire_seconds'];
			$update['createtime'] = TIMESTAMP;
			pdo_update('qrcode', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
		}
		itoast('恭喜，延长临时二维码时间成功！', referer(), 'success',ture);
	}
}

if($op == 'order') {
	$starttime = empty($_GPC['time']['start']) ? TIMESTAMP -  86399 * 30 : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 6*86400: strtotime($_GPC['time']['end']) + 86399;
	$where .= " WHERE uniacid = :uniacid AND acid = :acid AND createtime >= :starttime AND createtime < :endtime";
	$param = array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':starttime' => $starttime, ':endtime' => $endtime);
	!empty($_GPC['keyword']) && $where .= " AND name LIKE '%{$_GPC['keyword']}%'";
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('qrcode_stat'). $where, $param);
	$list = pdo_fetchall("SELECT * FROM ".tablename('qrcode_stat')." $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','. $psize, $param);
	if (!empty($list)) {
		$openid = array();
		foreach ($list as $index => &$qrcode) {
			if ($qrcode['type'] == 1) {
				$qrcode['type']="关注";
			} else {
				$qrcode['type']="扫描";
			}
			if(!in_array($qrcode['openid'], $openid)) {
				$openid[] = $qrcode['openid'];
			}
		}
		$openids = implode("','", $openid);
		$param_temp[':uniacid'] = $_W['uniacid'];
		$param_temp[':acid'] = $_W['acid'];
		$nickname = pdo_fetchall('SELECT nickname, openid FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND acid = :acid AND openid IN ('{$openids}')", $param_temp, 'openid');
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qrcode_stat') . $where, $param);
	$pager = pagination($total, $pindex, $psize);
	include $this->template('web/core/qr/order');
}

if($op == 'delsata') {
	$id = $_GPC['id'];
	$b = pdo_delete('qrcode_stat',array('id' =>$id, 'uniacid' => $_W['uniacid']));
	if ($b){
		itoast('删除成功',referer(),'success',ture);
	}else{
		itoast('删除失败',referer(),'error',ture);
	}
}

if ($op == 'search'){
    if ($_W['isajax']){
        $words = trim($_GPC['words']);
        $enable = intval($_GPC['enable']);
        if($enable == 1){
            $keyword = pdo_getcolumn('xcommunity_region',array('title' => $words,'uniacid' => $_W['uniacid']),'keyword');
            echo json_encode(array('err_code' => 0,'keyword' => $keyword));exit();
        }elseif($enable == 2){
            $keyword = pdo_getcolumn('xcommunity_dp',array('sjname' => $words,'uniacid' => $_W['uniacid']),'sjname');
            echo json_encode(array('err_code' => 0,'keyword' => $keyword));exit();
        }
    }
}