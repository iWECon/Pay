<?php
/**
 * Created by PhpStorm.
 * User: iwe
 * Date: 2017/12/30
 * Time: 下午2:25
 */

/*
 * 支付宝支付异步回调
 *
 * Created by iwe.
 * 2018.01.08
 * **/

if (!empty($_POST)) {
    // 回调信息不为空

    // 写入日志
    $respondBody = '';
    foreach ($_POST as $key => $value) {
        $respondBody .= $key .'='.$value.'&';
    }

    // 处理回调
    // 备份一份内容
    $tmpContent = $_POST;
    // 去除反斜杠
    $tmpContent['fund_bill_list'] = stripslashes($_POST['fund_bill_list']);

    // 引入SDK, 配置信息
    require_once (dirname(__FILE__).'/../SDK/aop/AopClient.php');
    require_once (dirname(__FILE__).'/../Config/AlipayConfig.php');
    // 注册SDK
    $aopClient = new AopClient();
    $aopClient->alipayrsaPublicKey = $appConfig['rsaPublic'];
    // 验证签名
    $checkResult = $aopClient->rsaCheckV1($tmpContent, null, $_POST['sign_type']);
    if ($checkResult) {
        // 支付验证通过
        $respond_data = $_POST;

        // 商户订单号 out_trade_no
        $out_trade_no = $respond_data['out_trade_no'];
        // 支付宝交易号
        $trade_no = $respond_data['trade_no'];
        // 交易状态
        $trade_status = $respond_data['trade_status'];

        if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
            // 交易成功/支付成功
            // 改变订单状态

            Save_log('body: '.$respondBody.', \nLog_ID: '.$log_id.', \n支付宝交易号: '.$trade_no.', \n结果: 支付成功.');

            return;
        }
    }
    Save_log('支付验证失败. \n回调参数: '.$respondBody);
    echo json_encode(array('code' => '-1', 'info' => '签名验证失败.', 'result' => null));
    exit();
} else {
    echo 'Hello, linux!';
    exit();
}


//打印日志
function Save_log($word='') {
    $fp = fopen("respond.log","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"记录时间：".strftime("%Y年%m月%d日 %H:%M:%S",time())."\n".$word."\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}