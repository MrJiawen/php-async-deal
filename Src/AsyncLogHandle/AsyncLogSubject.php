<?php

namespace CjwAsync\Src\AsyncLogHandle;

use Illuminate\Support\Facades\Log;

/**
 * Interface AsyncLogSubject
 * @package CjwAsync\Src\AsyncLogHandle
 */
class AsyncLogSubject
{
    /**
     * 存储观察者
     * @var array
     */
    private $observers = array();

    /**
     * 日志格式
     * @var string
     */
    protected $log_path;

    /**
     * 日志处理信息
     * @var array
     */
    protected $log_deal;

    /**
     * 出现异常的回调函数
     * @var callback
     */
    protected $exception_callback = null;

    /**
     * AsyncLogSubject constructor.
     * @param string $time
     */
    public function __construct($time = 'today')
    {
        // 1. 设置日志路径
        $this->getLogPath($time);

        // 2. 设置日志处理 变量的数据格式
        $this->log_deal = [
            // 成功容器里面装载着成功的开始行号和结束的行号，如果某一行发生一行异常，则按照顺序输出日志
            'success_contain' => [
                'start_line' => null,
                'end_line' => null,
            ],
            // 正在处理的某一行日志
            'deal_contain' => [

            ]
        ];
    }

    /**
     * 添加观察者
     * @param AsyncLogObserver $observer
     * @Author jiaWen.chen
     */
    public function addObserver(AsyncLogObserver $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * 发送信息
     * @Author jiaWen.chen
     */
    public function handle()
    {
        // 1. 验证文件是否存在
        if (!file_exists($this->log_path)) {
            Log::warning('执行定时任务，读取日志文件来完成订阅与发布，找不到异步日志文件', ['文件名：' . $this->log_path]);
            $this->exception('执行定时任务，读取日志文件来完成订阅与发布，找不到异步日志文件,文件名：' . $this->log_path);
            exit;
        }

        // 2. 读取日志文件
        $fp = fopen($this->log_path, "r");
        $lineNum = 1;
        while (!feof($fp)) {//循环读取，直至读取完整个文件

            // 2.1 格式化数据结构
            $this->handleBefore(fgets($fp, 1024 * 1024 * 1024), $lineNum++);
            // 2.2 判断是否已经执行完毕
            if (empty($this->log_deal['deal_contain'])) {
                $this->dealSuccessContain();
                Log::notice("执行定时任务，读取日志文件来完成订阅与发布，执行完毕！！！");
                break;
            }
            dump('执行定时任务，读取日志文件来完成订阅与发布,正在执行第' . $this->log_deal['deal_contain']['line_num'] . '行...');

            // 2.3 执行对应的message事物。
            $observerExists = $observerResult = null;
            foreach ($this->observers as $key => $observer) {
                if (!empty($this->log_deal['deal_contain']['async_message']) && $this->log_deal['deal_contain']['async_message'] == $observer->message) {
                    $observerResult = $observer->onHandle($this->log_deal['deal_contain']['async_context']);
                    $observerExists = true;
                }
            }

            if (empty($observerExists)) {
                // 2.4 如果不存在这个观察者
                $this->dealSuccessContain();
                Log::warning(
                    '执行定时任务，读取日志文件来完成订阅与发布，发现第' . $this->log_deal['deal_contain']['line_num'] . '行找不到与其对应的观察者',
                    [
                        'async的message：' . $this->log_deal['deal_contain']['async_message'],
                        '日志路径为：' . $this->log_path
                    ]);
                $this->exception($this->log_deal['deal_contain']);
            } else if ($observerResult === null) {
                // 2.5 如果对应的观察者没有返回值
                $this->dealSuccessContain();
                Log::warning(
                    '执行定时任务，读取日志文件来完成订阅与发布，发现第' . $this->log_deal['deal_contain']['line_num'] . '行执行后没有返回值',
                    [
                        'async的message：' . $this->log_deal['deal_contain']['async_message'],
                        '日志路径为：' . $this->log_path
                    ]);
                $this->exception($this->log_deal['deal_contain']);
            } else if ($observerResult === false) {
                // 2.6 执行失败
                $this->dealSuccessContain();
                Log::error(
                    '执行定时任务，读取日志文件来完成订阅与发布，发现第' . $this->log_deal['deal_contain']['line_num'] . '行执行失败',
                    [
                        'async的message：' . $this->log_deal['deal_contain']['async_message'],
                        '日志路径为：' . $this->log_path
                    ]);
                $this->exception($this->log_deal['deal_contain']);
            } else if ($observerResult === true) {
                // 2.7 执行成功
                $this->log_deal['success_contain'] = [
                    'start_line' => $this->log_deal['success_contain']['start_line'] ?: $this->log_deal['deal_contain']['line_num'],
                    'end_line' => $this->log_deal['deal_contain']['line_num']
                ];
            }
        }
    }

    /**
     * 移除某一个观察者
     * @param $observerName
     * @return bool
     * @Author jiaWen.chen
     */
    public function removeObserver($observerName)
    {
        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() == $observerName) {
                array_splice($this->observers, $key, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * 绑定异常监听函数
     * @param $callback
     * @Author jiaWen.chen
     */
    public function onException($callback)
    {
        $this->exception_callback = $callback;
    }

    /**
     * 调用异常处理
     * @param $message
     * @Author jiaWen.chen
     */
    protected function exception($message)
    {
        if (!empty($this->exception_callback))
            ($this->exception_callback)($message);
    }

    /**
     * 格式化日志数据结构
     * @param $logStr
     * @param $lineNum
     * @Author jiaWen.chen
     */
    protected function handleBefore($logStr, $lineNum)
    {
        // 1. 如果到最后一行，则清空deal_contain
        if ($logStr == false) {
            $this->log_deal['deal_contain'] = [];
            return;
        }
        $logArr = explode(' ', $logStr);

        //2. 配置 deal_contain 的基础信息
        $this->log_deal['deal_contain'] = [
            'datetime' => trim(array_shift($logArr) . ' ' . array_shift($logArr), '[]'),
            'line_num' => $lineNum,
        ];

        $logInfo = explode('.', array_shift($logArr));
        $this->log_deal['deal_contain'] = array_merge($this->log_deal['deal_contain'], [
            'log_name' => $logInfo[0],
            'log_level' => $logInfo[1],
            'async_message' => array_shift($logArr),
            'async_context' => json_decode(json_decode(trim(implode(' ', $logArr), "[] \n\t"), true), true)
        ]);
    }

    /**
     * 对执行成功的日志进行合并输出
     * @Author jiaWen.chen
     */
    protected function dealSuccessContain()
    {
        if (!empty($this->log_deal['success_contain']['start_line']) && !empty($this->log_deal['success_contain']['end_line'])) {
            if ($this->log_deal['success_contain']['start_line'] == $this->log_deal['success_contain']['end_line']) {
                Log::notice("执行定时任务，读取日志文件来完成订阅与发布，第" . $this->log_deal['success_contain']['start_line'] . "行执行成功");
            } else {
                Log::notice("执行定时任务，读取日志文件来完成订阅与发布，第" . $this->log_deal['success_contain']['start_line'] . "行至" . $this->log_deal['success_contain']['end_line'] . "行执行成功");
            }
        }
        $this->log_deal['success_contain'] = [
            'start_line' => null,
            'end_line' => null
        ];
    }

    /**
     * 设置日志路径
     * @param string $time
     * @Author jiaWen.chen
     */
    protected function getLogPath($time = 'today')
    {
        $this->log_path = storage_path() . '/logs/async/async-' . date('Y-m-d', strtotime($time)) . '.log';
    }

}