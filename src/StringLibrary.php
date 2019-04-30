<?php
declare(strict_types=1);
namespace functionsLibrary;

class StringLibrary
{
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