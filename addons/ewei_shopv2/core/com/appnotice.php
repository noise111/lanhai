<?php

class Appnotice_EweiShopV2ComModel extends ComModel{
    public function getAccessToken(){
        global $_W;
        $appset = m("common")->getSysset("app");
        $cacheKey = "eweishop:wxapp:accesstoken:" . $_W["uniacid"];
        $accessToken = m("cache")->get($cacheKey, $_W["uniacid"]);
        if( !empty($accessToken) && !empty($accessToken["token"]) && TIMESTAMP < $accessToken["expire"] ) 
        {
            return $accessToken["token"];
        }
        if( empty($appset["appid"]) || empty($appset["secret"]) ) 
        {
            return error(-1, "未填写小程序的 appid 或 appsecret！");
        }
        load()->func("communication");
        $content = ihttp_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appset["appid"] . "&secret=" . $appset["secret"]);
        if( is_error($content) ) 
        {
            return error(-1, "获取微信公众号授权失败, 请稍后重试！错误详情: " . $content["message"]);
        }
        $result = @json_decode($content["content"], true);
        if( empty($result) || !is_array($result) || empty($result["access_token"]) || empty($result["expires_in"]) ) 
        {
            $errorinfo = substr($content["meta"], strpos($content["meta"], "{"));
            $errorinfo = @json_decode($errorinfo, true);
            return error(-1, "获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: 错误代码-" . $errorinfo["errcode"] . "，错误信息-" . $errorinfo["errmsg"]);
        }
        $record["token"] = $result["access_token"];
        $record["expire"] = (TIMESTAMP + $result["expires_in"]) - 200;
        m("cache")->set($cacheKey, $record, $_W["uniacid"]);
        return $result["access_token"];
    }
    
    public function sendMyTplMessage($touser, $templatename, $datas, $form_id, $page = "/pages/index/index"){
        if($templatename){
            $data = array();
            $template = $this->getTMessage($templatename);
            foreach($template["datas"] as $index => $item ) {
                $key = str_replace(array( "{{", ".DATA}}" ), "", trim($item["key"]));
                if( empty($key) ) 
                {
                    continue;
                }
                $data[$key] = array( "value" => $this->replaceTemplate($item["value"], $datas), "color" => $item["color"] );
                if( $index == $tempate["emphasis_keyword"] ) 
                {
                    $emphasis_keyword = $key;
                }
            }
            $send_data = array();
            $send_data['touser'] = $touser;
            $send_data['weapp_template_msg'] = array(
                "template_id"       => $tempate["templateid"],
                "form_id"           => $form_id,
                "page"              => $page,
                "data"              => $data
            );
            $access_token = $this->getAccessToken();
            return $this->sendMyMessage($access_token, $send_data);
        } else {
            return false;
        }
    }

    public function sendMyMessage($access_token, $data){
        load()->func("communication");
        $result = ihttp_post("https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token=" . $access_token, json_encode($data));
        return $result;
    }

    /**
     * 发送导购销售商品生成订单提醒
     */
    public function sendOrderCreateMessage($orderid, $form_id){
        ini_set("display_error", 1);
        error_reporting(E_ALL);
        $order = pdo_get("ewei_shop_order", array("id" => $orderid));
        $sql = "SELECT GROUP_CONCAT(g.title) as title FROM " . tablename("ewei_shop_order_goods") ." og " .
               " LEFT JOIN " . tablename("ewei_shop_goods") . " g ON g.id = og.goodsid " .
               " WHERE og.orderid = $orderid";
        //商品详情
        $goods = pdo_fetchcolumn($sql);
        //收货地址
        if($order['addressid']){
            
        }
    }
}