<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slider_product extends Model
{
    protected $table = 'slider_product';

    protected $primaryKey = 'id_slider_product';
    
    protected $fillable = [
       'id_slider_product', 'id_books', 'path_image' 
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