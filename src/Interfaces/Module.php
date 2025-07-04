<?php

namespace Iqionly\Laraddon\Interfaces;

use Iqionly\Laraddon\Core;

abstract class Module {
    protected string $path;
    protected array $attributes = [
        'base' => '',
        'routes' => [],
        'api_routes' => false,
        'middleware_scopes' => [],
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

    public function getClass(): string {
        return $this->class;
    }

    public function getName(): string {
        return preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $this->attributes['base']);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getApiRoutesAttribute(): bool
    {
        return $this->attributes['api_routes'] ?? false;
    }

    private function setAttributes(): void {
        $this->attributes['base'] = '/' . Core::camelToUnderscore(basename($this->class), '-');

        if (file_exists($this->path . '/init.php')) {
            $mergeAttribute = require_once $this->path . '/init.php';
            $this->attributes = array_merge($this->attributes, $mergeAttribute);
        }
    }
}