<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ConsumptionLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return Auth::user()->role === 'agent'
                ? redirect()->intended('/admin')
                : redirect()->intended('/client');
        }

        return back()
            ->withErrors(['email' => 'Identifiants incorrects. Vérifiez votre e-mail et mot de passe.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone'        => ['nullable', 'string', 'max:25'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'zone'         => ['required', 'string', 'max:100'],
            'meter_number' => ['required', 'string', 'max:20', 'unique:users,meter_number'],
            'address'      => ['nullable', 'string', 'max:255'],
            'language'     => ['nullable', 'in:fr,dioula,baoule,bete,nouchi'],
        ], [
            'name.required'         => 'Le nom complet est obligatoire.',
            'email.required'        => 'L\'adresse e-mail est obligatoire.',
            'email.unique'          => 'Cet e-mail est déjà associé à un compte.',
            'password.min'          => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'    => 'Les mots de passe ne correspondent pas.',
            'zone.required'         => 'Veuillez sélectionner votre zone / commune.',
            'meter_number.required' => 'Le numéro de compteur est obligatoire.',
            'meter_number.unique'   => 'Ce numéro de compteur est déjà enregistré.',
        ]);

        $dailyKwh = round(mt_rand(80, 220) / 10, 1); // 8.0–22.0 kWh/jour propre à ce client

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'] ?? null,
            'password'     => $data['password'],
            'role'         => 'client',
            'zone'         => $data['zone'],
            'meter_number' => $data['meter_number'],
            'address'      => $data['address'] ?? null,
            'language'     => $data['language'] ?? 'fr',
            'daily_kwh'    => $dailyKwh,
            'balance'      => 0,
        ]);

        $this->seedConsumptionHistory($user);

        Auth::login($user);

        return redirect('/client')->with('welcome', 'Bienvenue sur votre espace CIE, ' . $user->name . ' !');
    }

    private function seedConsumptionHistory(User $user): void
    {
        $balance = (float) mt_rand(8000, 25000); // solde de départ simulé
        for ($i = 29; $i >= 0; $i--) {
            $date      = today()->subDays($i);
            $variation = 1 + (mt_rand(-15, 15) / 100);
            $kwh       = round($user->daily_kwh * $variation, 2);
            $amount    = round($kwh * 35, 2);
            $before    = $balance;
            $balance   = max(0.0, $balance - $amount);

            ConsumptionLog::create([
                'user_id'        => $user->id,
                'log_date'       => $date,
                'kwh'            => $kwh,
                'amount_fcfa'    => $amount,
                'balance_before' => $before,
                'balance_after'  => $balance,
            ]);
        }
        $user->update(['balance' => $balance]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
