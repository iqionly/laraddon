<?php declare(strict_types=1);

namespace Laraddon\Bus;

use Exception;
use Laraddon\Interfaces\Module as ModuleInterface;
use Symfony\Component\Finder\Finder;

final class Module extends ModuleInterface
{
    protected Finder $finder;

    /**
     * @var array<int,string> $models
     */
    protected $models = [];

    public function __construct(string $class, string $path)
    {
        parent::__construct($class,$path);
        $this->finder = new Finder();
        $this->setModels($path . '/Models');

    }

    protected function createDir(string $path): void 
    {
        if(!is_dir($path)) {
            mkdir($path, 0775);
        }
    }

    protected function setModels(string $pathModels): void
    {
        try{
            $this->createDir($pathModels);
            $files = $this->finder->name('/\.php$/')->in($pathModels);
            foreach ($files as $file) {
                // ltrim($file->getPath(), $pathModels)
                $this->models[rtrim($file->getRelativePathname(), '.php')] = $file->getPathname();
            }
        } catch (Exception $e) {

        }
    }

    protected function loadModels()
    {
        
    }
}