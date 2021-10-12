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
class CreateDir extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('auv:create_dir');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle(): void
    {
        if (! $dirs = config('auv_dir')) {
            $this->line('配置文件不存在，生成命令：php bin/hyperf.php vendor:publish auv/devtool', 'erroe');
        }

        foreach ($dirs as $rawName) {
            if ($this->alreadyExists($rawName)) {
                $this->line(sprintf('目录已经存在：%s', $rawName), 'comment');
                continue;
            }
            $path = $this->makeDirectory($rawName);
            $this->line(sprintf('目录创建成功：%s', $path), 'info');
        }
    }

    protected function alreadyExists(string $rawName): bool
    {
        return is_dir(BASE_PATH . $rawName);
    }

    protected function makeDirectory(string $rawName): string
    {
        mkdir(BASE_PATH . $rawName, 0777, true);
        return $rawName;
    }
}
