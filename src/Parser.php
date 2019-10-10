<?php
/**
 * Created by PhpStorm.
 * User: Carlo
 * Date: 2019-08-08
 * Time: 11:40
 */

namespace Carlo\Limiter;

use Exception;

/**
 * Class Parser
 *
 * @package Carlo\Limiter
 * 解析频率
 */
class Parser
{
    # 有效的频率周期单位
    private static $valid_rate_cycles = ['s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400];

    /**
     * @param $rate
     * @return array
     * @throws \Exception
     */
    public static function rate($rate)
    {
        $arr            = explode('/', trim($rate));
        $rate_count     = $arr[0];
        $rate_cycle_num = (int) substr($arr[1], 0, -1);
        $rate_cycle_key = substr($arr[1], -1);

        if (!array_key_exists($rate_cycle_key, self::$valid_rate_cycles)) {
            throw new Exception('限流规则格式错误');
        }
        $rate_count = (int) str_replace('r', '', $rate_count);

        return [
            'limit_count' => $rate_count,
            'limit_cycle' => self::$valid_rate_cycles[$rate_cycle_key] * $rate_cycle_num,
        ];
    }
}