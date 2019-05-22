<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
class Credit_EweiShopV2Page extends WebPage 
{
	protected function main($type = "credit1") 
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC["page"]));
		$psize = 20;
		$condition = " and log.uniacid=:uniacid and (log.module=:module1  or log.module=:module2) and log.credittype=:credittype";
		$condition1 = " and log.uniacid=:uniacid";
		$params = array( ":uniacid" => $_W["uniacid"], ":module1" => "ewei_shopv2", ":module2" => "ewei_shop", ":credittype" => $type );
		if( !empty($_GPC["keyword"]) ) 
		{
			$_GPC["keyword"] = trim($_GPC["keyword"]);
			$condition .= " and (m.realname like :keyword or m.nickname like :keyword or m.mobile like :keyword or u.username like :keyword)";
			$condition1 .= " and (m.realname like :keyword or m.nickname like :keyword or m.mobile like :keyword)";
			$params[":keyword"] = "%" . $_GPC["keyword"] . "%";
		}
		if( empty($starttime) || empty($endtime) ) 
		{
			$starttime = strtotime("-1 month");
			$endtime = time();
		}
		if( !empty($_GPC["time"]["start"]) && !empty($_GPC["time"]["end"]) ) 
		{
			$starttime = strtotime($_GPC["time"]["start"]);
			$endtime = strtotime($_GPC["time"]["end"]);
			$condition .= " AND log.createtime >= :starttime AND log.createtime <= :endtime ";
			$condition1 .= " AND log.createtime >= :starttime AND log.createtime <= :endtime ";
			$params[":starttime"] = $starttime;
			$params[":endtime"] = $endtime;
		}
		if( !empty($_GPC["level"]) ) 
		{
			$condition .= " and m.level=" . intval($_GPC["level"]);
			$condition1 .= " and m.level=" . intval($_GPC["level"]);
		}
		if( !empty($_GPC["groupid"]) ) 
		{
			$condition .= " and m.groupid=" . intval($_GPC["groupid"]);
			$condition1 .= " and m.groupid=" . intval($_GPC["groupid"]);
		}
		$search_flag = 0;
		if( $_GPC["groupid"] || $_GPC["level"] || $_GPC["keyword"] ) 
		{
			$search_flag = 1;
			if( $type == "credit1" ) 
			{
				$table1 = "select log.id,log.num,log.createtime,log.remark,log.credittype,m.id as mid,m.openid, m.realname,m.nickname,m.avatar, m.mobile, m.weixin,u.username,g.groupname,l.levelname from " . tablename("mc_credits_record") . " log " . " left join " . tablename("users") . " u on  log.operator=u.uid and log.operator<>0 and log.operator<>log.uid" . " left join " . tablename("ewei_shop_member") . " m on m.uid=log.uid" . " left join " . tablename("ewei_shop_member_group") . " g on m.groupid=g.id" . " left join " . tablename("ewei_shop_member_level") . " l on m.level =l.id" . " where 1 " . $condition . " and log.uid<>0";
				$table2 = "select log.id,log.num,log.createtime,log.remark,log.credittype,m.id as mid,m.openid, m.realname,m.nickname,m.avatar, m.mobile, m.weixin,u.username,g.groupname,l.levelname  from " . tablename("ewei_shop_member_credit_record") . " log " . " left join " . tablename("users") . " u on  log.operator=u.uid and log.operator<>0 and log.operator<>log.uid" . " left join " . tablename("ewei_shop_member") . " m on m.openid=log.openid" . " left join " . tablename("ewei_shop_member_group") . " g on m.groupid=g.id" . " left join " . tablename("ewei_shop_member_level") . " l on m.level =l.id" . " where 1 " . $condition . " and log.uid=0";
				$sql = "select * from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc";
			}
			else 
			{
				if( $type == "credit2" ) 
				{
					$condition .= " and log.uid<>0";
					$table1 = "select log.id,log.num,log.createtime,log.remark,log.credittype,m.id as mid,m.openid, m.realname,m.nickname,m.avatar, m.mobile, m.weixin,u.username,g.groupname,l.levelname  from " . tablename("mc_credits_record") . " log " . " left join " . tablename("users") . " u on  log.operator=u.uid and log.operator<>0 and log.operator<>log.uid" . " left join " . tablename("ewei_shop_member") . " m on m.uid=log.uid" . " left join " . tablename("ewei_shop_member_group") . " g on m.groupid=g.id" . " left join " . tablename("ewei_shop_member_level") . " l on m.level =l.id" . " where 1 " . $condition;
					$table2 = "select log.id,log.money,log.createtime,log.remark,'credit2' as credittype,m.id as mid,m.openid, m.realname,m.nickname,m.avatar, m.mobile, m.weixin,log.rechargetype as username,g.groupname,l.levelname from " . tablename("ewei_shop_member_log") . "as log " . " inner join " . tablename("ewei_shop_member") . " m on m.openid=log.openid" . " left join " . tablename("ewei_shop_member_group") . " g on m.groupid=g.id" . " left join " . tablename("ewei_shop_member_level") . " l on m.level =l.id" . " where m.uid=0 and log.status=1 " . $condition1;
					$sql = "select * from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc";
				}
			}
		}
		else 
		{
			$table1 = "select log.id,log.num,log.createtime,log.remark,log.credittype,u.username,log.uid,'xxx' as openid from " . tablename("mc_credits_record") . " log " . " left join " . tablename("users") . " u on  log.operator=u.uid and log.operator<>0 and log.operator<>log.uid" . " where 1 " . $condition . " and log.uid<>0";
			$table2 = "select log.id,log.num,log.createtime,log.remark,log.credittype,u.username,'0' as uid,log.openid  from " . tablename("ewei_shop_member_credit_record") . " log " . " left join " . tablename("users") . " u on  log.operator=u.uid and log.operator<>0 and log.operator<>log.uid" . " where 1 " . $condition . " and log.uid=0";
			$sql = "select * from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc";
		}
		if( empty($_GPC["export"]) ) 
		{
			$sql .= " LIMIT " . ($pindex - 1) * $psize . "," . $psize;
		}
		else 
		{
			ini_set("memory_limit", "-1");
		}
		$list = pdo_fetchall($sql, $params);
		if( $list && $search_flag == 0 ) 
		{
			foreach( $list as $key => $val ) 
			{
				$member = array( );
				if( $val["uid"] ) 
				{
					$member = pdo_fetch("select * from " . tablename("ewei_shop_member") . " where uniacid=:uniacid and uid=:uid", array( ":uniacid" => $_W["uniacid"], ":uid" => $val["uid"] ));
				}
				else 
				{
                    if(stripos($val['openid'], 'store_') !== false){
                        $storeid = str_replace('store_', '', $val['openid']);
                        $storeinfo = m('store')->getStoreInfo($storeid);
                        if($storeinfo){
                            $member = array(
                                'id'            => 0,
                                'openid'        => $storeid,
                                'realname'      => $storeinfo['storename'] . ' 门店',
                                'nickname'      => $storeinfo['storename'],
                                'avatar'        => $storeinfo['logo'],
                                'mobile'        => $storeinfo['mobile'],
                                'weixin'        => ''
                            );
                        }
                    } else if(stripos($val['openid'], 'merch_') !== false){    
                        $merchid = str_replace('merch_', '', $val['openid']);
                        $merchinfo = pdo_get("ewei_shop_merch_user", array("id" => $merchid));
                        if($merchinfo){
                            $member = array(
                                'id'            => 0,
                                'openid'        => $merchid,
                                'realname'      => $merchinfo['merchname'] . ' 零售商',
                                'nickname'      => $merchinfo['merchname'],
                                'avatar'        => $merchinfo['logo'],
                                'mobile'        => $merchinfo['mobile'],
                                'weixin'        => ''
                            );
                        }
                    } else {
                        $member = pdo_fetch("select * from " . tablename("ewei_shop_member") . " where uniacid=:uniacid and openid=:openid", array( ":uniacid" => $_W["uniacid"], ":openid" => $val["openid"] ));
                    }
				}
				$groupname = pdo_fetchcolumn("select groupname from " . tablename("ewei_shop_member_group") . " where uniacid=:uniacid and id=:id", array( ":uniacid" => $_W["uniacid"], ":id" => $member["groupid"] ));
				$levelname = pdo_fetchcolumn("select levelname from " . tablename("ewei_shop_member_level") . " where uniacid=:uniacid and id=:id", array( ":uniacid" => $_W["uniacid"], ":id" => $member["level"] ));
				$list[$key]["groupname"] = $groupname;
				$list[$key]["levelname"] = $levelname;
				$list[$key]["mid"] = $member["id"];
				$list[$key]["openid"] = $member["openid"];
				$list[$key]["realname"] = $member["realname"];
				$list[$key]["nickname"] = $member["nickname"];
				$list[$key]["avatar"] = $member["avatar"];
				$list[$key]["mobile"] = $member["mobile"];
				$list[$key]["weixin"] = $member["weixin"];
			}
		}
		if( $_GPC["export"] == 1 ) 
		{
			if( $_GPC["type"] == 1 ) 
			{
				plog("finance.credit.credit1.export", "导出积分明细");
			}
			else 
			{
				plog("finance.credit.credit2.export", "导出余额明细");
			}
			foreach( $list as &$row ) 
			{
				$row["createtime"] = date("Y-m-d H:i", $row["createtime"]);
				$row["groupname"] = (empty($row["groupname"]) ? "无分组" : $row["groupname"]);
				$row["levelname"] = (empty($row["levelname"]) ? "普通会员" : $row["levelname"]);
				if( $row["credittype"] == "credit1" ) 
				{
					$row["credittype"] = "积分";
				}
				else 
				{
					if( $row["credittype"] == "credit2" ) 
					{
						$row["credittype"] = "余额";
					}
				}
				if( empty($row["username"]) ) 
				{
					$row["username"] = "本人";
				}
			}
			unset($row);
			$columns = array( );
			$columns[] = array( "title" => "类型", "field" => "credittype", "width" => 12 );
			$columns[] = array( "title" => "昵称", "field" => "nickname", "width" => 12 );
			$columns[] = array( "title" => "姓名", "field" => "realname", "width" => 12 );
			$columns[] = array( "title" => "手机号", "field" => "mobile", "width" => 12 );
			$columns[] = array( "title" => "会员等级", "field" => "levelname", "width" => 12 );
			$columns[] = array( "title" => "会员分组", "field" => "groupname", "width" => 12 );
			$columns[] = array( "title" => ($type == "credit1" ? "积分变化" : "余额变化"), "field" => "num", "width" => 12 );
			$columns[] = array( "title" => "时间", "field" => "createtime", "width" => 12 );
			$columns[] = array( "title" => "备注", "field" => "remark", "width" => 24 );
			$columns[] = array( "title" => "操作人", "field" => "username", "width" => 12 );
			m("excel")->export($list, array( "title" => (($type == "credit1" ? "会员积分明细-" : "会员余额明细")) . date("Y-m-d-H-i", time()), "columns" => $columns ));
		}
        
        switch ($type){
            case 'credit1':
                $allcount = pdo_fetch("select count(*) as ccc from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc limit 1", $params);
                $total = $allcount["ccc"];
                break;
            case 'credit2':
                $allcount = pdo_fetch("select count(*) as ccc from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc limit 1", $params);
                $total = $allcount["ccc"];
                break;
            case 'benefit':
                $allcount = pdo_fetch("select count(*) as ccc from (" . $table1 . " UNION ALL " . $table2 . ") as main order by createtime desc limit 1", $params);
                $total = $allcount["ccc"];
                break;
        }

		$pager = pagination2($total, $pindex, $psize);
		$groups = m("member")->getGroups();
		$levels = m("member")->getLevels();
		include($this->template("finance/credit"));
	}
	public function credit1() 
	{
		$this->main("credit1");
	}
	public function credit2() 
	{
		$this->main("credit2");
	}
    
    public function benefit(){
        global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC["page"]));
		$psize = 20;
		$condition = " and log.uniacid=:uniacid and (log.module=:module1  or log.module=:module2) and log.credittype=:credittype";
		$params = array( ":uniacid" => $_W["uniacid"], ":module1" => "ewei_shopv2", ":module2" => "ewei_shop", ":credittype" => "benefit" );
		if( !empty($_GPC["keyword"]) ) 
		{
			$_GPC["keyword"] = trim($_GPC["keyword"]);
			//$condition .= " and ()";
			$params[":keyword"] = "%" . $_GPC["keyword"] . "%";
		}		
		if( !empty($_GPC["time"]["start"]) && !empty($_GPC["time"]["end"]) ) 
		{
			$starttime = strtotime($_GPC["time"]["start"]);
			$endtime = strtotime($_GPC["time"]["end"]);
			$condition .= " AND log.createtime >= :starttime AND log.createtime <= :endtime ";
			$params[":starttime"] = $starttime;
			$params[":endtime"] = $endtime;
		}
        
        if( empty($starttime) || empty($endtime) ) 
		{
			$starttime = strtotime("-1 month");
			$endtime = time();
		}
        $object = isset($_GPC['object']) && !empty($_GPC['object']) ? intval($_GPC['object']) : 0;
        $object_list = isset($_GPC['object_list']) && !empty($_GPC['object_list']) ? intval($_GPC['object_list']) : 0;
        if($object && $object_list){
            switch ($object){
                //门店
                case 1:
                    $stores = "'store_$object_list'";
                    $condition .= " AND openid = $stores ";
                    break;
                //零售商
                case 2:
                    $stores = pdo_fetchall("SELECT id FROM " . tablename("ewei_shop_store") . " WHERE merchid = $object_list", array(), "id");
                    foreach ($stores as &$s){
                        $s = "'store_{$s['id']}'";
                    }
                    $stores = array_keys(array_flip($stores));
                    $condition .= " AND openid IN(" . implode(',', $stores) . ")";
                    break;
                //运营中心
                case 3:
                    $merchs = pdo_fetchcolumn("SELECT GROUP_CONCAT(id) FROM " . tablename("ewei_shop_merch_user") . " WHERE agentid = $object_list GROUP BY agentid");
                    if($merchs){
                        $stores = pdo_fetchall("SELECT id FROM " . tablename("ewei_shop_store") . " WHERE merchid IN($merchs)", array(), "id");
                        foreach ($stores as &$s){
                            $s = "'store_{$s['id']}'";
                        }
                        $stores = array_keys(array_flip($stores));
                        $condition .= " AND openid IN(" . implode(',', $stores) . ")";
                    } else {
                        $condition .= " AND openid  =  '***'";
                    }
                    break;
            }
        }

		$search_flag = 0;
	
        $select = "select log.id,log.num,log.createtime,log.remark,log.credittype,log.openid ";
        $sql = " from " . tablename("ewei_shop_member_credit_record") . " log " .
               " where openid <> '0' " . $condition . " and log.uid=0";
        $order_by = " ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
        
		$list = pdo_fetchall($select . $sql . $order_by, $params);
        
		if( $list && $search_flag == 0 ) 
		{
			foreach( $list as $key => $val ) 
			{
				$member = array( );
                if(stripos($val['openid'], 'store_') !== false){
                    $storeid = str_replace('store_', '', $val['openid']);
                    $storeinfo = m('store')->getStoreInfo($storeid);
                    if($storeinfo){
                        $member = array(
                            'id'            => 0,
                            'openid'        => $storeid,
                            'realname'      => $storeinfo['storename'] . ' 门店',
                            'nickname'      => $storeinfo['storename'],
                            'avatar'        => $storeinfo['logo'],
                            'mobile'        => $storeinfo['mobile'],
                            'weixin'        => ''
                        );
                    }
                } else if(stripos($val['openid'], 'merch_') !== false){    
                    $merchid = str_replace('merch_', '', $val['openid']);
                    $merchinfo = pdo_get("ewei_shop_merch_user", array("id" => $merchid));
                    if($merchinfo){
                        $member = array(
                            'id'            => 0,
                            'openid'        => $merchid,
                            'realname'      => $merchinfo['merchname'] . ' 零售商',
                            'nickname'      => $merchinfo['merchname'],
                            'avatar'        => $merchinfo['logo'],
                            'mobile'        => $merchinfo['mobile'],
                            'weixin'        => ''
                        );
                    }
                } else {
                    $member = pdo_fetch("select * from " . tablename("ewei_shop_member") . " where uniacid=:uniacid and openid=:openid", array( ":uniacid" => $_W["uniacid"], ":openid" => $val["openid"] ));
                }
				$list[$key]["mid"] = $member["id"];
				$list[$key]["openid"] = $member["openid"];
				$list[$key]["realname"] = $member["realname"];
				$list[$key]["nickname"] = $member["nickname"];
				$list[$key]["avatar"] = $member["avatar"];
				$list[$key]["mobile"] = $member["mobile"];
				$list[$key]["weixin"] = $member["weixin"];
			}
		}
        $total = pdo_fetchcolumn("SELECT COUNT(*) " . $sql, $params);
        $object_list = $this->query_object(false);
		$pager = pagination2($total, $pindex, $psize);
		include($this->template("finance/benefit"));
    }
    
    public function query_object($is_ajax = true){
        global $_W;
        global $_GPC;
        $type = isset($_GPC['object']) ? intval($_GPC['object']) : 1;
        $id = isset($_GPC['id']) ? intval($_GPC['id']) : 0;
        switch($type){
            case 1:
                $list = pdo_getall("ewei_shop_store", array("uniacid" => $_W['uniacid']));
                foreach($list as &$item){
                    $item['name'] = $item['storename'];
                }
                break;
            case 2:
                $list = pdo_getall("ewei_shop_merch_user", array("uniacid" => $_W['uniacid']));
                foreach($list as &$item){
                    $item['name'] = $item['merchname'];
                }
                break;
            case 3:
                $list = pdo_getall("ewei_shop_member", array("uniacid" => $_W['uniacid'], "isaagent" => 1, "aagentstatus" => 1));
                foreach($list as &$item){
                    switch($item['aagenttype']){
                        case 1:
                            $area = iunserializer($item['aagentprovinces']);
                            break;
                        case 2:
                            $area = iunserializer($item['aagentcitys']);
                            break;
                        case 3:
                            $area = iunserializer($item['aagentareas']);
                            break;
                    }
                    $item['name'] = implode('-', $area);
                }
                break;
        }
        if($is_ajax){
            show_json(1, array("list" => $list));
        } else {
            return $list;
        }
    }
  
}
?>