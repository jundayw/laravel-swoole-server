# 安装方法
命令行下, 执行 composer 命令安装:
````
composer require jundayw/laravel-swoole-server
````

# 使用方法
authentication package that is simple and enjoyable to use.

## 导出配置
```
php artisan vendor:publish --tag=swoole-config
```

## 命令行
```
php artisan swoole {name} {--action=start|stop|reload|restart|infos}
```
配置文件

handler：根据具体业务需求自行处理，需要继承 Jundayw\LaravelSwooleServer\Handler；

## 启动
```
php artisan swoole default --action=start
```

## 终止
```
php artisan swoole default --action=stop
```

## 重载
```
php artisan swoole default --action=reload
```

## 重启
```
php artisan swoole default --action=restart
```

## 查看信息
```
php artisan swoole default --action=infos
```
