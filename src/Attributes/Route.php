<?php declare(strict_types=1);

namespace Laraddon\Attributes;

interface Route {
    public function __construct(string $get = '/', string $post = '/', string $put = '/', string $delete = '/');
}