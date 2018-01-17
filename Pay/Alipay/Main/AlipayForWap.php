<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 17:25
 */

require(dirname(__FILE__) . '/Alipay.php');

class AlipayForWap extends Alipay {

    public function __construct(){

        require_once (dirname(__FILE__).'/../Config/AlipayConfig.php');
        $this->config = $wapConfig;
        $this->method = 'alipay.trade.wap.pay';

        parent::__construct();
    }


}