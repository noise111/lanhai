<?php
if (!(defined('IN_IA'))) 
{

	exit('Access Denied');
}

class Agent_EweiShopV2Page extends PluginWebPage {

	public function main() {
		global $_W;
		global $_GPC;
		$aagentlevels = $this->model->getLevels(true, true);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$params = array();
		$condition = '';
		$keyword = trim($_GPC['keyword']);
		if (!(empty($searchfield)) && !(empty($keyword))) {
			$condition .= ' and ( dm.realname like :keyword or dm.nickname like :keyword or dm.mobile like :keyword)';
			$params[':keyword'] = '%' . $keyword . '%';
		}
		if ($_GPC['followed'] != '') {

			if ($_GPC['followed'] == 2) {
				$condition .= ' and f.follow=0 and dm.uid<>0';
			}
			else {
				$condition .= ' and f.follow=' . intval($_GPC['followed']);
			}
		}
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		if (!(empty($_GPC['time']['start'])) && !(empty($_GPC['time']['end']))) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND dm.aagenttime >= :starttime AND dm.aagenttime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}

		if ($_GPC['aagentlevel'] != '') {
			$condition .= ' and dm.aagentlevel=' . intval($_GPC['aagentlevel']);
		}
		if ($_GPC['aagenttype'] != '') {
			$condition .= ' and dm.aagenttype=' . intval($_GPC['aagenttype']);
		}
		if ($_GPC['aagentblack'] != '') {
			$condition .= ' and dm.aagentblack=' . intval($_GPC['aagentblack']);
		}
		if ($_GPC['aagentstatus'] != '') {
			$condition .= ' and dm.aagentstatus=' . intval($_GPC['aagentstatus']);
		}
		$sql = 'select dm.*,dm.nickname,dm.avatar,l.levelname,p.nickname as parentname,p.avatar as parentavatar from ' . tablename('ewei_shop_member') . ' dm ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('ewei_shop_abonus_level') . ' l on l.id = dm.aagentlevel' . ' where dm.uniacid = ' . $_W['uniacid'] . ' and dm.isaagent =1  ' . $condition . ' ORDER BY dm.aagenttime desc';
		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;

		}
		$list = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('select count(dm.id) from' . tablename('ewei_shop_member') . ' dm  ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('ewei_shop_abonus_level') . ' l on l.id = dm.aagentlevel' . ' where dm.uniacid =' . $_W['uniacid'] . ' and dm.isaagent =1  ' . $condition, $params);
		foreach ($list as &$row ) {
			$bonus = $this->model->getBonus($row['openid'], array('ok'));
			$row['bonus'] = $bonus['ok'];
			$row['followed'] = m('user')->followed($row['openid']);
            $row['agent_province']  = iunserializer($row['aagentprovinces']);
            $row['agent_city']      = iunserializer($row['aagentcitys']);
            $row['agent_area']      = iunserializer($row['aagentareas']);

		}
		unset($row);
		if ($_GPC['export'] == '1') {
			ca('abonus.agent.export');
			plog('abonus.agent.export', '导出区域运营中心数据');
			foreach ($list as &$row ) {
				$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
				$row['aagentime'] = ((empty($row['aagenttime']) ? '' : date('Y-m-d H:i', $row['aagentime'])));
				$row['groupname'] = ((empty($row['groupname']) ? '无分组' : $row['groupname']));
				$row['levelname'] = ((empty($row['levelname']) ? '普通等级' : $row['levelname']));
				$row['parentname'] = ((empty($row['parentname']) ? '总店' : '[' . $row['agentid'] . ']' . $row['parentname']));
				$row['statusstr'] = ((empty($row['status']) ? '' : '通过'));
				$row['followstr'] = ((empty($row['followed']) ? '' : '已关注'));
			}
			unset($row);
			m('excel')->export($list, array( 'title' => '区域运营中心数据-' . date('Y-m-d-H-i', time()), 'columns' => array( array('title' => 'ID', 'field' => 'id', 'width' => 12), array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '手机号', 'field' => 'mobile', 'width' => 12), array('title' => '微信号', 'field' => 'weixin', 'width' => 12), array('title' => 'openid', 'field' => 'openid', 'width' => 24), array('title' => '推荐人', 'field' => 'parentname', 'width' => 12), array('title' => '运营中心等级', 'field' => 'levelname', 'width' => 12), array('title' => '累计分红', 'field' => 'bonus', 'width' => 12), array('title' => '注册时间', 'field' => 'createtime', 'width' => 12), array('title' => '成为运营中心时间', 'field' => 'aagenttime', 'width' => 12), array('title' => '审核状态', 'field' => 'statusstr', 'width' => 12), array('title' => '是否关注', 'field' => 'followstr', 'width' => 12) ) ));
		}
		$pager = pagination($total, $pindex, $psize);
		load()->func('tpl');
		include $this->template();
	}

	public function delete() {
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}
		$members = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);
		foreach ($members as $member ) {
			pdo_update('ewei_shop_member', array('isaagent' => 0, 'aagentstatus' => 0), array('id' => $member['id']));
			plog('abonus.agent.delete', '取消区域运营中心资格 <br/>区域运营中心信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		}
		show_json(1, array('url' => referer()));
	}

	public function query() {
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$wechatid = intval($_GPC['wechatid']);
		if (empty($wechatid)) {
			$wechatid = $_W['uniacid'];
		}
		$params = array();
		$params[':uniacid'] = $wechatid;
		$condition = ' and uniacid=:uniacid and isaagent=1';
		if (!(empty($kwd))) {
			$condition .= ' AND ( `nickname` LIKE :keyword or `realname` LIKE :keyword or `mobile` LIKE :keyword )';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		if (!(empty($_GPC['selfid']))) {
			$condition .= ' and id<>' . intval($_GPC['selfid']);
		}
		$ds = pdo_fetchall('SELECT id,avatar,nickname,openid,realname,mobile FROM ' . tablename('ewei_shop_member') . ' WHERE 1 ' . $condition . ' order by createtime desc', $params);
		include $this->template('abonus/query');

	}

	public function check() {
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}
		$status = intval($_GPC['status']);
		$members = pdo_fetchall('SELECT id,openid,nickname,realname,mobile,aagentstatus FROM ' . tablename('ewei_shop_member') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);
		$time = time();
		foreach ($members as $member ) {
			if ($member['aagentstatus'] === $status) {
				continue;
			}
			if ($status == 1) {
				pdo_update('ewei_shop_member', array('aagentstatus' => 1, 'aagenttime' => $time), array('id' => $member['id'], 'uniacid' => $_W['uniacid']));
				plog('abonus.aagent.check', '审核运营中心 <br/>运营中心信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
				$this->model->sendMessage($member['openid'], array('nickname' => $member['nickname'], 'aagenttime' => $time), TM_ABONUS_BECOME);
			}
			else {
				pdo_update('ewei_shop_member', array('aagentstatus' => 0, 'aagenttime' => 0), array('id' => $member['id'], 'uniacid' => $_W['uniacid']));
				plog('abonus.agent.check', '取消审核 <br/>运营中心信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			}
		}
		show_json(1, array('url' => referer()));

	}

	public function aagentblack() {
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}
		$aagentblack = intval($_GPC['aagentblack']);
		$members = pdo_fetchall('SELECT id,openid,nickname,realname,mobile FROM ' . tablename('ewei_shop_member') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);
		foreach ($members as $member ) {
			if ($member['aagentblack'] === $aagentblack) {
				continue;
			}
			if ($aagentblack == 1) {
				pdo_update('ewei_shop_member', array('isaagent' => 1, 'aagentstatus' => 0, 'aagentblack' => 1), array('id' => $_GPC['id']));
				plog('abonus.agent.aagentblack', '设置黑名单 <br/>区域运营中心信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			}
			else {
				pdo_update('ewei_shop_member', array('isaagent' => 1, 'aagentstatus' => 1, 'aagentblack' => 0), array('id' => $_GPC['id']));
				plog('abonus.agent.aagentblack', '取消黑名单 <br/>区域运营中心信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			}
		}
		show_json(1, array('url' => referer()));

	}

    public function add(){
        $this->post();
    }
    
    public function edit(){
        $this->post();
    }
    
    public function post(){
        global $_W;
        global $_GPC;
        $id = $_GPC['id'];
        if($_W['ispost']){
            $data = array();
            isset($_GPC['isaagent']) ? $data['isaagent'] = intval($_GPC['isaagent']) : 0;
            isset($_GPC['aagentlevel']) ? $data['aagentlevel'] = intval($_GPC['aagentlevel']) : 0;
            isset($_GPC['aagenttype']) ? $data['aagenttype'] = intval($_GPC['aagenttype']) : 0;
            isset($_GPC['aagentstatus']) ? $data['aagentstatus'] = intval($_GPC['aagentstatus']) : 0;
            
            $aagentprovinces = (is_array($_GPC["aagentprovinces"]) ? $_GPC["aagentprovinces"] : array( ));
            $aagentcitys = (is_array($_GPC["aagentcitys"]) ? $_GPC["aagentcitys"] : array( ));
            $aagentareas = (is_array($_GPC["aagentareas"]) ? $_GPC["aagentareas"] : array( ));
            $data["aagentprovinces"] = iserializer($aagentprovinces);
            $data["aagentcitys"] = iserializer($aagentcitys);
            $data["aagentareas"] = iserializer($aagentareas);
            
            $com_model = m('common');
            if($aagentprovinces){
                $province_codes = array();
                foreach ($aagentprovinces as $province){
                    $p_code = $com_model->getAreaCode($province);
                    $province_codes[] = $p_code['province'];
                    //查是否有相同的运营中心省份
                    $sql = "SELECT openid FROM " . tablename("ewei_shop_member") . 
                           " WHERE id <> $id AND find_in_set('{$p_code['province']}', agent_province_code) AND isaagent = 1 AND aagentstatus = 1 AND aagenttype = 1";
                    $res = pdo_fetch($sql);
                    if($res){
                        show_json(0, "已经有运营中心了此区域：" . $province);
                    }
                }
                $data['agent_province_code'] = implode(',', $province_codes);
            } else {
                $data['agent_province_code'] = '';
            }
            
            if($aagentcitys){
                $city_codes = array();
                foreach ($aagentcitys as $city){
                    $city_arr = explode('-', $city);
                    $c_code = $com_model->getAreaCode($city_arr[0], $city_arr[1]);
                    $city_codes[] = $c_code['city'];
                    //查是否有相同的运营中心城市
                    $sql = "SELECT openid FROM " . tablename("ewei_shop_member") . 
                           " WHERE id <> $id AND find_in_set('{$c_code['city']}', agent_city_code) AND isaagent = 1 AND aagentstatus = 1 AND aagenttype = 2";
                    $res = pdo_fetch($sql);
                    if($res){
                        show_json(0, "已经有运营中心了此区域：" . $city);
                    }
                }
                $data['agent_city_code'] = implode(',', $city_codes);
            } else {
                $data['agent_city_code'] = '';
            }
            
            if($aagentareas){
                $area_codes = array();
                foreach ($aagentareas as $area){
                    $area_arr = explode('-', $area);
                    $a_code = $com_model->getAreaCode($area_arr[0], $area_arr[1], $area_arr[2]);
                    $area_codes[] = $a_code['area'];
                    //查是否有相同的运营中心区域
                    $sql = "SELECT openid FROM " . tablename("ewei_shop_member") . 
                           " WHERE id <> $id AND find_in_set('{$a_code['area']}', agent_area_code) AND isaagent = 1 AND aagentstatus = 1 AND aagenttype = 3";
                    $res = pdo_fetch($sql);
                    if($res){
                        show_json(0, "已经有运营中心了此区域：" . $area);
                    }
                }
                $data['agent_area_code'] = implode(',', $area_codes);
            } else {
                $data['agent_area_code'] = '';
            }
            
            if($data['aagenttype'] == 1){
                $data["aagentcitys"] = iserializer(array( ));
                $data["aagentareas"] = iserializer(array( ));
            } else if( $data["aagenttype"] == 2 ) {
                $data["aagentprovinces"] = iserializer(array( ));
                $data["aagentareas"] = iserializer(array( ));
            }
            else if( $data["aagenttype"] == 3 ) {
                $data["aagentprovinces"] = iserializer(array( ));
                $data["aagentcitys"] = iserializer(array( ));
            } else {
                $data["aagentprovinces"] = iserializer(array( ));
                $data["aagentcitys"] = iserializer(array( ));
                $data["aagentareas"] = iserializer(array( ));
            }
            
            if(!$id){
                $openid = trim($_GPC['openid']);
                if(empty($openid)){
                    show_json(0, "请选择运营微信号");
                }
                if($data['isaagent'] && $data['aagentstatus']){
                    $data['aagenttime'] = time();
                }
                $member = m("member")->getMemberBase($openid);
                $id = $member['id'];
            }
            pdo_update("ewei_shop_member", $data, array("id" => $id));
            show_json();
        }
        
        if($id){
            $member = pdo_get("ewei_shop_member", array("id" => $id));
            $member['aagentprovinces'] = iunserializer($member['aagentprovinces']);
            $member['aagentcitys'] = iunserializer($member['aagentcitys']);
            $member['aagentareas'] = iunserializer($member['aagentareas']);
        }

        include $this->template("abonus/post");
    }
}

?>