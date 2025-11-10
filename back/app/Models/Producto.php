<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'categoria','name','description','price','image','unit','active','ord'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
}
