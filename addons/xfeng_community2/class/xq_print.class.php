<?php

/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/24 下午2:24
 */
class xq_print
{

    static function xqprint($type,$api,$content,$regionid=''){
        global $_W;
        if($api == 1){
            //统一
            if($type =='yl'){
                $key = set('d8');
                $device_code = set('d9');
               $result = xq_print::yl($device_code,$key,$content);
            }elseif($type=='fy'){
                $key = set('d11');
                $device_code = set('d12');
                $account = set('d10');
                $result = xq_print::fy($key,$content,$device_code,$account);
            }
        }else{
            //小区
            if($type =='yl'){
                $key = set('x39',$regionid);
                $device_code = set('x40',$regionid);
                $result =  xq_print::yl($device_code,$key,$content);
            }elseif($type=='fy'){
                $key = set('x42',$regionid);
                $device_code = set('x43',$regionid);
                $account = set('x41',$regionid);
                $result =  xq_print::fy($key,$content,$device_code,$account);
            }
        }
        return $result;
    }
    static function yl($device_code,$key,$content){
        $selfitoast = array(
            'deviceNo'     => $device_code,
            'printContent' => $content,
            'key'          => $key,
            'times'        => 1 //打印次数
        );
        $url = "http://open.printcenter.cn:8080/addOrder";
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded ",
                'method'  => 'POST',
                'content' => http_build_query($selfitoast),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    static function fy($key,$msg,$device_code,$account){
        global $_W;
        $API_KEY = $key;
        $msg['deviceNo'] = $device_code;
        $msg['msgNo'] = $key;
        $msg['memberCode'] = $account;
        $msg['reqTime'] = number_format(1000 * time(), 0, '', '');
        $content = $msg['memberCode'] . $msg['msgDetail'] . $msg['deviceNo'] . $msg['msgNo'] . $msg['reqTime'] . $API_KEY;
        $msg['securityCode'] = md5($content);
        $msg['mode'] = 2;
         load()->func('communication');
        $result = ihttp_post('http://my.feyin.net/api/sendMsg', $msg);
        return $result;
    }
}