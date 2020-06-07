<?php

namespace Jundayw\LaravelSwooleServer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Swoole\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SwooleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'swoole';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a web socket server';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'swoole command';
    protected $config;
    protected $server;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->config = $this->laravel->make('config')->get('swoole');

        if (array_key_exists($this->argument('name'), $this->config)) {
            $this->config = $this->config[$this->argument('name')];
        } else {
            $this->config = $this->config['default'];
        }

        $action = $this->option('action');

        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'reload':
                $this->reload();
                break;
            case 'infos':
                $this->infos();
                break;
            default:
                $this->info('action allow start,stop,restart,reload,info');
                break;
        }
    }

    /**
     * process is running.
     *
     * @return bool
     */
    private function isRunning()
    {
        return is_array($this->getProcessIdentification(['master', 'manager']));
    }

    private function getProcessIdentification($names = [], $name = '')
    {
        $data = [];

        if (is_array($names) == false) {
            $names = explode(',', $names);
        }

        if (count($names) == 1) {
            $name = reset($names);
        }

        foreach ($names as $pidname) {
            $pid = shell_exec("pidof {$this->config['process_name'][$pidname]}");
            $pid = str_replace(PHP_EOL, '', $pid);
            if (empty($pid)) {
                return false;
            }
            $data[$pidname] = $pid;
        }

        return empty($name) ? $data : $data[$name];
    }

    /**
     * Kill process.
     *
     * @param int $sig
     * @param int $wait
     *
     * @return bool
     */
    protected function killProcess($sig, $wait = 0)
    {
        Process::kill(
            $this->getProcessIdentification('master'),
            $sig
        );

        if ($wait) {
            $start = time();

            do {
                if (!$this->isRunning()) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->isRunning();
    }

    private function start()
    {
        if ($this->isRunning()) {
            $this->error('Failed! process is already running.');
            return;
        }

        $this->info('Starting server...');
        $this->info("Server started: {$this->config['host']}:{$this->config['port']}");

        $this->server = new $this->config['server'](
            $this->config['host'],
            $this->config['port'],
            $this->config['process_type'],
            $this->config['socket_type']
        );

        $this->server->set($this->config['options']);

        foreach ($this->config['listeners'] as $listener) {
            $this->info("Server listener: {$listener['host']}:{$listener['port']}");
            $this->server->addListener($listener['host'], $listener['port'], $listener['sock_type']);
        }

        $handle = new $this->config['handler']($this->config);

        $this->server->on('start', [$handle, 'onEventStart']);
        $this->server->on('managerstart', [$handle, 'onEventManagerstart']);
        $this->server->on('workerstart', [$handle, 'onEventWorkerstart']);
        foreach ($this->config['events'] as $event => $callback) {
            if (in_array($callback, ['start', 'managerstart', 'workerstart'])) {
                continue;
            }
            $this->server->on($event, [$handle, $callback]);
        }

        $this->server->start();
    }

    private function stop()
    {
        if (!$this->isRunning()) {
            $this->error("Failed! There is no process running.");
            return;
        }

        $this->info('Stopping server...');

        $isRunning = $this->killProcess(SIGTERM, 15);

        if ($isRunning) {
            $this->error('Unable to stop the process.');
            return;
        }

        $this->info('> success');
    }

    private function restart()
    {
        if ($this->isRunning()) {
            $this->stop();
        }

        $this->start();
    }

    private function reload()
    {
        if (!$this->isRunning()) {
            $this->error("Failed! There is no process running.");
            return;
        }

        $this->info('Reloading ...');

        if (!$this->killProcess(SIGUSR1)) {
            $this->error('> failure');
            return;
        }

        $this->info('> success');
    }

    private function infos()
    {
        $host               = $this->config['host'];
        $port               = $this->config['port'];
        $reactorNum         = $this->config['options']['reactor_num'];
        $workerNum          = $this->config['options']['worker_num'];
        $hasTaskWorker      = $this->config['options']['task_worker_num'];
        $pidFile            = $this->config['options']['pid_file'];
        $logFile            = $this->config['options']['log_file'];
        $isRunning          = $this->isRunning();
        $master             = $this->getProcessIdentification('master');
        $manager            = $this->getProcessIdentification('manager');
        $worker             = $this->getProcessIdentification('worker');
        $task               = $this->getProcessIdentification('task');
        $package_max_length = $this->config['options']['package_max_length'];
        $buffer_output_size = $this->config['options']['buffer_output_size'];
        $socket_buffer_size = $this->config['options']['socket_buffer_size'];
        $max_request        = $this->config['options']['max_request'];

        $table = [
            ['PHP Version', 'Version' => phpversion()],
            ['Swoole Version', 'Version' => swoole_version()],
            ['Laravel Version', $this->getApplication()->getVersion()],
            ['Listen IP', $host],
            ['Listen Port', $port],
            ['Server Status', $isRunning ? 'Online' : 'Offline'],
            ['Reactor Num', $reactorNum],
            ['Worker Num', $workerNum],
            ['Task Worker Num', $hasTaskWorker],
            ['Master PID', $isRunning ? $master : 'None'],
            ['Manager PID', $isRunning && $manager ? $manager : 'None'],
            ['Worker PID', $isRunning && $worker ? $worker : 'None'],
            ['Task Worker PID', $isRunning && $task ? $task : 'None'],
            ['Log Path', $pidFile],
            ['Log File', $logFile],
            ['package_max_length', $package_max_length],
            ['buffer_output_size', $buffer_output_size],
            ['socket_buffer_size', $socket_buffer_size],
            ['max_request', $max_request],
        ];

        $this->table(['Name', 'Value'], $table);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the handle', 'default'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['action', null, InputOption::VALUE_OPTIONAL, 'The action (start,stop,restart,reload,infos) to allow', 'infos'],
        ];
    }
}
