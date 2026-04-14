<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FaceLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('face.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string'
        ]);

        $descriptor = $request->descriptor;

        $users = User::whereNotNull('face_descriptor')->get();

        /** @var \App\Models\User $user */
        foreach ($users as $user) {
            if ($user->face_descriptor === $descriptor) {
                Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
            'message' => 'Authentification faciale réussie ! Bienvenue sur CyberGuard.'
        ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Visage non reconnu. Veuillez réessayer ou utiliser l\'authentification OTP.'
        ]);
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update([
            'face_descriptor' => $request->descriptor
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visage enregistré avec succès. Vous pouvez maintenant utiliser l\'authentification faciale.'
        ]);
    }
}