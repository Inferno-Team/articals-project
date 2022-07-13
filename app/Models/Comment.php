<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'comment',
        'user_id',
        'artical_id'
    ];
    public function format()
    {
        return [
            'id' => $this->id,
            'time' => $this->created_at->diffForHumans(),
            'user'=>$this->user,
            'comment'=>$this->comment,
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function article()
    {
        return $this->belongsTo(Artical::class, 'article_id');
    }
}
