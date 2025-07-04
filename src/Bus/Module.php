<?php

namespace Iqionly\Laraddon\Bus;

use Iqionly\Laraddon\Interfaces\Module as ModuleInterface;

final class Module extends ModuleInterface
{
    public function getPath(): string
    {
        return $this->path;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}