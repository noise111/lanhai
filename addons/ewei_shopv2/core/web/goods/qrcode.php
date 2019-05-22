<?php

class Qrcode_EweiShopV2Page extends WebPage {
    
    protected $token_cache = '_wxapp_cache_token';
    
    protected $path = '/addons/ewei_shopv2/data/app_qrcode/';

    public function main(){
        global $_W;      
        global $_GPC;
        $params = array(
            ':uniacid'          => $_W['uniacid']
        );
        $sql = "SELECT q.*, g.thumb, g.marketprice, g.title from " . tablename('ewei_shop_goods_qrcode') . " q 
                LEFT JOIN " . tablename('ewei_shop_goods') . " g ON g.id = q.goodsid
                WHERE q.uniacid = :uniacid";
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
            $data['uniacid'] = $_W['uniacid'];
            $data['goodsid'] = intval($_GPC['goodsid']);
            $data['goodsname'] = trim($_GPC['goodsname']);
            $data['price'] = floatval($_GPC['price']);
            $data['batch'] = trim($_GPC['batch']);
            $data['total'] = intval($_GPC['total']);            
            if(empty($id)){
                $data['createtime'] = time();
                $data['batch_log'] = 0;
                pdo_insert('ewei_shop_goods_qrcode', $data);
                $id = pdo_insertid();
                
                show_json(1, array('url' => webUrl('goods/qrcode')));
            } else {           
                pdo_update('ewei_shop_goods_qrcode', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
                show_json(1, array('url' => webUrl('goods/qrcode')));
            }
        }
        
        if($id){
            $params = array(
                ':uniacid'          => $_W['uniacid'],
                ':id'               => $id
            );
            $sql = "SELECT q.*, g.thumb, g.marketprice, g.title from " . tablename('ewei_shop_goods_qrcode') . " q 
                LEFT JOIN " . tablename('ewei_shop_goods') . " g ON g.id = q.goodsid
                WHERE q.uniacid = :uniacid AND q.id = :id";
            $item = pdo_fetch($sql, $params); 
        }
        include $this->template();
    }
    
    public function delete(){
        global $_W;
		global $_GPC;
        $id = intval($_GPC['id']);
		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
		}
		$items = pdo_fetchall('SELECT id,goodsname, goodsid FROM ' . tablename('ewei_shop_goods_qrcode') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

		foreach ($items as $item) {
			pdo_delete('ewei_shop_goods_qrcode', array('id' => $item['id']));
		}

		show_json(1, array('url' => referer()));
    }
    
    public function remake(){
        global $_W;
		global $_GPC;
        $id = intval($_GPC['id']);
		if (empty($id)) {
			$id = is_array($_GPC['ids']) ? $_GPC['ids'] : 0;
		} else {
            $id = array($id);
        }
        $path = IA_ROOT . '/addons/ewei_shopv2/data/app_qrcode/' . $_W['uniacid'] . '/';
        foreach($id as $value){
            $file = md5('id=' . $value) . '.png';
            $res = unlink($path . $file);
        }
        show_json(1, array('url' => referer()));
        
    }
    
    public function getQrcodeContent($scene){
        $sets = m('common')->getSysset('app');
        if($sets){
            $access_token = m('cache')->get($this->token_cache);
            if(!$access_token){
                $access_token = $this->getAppAccessToken($sets['appid'], $sets['secret']);
            } else {
                if($access_token['expires_in'] < time()){
                    $access_token = $this->getAppAccessToken($sets['appid'], $sets['secret']);
                }             
            }
            $token = $access_token['access_token'];
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=%s";
            $format_url = sprintf($url, $token);
            //echo $format_url;
            $params = array(
                'scene'     => $scene,
                'page'      => 'pages/scan/index'
            );
            $response = http_post_json($format_url,json_encode($params));         
        }
        return $response;
    }
    
    protected function getAppAccessToken($appid, $appsecret){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
        $format_url = sprintf($url, $appid, $appsecret);
        $response = ihttp_request($format_url);
        $content = json_decode($response['content'], true);
        if($content['access_token']){
            m('cache')->set($this->token_cache, array('access_token' => $content['access_token'], 'expires_in' => time() + $content['expires_in']));
            return $content;
        } else {
            return false;
        }
    }   
    
    /**
     * 批量生成批次二维码
     */
    public function qrcodeBatch(){
        global $_W;
        global $_GPC;
        set_time_limit(0);
        $id = intval($_GPC['id']);
        $start = intval($_GPC['start']);
        $size = 10;
        $params = array(
            ':uniacid'      => $_W['uniacid'],
            ':id'            => $id
        );
        $sql = "SELECT * FROM " . tablename('ewei_shop_goods_qrcode') . " WHERE uniacid = :uniacid AND id = :id";
        $item = pdo_fetch($sql, $params);
        if($item){
            $path = IA_ROOT . $this->path . $_W['uniacid'] . "/$id/";
            if (!is_dir($path)) {
                load()->func('file');
                mkdirs($path);
            }
            $total = $item['total'];
            if($total < $size){
                $size = $total;
            }
            if($start < $total){
                $counter = 0;
                for($i = $start + 1; $i <= ($start + $size); $i++){
                    $file =$i . '.png';
                    $qrcode_file = $path . $file;
                    if (!is_file($qrcode_file)) {
                        $scene = "qid=$id&no=$i";
                        $content = $this->getQrcodeContent($scene);
                        file_put_contents($qrcode_file, $content);                   
                        unset($content);
                    }
                    $counter++;
                }
                if($start + $size >= $total){
                    pdo_update('ewei_shop_goods_qrcode', array('finishmake' => 1), array('uniacid' => $_W['uniacid'], 'id' => $id));
                    show_json(2, '完成！');
                } else {
                    show_json(1, array(
                                    'next_start' => $start + $size, 
                                    'message' => '生成成功！从第' + $start + '到第' + ($start + $size) + '记录')
                             );
                }    
            } else {
                pdo_update('ewei_shop_goods_qrcode', array('finishmake' => 1), array('uniacid' => $_W['uniacid'], 'id' => $id));
                show_json(2, '完成！');
            }
                    
        } else {
            show_json(0, 'id不存在！');
        }
    }
    
    public function codeList(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $pindex = max(1, intval($_GPC["page"]));
		$psize = 20;       
        $offset = (($pindex - 1) * $psize);  
        $sales_status = isset($_GPC['sales_status']) ? $_GPC['sales_status'] : -1;
        $time = $_GPC['time'];
        $starttime = strtotime(trim($time['start']));
        $endtime = strtotime(trim($time['end']));
        $search_type = isset($_GPC['search_type']) ? trim($_GPC['search_type']) : '';
        $keyword = isset($_GPC['keyword']) ? trim($_GPC['keyword']) : '';       

        $condition = "";
        if($sales_status == 1){
            $condition .= " AND l.openid <> '0' ";
        } else if($sales_status == 0){
            $condition .= " AND l.openid = '0' ";
        }
        if($starttime && $endtime){
            $condition .= " AND l.createtime >= $starttime AND l.createtime <= $endtime ";
        }
        
        $select = " SELECT l.*, s.nickname as saler_nickname, s.avatar as saler_avatar, m.nickname as member_nickname, m.avatar as member_avatar ";
        $sql = " FROM " . 
                tablename('ewei_shop_goods_qrcode_log') . " l " .
               "LEFT JOIN " . tablename('ewei_shop_member') . " s ON s.openid = l.saleropenid " .
               "LEFT JOIN " . tablename('ewei_shop_member') . " m ON m.openid = l.openid ";
        
        if($search_type && $keyword){
            switch($search_type){
                case 1:
                    $sql .= " LEFT JOIN " . tablename("ewei_shop_saler") . " sa ON sa.openid = l.saleropenid ";
                    $condition .= " AND sa.salername LIKE '%$keyword%'";
                    break;
                case 2:
                    $condition .= " AND (m.nickname LIKE '%$keyword%' OR m.realname LIKE '%$keyword%') ";
                    break;
                case 3:
                    $sql .= " LEFT JOIN " . tablename("ewei_shop_saler") . " sa ON sa.openid = l.saleropenid ";
                    $sql .= " LEFT JOIN " . tablename("ewei_shop_store") . " st ON st.id = sa.storeid ";
                    $condition .= " AND st.storename LIKE '%$keyword%' ";
                    break;
                case 4:
                    $sql .= " LEFT JOIN " . tablename("ewei_shop_saler") . " sa ON sa.openid = l.saleropenid ";
                    $sql .= " LEFT JOIN " . tablename("ewei_shop_merch_user") . " mu ON mu.id = sa.merchid ";
                    $condition .= " AND mu.merchname LIKE '%$keyword%' ";
                    break;
            }
        }    
        $where = " WHERE l.qid = $id AND l.uniacid = {$_W['uniacid']} $condition";
        $list = pdo_fetchall($select . $sql . $where . " LIMIT $offset, $psize");
        $total = pdo_fetchcolumn("SELECT COUNT(l.id) " . $sql . $where);
        
        $path = $this->path . $_W['uniacid'] . "/$id/";
        foreach($list as &$qrcode){
            $file =$qrcode['no'] . '.png';
            $qrcode_file = $path . $file;
            if (!is_file(IA_ROOT . $qrcode_file)) {
                $qrcode_file = '';
            }
            $qrcode['qrcode'] = $_W['siteroot'] . $qrcode_file;
            $qrcode['create_time']     = date("Y-m-d H:i:s", $qrcode['createtime']);
            if(!empty($qrcode['saleropenid'])){
                //查找门店相关信息
                $store_sql = "SELECT saler.merchid, s.storename, s.province, s.city, s.area, u.merchname, u.address FROM " . tablename("ewei_shop_saler") . " saler " . 
                             "LEFT JOIN " . tablename("ewei_shop_store") . " s ON s.id = saler.storeid " . 
                             "LEFT JOIN " . tablename("ewei_shop_merch_user") . " u ON u.id = s.merchid " . 
                             "WHERE saler.openid = '{$qrcode['saleropenid']}'";
                $info = pdo_fetch($store_sql);
                if($info){
                    $qrcode['merchname'] = empty($info['merchid']) ? '总店' : $info['merchname'];
                    $qrcode['storename'] = $info['storename'];
                    $qrcode['address'] = $info['address'];
                    $qrcode['province'] = $info['province'];
                    $qrcode['city'] = $info['city'];
                    $qrcode['area'] = $info['area'];                       
                }
            }
        }
        //获取该二维码的销售情况
        $sql = "SELECT COUNT(id) FROM " . tablename("ewei_shop_goods_qrcode_log")  .
               " WHERE qid = $id AND uniacid = {$_W['uniacid']} AND openid <> '0'";
        $sale_total = pdo_fetchcolumn($sql);
        $pager = pagination2($total, $pindex, $psize);
        $item = pdo_get("ewei_shop_goods_qrcode", array("id" => $id));
        include $this->template();
    }
    
    public function printList(){
        global $_W;
        global $_GPC;
        $qid = intval($_GPC['id']);
        $params = array(
            ':uniacid'          => $_W['uniacid'],
            ':id'               => $qid
        );
        $sql = "SELECT q.*, g.thumb, g.marketprice, g.title from " . tablename('ewei_shop_goods_qrcode') . " q 
                LEFT JOIN " . tablename('ewei_shop_goods') . " g ON g.id = q.goodsid
                WHERE q.uniacid = :uniacid AND q.id = :id";
        $item = pdo_fetch($sql, $params);
        $path = $this->path . $_W['uniacid'] . "/$qid/";
        $item['path'] = $path;
        $list = array();
        $length = strlen($item['total']);
        $format = "%0".$length."d";
        for($i = 1; $i <= 6; $i++){
            $file =$i . '.png';
            $qrcode_file = $path . $file;
            $list[] = array(
                'title'     => $item['title'],
                'qrcode'    => '1',
                'batch'     => $item['batch'] . '-' . sprintf($format, $i),
                'qrcode'    => $_W['siteroot'] . $qrcode_file
            );
        }
        include $this->template();
    }
    
    public function query_no(){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
        $qrcodeid = $_GPC['qrcodeid'];
        $log = pdo_getall("ewei_shop_goods_qrcode_log", array("qid" => $qrcodeid), array("no"), "no");
        $log_no = array_keys($log);
        $batch = pdo_get("ewei_shop_goods_qrcode", array("id" => $qrcodeid, "uniacid" => $uniacid));
        if($batch['total'] && $batch['total'] > 0){
            $no_list = range(1, $batch['total']);
            $no_list = array_diff($no_list, $log_no);
        } else {
            $no_list = array();
        }
        show_json(1, array("list" => $no_list));
    }
    
    private function batch_insert_log($qrcodeid){
        global $_W;
        $qrcode = pdo_get("ewei_shop_goods_qrcode", array("id" => $qrcodeid));
        if($qrcode && $qrcode['total'] > 0 && $qrcode['batch_log'] == 0){
            $now = time();
            $sql = "INSERT IGNORE INTO " . tablename("ewei_shop_goods_qrcode_log") . 
                   " (`qid`, `no`, `uniacid`, `createtime`) VALUES ";
            for($i = 1; $i <= $qrcode['total']; $i++){
                $sql .= " ('$qrcodeid', '$i', '{$_W['uniacid']}', '$now'),";
            }
            $sql = rtrim($sql, ',');
            pdo_query($sql);
            pdo_update("ewei_shop_goods_qrcode", array("batch_log" => 1), array("id" => $qrcodeid));
        }
    }
    
    public function test(){
        $this->batch_insert_log();
    }
}
