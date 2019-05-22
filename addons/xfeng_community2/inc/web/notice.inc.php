<?php  
global $_GPC;
global $_W;
$do=$_GPC['do'];
$method=($_GPC['method']?$_GPC['method']:'manage');
$GLOBALS['frames']=$this->NavMenu($do,$method);
$xqmenu=$this->xqmenu();
$op=(!empty($_GPC['op'])?$_GPC['op']:'list');
$user=$this->user();
if ($op=='list') 
{
	$condition='uniacid=\''.$_W['uniacid'].'\'';
	if (($user['type']==2 || $user[type]==3)) 
	{
		$condition .=' AND uid=\''.$_W['uid'].'\'';
	}
	$list=pdo_getall('xcommunity_wechat_notice',$condition,array(0=>'fansopenid',1=>'type',2=>'repair_status',3=>'report_status',4=>'shopping_status',5=>'homemaking_status',6=>'cost_status',7=>'id',8=>'business_status'));
	foreach($list as $key=>$val)
	{
		load()->model('mc');
		$userinfo=mc_fetch($val['fansopenid']);
		$list[$key]['nickname']=$userinfo['nickname'];
		$member=$this->member($val['fansopenid']);
		$list[$key]['realname']=$member['realname'];
	}
	include($this->template('web/notice/list'));
}
else 
{
	if ($op=='add') 
	{
		$id=intval($_GPC['id']);
		if ($id) 
		{
			$item=pdo_fetch('SELECT * FROM'.tablename('xcommunity_wechat_notice').'WHERE uniacid=:uniacid AND id=:id',array(':uniacid'=>$_W['uniacid'],':id'=>$id));
			if (empty($item)) 
			{
				message('该信息不存在或已删除',referer(),'error');
			}
			$regions=$this->regions();
			$regs=iunserializer($item['regionid']);
			$regionid=ltrim(rtrim(implode(',',$regs)),',');
		}
		if ($_W['ispost']) 
		{
			$birth=$_GPC['birth'];
			$regionid=explode(',',$_GPC['regionid']);
			$data=array('uniacid'=>$_W['uniacid'],'fansopenid'=>$_GPC['fansopenid'],'repair_status'=>$_GPC['repair_status'],'report_status'=>$_GPC['report_status'],'shopping_status'=>$_GPC['shopping_status'],'business_status'=>$_GPC['business_status'],'homemaking_status'=>$_GPC['homemaking_status'],'cost_status'=>$_GPC['cost_status'],'change_status'=>$_GPC['change_status'],'type'=>intval($_GPC['type']),'province'=>$birth['province'],'city'=>$birth['city'],'dist'=>$birth['district'],'regionid'=>serialize($regionid),'notice_status'=>$_GPC['notice_status']);
			if ($id) 
			{
				if (pdo_update('xcommunity_wechat_notice',$data,array('id'=>$id))) 
				{
					message('提交成功',referer(),'success');
				}
			}
			else 
			{
				$data['uid']=$_W['uid'];
				if (pdo_insert('xcommunity_wechat_notice',$data)) 
				{
					message('提交成功',referer(),'success');
				}
			}
		}
		include($this->template('web/notice/add'));
	}
	else 
	{
		if ($op=='delete') 
		{
			$id=intval($_GPC['id']);
			if ($id) 
			{
				$r=pdo_delete('xcommunity_wechat_notice',array('id'=>$id));
				if ($r) 
				{
					$result=array('status'=>1);
					echo json_encode($result);
					exit(0);
				}
			}
		}
		else 
		{
			if ($op=='verify') 
			{
				$id=intval($_GPC['id']);
				$type=$_GPC['type'];
				$data=intval($_GPC['data']);
				if (in_array($type,array(0=>'repair_status',1=>'report_status',2=>'shopping_status',3=>'homemaking_status',4=>'cost_status',5=>'business_status'))) 
				{
					$data=($data==2 ? 1 : 2);
					pdo_update('xcommunity_wechat_notice',array($type=>$data),array('id'=>$id,'uniacid'=>$_W['uniacid']));
					exit(json_encode(array('result'=>1,'data'=>$data)));
				}
			}
		}
	}
}
?>