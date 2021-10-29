<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace AUV_devtool\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
#[Command]
class AuvInit extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('auv:init');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('AUV项目初始化');
    }

    public function handle(): void
    {
        if (!$dirs = config('auv_config.auv_dir')) {
            $this->line('配置文件不存在，生成命令：php bin/hyperf.php vendor:publish auv/devtool', 'error');
            return;
        }

        $this->line('准备创建AUV目录结构', 'comment');

        foreach ($dirs as $rawName) {
            if ($this->dirAlreadyExists($rawName)) {
                $this->line(sprintf('目录已经存在：%s,跳过', $rawName), 'comment');
                continue;
            }
            $path = $this->makeDirectory($rawName);
            $this->line(sprintf('目录创建成功：%s', $path), 'info');
        }
        $this->line('创建AUV目录结构完成', 'info');

        $this->line('准备删除不必要的文件', 'comment');

        $fileName = BASE_PATH . '/Listener/DbQueryExecutedListener.php';
        $unlinkFail = false;

        if (file_exists($fileName)) {
            try {
                unlink($fileName);
            } catch (\Throwable $exception) {
                $unlinkFail = true;
                $this->line('删除文件失败[' . $exception->getMessage() . '],请稍后手动删除文件:' . $fileName, 'error');
            }
        }
        if ($unlinkFail) $this->line('文件删除完成', 'comment');
    }

    protected function dirAlreadyExists(string $rawName): bool
    {
        return is_dir(BASE_PATH . $rawName);
    }

    protected function makeDirectory(string $rawName): string
    {
        mkdir(BASE_PATH . $rawName, 0777, true);
        return $rawName;
    }
}
