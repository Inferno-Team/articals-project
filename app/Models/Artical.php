<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artical extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'field_id',
        'doctor_id',
        'type',
        'university_name',
        'writer_id',
        'file_url'
    ];
    public function approved()
    {
        return $this->hasOne(ApprovedArtical::class, 'artical_id', 'id');
    }public function bannd()
    {
        return $this->hasOne(BannedArtical::class, 'artical_id', 'id');
    }
    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
}
