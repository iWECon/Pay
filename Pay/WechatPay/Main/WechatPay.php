<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 12:58
 */

require (dirname(__FILE__).'/../../Payment/Payment.php');

class WechatPay extends Payment {

    protected $config;

    /* 必填项, 通过WechatPayConfig.php 配置即可 */
    protected $app_id;                 // appid
    protected $mch_id;                 // 商户id
    protected $api_key;                // 商户key, 商户平台设定的32位密钥

    /* 必填项 */
    protected $nonce_str;            // 随机字符串
    protected $body;                 // 商品描述
    protected $out_trade_no;         // 订单号
    protected $total_fee;            // 总金额, 单位: 分
    protected $spbill_create_ip;     // 提交订单终端ip
    protected $notify_url;           // 异步通知URL
    protected $trade_type;           // 交易类型

    /* 选填, 参考: https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1 */
    public $device_info  = null;
    public $sign_type    = null; // 默认为MD5
    public $detail       = null;
    public $attach       = null; // 附加描述信息
    public $fee_type     = null;
    public $time_start   = null;
    public $time_expire  = null;
    public $goods_tag    = null;
    public $product_id   = null;
    public $limit_pay    = null;
    public $openid       = null;
    public $scene_info   = null;

    // 统一下单接口, 参考: https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1 中的[接口链接]
    private $unifiedOrderURL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    public function __construct() {
        $this->app_id = $this->config['app_id'];
        $this->mch_id = $this->config['mch_id'];
        $this->api_key = $this->config['api_key'];
    }

    // 配置必填参数
    public function configMustParams($param_body, $param_totalFee, $param_outTradeNo, $param_timeExpire, $param_notifyURL) {
        if (!is_numeric($param_timeExpire)) {
            die('有效时间 param_timeExpire 只能为数字, 且单位为: 秒');
        }
        $this->time_expire  = date('YmdHis', time() + $param_timeExpire);

        $this->body         = $param_body;
        $this->total_fee    = $param_totalFee;
        $this->out_trade_no = $param_outTradeNo;
        $this->notify_url   = $param_notifyURL;
    }


    // Check Parameters
    private function checkParameters() {

        if (empty($this->body)) {
            die('商品描述(body)为空');
        }
        if (empty($this->out_trade_no)) {
            die('订单号(out_trade_no)为空');
        }
        if (empty($this->total_fee) || !is_numeric($this->total_fee)) {
            die('总金额(total_fee)为空');
        }
        if (empty($this->notify_url)) {
            die('通知地址(notify_url)为空');
        }

        // Completion notifyURL
        /*
        if (!preg_match('#^http:\/\/#i', $this->notify_url)) {
            $this->notify_url = "http://".$_SERVER['HTTP_HOST'] . $this->notify_url;
        } */
    }

    // Generation Sign
    public function makeSign() {

        // 1. CheckParam
        $this->checkParameters();

        // 2. Configuration must parameters
        $this->nonce_str = $this->make32RandChar();
        $this->spbill_create_ip = $this->getClientIP();

        // 3. Packaging must parameters
        $waitRequestParam['nonce_str'] = $this->nonce_str;
		$waitRequestParam['body'] = $this->body;
        $waitRequestParam['notify_url'] = $this->notify_url;
        $waitRequestParam['out_trade_no'] = $this->out_trade_no;
        $waitRequestParam['spbill_create_ip'] = $this->spbill_create_ip;
        $waitRequestParam['total_fee'] = $this->total_fee;
        $waitRequestParam['time_expire'] = $this->time_expire;
        $waitRequestParam['trade_type'] = $this->trade_type;

        // 4. Packaging non essential Parameters
        if (!is_null($this->device_info)) {
            $waitRequestParam['device_info'] = $this->device_info;
        }
        if (!is_null($this->sign_type)) {
            $waitRequestParam['sign_type'] = $this->sign_type;
        }
        if (!is_null($this->detail)) {
            $waitRequestParam['detail'] = $this->detail;
        }
        if (!is_null($this->attach)) {
            $waitRequestParam['attach'] = $this->attach;
        }
        if (!is_null($this->fee_type)) {
            $waitRequestParam['fee_type'] = $this->fee_type;
        }
        if (!is_null($this->time_start)) {
            $waitRequestParam['time_start'] = $this->time_start;
        }
        if (!is_null($this->time_expire)) {
            $waitRequestParam['time_expire'] = $this->time_expire;
        }
        if (!is_null($this->goods_tag)) {
            $waitRequestParam['goods_tag'] = $this->goods_tag;
        }
        if (!is_null($this->product_id)) {
            $waitRequestParam['product_id'] = $this->product_id;
        }
        if (!is_null($this->limit_pay)) {
            $waitRequestParam['limit_pay'] = $this->limit_pay;
        }
        if (!is_null($this->openid)) {
            $waitRequestParam['openid'] = $this->openid;
        }
        if (!is_null($this->scene_info)) {
            $waitRequestParam['scene_info'] = $this->scene_info;
        }

        // 5. Place an order
        return $this->unifiedOrder($waitRequestParam);
    }

    // Place an order
    private function unifiedOrder($data) {

        // 1. Reserve raw data
        $tempData = $data;

        // 2. Append appid & mch_id
        $tempData['appid'] = $this->app_id;
        $tempData['mch_id'] = $this->mch_id;

        // 3. Get Sign
        $tempData['sign'] = $this->getSign($tempData, $this->api_key);

        // 4. Change Array to XML
        $xml = $this->arrayToXml($tempData);

        // 5. Request unifiedOrderURL POST XML
        $response = $this->postXmlCurl($xml, $this->unifiedOrderURL);

        // 6. Change ResponseData(XML) to Array
        $responseArray = $this->xmlToArray($response);

        // 7. Check
        if (!is_array($responseArray) || count($responseArray) <= 0) {
            return false;
        }

        // 8. Check prepay_id
        $prepayID = $responseArray['prepay_id'];

        if (empty($prepayID) || !isset($prepayID)) {
            die(json_encode(array('info'=>'没有获取到微信返回的预支付订单号(prepay_id)', 'data'=>$responseArray)));
        }
        // 9. Get final Sign
		if ($this->trade_type == 'APP') {
			return $this->getAppFinalSign($prepayID);
		} elseif ($this->trade_type == 'JSAPI') {
			return $this->getMicroFinalSign($prepayID);
		}
		return false;
    }


    // Get fainal sign
    private function getAppFinalSign($prepayID) {
        $data['appid'] = $this->app_id;
        $data['noncestr'] = $this->make32RandChar();
        $data['package'] = "Sign=WXPay";
        $data['partnerid'] = $this->mch_id;
        $data['prepayid'] = $prepayID;
        $data['timestamp'] = time();
        $data['sign'] = $this->getSign($data, $this->api_key);
        return $data;
    }
	
	private function getMicroFinalSign($prepayID) {
		
		$time = time();
		$data['appId'] = $this->app_id;
		$data['nonceStr'] = $this->make32RandChar();
		$data['timeStamp'] = "{$time}";
		$data['package'] = "prepay_id=" . $prepayID;
		$data['signType'] = "MD5";
		$data['paySign'] = $this->getSign($data, $this->api_key);
		return $data;
	}


    // Get sign
    private function getSign($data, $key) {
		
        // 1. 排序并转换为字符串
        if ($this->trade_type == "JSAPI") {
            $str = $this->sortByAsciiAndConverToParam($data, false);
        } else {
            $str = $this->sortByAsciiAndConverToParam($data);
        }

        // 2. 拼接key
        $str = $str . "&key=" . $key;

        // 3. MD5加密并转换为大写
        $md5Str = strtoupper(md5($str));

        // 返回最终的结果
        return $md5Str;
    }








    /* 辅助函数 */

    private function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    private function xmlToArray($xml) {
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array;
    }

    private function postXmlCurl($xml, $url, $second = 30, $useCert = false, $sslcert_path = '', $sslkey_path = '') {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);

        if($useCert == true){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $sslcert_path);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $sslkey_path);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);

        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
}
