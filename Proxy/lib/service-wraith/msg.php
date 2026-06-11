<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraith extends ServiceWraithCore {
	private static int $key, $max_message_size;
	private static $function;

	public static function msg(callable $function,int $key,$max_message_size = 1024) {
		self::$function = $function;
		self::$key = $key;
		self::$max_message_size = $max_message_size;

		self::construct();
	}

	public static function run(?string $directory = null): void {
		self::initial($directory ?? __DIR__);

		$message_queue = msg_get_queue(self::$key);

		while(self::$run) {
			while(self::$run && $msg_qnum = msg_stat_queue($message_queue)['msg_qnum']) {
				for($i=$msg_qnum; $i>0; $i--) {
					self::log(LOG_NOTICE,'Queue: '.$i);

					$result = msg_receive($message_queue, 0, $received_message_type, self::$max_message_size, $message);
					if($result) {
						$continue = call_user_func(self::$function,$message,$received_message_type) ?? true;
						if($continue===false) {
							self::terminate();
							break;
						}
					}
					else {
						self::log(LOG_ERR,'Receive error');
						self::terminate();
						break;
					}
				}
			}

			self::finally();
			usleep(50000);
		}

		return;
	}
}
