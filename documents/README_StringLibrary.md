## StringLibrary(字符串处理)
- Pendant <pendant59@qq.com>

- 方法
    - getRandomString     - 随机字符串
    - mbSubstr            - 字符串截取(可截取中文字符串)
    
- 参数
```
/**
 * 随机字符串
 * @param int $length           长度
 * @param int $type             强度类型 1:[0-9], 2:[A-Za-z0-9] others:[A-Za-z0-9!@%-_=+]
 * @param string $prefix        前缀
 * @return string
 */
public static function getRandomString(int $length, int $type = 1, string $prefix = ''){}

/**
 * 字符串截取
 * @param string $str           待截取字符串
 * @param int $begin            起始位置
 * @param int $length           截取长度
 * @param bool $suffix          是否拼接省略号 默认否
 * @param string $charset       待截取的字符串的编码 默认 utf-8  仅支持'utf-8', 'gb2312', 'gbk', 'big5'
 * @return false|string
 */
public static function mbSubstr(string $str, int $begin, int $length, bool $suffix = false, string $charset = "utf-8") {}
```