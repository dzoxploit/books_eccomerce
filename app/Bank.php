<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'bank';

    protected $primaryKey = 'id_bank';
    
    protected $fillable = [
       'id_bank', 'nama_bank', 'alamat', 'no_telepon' 
    ];


    // public function getShortContentAttribute()
    // {
    //     return str_limit($this->content, rand(60,150));
    // }
    // public function getPublishedAtAttribute($dates)
    // {
    //     return $dates->diffForHumans(); // Use whatever you want here to format the date, this is just an example
    // }
}