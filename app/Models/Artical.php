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
    public function format(){
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'type'=>$this->type,
            'university_name'=>$this->university_name,
            'file_url'=>$this->file_url,
            'created_at'=>$this->created_at->diffForHumans(),
            'download_number'=>$this->download_number,
            'writer'=>$this->writer,
            'doctor'=>$this->doctor,
            'field'=>$this->field,
            'banned'=>$this->banned,
            'approved'=>$this->approved,
            'comments'=>$this->comments->map->format(),
        ];
    }
    public function approved()
    {
        return $this->hasOne(ApprovedArtical::class, 'artical_id');
    }public function bannd()
    {
        return $this->hasOne(BannedArtical::class, 'artical_id');
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
    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
