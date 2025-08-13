<?php declare(strict_types=1);

namespace Laraddon\Bus;

use Laraddon\Interfaces\Module as ModuleInterface;
use Symfony\Component\Finder\Finder;

final class Module extends ModuleInterface
{
    protected Finder $filesystem;

    public function __construct(string $class, string $path)
    {
        parent::__construct($class,$path);
        $this->filesystem = new Finder();
        $this->setModels();

    }

    public function setModels()
    {
        
    }

    /**
     * @var array<int,string> $models
     */
    protected $models = [];

    public function loadModels()
    {
        
    }
}