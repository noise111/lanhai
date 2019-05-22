<?php

/**
 * Created by lanniu.
 * User: zhoufeng
 * Time: 2017/6/17 下午8:19
 */
class model_region
{
    // 会员数据表
    public static $TABLE_REGION = "xcommunity_region";
    public static $TABLE_PROPERTY = "xcommunity_property";
    public static $TABLE_COMPANY = "xcommunity_company";
    //检查一条小区数据
    static function region_check($regionid)
    {
        global $_W;
        $region = pdo_get(self::$TABLE_REGION, array('uniacid' => $_W['uniacid'], 'id' => $regionid), array('title','id','linkway','qq','thumb','pid','commission', 'province', 'city', 'dist'));
//        if(empty($region)){
//            itoast('非法操作',referer(),'error');exit();
//        }else{
            return $region;
//        }
    }
    //查所有小区数据
    static function region_fetall($data='',$uid='')
    {
        global $_W;
        $condition ="uniacid='{$_W['uniacid']}' ";
        if($data){
            $condition .= $data;
        }
        $uid = $uid ? $uid : $_W['uid'];
        $user = util::xquser($uid);
        if($user['type'] == 3 || $user['type'] == 4){
            $condition .= " and id in({$user['regionid']})";
        }
        return pdo_getall(self::$TABLE_REGION,$condition, array());

    }
    //查所有物业
    static function property_fetall($data='')
    {
        global $_W;
        $condition ="uniacid='{$_W['uniacid']}' ";
        if($data){
            $condition .= $data;
        }
        return pdo_getall(self::$TABLE_PROPERTY,$condition, array());
    }
    //查所有公司
    static function company_fetall()
    {
        global $_W;
        $condition ="uniacid='{$_W['uniacid']}' ";
        $user = util::xquser($_W['uid']);
        if($user['type'] == 2 || $user['type'] == 3){
//            $condition .= " and id in({$user['pid']})";
            $condition .=" and uid={$_W['uid']}";

        }
        return pdo_getall(self::$TABLE_COMPANY,$condition, array());
    }
}