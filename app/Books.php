<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    protected $table = 'books';

    protected $primaryKey = 'id_books';
    
    protected $fillable = [
       'id_books', 'title', 'author','description', 'quantity', 'main_price', 'daily_price', 'penalty_price', 'created_at','updated_at' 
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