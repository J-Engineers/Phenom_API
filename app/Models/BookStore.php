<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookStore extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = "book_store";

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'store_user_id',
        'book_name',
        'book_author_name',
        'book_isbn',
        'book_cover',
        'book_category',
        'book_quantity',
        'book_price',
        'book_description',
        'status',
    ];

    /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
       
    ];

    /**
    * The attributes that should be cast.
    *
    * @var array<string, string>
    */
    protected $casts = [
       
    ];
}
