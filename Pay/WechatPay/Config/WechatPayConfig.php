<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 11:18
 */


/* App 支付配置
 *
 * param app_id: 应用ID
 * param mch_id: 商户Id
 * param api_key: 商户秘钥(商户Key) 32位, from pay.weixin.qq.com -->账户中心-->账户设置-->API安全-->密钥设置
 * */
$appConfig = array(
    'app_id' => '',
    'mch_id' => '',
    'api_key' => ''
);



/* 小程序 支付配置
 *
 * param app_id: 小程序 AppID
 * param secret: 小程序 AppSecret
 * param mch_id: 商户ID
 * param api_key: 商户秘钥(商户Key)
 * */
$microProgramConfig = array(
    'app_id' => '',
    'secret' => '',
    'mch_id' => '',
    'api_key' => ''
);
