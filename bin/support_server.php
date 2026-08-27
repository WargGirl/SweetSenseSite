<?php

require_once dirname(__DIR__) . '/classes/Database.php';

$host = '0.0.0.0';
$port = (int)(getenv('WS_PORT') ?: 8080);

try {
    $db = new Database();
    $pdo = $db->getPdo();
} catch (Throwable $e) {
    die("[DB ERROR] " . $e->getMessage() . "\n");
}

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($server, $host, $port);
socket_listen($server);

echo "SweetSense WebSocket Server started on ws://{$host}:{$port}\n";

$clients = [$server];
$users = [];

while (true) {
    $read = $clients;
    $write = null;
    $except = null;

    if (socket_select($read, $write, $except, 0, 10000) < 1) {
        continue;
    }

    if (in_array($server, $read, true)) {
        $newSocket = socket_accept($server);
        if ($newSocket) {
            $clients[] = $newSocket;
            $header = @socket_read($newSocket, 2048);
            performHandshake($header, $newSocket, $host, $port);
        }
        $key = array_search($server, $read, true);
        unset($read[$key]);
    }

    foreach ($read as $client) {
        $clientId = spl_object_id($client);
        $data = @socket_read($client, 4096, PHP_BINARY_READ);

        if ($data === false || $data === '') {
            $key = array_search($client, $clients, true);
            if ($key !== false) {
                unset($clients[$key]);
            }
            unset($users[$clientId]);
            @socket_close($client);
            continue;
        }

        $decoded = unmask($data);
        if (!$decoded) {
            continue;
        }

        $msg = json_decode($decoded, true);
        if (!is_array($msg) || empty($msg['type'])) {
            continue;
        }

        if ($msg['type'] === 'auth') {
            $users[$clientId] = [
                'socket'   => $client,
                'userId'   => (int)($msg['userId'] ?? 0),
                'username' => (string)($msg['username'] ?? 'User')
            ];
        }
        elseif ($msg['type'] === 'private_message') {
            $sender = $users[$clientId] ?? null;
            $targetUserId = (int)($msg['toUserId'] ?? 0);
            $text = trim((string)($msg['message'] ?? ''));

            if ($sender && $targetUserId > 0 && $text !== '') {
                $fromId = (int)$sender['userId'];
                $fromName = $sender['username'];

                try {
                    $stmt = $pdo->prepare("INSERT INTO chat_messages (from_user_id, to_user_id, message, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$fromId, $targetUserId, $text]);
                } catch (Throwable $e) {
                    echo "[DB Error] " . $e->getMessage() . "\n";
                }

                $response = json_encode([
                    'type'         => 'private_message',
                    'fromUserId'   => $fromId,
                    'fromUsername' => $fromName,
                    'toUserId'     => $targetUserId,
                    'message'      => $text,
                    'timestamp'    => date('d.m.Y H:i')
                ], JSON_UNESCAPED_UNICODE);

                foreach ($users as $u) {
                    if ((int)$u['userId'] === $targetUserId || (int)$u['userId'] === $fromId) {
                        sendPacket($u['socket'], $response);
                    }
                }
            }
        }
    }
}

function sendPacket($client, string $msg): void {
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($msg);
    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } else {
        $header = pack('CCNN', $b1, 127, 0, $length);
    }
    @socket_write($client, $header . $msg, strlen($header . $msg));
}

function unmask(string $payload): string {
    if (strlen($payload) < 2) return '';
    $length = ord($payload[1]) & 127;
    if ($length === 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length === 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }
    $text = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function performHandshake($headers, $client, $host, $port): void {
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", (string)$headers, $match)) {
        $key = trim($match[1]);
        $accept = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: {$accept}\r\n\r\n";
        @socket_write($client, $upgrade, strlen($upgrade));
    }
}
