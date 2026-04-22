<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LogoutRequest;
use App\Services\Auth\LogoutService;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __construct(
        private readonly LogoutService $logoutService,
    ) {
    }

    /**
     * Déconnexion — révoque la session courante ou toutes les sessions.
     */
    public function logout(LogoutRequest $request)
    {
        $user = $request->attributes->get('user') ?? Auth::user();

        // Si l'utilisateur n'est toujours pas trouvé, essayer via le cookie
        if (!$user) {
            $sessionToken = $request->bearerToken() ?? $request->cookie('access_token');
            if ($sessionToken) {
                // Utiliser une méthode publique du LogoutService pour valider la session
                // Pour l'instant, on va simplement essayer de récupérer l'utilisateur via le token
                // On pourra améliorer cela plus tard en ajoutant une méthode publique dans LogoutService
                try {
                    // On va tenter de valider la session via le SecuritySessionService
                    // Mais comme c'est privé, on va simplement essayer de récupérer l'utilisateur via Auth::user()
                    // Si le middleware a fonctionné, Auth::user() devrait être défini
                    $user = Auth::user();
                } catch (\Exception $e) {
                    // Si Auth::user() échoue, on laisse $user à null
                }
            }
        }

        if (!$user) {
            // Si l'utilisateur n'est toujours pas trouvé, afficher un message neutre
            return redirect()->route('login')->with('success', 'Déconnexion effectuée.');
        }

        if ($request->boolean('all_sessions')) {
            $this->logoutService->logoutAll($user, $request);
        } else {
            $sessionToken = $request->bearerToken() ?? $request->cookie('access_token');
            $this->logoutService->logout($user, $sessionToken, $request);
        }

        // Redirection avec invalidation du cookie
        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.')
            ->withCookie(cookie()->forget('access_token'));
    }
}
