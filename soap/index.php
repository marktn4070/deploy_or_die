<?php
declare(strict_types=1);

class FakeWorkorderService
{

	// public function workorderGet($param): object
	// {
	// 	return (object)[
	// 		'plate' => 'AB12345',
	// 		'brand' => 'Toyota',
	// 		'jobprovider_key' => '01',
	// 		'files' => [
	// 			(object)[
	// 				'file_id' => 1,
	// 				'name' => 'sample.jpg',
	// 				'mimetype' => 'image/jpeg',
	// 				'data' => base64_encode(file_get_contents(__DIR__ . '/sample.jpg')),
	// 			],
	// 		],
	// 	];
	// }


public function workorderGet($param): object
{
    return (object)[
        'plate' => 'AB12345',
        'brand' => 'Toyota',
        'jobprovider_key' => '01',
        'files' => [
            (object)['file_id' => 1],
        ],
    ];
}

public function workorderFileGet($param): object
{
    $content = file_get_contents(__DIR__ . '/sample.jpg');
    if ($content === false) {
        throw new \Exception('Could not read sample.jpg');
    }

    return (object)[
        'file_id' => (int)($param->file_id ?? $param['file_id'] ?? 1),
        'name' => 'sample.jpg',
        'mimetype' => 'image/jpeg',
        'data' => base64_encode($content),
    ];
}



    public function workorderAction($param): object
    {
        return (object)[
            'success' => true,
            'message' => 'Action received',
        ];
    }
}

$server = new SoapServer(__DIR__ . '/service.wsdl', [
    'cache_wsdl' => WSDL_CACHE_NONE,
]);
$server->setClass(FakeWorkorderService::class);
$server->handle();