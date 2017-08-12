<?php

namespace CjwAsync\Src\AsyncLogHandle;

use CjwAsync\Src\Log\AsyncLog;
use CjwAsync\Src\Log\GeneratorLog;

abstract class AsyncLogObserverOfGenerator implements AsyncLogObserver
{
    /**
     * 订阅的信息（类型于频道）
     * @var string
     */
    public $message;

    /**
     * 选用存储日志的driver
     * @var GeneratorLog
     */
    protected $_asyncLog;

    /**
     * AsyncLogObserverOfGenerator constructor.
     */
    public function __construct()
    {
        // 1. 设置 订阅的名称
        $this->message = 'the_async_log_name_is:"' . get_class($this) . '"';
        // 2. 写入日志的driver
        $this->_use_async_log();
    }

    /**
     * 订阅消息内容           所有的日志是使用info级别
     * @param array $context
     */
    public function subscribe(array $context)
    {
        $this->_asyncLog->info($this->message, [json_encode($context, JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * 使用的log的driver
     */
    protected function _use_async_log()
    {
        $this->_asyncLog = new AsyncLog();
    }
}