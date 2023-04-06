<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $table = "categories";
    
    //relacion de uno a muchos
    public function posts(){
        return $this->hasMany('app/models/post');
    }

    use HasFactory;
}
