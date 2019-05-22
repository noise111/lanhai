<?php

/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/21 上午12:13
 */
class sms
{
    public static $JUHE_URL = "http://v.juhe.cn/sms/send";
    public static $WWT_URL = "http://cf.51welink.com/submitdata/Service.asmx/g_Submit";
    public static $QHYX_URL = "http://203.81.21.34/send/gsend.asp?";

    static function send($mobile, $data, $type, $regionid, $api, $tpl_id = '', $uniacid = '', $key = '')
    {
        global $_W;
        load()->func('communication');

        if ($type == 'juhe') {
            $key = $api == 1 ? set('s13') : set('x38', $regionid);
            $params = "mobile=" . $mobile . "&tpl_id=" . $tpl_id . "&tpl_value=" . $data . "&key=" . $key;
            $content = ihttp_post(self::$JUHE_URL, $params);
            if ($content) {
                $result = json_decode($content['content'], true);
                $error_code = $result['error_code'];
                if ($error_code == 0) {
                    //状态为0，说明短信发送成功
                    return array('status' => 1);
                    exit();
                }
                else {
                    return array('status' => 0);
                }
            }
        }
        elseif ($type == 'wwt') {
            $sname = $api == 1 ? set('s14') : set('x30', $regionid);
            $spwd = $api == 1 ? set('s15') : set('x31', $regionid);
            $key = $api == 1 ? set('s16') : set('x32', $regionid);
            $params = 'sname=' . $sname . "&spwd=" . trim($spwd) . "&scorpid=" . "&sprdid=1012888" . "&sdst=" . $mobile . "&smsg=" . rawurlencode($data . '【' . $key . '】');

            $result = ihttp_post(self::$WWT_URL, $params);
            $result = xmlToArray($result['content']);
            if(!empty($result['MsgID'])){
                return array('status' => 1);
            }else{
                return array('status' => 0, 'message' => '短信发送失败(错误信息: ' . $result['MsgState'] . ')');
            }
        }
        elseif ($type == 'aliyun_new') {
            include_once COMMUNITY_ADDON_ROOT . 'class/aliyun.class.php';
            $aliyun_new_keyid = $api == 1 ? set('s30') : set('x75', $regionid);
            $aliyun_new_keysecret = $api == 1 ? set('s31') : set('x76', $regionid);
            $smssign = $api == 1 ? set('s32') : set('x77', $regionid);
            $option = array('keyid' => $aliyun_new_keyid, 'keysecret' => $aliyun_new_keysecret, 'phonenumbers' => $mobile, 'signname' => $smssign, 'templatecode' => $tpl_id, 'templateparam' => $data);
            $result = sendSms($option);
            if ($result['Message'] != 'OK') {
                return array('status' => 0, 'message' => '短信发送失败(错误信息: ' . $result['Message'] . ')');
            }
            else {
                return array('status' => 1);
            }
        }
        elseif ($type == 'qhyx') {
            $sname = $api == 1 ? set('s35') : '';
            $spwd = $api == 1 ? set('s36') : '';
            $time = TIMESTAMP;
            $params = 'name=' . trim($sname) . "&pwd=" . trim($spwd) . "&dst=" . $mobile . "&msg=" . rawurlencode(mb_convert_encoding($data, 'gb2312', 'utf-8')) . "&time=" . $time;
            $url = self::$QHYX_URL . $params;
            $result = ihttp_get($url);
            return array('status' => 1);
            exit();
        }
    }

    static function juhe_send($mobile, $data, $api, $dat = '')
    {
        //$api 1 独立接口
        load()->func('communication');
        $key = $api == 1 ? set('s13') : set('x38', $dat);
        $tpl_id = $api == 1 ? set('s11') : set('x36', $dat);
        if (empty($tpl_id) || empty($key)) {
            return '短信接口设置有问题';
            exit();
        }
        $params = "mobile=" . $mobile . "&tpl_id=" . $tpl_id . "&tpl_value=" . $data . "&key=" . $key;
        $content = ihttp_post(self::$JUHE_URL, $params);
//        if($content){
//            $result = json_decode($content['content'],true);
//            $error_code = $result['error_code'];
//            if($error_code == 0){
//                //状态为0，说明短信发送成功
//                return array('status' => 1);exit();
//            }
//        }
    }

    static function wwt_send($mobile, $data, $api, $dat = '')
    {
        load()->func('communication');
        $sname = $api == 1 ? set('s14') : set('x30', $dat);
        $spwd = $api == 1 ? set('s15') : set('x31', $dat);
        $key = $api == 1 ? set('s16') : set('x32', $dat);
        if (empty($sname) || empty($spwd) || empty($key)) {
            return '短信接口设置有问题';
            exit();
        }
        $params = 'sname=' . $sname . "&spwd=" . $spwd . "&scorpid=" . "&sprdid=1012888" . "&sdst=" . $mobile . "&smsg=" . rawurlencode($data . '【' . $key . '】');
        $result = ihttp_post(self::$WWT_URL, $params);

    }
}