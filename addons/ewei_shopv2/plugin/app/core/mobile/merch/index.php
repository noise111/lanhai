<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/merchbase.php");
class Index_EweiShopV2Page extends Merchbase_EweiShopV2Page{
    
    public function main(){
        global $_W;
        $merchid = $_W['merchid'];

        $merch = $this->account['list'][$merchid];
        $merch = set_medias($merch, "logo");
        if($merch && !empty($merch['payopenid'])){
            $member = m("member")->getMember($merch["payopenid"]);
            $merch['payopenid_text'] = $member['nickname'];
        }
        app_json($merch);
    }
    
    public function edit_merch(){
        global $_W;
        global $_GPC;
        $this->check_perm("mini.account.edit_merch");
        $merchid = $_W['merchid'];
        $data = array();
        isset($_GPC['merchname']) ?     $data['merchname'] = trim($_GPC['merchname']) : false;
        isset($_GPC['desc']) ?          $data['desc'] = trim($_GPC['desc']) : false;
        isset($_GPC['realname']) ?      $data['realname'] = trim($_GPC['realname']) : false;
        isset($_GPC['mobile']) ?        $data['mobile'] = trim($_GPC['mobile']) : false;
        isset($_GPC['payopenid']) ?     $data['payopenid'] = trim($_GPC['payopenid']) : false;
        if(!empty($data)){
            // if(!empty($data['merchname'])){
            //     $sql = "SELECT id FROM " . tablename("ewei_shop_merch_user") . " WHERE id <> $merchid AND merchname = '{$data['merchname']}' ";
            //     $db = pdo_fetch($sql);
            //     $db ? app_error(-1, "该零售商已存在") : false;
            // }
            pdo_update("ewei_shop_merch_user", $data, array("id" => $merchid));
        }
        app_json();
    }
    
    public function add_account(){
        $this->check_perm("mini.account.add");
        $this->post_account();
    }
    
    public function edit_account(){
        $this->check_perm("mini.account.edit");
        $this->post_account();
    }
    
    public function post_account(){
        global $_W;
        global $_GPC;
        $id =  intval($_GPC['id']);
        
        $data = array();
        isset($_GPC['account_openid']) ? $data['openid'] = trim($_GPC['account_openid']) : false;
        isset($_GPC['username']) ? $data['username'] = trim($_GPC['username']) : false;
        
        isset($_GPC['status']) ? $data['status'] = intval($_GPC['status']) : false;
              
        //edit
        if($id > 0){
            $old_db = pdo_get("ewei_shop_merch_account", array("id" => $id, "uniacid" => $_W['uniacid']));
            if(isset($data['openid']) && !empty($data['openid']) && $data['openid'] != $old_db['openid']){
                $db = pdo_get("ewei_shop_merch_account", array("openid" => $data['openid'] , "merchid" => $_W['merchid'] , "uniacid" => $_W['uniacid']));   
                if($db && $db['openid'] == $data['openid']){
                    app_error(-1, "该微信已存在");
                }
            }
            if(isset($data['username']) && !empty($data['username']) && trim($data['username']) != trim($old_db['username'])){
                $db = pdo_get("ewei_shop_merch_account", array("username" => trim($data['username']) , "merchid" => $_W['merchid'] , "uniacid" => $_W['uniacid']));   
                if($db && trim($db['username']) == trim($data['username']) ){
                    app_error(-1, "该子账号名称已存在");
                }
            }
            
            if($_GPC['pwd'] && $_GPC['pwd']!=''){
                $salt = random(8);
                $data['salt'] = $salt;
                $data['pwd']  = md5($_GPC['pwd'] . $salt);
            }

            pdo_update("ewei_shop_merch_account", $data, array("id" => $id));
            app_json();
        } else {
            isset($_GPC['pwd']) ? $data['pwd'] = trim($_GPC['pwd']) : false;

            if(empty($data['openid'])){
                app_error(-1, "请选择微信");
            }
            $db = pdo_get("ewei_shop_merch_account", array("openid" => $data['openid'],"merchid" => $_W['merchid'] , "uniacid" => $_W['uniacid']));
            if($db){
                app_error(-1, "该微信已存在");
            }
            if($data['username'] && !empty($data['username'])){
                $db = pdo_get("ewei_shop_merch_account", array("username" => $data['username']));
                if($db){
                    app_error(-1, "该子账号名称已存在");
                }
                empty($data['pwd']) ? app_error(-1, "请输入密码") : false;
                $salt = random(8);
                $data['salt'] = $salt;
                $data['pwd'] = md5(trim($data['pwd']) . $salt);
                $data['perms']       = serialize(array(  ));
                $data['isfounder'] = 0;
            }
            
            $data['uniacid'] = $_W['uniacid'];
            $data['merchid'] = $_W['merchid'];
            pdo_insert("ewei_shop_merch_account", $data);
            $id = pdo_insertid();
            m("notice")->sendChildAccountMessage($data['openid'], 1);
            app_json(array("id" => $id));
        }
    }
    
    public function get_detail(){
        global $_W;
        global $_GPC; 
        $params = array(
            ":id"      => $_GPC['id'],
            ":uniacid"      => $_W['uniacid']
        );
        $detail = pdo_fetch("SELECT * FROM " . tablename("ewei_shop_merch_account") . " WHERE id = :id AND uniacid = :uniacid $condition", $params);
        if($detail){
            $member = m("member")->getMember($detail["openid"]);
            $detail['openid_text'] = $member['nickname'];
        }else{
            app_error(-1, "该子账号不存在");
        }
        app_json(array("detail" => $detail));   
    }

    public function account_list(){
        global $_W;
        global $_GPC; 
        $this->check_perm("mini.account.list");
        $condition = "";
        if(isset($_GPC['status'])){
            $condition .= " AND status = {$_GPC['status']}";
        }
        $params = array(
            ":merchid"      => $_W['merchid'],
            ":uniacid"      => $_W['uniacid']
        );
        $list = pdo_fetchall("SELECT * FROM " . tablename("ewei_shop_merch_account") . " WHERE merchid = :merchid AND uniacid = :uniacid $condition", $params);
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
            pdo_update("ewei_shop_merch_user", array("payopenid" => $openid), array("id" => $_W['merchid']));
            app_json();
        } else {
            app_error(-1, "请选择openid");
        }
    }
}
