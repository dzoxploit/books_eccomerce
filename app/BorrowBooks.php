<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BorrowBooks extends Model
{
    protected $table = 'borrowing_books';

    protected $primaryKey = 'id_borrowing_books';
    
    protected $fillable = [
       'id_borrowing_books', 'id_users', 'tgl_peminjaman','tgl_pengembalian', 'total_price', 'status_payment','status_borrow'
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