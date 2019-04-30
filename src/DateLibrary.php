<?php
declare(strict_types=1);
namespace functionsLibrary;

class DateLibrary
{
    /**
     * 获取前N天 / 后N天 的格式化日期(包含对应零点时间戳)
     * @param bool $pre         默认true 过去的日期，false 将来的日期
     * @param string $format    默认 'Y-m-d' 日期格式化规则
     * @param int $days         天数
     * @return array            [1 => ['week'=>0, 'format'=>'2019-4-29', 'timestamp'=> 1556467200], 2 => ['week'=>1, ...]]
     */
    public static function getFormatDays(bool $pre = true, string $format = 'Y-m-d', int $days = 7): array
    {
        $date = [];
        for ($i=1; $i<=$days; $i++){
            if ($pre) {
                $res = $i - $days - 1;
            }else{
                $res = $i + $days + 1;
            }
            $timestamp = strtotime( $res . ' day');
            # $i 第几天  $week 周几(周日是 0)
            $week = date('w' , $timestamp);
            $date[$i]['week'] = $week;
            $date[$i]['format'] = date($format , $timestamp);
            $date[$i]['timestamp'] = strtotime($date[$i][$week]['format']);
        }
        return $date;
    }

    /**
     * 将unix时间戳格式化为指定格式
     * @param int $timestamp        unix时间戳
     * @param string $format        格式
     * @return string               格式化后的时间戳 或 减号
     */
    public static function getUnixFormat($timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return $timestamp ? date($format, $timestamp) : '-';
    }

    /**
     * 获得当前日期前/后 第 N 天 的格式化日期
     * @param int $days             天数
     * @param bool $pre             默认 false 未来第$days天的当天格式化日期, true 过去第$days天的当天格式化日期
     * @param string $format        默认 'Y-m-d H:i:s' 日期格式化规则
     * @return array                ['format' => '2017-4-30 15:00:00', 'timestamp' => 1556607600]
     */
    public static function getFormatDay(int $days = 1, bool $pre = false, string $format = 'Y-m-d H:i:s'): array
    {
        $type = $pre ? '-' : '+';
        $timestamp = strtotime("$type $days day");
        $date = date($format, $timestamp);
        $data = [
            'format' => date($format, $timestamp),
            'timestamp' => strtotime($date)
        ];
        return $data;
    }

    /**
     * 获取指定年月的日历
     * @param int $year         默认当前年份
     * @param int $month        默认当前月份
     * @return array
     */
    public static function getCalendar(int $year = 0, int $month = 0): array
    {
        if (!$month || !$year) {
            $last_day  = date('t');
            $month    = date('n');
            $year     = date('Y');
        } else {
            $last_day  = date('t', strtotime("$year-$month-1"));
        }
        $week_arr = [];
        $week     = [];
        for ($j = 1; $j <= $last_day; $j++) {
            $week_day = date('w', strtotime("$year-$month-$j"));
            $week[$week_day] = $j;
            if ($week_day == 6 || $j == $last_day) {
                $week_arr[] = $week;
                $week = [];
            }
        }
        return $week_arr;
    }

    /**
     * 获取本周的开始日期以及结束日期
     * @param string $formate           默认 'Y-m-d H:i:s' 日期格式化规则
     * @param int $type                 默认0 类型 0 仅本周,1 包含上周, 2 包含下周 3 包含前后一周
     * @param int $start_days           默认1 表示每周星期一为开始日期 0 表示每周日为开始日期
     * @return array
     */
    public static function getWeekStartEnd(string $formate = 'Y-m-d', int $type = 0, int $start_days = 1): array
    {
        $date = date('Y-m-d');
        $start_days = 1;
        $w = date('w', strtotime($date));

        $date_arr = [];

        //本周开始日期, 如果 $w 是0，则表示周日，减去 6 天
        $date_arr['this_week']['start'][] = $this_start = date($formate, strtotime("$date -" . ($w ? $w - $start_days : 6) . ' days'));
        $date_arr['this_week']['start'][] = strtotime("$date -" . ($w ? $w - $start_days : 6) . ' days');
        //本周结束日期
        $date_arr['this_week']['end'][] = $this_end = date($formate, strtotime("$this_start +6 days"));
        $date_arr['this_week']['end'][] = strtotime("$this_start +6 days");

        if ($type == 1) {
            //上周开始日期
            $date_arr['pre_week']['start'][] = date($formate, strtotime("$this_start - 7 days"));
            $date_arr['pre_week']['start'][] = strtotime("$this_start - 7 days");
            //上周结束日期
            $date_arr['pre_week']['end'][] = date($formate, strtotime("$this_start - 1 days"));
            $date_arr['pre_week']['end'][] = strtotime("$this_start - 1 days");
        } elseif ($type == 2) {
            //下周开始日期
            $date_arr['next_week']['start'][] = date($formate, strtotime("$this_end + 1  days"));
            $date_arr['next_week']['start'][] = strtotime("$this_end + 1  days");
            //下周结束日期
            $date_arr['next_week']['end'][]  = date($formate, strtotime("$this_end + 7 days"));
            $date_arr['next_week']['end'][]  = strtotime("$this_end + 7 days");
        } elseif ($type == 3) {
            //上周开始日期
            $date_arr['pre_week']['start'][] = date($formate, strtotime("$this_start - 7 days"));
            $date_arr['pre_week']['start'][] = strtotime("$this_start - 7 days");
            //上周结束日期
            $date_arr['pre_week']['end'][] = date($formate, strtotime("$this_start - 1 days"));
            $date_arr['pre_week']['end'][] = strtotime("$this_start - 1 days");
            //下周开始日期
            $date_arr['next_week']['start'][] = date($formate, strtotime("$this_end + 1  days"));
            $date_arr['next_week']['start'][] = strtotime("$this_end + 1  days");
            //下周结束日期
            $date_arr['next_week']['end'][]  = date($formate, strtotime("$this_end + 7 days"));
            $date_arr['next_week']['end'][]  = strtotime("$this_end + 7 days");
        }
        return $date_arr;
    }
}