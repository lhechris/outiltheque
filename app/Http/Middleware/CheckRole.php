<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->role || $user->role !== $role) {
                // Journalisation de la tentative d'accès non autorisé
                \Log::warning("Tentative d'accès non autorisé à la route avec le rôle : $role");

                return response()->json(['message' => 'Accès non autorisé.'], 403);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retournez une réponse 403 générique
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}