<?php

namespace Iqionly\Laraddon\Interfaces;

use Iqionly\Laraddon\Core;

abstract class Module {
    protected string $path;
    protected array $attributes = [
        'base' => '',
        'routes' => [],
    ];
    protected string $class;

    public function __construct(string $class, string $path) {
        $this->class = rtrim($class, '\\');
        $this->path = $path;
        $this->setAttributes();
    }

    public function __toString(): string {
        return $this->attributes['base'];
    }

    private function setAttributes(): void {
        $this->attributes['base'] = '/' . Core::camelToUnderscore($this->class, '-');

        if (file_exists($this->path . '/init.php')) {
            $mergeAttribute = require_once $this->path . '/init.php';
            $this->attributes = array_merge($this->attributes, $mergeAttribute);
        }
    }
}