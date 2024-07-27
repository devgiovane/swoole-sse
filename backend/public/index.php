<?php declare(strict_types=1);


require_once __DIR__ . '/../vendor/autoload.php';


use Swoole\Coroutine;
use Swoole\Http\{Server, Request, Response};

$server = new Server("0.0.0.0", 8000);
$server->set([
    'enable_coroutine' => true,
    'log_level' => SWOOLE_LOG_WARNING,
]);

$server->on("start", function (Server $server) {
    echo "Http server is started at http://{$server->host}:{$server->port}" . PHP_EOL;
});

$clients = [];
$server->on("request", function (Request $request, Response $response) use ($server, &$clients) {
	$info = $request->server['path_info'];
    $path = $request->server['request_uri'];
	$method = $request->server['request_method'];
	// if ($method === 'GET' && $path === '/health') {
	// 	$response->header('Access-Control-Allow-Origin', '*');
	// 	$response->header('Content-Type', 'text/event-stream');
	// 	$response->header('Cache-Control', 'no-cache');
	// 	$response->header('Connection', 'keep-alive');
	// 	$response->header('X-Accel-Buffering', 'no');
	// 	while (true) {
	// 		$data = json_encode([ "time" => (new DateTime())->format('H:i:s') ]);
	// 		$success = $response->write("data: {$data}\n\n");
	// 		if (!$success) {
	// 			$response->end();
	// 		}
	// 		Coroutine::sleep(1);
	// 	}
	// }
	if ($info == '/favicon.ico' || $path == '/favicon.ico') {
        $response->end();
        return;
    }
	if ($method === 'GET' && $path === '/send') {
		$data = json_encode([ "time" => (new DateTime())->format('H:i:s') ]);
		foreach ($clients as $fd => $client) {
			var_dump($client->isWritable());
			$success = $client->write("data: {$data}\n\n");
			var_dump($success);
		}
		$response->header('Content-Type', 'text/html; charset=utf-8');
		$response->end('<h1>Send ok</h1>');
		}
	if ($method === 'GET' && $path === '/clients') {
		$response->header('Content-Type', 'text/html; charset=utf-8');
		$response->end('<h1>You have ' . count($clients) . ' client connecteds. </h1>');	
	}
	if ($method === 'GET' && $path === '/events') {
		$response->header('Access-Control-Allow-Origin', '*');
		$response->header('Content-Type', 'text/event-stream');
		$response->header('Cache-Control', 'no-cache');
		$response->header('Connection', 'keep-alive');
		$response->header('X-Accel-Buffering', 'no');
		$fd = $request->fd;
		$clients[$fd] = $response;
		echo "Client {$fd} connected\n";
		$data = json_encode([ "ping" => "ok" ]);
		$response->write("data: {$data}\n\n");
		while (true) {
			Coroutine::sleep(5);
		}
	}
	$response->end();
});


$server->on("close", function (Server $server, int $fd) use (&$clients) {
	echo "Client {$fd} disconnected\n";
	unset($clients[$fd]);
});

$server->start();
