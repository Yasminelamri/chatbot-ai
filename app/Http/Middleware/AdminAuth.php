<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est authentifié comme admin
        $adminPassword = env('ADMIN_PASSWORD', 'jadara2024');
        
        // Vérifier la session admin
        if (session('admin_authenticated')) {
            return $next($request);
        }
        
        // Si une tentative de connexion admin
        if ($request->has('admin_password')) {
            if ($request->input('admin_password') === $adminPassword) {
                session(['admin_authenticated' => true]);
                return redirect()->route('admin.faq')->with('success', 'Connexion réussie !');
            } else {
                return back()->withErrors(['admin_password' => 'Mot de passe incorrect'])->withInput();
            }
        }
        
        // Afficher la page de connexion admin
        return response()->view('admin.login');
    }
}
