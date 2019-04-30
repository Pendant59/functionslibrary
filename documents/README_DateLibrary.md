## DateLibrary(时间处理 - 注意PHP时区设置)
- Pendant <pendant59@qq.com>

- 方法
    - getFormatDays     - 获取前N天 / 后N天 的格式化日期(包含对应零点时间戳)
    - getUnixFormat     - 将unix时间戳格式化为指定格式
    - getFormatDays     - 获得当前日期前/后 N 天的格式化日期(包含对应时间戳)
    - getCalendar       - 获取指定年月的日历
    - getWeekStartEnd   - 获取本周的开始日期和结束日期(可包括前后一周)
    
- 参数
```
/**
 * 获取前N天 / 后N天 的格式化日期(包含对应零点时间戳)
 * @param bool $pre         默认true 过去的日期，false 将来的日期
 * @param string $format    默认 'Y-m-d' 日期格式化规则
 * @param int $days         天数
 * @return array            [1 => ['week'=>0, 'format'=>'2019-4-29', 'timestamp'=> 1556467200], 2 => ['week'=>1, ...]]
 */
public static function getFormatDays(bool $pre = true, string $format = 'Y-m-d', int $days = 7) {}

/**
 * 将unix时间戳格式化为指定格式
 * @param int $timestamp        unix时间戳
 * @param string $format        格式
 * @return string               格式化后的时间戳 或 减号
 */
public static function getUnixFormat($timestamp, string $format = 'Y-m-d H:i:s') {}

/**
 * 获得当前日期前/后 第 N 天 的格式化日期(包含对应时间戳)
 * @param int $days             天数
 * @param bool $pre             默认 false 未来第$days天的当天格式化日期, true 过去第$days天的当天格式化日期
 * @param string $format        默认 'Y-m-d H:i:s' 日期格式化规则
 * @return array                ['format' => '2017-4-30 15:00:00', 'timestamp' => 1556607600]
 */
public static function getFormatDay(int $days = 1, bool $pre = false, string $format = 'Y-m-d H:i:s'){}

/**
 * 获取指定年月的日历
 * @param int $year         默认当前年份
 * @param int $month        默认当前月份
 * @return array
 */
public static function getCalendar(int $year = 0, int $month = 0) {}

/**
 * 获取本周的开始日期以及结束日期
 * @param string $formate           默认 'Y-m-d H:i:s' 日期格式化规则
 * @param int $type                 默认0 类型 0 仅本周,1 包含上周, 2 包含下周 3 包含前后一周
 * @param int $start_days           默认1 表示每周星期一为开始日期 0 表示每周日为开始日期
 * @return array
 */
public static function getWeekStartEnd(string $formate = 'Y-m-d', int $type = 0, int $start_days = 1){}
```