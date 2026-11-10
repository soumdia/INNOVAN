<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosteDonnee extends Model
{
    protected $table = 'poste_donnees';

    protected $fillable = [
        'poste_id', 'zone', 'quartier', 'cluster', 'latitude', 'longitude',
        'charge_mw', 'capacite_mw', 'source_reseau',
        'niveau_barrage_pct', 'source_barrage',
        'temperature_c', 'humidite_pct', 'pluviometrie_norm', 'temp_max_24h',
        'source_meteo', 'meteo_updated_at',
        'age_cable_ans', 'maintenance_recente', 'derniere_maintenance_at',
        'nb_incidents_7j', 'incidents_moy_3j',
        'donnees_updated_at',
    ];

    protected $casts = [
        'maintenance_recente'   => 'boolean',
        'meteo_updated_at'      => 'datetime',
        'derniere_maintenance_at' => 'datetime',
        'donnees_updated_at'    => 'datetime',
        'charge_mw'             => 'float',
        'capacite_mw'           => 'float',
        'niveau_barrage_pct'    => 'float',
        'temperature_c'         => 'float',
        'humidite_pct'          => 'float',
        'pluviometrie_norm'     => 'float',
        'temp_max_24h'          => 'float',
        'age_cable_ans'         => 'float',
        'incidents_moy_3j'      => 'float',
    ];

    public function getTauxUtilisationAttribute(): float
    {
        return $this->capacite_mw > 0 ? round($this->charge_mw / $this->capacite_mw, 4) : 0.5;
    }

    public function getStressHydrauliqueAttribute(): float
    {
        return round((100 - $this->niveau_barrage_pct) / 100, 4);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'poste_id', 'poste_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'poste_id', 'poste_id');
    }

    // Recalcule nb_incidents_7j et incidents_moy_3j depuis la table incidents
    public function recalculerIncidents(): void
    {
        $now   = now();
        $inc7j = Incident::where('poste_id', $this->poste_id)
            ->where('declared_at', '>=', $now->copy()->subDays(7))
            ->count();
        $inc3j = Incident::where('poste_id', $this->poste_id)
            ->where('declared_at', '>=', $now->copy()->subDays(3))
            ->count();

        $this->update([
            'nb_incidents_7j'  => $inc7j,
            'incidents_moy_3j' => round($inc3j / 3, 2),
        ]);
    }

    // Vérifie si la maintenance récente doit être réinitialisée (>7 jours)
    public function verifierMaintenanceRecente(): void
    {
        if ($this->maintenance_recente && $this->derniere_maintenance_at) {
            if ($this->derniere_maintenance_at->diffInDays(now()) >= 7) {
                $this->update(['maintenance_recente' => false]);
            }
        }
    }
}
