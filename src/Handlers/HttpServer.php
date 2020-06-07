<?php

namespace Jundayw\LaravelSwooleServer\Handlers;

class HttpServer extends Server
{
    public function request($request, $response)
    {
        $response->end("<h1>Hello World. #" . rand(1000, 9999) . "</h1>");
    }
}