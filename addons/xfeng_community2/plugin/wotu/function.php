<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/13 0013 下午 2:17
 */
/**
 * 获取token（鉴权接口）
 * 接口文档：http://developer.uface.uni-ubi.com/document
 */
function getAuthToken($appid, $appkey, $appSecret)
{
    global $_W;
    $tm = time();
    $sign = strtolower(md5($appkey . $tm . $appSecret));
    $params = array(
        'appId'     => $appid,
        'appKey'    => $appkey,
        'timestamp' => $tm,
        'sign'      => $sign
    );
    $cachekey = "accesstoken:{$_W['uniacid']}";
    $cache = cache_load($cachekey);
    if (!empty($cache) && !empty($cache['access_token']) && $cache['expire'] > TIMESTAMP) {
        return $cache['access_token'];
    }
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/auth', $params);
    $result = @json_decode($result['content'], true);
    $record = array();
    $record['access_token'] = $result['data'];
    $record['expire'] = $tm + 60 * 60;
    cache_write($cachekey, $record);
    return $result['data'];
}

/**
 * @param $appid 应用Id
 * @param $appkey 应用Key
 * @param $appSecret
 * @param $deviceKey 设备序列号
 * @param $name 设备名称
 * @param $tag 设备标签
 * @return array|post|mixed
 * 设备创建
 */
function addDevice($appid, $appkey, $appSecret, $deviceKey, $name = '', $tag = '')
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'     => $appid,
        'token'     => $token,
        'deviceKey' => $deviceKey
    );
    if ($name) {
        $params['name'] = $name;
    }
    if ($tag) {
        $params['tag'] = $tag;
    }
    $result = http_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/device', json_encode($params));
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 设备查询
 */
function getDevice($appid, $appkey, $appSecret, $deviceKey)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $url = "http://gs-api.uface.uni-ubi.com/v1/" . $appid . "/device/" . $deviceKey . "?appId=" . $appid . "&token=" . $token . "&deviceKey=" . $deviceKey;
    load()->func('communication');
    $result = ihttp_get($url);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 设备删除
 */
function deleteDevice($appid, $appkey, $appSecret, $deviceKey)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'     => $appid,
        'token'     => $token,
        'deviceKey' => $deviceKey
    );
    $url = "http://gs-api.uface.uni-ubi.com/v1/" . $appid . "/device/" . $deviceKey;
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    $result = callInterfaceCommon($url, 'DELETE', $params, $headers);
    $result = @json_decode($result, true);
    return $result;
}

/**
 * 人员录入
 */
function addPerson($appid, $appkey, $appSecret, $name, $idNo = '', $phone = '', $tag = '', $type = 0)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId' => $appid,
        'token' => $token,
        'name'  => $name
    );
    if ($idNo) {
        $params['idNo'] = $idNo;
    }
    if ($phone) {
        $params['phone'] = $phone;
    }
    if ($tag) {
        $params['tag'] = $tag;
    }
    if ($type) {
        $params['type'] = $type;
    }
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person', $params);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 人员的更新
 */
function updatePerson($appid, $appkey, $appSecret, $guid, $name, $idNo = '', $phone = '', $tag = '', $type = 0)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId' => $appid,
        'token' => $token,
        'guid'  => $guid,
        'name'  => $name
    );
    if ($idNo) {
        $params['idNo'] = $idNo;
    }
    if ($phone) {
        $params['phone'] = $phone;
    }
    if ($tag) {
        $params['tag'] = $tag;
    }
    if ($type) {
        $params['type'] = $type;
    }
    $url = "http://gs-api.uface.uni-ubi.com/v1/" . $appid . "/person/" . $guid;
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    $result = callInterfaceCommon($url, 'PUT', $params, $headers);
    $result = @json_decode($result, true);
    return $result;
}

/**
 * 人员设备授权
 */
function addPersonDevice($appid, $appkey, $appSecret, $guid, $deviceKeys = '', $passTimes = '')
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'      => $appid,
        'token'      => $token,
        'guid'       => $guid,// 人员的 id
        'deviceKeys' => $deviceKeys,// 所有需授权的设备 多个用逗号隔开
//        'passTimes'  => $passTimes// 允许进入的时间段
    );
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person/' . $guid . '/devices', $params);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 人员删除
 */
function deletePerson($appid, $appkey, $appSecret, $guid)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId' => $appid,
        'token' => $token,
        'guid'  => $guid,// 人员的 id
    );
    $url = 'http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person/' . $guid;
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    $result = callInterfaceCommon($url, 'DELETE', $params, $headers);
    $result = @json_decode($result, true);
    return $result;
}

/**
 * 人员照片注册(Base64)
 */
function addPersonImage($appid, $appkey, $appSecret, $guid, $img, $type = 1, $useUFaceCloud = true, $validLevel = 0)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId' => $appid,
        'token' => $token,
        'guid'  => $guid,// 照片所属人的 id
        'img'   => $img,// 照片数据（ base64编码）
//        'type'          => $type,// 照片类型 1：普通 RGB 照片 2：红外照片
//        'useUFaceCloud' => $useUFaceCloud,// 服务器端照片检测
//        'validLevel'    => $validLevel// 校验等级
    );
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person/' . $guid . '/face', $params);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 人员照片注册(url)
 */
function addPersonImageUrl($appid, $appkey, $appSecret, $guid, $imageUrl, $type = 1, $useUFaceCloud = true, $validLevel = 0)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'    => $appid,
        'token'    => $token,
        'guid'     => $guid,// 照片所属人的 id
        'imageUrl' => $imageUrl,// 照片url(jpg格式)
//        'type'          => $type,// 照片类型 1：普通 RGB 照片 2：红外照片
//        'useUFaceCloud' => $useUFaceCloud,// 服务器端照片检测
//        'validLevel'    => $validLevel// 校验等级
    );
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person/' . $guid . '/face/imageUrl', $params);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 远程开锁(设备交互)
 */
function openLn6($appid, $appkey, $appSecret, $deviceKey)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'     => $appid,
        'token'     => $token,
        'deviceKey' => $deviceKey,
    );
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/deviceInteractiveRecord/' . $deviceKey, $params);
    $result = @json_decode($result['content'], true);

    if ($result['code'] == 'GS_SUS900') {
        $content = array(
            'code'   => 0,
            'info'   => '成功开门',
            'status' => 'ok'
        );
    } else {
        $content = array(
            'code'   => 1,
            'info'   => '设备离线',
            'status' => 'no'
        );
    }
    return $content;
}

/**
 * 人员的照片删除
 */
function deletePersonImage($appid, $appkey, $appSecret, $guid, $personGuid)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'      => $appid,
        'token'      => $token,
        'guid'       => $guid,// 照片的 id
        'personGuid' => $personGuid,// 照片所属人员 id
    );
    $url = "http://gs-api.uface.uni-ubi.com/v1/" . $appid . "/person/" . $personGuid . "/face/" . $guid;
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    $result = callInterfaceCommon($url, 'DELETE', $params, $headers);
    $result = @json_decode($result, true);
    return $result;
}

/**
 * 人员设备批量销权
 */
function plDeletePersonDevice($appid, $appkey, $appSecret, $guid, $deviceKeys)
{
    $token = getAuthToken($appid, $appkey, $appSecret);
    $params = array(
        'appId'      => $appid,
        'token'      => $token,
        'guid'       => $guid,// 人员的 id
        'deviceKeys' => $deviceKeys,// 所有需销权的设备 多个用逗号隔开
    );
    load()->func('communication');
    $result = ihttp_post('http://gs-api.uface.uni-ubi.com/v1/' . $appid . '/person/' . $guid . '/devices/delete', $params);
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 *  发起POST DELETE GET PUT 请求通用类
 */
function callInterfaceCommon($URL, $type, $params, $headers)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $URL); //发贴地址
    if ($headers != "") {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } else {
//        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: text/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/plain;charset=utf8'));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    switch ($type) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
    }
    $file_contents = curl_exec($ch);//获得返回值
    return $file_contents;
    curl_close($ch);
}