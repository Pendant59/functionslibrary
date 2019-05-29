<?php
declare(strict_types=1);
namespace functionsLibrary;

/**
 * Class StringLibrary
 * @package functionsLibrary
 */
class StringLibrary
{
     /**
     * 校验身份证合法性
     * @param string $string           18位身份证
     * @return bool
     */
    public function checkIdCard(string $string) 
    {
        $string = strtoupper($string);
        $regx = "/(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $string))
        {
            return false;
        }

        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        preg_match($regx, $string, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
        if(!strtotime($dtm_birth)) { //检查生日日期是否正确
            return false;
        }
        //检验18位身份证的校验码是否正确。
        //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
        $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $sign = 0;
        for ( $i = 0; $i < 17; $i++ ) {
            $b = (int) $string{$i};
            $w = $arr_int[$i];
            $sign += $b * $w;
        }
        $n = $sign % 11;
        $val_num = $arr_ch[$n];
        if ($val_num != substr($string,-1)) {
            return false;
        }
        return true;
    }

    /**
     * 随机字符串
     * @param int $length           长度
     * @param int $type             强度类型 1:[0-9], 2:[A-Za-z0-9] others:[A-Za-z0-9!@%-_=+]
     * @param string $prefix        前缀
     * @return string
     */
    public static function getRandomString(int $length, int $type = 1, string $prefix = '')
    {
        switch ($type) {
            case 1;
                $chars = str_shuffle('0123456789');
                break;
            case 2;
                $chars = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxy123456789');
                break;
            default:
                $chars = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxy123456789!@%-_=+');
                break;
        }
        $length = $length - strlen($prefix);
        $max_length = strlen($chars) - 1;
        $string = $prefix;
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, mt_rand(0, $max_length), 1);
        }
        return $string;
    }

    /**
     * 字符串截取
     * @param string $str           待截取字符串
     * @param int $begin            起始位置
     * @param int $length           截取长度
     * @param bool $suffix          是否拼接省略号 默认否
     * @param string $charset       待截取的字符串的编码 默认 utf-8  仅支持'utf-8', 'gb2312', 'gbk', 'big5'
     * @return false|string
     */
    public static function mbSubstr(string $str, int $begin, int $length, bool $suffix = false, string $charset = "utf-8") {
        if (!in_array($charset, ['utf-8', 'gb2312', 'gbk', 'big5'])) {
            return $str;
        }
        if (function_exists("mb_substr"))
            $slice = mb_substr($str, $begin, $length, $charset);
        elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $begin, $length, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $begin, $length));
        }
        return $suffix ? $slice . '...' : $slice;
    }
}