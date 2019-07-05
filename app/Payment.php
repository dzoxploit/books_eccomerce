<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';
    
    protected $fillable = [
       'id_pembayaran','id_borrowing_books','id_users','id_bank', 'no_rekening','atas_nama', 'status','created_at','updated_at' 
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