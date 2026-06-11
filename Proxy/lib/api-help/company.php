<?php
declare(strict_types=1);

class Company {
	public string $tenant;
	public ?string $token;
	private array $properties = [];
	private ?object $client = null;

	function __construct(object $rs,?array $properties = null) {
		$this->tenant = $rs->tenant;
		$this->token = empty($rs->token) ? null : $rs->token;
		foreach($properties ?? [] as $key => $value) {
			if($value==='json') {
				$this->properties[$key] = empty($rs->{$key}) ? (object) [] : json_decode($rs->{$key});
			}
			elseif($value==='string') {
				$this->properties[$key] = $rs->{$key} ?? '';
			}
			elseif($value==='int') {
				$this->properties[$key] = intval($rs->{$key} ?? 0);
			}
			elseif($value==='float') {
				$this->properties[$key] = floatval($rs->{$key} ?? 0);
			}
		}
	}

	public function __get(string $key) : mixed {
		if(isset($this->properties[$key])) {
			return $this->properties[$key];
		}
		else {
			throw new \Exception('Property not found');
		}
	}
	
	public function soap(string $function,?array $param = null) : object|string {
		if(!$this->client) {
			$this->connect();
		}
		try {
			return $this->client->$function($param);
		}
		catch (\SoapFault $e) {
			syslog(LOG_ERR,$e->faultcode.': '.$e->getMessage().' ('.$this->tenant.')');
			if(((int) $e->faultcode)===405) {
				global $mysqli;

				$sql = "UPDATE `company` SET `active`=0 WHERE `tenant`='$this->tenant'";
				$mysqli->query($sql);
				throw new \Exception('Disabled: '.$this->tenant);
			}
			if(((int) $e->faultcode)===404) {
				throw new \Exception('Ignore not found');
			}
			else {
				if(class_exists('ServiceWraith')) {
					ServiceWraith::sleep(600);
				}
				throw new \Exception('SoapFault');
			}
		}
	}

	public function connect() : void {
		if(empty($this->tenant) || empty($this->token)) {
			throw new \Exception('Missing: tenant/token');
		}

		$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
		if(defined('SOAP_SECURE') && SOAP_SECURE===false) {
			$context = stream_context_create(['ssl' => 
				['verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true],
			]);
			$options['stream_context'] = $context;
		}

		if(!defined('SOAP_SOFTWARE')) {
			define('SOAP_SOFTWARE','API-help');
		}

		if(defined('SOAP_LOCATION')) {
			$options['location'] = SOAP_LOCATION;
		}

		$options['keep_alive'] = false;
		$wsdl = defined('SOAP_WSDL') ? SOAP_WSDL : __DIR__.'/../../service.wsdl';
		$this->client = new SoapClient($wsdl,$options);

		$header = [];
		$headerVar = new SoapVar('<software>'.SOAP_SOFTWARE.'</software>',XSD_ANYXML);
		$header[] = new SoapHeader('http://schemas.xmlsoap.org/soap/envelope/','ignored',$headerVar);
		$headerVar = new SoapVar('<operator>'.SOAP_OPERATOR.'</operator>',XSD_ANYXML);
		$header[] = new SoapHeader('http://schemas.xmlsoap.org/soap/envelope/','ignored',$headerVar);
		$headerVar = new SoapVar('<customer>'.$this->tenant.'</customer>',XSD_ANYXML);
		$header[] = new SoapHeader('http://schemas.xmlsoap.org/soap/envelope/','ignored',$headerVar);
		$headerVar = new SoapVar('<token>'.$this->token.'</token>',XSD_ANYXML);
		$header[] = new SoapHeader('http://schemas.xmlsoap.org/soap/envelope/','ignored',$headerVar);
		$this->client->__setSoapHeaders($header);
	}
}
