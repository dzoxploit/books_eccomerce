<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detail_borrow_books extends Model
{
    protected $table = 'detail_borrowing_books';

    protected $primaryKey = 'id_detail_borrowing';
    
    protected $fillable = [
       'id_borrowing_books', 'id_users', 'status_transaction','id_books', 'tgl_peminjaman', 'tgl_pengembalian', 'status_penalty', 'status_condition_books', 'status_return','total_price' 
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