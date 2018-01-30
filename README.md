> 一款支持支付宝支付AppSDK、微信支付AppSDK和微信小程序支付的集成框架!

##### 使用方法
1. 下载 `Pay`(Pay目录以及Pay目录包含的所有文件) 到服务器中.
2. 从支付宝下载 `服务端SDK`([由此进入](https://docs.open.alipay.com/54/103419)), `解压后`放入 `Pay/Alipay/SDK` 中, 若没有SDK这个文件夹, 则需要`自行创建`;

##### 配置
`支付宝` 的配置在 `Pay/Alipay/Config/AlipayConfig.php`; \
AlipayConfig.php
```
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
```


`微信` 的配置在 `Pay/WechatPay/Config/WechatPayConfig.php`; \
WechatPayConfig.php
```
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
```

##### 使用
见 `Pay/index.php`，按照注释填写相关内容；\
微信回调地址：https://域名/路径/Pay/WechatPay/Respond/respond.php \
支付宝回调地址：https://域名/路径/Pay/Alipay/Respond/respond.php

**微信回调地址**\
需要修改/添加：
```
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
```

**支付宝回调地址**\
需要修改/添加：
```
        if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
            // 交易成功/支付成功
            // 改变订单状态

            Save_log('body: '.$respondBody.', \nLog_ID: '.$log_id.', \n支付宝交易号: '.$trade_no.', \n结果: 支付成功.');

            return;
        }
```


**需要配置的参数已在php文件中说明, 自行查找解决~**
