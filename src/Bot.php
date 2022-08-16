<?php

namespace Fengxin2017\HyperfDing;

use Exception;
use Hyperf\Utils\Str;

/**
 * @method static mixed text($text)
 * @method static mixed notice($notice)
 * @method static mixed markdown($markdown)
 * @method static mixed exception($exception)
 *
 * Class Bot
 */
abstract class Bot
{
    /**
     * @var array
     */
    protected static $bots = [];

    /**
     * @var array
     */
    protected $validMethods = ['exception', 'markdown', 'text', 'notice'];

    /**
     * Bot constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, array $params)
    {
        if (!in_array($method, $this->getValidMethods())) {
            throw new Exception('call to undefined method ' . $method);
        }

        return call_user_func_array([$this->getTheExactCore(), $method], $params);
    }

    /**
     * @return array
     */
    protected function getValidMethods(): array
    {
        return $this->validMethods;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function getTheExactCore()
    {
        $snakeBotName = $this->getSnakeBotName();

        if (isset(static::$bots[$snakeBotName])) {
            return static::$bots[$snakeBotName];
        }

        $config = config('ding.bots.' . $snakeBotName);

        if (is_null($config)) {
            throw new Exception(sprintf('【%s】无对应机器人配置', 'config/ding.php'));
        }

        return static::$bots[$snakeBotName] = make(Ding::class, ['params' => $config]);
    }

    /**
     * @return string
     */
    protected function getSnakeBotName(): string
    {
        return Str::snake(substr(static::class, strrpos(static::class, '\\') + 1));
    }

    /**
     * @param string $method
     * @param array $params
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $params)
    {
        return (new static())->$method(...$params);
    }
}
