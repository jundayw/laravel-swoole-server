<?php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 1215)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world");
echo $client->recv();
$client->close();

