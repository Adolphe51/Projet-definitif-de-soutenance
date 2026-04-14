<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // ================= USERS =================
            ['nom' => 'users.create', 'description' => 'Créer un compte utilisateur', 'ressourceType' => 'Utilisateurs'],
            ['nom' => 'users.list', 'description' => 'Consulter la liste des comptes utilisateurs', 'ressourceType' => 'Utilisateurs'],
            ['nom' => 'users.edit', 'description' => 'Modifier un compte utilisateur', 'ressourceType' => 'Utilisateurs'],
            ['nom' => 'users.deactivate', 'description' => 'Désactiver un compte utilisateur', 'ressourceType' => 'Utilisateurs'],

            // ================= ALERTS =================
            ['nom' => 'alerts.list', 'description' => 'Consulter les alertes', 'ressourceType' => 'Alertes'],
            ['nom' => 'alerts.create', 'description' => 'Créer une alerte', 'ressourceType' => 'Alertes'],
            ['nom' => 'alerts.edit', 'description' => 'Modifier les alertes', 'ressourceType' => 'Alertes'],
            ['nom' => 'alerts.export', 'description' => 'Exporter les alertes', 'ressourceType' => 'Alertes'],

            // ================= LOGS / AUDIT =================
            ['nom' => 'audit.view', 'description' => 'Consulter le journal d\'audit', 'ressourceType' => 'Audit'],
            ['nom' => 'audit.export', 'description' => 'Exporter le journal d\'audit', 'ressourceType' => 'Audit'],

            // ================= ADMINISTRATION =================
            ['nom' => 'admin.roles', 'description' => 'Gérer les rôles et permissions', 'ressourceType' => 'Administration'],
            ['nom' => 'admin.settings', 'description' => 'Accéder aux paramètres système', 'ressourceType' => 'Administration'],
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate(
                ['nom' => $permission['nom']],
                [
                    'description' => $permission['description'],
                    'ressourceType' => $permission['ressourceType']
                ]
            );
        }

        // Assignation des permissions par rôle
        $matrix = [
            'admin' => [
                'users.list',
                'users.create',
                'users.edit',
                'users.deactivate',
                'alerts.list',
                'alerts.create',
                'alerts.edit',
                'alerts.export',
                'audit.view',
                'audit.export',
                'admin.roles',
                'admin.settings',
            ],
            'analyst' => [
                'users.list',
                'alerts.list',
                'alerts.export',
                'audit.view',
            ],
        ];

        foreach ($matrix as $role => $permNames) {
            foreach ($permNames as $permName) {
                $permission = Permission::where('nom', $permName)->first();
                if ($permission) {
                    RolePermission::firstOrCreate([
                        'role' => $role,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }
    }
}
