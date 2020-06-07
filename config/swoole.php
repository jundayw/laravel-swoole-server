<?php

return [
    'default' => [
        'server' => Swoole\WebSocket\Server::class,
        'server' => Swoole\Http\Server::class,
        'server' => Swoole\Server::class,
        'host' => env('SERVER_HOST', '0.0.0.0'),
        'port' => env('SERVER_PORT', '1215'),
        'process_type' => SWOOLE_PROCESS,
        'socket_type' => SWOOLE_SOCK_TCP,
        'options' => [
            'pid_file' => env('SERVER_PID_FILE', base_path('storage/logs/swoole.pid')),
            'log_file' => env('SERVER_LOG_FILE', base_path('storage/logs/swoole.log')),
            'daemonize' => env('SERVER_DAEMONIZE', false),
            // Normally this value should be 1~4 times larger according to your cpu cores.
            'reactor_num' => env('SERVER_REACTOR_NUM', 4),
            'worker_num' => env('SERVER_WORKER_NUM', 2),
            'task_worker_num' => env('SERVER_TASK_WORKER_NUM', 4),
            // The data to receive can't be larger than buffer_output_size.
            'package_max_length' => 20 * 1024 * 1024,
            // The data to send can't be larger than buffer_output_size.
            'buffer_output_size' => 10 * 1024 * 1024,
            // Max buffer size for socket connections
            'socket_buffer_size' => 128 * 1024 * 1024,
            // Worker will restart after processing this number of requests
            'max_request' => 3000,
            // Enable coroutine send
            'send_yield' => true,
            // You must add --enable-openssl while compiling Swoole
            'ssl_cert_file' => null,
            'ssl_key_file' => null,
        ],
        'listeners' => [
            [
                'host' => '0.0.0.0',
                'port' => 9501,
                'sock_type' => SWOOLE_SOCK_TCP
            ],
        ],
        'process_name' => [
            'master' => 'master',
            'manager' => 'manager',
            'worker' => 'worker',
            'task' => 'task',
        ],
        'events' => [
            'start' => 'start',
            'managerstart' => 'managerstart',
            'workerstart' => 'workerstart',
            'shutdown' => 'shutdown',
            // Swoole\Server
            'connect' => 'connect',
            'close' => 'close',
            'receive' => 'receive', // TCP
            'packet' => 'packet', // UDP
            'task' => 'task',
            'finish' => 'finish',
            // Swoole\Http\Server
            'request' => 'request',
            // Swoole\WebSocket\Server
            'open' => 'open',
            'message' => 'message',
        ],
        'handler' => Jundayw\LaravelSwooleServer\Handlers\Server::class,
    ],
];