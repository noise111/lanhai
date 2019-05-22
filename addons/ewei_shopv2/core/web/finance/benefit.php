<?php  

if( !defined("IN_IA") ) {
	exit( "Access Denied" );
}

class Benefit_EweiShopV2Page extends WebPage {
    
    public function main($status = 1){
        global $_W;
		global $_GPC;
		$action_status = $status;
		$applytitle = "";
        switch ($status){
            case 1:
                $applytitle = "待确认";
                break;
            case 2:
                $applytitle = "待打款";
                break;
            case 3:
                $applytitle = "已打款";
                break;
            case -1:
                $action_status = "_1";
				$applytitle = "已无效";
                break;
        }
        $apply_type = array("微信钱包", 2 => "支付宝", 3 => "银行卡");
		$pindex = max(1, intval($_GPC["page"]));
		$psize = 20;
		$condition = " and uniacid=:uniacid and bill_type = 3 ";
        $params = array(":uniacid" => $_W["uniacid"]);
        
		$timetype = $_GPC["timetype"];
		if(!empty($_GPC["timetype"])) 
		{
			$starttime = strtotime($_GPC["time"]["start"]);
			$endtime = strtotime($_GPC["time"]["end"]);
            if(empty($starttime) || empty($endtime)) {
                $starttime = strtotime("-1 month");
                $endtime = time();
            }
			if(!empty($timetype)) 
			{
				$condition .= " AND " . $timetype . " >= :starttime AND " . $timetype . "  <= :endtime ";
				$params[":starttime"] = $starttime;
				$params[":endtime"] = $endtime;
			}
		}
        
        $condition .= " and status=:status";
		$params[":status"] = (int) $status;
		if($_GPC["status"] !== "" && $_GPC["status"] !== NULL) 
		{
			$_GPC["status"] = intval($_GPC["status"]);
			$params[":status"] = $_GPC["status"];
		}
        $searchfield = strtolower(trim($_GPC["searchfield"]));
		$keyword = trim($_GPC["keyword"]);
        if(!empty($searchfield) && !empty($keyword)) {
			if($searchfield == "applyno") 
			{
				$condition .= " and applyno like :keyword";
			}
			$params[":keyword"] = "%" . $keyword . "%";
		}
        $sql = "SELECT *
                FROM " . tablename("ewei_shop_merch_bill") .
               " where 1 " . $condition . " ORDER BY id desc ";
        if(empty($_GPC["export"])){
			$sql .= " limit " . ($pindex - 1) * $psize . "," . $psize;
		}
        $list = pdo_fetchall($sql, $params);
        foreach($list as &$item){
            //获取提现信息
            switch ($item['member_type']){
                //经销商
                case 1:
                    $member = pdo_get("ewei_shop_merch_user", array("id" => $item['pid']));
                    $item['realname'] = $member['merchname'];                   
                    break;
                //运营中心
                case 2:
                    $member = m("member")->getMemberBase($item['pid']);
                    $item['realname'] = $member['realname'];  
                    break;
                //用户
                case 3:
                    $member = m("member")->getMemberBase($item['pid']);
                    $item['realname'] = $member['realname'];  
                    break;
            }
            $item['mobile'] = $member['mobile'];
        }
        $total = (pdo_fetchcolumn("select COUNT(1) from " . tablename("ewei_shop_merch_bill") . " where 1 " . $condition, $params));
//        print_r($total);
		$pager = pagination2($total, $pindex, $psize);
        include($this->template("finance/benefit/index"));
    }
    
    public function status1(){
        $this->main(1);
    }
    
    public function status2(){
        $this->main(2);
    }
    
    public function status3(){
        $this->main(3);
    }
    
    public function status_1(){
        $this->main(-1);
    }
    
    public function detail() {
		global $_W;
		global $_GPC;
		$id = intval($_GPC["id"]);
		$status = intval($_GPC["status"]);
		$item = m("finance")->getOneApply($id);
		if( empty($item["applytype"]) ) 
		{
			$merch_user = pdo_fetch("select * from " . tablename("ewei_shop_merch_user") . " where uniacid=:uniacid and id=" . $item["merchid"], array( ":uniacid" => $_W["uniacid"] ));
			if( !empty($merch_user["payopenid"]) ) 
			{
				$member = m("member")->getMember($merch_user["payopenid"]);
			}
		}
		$apply_type = array( "微信钱包", 2 => "支付宝", 3 => "银行卡" );
		if( $status == 1 ) 
		{
			$is_check = 1;
		}

		$keyword = trim($_GPC["keyword"]);

		include($this->template('finance/benefit/detail'));
	}
    
    public function confirm(){
		global $_W;
		global $_GPC;
		$id = intval($_GPC["id"]);
		$type = intval($_GPC["type"]);
		$item = m("finance")->getOneApply($id);
		if( empty($item) ) 
		{
			show_json(0, "未找到提现申请!");
		}

		if( $type == 1 ) 
		{
			$change_data = array( );
			$change_data["status"] = 2;
			pdo_update("ewei_shop_merch_bill", $change_data, array( "id" => $id ));
		}
		else 
		{
			if( $type == -1 ) 
			{
				$change_data = array( );
				$change_data["invalidtime"] = time();
				$change_data["status"] = -1;
				pdo_update("ewei_shop_merch_bill", $change_data, array( "id" => $id ));
			}
		}
		show_json(1);
	}
    
    public function benefitpay() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC["id"]);
		$handpay = intval($_GPC["handpay"]);
		$finalprice = floatval($_GPC["finalprice"]);
		if( empty($id) ) 
		{
			show_json(0, "参数错误!");
		}
		if( $finalprice <= 0 ) 
		{
			show_json(0, "打款金额错误!");
		}
		$item = m("finance")->getOneApply($id);
		if( empty($item) ) 
		{
			show_json(0, "未找到提现申请!");
		}
		$payprice = $finalprice * 100;
		if( empty($handpay) && empty($item["applytype"]) ) 
		{
            $payopenid = "";
            switch ($item['member_type']){
                //经销商
                case 1:
                    $merch_user = pdo_get("ewei_shop_merch_user", array("id" => $item['pid']));  
                    $payopenid = $merch_user['payopenid'];
                    break;
                //运营中心
                case 2:
                    $member = m("member")->getMemberBase($item['pid']);
                    $payopenid = $member['agent_payopenid'];
                    break;
                //用户
                case 3:
                    $member = m("member")->getMemberBase($item['pid']);
                    $payopenid = $member['openid'];
                    break;
            }
			if( empty($payopenid) ) 
			{
				show_json(0, "请先设置结算收款人!");
			}
			$result = m("finance")->pay($payopenid, 1, $payprice, $item["applyno"], "分润提现打款");
			if( is_error($result) ) 
			{
				show_json(0, $result["message"]);
			}
		}
		if( empty($handpay) && $item["applytype"] == 2 ) 
		{
			$sec = m("common")->getSec();
			$sec = iunserializer($sec["sec"]);
			if( !empty($sec["alipay_pay"]["open"]) ) 
			{
				if( empty($sec["alipay_pay"]["sign_type"]) ) 
				{
					show_json(0, "支付宝仅支持单笔转账打款!");
				}
				$billminey = $finalprice * 100;
				$batch_no = "D" . date("Ymdhis") . "RW" . $item["id"] . "MERCH" . $billminey;
				$single_res = m("finance")->singleAliPay(array( "account" => $item["alipay"], "name" => $item["applyrealname"], "money" => $finalprice ), $batch_no, $sec["alipay_pay"], "特约零售商提现申请打款");
				if( $single_res["errno"] == "-1" ) 
				{
					show_json(0, $single_res["message"]);
				}
				$order_id = $single_res["order_id"];
				$query_res = m("finance")->querySingleAliPay($sec["alipay_pay"], $order_id, $batch_no);
				if( $query_res["errno"] == "-1" ) 
				{
					show_json(0, $query_res["message"]);
				}
			}
			else 
			{
				show_json(0, "未开启,支付宝打款!");
			}
		}
		$change_data = array( );
		$change_data["paytime"] = time();
		$change_data["status"] = 3;
		$change_data["finalprice"] = $finalprice;
		$change_data["handpay"] = $handpay;
		pdo_update("ewei_shop_merch_bill", $change_data, array( "id" => $id ));
		show_json(1);
	}
}