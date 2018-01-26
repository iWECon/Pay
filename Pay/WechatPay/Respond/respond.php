<?php
/**
 * Created by PhpStorm.
 * User: iwe
 * Date: 2017/12/30
 * Time: 下午2:25
 */


require(dirname(__FILE__).'/../Config/WechatPayConfig.php');

//存储微信的回调
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
$data = xmlToArray($xml);

// 验证签名
$checkingPass = checkSign($data);
if ($checkingPass == FALSE) {
    echo json_encode(array('code' => '200', 'info' => '支付失败', 'result' => array('return_code' => 'FAIL', 'return_msg' => '签名失败.')));
    exit();
} else {
    $return = array('return_code' => 'SUCCESS');
}
// 这里echo是将数据返回给微信
echo returnXml($return);

if ($checkingPass == TRUE) {
    if ($data['return_code'] == 'FAIL') {
        log_result('通信出错: \n'.$xml.'\n');
    } elseif ($data['result_code'] == 'FAIL') {
        log_result('业务出错: \n'.$xml.'\n');
    } else {
        // 在此写下订单支付成功后的操作, 例如修改订单状态为已支付 或者其他操作

        // 记录订单号到日志, 根据实际情况记录需要的信息到日志中
        //$wxsn = $data['out_trade_no'];
        //log_result('支付成功, 微信订单号: '.$wxsn.', \n回调参数: \n'.$xml);
        exit();
    }
}
log_result('未知状态: \n'.$xml);
exit();

// --------------------- 下面都是一些验证时候使用的方法, 所以无需理会
function checkSign($data) {
    $tmpData = $data;
    //log_result("tmpData / Data：".$tmpData);
    unset($tmpData['sign']);

    if ($tmpData['trade_type'] == 'JSAPI') {
        // 小程序支付验证
        $sign = getSign($tmpData, true);
    } else {
        // 微信app sdk支付验证
        $sign = getSign($tmpData);//本地签名
    }
    log_result("客户端签名：".$data['sign']."  本地签名:".$sign);
    if ($data['sign'] == $sign) {
        return TRUE;
    }
    return FALSE;
}
function getSign($Obj, $isMicro = false) {

    foreach ($Obj as $k => $v) {
        $Parameters[$k] = $v;
    }

    //签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    //签名步骤二：在string后加入KEY
    if ($isMicro) { // 小程序配置
        global $microProgramConfig;
        $String = $String.'&key='.$microProgramConfig['api_key'];
    } else { // 微信appsdk支付配置
        global $appConfig;
        $String = $String.'&key='.$appConfig['api_key'];
    }
    //签名步骤三：MD5加密
    $String = md5($String);
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    return $result_;
}

/**
 * 	作用：格式化参数，签名过程需要使用
 */
function formatBizQueryParaMap($paraMap, $urlencode) {
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v) {
        if($urlencode) {
            $v = urlencode($v);
        }
        //$buff .= strtolower($k) . "=" . $v . "&";
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar = '';
    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff)-1);
    }
    return $reqPar;
}

/**
 * 将xml转为array
 */
function xmlToArray($xml) {
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}


/**
 * 将xml数据返回微信
 */
function returnXml($returnParameters) {
    $returnXml = createXml($returnParameters);
    return $returnXml;
}
/**
 * 生成接口参数xml
 */
function createXml($returnParameters) {
    global $appConfig;
    if($returnParameters["return_code"] == "SUCCESS"){
        $returnParameters["appid"] = $appConfig['app_id'];//公众账号ID
        $returnParameters["mch_id"] = $appConfig['mch_id'];//商户号
        $returnParameters["nonce_str"] = create_noncestr();//随机字符串
        $returnParameters["sign"] = getSign($returnParameters);//签名
    }
    return arrayToXml($returnParameters);
}
//生成随机数
function create_noncestr( $length = 32 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str ="";
    for ( $i = 0; $i < $length; $i++ )  {
        $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $str;
}
/**
 * 	作用：array转xml
 */
function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val))
        {
            $xml.="<".$key.">".$val."</".$key.">";

        }
        else
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    }
    $xml.="</xml>";
    return $xml;
}


// 打印log
function log_result($word) {
    $fp = fopen("respond.log","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"记录时间：".strftime("%Y年%m月%d日 %H:%M:%S", time())."\n".$word."\n\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}