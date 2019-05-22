<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
class Index_EweiShopV2Page extends MerchWebPage
{
	public function __construct($_init = false, $_com = 'verify')
	{
		parent::__construct($_init, $_com);
	}

	public function main()
	{
		global $_W;
        global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$paras = array(':uniacid' => $_W['uniacid'], ':merchid' => $_W['merchid']);
		$condition = ' uniacid = :uniacid and merchid=:merchid';

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' AND (storename LIKE \'%' . $_GPC['keyword'] . '%\' OR address LIKE \'%' . $_GPC['keyword'] . '%\' OR tel LIKE \'%' . $_GPC['keyword'] . '%\')';
		}

		if (!empty($_GPC['type'])) {
			$type = intval($_GPC['type']);
			$condition .= ' AND type = :type';
			$paras[':type'] = $type;
		}

		$sql = 'SELECT * FROM ' . tablename('ewei_shop_store') . (' WHERE ' . $condition . ' ORDER BY displayorder desc,id desc');
		$sql .= ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		$sql_count = 'SELECT count(1) FROM ' . tablename('ewei_shop_store') . (' WHERE ' . $condition);
		$total = pdo_fetchcolumn($sql_count, $paras);
		$pager = pagination2($total, $pindex, $psize);
		$list = pdo_fetchall($sql, $paras);

		foreach ($list as &$row) {
			$row['salercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_saler') . ' where storeid=:storeid AND merchid=:merchid limit 1', array(':storeid' => $row['id'], $_W['merchid']));
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
        $area_set = m('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$id = intval($_GPC['id']);

		if ($_W['ispost']) {
            if($_GPC['province'] == '请选择省份' || $_GPC['city'] == '请选择城市' || $_GPC['area'] == '请选择区域'){
                show_json(0, '请完善门店的省市区信息');
            }
            if (empty($_GPC['map']['lng']) || empty($_GPC['map']['lat'])) {
				show_json(0, '门店位置不能为空');
			}
			$data = array(
                'uniacid' => $_W['uniacid'], 
                'storename' => trim($_GPC['storename']), 
                'address' => trim($_GPC['address']), 
                'tel' => trim($_GPC['tel']), 
                'lng' => $_GPC['map']['lng'], 
                'lat' => $_GPC['map']['lat'], 
                'type' => intval($_GPC['type']), 
                'realname' => trim($_GPC['realname']), 
                'mobile' => trim($_GPC['mobile']), 
                'fetchtime' => trim($_GPC['fetchtime']), 
                'saletime' => trim($_GPC['saletime']), 
                'logo' => save_media($_GPC['logo']), 
                'desc' => trim($_GPC['desc']), 
                'status' => intval($_GPC['status']), 
                'merchid' => intval($_W['merchid']),
                'province'  => trim($_GPC['province']),
                'city'  => trim($_GPC['city']),
                'area'  => trim($_GPC['area'])
            );
            //获取地区码
            $area_code = m('common')->getAreaCode($_GPC['province'], $_GPC['city'], $_GPC['area']);
            $data['provincecode'] = $area_code['province'];
            $data['citycode'] = $area_code['city'];
            $data['areacode'] = $area_code['area'];

			if (!empty($id)) {
				pdo_update('ewei_shop_store', $data, array('id' => $id, 'uniacid' => $_W['uniacid'], 'merchid' => intval($_W['merchid'])));
				mplog('store.edit', '编辑门店 ID: ' . $id);
			}
			else {
				pdo_insert('ewei_shop_store', $data);
				$id = pdo_insertid();
				mplog('store.add', '添加门店 ID: ' . $id);
			}

			show_json(1, array('url' => merchUrl('store')));
		}

		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_store') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
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

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid'] . ' and merchid=' . $_W['merchid']));

		foreach ($items as $item) {
			pdo_delete('ewei_shop_store', array('id' => $item['id']));
			mplog('store.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$displayorder = intval($_GPC['value']);
		$item = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid'] . ' and merchid=' . $_W['merchid']));

		if (!empty($item)) {
			pdo_update('ewei_shop_store', array('displayorder' => $displayorder), array('id' => $id));
			mplog('store.edit', '修改门店排序 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function status()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid'] . ' and merchid=' . $_W['merchid']));

		foreach ($items as $item) {
			pdo_update('ewei_shop_store', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
			mplog('store.edit', '修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['storename'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '启用' : '禁用');
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
		$params[':merchid'] = $_W['merchid'];
		$condition = ' and uniacid=:uniacid and merchid=:merchid and type in (2,3) and status=1';

		if (!empty($kwd)) {
			$condition .= ' AND `storename` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
        
		$ds = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE 1 ' . $condition . ' order by id asc'), $params);
		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		include $this->template('store/query');
		exit();
	}
}

?>
