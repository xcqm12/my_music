<?php
class Payment {
    // 创建易支付订单，返回支付链接
    public static function createOrder($orderNo, $amount, $name, $type = 'alipay') {
        $params = [
            'pid' => EPAY_PID,
            'type' => $type,
            'out_trade_no' => $orderNo,
            'notify_url' => EPAY_NOTIFY_URL,
            'return_url' => EPAY_RETURN_URL,
            'name' => $name,
            'money' => $amount,
            'sign' => '',
            'sign_type' => 'MD5'
        ];
        ksort($params);
        $signStr = urldecode(http_build_query($params)) . EPAY_KEY;
        $params['sign'] = md5($signStr);
        return EPAY_API_URL . '?' . http_build_query($params);
    }

    // 验证异步通知
    public static function verifyCallback($data) {
        if (!isset($data['sign']) || !isset($data['out_trade_no']) || !isset($data['trade_status']) || $data['trade_status'] != 'TRADE_SUCCESS') {
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        unset($data['sign_type']);
        ksort($data);
        $signStr = urldecode(http_build_query($data)) . EPAY_KEY;
        $calcSign = md5($signStr);
        return $calcSign === $sign;
    }
}