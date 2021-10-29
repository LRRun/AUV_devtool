<?php


namespace AUV_devtool\Log;


use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Psr\Log\LoggerInterface;
use Throwable;

class AuvLogger
{

    /**
     * 获取日志对象
     * @param string $topic
     * @return LoggerInterface
     */
    public static function get(string $topic = 'default'): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($topic);
    }


    /**
     * 订单日志
     * @param string $business 场景，例如：open/ele/miniapp
     * @param int $orderId 订单号
     * @param string $explain 中文说明
     * @param array $info 要打印的内容
     * @param string $level 打印级别:DEBUG INFO NOTICE WARNING ERROR CRITICAL ALERT EMERGENCY
     * @param array $extension 扩展打印内容
     * @param string $requestId 请求ID,不传递则自动获取协程上下文中的requestId
     */
    public static function order(string $business, int $orderId, string $explain, array $info = [], string $level = 'info', array $extension = [], string $requestId = '')
    {
        $requestId = !empty($requestId) ? $requestId : Context::get('requestId');
        self::get('order')->$level(
            self::splicingStatement(
                $requestId,
                $business,
                $orderId,
                $explain,
                Json::encode($info)
            )
            , $extension);// 扩展打印内容
    }


    /**
     * @param string $business 异常场景：Controller/Service/Db/Middleware/Utils
     * @param Throwable $exception 捕获的异常
     * @param string $fileName 捕获异常的文件
     * @param int $lineNone 捕获异常的行号
     * @param string $level 日志级别:DEBUG INFO NOTICE WARNING ERROR CRITICAL ALERT EMERGENCY
     * @param array $extension 扩展打印内容
     * @param string $requestId 请求ID,不传递则自动获取协程上下文中的requestId
     */
    public static function exception(string $business, Throwable $exception, string $fileName, int $lineNone, string $level = 'error', array $extension = [], string $requestId = '')
    {
        $requestId = !empty($requestId) ? $requestId : Context::get('requestId');
        self::get('exception')->$level(
            self::splicingStatement(
                $requestId,
                $business,
                $fileName,
                $lineNone,
                (string)$exception->getMessage()
            )
            , $extension);// 扩展打印内容
    }


    /**
     * SQL日志
     * @param float $time
     * @param string $sql
     * @param string $level
     * @param array $extension
     * @param string $requestId
     */
    public static function sql(float $time, string $sql, string $level = 'info', array $extension = [], $requestId = '')
    {
        $requestId = !empty($requestId) ? $requestId : Context::get('requestId');
        self::get('sql')->$level(
            self::splicingStatement(
                $requestId,
                $time,
                $sql
            )
            , $extension);// 扩展打印内容
    }

    /**
     * 获取分隔符
     * @return string
     */
    private static function getContentBreak(): string
    {
        return config('auv_config.auv_log.logcontent_break') ?? '|=|';
    }


    /**
     * 拼接语句
     * @return string
     */
    private static function splicingStatement(): string
    {
        $statement = '';
        $params = \func_get_args();
        foreach ($params as $item) {
            $statement .= self::getContentBreak() . (string)$item;
        }
        $statement .= self::getContentBreak();
        return $statement;
    }
}