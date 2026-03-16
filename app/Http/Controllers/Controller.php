<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Retourne une réponse JSON standard pour succès.
     */
    protected function successResponse($data = [], string $message = 'Opération réussie', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Retourne une réponse JSON standard pour erreur.
     */
    protected function errorResponse(string $message = 'Une erreur est survenue', int $status = 400, $data = []): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Valide les données avec des règles et retourne les erreurs JSON si échoue.
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validated = $request->validate($rules, $messages);
        return $validated;
    }

    /**
     * Méthode générique pour pagination JSON.
     */
    protected function paginate($query, int $perPage = 15): JsonResponse
    {
        $paginated = $query->paginate($perPage);
        return $this->successResponse($paginated);
    }

    /**
     * Méthode utilitaire pour vérifier si une requête est AJAX.
     */
    protected function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}