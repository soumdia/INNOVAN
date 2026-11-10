<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = [
        'poste_id', 'zone', 'performed_by', 'performed_at', 'type', 'notes',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function posteDonnee()
    {
        return $this->belongsTo(PosteDonnee::class, 'poste_id', 'poste_id');
    }
}
