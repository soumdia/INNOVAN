<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumptionLog extends Model
{
    protected $fillable = [
        'user_id', 'log_date', 'kwh', 'amount_fcfa', 'balance_before', 'balance_after',
    ];

    protected $casts = [
        'log_date'      => 'date',
        'kwh'           => 'decimal:2',
        'amount_fcfa'   => 'decimal:2',
        'balance_before'=> 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
