<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'poste_id', 'zone', 'quartier', 'declared_by', 'declared_at',
        'source_type', 'type', 'description', 'severity', 'resolved', 'resolved_at',
    ];

    protected $casts = [
        'declared_at'  => 'datetime',
        'resolved_at'  => 'datetime',
        'resolved'     => 'boolean',
    ];

    public function declarant()
    {
        return $this->belongsTo(User::class, 'declared_by');
    }

    public function posteDonnee()
    {
        return $this->belongsTo(PosteDonnee::class, 'poste_id', 'poste_id');
    }
}
