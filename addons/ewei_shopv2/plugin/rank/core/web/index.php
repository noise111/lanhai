<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage {
    
    public $model;
    
    public function __construct($_init = true) {
        parent::__construct($_init);
        $this->model = m('plugin')->loadModel("rank");
    }
	public function main()
	{
		global $_W;
        $list = pdo_getall("ewei_shop_rank");
        $rank_com = com("rank");
		include $this->template();
	}

    public function set_data(){
        global $_W;
        global $_GPC;
        $rank_type = isset($_GPC['rank_type']) ? intval($_GPC['rank_type']) : RANK_TYPE_SALES;
        $time = isset($_GPC['time']) ? $_GPC['time'] : array();
        if(isset($time['start']) && !empty($time['start'])){
            $starttime = $time['start'];
        } else {
            $starttime = date("Y-m-d H:i:s", strtotime('today'));
        }
        
        if(isset($time['end']) && !empty($time['end'])){
            $endtime = $time['end'];
        } else {
            $endtime = date("Y-m-d H:i:s", strtotime('today +15 day'));
        }
        $set = array(
            'rank_type'         => $rank_type,
            'starttime'         => $starttime,
            'endtime'           => $endtime
        );
        m("common")->updatePluginset(array("rank" => $set));
        show_json(1);
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
        
        $id = isset($_GPC['id']) ? intval($_GPC['id']) : 0;
        if($id){
            $item = pdo_get("ewei_shop_rank", array("id" => $id));
            $range_list = com("rank")->get_range_list($item['range']);
            $goods = pdo_get("ewei_shop_goods", array("id" => $item['goodsid']));
        }
        $data = array();
        $time = $_GPC['time'];
        isset($time['start']) ? $_GPC['starttime'] = strtotime($time['start']) : 0;
        isset($time['end']) ? $_GPC['endtime'] = strtotime($time['end']) : 0;
        if($_W['ispost']){
            if($id){              
                isset($_GPC['name']) ?          $data['name'] = trim($_GPC['name']) : false;
                isset($_GPC['cateid']) ?        $data['cateid'] = intval($_GPC['cateid']) : false;
                isset($_GPC['data_target']) ?   $data['data_target'] = intval($_GPC['data_target']) : false;
                isset($_GPC['data_type']) ?     $data['data_type'] = intval($_GPC['data_type']) : false;
                isset($_GPC['range']) ?         $data['range'] = intval($_GPC['range']) : false;
                isset($_GPC['starttime']) ?     $data['starttime'] = $_GPC['starttime'] : false;
                isset($_GPC['endtime']) ?       $data['endtime'] = $_GPC['endtime'] : false;
                isset($_GPC['long_time']) ?     $data['long_time'] = intval($_GPC['long_time']) : $data['long_time'] = 0;
                isset($_GPC['status']) ?        $data['status'] = intval($_GPC['status']) : false;
                isset($_GPC['is_show']) ?       $data['is_show'] = intval($_GPC['is_show']) : false;
                isset($_GPC['displayorder']) ?  $data['displayorder'] = intval($_GPC['displayorder']) : false;
                isset($_GPC['range_id']) ?      $data['range_id'] = intval($_GPC['range_id']) : false;
                isset($_GPC['goodsid']) ?       $data['goodsid'] = intval($_GPC['goodsid']) : false;
                if(isset($data['name'])){
                    $db = pdo_get("ewei_shop_rank", array("name" => $data['name']));
                    if($db && $db['id'] != $id){
                        show_json(0, "榜单名已存在");
                    } else {
                        pdo_update("ewei_shop_rank", $data, array("id" => $id));
                        show_json(1);
                    }
                }
            } else {
                $data['name'] =             isset($_GPC['name']) ? trim($_GPC['name']) : '';
                $data['cateid'] =           isset($_GPC['cateid']) ? intval($_GPC['cateid']) : 0;
                $data['data_target'] =      isset($_GPC['data_target']) ? intval($_GPC['data_target']) : 1;
                $data['data_type'] =        isset($_GPC['data_type']) ? intval($_GPC['data_type']) : 1;
                $data['range'] =            isset($_GPC['range']) ? intval($_GPC['range']) : 1;
                $data['starttime'] =        isset($_GPC['starttime']) ? $_GPC['starttime'] : 0;
                $data['endtime'] =          isset($_GPC['endtime']) ? $_GPC['endtime'] : 0;
                $data['long_time'] =        isset($_GPC['long_time']) ? intval($_GPC['long_time']) : 1;
                $data['status'] =           isset($_GPC['status']) ? intval($_GPC['status']) : 1;
                $data['is_show'] =          isset($_GPC['is_show']) ? intval($_GPC['is_show']) : 1;
                $data['displayorder'] =     isset($_GPC['displayorder']) ? intval($_GPC['displayorder']) : 99;
                $data['range_id'] =         isset($_GPC['range_id']) ? intval($_GPC['range_id']) : 0;
                $data['goodsid'] =          isset($_GPC['goodsid']) ? intval($_GPC['goodsid']) : 0;

                $data['uniacid']        = $_W['uniacid'];
                $data['createtime']     = time();
                $db = pdo_get("ewei_shop_rank", array("name" => $data['name']));
                if($db){
                    show_json(0, "榜单名已存在");
                }
                pdo_insert("ewei_shop_rank", $data);
                show_json(1);
            }
        }
        
        include $this->template();
    }
    
    public function delete(){
        global $_GPC;
        $id = $_GPC['ids'];
        pdo_delete("ewei_shop_rank", array("id" => $id));
        show_json(1);
    }
    
    public function get_range_list(){
        global $_W;
        global $_GPC;
        $range = intval($_GPC['range']);
        $list = com("rank")->get_range_list($range);
        show_json(1, array('list' => $list));
    }
    
    public function test(){
        $com = com("rank");
        $list = $com->get_data(5, 86, 5);
        echo "<pre>";
        print_r($list);
        echo "</pre>";
    }

    public function type(){
        $com = com("rank");
        $list = $com->get_data(5, 86, 5);
        echo "<pre>";
        print_r($list);
        echo "</pre>";
    }


}

?>
