<?php

namespace common\components;

use Yii;
use yii\base\Component;

class WebSocketServer extends Component {

    private $server;
    private $clients = [];
    private $running = true;
    private $nestedLevel = 0;

    const TTL = 30; // seconds
    const TRACE_LEVEL = 5;

    public function init() {
        parent::init();
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->server, '127.0.0.1', 8082);
        socket_listen($this->server);
    }

    public function run() {
        while ($this->running) {
            $err = $this->handleNewSocket();
            if ($err) {
                $this->log('Server run', 'No new socket to handle');
            }

            $err = $this->handleNotifications();
            if ($err) {
                $this->log('Server run', 'Unable to handle new notifications: ' . socket_strerror($err));
                $this->running = false;
            }

            $this->cleanInactiveConnections();

            $this->log('Server run', $this->running ? 'WebSocket Server is running' : 'WebSocket Server will stop');
            usleep(1000000);
        }
    }

    private function log($fx, $log, $level = 0) {
        if ($level < $this::TRACE_LEVEL) {
            $msg = "";
            for ($i = 0; $i < $level; $i++) {
                $msg .= '  ';
            }
            $msg .= $fx . ' - ' . $log . "\r\n";
            echo $msg;
        }
    }

    private function handleNewSocket() {
        $success = true;
        $socket = socket_accept($this->server);
        if ($socket) {
            $this->log('handleNewSocket', 'socket accepted', 1);
            $success = $this->addNewClient($socket);
        }
        $this->log('handleNewSocket', 'handleNewSocket returns ' . $success ? 'True' : 'False', 1);
        return $success;
    }

    private function handleNotifications() {
        foreach ($this->clients as $key => $client) {
            $this->log('handleNotifications', 'Client key=' . $key, 1);
            $socket = $client['socket'];
            $data = $this->readClientData($socket);
            $this->log('handleNotifications', 'Data=' . $data, 1);
            if ($data) {
                $this->clients[$key]['last_activity'] = time();
                $this->pushNotification($socket, $data);
            }

            if ($socket) {
                $err = socket_last_error($socket);
                if ($err == 104) {
                    unset($this->clients[$key]);
                    $this->log('handleNotifications', 'socket is already closed', 1);
                    socket_clear_error($socket);
                } elseif ($err) {
                    $this->log('handleNotifications', 'socket_last_error=' . socket_strerror(socket_last_error()), 1);
                    return $err;
                }
            }
        }
        return null;
    }

    private function cleanInactiveConnections() {
        $currentTime = time();
        foreach ($this->clients as $key => $client) {
            if ($currentTime - $client['last_activity'] > $this::TTL) {
                socket_close($client['socket']);
                unset($this->clients[$key]);
            }
        }
    }

    private function addNewClient($socket) {
        $headers = socket_read($socket, 1024);
        if (!$headers) {
            $this->log('addNewClient', 'Cannot read socket: ' . socket_strerror(socket_last_error()), 2);
            socket_clear_error();
        }

        $key = $this->getKey($headers);
        if ($key) {
            $this->handleHandshake($socket, $key);
            $this->log('addNewClient', 'New client: key=' . $key, 2);
            $this->clients[$key] = [
                'socket' => $socket,
                'last_activity' => time()
            ];
            $this->log('addNewClient', 'Clients: ' . print_r($this->clients), 2);
        } else {
            $this->log('addNewClient', 'No key found', 2);
        }
        return ($key !== null);
    }

    private function handleHandshake($socket, $key) {
        $acceptKey = $this->acceptKey($key);
        if (!$acceptKey) {
            $this->log('handleHandshake', 'Accept key failed: ' . socket_strerror(socket_last_error()), 3);
            socket_clear_error();
        }
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
        $success = socket_write($socket, $response);
        if (!$success) {
            $this->log('handleHandshake', 'Cannot write socket: ' . socket_strerror(socket_last_error()), 3);
            socket_clear_error();
        }
    }

    private function getKey($headers) {
        $matches = [];
        if (preg_match('/Sec-WebSocket-Key: (.*)$/m', $headers, $matches)) {
            $key = trim($matches[1]);
            $this->log('getKey', 'key=' . $key, 3);
            return $key;
        }
        $this->log('getKey', 'Not key found', 3);
        return null;
    }

    private function acceptKey($key) {
        $magic = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        return base64_encode(sha1($key . $magic, true));
    }

    private function readClientData($socket) {
        $data = socket_read($socket, 1024);
        if (!$data) {
            $this->log('readClientData', 'Cannot read socket: ' . socket_strerror(socket_last_error()), 2);
            socket_clear_error();
        }

        if ($data === false || $data === '') {
            $success = socket_close($socket);
            if (!$success) {
                $this->log('readClientData', 'Cannot close socket: ' . socket_strerror(socket_last_error()), 2);
                socket_clear_error();
            }
            return false;
        }

        $this->log('readClientData', 'Data=' . $data, 2);
        return $data;
    }

    private function pushNotification($socket, $data) {
        $this->log('pushNotification', 'Data=' . $data, 2);
        $message = $this->decodeNotification($data);
        $this->log('pushNotification', 'Message=' . $message, 2);
        $payload = json_decode($message, true);
        $this->log('pushNotification', 'Payload=' . print_r($payload), 2);

        if ($payload && isset($payload['event'])) {
            $this->log('pushNotification', 'Broadcast', 2);
            $this->broadcast($payload);
        }
    }

    private function decodeNotification($message) {
        $length = ord($message[1]) & 127;
        $masks = substr($message, 2, 4);
        $data = substr($message, 6, $length);

        $text = '';
        for ($i = 0; $i < $length; ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }

    private function encodeNotification($message) {
        $length = strlen($message);
        $frame = chr(129);

        if ($length <= 125) {
            $frame .= chr($length);
        } elseif ($length <= 65535) {
            $frame .= chr(126) . pack('n', $length);
        } else {
            $frame .= chr(127) . pack('Q', $length);
        }

        return $frame . $message;
    }

    private function sendNotification($socket, $message) {
        $this->log('sendNotification', 'message=' . $message, 4);
        $encodedNotification = $this->encodeNotification(json_encode($message));
        $this->log('sendNotification', 'encodedNotification=' . $encodedNotification, 4);
        $success = socket_write($socket, $encodedNotification, strlen($encodedNotification));
        if (!$success) {
            $this->log('sendNotification', 'Cannot write socket: ' . socket_strerror(socket_last_error()), 4);
            socket_clear_error();
        }
    }

    public function broadcast($message) {
        $this->log('broadcast', 'Message=' . print_r($message), 3);
        foreach ($this->clients as $key => $client) {
            $this->log('broadcast', 'Client key=' . $key, 3);
            $this->sendNotification($client['socket'], $message);
        }
    }
}
