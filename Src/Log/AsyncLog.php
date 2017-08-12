<?php

namespace CjwAsync\Src\Log;

/**
 * Class AsyncLog
 * @package CjwAsync\Src\Log
 */
class AsyncLog extends GeneratorLog
{
    /**
     * 日志存储的类型
     * @var string
     */
    protected $log_type = 'daily';

    /**
     * 日志名称
     * @var string
     */
    protected $log_name = 'async_log';

    /**
     * 按天进行切割日志的配置信息
     * @var array
     */
    protected $dailyConfig = [
        'level' => 'debug',
        'fileName' => 'async/async',
        'day' => 100,
    ];
}