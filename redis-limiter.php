<?php

$redis = new Redis();
try{
	$lua_limiter = <<<LUA
local key = KEYS[1] -- 限流key
local limit = tonumber(ARGV[1] or "1") -- 限流大小
local expire_time = tonumber(ARGV[2] or "1") -- 限流频率
local current = tonumber(redis.call('get',key) or "0")
if current + 1 > limit then
	return 0
else
	redis.call("INCRBY",key,"1")
	redis.call("expire",key,expire_time)
	return 1
end
LUA;
	$redis->connect('127.0.0.1','9101');
	$redis->auth('redis_password');
	$key = 'my_key';
	$limit = 2;
	$expire_time = 1;
	$is_pass = $redis->eval($lua_limiter,[$key,$limit,$expire_time],1);
	var_dump('is pass: '.$is_pass);
}catch (Exception $e){
	var_dump('[error]',$e->getMessage());
}