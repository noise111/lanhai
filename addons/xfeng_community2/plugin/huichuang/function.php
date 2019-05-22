<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/8 上午8:24
 */
function getAccessToken($company_id, $access_secret, $sign_key, $i = '')
{
    global $_W;
    $i = $i ? $i : $_W['uniacid'];
    $cachekey = "accesstoken:{$i}{$company_id}";
    $cache = cache_load($cachekey);
    if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
        return $cache['token'];
    }
    else {
        load()->func('communication');
        $url = 'https://www.parkthe.com/xyyapi/public/company/token';
        $data = array(
            'company_id'    => $company_id,
            'access_secret' => $access_secret,
        );
        $d = base64_encode(json_encode($data));
        $nonce = getRandom();
        $createtime = TIMESTAMP;
        $content = array(
            'rid'        => 'ab1000014101' . $createtime,
            'time_mills' => strval($createtime),
            'nonce'      => $nonce,
            'key'        => $sign_key,
            'data'       => $d
        );
        asort($content);

        $content = implode(',', $content);

        $sign = md5($content);

        $t = array(
            'rid'        => 'ab1000014101' . $createtime,
            'time_mills' => $createtime,
            'nonce'      => $nonce,
            'sign'       => $sign,
            'data'       => $d
        );

        $result = ihttp_post($url, $t);
        $result = json_decode($result['content'], true);

        $t2 = base64_decode($result['data']);

        $t2 = json_decode($t2, true);
        $record = array();
        $record['token'] = $t2['access_token'];
        $record['expire'] = TIMESTAMP + $t2['expires_in'] - 200;
        cache_write($cachekey, $record);
        return $t2['access_token'];
    }

}

function getParks($sign_key, $token)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/company/parks';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $content = array(
        'rid'        => 'ab1000014102' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'key'        => $sign_key,
        'token'      => $token
    );
    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014102' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token
    );

    $result = ihttp_post($url, $t);
    $result = json_decode($result['content'], true);
    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    return $t2;
}

function getAllCars($sign_key, $park_id, $token, $last_time)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/cars';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id'   => $park_id,
        'last_time' => $last_time,
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014103' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'key'        => $sign_key,
        'token'      => $token,
        'data'       => $d
    );
    asort($content);
    $content = implode(',', $content);
    $sign = md5($content);
    $t = array(
        'rid'        => 'ab1000014103' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );

    $result = ihttp_post($url, $t);
    $result = json_decode($result['content'], true);
    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    if (!empty($t2)) {
        return $t2;
    }

}

function getOneCar($sign_key, $park_id, $token, $car_no)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/cars';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id' => $park_id,
        'car_no'  => $car_no,
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014104' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'key'        => $sign_key,
        'token'      => $token,
        'data'       => $d
    );

    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014104' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );

    $result = ihttp_post($url, $t);

    $result = json_decode($result['content'], true);
    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    return $t2;
}

function updateCar($sign_key, $park_id, $token, $park_serial, $car_no, $begin_date, $end_date, $pay_fee, $toll_date, $toll_by, $remark)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/car/renew';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id'     => $park_id,
        'car_no'      => $car_no,
        'park_serial' => $park_serial,
        'car_no'      => $car_no,
        'begin_date'  => $begin_date,
        'end_date'    => $end_date,
        'pay_fee'     => $pay_fee,
        'toll_date'   => $toll_date,
        'toll_by'     => $toll_by,
        '$remark'     => $remark
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014105' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'key'        => $sign_key,
        'token'      => $token,
        'data'       => $d
    );

    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014105' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );

    $result = ihttp_post($url, $t);
    $result = json_decode($result['content'], true);

    return $result;
}

function getCardTypes($park_id, $sign_key, $token)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/cardtypes';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id' => $park_id,
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014106' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'token'      => $token,
        'key'        => $sign_key,
        'data'       => $d
    );

    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014106' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );

    $result = ihttp_post($url, $t);

    $result = json_decode($result['content'], true);
    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    return $t2;
//    return $result;
}

function getPowers($park_id, $sign_key, $token)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/power';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id' => $park_id,
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014107' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'token'      => $token,
        'key'        => $sign_key,
        'data'       => $d
    );

    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014107' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );

    $result = ihttp_post($url, $t);
    $result = json_decode($result['content'], true);

    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    return $t2;
}
function getTemp($park_id, $sign_key, $token, $begin_time, $end_time)
{
    load()->func('communication');
    $url = 'https://www.parkthe.com/xyyapi/private/park/charge/temp';
    $nonce = getRandom();
    $createtime = TIMESTAMP;
    $data = array(
        'park_id'    => $park_id,
        'begin_time' => $begin_time,
        'end_time'   => $end_time,
    );
    $d = base64_encode(json_encode($data));
    $content = array(
        'rid'        => 'ab1000014108' . $createtime,
        'time_mills' => strval($createtime),
        'nonce'      => $nonce,
        'token'      => $token,
        'key'        => $sign_key,
        'data'       => $d
    );
//print_r($park_id.'/'.$sign_key.'/'.$token.'/'.$begin_time.'/'.$end_time);exit();
    asort($content);

    $content = implode(',', $content);

    $sign = md5($content);

    $t = array(
        'rid'        => 'ab1000014108' . $createtime,
        'time_mills' => $createtime,
        'nonce'      => $nonce,
        'sign'       => $sign,
        'token'      => $token,
        'data'       => $d
    );
    $result = ihttp_post($url, $t);
    $result = json_decode($result['content'], true);

    $t2 = base64_decode($result['data']);
    $t2 = json_decode($t2, true);
    return $t2;
}