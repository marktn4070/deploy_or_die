<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraith extends ServiceWraithCore {
	private static string $mailbox, $user, $password;
	private static int $flags, $sleep, $backoff = 300;
	private static $function;
	public static $imap;

	public static function mail(callable $function,string $mailbox,string $user,string $password,int $flags = 0) {
		self::$function = $function;
		self::$mailbox = $mailbox;
		self::$user = $user;
		self::$password = $password;
		self::$flags = $flags;

		self::$sleep = 5;
		self::construct();
	}

	private static function open(): void {
		self::log(LOG_INFO,'Connecting');
		self::$imap = @imap_open(self::$mailbox, self::$user, self::$password);
		self::errors();
	}

	private static function close(): void {
		self::log(LOG_INFO,'Closing');
		imap_close(self::$imap);
	}

	private static function errors(): void {
		if($imap_errors = imap_errors()) {
			foreach($imap_errors as $error) self::log(LOG_ERR,'MailError: '.$error);
		}
	}

	public static function run(?string $directory = null): void {
		self::initial($directory ?? __DIR__);

		self::open();
		if(self::$imap===false || !imap_is_open(self::$imap)) {
			self::log(LOG_ERR,'No connection');
			return;
		}

		while(self::$run) {
			if(self::$imap===false || !imap_ping(self::$imap)) {
				self::log(LOG_ERR,'Ping failed');
				self::sleep(self::$backoff);
				self::open();
			}
			else {
				$num_msg = imap_num_msg(self::$imap);
				if($num_msg) {
					self::log(LOG_NOTICE,'Found '.$num_msg.' messages');
					$continue = call_user_func(self::$function,self::$imap,$num_msg) ?? true;
					imap_expunge(self::$imap);
					self::errors();
					if($continue===false) {
						self::terminate();
					};
				}
				self::finally();
				self::sleep(self::$sleep, false);
			}
		}

		self::close();
		return;
	}
}
