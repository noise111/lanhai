<?php  
global $_GPC;
global $_W;
$do=$_GPC['do'];
$method='fun';
$GLOBALS['frames']=$this->NavMenu($do,$method);
$xqmenu=$this->xqmenu();
$op=(in_array($_GPC['op'],array(0=>'add',1=>'list',2=>'delete',3=>'qrcreate'))?$_GPC['op']:'list');
$id=intval($_GPC['id']);
$user=$this->user();
if ($op=='list') 
{
	if (!empty($_GPC['displayorder'])) 
	{
		foreach($_GPC['displayorder'] as $id=>$displayorder)
		{
			pdo_update('xcommunity_building_device',array('displayorder'=>$displayorder),array('id'=>$id));
		}
		message('排序更新成功！','refresh','success');
	}
	$pindex=max(1,intval($_GPC['page']));
	$psize=20;
	$condition='';
	if ($user['type']==3) 
	{
		$condition .=' AND  regionid in('.$user['regionid'].')';
	}
	if (!empty($_GPC['keyword'])) 
	{
		$condition .=' AND device_code LIKE :keyword';
		$keyword=trim($_GPC['keyword']);
		$params[':keyword']='%'.$keyword.'%';
	}
	$sql='select * from '.tablename('xcommunity_building_device').'where  uniacid =:uniacid '.$condition.' order by displayorder asc LIMIT '.(($pindex-1)*$psize).','.$psize;
	$params[':uniacid']=$_W['uniacid'];
	$list=pdo_fetchall($sql,$params);
	$total=pdo_fetchcolumn('select count(*) from'.tablename('xcommunity_building_device').'where  uniacid =:uniacid '.$condition,$params);
	$pager=pagination($total,$pindex,$psize);
	include($this->template('web/building/list'));
}
else 
{
	if ($op=='add') 
	{
		$regions=$this->regions();
		if ($id) 
		{
			$item=pdo_get('xcommunity_building_device',array('id'=>$id),array(0=>'id',1=>'title',2=>'unit',3=>'device_code',4=>'type',5=>'regionid',6=>'openurl'));
		}
		if (checksubmit('submit')) 
		{
			if (empty($id)) 
			{
				$device=pdo_get('xcommunity_building_device',array('device_code'=>$_GPC['device_code']),array(0=>'id'));
				if ($device) 
				{
					message('已存在设备编号',referer(),'error');
					exit(0);
				}
			}
			$data=array('uniacid'=>$_W['uniacid'],'title'=>$_GPC['title'],'device_code'=>$_GPC['device_code'],'type'=>intval($_GPC['type']),'openurl'=>$_GPC['openurl'],'regionid'=>intval($_GPC['regionid']));
			if ($data['type']==1) 
			{
				$data['unit']=$_GPC['unit'];
			}
			else 
			{
				$data['unit']=' ';
			}
			if ($id) 
			{
				pdo_update('xcommunity_building_device',$data,array('id'=>$id));
			}
			else 
			{
				$data['uid']=$_W['uid'];
				pdo_insert('xcommunity_building_device',$data);
			}
			message('操作成功',$this->createWebUrl('building',array('op'=>'list','regionid'=>$regionid)),'success');
		}
		include($this->template('web/building/add'));
	}
	else 
	{
		if ($op=='delete') 
		{
			if ($id) 
			{
				$r=pdo_delete('xcommunity_building_device',array('id'=>$id));
				if ($r) 
				{
					message('操作成功',$this->createWebUrl('building',array('op'=>'list','regionid'=>$regionid)),'success');
				}
			}
		}
		else 
		{
			if ($op=='qrcreate') 
			{
				global $_W;
				global $_GPC;
				$id=intval($_GPC['id']);
				require_once(IA_ROOT.'/framework/library/qrcode/phpqrcode.php');
				$url=$_W['siteroot'].'app/index.php?i='.$_W['uniacid'].'&c=entry&id='.$id.'&do=lock&m=xfeng_community';
				$errorCorrectionLevel='L';
				$matrixPointSize=10;
				QRcode::png($url,'qrcode.png',$errorCorrectionLevel,$matrixPointSize,2);
				$QR='qrcode.png';
				echo '<img src=\'qrcode.png\' >';
			}
		}
	}
}
?>