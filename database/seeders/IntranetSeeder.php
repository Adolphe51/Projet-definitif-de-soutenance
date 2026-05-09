<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Student;
use App\Models\Intranet\Course;
use App\Models\Intranet\Enrollment;
use App\Models\Intranet\Message;
use Illuminate\Support\Str;

class IntranetSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->shouldRefreshMiniSite()) {
            $this->refreshMiniSiteDataset();
        }

        // Les noms techniques Student/Course restent en base,
        // mais le jeu de demonstration doit raconter un mini site metier.
        $users = $this->seedUsers();
        $services = $this->seedServices();

        $this->seedAssignments($users, $services);
        $this->seedMessages($users);
    }

    private function shouldRefreshMiniSite(): bool
    {
        return (bool) config('cyberguard.mini_site.refresh_on_seed', false);
    }

    private function refreshMiniSiteDataset(): void
    {
        DB::transaction(function (): void {
            Message::query()->delete();
            Enrollment::query()->delete();
            Course::query()->delete();
            Student::query()->delete();
        });

        if ($this->command) {
            $this->command->info('Mini site metier reinitialise avec un jeu de demonstration plus clair et oriente metier.');
        }
    }

    private function seedUsers(): array
    {
        $users = [];

        foreach ($this->userDefinitions() as $definition) {
            $user = Student::updateOrCreate(
                ['student_id' => $definition['user_code']],
                [
                    'id' => Student::where('student_id', $definition['user_code'])->value('id') ?: (string) Str::uuid(),
                    'student_id' => $definition['user_code'],
                    'first_name' => $definition['first_name'],
                    'last_name' => $definition['last_name'],
                    'email' => $definition['email'],
                    'phone' => $definition['phone'],
                    'date_of_birth' => $definition['date_of_birth'],
                    'address' => $definition['business_unit'] . ' - ' . $definition['location'] . ' - ' . $definition['responsibility'],
                    'status' => $definition['status'],
                ]
            );

            $users[$definition['user_code']] = $user;
        }

        return $users;
    }

    private function seedServices(): array
    {
        $services = [];

        foreach ($this->serviceDefinitions() as $definition) {
            $service = Course::updateOrCreate(
                ['course_code' => $definition['service_code']],
                [
                    'id' => Course::where('course_code', $definition['service_code'])->value('id') ?: (string) Str::uuid(),
                    'course_code' => $definition['service_code'],
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'department' => $definition['business_area'],
                    'credits' => $definition['criticality_level'],
                    'semester' => $definition['workflow_cycle'],
                    'max_students' => $definition['target_capacity'],
                    'status' => $definition['status'],
                ]
            );

            $services[$definition['service_code']] = $service;
        }

        return $services;
    }

    private function seedAssignments(array $users, array $services): void
    {
        foreach ($this->assignmentDefinitions() as $definition) {
            $user = $users[$definition['user_code']] ?? null;
            $service = $services[$definition['service_code']] ?? null;

            if (!$user || !$service) {
                continue;
            }

            $assignment = Enrollment::firstOrNew([
                'student_id' => $user->id,
                'course_id' => $service->id,
                'semester' => $definition['workflow_cycle'],
            ]);

            if (!$assignment->exists) {
                $assignment->id = (string) Str::uuid();
            }

            // grade stocke ici un code de relation court pour conserver
            // un sens metier lisible sans changer le schema.
            $assignment->grade = $definition['relation_code'];
            $assignment->final_score = $definition['priority_score'];
            $assignment->status = $definition['status'];
            $assignment->enrollment_date = $definition['linked_at'];
            $assignment->save();
        }
    }

    private function seedMessages(array $users): void
    {
        foreach ($this->messageDefinitions() as $definition) {
            $sender = $users[$definition['sender']] ?? null;
            $recipient = $users[$definition['recipient']] ?? null;

            if (!$sender || !$recipient) {
                continue;
            }

            Message::updateOrCreate(
                [
                    'sender_id' => $sender->id,
                    'recipient_id' => $recipient->id,
                    'subject' => $definition['subject'],
                ],
                [
                    'id' => Message::where('sender_id', $sender->id)
                        ->where('recipient_id', $recipient->id)
                        ->where('subject', $definition['subject'])
                        ->value('id') ?: (string) Str::uuid(),
                    'body' => $definition['body'],
                    'is_read' => $definition['is_read'],
                ]
            );
        }
    }

    private function userDefinitions(): array
    {
        return [
            [
                'user_code' => 'USR001',
                'first_name' => 'Awa',
                'last_name' => 'Konan',
                'email' => 'awa.konan@cyberguard.demo',
                'phone' => '+225 07 11 22 33 44',
                'date_of_birth' => '1998-04-12',
                'business_unit' => 'Direction RH',
                'location' => 'Plateau, Abidjan',
                'responsibility' => 'validation des habilitations et suivi des demandes sensibles',
                'status' => 'active',
            ],
            [
                'user_code' => 'USR002',
                'first_name' => 'Moussa',
                'last_name' => 'Traore',
                'email' => 'moussa.traore@cyberguard.demo',
                'phone' => '+225 05 12 23 34 45',
                'date_of_birth' => '1995-11-08',
                'business_unit' => 'Pole IT',
                'location' => 'Cocody, Abidjan',
                'responsibility' => 'administration des services, droits de modification et traces techniques',
                'status' => 'active',
            ],
            [
                'user_code' => 'USR003',
                'first_name' => 'Nadia',
                'last_name' => 'Bamba',
                'email' => 'nadia.bamba@cyberguard.demo',
                'phone' => '+225 01 44 55 66 77',
                'date_of_birth' => '1997-01-19',
                'business_unit' => 'Communication interne',
                'location' => 'Marcory, Abidjan',
                'responsibility' => 'relecture des contenus, publication et coordination des messages internes',
                'status' => 'active',
            ],
            [
                'user_code' => 'USR004',
                'first_name' => 'Kevin',
                'last_name' => 'Yao',
                'email' => 'kevin.yao@cyberguard.demo',
                'phone' => '+225 07 88 77 66 55',
                'date_of_birth' => '1994-06-30',
                'business_unit' => 'Achats',
                'location' => 'Yopougon, Abidjan',
                'responsibility' => 'gestion des contrats fournisseurs et suivi des pieces jointes critiques',
                'status' => 'inactive',
            ],
            [
                'user_code' => 'USR005',
                'first_name' => 'Sonia',
                'last_name' => 'Nguessan',
                'email' => 'sonia.nguessan@cyberguard.demo',
                'phone' => '+225 05 66 55 44 33',
                'date_of_birth' => '1999-03-14',
                'business_unit' => 'Centre support',
                'location' => 'Bingerville, Abidjan',
                'responsibility' => 'prise en charge des demandes metier avec contenu libre a qualifier',
                'status' => 'active',
            ],
            [
                'user_code' => 'USR006',
                'first_name' => 'Ibrahim',
                'last_name' => 'Coulibaly',
                'email' => 'ibrahim.coulibaly@cyberguard.demo',
                'phone' => '+225 01 10 20 30 40',
                'date_of_birth' => '1996-09-05',
                'business_unit' => 'Conformite',
                'location' => 'Treichville, Abidjan',
                'responsibility' => 'archivage des preuves, revue des alertes et validation du discours de demo',
                'status' => 'graduated',
            ],
        ];
    }

    private function serviceDefinitions(): array
    {
        return [
            [
                'service_code' => 'SVC-RH',
                'title' => 'Portail RH - habilitations et demandes sensibles',
                'description' => 'Service metier utilise pour creer, verifier et approuver les demandes d acces RH. Les commentaires libres et les validations servent de support de demonstration pour l audit et la detection.',
                'business_area' => 'Ressources humaines',
                'criticality_level' => 5,
                'workflow_cycle' => 'Q2-26',
                'target_capacity' => 40,
                'status' => 'active',
            ],
            [
                'service_code' => 'SVC-IT',
                'title' => 'Catalogue IT - demandes et changements techniques',
                'description' => 'Catalogue de services permettant de suivre les tickets, modifications et traces d acces. Les zones de texte libres sont utiles pour raconter un scenario SQL Injection ou une recherche anormale.',
                'business_area' => 'Informatique',
                'criticality_level' => 4,
                'workflow_cycle' => 'Q2-26',
                'target_capacity' => 60,
                'status' => 'active',
            ],
            [
                'service_code' => 'SVC-SUP',
                'title' => 'Centre support - demandes internes et escalades',
                'description' => 'Point d entree des demandes internes avec contenu libre, captures et commentaires. Ce service sert bien a illustrer le risque de charge utile malveillante dans un flux ordinaire.',
                'business_area' => 'Support',
                'criticality_level' => 4,
                'workflow_cycle' => 'Q2-26',
                'target_capacity' => 35,
                'status' => 'active',
            ],
            [
                'service_code' => 'SVC-ACH',
                'title' => 'Achats - contrats fournisseurs et validations',
                'description' => 'Module de suivi des contrats, echeances et pieces jointes. Les documents importes et commentaires de validation donnent du contexte aux analyses CyberGuard.',
                'business_area' => 'Achats',
                'criticality_level' => 3,
                'workflow_cycle' => 'Q2-26',
                'target_capacity' => 20,
                'status' => 'active',
            ],
            [
                'service_code' => 'SVC-COM',
                'title' => 'Communication interne - notes et publications',
                'description' => 'Espace de publication des notes operationnelles et messages internes. Il permet d expliquer simplement comment un contenu HTML ou JavaScript peut etre bloque puis rattache a une alerte.',
                'business_area' => 'Communication',
                'criticality_level' => 3,
                'workflow_cycle' => 'Q2-26',
                'target_capacity' => 50,
                'status' => 'active',
            ],
        ];
    }

    private function assignmentDefinitions(): array
    {
        return [
            [
                'user_code' => 'USR001',
                'service_code' => 'SVC-RH',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'PO',
                'priority_score' => 19.50,
                'status' => 'completed',
                'linked_at' => '2026-03-03 09:00:00',
            ],
            [
                'user_code' => 'USR001',
                'service_code' => 'SVC-SUP',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'VA',
                'priority_score' => 16.50,
                'status' => 'enrolled',
                'linked_at' => '2026-03-12 09:00:00',
            ],
            [
                'user_code' => 'USR002',
                'service_code' => 'SVC-IT',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'PO',
                'priority_score' => 18.00,
                'status' => 'completed',
                'linked_at' => '2026-03-05 09:00:00',
            ],
            [
                'user_code' => 'USR002',
                'service_code' => 'SVC-ACH',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'OP',
                'priority_score' => 14.00,
                'status' => 'enrolled',
                'linked_at' => '2026-03-18 09:00:00',
            ],
            [
                'user_code' => 'USR003',
                'service_code' => 'SVC-COM',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'RE',
                'priority_score' => 15.50,
                'status' => 'enrolled',
                'linked_at' => '2026-03-22 09:00:00',
            ],
            [
                'user_code' => 'USR005',
                'service_code' => 'SVC-SUP',
                'workflow_cycle' => 'Q2-26',
                'relation_code' => 'OP',
                'priority_score' => 17.50,
                'status' => 'completed',
                'linked_at' => '2026-03-25 09:00:00',
            ],
        ];
    }

    private function messageDefinitions(): array
    {
        return [
            [
                'sender' => 'USR001',
                'recipient' => 'USR002',
                'subject' => 'Portail RH | Validation des habilitations avant ouverture',
                'body' => 'Objet : confirmer les droits de modification du Portail RH. Relation : Awa pilote le service RH et Moussa valide le perimetre technique avant ouverture. Action attendue : verifier les habilitations, tracer les changements et signaler a CyberGuard toute saisie suspecte.',
                'is_read' => true,
            ],
            [
                'sender' => 'USR002',
                'recipient' => 'USR003',
                'subject' => 'Catalogue IT | Requetes anormales sur plusieurs fiches service',
                'body' => 'Objet : partager un contexte de detection sur le Catalogue IT. Relation : Moussa administre le service et Nadia prepare le message de diffusion interne. Action attendue : croiser les requetes suspectes avec le dashboard avant publication d une consigne.',
                'is_read' => false,
            ],
            [
                'sender' => 'USR003',
                'recipient' => 'USR005',
                'subject' => 'Communication interne | Relecture d un contenu HTML avant diffusion',
                'body' => 'Objet : valider un contenu avant publication. Relation : Nadia produit la note et Sonia gere les remontees du support. Action attendue : controler le HTML, bloquer toute balise active et rattacher le cas a l alerte correspondante si un script est detecte.',
                'is_read' => true,
            ],
            [
                'sender' => 'USR005',
                'recipient' => 'USR004',
                'subject' => 'Achats | Piece jointe fournisseur a verifier avant validation',
                'body' => 'Objet : analyser une piece jointe transmise dans le circuit achats. Relation : Sonia remonte le signalement et Kevin suit la validation fournisseur. Action attendue : inspecter le document, confirmer son innocuite puis journaliser la decision.',
                'is_read' => false,
            ],
            [
                'sender' => 'USR006',
                'recipient' => 'USR002',
                'subject' => 'Centre support | Tentatives repetitives observees ce matin',
                'body' => 'Objet : qualifier plusieurs tentatives repetitives sur le Centre support. Relation : Ibrahim prepare la lecture conformite et Moussa verifie les traces techniques. Action attendue : confirmer s il s agit d un scenario brute force et conserver les preuves utiles a la demo.',
                'is_read' => true,
            ],
            [
                'sender' => 'USR001',
                'recipient' => 'USR003',
                'subject' => 'Demo CyberGuard | Fil conducteur mini site, alerte et SOC',
                'body' => 'Objet : fixer le fil conducteur de soutenance. Relation : Awa cadre la sequence metier et Nadia prepare la restitution. Action attendue : montrer un enchainement simple et clair : action sur le mini site, audit, detection, alerte puis traitement dans CyberGuard.',
                'is_read' => true,
            ],
        ];
    }
}
