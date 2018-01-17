<?php
/**
 * Created by PhpStorm.
 * User: iww
 * Date: 2017/11/15
 * Time: 10:43
 */


class Payment {
    // 随机生成32位字符串
    protected function make32RandChar() {
        return $this->makeRandChar(32);
    }

    // 随机生成字符串
    // param $length 指定生成位数
    private function makeRandChar($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }

    // 获取当前服务器IP
    public function getClientIP() {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    // 按照ascii排序并转换为参数样式的字符串
    public function sortByAsciiAndConverToParam($data, $useLowerKey = true) {
        foreach ($data as $key => $value) {
            if ($useLowerKey) {
                $key = strtolower($key);
            }
            $param[$key] = $value;
        }
        // 排序
        ksort($param);
        // 转换为字符串
        $str = $this->formatBizQueryParamMap($param, false, $useLowerKey);
        return $str;
    }

    // 将数组转换为uri字符串
    private function formatBizQueryParamMap($paramMap, $urlEncode = false, $useLowerKey = true) {

        $buff = "";
        ksort($paramMap);

        foreach ($paramMap as $k => $v) {
            if($urlEncode) {
                $v = urlencode($v);
            }
            if ($useLowerKey) {
                $k = strtolower($k);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
