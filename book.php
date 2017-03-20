<?php
namespace App;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * User: fff
 * Date: 20/3/17
 * Time: PM1:55
 */
class book extends Eloquent
{
    use SoftDeletes;
    protected $table = 'books';
    protected $guarded = [];
}