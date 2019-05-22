<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends WebPage {
    
    public function main(){
        global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$paras = array(':uniacid' => $_W['uniacid']);
		$condition = ' s.uniacid = :uniacid AND s.merchid > 0';

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' AND (s.storename LIKE \'%' . $_GPC['keyword'] . '%\' OR s.address LIKE \'%' . $_GPC['keyword'] . '%\' OR s.tel LIKE \'%' . $_GPC['keyword'] . '%\')';
		}

		if (!empty($_GPC['type'])) {
			$type = intval($_GPC['type']);
			$condition .= ' AND s.type = :type';
			$paras[':type'] = $type;
		}
        $join = " LEFT JOIN " . tablename('ewei_shop_merch_user') . " u ON u.id = s.merchid";
		$sql = 'SELECT s.*, u.merchname FROM ' . tablename('ewei_shop_store') . " s $join WHERE " . $condition . ' ORDER BY s.displayorder desc,s.id desc';
		$sql .= ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		$sql_count = 'SELECT count(1) FROM ' . tablename('ewei_shop_store') . (' s WHERE ' . $condition);
		$total = pdo_fetchcolumn($sql_count, $paras);
		$pager = pagination2($total, $pindex, $psize);
		$list = pdo_fetchall($sql, $paras);
        
		foreach ($list as &$row) {
			$row['salercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_saler') . ' where storeid=:storeid limit 1', array(':storeid' => $row['id']));
		}
        unset($row);
		include $this->template();
    }
    
    public function saler(){
        global $_W;
		global $_GPC;
		$condition = ' s.uniacid = :uniacid AND s.merchid > 0';
		$params = array(':uniacid' => $_W['uniacid']);
		if ($_GPC['status'] != '') 
		{
			$condition .= ' and s.status = :status';
			$params[':status'] = $_GPC['status'];
		}
		if (!(empty($_GPC['keyword']))) 
		{
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( s.salername like :keyword or m.realname like :keyword or m.mobile like :keyword or m.nickname like :keyword)';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}
		$sql = 'SELECT s.*,m.nickname,m.avatar,m.mobile,m.realname,store.storename, u.merchname ' .
               'FROM ' . tablename('ewei_shop_saler') . ' s ' .
               'LEFT JOIN ' . tablename('ewei_shop_merch_user') . ' u  ON s.merchid = u.id'. 
               ' left join ' . tablename('ewei_shop_member') . ' m on s.openid=m.openid and m.uniacid = s.uniacid ' . 
               ' left join ' . tablename('ewei_shop_store') . ' store on store.id=s.storeid ' . 
               ' WHERE ' . $condition . ' ORDER BY id asc';
		$list = pdo_fetchall($sql, $params);
        $roles = pdo_fetchall("SELECT * FROM" . tablename('ewei_shop_saler_role') . " WHERE uniacid = {$_W['uniacid']}", array(), 'id');
        foreach ($list as &$row){
            $row['rolename'] = $roles[$row['roleid']]['rolename'];
        }
        unset($row);
		include $this->template();
    }
    
    public function pass(){
        global $_W;
		global $_GPC;
        $id = intval($_GPC["id"]);
        $pass = intval($_GPC['pass']);
		if( empty($id) ) 
		{
			$id = (is_array($_GPC["ids"]) ? implode(",", $_GPC["ids"]) : 0);
		}
        
        $saler = m('store')->getSalerInfo(array('id' => $id));
        if($saler){ 
            
            if(empty($saler['passtime']) && $pass == 1){
                $now = time();
                $update_sql = "update " . tablename("ewei_shop_saler") . " set pass = $pass, passtime = $now where id in ( " . $id . " ) AND uniacid=" . $_W["uniacid"];
            } else {
                $update_sql = "update " . tablename("ewei_shop_saler") . " set pass = $pass where id in ( " . $id . " ) AND uniacid=" . $_W["uniacid"];
            }
            pdo_query($update_sql);
            $check_data = array(
                'passtime'      => $saler['passtime'],
                'pass'          => $pass
            );
            com('coupon')->checkSocialPoint($saler['openid'], 'newpass', $check_data);
            show_json(1, array( "url" => referer()));
        } else {
            show_json(0, '店员不存在');
        }
    }
}
