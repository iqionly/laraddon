<?php declare(strict_types=1);

namespace Laraddon\Interfaces;

interface Initiable {
    public function init(): self;
}