<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Personnel_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' s.uniacid = :uniacid AND s.merchid = 0 AND s.is_delete = 0';
		$params = array(':uniacid' => $_W['uniacid']);

		if ($_GPC['status'] != '') {
			$condition .= ' and s.status = :status';
			$params[':status'] = $_GPC['status'];
		}

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( s.salername like :keyword or m.realname like :keyword or m.mobile like :keyword or m.nickname like :keyword)';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}

		$sql = 'SELECT s.*,m.nickname,m.avatar,m.realname,store.storename FROM ' . 
                tablename('ewei_shop_selfexpress_personnel') . '  s ' .
                ' left join ' . tablename('ewei_shop_member') . ' m on s.openid=m.openid and m.uniacid = s.uniacid ' . 
                ' left join ' . tablename('ewei_shop_store') . ' store on store.id=s.storeid ' . 
                ' WHERE ' . $condition . ' ORDER BY id asc';

        $sql .= ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
        $sql_count = 'SELECT count(*) FROM ' . tablename('ewei_shop_selfexpress_personnel') . " s " . (' WHERE ' . $condition);

        $total = pdo_fetchcolumn($sql_count, $params);

        $pager = pagination2($total, $pindex, $psize);

        $list = pdo_fetchall($sql, $params);
        $roles = pdo_fetchall("SELECT * FROM" . tablename('ewei_shop_saler_role') . " WHERE uniacid = {$_W['uniacid']}", array(), 'id');
        foreach ($list as &$row){
            $row['rolename'] = $roles[$row['roleid']]['rolename'];
        }
        unset($row);
		include $this->template();
	}

	public function add()
	{
		$this->post();
	}

	public function edit()
	{
		$this->post();
	}

	protected function post()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_selfexpress_personnel') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
//		print_r($item);
		if ($_W['ispost']) {
			$data = array(
                'uniacid' => $_W['uniacid'],
                'storeid' => intval($_GPC['storeid']),
                'openid' => trim($_GPC['openid']), 
                'status' => intval($_GPC['status']),
                'salername' => trim($_GPC['salername']),
                'mobile' => trim($_GPC['mobile']),
//                'roleid' => intval($_GPC['roleid'])
            );
//            show_json(0, array("list"=>$data));
			if(empty($_GPC['id'])){
                $haspersonnel = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_personnel") . " where salername = :salername ",array(":salername"=>$_GPC['salername']));
                if(!empty($haspersonnel)){
                    show_json(0, '配送员已存在');
                }

                $haspersonnelmobile = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_personnel") . " where mobile = :mobile ",array(":mobile"=>$_GPC['mobile']));
                if(!empty($haspersonnelmobile)){
                    show_json(0, '手机号码已存在');
                }
                pdo_insert('ewei_shop_selfexpress_personnel', $data);
                $id = pdo_insertid();
                plog('selfexpress.personnel.add', '添加配送员 ID: ' . $id . '  <br/>配送员信息: ID: ' . $id . ' / ' . $data['salername'] . '/' . $data['mobile'] .  ' ');
			}else{

                $haspersonnel = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_personnel") . " where salername = :salername  and id != :id",array(":salername"=>$_GPC['salername'],":id"=>$id));
                if(!empty($haspersonnel)){
                    show_json(0, '配送员已存在');
                }
                $haspersonnelmobile = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_personnel") . " where mobile = :mobile  and id != :id",array(":mobile"=>$_GPC['mobile'],":id"=>$id));
                if(!empty($haspersonnelmobile)){
                    show_json(0, '手机号码已存在');
                }
//                show_json(0, array('url' => $data));
                pdo_update('ewei_shop_selfexpress_personnel', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
				plog('selfexpress.personnel.edit', '编辑配送员 ID: ' . $id . ' <br/>配送员信息: ID: ' . $_GPC['id'] . ' / ' . $data['salername'] . '/' . $data['mobile'] .  ' ');

			}
			show_json(1, array('url' => webUrl('selfexpress/personnel')));
		}
        $role = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_saler_role') . ' WHERE uniacid=:uniacid AND merchid = 0', array(':uniacid' => $_W['uniacid']));
		include $this->template();
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,salername FROM ' . tablename('ewei_shop_selfexpress_personnel') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			//pdo_delete('ewei_shop_selfexpress_personnel', array('id' => $item['id']));
            pdo_update("ewei_shop_selfexpress_personnel", array('is_delete' => 1), array('id' => $item['id']));
			plog('selfexpress.personnel.delete', '删除配送员 ID: ' . $item['id'] . ' 配送员名称: ' . $item['salername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function status()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,salername FROM ' . tablename('ewei_shop_selfexpress_personnel') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_update('ewei_shop_selfexpress_personnel', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
			plog('selfexpress.personnel.edit', '修改配送员状态<br/>ID: ' . $item['id'] . '<br/>配送员名称: ' . $item['salername'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function query()
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and s.uniacid=:uniacid';

		if (!empty($kwd)) {
			$condition .= ' AND ( m.nickname LIKE :keyword or m.realname LIKE :keyword or m.mobile LIKE :keyword or store.storename like :keyword )';
			$params[':keyword'] = '%' . $kwd . '%';
		}

		$ds = pdo_fetchall('SELECT s.*,m.nickname,m.avatar,m.mobile,m.realname,store.storename FROM ' . tablename('ewei_shop_selfexpress_personnel') . '  s ' . ' left join ' . tablename('ewei_shop_member') . ' m on s.openid=m.openid ' . ' left join ' . tablename('ewei_shop_store') . ' store on store.id=s.storeid ' . (' WHERE 1 ' . $condition . ' ORDER BY id asc'), $params);
		include $this->template();
		exit();
	}
}

?>
