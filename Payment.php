<?php
// Payment.php - 易支付（支持动态配置）

class Payment {
    /**
     * 创建订单（生成支付链接）
     */
    public static function createOrder($orderNo, $amount, $name, $type = 'alipay') {
        $config = getEpayConfig();
        
        $params = [
            'pid'         => $config['pid'],
            'type'        => $type,
            'out_trade_no'=> $orderNo,
            'notify_url'  => $config['notify_url'],
            'return_url'  => $config['return_url'],
            'name'        => $name,
            'money'       => $amount,
            'sign_type'   => 'MD5'
        ];

        ksort($params);
        $signStr = http_build_query($params) . $config['key'];
        $params['sign'] = md5($signStr);

        return $config['api_url'] . '?' . http_build_query($params);
    }

    /**
     * 验证异步回调（notify）
     */
    public static function verifyCallback($data) {
        $config = getEpayConfig();
        
        if (!isset($data['sign']) || !isset($data['out_trade_no']) || !isset($data['trade_status'])) {
            return false;
        }
        if ($data['trade_status'] != 'TRADE_SUCCESS') {
            return false;
        }

        $sign = $data['sign'];
        unset($data['sign']);

        ksort($data);
        $signStr = http_build_query($data) . $config['key'];
        $calcSign = md5($signStr);

        return $calcSign === $sign;
    }
}