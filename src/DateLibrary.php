<?php
declare(strict_types=1);
namespace functionsLibrary;

class DateLibrary
{
    /**
     * 获取前N天 / 后N天 的格式化日期(包含对应时间戳)
     * @param bool $pre
     * @param string $format
     * @param int $days
     * @return array
     */
    public static function getSevenDays(bool $pre = true, string $format = 'Y-m-d', int $days = 7): array
    {
        $date = [];
        for ($i=1; $i<=$days; $i++){
            if ($pre) {
                $res = $i - $days - 1;
            }else{
                $res = $i + $days + 1;
            }
            $timestamp = strtotime( $res . ' day');
            # $i 第几天  $week 周几
            $week = date('w' , $timestamp);
            $date[$i][$week]['format'] = date($format , $timestamp);
            $date[$i][$week]['timestamp'] = strtotime($date[$i][$week]['format']);
        }
        return $date;
    }
    /**
     * 将unix时间戳格式化为指定格式
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function getUnixFormat(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return $timestamp ? date($format, $timestamp) : '-';
    }

    /**
     * 获得当前日期前/后 N 天的格式化日期
     * @param int $days
     * @param bool $pre
     * @param string $format
     * @return false|string
     */
    public static function getFormatDays(int $days = 1, bool $pre = false, string $format = 'Y-m-d H:i:s')
    {
        $type = $pre ? '-' : '+';
        return date($format, strtotime("$type $days day"));
    }

    /**
     * 获取指定年月的日历
     * @param int $year
     * @param int $month
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
     * @param string $formate
     * @param int $type  1 包含上周, 2 包含下周 3 包含前后一周
     * @param int $start_days  1 表示每周星期一为开始日期 0表示每周日为开始日期
     * @return array
     */
    public static function getWeekStartEnd(string $formate = 'Y-m-d', int $type = 0, int $start_days = 1): array
    {
        $date = date('Y-m-d');
        $start_days = 1;
        $w = date('w', strtotime($date));

        $date_arr = [];

        //本周开始日期, 如果 $w 是0，则表示周日，减去 6 天
        $date_arr['this_week']['start'][] = $this_start = date('Y-m-d', strtotime("$date -" . ($w ? $w - $start_days : 6) . ' days'));
        $date_arr['this_week']['start'][] = strtotime("$date -" . ($w ? $w - $start_days : 6) . ' days');
        //本周结束日期
        $date_arr['this_week']['end'][] = $this_end = date('Y-m-d', strtotime("$this_start +6 days"));
        $date_arr['this_week']['end'][] = strtotime("$this_start +6 days");

        if ($type == 1) {
            //上周开始日期
            $date_arr['pre_week']['start'][] = date('Y-m-d', strtotime("$this_start - 7 days"));
            $date_arr['pre_week']['start'][] = strtotime("$this_start - 7 days");
            //上周结束日期
            $date_arr['pre_week']['end'][] = date('Y-m-d', strtotime("$this_start - 1 days"));
            $date_arr['pre_week']['end'][] = strtotime("$this_start - 1 days");
        } elseif ($type == 2) {
            //下周开始日期
            $date_arr['next_week']['start'][] = date('Y-m-d', strtotime("$this_end + 1  days"));
            $date_arr['next_week']['start'][] = strtotime("$this_end + 1  days");
            //下周结束日期
            $date_arr['next_week']['end'][]  = date('Y-m-d', strtotime("$this_end + 7 days"));
            $date_arr['next_week']['end'][]  = strtotime("$this_end + 7 days");
        } elseif ($type == 3) {
            //上周开始日期
            $date_arr['pre_week']['start'][] = date('Y-m-d', strtotime("$this_start - 7 days"));
            $date_arr['pre_week']['start'][] = strtotime("$this_start - 7 days");
            //上周结束日期
            $date_arr['pre_week']['end'][] = date('Y-m-d', strtotime("$this_start - 1 days"));
            $date_arr['pre_week']['end'][] = strtotime("$this_start - 1 days");
            //下周开始日期
            $date_arr['next_week']['start'][] = date('Y-m-d', strtotime("$this_end + 1  days"));
            $date_arr['next_week']['start'][] = strtotime("$this_end + 1  days");
            //下周结束日期
            $date_arr['next_week']['end'][]  = date('Y-m-d', strtotime("$this_end + 7 days"));
            $date_arr['next_week']['end'][]  = strtotime("$this_end + 7 days");
        }
        return $date_arr;
    }
}