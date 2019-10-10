<?php
/**
 * Created by PhpStorm.
 * User: Carlo
 * Date: 2019-08-08
 * Time: 11:40
 */
namespace Carlo\Limiter;
use Exception;
class Limit{
	
	private static $instance = null;
	
	/**
	 * 临时规则存储
	 * @var string
	 */
	private static $tmp_limit_id='';
	/**
	 * 所有限流规则
	 * @var array
	 *
	 */
	private static $rate_rules=[];
	/**
	 * redis链接配置
	 * @var array
	 */
	private static $redis_conf = [];
	private function __construct() { }
	
	public static function getInstance()
	{
		if (self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * 添加限流id
	 * @param string $limit_id
	 *
	 * @return Limit|null
	 */
	public function addItem($limit_id='')
	{
		self::$tmp_limit_id = $limit_id;
		return self::getInstance();
	}
	
	/**
	 * 设置最大流量
	 * @param string $rate
	 *
	 * @return Limit|null
	 */
	public function setMax($rate='1r/1s')
	{
		self::$rate_rules[self::$tmp_limit_id] = $rate;
		self::$tmp_limit_id = '';
		return self::getInstance();
	}
	
	public static function setRedisConf($redisConf = [])
	{
		if (!isset($redisConf['host'])){
			$redisConf['host'] = '127.0.0.1';
		}
		if (!isset($redisConf['port'])){
			$redisConf['port'] = 6379;
		}
		if (!isset($redisConf['auth'])){
			$redisConf['auth'] = '';
		}
		self::$redis_conf = $redisConf;
		unset($redisConf);
		return self::getInstance();
	}
	
	/**
	 * 当前规则是否允许
	 * @param string $limit_id
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function isAllow($limit_id='')
	{
		if (!isset(self::$rate_rules[$limit_id])){
			throw new Exception('未设置限流规则');
		}
		$rate = self::$rate_rules[$limit_id];
		$rate_rule = Parser::rate($rate);
		RedisLimiter::init(self::$redis_conf);
		return RedisLimiter::isAllow($limit_id, $rate_rule['limit_count'], $rate_rule['limit_cycle']);
	}
	
}
