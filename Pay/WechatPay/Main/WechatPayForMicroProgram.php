<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 13:53
 */
	
require_once(dirname(__FILE__).'/WechatPay.php');

class WechatPayForMicroProgram extends WechatPay {

    private $openIDRequestURL = 'https://api.weixin.qq.com/sns/jscode2session?';

    public function __construct() {

        require_once (dirname(__FILE__).'./../Config/WechatPayConfig.php');
        $this->config = $microProgramConfig;
        $this->trade_type = 'JSAPI';

        parent::__construct();
    }

    public function getOpenIDByCode($code) {
        $url = $this->openIDRequestURL . "appid=" . $this->app_id . "&secret=" . $this->config['secret'] . "&js_code=" . $code . "&grant_type=authorization_code";
        $content = file_get_contents($url);
        $data = json_decode($content, true);
        $this->openid = $data['openid'];
    }
}
