<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rejectbooks extends Model
{
    protected $table = 'reject_books';

    protected $primaryKey = 'id_reject_books';
    
    protected $fillable = [
       'id_books', 'id_books','quantity_reject','created_at','updated_at' 
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