<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends ComWebPage
{
	public function __construct($_com = 'verify')
	{
		parent::__construct($_com);
	}

    public function main(){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
        $params=array();
        $condition = "";
        if($_GPC['status'] == 1){
            $condition .= " and status = 0 ";
		}
        if($_GPC['status'] == 2){
            $condition .= " and status = 1 ";
        }



        if( empty($starttime) || empty($endtime) )
        {
            $starttime = strtotime("-1 month");
            $endtime = time();
        }
        $searchtime = trim($_GPC["searchtime"]);
        if( !empty($searchtime) && is_array($_GPC["time"]) && in_array($searchtime, array( "create", "finish" )) )
        {
            $starttime = strtotime($_GPC["time"]["start"]);
            $endtime = strtotime($_GPC["time"]["end"]);
            $condition .= " AND  " . $searchtime . "time >= :starttime AND  " . $searchtime . "time <= :endtime ";
            $params[":starttime"] = $starttime;
            $params[":endtime"] = $endtime;
        }


        if( !empty($_GPC["searchfield"]) && !empty($_GPC["keyword"]) ) {
            $searchfield = trim(strtolower($_GPC["searchfield"]));
            $_GPC["keyword"] = trim($_GPC["keyword"]);
            $paras[":keyword"] = htmlspecialchars_decode($_GPC["keyword"], ENT_QUOTES);
            if( $searchfield == "name" )
            {
                $condition .= " and `name` like " . "'%" . $paras[":keyword"] . "%'";
            }
            if( $searchfield == "mobile" )
            {
                $condition .= " and `mobile` like " . "'%" . $paras[":keyword"] . "%'";
            }
            if( $searchfield == "add" )
            {
                $condition .= " and `add` like " . "'%" . $paras[":keyword"] . "%'";
            }
        }
        $sql = " select * from ims_ewei_shop_reservation where uniacid=$uniacid " . $condition ;
        $list = (pdo_fetchall($sql,$params));
//        $list = pdo_debug(pdo_fetchall($sql,$params));
//        print_r($list);die;
        $array = array(
            "1" => "一室",
            "2" => "二室一厅",
            "3" => "二室二厅",
            "4" => "三室一厅",
            "5" => "三室二厅",
            "6" => "四室",
            "7" => "错层",
            "8" => "跃层或复式",
            "9" => "联排别墅",
            "10" => "叠加别墅",
            "11" => "独栋别墅",
        );

        foreach ($list as $k => $v){
            $list[$k]['housetypename'] = $array[$list[$k]['housetype']];
        }
//        print_r($list);

//        $reservationsaler = (pdo_fetchall(" select * from ims_ewei_shop_reservation_saler where status = 1 and merchid=0 and is_delete = 0 "));
//        print_r($reservationsaler);
        include($this->template('reservation/reservationlist'));
    }



    public function detail(){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
        $id = intval($_GPC['id']);
        $item = pdo_fetch(" select * from ims_ewei_shop_reservation  where uniacid={$uniacid} and id = {$id} ");
//        print_r($list);
        $reservationsaler = (pdo_fetchall(" select * from ims_ewei_shop_reservation_saler where status = 1 and merchid=0 and is_delete = 0 "));
//        print_r($reservationsaler);
		$diylist = pdo_fetch(" select * from ims_ewei_shop_diyform_type where title='服务申请表单' ");
        $diylist['fields'] = unserialize($diylist['fields']);
        $housetype = $diylist['fields']['diyhuxing']['tp_text'];
//        for($i=0; $i<=count($housetype); $i++){
//            $housetype[$i] = "'". $housetype[$i] ."'";
//		}
//        print_r(count($housetype));
        $array = array(
            "1" => "一室",
            "2" => "二室一厅",
            "3" => "二室二厅",
            "4" => "三室一厅",
            "5" => "三室二厅",
            "6" => "四室",
            "7" => "错层",
            "8" => "跃层或复式",
            "9" => "联排别墅",
            "10" => "叠加别墅",
            "11" => "独栋别墅",
        );

        if ($_W['ispost']) {
        	if(empty($_GPC['merchid'])){
                show_json(0, '请分配服务人员');
			}
			$data = array();
        	$data['id'] = $_GPC['id'];
        	$data['workerid'] = $_GPC['merchid'];
        	$data['name'] = $_GPC['realname'];
        	$data['mobile'] = $_GPC['mobile'];
        	$data['area'] = $_GPC['area'];
        	$data['add'] = $_GPC['desc'];
        	if($_GPC['status']==1 && empty($_GPC['finishtime'])){
                $data['finishtime'] = time();
			}
            if($_GPC['status']==0){
                $data['finishtime'] = '';
            }
        	$data['status'] = $_GPC['status'];
        	$data['housetype'] = $_GPC['housetype'];
        	$data['remark'] = $_GPC['remark'];
        	pdo_update("ewei_shop_reservation",$data);
            show_json(1, '成功');
		}
        include($this->template('reservation/detail'));
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

			if (empty($_GPC['logo'])) {
				show_json(0, '门店LOGO不能为空');
			}
            
            if($_GPC['province'] == '请选择省份' || $_GPC['city'] == '请选择城市' || $_GPC['area'] == '请选择区域'){
                show_json(0, '请完善门店的省市区信息');
            }

			if (empty($_GPC['map']['lng']) || empty($_GPC['map']['lat'])) {
				show_json(0, '门店位置不能为空');
			}

			if (empty($_GPC['address'])) {
				show_json(0, '门店地址不能为空');
			}
			else {
				if (30 < mb_strlen($_GPC['address'], 'UTF-8')) {
					show_json(0, '门店地址不能超过30个字符');
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
					show_json(0, '门店分类不能超过3个');
				}

				$cates = implode(',', $_GPC['cates']);
			}

			if (empty($_GPC['tel']) || strlen(trim($_GPC['tel'])) <= 0) {
				show_json(0, '门店电话不能为空');
			}
			else {
				if (20 < strlen($_GPC['tel'])) {
					show_json(0, '门店电话不能大于20个字符');
				}
			}

			if (!empty($_GPC['saletime'])) {
				if (20 < strlen($_GPC['saletime'])) {
					show_json(0, '营业时间不能大于20个字符');
				}
			}

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
				pdo_update('ewei_shop_store', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
				plog('shop.verify.store.edit', '编辑门店 ID: ' . $id);
			}
			else {
				pdo_insert('ewei_shop_store', $data);
				$id = pdo_insertid();
				plog('shop.verify.store.add', '添加门店 ID: ' . $id);
			}

			show_json(1, array('url' => webUrl('reservation')));
		}

		if (p('newstore')) {
			$storegroup = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_newstore_storegroup') . ' WHERE  uniacid=:uniacid  ', array(':uniacid' => $_W['uniacid']));
			$category = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_newstore_category') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		}

		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_store') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
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


		//获取服务人员列表
//        $reservationsaler = (pdo_fetchall(" select * from ims_ewei_shop_reservation_saler where status = 1 and merchid=0 and is_delete = 0 "));
//        print_r($reservationsaler);




		include $this->template("reservation/detail");
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_delete('ewei_shop_store', array('id' => $item['id']));
			plog('shop.verify.store.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$displayorder = intval($_GPC['value']);
		$item = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		if (!empty($item)) {
			pdo_update('ewei_shop_store', array('displayorder' => $displayorder), array('id' => $id));
			plog('shop.verify.store.edit', '修改门店排序 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' 排序: ' . $displayorder . ' ');
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

		$items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_update('ewei_shop_store', array('status' => intval($_GPC['status'])), array('id' => $item['id']));
			plog('shop.verify.store.edit', '修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['storename'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function query()
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
//		$limittype = empty($_GPC['limittype']) ? 0 : intval($_GPC['limittype']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and merchid =0  and status=1 ';

//		if ($limittype == 0) {
//			$condition .= '  and type in (1,2,3) ';
//		}

		if (!empty($kwd)) {
			$condition .= ' AND `salername` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ds = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_reservation_saler') . (' WHERE 1 ' . $condition . ' order by id asc'), $params);

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
