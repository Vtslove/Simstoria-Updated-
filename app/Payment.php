<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];
    
    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }

    public function reward(){
        return $this->belongsTo(Reward::class);
    }
    
    public function scopeSuccess($query){
        return $query->whereStatus('success');
    }
    public function scopePending($query){
        return $query->whereStatus('pending');
    }
}
