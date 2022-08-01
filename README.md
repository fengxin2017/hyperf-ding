<h1 align="center"> Hyperf - Ding </h1>

<p align="center"></p>


## Installing

```shell
$ composer require fengxin2017/hyperf-ding -vvv

$ php bin/hyperf.php vendor:publish fengxin2017/hyperf-ding
```

## Usage

```
use Fengxin2017\HyperfDing\Ding;

$ding = new Ding([
                    'token' => 'xxxx',
                    'secret' => 'xxxxx',
                    'title' => '小白鼠'
                    // ....
                ]);

$ding->text('API 线上调试时很有用哦');
$ding->markdown('### 标题');
$ding->exception(new Exception('出问题啦'));

$ding = new Ding([
                    'token' => 'xxxx',
                    'secret' => 'xxxxx',
                    'title' => '小白鼠'
                    // ....
                ]);

// 覆盖配置默认配置
$ding->setTitle('改个标题')
    ->setTrace(true)   // 开启追踪
    ->setLimit(true)   // 开启上报间隔时间
    ->setReportFrequency(20)  // 上报时间间隔
    ->setDescription('改个描述') // 自定义描述
    ->exception(new Exception('出问题')); 


$ding = new Ding();

// 对应配置中的`prod`配置
$ding->prod()->text('我是生产机器人');

// 对应配置中的`dev`配置 
$ding->dev()->exception(new Exception('开发异常'));
```

### 助手函数

```
// 默认配置取config/ding.php的default

ding()->text('API 线上调试时很有用哦');
ding()->markdown('### 标题');
ding()->exception(new Exception('出问题啦'));

// 覆盖配置,没设置到的地方会使用对应机器人默认配置
ding()->setTitle('改个标题')
    ->setTrace(true)   // 开启追踪
    ->setLimit(true)   // 开启上报间隔时间
    ->setReportFrequency(20)  // 上报时间间隔
    ->setDescription('改个描述') // 自定义描述
    ->exception(new Exception('出问题')); 

// 调用其他机器人
ding('dev')->markdown('> 我是开发机器人');
ding()->prod()->text('我是生产机器人');

// 自定义配置调用
ding([
    'token' => 'xxxx',
    'secret' => 'xxxxx',
    'title' => '小白鼠'
    // ....
])->text('ABC');

ding([
    'token' => 'xxxx',
    'secret' => 'xxxxx',
    'title' => '小白鼠'
    // ....
])->exception(new Exception('异常'));

ding()->setToken()->setSecret()->setTitle('标题')->text('招呼咯');
ding()->setToken()->setSecret()->exception(new Exception('异常'));

```

### DINGDING自定义机器人调用

### NOTICE 
> 机器人调用为单例模式，不支持动态修改配置。

> 类名为配置中机器人驼峰首字母大写。

```
// 

<?php
namespace App\Ding\Bots;
use Fengxin2017\HyperfDing\Bot;

class Prod extends Bot
{
}

class Dev extends Bot
{
}

<? php
use App\Ding\Bots\Prod;
use App\Ding\Bots\Dev;

Prod::markdown('### 这是标题');
Dev::text('API 线上调试时很有用哦');
Prod::exception(new Exception('出错啦'));

```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/fengxin2017/ding/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/fengxin2017/ding/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT