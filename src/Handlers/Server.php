<?php

namespace Jundayw\LaravelSwooleServer\Handlers;

class Server extends Handler
{
    public function start($server)
    {
        echo "Server master started.", PHP_EOL;
    }

    public function managerstart($server)
    {
        echo "Server manager started.", PHP_EOL;
    }

    public function workerstart($server, $worker_id)
    {
        if ($server->taskworker) {
            echo "Server worker[{$worker_id}] started.", PHP_EOL;
        } else {
            echo "Server task worker[{$worker_id}] started.", PHP_EOL;
        }
    }

    public function shutdown($server)
    {
        echo "Server shuttng down.", PHP_EOL;
    }

    public function connect($server, $fd, $reactorId)
    {
        echo "Connection: #{$fd}.", PHP_EOL;
    }

    public function close($server, $fd, $reactorId)
    {
        echo "Connection closed: #{$fd}.", PHP_EOL;
    }

    public function receive($server, $fd, $reactor_id, $data)
    {
        $server->task($data);
        $server->send($fd, "Echo to #{$fd}:" . $data);
        $server->close($fd);
    }

    public function packet($server, $data, $client_info)
    {
    }

    public function task($server, $task_id, $from_worker_id, $data)
    {
        echo "Start task: #{$task_id}.", PHP_EOL;
        echo "Start data: #{$data}.", PHP_EOL;
        return $data;
    }
}