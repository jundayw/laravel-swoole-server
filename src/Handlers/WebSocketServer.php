<?php

namespace Jundayw\LaravelSwooleServer\Handlers;

class WebSocketServer extends HttpServer
{
    public function open($server, $request)
    {
        echo "connection open: {$request->fd}", PHP_EOL;
        $server->tick(1000, function() use ($server, $request) {
            $server->push($request->fd, 'ping');
        });
    }

    public function message($server, $frame)
    {
        echo "received message: {$frame->data}", PHP_EOL;
        $server->push($frame->fd, json_encode(["hello" => $frame->fd, 'data' => $frame->data, 'time' => time()]));
    }
}