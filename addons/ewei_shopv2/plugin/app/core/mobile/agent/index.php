<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Index_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    
    public function main(){
        global $_W;
        $agent_id = $_W['agentid'];
        $member = $this->account['list'][$agent_id];
        $member['agent_province'] = iunserializer($member['aagentprovinces']);
        $member['agent_city'] = iunserializer($member['aagentcitys']);
        $member['agent_area'] = iunserializer($member['aagentareas']);
        $member['account_time'] = date("Y-m-d", $member['aagenttime'] + 24*3600*365*3);
        $member['days'] = $this->getAgentTime();
        $all_merchs = $this->getMerch(2);
        $member['merch_number'] = count($all_merchs);
        if($member && !empty($member['agent_payopenid'])){
            $member2 = m("member")->getMember($member["agent_payopenid"]);
            $member['payopenid_text'] = $member2['nickname'];
        }
        app_json($member);
    }
    
    public function add_account(){
        global $_W;
        global $_GPC;
        $account_openid = trim($_GPC['account_openid']);
        $status = intval($_GPC['status']);
        $agentid = isset($_GPC['agentid']) ? intval($_GPC['agentid']) : 0;
        if(empty($agentid)){
            $agentid = $_W['agentid'];
        }
        if(empty($account_openid)){
            app_error(-1, "openid不能为空");
        }
        $db = pdo_get("ewei_shop_agent_account", array("uniacid" => $_W['uniacid'], "openid" => $_W['openid'], "agentid" => $agentid));
        if($db){
            app_error(-1, "帐号已存在");
        }
        $data = array(
            "uniacid"           => $_W['uniacid'],
            "openid"            => $account_openid,
            "status"            => $status,
            "agentid"           => $_W['agentid']
        );
        pdo_insert("ewei_shop_agent_account", $data);
        $id = pdo_insertid();
        m("notice")->sendChildAccountMessage($data['openid'], 2);
        app_json(array("id" => $id));
    }
    
    public function delete_account(){
        global $_W;
        global $_GPC;
        $account_id = trim($_GPC['account_id']);
        $res = pdo_delete("ewei_shop_agent_account", array("uniacid" => $_W['uniacid'], "id" => $account_id, "agentid" => $_W['agentid']));
        if($res){
            app_json();
        } else {
            app_error(-1, "删除出错");
        }
    }
    
    public function account_list(){
        global $_W;
        global $_GPC; 
        $condition = "";
        if(isset($_GPC['status'])){
            $condition .= " AND status = {$_GPC['status']}";
        }
        $params = array(
            ":agentid"      => $_W['agentid'],
            ":uniacid"      => $_W['uniacid']
        );
        $list = pdo_fetchall("SELECT * FROM " . tablename("ewei_shop_agent_account") . " WHERE agentid = :agentid AND uniacid = :uniacid $condition", $params);
        foreach($list as &$account){
            $member = m("member")->getMemberBase($account['openid']);
            $account['nickname'] = $member['nickname'];
            $account['realname'] = $member['realname'];
            $account['avatar']   = $member['avatar'];
            $account['mobile']   = $member['mobile'];
        }
        app_json(array("list" => $list));
    }
    
    public function set_payopenid(){
        global $_W;
        global $_GPC;
        $openid = trim($_GPC['payopenid']);
        if(!empty($openid)){
            pdo_update("ewei_shop_member", array("agent_payopenid" => $openid), array("id" => $_W['agentid']));
            app_json();
        } else {
            app_error(-1, "请选择openid");
        }
    }
    
}

