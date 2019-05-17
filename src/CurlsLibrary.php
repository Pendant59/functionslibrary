<?php
declare(strict_types=1);
namespace functionsLibrary;

/**
 * Class CurlsLibrary
 * @package functionsLibrary
 */
class CurlsLibrary
{
    # 默认请求头 Default header
    protected static $header_default = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Content-Type: application/x-www-form-urlencoded',
    ];

    # Json请求头 Json header
    protected static $header_json_default = [
        'Accept: application/json',
        'Content-Type: application/json;charset=utf-8',
    ];

    # POST upload file 请求头  
    protected static $header_file_default = [
        'Content-Type: multipart/form-data;charset=utf-8',
    ];

    # 默认cURL设置 Default config
    protected static $default_config = [
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_SSL_VERIFYHOST' => false,
        'CURLOPT_FOLLOWLOCATION' => 1,
        'CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_HEADER'         => false,
        'CURLOPT_CONNECTTIMEOUT' => 5,
        'CURLOPT_TIMEOUT'        => 10,
        'CURLOPT_USERAGENT'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
    ];

    /**
     * 发送POST,PUT,DELETE,PATCH 请求
     * @param string $type
     * @param string $url
     * @param array $data
     * @param array $header
     * @param array $self_config
     * @return array
     */
    public static function restfulRequest(string $type, string $url, array $data, array $header = [], array $self_config = [])
    {
        $type = strtoupper($type);
        if (!in_array($type, ['PUT', 'DELETE', 'PATCH', 'POST'])) {
            return self::apiReturn(400, '仅支持 POST,PUT,DELETE,PATCH');
        }

        if (!empty($header)) {
            if (is_array(reset($header))){
                return self::apiReturn(400, 'header 仅支持一维关联数组');
            }
            $self_header = array_merge(self::$header_default, $header);
        } else {
            $self_header = self::$header_default;
        }

        if (!empty($self_config)) {
            $self_config = array_merge(self::$default_config, $self_config);
        } else {
            $self_config = self::$default_config;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $self_header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $self_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $self_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $self_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $self_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $self_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $self_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $self_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $self_config['CURLOPT_TIMEOUT']);

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
    public static function postUploadFiles(string $url, array $data, array $header = [], array $self_config = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);

        if (!empty($header)) {
            if (is_array(reset($header))){
                return self::apiReturn(400, 'header 仅支持一维关联数组');
            }
        }

        $self_header= array_merge(self::$header_file_default, $header);

        if (!empty($self_config)) {
            $self_config = array_merge(self::$default_config, $self_config);
        } else {
            $self_config = self::$default_config;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $self_header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $self_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $self_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $self_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $self_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $self_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $self_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $self_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $self_config['CURLOPT_TIMEOUT']);

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
        # 创建批处理cURL句柄
        $mh = curl_multi_init();
        # cURL资源数组
        $url_handlers = [];
        # 返回值
        $url_data = [];
        # 是否循环 header
        $loop_header = false;

        if (!empty($header)){
            if (count($header) == 1) {
                if (is_array(reset($header))){
                    return self::apiReturn(400, 'header 仅支持一维关联数组(通用header)和二维索引数组(每个请求都有一个header)');
                }
            } else {
                if (count($header) !== count($urls) || !is_array(reset($header))) {
                    return self::apiReturn(400, 'header 仅支持一维关联数组(通用header)和二维索引数组(每个请求都有一个header)');
                }
                $loop_header = true;
            }
        }

        foreach($urls as $key => $url) {
            if ($loop_header){
                $ch = self::postRequest($url, $data[$key]['data'], $data[$key]['json'], $header[$key], $self_config, false);
            } else {
                $ch = self::postRequest($url, $data[$key]['data'], $data[$key]['json'], $header, $self_config, false);
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
     * @param bool $single                  默认true 返回请求结果，false 返回cURL句柄
     * @return array|false|resource
     */
    public static function postRequest(string $url, array $data, bool $json = false, array $header = [], array $self_config = [], bool $single = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);

        if (!empty($header)) {
            if (is_array(reset($header))){
                return self::apiReturn(400, 'header 仅支持一维关联数组');
            }
            if ($json){
                $self_header= array_merge(self::$header_json_default, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data,  JSON_UNESCAPED_UNICODE));
            } else {
                $self_header = array_merge(self::$header_default, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } else {
            if ($json){
                $self_header= self::$header_json_default;
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data,  JSON_UNESCAPED_UNICODE));
            } else {
                $self_header = self::$header_default;
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        if (!empty($self_config)) {
            $self_config = array_merge(self::$default_config, $self_config);
        } else {
            $self_config = self::$default_config;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $self_header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $self_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $self_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $self_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $self_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $self_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $self_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $self_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $self_config['CURLOPT_TIMEOUT']);

        if ($single) {
            return self::sendRequest($ch);
        } else {
            return $ch;
        }
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
        # 创建批处理cURL句柄
        $mh = curl_multi_init();
        # cURL资源数组
        $url_handlers = [];
        # 返回值
        $url_data = [];
        # 是否循环 header
        $loop_header = false;

        if (!empty($header)){
            if (count($header) == 1) {
                if (is_array(reset($header))){
                    return self::apiReturn(400, 'header 仅支持一维关联数组(通用header)和二维索引数组(每个请求都有一个header)');
                }
            } else {
                if (count($header) !== count($urls) || !is_array(reset($header))) {
                    return self::apiReturn(400, 'header 仅支持一维关联数组(通用header)和二维索引数组(每个请求都有一个header)');
                }
                $loop_header = true;
            }
        }

        # 创建cURL资源
        foreach($urls as $key => $url) {
            if ($loop_header){
                $ch = self::getRequest( $url, $header[$key], $self_config, false);
            } else {
                $ch = self::getRequest( $url, $header, $self_config, false);
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
     * Curl Get
     * GET 单次curl请求
     * @param string $url                 带协议的请求地址
     * @param array $header               自定义header
     * @param array $self_config          自定义cURL设置
     * @param bool $single                默认true 返回请求结果，false 返回cURL句柄
     * @return array|false|resource
     */
    public static function getRequest(string $url, array $header = [], array $self_config = [], bool $single = true)
    {
        if (!empty($header)) {
            if (is_array(reset($header))){
                return self::apiReturn(400, 'header 仅支持一维关联数组');
            }
            $self_header = array_merge(self::$header_default, $header);
        } else {
            $self_header = self::$header_default;
        }

        if (!empty($self_config)) {
            $self_config = array_merge(self::$default_config, $self_config);
        } else {
            $self_config = self::$default_config;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $self_header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $self_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $self_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $self_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $self_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $self_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HEADER, $self_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $self_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $self_config['CURLOPT_TIMEOUT']);

        if ($single) {
            return self::sendRequest($ch);
        } else {
            return $ch;
        }
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