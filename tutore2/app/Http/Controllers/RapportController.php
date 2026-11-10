<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Alert;
use App\Models\User;
use App\Models\ConsumptionLog;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function pdf()
    {
        $stats = [
            'clients'       => User::where('role', 'client')->count(),
            'agents'        => User::where('role', 'agent')->count(),
            'alerts_total'  => Alert::count(),
            'alerts_urgent' => Alert::where('probability', '>=', 80)->count(),
            'zones'         => Alert::distinct()->count('zone'),
            'avg_prob'      => (int) round(Alert::avg('probability') ?? 0),
        ];

        $pdf = Pdf::loadView('rapport.pdf', compact('stats'))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('INNOVAN-CI_Rapport_Projet_2026.pdf');
    }

    public function database()
    {
        $stats = [
            'users'             => User::count(),
            'clients'           => User::where('role', 'client')->count(),
            'agents'            => User::where('role', 'agent')->count(),
            'alerts'            => Alert::count(),
            'alerts_3h'         => Alert::where('prediction_mode', '3h')->count(),
            'postes'            => \App\Models\PosteDonnee::count(),
            'incidents'         => \App\Models\Incident::count(),
            'incidents_clients' => \App\Models\Incident::where('source_type', 'client')->count(),
            'maintenances'      => \App\Models\Maintenance::count(),
            'consumption_logs'  => ConsumptionLog::count(),
        ];

        $pdf = Pdf::loadView('rapport.database', compact('stats'))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('INNOVAN-CI_Base_de_Donnees_2026.pdf');
    }
}
