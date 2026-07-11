<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class IdeHelperCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'ide:helper';
    }

    public function description(): string
    {
        return 'Generate IDE helper stubs for framework services.';
    }

    public function handle(Input $input, Output $output): int
    {
        $path = $input->option('path', '_ide_helper.php');
        $path = $this->app->basePath(is_string($path) ? $path : '_ide_helper.php');
        $this->files->put($path, $this->contents());
        $output->line('IDE helper written: ' . $path);

        return 0;
    }

    private function contents(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace WTD\IdeHelper;

/**
 * @method static \WTD\Routing\Router router()
 * @method static \WTD\Config\Repository config()
 * @method static \WTD\Database\DatabaseManager database()
 * @method static \WTD\Cache\CacheManager cache()
 * @method static \WTD\Storage\StorageManager storage()
 */
final class App
{
}

PHP;
    }
}
