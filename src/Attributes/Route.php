<?php

namespace Laraddon\Attributes;

interface Route {
    public function __construct(string $get = '/', string $post = '/', string $put = '/', string $delete = '/');
}