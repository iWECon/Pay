<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 16:12
 */


require_once (dirname(__FILE__).'/../../Payment/Payment.php');

class Alipay extends Payment {

    // 参数详情参见：https://docs.open.alipay.com/204/105465/

    /* 公共参数, 必填 */
    protected $app_id;
    protected $method;
    private $charset = 'UTF-8';
    private $sign_type = 'RSA2';
    private $sign;
    private $timestamp;
    private $version = '1.0';
    private $notify_url;
    private $biz_content;

    /* 公共参数, 非必填 */
    public $format = 'JSON';

    /* 业务参数, 必填 */
    private $subject;               // 商品标题/交易标题/订单标题/订单关键字等
    private $out_trade_no;          // 商户网站唯一订单号
    private $total_amount;          // 单位: 元, 精确到小数点后两位
    private $product_code = 'QUICK_MSECURITY_PAY';

    /* 业务参数, 非必填 */
    private $body;                  // 对一笔交易的具体描述信息
    private $timeout_express;       // 超时时间 (1d 15m 1c 分别表示 1天 15分钟 当天0点前)
    private $goods_type;            // 商品类型 0虚拟, 1实物
    private $passback_params;       // 公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数
    private $promo_params;          // 优惠参数, 注：仅与支付宝协商后可用
    private $extend_params;         // 业务扩展参数
    private $enable_pay_channels;   // 可用渠道, 用户只能在指定渠道范围内支付, 当有多个渠道时用“,”分隔, 注: 与disable_pay_channels互斥
    private $disable_pay_channels;  // 禁用渠道, 用户不可用指定渠道支付, 当有多个渠道时用“,”分隔, 注: 与enable_pay_channels互斥
    private $store_id;              // 商户门店编号, 该参数用于请求参数中以区分各门店
    private $ext_user_info;         // 外部指定买家, 详见外部用户ExtUserInfo参数说明


    /* 业务扩展参数, 非必填 */
    private $sys_service_provider_id;   // 系统商编号，该参数作为系统商返佣数据提取的依据，请填写系统商签约协议的PID
    private $needBuyerRealnamed;        // 是否发起实名校验, T: 发起, F: 不发起
    private $TRANS_MEMO;                // 账务备注, 注: 该字段显示在离线账单的账务备注中
    private $hb_fq_num;                 // 花呗分期数（目前仅支持3、6、12), 参见文档: https://docs.open.alipay.com/277/106748
    private $hb_fq_seller_percent;      // 卖家承担收费比例，商家承担手续费传入100，用户承担手续费传入0，仅支持传入100、0两种，其他比例暂不支持

    /* 自定义私有参数 */
    protected $config;
    protected $aopClient;
    protected $tradeAppPayRequest;
    protected $rsaPublicKey;
    protected $rsaPrivateKey;

    public function __construct() {

        require_once (dirname(__FILE__).'/../SDK/aop/AopClient.php');
        $this->aopClient = new AopClient();

        $this->app_id = $this->config['app_id'];

        $this->rsaPublicKey = $this->config['rsaPublic'];
        $this->rsaPrivateKey = $this->config['rsaPrivate'];
    }

    public function configMustParams($param_charset, $param_signType) {
        $this->charset = $param_charset;
        $this->sign_type = $param_signType;
    }

    /* 配置公共参数
     *
     * body: 名称
     * subject: 支付标题
     * outTradeNo: 订单号
     * timeoutExpress: 超时时间, 30m 表示30分钟
     * totalAmount: 订单金额
     * notifyURL: 异步通知回调地址, 注意不要带参数, 否则签名后调取支付宝app支付会提示不通过
     * */
    public function configBizContent($param_body, $param_subject, $param_outTradeNo, $param_timeoutExpress, $param_totalAmount, $param_notifyURL) {
        $this->body = $param_body;
        $this->subject = $param_subject;
        $this->out_trade_no = $param_outTradeNo;
        $this->timeout_express = $param_timeoutExpress;
        $this->total_amount = $param_totalAmount;
        $this->notify_url = $param_notifyURL;
    }

    /* make biz content */
    private function makeBizContent() {

        $array = array();

        $array['body'] = $this->body;
        $array['subject'] = $this->subject;
        $array['out_trade_no'] = $this->out_trade_no;
        $array['timeout_express'] = $this->timeout_express;
        $array['total_amount'] = $this->total_amount;
        $array['product_code'] = $this->product_code;

        $bizContent = json_encode($array);
        $this->biz_content = $bizContent;
    }

    /* 创建签名后的字符串用于给app调去支付宝app支付 */
    public function makeSign() {

        $this->makeBizContent();

        $this->configAopClient();
        $this->configTradeAppPay();

        $response = $this->aopClient->sdkExecute($this->tradeAppPayRequest);
        // $response = htmlspecialchars($response);
        return $response;
    }

    private function configAopClient() {
        $this->aopClient->appId = $this->app_id;
        $this->aopClient->format = $this->format;
        $this->aopClient->postCharset = $this->charset;
        $this->aopClient->signType = $this->sign_type;
        $this->aopClient->rsaPrivateKey = $this->rsaPrivateKey;
        $this->aopClient->alipayrsaPublicKey = $this->rsaPublicKey;
    }

    private function configTradeAppPay() {
        require_once (dirname(__FILE__).'/../../Alipay/SDK/aop/request/AlipayTradeAppPayRequest.php');

        $this->tradeAppPayRequest = new AlipayTradeAppPayRequest();

        $this->tradeAppPayRequest->setNotifyUrl($this->notify_url);
        $this->tradeAppPayRequest->setBizContent($this->biz_content);
    }

}

