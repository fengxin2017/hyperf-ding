<?php

namespace Fengxin2017\HyperfDing\Contracts;

use Exception;

/**
 * Interface CoreContract.
 */
interface CoreContract
{
    /**
     * @param string $token
     *
     * @return mixed
     */
    public function setToken(string $token);

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param string $secret
     *
     * @return mixed
     */
    public function setSecret(string $secret);

    /**
     * @return string
     */
    public function getSecret(): string;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param bool $trace
     *
     * @return mixed
     */
    public function setTrace(bool $trace);

    /**
     * @return bool
     */
    public function getTrace(): bool;

    /**
     * @param int $reportFrequency
     *
     * @return mixed
     */
    public function setReportFrequency(int $reportFrequency);

    /**
     * @return int
     */
    public function getReportFrequency(): int;

    /**
     * @param string $text
     *
     * @return mixed
     */
    public function text(string $text);

    /**
     * @param string $markdown
     *
     * @return mixed
     */
    public function markdown(string $markdown);

    /**
     * @param string $notice
     *
     * @return mixed
     */
    public function notice(string $notice);

    /**
     * @param \Exception $exception
     *
     * @return mixed
     */
    public function exception(Exception $exception);
}
