<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 11:16
 */


if ($action == "alipay") {
    AlipayWithApp($tradeNo, $totalAmount);
} else if ($action == "wechat") {
    WechatPayWithApp($tradeNo, $totalAmount);
}
exit();

// 支付宝 App 支付
function AlipayWithApp($tradeNo, $totalAmount) {
    require ('Alipay/Main/AlipayForApp.php');
    $alipay = new AlipayForApp();
    $alipay->configBizContent(
        '名称',
        '交易标题',
        '订单号',
        '超时时间，例如：30m 表示30分钟',
        '订单金额(需要支付的金额)',
        'https://域名/Pay/Alipay/Respond/respond.php' // 异步回调地址, 不要带参数
    );
    final_get_code(array('alisn' => $alipay->makeSign()));
}

// 微信 App 支付
function WechatPayWithApp($tradeNo, $totalAmount) {

    require('WechatPay/Main/WechatPayForApp.php');

    $app = new WechatPayForApp();
    // 最短失效时间为 5 分钟, 也就是最少要填写300秒
    $app->configMustParams(
        '好多巴多订单支付',
        '订单金额, 按分表示, 如果传入的元则需要 * 100',
        '订单号',
        '超时时间, 单位秒, 例如：1800 表示1800秒',
        'https://域名/Pay/WechatPay/Respond/respond.php' // 异步回调地址, 不要带参数
    );
    $result = $app->makeSign();
    echo json_encode(array($result));
}

// 微信小程序支付
function WechatPayForMicroProgram() {

    require('WechatPay/Main/WechatPayForMicroProgram.php');
    $micro = new WechatPayForMicroProgram();
    $micro->getOpenIDByCode('小程序传过来的Code');
    $micro->configMustParams(
        '商品描述',
        '金额, 记得 * 100',
        '订单号',
        '超时时间, 单位：秒',
        '回调地址'
    );
    $result = $micro->makeSign();
    echo json_encode($result);
}

