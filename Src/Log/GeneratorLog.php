<?php

namespace CjwAsync\Src\Log;

use Illuminate\Log\Writer;
use Monolog\Logger;

/**
 * 一个基础的自定义日志类
 * Class GeneratorLog
 * @package CjwAsync\Src\Log
 */
abstract class GeneratorLog
{
    /**
     * 日志对象
     * @var
     */
    private static $logObj;

    /**
     * 日志存储的类型
     * @var string
     */
    protected $log_type = 'single';    // [ 'single' 、'daily']

    /**
     * 日志名称
     * @var string
     */
    protected $log_name = 'logName';

    /**
     * 按天进行切割日志的配置信息
     * @var array
     */
    protected $dailyConfig = [
        'level' => 'debug',
        'fileName' => 'DirName/fileName',
        'day' => 100,
    ];

    /**
     * 不切割日志的配置信息
     * @var array
     */
    protected $singleConfig = [
        'level' => 'debug',
        'fileName' => 'DirName/fileName',
    ];

    /**
     * 常见的错误级别
     * @var string
     */
    private $debug = 'debug';
    private $info = 'info';
    private $notice = 'notice';
    private $warning = 'warning';
    private $error = 'error';

    /**
     * 处理方法
     * @param $message
     * @param $context
     * @param $level
     */
    protected function handle($message, $context, $level)
    {
        // 1. 获取单例对象
        if (empty(self::$logObj instanceof Writer)) {
            self::$logObj = new Writer(new Logger($this->log_name));
            if ($this->log_type == 'single') {
                self::$logObj->useFiles(
                    storage_path() . '/logs/' . $this->dailyConfig['fileName'] . '.log',
                    $this->dailyConfig['level']
                );
            } else if ($this->log_type == 'daily') {
                self::$logObj->useDailyFiles(
                    storage_path() . '/logs/' . $this->dailyConfig['fileName'] . '.log',
                    $this->dailyConfig['day'],
                    $this->dailyConfig['level']
                );
            } else {
                simpleError('please set the correct parameters ', __FILE__, __LINE__);
            }

        }

        self::$logObj->$level($message, $context);
    }

    /**
     * debug
     * @param $message
     * @param array $context
     */
    public function debug($message, $context = [])
    {
        $this->handle($message, $context, $this->debug);
    }

    /**
     * info
     * @param $message
     * @param array $context
     */
    public function info($message, $context = [])
    {
        $this->handle($message, $context, $this->info);
    }

    /**
     * notice
     * @param $message
     * @param array $context
     */
    public function notice($message, $context = [])
    {
        $this->handle($message, $context, $this->notice);
    }

    /**
     * warning
     * @param $message
     * @param array $context
     */
    public function warning($message, $context = [])
    {
        $this->handle($message, $context, $this->warning);
    }

    /**
     * error
     * @param $message
     * @param array $context
     */
    public function error($message, $context = [])
    {
        $this->handle($message, $context, $this->error);
    }
}