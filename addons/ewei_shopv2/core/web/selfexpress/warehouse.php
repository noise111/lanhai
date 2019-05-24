<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Warehouse_EweiShopV2Page extends WebPage
{
//	public function __construct($_com = 'verify')
//	{
//		parent::__construct($_com);
//	}

	public function main()
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$paras = array(':uniacid' => $_W['uniacid']);
        $condition = ' s.uniacid = :uniacid AND s.merchid = 0';
        
		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' AND (s.storename LIKE \'%' . $_GPC['keyword'] . '%\' OR s.address LIKE \'%' . $_GPC['keyword'] . '%\' OR s.tel LIKE \'%' . $_GPC['keyword'] . '%\')';
		}

		if (!empty($_GPC['type'])) {
			$type = intval($_GPC['type']);
			$condition .= ' AND type = :type';
			$paras[':type'] = $type;
		}

		$sql = 'SELECT s.*, u.merchname FROM ' . tablename('ewei_shop_selfexpress_warehouse') . 's LEFT JOIN '. tablename('ewei_shop_merch_user') .' u ON u.id = s.merchid  WHERE ' . $condition . ' ORDER BY displayorder desc,id desc';
		$sql .= ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
        $sql_count = 'SELECT count(*) FROM ' . tablename('ewei_shop_selfexpress_warehouse') . " s " . (' WHERE ' . $condition);

        $total = pdo_fetchcolumn($sql_count, $paras);

        $pager = pagination2($total, $pindex, $psize);

		$list = pdo_fetchall($sql, $paras);
		foreach ($list as &$row) {
			$row['salercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_saler') . ' where storeid=:storeid limit 1', array(':storeid' => $row['id']));
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
		$area_set = m('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);

		if ($_W['ispost']) {
			if (!empty($_GPC['perms'])) {
				$perms = implode(',', $_GPC['perms']);
			}
			else {
				$perms = '';
			}

//			if (empty($_GPC['logo'])) {
//				show_json(0, '仓库LOGO不能为空');
//			}
            
//            if($_GPC['province'] == '请选择省份' || $_GPC['city'] == '请选择城市' || $_GPC['area'] == '请选择区域'){
//                show_json(0, '请完善仓库的省市区信息');
//            }

			if (empty($_GPC['map']['lng']) || empty($_GPC['map']['lat'])) {
				show_json(0, '仓库位置不能为空');
			}

			if (empty($_GPC['address'])) {
				show_json(0, '仓库地址不能为空');
			}
			else {
				if (30 < mb_strlen($_GPC['address'], 'UTF-8')) {
					show_json(0, '仓库地址不能超过30个字符');
				}
			}

			$label = '';

			if (!empty($_GPC['lab'])) {
				if (8 < count($_GPC['lab'])) {
					show_json(0, '标签不能超过8个');
				}

				foreach ($_GPC['lab'] as $lab) {
					if (20 < mb_strlen($lab, 'UTF-8')) {
						show_json(0, '标签长度不能超过20个字符');
					}

					if (strlen(trim($lab)) <= 0) {
						show_json(0, '标签不能为空');
					}
				}

				$label = implode(',', $_GPC['lab']);
			}

			$tag = '';

			if (!empty($_GPC['tag'])) {
				if (3 < count($_GPC['tag'])) {
					show_json(0, '角标不能超过3个');
				}

				foreach ($_GPC['tag'] as $tg) {
					if (3 < mb_strlen($tg, 'UTF-8')) {
						show_json(0, '角标长度不能超过3个字符');
					}

					if (strlen(trim($tg)) <= 0) {
						show_json(0, '角标不能为空');
					}
				}

				$tag = implode(',', $_GPC['tag']);
			}

			$cates = '';

			if (!empty($_GPC['cates'])) {
				if (3 < count($_GPC['cates'])) {
					show_json(0, '仓库分类不能超过3个');
				}

				$cates = implode(',', $_GPC['cates']);
			}

//			if (empty($_GPC['tel']) || strlen(trim($_GPC['tel'])) <= 0) {
//				show_json(0, '仓库电话不能为空');
//			}
//			else {
//				if (20 < strlen($_GPC['tel'])) {
//					show_json(0, '仓库电话不能大于20个字符');
//				}
//			}

//			if (!empty($_GPC['saletime'])) {
//				if (20 < strlen($_GPC['saletime'])) {
//					show_json(0, '营业时间不能大于20个字符');
//				}
//			}

			$data = array(
                'uniacid' => $_W['uniacid'], 
                'storename' => trim($_GPC['storename']), 
                'address' => trim($_GPC['address']), 
                'province' => trim($_GPC['province']), 
                'city' => trim($_GPC['city']), 
                'area' => trim($_GPC['area']), 
                'provincecode' => trim($_GPC['chose_province_code']), 
                'citycode' => trim($_GPC['chose_city_code']), 
                'areacode' => trim($_GPC['chose_area_code']), 
                'tel' => trim($_GPC['tel']), 
                'lng' => $_GPC['map']['lng'], 
                'lat' => $_GPC['map']['lat'], 
                'type' => intval($_GPC['type']), 
                'realname' => trim($_GPC['realname']), 
                'mobile' => trim($_GPC['mobile']), 
                'label' => $label, 'tag' => $tag, 
                'fetchtime' => trim($_GPC['fetchtime']), 
                'saletime' => trim($_GPC['saletime']), 
                'logo' => save_media($_GPC['logo']), 
                'desc' => trim($_GPC['desc']), 
                'opensend' => intval($_GPC['opensend']), 
                'status' => intval($_GPC['status']), 
                'cates' => $cates, 
                'perms' => $perms,
                'merchid' => intval($_GPC['merchid'])
            );
            
            //获取地区码
            $area_code = m('common')->getAreaCode($_GPC['province'], $_GPC['city'], $_GPC['area']);
            $data['provincecode'] = $area_code['province'];
            $data['citycode'] = $area_code['city'];
            $data['areacode'] = $area_code['area'];

			if (p('newstore')) {
				$data['storegroupid'] = intval($_GPC['storegroupid']);
			}

			$data['order_printer'] = is_array($_GPC['order_printer']) ? implode(',', $_GPC['order_printer']) : '';
			$data['order_template'] = intval($_GPC['order_template']);
			$data['ordertype'] = is_array($_GPC['ordertype']) ? implode(',', $_GPC['ordertype']) : '';

			if (!empty($id)) {
                $haswarehouse = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_warehouse") . " where storename = :storename  and id != :id",array(":storename"=>$_GPC['storename'],":id"=>$id));
                if(!empty($haswarehouse)){
                    show_json(0, '仓库已存在');
                }
				pdo_update('ewei_shop_selfexpress_warehouse', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
				plog('selfexpress.warehouse.edit', '编辑仓库 ID: ' . $id);
			}
			else {

                $haswarehouse = pdo_fetch(" select * from " . tablename("ewei_shop_selfexpress_warehouse") . " where storename = :storename ",array(":storename"=>$_GPC['storename']));
                if(!empty($haswarehouse)){
                    show_json(0, '仓库已存在');
                }
				pdo_insert('ewei_shop_selfexpress_warehouse', $data);
				$id = pdo_insertid();
				plog('selfexpress.warehouse.add', '添加仓库 ID: ' . $id);
			}

			show_json(1, array('url' => webUrl('selfexpress.warehouse')));
		}

		if (p('newstore')) {
			$storegroup = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_newstore_storegroup') . ' WHERE  uniacid=:uniacid  ', array(':uniacid' => $_W['uniacid']));
			$category = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_newstore_category') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		}

		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_selfexpress_warehouse') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
		$perms = explode(',', $item['perms']);

		if ($printer = com('printer')) {
			$item = $printer->getStorePrinterSet($item);
			$order_printer_array = $item['order_printer'];
			$ordertype = $item['ordertype'];
			$order_template = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE uniacid=:uniacid AND merchid=0', array(':uniacid' => $_W['uniacid']));
		}

        //获取特约零售商信息
        $merch_model = p('merch');
        $merch_data = $merch_model->getMerch();
		$label = explode(',', $item['label']);
		$tag = explode(',', $item['tag']);
		$cates = explode(',', $item['cates']);
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

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_selfexpress_warehouse') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_delete('ewei_shop_selfexpress_warehouse', array('id' => $item['id']));
			plog('selfexpress.warehouse.delete', '删除仓库 ID: ' . $item['id'] . ' 仓库名称: ' . $item['storename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$displayorder = intval($_GPC['value']);
		$item = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_selfexpress_warehouse') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		if (!empty($item)) {
			pdo_update('ewei_shop_selfexpress_warehouse', array('displayorder' => $displayorder), array('id' => $id));
			plog('selfexpress.warehouse.edit', '修改仓库排序 ID: ' . $item['id'] . ' 仓库名称: ' . $item['storename'] . ' 排序: ' . $displayorder . ' ');
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

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_selfexpress_warehouse') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_update('ewei_shop_selfexpress_warehouse', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
			plog('selfexpress.warehouse.edit', '修改仓库状态<br/>ID: ' . $item['id'] . '<br/>仓库名称: ' . $item['storename'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function query()
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$limittype = empty($_GPC['limittype']) ? 0 : intval($_GPC['limittype']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and merchid =0  and status=1 ';

		if ($limittype == 0) {
			$condition .= '  and type in (1,2,3) ';
		}

		if (!empty($kwd)) {
			$condition .= ' AND `storename` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ds = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_selfexpress_warehouse') . (' WHERE 1 ' . $condition . ' order by id asc'), $params);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		include $this->template();
		exit();
	}

	public function querygoods()
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and deleted = 0 and `type` in (1,5,30)  and merchid =0';

		if (!empty($kwd)) {
			$condition .= ' AND `title` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}

		$ds = pdo_fetchall('SELECT id,title,thumb FROM ' . tablename('ewei_shop_goods') . (' WHERE 1 ' . $condition . ' order by createtime desc'), $params);
		$ds = set_medias($ds, array('thumb', 'share_icon'));

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		include $this->template();
	}
}

?>
