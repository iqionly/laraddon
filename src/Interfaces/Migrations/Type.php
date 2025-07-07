<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Interfaces\Migrations;

abstract class Type {

    /**
     * Column name
     *
     * @var string
     */
    protected string $column;

    /**
     * Column length
     *
     * @var int
     */
    protected int $length;

    /**
     * Column name
     *
     * @var string
     */
    protected string $query_type = '';

    public function __construct(string $column, int $length = 255) {
        $this->column = $column;
        $this->length = $length;
    }
    
    /**
     * Type string
     *
     * @param  string $column
     * @param  int $length
     * @param  string $default
     * @return StringType
     */
    public static function string(string $column, int $length = 255, string $default = ''): StringType {
        $stringType = new StringType($column, $length);
        $stringType->query_type = $column . ' VARCHAR(' . $length . ')';
        return $stringType;
    }
    
    /**
     * Type integer
     *
     * @param  string  $column
     * @return IntegerType
     */
    public static function integer(string $column): IntegerType {
        $integerType = new IntegerType($column);
        $integerType->query_type = $column . ' INT';
        return $integerType;
    }
}