# 说明

## 添加redis配置
```php
$redisConf = [
	'host' => '127.0.0.1',
	'port' => 16379,
	'auth' => ''
];
Limit::setRedisConf($redisConf);
```

## 配置限流规则
```php
Limit::getInstance()->addItem('default')->setMax('1r/1s');
```
addItem() 配置的是限流的key

setMax()  配置的是限流规则，类似nginx限流规则
- 1r/1s 表示每秒限一次
- 1r/1m 每分钟一次
- 1r/1h 每小时一次
- 1r/1d 每天一次

## 3.实际项目中检查是否符合限流
```php
if (Limit::isAllow('default')){
    echo '成功',PHP_EOL;
}else{
    echo '失败',PHP_EOL;
}
```


# 完整demo
```php
require_once __DIR__.'/vendor/autoload.php';
use Carlo\Limiter\Limit;
$redisConf = [
	'host' => '127.0.0.1',
	'port' => 16379,
	'auth' => ''
];
Limit::setRedisConf($redisConf);
Limit::getInstance()->addItem('default')->setMax('1r/1s');
for ($i=0;$i<100;$i++){
	usleep(500000);
	echo '['.date('Y-m-d H:i:s').']';
	if (Limit::isAllow('default')){
		echo '成功',PHP_EOL;
	}else{
		echo '失败',PHP_EOL;
	}
}
```