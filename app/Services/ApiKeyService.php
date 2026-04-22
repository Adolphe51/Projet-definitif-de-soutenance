<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ApiKeyService
{
    /**
     * Chiffre et stocke une clé API de manière sécurisée
     */
    public static function encrypt(string $apiKey): string
    {
        try {
            return Crypt::encryptString($apiKey);
        } catch (\Exception $e) {
            Log::error('Failed to encrypt API key', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Déchiffre une clé API stockée
     */
    public static function decrypt(string $encryptedKey): string
    {
        try {
            return Crypt::decryptString($encryptedKey);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt API key', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Génère une nouvelle clé API avec préfixe pour identification
     */
    public static function generate(string $prefix = 'app'): string
    {
        $key = $prefix . '_' . bin2hex(random_bytes(32));
        return $key;
    }

    /**
     * Masque une clé API pour affichage (montre seulement les 8 derniers caractères)
     */
    public static function mask(string $apiKey): string
    {
        $length = strlen($apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        return str_repeat('*', $length - 8) . substr($apiKey, -8);
    }

    /**
     * Valide le format d'une clé API
     */
    public static function validate(string $apiKey): bool
    {
        return preg_match('/^[a-z0-9_]+$/', $apiKey) && strlen($apiKey) >= 40;
    }
}
