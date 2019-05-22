<?php
//www.k8ym.com
global $_W;
define("IN_MOBILE", true);
require "../../../../../framework/bootstrap.inc.php";
require_once IA_ROOT . "/addons/xfeng_community/model.php";
require_once "./function.php";
$input = file_get_contents("php://input");
$result = json_decode($input, true);
if ($result) {
    $device = getParkDevice($result["identity"]);
    $park = getPark($device["parkid"]);
    $carno = $result["carno"];
    if ($device) {
        $log = addLog($result, $device["parkid"], $device["id"], $device["uniacid"]);
        $type = $device["type"];
        if ($result["idx"] == 2 && $result["open"] == 0 && $result["type"] == 2 && $type == 2) {
            $intoTime = $log["intoTime"];
            $outTime = TIMESTAMP;
            $ruleId = $park["temrule_id"];
            if ($log["logid"] && $intoTime) {
                $totalPrice = startRule($ruleId, $intoTime, $outTime, $result["identity"], $log["logid"]);
                if ($totalPrice) {
                    $total_price = $totalPrice;
                    $price = $totalPrice;
                    updateOutLog($total_price, $price, $paytime, $paytype, $log["logid"], $open_status, $type, $status);
                    $orderid = addOrder($carno, $totalPrice, $status, $result["identity"], $device["parkid"], $intoTime, $outTime, $paytype, $paytime, $log["logid"], $pay_status, $device["uniacid"]);
                }
                carPlace($result["identity"], $carno, $intoTime, $outTime, $totalPrice, $park["qr_status"]);
                if ($totalPrice) {
                    $status = qrLower($park["qr_status"], $result["identity"], $device["uniacid"], $orderid);
                }
            }
        }
    }
}