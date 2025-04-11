<?php

namespace Iqionly\Laraddon\ExampleAddon\Models;

class AddonTestRelationModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'test_relation_table';
    protected $fillable = ['id', 'test_relation_table_id', 'column1', 'column2'];
}