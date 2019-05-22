<?php  
global $_GPC;
global $_W;
load()->classs('wesession');
WeSession::start($_W['uniacid'],$_W['fans']['from_user'],60);
$mobile=$_GPC['mobile'];
load()->model('mc');
$r=mc_check(array('mobile'=>$mobile));
if (empty($r)) 
{
	$result=array('status'=>2);
	echo json_encode($result);
	exit(0);
}
else 
{
	if ($mobile==$_SESSION['mobile']) 
	{
		$code=$_SESSION['code'];
	}
	else 
	{
		$code=random(6,1);
		$_SESSION['mobile']=$mobile;
		$_SESSION['code']=$code;
	}
	$key='sms';
	$set=ln_syssetting_read('',$key);
	if ($set['tysms']) 
	{
		if ($set['code']) 
		{
			if ($set['api']==1) 
			{
				$sms=ln_syssetting_read('','smswwt');
				$sdst=$_SESSION['mobile'];
				$smsg='您的验证码是：'.$code.'。请不要把验证码泄露给其他人。';
				$sname=$sms['account'];
				$spwd=$sms['pwd'];
				$sign=$sms['sign'];
				$scorpid='';
				$sprdid=1012888;
				$smsg=$smsg.'【'.$sign.'】';
				$params='sname='.$sname.'&spwd='.$spwd.'&scorpid='.$scorpid.'&sprdid='.$sprdid.'&sdst='.$sdst.'&smsg='.rawurlencode($smsg);
				$url='http://cf.51welink.com/submitdata/Service.asmx/g_Submit';
				load()->func('communication');
				$content=ihttp_post($url,$params);
			}
			else 
			{
				if ($set['api']==2) 
				{
					$sms=ln_syssetting_read('','smsjh');
					$mobile=$_SESSION['mobile'];
					$tpl_id=$sms['resgisterid'];
					$tpl_value=urlencode('#code#='.$code);
					$appkey=$sms['sms_account'];
					$params='mobile='.$mobile.'&tpl_id='.$tpl_id.'&tpl_value='.$tpl_value.'&key='.$appkey;
					$url='http://v.juhe.cn/sms/send';
					load()->func('communication');
					$content=ihttp_post($url,$params);
					return $content;
				}
			}
		}
	}
	else 
	{
		$xqset=ln_syssetting_read(intval($_GPC['regionid']),'xqsms');
		if ($xqset['code']) 
		{
			if ($xqset['api']==1) 
			{
				$sdst=$_SESSION['mobile'];
				$smsg='您的验证码是：'.$code.'。请不要把验证码泄露给其他人。';
				$sname=$xqset['account'];
				$spwd=$xqset['pwd'];
				$sign=$xqset['sign'];
				$scorpid='';
				$sprdid=1012888;
				$smsg=$smsg.'【'.$sign.'】';
				$params='sname='.$sname.'&spwd='.$spwd.'&scorpid='.$scorpid.'&sprdid='.$sprdid.'&sdst='.$sdst.'&smsg='.rawurlencode($smsg);
				$url='http://cf.51welink.com/submitdata/Service.asmx/g_Submit';
				load()->func('communication');
				$content=ihttp_post($url,$params);
			}
			else 
			{
				if ($xqset['api']==2) 
				{
					$mobile=$_SESSION['mobile'];
					$tpl_id=$xqset['resgisterid'];
					$tpl_value=urlencode('#code#='.$code);
					$appkey=$xqset['sms_account'];
					$params='mobile='.$mobile.'&tpl_id='.$tpl_id.'&tpl_value='.$tpl_value.'&key='.$appkey;
					$url='http://v.juhe.cn/sms/send';
					load()->func('communication');
					$content=ihttp_post($url,$params);
					return $content;
				}
			}
		}
	}
}
?>