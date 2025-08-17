<?php declare(strict_types=1);

namespace Laraddon\Interfaces\Databases\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DefaultState extends Type {
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getEnumDeclarationSQL($column);
    }
}