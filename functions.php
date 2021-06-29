<?php

if (!function_exists('str2arr')) {
    /**
     * 字符串转数组
     * @param string $text 待转内容
     * @param string $separ 分隔字符
     * @param null|array $allow 限定规则
     * @return array
     */
    function str2arr(string $text, string $separ = ',', ?array $allow = null): array
    {
        $text = trim($text, $separ);
        $data = strlen($text) ? explode($separ, $text) : [];
        if (is_array($allow)) foreach ($data as $key => $item) {
            if (!in_array($item, $allow)) unset($data[$key]);
        }
        foreach ($data as $key => $item) {
            if ($item === '') unset($data[$key]);
        }
        return $data;
    }
}

if (!function_exists('show_goods_spec')) {
    /**
     * 商品规格过滤显示
     * @param string $spec 原规格内容
     * @return string
     */
    function show_goods_spec(string $spec): string
    {
        $specs = [];
        foreach (explode(';;', $spec) as $sp) {
            $specs[] = explode('::', $sp)[1];
        }
        return join(' ', $specs);
    }
}

if (!function_exists('get_random_order')) {
    /**
     * 获取订单号
     * @param int $length 长度
     * @return string
     */
    function get_random_order($length = 6)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        $order = time() . mt_rand($min, $max);
        return $order;
    }
}

if (!function_exists('getIP')) {
    /**
     * 获取ip地址
     * @return string
     */
    function getIP()
    {
        global $ip;

        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknow";

        return $ip;
    }
}
