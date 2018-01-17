<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/16
 * Time: 13:18
 */

define("TOKEN","ynAbeuyrZ4MPjl04Bf9mRRHYAHiz3daN"); // 后台填写的token
$wechatObj = new wechatAPI();
$wechatObj->isValid();

class wechatAPI {

    //验证微信接口，如果确认是微信就返回它传来的echostr参数
    public function isValid() {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    //官方的验证函数
    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
};