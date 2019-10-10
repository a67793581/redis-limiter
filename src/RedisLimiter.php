<?php
/**
 * Created by PhpStorm.
 * User: Carlo
 * Date: 2019-08-08
 * Time: 12:11
 */

namespace Carlo\Limiter;

use Exception;
use Redis;

class RedisLimiter
{
    private static $lua_limiter = <<<LUA
local key        = KEYS[1] -- 限流key
local limit      = tonumber(ARGV[1] or "1") -- 限流大小
local limit_time = tonumber(ARGV[2] or "1") -- 限流频率
local current    = tonumber(redis.call('get',key) or "0")
if current + 1 > limit then
	return 0
else -- 请求书+1 并设置过期时间
	redis.call("INCRBY",key,"1")
	redis.call("expire",key,limit_time)
	return 1
end
LUA;

    /**
     * redis实例
     *
     * @var Redis
     */
    private static $redis = null;

    # redis实例链接超时时间
    private static $redis_timeout = 3;

    /**
     * 初始化Redis
     *
     * @param array $redis_conf
     *
     * @throws \Exception
     */
    public static function init($redis_conf = [])
    {
        self::check_valid();
        $redis_host = $redis_conf['host'];
        $redis_port = $redis_conf['port'];
        $redis_auth = $redis_conf['auth'];
        unset($redis_conf);
        self::$redis = new Redis();
        try {
            self::$redis->connect($redis_host, $redis_port, self::$redis_timeout);
            if (!empty($redis_auth)) {
                self::$redis->auth($redis_auth);
            }
        }
        catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 检查当前环境是否包含redis扩展
     *
     * @return bool
     * @throws \Exception
     */
    private static function check_valid()
    {
        if (extension_loaded('redis')) {
            return true;
        }
        else {
            throw new Exception('Redis扩展未加载');
        }
    }

    /**
     * 当前是否请求是否允许
     *
     * @param string $limit_id
     * @param int $limit_count
     * @param int $limit_cycle
     *
     * @return mixed
     */
    public static function isAllow($limit_id = '', $limit_count = 1, $limit_cycle = 1)
    {
        return self::$redis->eval(self::$lua_limiter, [$limit_id, $limit_count, $limit_cycle], 1);
    }
}