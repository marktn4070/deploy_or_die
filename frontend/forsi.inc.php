<?php
class Company {
	public $client_id;
	public $refresh_token;
	public $name;
	public $email;
	function __construct($client_id,$refresh_token,$name = '',$email = []) {
		$this->client_id = (int) $client_id;
		$this->refresh_token = $refresh_token;
		$this->name = $name;
		$this->email = $email;
	}
}

function forsicall($company,$function_name,$function_data = null,$function_type = 'json') {
	static $tokencache;
	
	if(empty($tokencache[$company->client_id])) {
		$data = ['applicationKey' => API_KEY,'refreshToken' => $company->refresh_token];
		$return = restcall('https://forsi.dk/external-rest-api/v2/authentication',$data);
		$tokencache[$company->client_id] = json_decode($return);
	}
	else {
		$expirationDate = new DateTime($tokencache[$company->client_id]->expirationDate);
		if($expirationDate < (new DateTime('10 seconds'))) {
			$data = ['applicationKey' => API_KEY,'refreshToken' => $company->refresh_token];
			$return = restcall('https://forsi.dk/external-rest-api/v2/authentication',$data);
			$tokencache[$company->client_id] = json_decode($return);
		}
	}
	$apitoken = $tokencache[$company->client_id]->apiToken;
	try {
		$return = restcall('https://forsi.dk/external-rest-api/v2'.$function_name,$function_data,$apitoken,$function_type);
	}
	catch(\Exception $e) {
		if($e->getCode()!=401) {
			throw new \Exception($e->getMessage(),$e->getCode());
		}
		else {
			throw new \Exception("401 on apiToken !!! ".$e->getMessage(),500);
		}
	}
	return $return;
}

function restcall($url,$data = null,$apitoken = null,$type = 'json') {
	$ch = curl_init($url);
	$header = [];
	if($type=='json') {
		$header[] = 'Content-Type:application/json';
	}
	if($apitoken) {
		$header[] = 'Authorization: Bearer '.$apitoken;
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if($data) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $type=='json' ? json_encode($data) : $data);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$return = curl_exec($ch);
	
	if(curl_errno($ch)) {
		throw new \Exception(curl_error($ch),500);
	}
	
	$info = curl_getinfo($ch);
	if($info['http_code']!==200) {
		throw new \Exception('curl http_code: '.$info['http_code'],$info['http_code']);
	}
	
	curl_close($ch);
	return $return;
}
