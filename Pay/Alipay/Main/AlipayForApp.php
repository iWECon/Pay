<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 16:40
 */

require(dirname(__FILE__) . '/Alipay.php');

class AlipayForApp extends Alipay {

    public function __construct(){

        require_once (dirname(__FILE__).'/../Config/AlipayConfig.php');
        $this->config = $appConfig;
        $this->method = 'alipay.trade.app.pay';

        parent::__construct();
    }



}