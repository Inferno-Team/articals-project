<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'artical_id',
        'problem'
    ];
    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }
    public function artical()
    {
        return $this->hasOne(Artical::class, 'artical_id');
    }
}
