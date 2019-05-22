<?php

class Appqrcode_EweiShopV2ComModel extends ComModel{
    
    protected $token_cache = '_wxapp_cache_token';
    
    public function getQrcodeContent($scene){
        $response = false;
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
            m('cache')->set($this->token_cache, array('access_token' => $content['access_token'], 'expires_in' => (time() + $content['expires_in']- 1800)));
            return $content;
        } else {
            return false;
        }
    }
    
    public function getAccessToken(){
        $access_token = m('cache')->get($this->token_cache);
        $sets = m('common')->getSysset('app');
        if(!$access_token){          
            $access_token = $this->getAppAccessToken($sets['appid'], $sets['secret']);
        } else {
            if($access_token['expires_in'] < time()){
                $access_token = $this->getAppAccessToken($sets['appid'], $sets['secret']);
            }                
        }
        $token = $access_token['access_token'];
        return $token;
    }
    
    public function getOrderVerifyCode($orderid){
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=%s";
        $format_url = sprintf($url, $token);
        $scene = "orderid=$orderid&act=verify";
        $params = array(
            'scene'     => $scene,
            'page'      => 'pages/order/verify/index'
        );
        $response = http_post_json($format_url,json_encode($params));   
        return $response;        
    }    
}