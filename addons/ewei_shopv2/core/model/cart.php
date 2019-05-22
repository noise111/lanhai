<?php


class Cart_EweiShopV2Model {
    
    /**
     * 获取商品可用优惠券
     * @param int $goodid 
     * @param array $coupon_type
     */
    public function getCouponsByGood($goodid, $coupon_type = array()){
        global $_W;
        global $_GPC;
        $goods_model = m('goods');
        $coupons = $goods_model->getCouponsByGood($goodid, $coupon_type);
        $coupon_ids = array_keys($coupons);
        if(empty($coupon_ids)){
            return array();
        } else {
            $param = array(
                ":uniacid"          => $_W['uniacid'],
                ":openid"           => $_W['openid']
            );
            //获取已购物车中已被选择的优惠券数据
            $sql = "SELECT couponid FROM " . tablename('ewei_shop_member_salercart') . " WHERE uniacid = :uniacid AND openid = :openid AND couponid > 0";
            $selected = pdo_fetchall($sql, $param, 'couponid');
            $selected = array_keys($selected);
            $coupon_ids = implode(',', $coupon_ids);
            
            $time = time();
			$sql  = " select d.*, c.deduct, c.backmoney, c.backcommission from " . tablename("ewei_shop_coupon_data") . " d";
			$sql .= " left join " . tablename("ewei_shop_coupon") . " c on d.couponid = c.id";
			$sql .= " where d.uniacid=:uniacid and  d.used=0 ";
			$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=" . $time . " ) )  or  (c.timelimit =1 and c.timestart<=" . $time . " && c.timeend>=" . $time . "))";
            $sql .= " and d.couponid IN($coupon_ids) AND d.openid = :openid ";
            
            $orderby = " ORDER BY d.gettime ASC";
            //自己的券
            $condition = " AND d.sender = :openid ";
            $list1 = pdo_fetchall($sql . $condition . $orderby, $param);
            //分享得来的券
            $condition = " AND d.sender <> :openid ";
            $list2 = pdo_fetchall($sql . $condition . $orderby, $param);
            $list = array_merge($list1, $list2);
            foreach($list as $key => &$row){
                if(in_array($row['id'], $selected)){
                    unset($list[$key]);
                    continue;
                }
                $row = array_merge($coupons[$row['couponid']], $row);
                unset($row);
            }
            return $list;
        }
    }
}
