<?php

namespace CjwAsync\Src\AsyncLogHandle;
use CjwAsync\Src\Log\GeneratorLog;

/**
 * Class AsyncLogObserver
 * @package CjwAsync\Src\AsyncLogHandle
 */
interface AsyncLogObserver
{
    /**
     * 绑定处理方法
     * @param $context
     * @return mixed
     * @Author jiaWen.chen
     */
    public function onHandle($context);

    /**
     * 订阅消息内容
     * @param array $context
     * @Author jiaWen.chen
     */
    public function subscribe(array $context);
}