<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

try {
    $client = new SoapClient(__DIR__ . '/soap/service.wsdl', [
        'trace' => 1,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
    ]);

    $result = $client->workorderGet([
        'workorder_id' => 123
    ]);

    echo "SOAP works\n\n";
    print_r($result);

} catch (Throwable $e) {
    echo "SOAP fejl:\n";
    echo $e->getMessage() . "\n\n";

    if (isset($client)) {
        echo "Last request:\n";
        echo $client->__getLastRequest() . "\n\n";

        echo "Last response:\n";
        echo $client->__getLastResponse() . "\n";
    }
}