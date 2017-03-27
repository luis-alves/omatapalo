<?php
namespace Src\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class Compras extends Model
{
    protected $table = 'custos';

    protected $casts = [
      'valor' => 'float'
    ];
    // protected $connection = "db";
}
