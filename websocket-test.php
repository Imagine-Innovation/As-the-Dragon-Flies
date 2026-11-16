<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');

echo "=====================================================\n";
echo "Starting Minimal WebSocket Server\n";
echo "=====================================================\n";
echo "PHP Version   : " . PHP_VERSION . "\n";
echo "PHP SAPI      : " . PHP_SAPI . "\n";
echo "ZTS           : " . (PHP_ZTS ? 'YES' : 'NO') . "\n";
echo "OS            : " . PHP_OS_FAMILY . "\n";
echo "=====================================================\n\n";

/*
set_error_handler(
    function (
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        echo "\n[PHP ERROR]\n";
        echo "Number : {$errno}\n";
        echo "Message: {$errstr}\n";
        echo "File   : {$errfile}\n";
        echo "Line   : {$errline}\n\n";

        return false;
    }
);
*/

set_exception_handler(
    function (Throwable $e): void {
        echo "\n[UNCAUGHT EXCEPTION]\n";
        echo get_class($e) . "\n";
        echo $e->getMessage() . "\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n";
        echo $e->getTraceAsString() . "\n\n";
    }
);

class TestWebSocketServer implements MessageComponentInterface
{
	public function onOpen(ConnectionInterface $conn): void
	{
		file_put_contents(
			'c:/temp/ws-test.log',
			date('Y-m-d H:i:s') .
			' OPEN from ' .
			$conn->remoteAddress .
			PHP_EOL,
			FILE_APPEND
		);

		echo "OPEN\n";
	}

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $message = (string)$msg;

        echo "\n";
        echo "=====================================================\n";
        echo "MESSAGE\n";
        echo "From       : {$from->resourceId}\n";
        echo "Payload    : {$message}\n";
        echo "=====================================================\n";

        $from->send(
            json_encode([
                'type'      => 'echo',
                'timestamp' => date('c'),
                'message'   => $message,
            ], JSON_PRETTY_PRINT)
        );
    }

    public function onClose(ConnectionInterface $conn): void
    {
        echo "\n";
        echo "=====================================================\n";
        echo "CLOSE\n";
        echo "ResourceId : {$conn->resourceId}\n";
        echo "=====================================================\n";
    }

	public function onError(ConnectionInterface $conn, \Exception $e): void
	{
		file_put_contents(
			'c:/temp/ws-test.log',
			date('Y-m-d H:i:s') .
			" ERROR: " .
			$e->getMessage() .
			PHP_EOL,
			FILE_APPEND
		);
	}
}

try {

    echo "[1] Creating Event Loop...\n";
    $loop = Loop::get();
    echo "OK\n\n";

    echo "[2] Creating SocketServer...\n";
    $socket = new SocketServer(
        '0.0.0.0:8099',
        [],
        $loop
    );
    echo "OK\n\n";

    echo "[3] Creating Application Handler...\n";
    $handler = new TestWebSocketServer();
    echo "OK\n\n";

    echo "[4] Creating WsServer...\n";
    $wsServer = new WsServer($handler);
    $wsServer->enableKeepAlive($loop, 30);
    echo "OK\n\n";
    echo "[5] Creating HttpServer...\n";
    $httpServer = new HttpServer($wsServer);
    echo "OK\n\n";

    echo "[6] Creating IoServer...\n";
    $server = new IoServer(
        $httpServer,
        $socket,
        $loop
    );
    echo "OK\n\n";

    echo "=====================================================\n";
    echo "SERVER READY\n";
    echo "Listening on ws://0.0.0.0:8099\n";
    echo "=====================================================\n\n";

    $loop->run();

} catch (Throwable $e) {

    echo "\n[FATAL STARTUP ERROR]\n";
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
