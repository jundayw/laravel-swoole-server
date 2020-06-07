<?php

namespace Jundayw\LaravelSwooleServer\Handlers;

class Handler
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    final public function onEventStart()
    {
        $master = $this->config['process_name']['master'];
        swoole_set_process_name($master);

        if (array_key_exists('start', $this->config['events']) == false) {
            return;
        }
        if (method_exists($this, $this->config['events']['start'])) {
            call_user_func_array([$this, $this->config['events']['start']], func_get_args());
        }
    }

    final public function onEventManagerstart()
    {
        $manager = $this->config['process_name']['manager'];
        swoole_set_process_name($manager);

        if (array_key_exists('managerstart', $this->config['events']) == false) {
            return;
        }
        if (method_exists($this, $this->config['events']['managerstart'])) {
            call_user_func_array([$this, $this->config['events']['managerstart']], func_get_args());
        }
    }

    final public function onEventWorkerstart($server)
    {
        $worker = $this->config['process_name'][$server->taskworker ? 'task' : 'worker'];
        swoole_set_process_name($worker);

        if (array_key_exists('workerstart', $this->config['events']) == false) {
            return;
        }
        if (method_exists($this, $this->config['events']['workerstart'])) {
            call_user_func_array([$this, $this->config['events']['workerstart']], func_get_args());
        }
    }

    public function __call($name, $arguments)
    {
        echo $name, PHP_EOL;
    }
}