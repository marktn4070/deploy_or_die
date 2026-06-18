<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$client = new SoapClient(__DIR__ . '/soap/service.wsdl', [
    'trace' => 1,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
]);

$workorder = $client->workorderGet([
    'workorder_id' => 123
]);

echo "WORKORDER RESULT\n";
print_r($workorder);

// var_dump($client->__soapCall(
//     'workorderGet',
//     [['workorder_id' => 123]]
// ));
// echo "\nREQUEST\n";
// echo $client->__getLastRequest();

// echo "\n\nRESPONSE\n";
// echo $client->__getLastResponse();


$fileId = $workorder->files->file_id ?? ($workorder->files[0]->file_id ?? 1);

$file = $client->workorderFileGet([
    'file_id' => $fileId
]);

echo "\n\nFILE RESULT\n";
print_r($file);


// var_dump($client->__soapCall(
//     'workorderFileGet',
//     [['file_id' => 1]]
// ));
// echo "\nREQUEST\n";
// echo $client->__getLastRequest();

// echo "\n\nRESPONSE\n";
// echo $client->__getLastResponse();
