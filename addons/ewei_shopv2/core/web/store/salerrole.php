<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Salerrole_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$condition = ' r.uniacid = :uniacid AND r.merchid = 0';
		$params = array(':uniacid' => $_W['uniacid']);

		if ($_GPC['status'] != '') {
			$condition .= ' and r.status = :status';
			$params[':status'] = $_GPC['status'];
		}

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( r.rolename like :keyword )';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}

		$sql = 'SELECT r.* FROM ' . 
                tablename('ewei_shop_saler_role') . '  r ' . 
                ' WHERE ' . $condition . ' ORDER BY id asc';
		$list = pdo_fetchall($sql, $params);
		include $this->template();
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
        
        $id = intval($_GPC['id']);
        if($_W['ispost']){
            $permission = array();
            $permission['shoppingguide']    = isset($_GPC['shoppingguide']) ?  1 : 0;
            $permission['storemanager']     = isset($_GPC['storemanager']) ?  1 : 0;
            $permission['verify']           = isset($_GPC['verify']) ?  1 : 0;
            $permission['deliver']          = isset($_GPC['deliver']) ?  1 : 0;
            if(!empty($id)){
                $update = array();
                isset($_GPC['rolename'])? $update['rolename'] = trim($_GPC['rolename']) : false;
                isset($_GPC['status'])? $update['status'] = intval($_GPC['status']) : false;
                isset($_GPC['rolekey']) ? $update['rolekey'] = trim($_GPC['rolekey']) : false;
                $params = array(
                    'uniacid'      => $_W['uniacid'],
                    'id'           => $id
                );
                $update = array_merge($update, $permission);
                $result = pdo_update('ewei_shop_saler_role', $update, $params);
                $result ? show_json(1, array('url' => webUrl('store/salerrole'))) : show_json(0, '修改失败');
            } else {        
                $insert = array(
                    'uniacid'       => $_W['uniacid'],
                    'rolename'      => trim($_GPC['rolename']),
                    'status'        => intval($_GPC['status']),
                    'merchid'       => 0
                );
                $insert = array_merge($insert, $permission);
                $id = pdo_insert('ewei_shop_saler_role', $insert);
                if($id){
                    show_json(1, array('id' => $id));
                } else {
                    show_json(0, '已有相同的角色');
                }
            }
        } else {
            $sql = "SELECT * FROM " . tablename('ewei_shop_saler_role') . " WHERE uniacid = :uniacid AND id = :id";
            $parmas = array(':uniacid' => $_W['uniacid'], ':id' => $id);
            $item = pdo_fetch($sql, $parmas);
        }
        
        include $this->template();
    }
    
    public function status()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,rolename FROM ' . tablename('ewei_shop_saler_role') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);
        
		foreach ($items as $item) {
			pdo_update('ewei_shop_saler_role', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
			plog('store.salerrole.edit', '修改角色状态<br/>ID: ' . $item['id'] . '<br/>角色名称: ' . $item['rolename'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}
    
    public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,rolename FROM ' . tablename('ewei_shop_saler_role') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_delete('ewei_shop_saler_role', array('id' => $item['id']));
			plog('store.salerrole.delete', '删除角色 ID: ' . $item['id'] . ' 角色名称: ' . $item['rolename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}
}
?>
