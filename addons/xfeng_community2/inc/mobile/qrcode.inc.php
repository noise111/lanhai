<?php  
global $_W;
global $_GPC;
$id=intval($_GPC['id']);
if (empty($id)) 
{
	message('非法操作',$this->createMobileUrl('home'),'error');
	exit(0);
}
$member=$this->member($_W['fans']['from_user']);
if ($member) 
{
	message('粉丝已绑定，无需在绑定',$this->createMobileUrl('home'),'error');
	exit(0);
}
$item=pdo_get('xcommunity_room',array('id'=>$id),array(0=>'id',1=>'regionid',2=>'realname',3=>'mobile',4=>'build',5=>'unit',6=>'room',7=>'house'));
if (empty($item)) 
{
	message('非法操作',$this->createMobileUrl('home'),'error');
	exit(0);
}
$xqset=$this->set(intval($item['regionid']),'xqset');
$build=$item['build'];
$unit=$item['unit'];
$room=$item['house'];
$address=$item['room'];
$data=array('weid'=>$_W['uniacid'],'createtime'=>TIMESTAMP,'regionid'=>$item['regionid'],'openid'=>$_W['fans']['from_user'],'realname'=>$item['realname'],'mobile'=>$item['mobile'],'build'=>$build,'unit'=>$unit,'room'=>$room,'address'=>$address,'type'=>0,'open_status'=>($xqset['door'] ? 1 : 0),'memberid'=>$_W['member']['uid'],'status'=>($xqset['verify'] ? 0 : 1),'enable'=>1);
if (pdo_insert('xcommunity_member',$data)) 
{
	message('绑定成功',$this->createMobileUrl('home'),'success');
}
?>