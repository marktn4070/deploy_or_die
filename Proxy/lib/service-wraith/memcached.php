<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraith extends ServiceWraithCore {
	private static string $host, $key;
	private static int $port, $backoff = 300;
	private static $function;
	private static $memcached;

	public static function memcached(callable $function,string $key,string $host = 'localhost', int $port = 11211) {
		self::$function = $function;
		self::$key = $key;
		self::$host = $host;
		self::$port = $port;

		self::construct();
	}

	private static function open(): bool {
		self::log(LOG_INFO,'Connecting');
		self::$memcached = new Memcached();
		return self::$memcached->addServer(self::$host, self::$port);
	}

	private static function close(): void {
		self::log(LOG_INFO,'Closing');
		self::$memcached->quit();
	}

	public static function run(?string $directory = null): void {
		self::initial($directory ?? __DIR__);

		$connected = self::open();
		if($connected!==true) {
			self::log(LOG_ERR,'No connection');
			return;
		}

		while(self::$run) {
			if(!$connected) {
				$connected = self::open();
			}

			$queue = self::$memcached->get(self::$key);
			if(self::$memcached->getResultCode()===Memcached::RES_NOTFOUND) {
				self::log(LOG_NOTICE,'Initialize');
				self::$memcached->set(self::$key,0);
				$queue = 0;
			}
			elseif(!$connected || self::$memcached->getResultCode()!==Memcached::RES_SUCCESS) {
				self::log(LOG_ERR,self::$memcached->getResultMessage());
				self::sleep(self::$backoff);
				$connected = false;
				continue;
			}
			if($queue) {
				self::log(LOG_NOTICE,'Trigger '.$queue);
				$continue = call_user_func(self::$function) ?? true;
				if($continue===false) {
					self::terminate();
				};

				self::$memcached->decrement(self::$key,$queue);
			}

			self::finally();
			usleep(50000);
		}

		self::close();
		return;
	}
}
