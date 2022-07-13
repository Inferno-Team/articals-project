<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedArtical extends Model
{
    use HasFactory;
    protected $fillable = [
        'approver_id',
        'artical_id'
    ];
    public function format(){
        return [
            'id'=>$this->id,
            'artical_id'=>$this->artical_id,
            'created_at'=>$this->created_at->diffForHumans(),
            'artical'=>$this->artical->format(),
        ];
    }
    public function approver()
    {
        return $this->hasOne(User::class, 'approver_id');
    }
    public function artical()
    {
        return $this->hasOne(Artical::class,'id', 'artical_id');
    }
}
