<?php
namespace functionsLibrary;


class CurlsLibrary
{
    /**
     * Curl Post
     * @param string $url
     * @param array $data
     * @param bool $json
     * @param array $header
     * @param array $others
     * @return array
     */
    public function postSingleCurl(string $url, array $data, bool $json = false, array $header = [], array $others = [])
    {
        if ($json){
            $header_default = [
                 'Accept: application/json',
                 'Content-Type: application/json;charset=utf-8',
            ];
        } else {
            $header_default = [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Content-Type: application/x-www-form-urlencoded',
            ];
        }

        if (is_array($header) && !empty($header)) {
            $header_default = array_merge($header_default, $header);
        }

        $default_config = [
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_FOLLOWLOCATION' => 1,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_HEADER'         => false,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_TIMEOUT'        => 10,
            'CURLOPT_USERAGENT'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
        ];
        if (is_array($others) && !empty($others)) {
            $default_config = array_merge($default_config, $others);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if ($json){
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $default_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $default_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $default_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $default_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $default_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_default);
        curl_setopt($ch, CURLOPT_HEADER, $default_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $default_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $default_config['CURLOPT_TIMEOUT']);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $code = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);
            return $this->api_return($code, $message);
        }else{
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $this->api_return($code, null, $result);
        }
    }

    /**
     * Curl Get
     * @param string $url
     * @param array $header
     * @param array $others
     * @return array
     */
    public function getSingleCurl(string $url, array $header = [], array $others = [])
    {
        $header_default = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded',
        ];

        if (is_array($header) && !empty($header)) {
            $header_default = array_merge($header_default, $header);
        }

        $default_config = [
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_FOLLOWLOCATION' => 1,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_HEADER'         => false,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_TIMEOUT'        => 10,
            'CURLOPT_USERAGENT'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
        ];

        if (is_array($others) && !empty($others)) {
            $default_config = array_merge($default_config, $others);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $default_config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $default_config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $default_config['CURLOPT_FOLLOWLOCATION']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $default_config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_USERAGENT, $default_config['CURLOPT_USERAGENT']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_default);
        curl_setopt($ch, CURLOPT_HEADER, $default_config['CURLOPT_HEADER']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $default_config['CURLOPT_CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $default_config['CURLOPT_TIMEOUT']);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $code = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);
            return $this->api_return($code, $message);
        }else{
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $this->api_return($code, null, $result);
        }
    }


    /**
     * 返回
     * @param int $code             HTTP 状态码
     * @param string $message       提示信息
     * @param array $data           返回数据
     * @return array
     */
    public function api_return(int $code, $message = null, $data = null):array
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