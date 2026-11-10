<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RapportController;

Route::get('/', function () {
    if (!auth()->check()) return redirect('/login');
    return auth()->user()->role === 'client'
        ? redirect('/client')
        : redirect('/admin');
});

// Rapport PDF (accessible sans authentification pour la démo)
Route::get('/rapport/pdf',      [RapportController::class, 'pdf'])->name('rapport.pdf');
Route::get('/rapport/database', [RapportController::class, 'database'])->name('rapport.database');

// Auth
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// Admin — protégé par middleware agent
$agent = \App\Http\Middleware\EnsureAgent::class;

Route::get( '/admin',              [AdminController::class, 'index'])->middleware($agent);
Route::get( '/admin/alerts',       [AdminController::class, 'alerts'])->middleware($agent);
Route::get( '/admin/prediction',   [AdminController::class, 'showPrediction'])->middleware($agent)->name('admin.prediction');
Route::post('/admin/upload',       [AdminController::class, 'uploadDataset'])->middleware($agent)->name('admin.upload');
Route::post('/admin/predict',      [AdminController::class, 'runPrediction'])->middleware($agent)->name('admin.predict');
Route::post('/admin/predict-3h',   [AdminController::class, 'predict3h'])->middleware($agent)->name('admin.predict3h');
Route::post('/admin/alerts/{id}/notify-agents', [AdminController::class, 'notifyAgents'])->middleware($agent)->name('admin.notify-agents');
Route::get( '/admin/zones',        [AdminController::class, 'zones'])->middleware($agent)->name('admin.zones');
Route::get( '/admin/coupures',     [AdminController::class, 'coupures'])->middleware($agent)->name('admin.coupures');
Route::get( '/admin/clients',      [AdminController::class, 'clients'])->middleware($agent)->name('admin.clients');
Route::get( '/admin/agents',          [AdminController::class, 'agents'])->middleware($agent)->name('admin.agents');
Route::get( '/admin/signalements',    [AdminController::class, 'signalements'])->middleware($agent)->name('admin.signalements');
Route::post('/admin/signalements/{id}/resolve', [AdminController::class, 'resolveSignalement'])->middleware($agent)->name('admin.signalements.resolve');
Route::post('/admin/agents',       [AdminController::class, 'storeAgent'])->middleware($agent)->name('admin.agents.store');
Route::get( '/admin/rapports',     [AdminController::class, 'rapports'])->middleware($agent)->name('admin.rapports');

// Dataset centralisé
Route::get( '/admin/dataset',              [DatasetController::class, 'index'])->middleware($agent)->name('dataset.index');
Route::post('/admin/dataset/weather',      [DatasetController::class, 'refreshWeather'])->middleware($agent)->name('dataset.weather');
Route::post('/admin/dataset/barrage',      [DatasetController::class, 'updateBarrage'])->middleware($agent)->name('dataset.barrage');
Route::put( '/admin/dataset/poste/{id}',   [DatasetController::class, 'updatePoste'])->middleware($agent)->name('dataset.poste');
Route::post('/admin/dataset/incident',     [DatasetController::class, 'declareIncident'])->middleware($agent)->name('dataset.incident');
Route::post('/admin/dataset/maintenance',  [DatasetController::class, 'declareMaintenance'])->middleware($agent)->name('dataset.maintenance');
Route::post('/admin/dataset/build',        [DatasetController::class, 'buildAndPredict'])->middleware($agent)->name('dataset.build');

// Client — protégé par middleware client
$client = \App\Http\Middleware\EnsureClient::class;

Route::get( '/client',            [ClientController::class, 'index'])->middleware($client);
Route::get( '/client/alerts',     [ClientController::class, 'alerts'])->middleware($client);
Route::post('/client/set-lang',   [ClientController::class, 'setLanguage'])->middleware($client)->name('client.set-lang');
Route::get( '/client/recharge',   [ClientController::class, 'recharge'])->middleware($client)->name('client.recharge');
Route::post('/client/recharge',   [ClientController::class, 'processRecharge'])->middleware($client)->name('client.recharge.post');
Route::post('/client/incidents',        [ClientController::class, 'storeIncident'])->middleware($client)->name('client.incidents.store');
Route::post('/client/voice-transcribe', [ClientController::class, 'transcribeVoice'])->middleware($client)->name('client.voice-transcribe');

// API : alertes non lues pour le client (appelée en JS pour notification vocale)
Route::get('/api/client/alerts/latest', [ClientController::class, 'latestAlert'])->middleware($client)->name('client.latest-alert');
