<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 16:09
 */


/*
 * app_id: 支付宝中创建的应用AppID
 * partner_id: 支付宝商户ID(PID)
 * seller: 支付宝登录账号(一般为邮箱账号吧)
 * rsaPrivate: 用工具生成的 商户应用私钥, 生成的商户应用公钥是要先写到 openhome.alipay.com app中的加密方式的
 * rsaPublic: 登录后查看对应App的支付宝公钥 https://open.alipay.com/platform/keyManage.htm
 * **/
$appConfig = array(
    'app_id'     => '',
    'partner_id' => '',
    'seller'     => '',
    'rsaPrivate' => '',
    'rsaPublic'  => ''
);



$wapConfig = array(
    'app_id'     => '',
    'partner_id' => '',
    'seller'     => '',
    'rsaPrivate' => '',
    'rsaPublic'  => ''
);
