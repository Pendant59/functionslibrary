<?php
declare(strict_types=1);
namespace functionsLibrary;

use functionsLibrary\Exception\CurlConfigException;
use functionsLibrary\Exception\CurlParamsException;

/**
 * Class CurlsLibrary
 * @package functionsLibrary
 */
class CurlsLibrary
{
    # 默认请求头 Default header
    protected static $header_default = [
        'accept'            => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'content-type'      => 'application/x-www-form-urlencoded',
    ];

    # Json请求头 Json header
    protected static $header_json_default = [
        'content-type'  => 'application/json;charset=utf-8',
    ];

    # POST upload file 请求头  
    protected static $header_file_default = [
        'content-type: multipart/form-data;charset=utf-8',
    ];

    # 默认cURL设置 Default config
    protected static $default_config = [
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_SSL_VERIFYHOST' => false,
        'CURLOPT_FOLLOWLOCATION' => 1,
        'CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_HEADER'         => false,
        'CURLOPT_CONNECTTIMEOUT' => 5,
        'CURLOPT_TIMEOUT'        => 7,
        'CURLOPT_USERAGENT'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
    ];

    /**
     * 发送POST,PUT,DELETE,PATCH 请求
     * @param string $type
     * @param string $url
     * @param array $data
     * @param bool $json
     * @param array $header
     * @param array $self_config
     * @return array
     */
    public static function restfulRequest(string $type, string $url, array $data, bool $json, array $header = [], array $self_config = [])
    {
        $type = strtoupper($type);
        if (!in_array($type, ['PUT', 'DELETE', 'PATCH', 'POST'])) {
            throw CurlParamsException::throwError($type);
        }
        $others = [];
        array_push($others, self::initHeader($header, $json));
        array_push($others, self::initConfig($self_config));
        $ch = self::initNotGetCurl($type, $url, $data, $json, $others);
        return self::sendRequest($ch);
    }

    /**
     * Curl post uploadFiles
     * @param string $url               带协议的请求地址
     * @param array $data               参数数组
     * @param array $header             自定义header
     * @param array $self_config        自定义cURL设置
     * @return array
     */
    public static function postUploadFiles(string $url, array $data, array $header = [], array $self_config = [])
    {
        $others = [];
        array_push($others, self::initHeader($header, false, true));
        array_push($others, self::initConfig($self_config));
        $ch = self::initNotGetCurl('POST', $url, $data, false, $others);
        return self::sendRequest($ch);
    }

    /**
     * Curl multi post
     * POST 并发curl请求
     * @param array $urls               带协议的请求地址
     * @param array $data               参数数组
     * @param array $header             自定义header
     * @param array $self_config        自定义cURL设置
     * @return array
     */
    public static function postMultiRequests(array $urls, array $data, array $header = [], array $self_config = [])
    {
        if (strpos(strtolower(php_uname('s')),'wind') !== false) {
            throw CurlConfigException::throwError('Windows System');
        }
        # 创建批处理cURL句柄
        $mh = curl_multi_init();
        # cURL资源数组
        $url_handlers = [];
        # 返回值
        $url_data = [];
        # 是否循环 header 默认false 即通用header;
        $loop_header = false;

        if (!empty($header)){
            if (count($header) !== count($header, 1) ) {
                throw CurlConfigException::throwError('header');
            } else {
                if (count($header) !== count($urls)) {
                    throw CurlConfigException::throwError('header');
                }
                array_walk($header, function ($v, $k) {
                    if (!is_array($v)) {
                        throw CurlConfigException::throwError('header');
                    }
                });
                $loop_header = true;
            }
        }

        foreach($urls as $key => $url) {
            $others = [];
            array_push($others, initConfig($self_config));
            if ($loop_header){
                array_push($others, self::initHeader($header[$key]));
                $ch = self::initNotGetCurl( 'post', $url, $data[$key]['data'], $data[$key]['json'], $others);
            } else {
                # 通用header 一维关联数组
                array_push($others, self::initHeader($header));
                $ch = self::initNotGetCurl( 'post', $url, $data[$key]['data'], $data[$key]['json'], $others);
            }
            # 存入资源组
            $url_handlers[] = $ch;
            # 增加cURL句柄
            if (!is_resource($ch)) {
                return self::apiReturn(400, '非资源类型');
            }
            curl_multi_add_handle($mh, $ch);
        }

        # 活跃的连接数量
        $active = null;
        do {
            # curl_multi_exec 处理在栈中的每一个句柄。无论该句柄需要读取或写入数据都可调用此方法。
            # $mrc是返回值，正常为CURLM_OK(0)表示已经全部处理完成，CURLM_CALL_MULTI_PERFORM(-1)表示还有未处理的
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        # 获取结果
        foreach($url_handlers as $key => $ch) {
            $url_data[$key]['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url_data[$key]['result'] = curl_multi_getcontent($ch);

            # 删除处理完的cURL句柄
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);
        return self::apiReturn(200, null, $url_data);
    }

    /**
     * Curl Post
     * POST 单次curl请求
     * @param string $url                   带协议的请求地址
     * @param array $data                   参数数组
     * @param bool $json                    是否将参数转为json格式传输
     * @param array $header                 自定义header
     * @param array $self_config            自定义cURL设置
     * @return array|false|resource
     */
    public static function postRequest(string $url, array $data, bool $json = false, array $header = [], array $self_config = [])
    {
        $others = [];
        array_push( $others, self::initHeader($header, $json));
        array_push( $others, self::initConfig($self_config));
        $ch = self::initNotGetCurl('POST', $url, $data, $json, $others);
        return self::sendRequest($ch);
    }

    /**
     * 初始化非GET请求cURL句柄
     * @param string $method
     * @param string $url
     * @param array $data
     * @param bool $json
     * @param array $others
     * @return false|resource
     */
    protected static function initNotGetCurl(string $method, string $url, array $data, bool $json, array $others)
    {
        list($header, $config) = $others;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if ($json) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data,  JSON_UNESCAPED_UNICODE));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['CURLOPT_TIMEOUT']);
        return $ch;
    }

    /**
     * Curl_multi Get
     * GET 并发curl请求
     * @param array $urls                   带协议的请求地址
     * @param array $header                 自定义header
     * @param array $self_config            自定义cURL设置
     * @return array
     */
    public static function getMultiRequests(array $urls, array $header = [], array $self_config = [])
    {
        if (strpos(strtolower(php_uname('s')),'wind') !== false) {
            throw CurlConfigException::throwError('Windows System');
        }
        # 创建批处理cURL句柄
        $mh = curl_multi_init();
        # cURL资源数组
        $url_handlers = [];
        # 返回值
        $url_data = [];
        # 是否循环 header 默认false 即通用header;
        $loop_header = false;

        if (!empty($header)){
            if (count($header) !== count($header, 1) ) {
                throw CurlConfigException::throwError('header');
            } else {
                if (count($header) !== count($urls)) {
                    throw CurlConfigException::throwError('header');
                }
                array_walk($header, function ($v, $k) {
                    if (!is_array($v)) {
                        throw CurlConfigException::throwError('header');
                    }
                });
                $loop_header = true;
            }
        }

        # 创建cURL资源
        foreach($urls as $key => $url) {
            if ($loop_header){
                $self_header = self::initHeader($header[$key]);
                $config = self::initConfig($self_config);
                $ch = self::initGetCurl($url, $self_header, $config);
            } else {
                $self_header = self::initHeader($header);
                $config = self::initConfig($self_config);
                $ch = self::initGetCurl($url, $self_header, $config);
            }
            # 存入资源组
            $url_handlers[] = $ch;
            # 增加cURL句柄
            if (!is_resource($ch)) {
                return self::apiReturn(400, '非资源类型');
            }
            curl_multi_add_handle($mh, $ch);
        }
        # 活跃的连接数量
        $active = null;
        do {
            # curl_multi_exec 处理在栈中的每一个句柄。无论该句柄需要读取或写入数据都可调用此方法, 用于发起请求。
            # $mrc是返回值，正常为CURLM_OK(0)表示已经全部处理完成，CURLM_CALL_MULTI_PERFORM(-1)表示还有未处理的
            # curl_multi_exec 会并发请求$mh中包含的句柄，并将$active 改成 活跃的连接数量，完成一个就减少1，最终会减为0;
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        # 此处在 windows 下并发请求会造成死循环
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh, 5) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        # 获取结果
        foreach($url_handlers as $key => $ch) {
            $url_data[$key]['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url_data[$key]['result'] = curl_multi_getcontent($ch);
            # 删除处理完的cURL句柄
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        return self::apiReturn(200, null, $url_data);
    }

    /**
     * Curl Get
     * GET 单次curl请求
     * @param string $url                 带协议的请求地址
     * @param array $header               自定义header
     * @param array $self_config          自定义cURL设置
     * @return array|false|resource
     */
    public static function getRequest(string $url, array $header = [], array $self_config = [])
    {
        $self_header = self::initHeader($header);
        $config = self::initConfig($self_config);
        $ch = self::initGetCurl($url, $self_header, $config);
        return self::sendRequest($ch);
    }

    /**
     * 初始化GET请求cURL句柄
     * @param string $url
     * @param array $header
     * @param array $config
     * @return false|resource
     */
    protected static function initGetCurl(string $url, array $header, array $config)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['CURLOPT_TIMEOUT']);
        return $ch;
    }

    /**
     * 合并header
     * @param array $header
     * @param bool $json
     * @param bool $file
     * @return array
     */
    public static function initHeader(array $header, bool $json = false, $file = false)
    {
        if (!empty($header)) {
            # 一维数组
            if (count($header) !== count($header, 1)){
                throw CurlConfigException::throwError('header');
            }
            $default = $json ? self::$header_json_default : ($file ? self::$header_file_default : self::$header_default);
            # key转小写
            $header = array_change_key_case($header, CASE_LOWER);
            $new_header = array_merge($default, $header);
        } else {
            $new_header = $json ? self::$header_json_default : ($file ? self::$header_file_default : self::$header_default);
        }
        foreach ($new_header as $key => $value) {
            $new_header[$key] = $key . ': ' . $value;
        }
        return array_values($new_header);
    }

    /**
     * 合并配置参数
     * @param $config
     * @return array
     */
    public static function initConfig($config)
    {
        if (!empty($config)) {
            $new_config = array_merge(self::$default_config, $config);
        } else {
            $new_config = self::$default_config;
        }
        return $new_config;
    }

    /**
     * 发送单次请求
     * @param $ch
     * @return array
     */
    protected static function sendRequest($ch)
    {
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $code = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);
            return self::apiReturn($code, $message);
        }else{
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return self::apiReturn($code, null, $result);
        }
    }

    /**
     * 返回
     * @param int $code             HTTP 状态码
     * @param string $message       提示信息
     * @param array $data           返回数据
     * @return array
     */
    protected static function apiReturn(int $code, $message = null, $data = null):array
    {
        $return = [
            'code' => $code,
            'message'  => $message ?: ($code == 200 ? 'Success' : 'Error'),
        ];
        if (!empty($data)){
            $return['data'] = $data;
        }
        return $return;
    }

}