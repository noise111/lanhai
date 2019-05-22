<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Agentbase_EweiShopV2Page extends AppMobilePage{
    
    protected $is_main = false;

    public function __construct() {
        parent::__construct();
        global $_W;
        global $_GPC;
        $agent_id = isset($_GPC['agentid']) ? $_GPC['agentid'] : 0;
        if(!$_W['openid']){
            app_error(0, '请先登录');
        }
        $this->account = m('member')->getAgentInfo($_W['openid']);
        if($this->account['is_agent'] != 1){
            app_error(AppError::$AgentNotValid);
        }
        if(empty($agent_id) && !empty($this->account['list'])){
            $first = reset($this->account['list']);
            $_W['agentid'] = $first['id'];
        } else {
            $_W['agentid'] = isset($this->account['list'][$agent_id]) ? $agent_id : app_error(AppError::$AgentNotValid);
        }
        
        if($this->member['id'] == $agent_id){
            $this->is_main = true;
        }
    }
    
    public function getMerch($type = 1){
        global $_W;
        $condition = '';
        if(!$this->is_main){
            $condition .= " AND agent_account_id = {$this->member['id']}";
        }
        if($type == 1){
            $sql = "SELECT GROUP_CONCAT(id) FROM " . tablename("ewei_shop_merch_user") . 
                   " WHERE agentid = {$_W['agentid']} AND uniacid = {$_W['uniacid']} AND status = 1 $condition ";
            return pdo_fetchcolumn($sql);
        } else {
            $sql = "SELECT * FROM " . tablename("ewei_shop_merch_user") . 
                   " WHERE agentid = {$_W['agentid']} AND uniacid = {$_W['uniacid']} AND status = 1 $condition ";
            return pdo_fetchall($sql);
        }
    }
    
    public function getMerchStore($merchid = 0, $type = 1){
        global $_W;
        if($merchid){
            $merchs = $merchid;
        } else {
            $merchs = $this->getMerch(1);
        }
        if($type == 1){
            $sql = "SELECT GROUP_CONCAT(id) FROM " . tablename("ewei_shop_store") . 
                   " WHERE merchid IN($merchs) AND uniacid = {$_W['uniacid']} AND status = 1 ";
            return pdo_fetchcolumn($sql);
        } else {
            $sql = "SELECT * FROM " . tablename("ewei_shop_merch_user") . 
                   " WHERE merchid IN($merchs) AND uniacid = {$_W['uniacid']} AND status = 1 ";
            return pdo_fetchall($sql);
        }
    }
    
    /**
     * 获取经营天数
     */
    public function getAgentTime(){
        $member = $this->account;
        $days = ceil((time() - $member['aagenttime']) / (3600*24));
        return $days;
    }
}
