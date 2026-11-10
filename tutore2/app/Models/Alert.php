<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone', 'quartier', 'start_at', 'end_at', 'reason',
        'cause_probable', 'poste_id', 'cluster', 'probability', 'status',
        'agents_notified_at', 'prediction_mode',
    ];

    protected $casts = [
        'start_at'           => 'datetime',
        'end_at'             => 'datetime',
        'probability'        => 'integer',
        'agents_notified_at' => 'datetime',
    ];

    // ── Scopes réutilisables ──────────────────────────────────────

    public function scopeFutures(Builder $q): Builder
    {
        // Inclut : alertes à venir ET alertes 3H en cours (start_at passé mais end_at futur)
        return $q->where(function ($q2) {
            $q2->where('start_at', '>=', now())
               ->orWhere(function ($q3) {
                   $q3->where('start_at', '<=', now())
                      ->where('end_at', '>=', now());
               });
        });
    }

    public function scopeUrgentes(Builder $q): Builder
    {
        return $q->where('probability', '>=', 80);
    }

    public function scopeForZone(Builder $q, ?string $zone): Builder
    {
        return $zone ? $q->where('zone', $zone) : $q;
    }

    public function scopeEnCours(Builder $q): Builder
    {
        return $q->where('start_at', '<=', now())
                 ->where(fn($s) => $s->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isUrgent(): bool
    {
        return $this->probability >= 80;
    }

    public function levelLabel(): string
    {
        return match(true) {
            $this->probability >= 80 => 'Urgent',
            $this->probability >= 60 => 'Probable',
            default                  => 'Possible',
        };
    }

    public function levelColor(): string
    {
        return match(true) {
            $this->probability >= 80 => 'var(--red)',
            $this->probability >= 60 => 'var(--yellow)',
            default                  => 'var(--green)',
        };
    }
}
