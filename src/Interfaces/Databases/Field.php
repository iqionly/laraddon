<?php declare(strict_types=1);

namespace Laraddon\Interfaces\Databases;

use Attribute;

#[Attribute]
final class Field
{   
    /** @codeCoverageIgnore */
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?string $name,
        public readonly ?bool $nullable = false,
        public readonly ?bool $unique = false,
        public readonly ?bool $primary = false,
        public readonly ?bool $autoIncrement = false,
        public readonly ?string $comment = '',
        public readonly ?array $enum = [],
        public readonly mixed $default = null,
        ...$args
    ){
        $this->type = $type ?? $args[0];
    }
}