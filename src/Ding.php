<?php

namespace Fengxin2017\HyperfDing;

use Dleno\CommonCore\Tools\Server;
use Exception;
use Fengxin2017\HyperfDing\Contracts\CoreContract;
use GuzzleHttp\Client;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Hyperf\WebSocketServer\Context as WsContext;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Ding
 * @package Fengxin2017\HyperfDing
 */
class Ding implements CoreContract
{
    /**
     * @Inject()
     * @var Redis $redis
     */
    protected $redis;
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $trace;

    /**
     * @var int
     */
    protected $reportFrequency;

    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Ding constructor.
     * @param array|null $params
     * @throws Exception
     */
    public function __construct(array $params = null)
    {
        $this->defaultConfig = $this->getDefaultConfig();

        $this->apiUrl = config('ding.request_url', 'https://oapi.dingtalk.com/robot/send?access_token=%s&timestamp=%s&sign=%s');
        $this->token = $params['token'] ?? $this->defaultConfig['token'];
        $this->secret = $params['secret'] ?? $this->defaultConfig['secret'];
        $this->name = $params['name'] ?? $this->defaultConfig['name'];
        $this->trace = $params['trace'] ?? $this->defaultConfig['trace'];
        $this->reportFrequency = $params['report_frequency'] ?? $this->defaultConfig['report_frequency'];

        $this->client = new Client();
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getDefaultBotName(): string
    {
        $defultBotName = config('ding.default');

        if (is_null($defultBotName)) {
            throw new Exception(sprintf('???%s???????????????????????????', 'config/ding.php'));
        }

        return $defultBotName;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getDefaultConfig(): array
    {
        return tap(config('ding.bots.' . $this->getDefaultBotName()), function ($config) {
            $this->checkConfig($config, ['token', 'secret', 'name', 'trace', 'report_frequency']);
        });
    }

    /**
     * @param array $config
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    protected function checkConfig(array $config, array $fields)
    {
        foreach ($fields as $field) {
            if (!isset($config[$field])) {
                throw new Exception(sprintf('???????????????????????????%s???', $field));
            }
        }

        return true;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function getTrace(): bool
    {
        return $this->trace;
    }

    /**
     * @param bool $trace
     *
     * @return $this
     */
    public function setTrace(bool $trace): self
    {
        $this->trace = $trace;

        return $this;
    }

    /**
     * @param int $reportFrequency
     *
     * @return $this|mixed
     */
    public function setReportFrequency(int $reportFrequency): self
    {
        $this->reportFrequency = $reportFrequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getReportFrequency(): int
    {
        return $this->reportFrequency;
    }

    /**
     * @param string $title
     * @param string $type
     * @param string $content
     * @param string $contentType
     * @return mixed
     * @throws \Throwable
     */
    protected function ding(string $title, string $type, string $content, string $contentType = 'content')
    {
        if ($type === 'markdown') {
            $contentType = 'text';
        }

        return $this->sendDingTalkRobotMessage([
            'msgtype' => $type,
            $type => [
                'title' => $title,
                $contentType => $content,
            ],
        ]);
    }

    /**
     * @param array $msg
     * @return mixed
     * @throws \Throwable
     */
    public function sendDingTalkRobotMessage(array $msg)
    {
        if (Coroutine::inCoroutine()) {
            Coroutine::create(function () use ($msg) {
                $this->_sendDingTalkRobotMessage($msg);
            });
        } else {
            $this->_sendDingTalkRobotMessage($msg);
        }

        return true;
    }

    /**
     * @param array $msg
     */
    protected function _sendDingTalkRobotMessage(array $msg)
    {
        // ??????????????????????????????????????????????????????20?????????
        $timestamp = (string)(time() * 1000);
        $secret = $this->getSecret();
        $token = $this->getToken();
        $sign = urlencode(base64_encode(hash_hmac('sha256', $timestamp . "\n" . $secret, $secret, true)));
        $response = $this->client->post(sprintf($this->apiUrl, $token, $timestamp, $sign), ['json' => $msg]);
        $result = json_decode($response->getBody(), true);
        if (!isset($result['errcode']) || $result['errcode']) {
            var_dump('??????????????????');
            var_dump($result);
        }
    }

    /**
     * @param string $text
     * @return mixed
     * @throws \Throwable
     */
    public function text(string $text)
    {
        return $this->ding('????????????', 'text', $text);
    }

    /**
     * @param string $markdown
     * @return mixed
     * @throws \Throwable
     */
    public function markdown(string $markdown)
    {
        return $this->ding('MARKDOWN??????', 'markdown', $markdown);
    }

    /**
     * @param string $notice
     * @return mixed|void
     * @throws \Throwable
     */
    public function notice(string $notice)
    {
        return $this->ding('????????????', 'markdown', $this->formatNotice($notice));
    }

    /**
     * @param \Throwable $exception
     * @return mixed|void
     * @throws \Throwable
     */
    public function exception(\Throwable $exception)
    {
        if (!$this->shouldReportException($exception)) {
            return;
        }

        return $this->ding('????????????', 'markdown', $this->formatException($exception));
    }

    /**
     * @param \Throwable $exception
     * @return bool
     */
    protected function shouldReportException(\Throwable $exception): bool
    {
        $exceptionkey = env('APP_NAME', 'ding') . ':ding_exception_key:' . md5($this->name . $exception->getMessage());

        return $this->redis->set($exceptionkey, true, ['NX', 'EX' => $this->reportFrequency]);
    }

    /**
     * @param string $notice
     * @return string
     */
    protected function formatNotice(string $notice)
    {
        $time = date('Y-m-d H:i:s', time());

        if (class_exists(\Dleno\CommonCore\Tools\Client::class)) {
            $ip = \Dleno\CommonCore\Tools\Client::getIP();
        } else {
            $ip = '';
        }

        // ?????????????????????????????????
        if (Context::has(ServerRequestInterface::class)) {
            $request = ApplicationContext::getContainer()
                ->get(RequestInterface::class);
        } elseif (class_exists(WsContext::class) && WsContext::has(ServerRequestInterface::class)) {
            $request = WsContext::get(ServerRequestInterface::class);
        } else {
            $request = null;
        }

        if (is_null($request)) {
            $requestUrl = 'local';
            $method = 'local';
            $params = 'local';
            $headers = 'local';
        } else {
            $requestUrl = $request->getUri();
            $method = $request->getMethod();
            /** @noinspection JsonEncodingApiUsageInspection */
            $params = json_encode($request->all());
            $headers = $request->getHeaders();
            foreach ($headers as $key => $val) {
                unset($headers[$key]);
                $key = strtolower($key);
                $headerPrefixs = array_unique(config('ding.header_prefix') ?: []);
                foreach ($headerPrefixs as $headerPrefix) {
                    if (strpos($key, $headerPrefix) !== false) {
                        $headers[$key] = is_array($val) ? join('; ', $val) : $val;
                    }
                }
            }
            //??????????????????key
            foreach (config('ding.ignore_headers') ?: [] as $key) {
                $key = strtolower($key);
                unset($headers[$key]);
            }

            $headers = json_encode($headers);
        }

        $hostName = gethostname();
        $env = config('app_env');

        if (class_exists(Server::class)) {
            $traceId = Server::getTraceId();
        } else {
            $traceId = null;
        }

        return $this->formatMessage([
            ['??????', $this->name . '-' . '????????????~'],
            ['????????????', $notice],
            ['??????ID', $traceId],
            ['????????????', $hostName],
            ['??????', $env],
            ['??????IP', $ip],
            ['?????????', $headers],
            ['????????????', $params],
            ['??????', $time],
            ['????????????', $method],
            ['????????????', $requestUrl],
            ['??????????????????', '(???' . $this->reportFrequency . 's ??????)'],
        ]);
    }

    /**
     * @param \Throwable $exception
     * @return string
     */
    protected function formatException(\Throwable $exception)
    {
        $class = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $time = date('Y-m-d H:i:s', time());

        if (class_exists(\Dleno\CommonCore\Tools\Client::class)) {
            $ip = \Dleno\CommonCore\Tools\Client::getIP();
        } else {
            $ip = null;
        }

        //?????? HTTP/WS/LOCAL
        if (Context::has(ServerRequestInterface::class)) {
            $request = ApplicationContext::getContainer()
                ->get(RequestInterface::class);
        } elseif (class_exists(WsContext::class) && WsContext::has(ServerRequestInterface::class)) {
            $request = WsContext::get(ServerRequestInterface::class);
        } else {
            $request = null;
        }

        if (is_null($request)) {
            $requestUrl = 'local';
            $method = 'local';
            $params = 'local';
            $headers = 'local';
        } else {
            $requestUrl = $request->getUri();
            $method = $request->getMethod();
            /** @noinspection JsonEncodingApiUsageInspection */
            $params = json_encode($request->all());
            $headers = $request->getHeaders();
            foreach ($headers as $key => $val) {
                unset($headers[$key]);
                $key = strtolower($key);
                $headerPrefixs = array_unique(config('ding.header_prefix') ?: []);
                foreach ($headerPrefixs as $headerPrefix) {
                    if (strpos($key, $headerPrefix) !== false) {
                        $headers[$key] = is_array($val) ? join('; ', $val) : $val;
                    }
                }
            }
            //??????????????????key
            foreach (config('ding.ignore_headers', []) as $key) {
                $key = strtolower($key);
                unset($headers[$key]);
            }

            $headers = json_encode($headers);
        }

        $hostName = gethostname();
        $env = config('app_env');

        if (class_exists(Server::class)) {
            $traceId = Server::getTraceId();
        } else {
            $traceId = null;
        }

        $explode = explode("\n", $exception->getTraceAsString());
        array_unshift($explode, '');

        $messageBody = [
            ['??????', $this->name . '-' . '????????????~'],
            ['??????ID', $traceId],
            ['????????????', $hostName],
            ['??????', $env],
            ['??????', $class],
            ['??????IP', $ip],
            ['?????????', $headers],
            ['????????????', $params],
            ['??????', $time],
            ['????????????', $method],
            ['????????????', $requestUrl],
            ['????????????', $message],
            ['??????????????????', '(???' . $this->reportFrequency . 's ??????)'],
            ['????????????', sprintf('%s:%d', str_replace([BASE_PATH, '\\'], ['', '/'], $file), $line)],
        ];

        if ($this->getTrace()) {
            $messageBody[] = [
                '????????????',
                PHP_EOL . '>' . implode(PHP_EOL . '> - ', $explode),
            ];
        }

        return $this->formatMessage($messageBody);
    }

    /**
     * @param array $messageBody
     * @return string
     */
    protected function formatMessage(array $messageBody)
    {
        $messageBody = array_map(function ($item) {
            [$key, $val] = $item;

            return sprintf('- %s: %s> %s', $key, PHP_EOL, $val);
        }, $messageBody);

        return implode(PHP_EOL, $messageBody);
    }

    /**
     * @param string $botName
     * @param array $parameters
     * @return $this
     * @throws Exception
     */
    public function __call(string $botName, array $parameters)
    {
        $config = config('ding.bots.' . Str::snake($botName));

        if (is_null($config)) {
            throw new Exception(sprintf('Bot ???%s??? not exists.', $botName));
        }

        isset($config['token']) && $this->setToken($config['token']);
        isset($config['secret']) && $this->setSecret($config['secret']);
        isset($config['name']) && $this->setName($config['name']);
        isset($config['trace']) && $this->setTrace($config['trace']);
        isset($config['report_frequency']) && $this->setReportFrequency($config['report_frequency']);
        return $this;
    }
}
