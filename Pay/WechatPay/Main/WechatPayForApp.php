<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 12:59
 */

require(dirname(__FILE__).'/WechatPay.php');

class WechatPayForApp extends WechatPay {

    //public $time_expire;   // 订单有效时间

    public function __construct() {
        require_once (dirname(__FILE__).'/../Config/WechatPayConfig.php');
        $this->config = $appConfig;
        $this->trade_type = 'APP';

        parent::__construct();
    }

}