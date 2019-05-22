<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/8 上午8:37
 */

function getCounterToken($appid='',$secret=''){
    global $_W;
    $secret = 'admin';
    $appid = 'admin';
    $cachekey = "accesstoken:{$_W['uniacid']}";
    $cache = cache_load($cachekey);
    if (!empty($cache) && !empty($cache['access_token']) && $cache['expire'] > TIMESTAMP) {
        return $cache['access_token'];
    }
    try{
        libxml_disable_entity_loader(false);
        $client = new SoapClient("http://47.93.207.49:8080/IoTWebService/platformws?wsdl",array('encoding'=>'UTF-8'));
        $a=array('arg0'=>$appid,'arg1'=>$secret);
        $resss=$client->getPermission($a); //获取验证
        $access_token=json_decode($resss->return,true)['value'];
        $record = array();
        $record['access_token'] = $access_token;
        $record['expire'] = TIMESTAMP + 60*60;
        cache_write($cachekey, $record);
        return $access_token;
    } catch (SOAPFault $e) {}
}
function getDeviceInfo($deviceid){
    //获取当前设备信息
    try{
        libxml_disable_entity_loader(false);
        $client = new SoapClient("http://47.93.207.49:8080/IoTWebService/platformws?wsdl",array('encoding'=>'UTF-8'));
        $access_token = getCounterToken();
        $b=array('arg0'=>$access_token,'arg1'=>$deviceid);
        $ass = $client->getDeviceStateByID($b);
        return json_decode($ass->return)->value->heartbeat;
    } catch (SOAPFault $e) {}

}
function OpenDevice($deviceid){
    //开柜子
    try{
        libxml_disable_entity_loader(false);
        $client = new SoapClient("http://47.93.207.49:8080/IoTWebService/platformws?wsdl",array('encoding'=>'UTF-8'));
        $access_token = getCounterToken();
        $b=array('arg0'=>$access_token,'arg1'=>$deviceid,'arg2'=>'1');
        $ass=$client->controlDevices($b);
        $result = json_decode($ass->return,true)['res'];
        return $result;
    } catch (SOAPFault $e) {}
}