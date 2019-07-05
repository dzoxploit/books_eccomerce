<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnBooks extends Model
{
    protected $table = 'return_books';

    protected $primaryKey = 'id_return_books';
    
    protected $fillable = [
       'id_return_books', 'id_transaction', 'tgl_pengembalian','status_return_books','created_at','updated_at' 
    ];

    protected $hidden = [
        'password', 'remember_token'
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