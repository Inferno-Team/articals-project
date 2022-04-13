<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedArtical extends Model
{
    use HasFactory;
    protected $fillable = [
        'ban_id',
        'artical_id'
    ];
    public function ban()
    {
        return $this->hasOne(User::class, 'ban_id');
    }
    public function artical()
    {
        return $this->hasOne(Artical::class, 'artical_id');
    }
}
