<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Receiver extends WeModuleReceiver
{
	public function receive()
	{
		global $_W;
		$type = $this->message['type'];
		$event = $this->message['event'];
		if ($event == 'subscribe' && $type == 'subscribe') {
			$this->saleVirtual();    
            $this->firstSubscribe();
		}       
	}

	public function saleVirtual($obj = NULL)
	{
		global $_W;

		if (empty($obj)) {
			$obj = $this;
		}

		$sale = m('common')->getSysset('sale');
		$data = $sale['virtual'];

		if (empty($data['status'])) {
			return false;
		}

		load()->model('account');
		$account = account_fetch($_W['uniacid']);
		$open_redis = function_exists('redis') && !is_error(redis());

		if ($open_redis) {
			$redis_key1 = 'ewei_' . $_W['uniacid'] . '_member_salevirtual_isagent';
			$redis_key2 = 'ewei_' . $_W['uniacid'] . '_member_salevirtual';
			$redis = redis();

			if (!is_error($redis)) {
				if ($redis->get($redis_key1) != false) {
					$totalagent = $redis->get($redis_key1);
					$totalmember = $redis->get($redis_key2);
				}
				else {
					$totalagent = pdo_fetchcolumn('select count(*) from' . tablename('ewei_shop_member') . ' where uniacid =' . $_W['uniacid'] . ' and isagent =1');
					$totalmember = pdo_fetchcolumn('select count(*) from' . tablename('ewei_shop_member') . ' where uniacid =' . $_W['uniacid']);
					$redis->set($redis_key1, $totalagent, array(0 => 'nx', 'ex' => '3600'));
					$redis->set($redis_key2, $totalmember, array(0 => 'nx', 'ex' => '3600'));
				}
			}
		}
		else {
			$totalagent = pdo_fetchcolumn('select count(*) from' . tablename('ewei_shop_member') . ' where uniacid =' . $_W['uniacid'] . ' and isagent =1');
			$totalmember = pdo_fetchcolumn('select count(*) from' . tablename('ewei_shop_member') . ' where uniacid =' . $_W['uniacid']);
		}

		$acc = WeAccount::create();
		$member = abs((int) $data['virtual_people']) + (int) $totalmember;
		$commission = abs((int) $data['virtual_commission']) + (int) $totalagent;
		$user = m('member')->checkMemberFromPlatform($obj->message['from'], $acc);

		if ($_SESSION['eweishop']['poster_member']) {
			$user['isnew'] = true;
			$_SESSION['eweishop']['poster_member'] = NULL;
		}

		if ($user['isnew']) {
			$message = str_replace('[会员数]', $member, $data['virtual_text']);
			$message = str_replace('[排名]', $member + 1, $message);
		}
		else {
			$message = str_replace('[会员数]', $member, $data['virtual_text2']);
		}

		$message = str_replace('[分销商数]', $commission, $message);
		$message = str_replace('[昵称]', $user['nickname'], $message);
		$message = htmlspecialchars_decode($message, ENT_QUOTES);
		$message = str_replace('"', '\\"', $message);
		return $this->sendText($acc, $obj->message['from'], $message);
	}

	public function sendText($acc, $openid, $content)
	{
		$send['touser'] = trim($openid);
		$send['msgtype'] = 'text';
		$send['text'] = array('content' => urlencode($content));
		$data = $acc->sendCustomNotice($send);
		return $data;
	}
    
    /**
     *  判断用户是否是首次关注
     */
    public function firstSubscribe(){
        global $_W;
        $openid = $this->message['from'];
        $member_model = m('member');
        $member = $member_model->getMember($openid);
        if(empty($member)){
            //创建人人商城会员信息
            $fans = mc_fansinfo($openid);
            $member = array( 
                "uniacid" => $_W["uniacid"], 
                "uid" => $fans['uid'], 
                "openid" => $openid, 
                "realname" => (!empty($fans["realname"]) ? $fans["realname"] : ""), 
                "mobile" => (!empty($fans["mobile"]) ? $fans["mobile"] : ""), 
                "nickname" => (!empty($fans["nickname"]) ? $fans["nickname"] : ""), 
                "nickname_wechat" => (!empty($fans["nickname"]) ? $fans["nickname"] : ""), 
                "avatar" => (!empty($fans["avatar"]) ? $fans["avatar"] : ""), 
                "avatar_wechat" => (!empty($fans["avatar"]) ? $fans["avatar"] : ""), 
                "gender" => (!empty($fans["gender"]) ? $fans["gender"] : "-1"),
                "province" => (!empty($fans["tag"]["province"]) ? $fans["tag"]["province"] : ""), 
                "city" => (!empty($fans["tag"]["city"]) ? $fans["tag"]["city"] : ""), 
                "area" => (!empty($fans["tag"]["area"]) ? $fans["tag"]["area"] : ""), 
                "createtime" => time(), 
                "status" => 0 
            );
            if(isset($fans['unionid']) && !empty($fans['unionid'])){
                $union_member = $member_model->getMember($fans['unionid']);
                if(!empty($union_member)){
                    pdo_update("ewei_shop_member", array("ori_openid" => $openid), array("id" => $union_member['id']));
                    return;
                }
                $member['openid'] = $fans['unionid'];
                $member['ori_openid'] = $openid;
                $member['unionid'] = $fans['unionid'];
            }           
            
            pdo_insert("ewei_shop_member", $member);
            //如果关注了，就读取绑定送抽奖活动信息
            if($fans['follow']){               
                //绑定送抽奖
                $lotery_params = array(
                    ":uniacid"		=> $_W['uniacid'],
                    ":task_type"	=> 5
                );
                $sql = "SELECT * FROM " . tablename("ewei_shop_lottery") . " WHERE uniacid = :uniacid AND is_delete = 0 AND task_type = :task_type";
                $lotery = pdo_fetchall($sql, $lotery_params);
                if($lotery && count($lotery)>0){					
                    $addtime = time();
                    foreach($lotery as $row){
                        $lotery_id = $row['lottery_id'];
                        $task_data = unserialize($row['task_data']);
                        $number = $task_data['first_follow_number'];
                        $insert = array(
                            "uniacid"		=> $_W['uniacid'],
                            "join_user"		=> $openid,
                            "lottery_id"	=> $row['lottery_id'],
                            "lottery_num"	=> 1,
                            "lottery_tag"	=> "首次绑定抽奖",
                            "addtime"		=> $addtime
                        );
                        if($number){
                            for($i = 0; $i < $number; $i++){
                                pdo_insert("ewei_shop_lottery_join", $insert);
                            }
                        }
                    }
                }
                $lotery_id ? : $lotery_id = 0;
                //m("message")->sendCustomNotice($openid, $entrytext);
                $datas = array( array('name' => '活动名称', 'value' => 'ceshi') );
                $url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shopv2&do=mobile&r=lottery.index.lottery_info&id=' .$lotery_id; 
                $url = str_replace('addons/ewei_shopv2/', '', $url);
                $entrytext = "欢迎关注公众号，您获得了抽奖机会！";
                $remark = '<a href=\'' . $url . '\'>点此抽奖</a>';
                $text = $entrytext . $remark;
                $message = array( 'first' => array('value' => $entrytext . '恭喜您获得抽奖机会', 'color' => '#000000'), 'keyword1' => array('value' => 'ceshi', 'color' => '#000000'), 'keyword2' => array('value' => '获得抽奖机会', 'color' => '#000000'), 'remark' => array('value' => '恭喜您获得抽奖机会！！', 'color' => '#000000') );
                m('notice')->sendNotice(array('openid' => $openid, 'tag' => 'lottery_get', 'default' => $message, 'cusdefault' => $text, 'url' => $url, 'datas' => $datas));
            }
        }
    }
}

?>
