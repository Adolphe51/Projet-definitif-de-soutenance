<?php

namespace App\Http\Controllers;

use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use App\Services\HoneypotService;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HoneypotController extends Controller
{
    public function index()
    {
        $traps        = HoneypotTrap::orderByDesc('interactions_count')->get();
        $interactions = HoneypotInteraction::with('trap')->orderByDesc('created_at')->limit(20)->get();
        $totalInteractions = HoneypotInteraction::count();
        $uniqueAttackers   = HoneypotInteraction::distinct('source_ip')->count('source_ip');
        $credsCaptured     = HoneypotInteraction::whereNotNull('credentials_attempted')->count();

        return view('honeypot.index', compact('traps', 'interactions', 'totalInteractions', 'uniqueAttackers', 'credsCaptured'));
    }

    public function detail(int $id)
    {
        $trap         = HoneypotTrap::findOrFail($id);
        $interactions = HoneypotInteraction::where('honeypot_trap_id', $id)->orderByDesc('created_at')->paginate(15);
        return view('honeypot.detail', compact('trap', 'interactions'));
    }

    public function simulate(int $id): JsonResponse
    {
        $interaction = HoneypotService::simulateInteraction($id);
        return response()->json([
            'success' => true,
            'interaction' => [
                'id'          => $interaction->id,
                'ip'          => $interaction->source_ip,
                'country'     => $interaction->country,
                'city'        => $interaction->city,
                'credentials' => $interaction->credentials_attempted,
                'actions'     => $interaction->actions_taken,
                'risk_score'  => $interaction->risk_score,
                'payload'     => $interaction->payload,
                'user_agent'  => $interaction->user_agent,
                'duration'    => $interaction->session_duration,
            ],
        ]);
    }

    public function initialize(): JsonResponse
    {
        HoneypotService::createDefaultTraps();
        return response()->json(['success' => true, 'message' => 'Pièges initialisés']);
    }

    public function toggle(int $id): JsonResponse
    {
        $trap = HoneypotTrap::findOrFail($id);
        $newStatus = $trap->status === 'active' ? 'inactive' : 'active';
        $trap->update(['status' => $newStatus]);
        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function liveStats(): JsonResponse
    {
        // Simuler automatiquement une interaction ~20% du temps
        if (rand(1, 100) <= 20) {
            $activeTraps = HoneypotTrap::where('status', 'active')->pluck('id');
            if ($activeTraps->isNotEmpty()) {
                HoneypotService::simulateInteraction($activeTraps->random());
            }
        }

        $recent = HoneypotInteraction::with('trap')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($i) => [
                'id'         => $i->id,
                'trap_name'  => $i->trap->name ?? '?',
                'trap_type'  => $i->trap->type ?? '?',
                'ip'         => $i->source_ip,
                'country'    => $i->country,
                'city'       => $i->city,
                'lat'        => $i->latitude,
                'lon'        => $i->longitude,
                'risk_score' => $i->risk_score,
                'credentials'=> $i->credentials_attempted,
                'actions'    => $i->actions_taken,
                'time'       => $i->created_at->diffForHumans(),
            ]);

        return response()->json([
            'interactions' => $recent,
            'total'        => HoneypotInteraction::count(),
            'unique_ips'   => HoneypotInteraction::distinct('source_ip')->count('source_ip'),
            'creds'        => HoneypotInteraction::whereNotNull('credentials_attempted')->count(),
            'active_traps' => HoneypotTrap::where('status', 'active')->count(),
        ]);
    }

    // ---- Pages appâts accessibles depuis le web (pièges réels) ----

    public function fakeLogin(Request $request)
    {
        self::logWebInteraction($request, 'fake_login', '/login');

        if ($request->isMethod('post')) {
            $this->captureCredentials($request, 'fake_login');
            // Simuler un "chargement" puis redirection vers une page d'erreur
            return view('honeypot.traps.fake_error')->with('message', 'Identifiants incorrects. Votre IP a été enregistrée.');
        }
        return view('honeypot.traps.fake_login');
    }

    public function fakeAdmin(Request $request)
    {
        self::logWebInteraction($request, 'fake_admin', '/admin');

        if ($request->isMethod('post')) {
            $this->captureCredentials($request, 'fake_admin');
            return view('honeypot.traps.fake_admin_success');
        }
        return view('honeypot.traps.fake_admin_panel');
    }

    public function fakePhpMyAdmin(Request $request)
    {
        self::logWebInteraction($request, 'fake_phpmyadmin', '/phpmyadmin');

        if ($request->isMethod('post')) {
            $this->captureCredentials($request, 'fake_phpmyadmin');
            return view('honeypot.traps.fake_db_panel');
        }
        return view('honeypot.traps.fake_phpmyadmin');
    }

    public function fakeWordpressLogin(Request $request)
    {
        self::logWebInteraction($request, 'fake_wordpress', '/wp-admin');

        if ($request->isMethod('post')) {
            $this->captureCredentials($request, 'fake_wordpress');
            return redirect()->back()->with('error', 'ERROR: Invalid username or password.');
        }
        return view('honeypot.traps.fake_wordpress');
    }

    public function fakeApiEndpoint(Request $request, $path = '')
    {
        self::logWebInteraction($request, 'fake_api', '/api/' . $path);
        $datasets = \App\Services\HoneypotService::getFakeDataset('api_keys');
        return response()->json([
            'status'  => 'success',
            'data'    => $datasets,
            'warning' => 'THIS IS A HONEYPOT. Your IP has been logged.',
        ], 200);
    }

    public function canaryToken(Request $request)
    {
        self::logWebInteraction($request, 'canary_token', '/internal/confidential.pdf');
        return view('honeypot.traps.canary_document');
    }

    private static function logWebInteraction(Request $request, string $type, string $path): void
    {
        $ip   = $request->ip() ?? '0.0.0.0';
        $geo  = GeoService::lookup($ip);
        $trap = HoneypotTrap::where('type', $type)->first();
        if (!$trap) return;

        HoneypotInteraction::create([
            'honeypot_trap_id' => $trap->id,
            'source_ip'        => $ip,
            'country'          => $geo['country'],
            'city'             => $geo['city'],
            'latitude'         => $geo['lat'],
            'longitude'        => $geo['lon'],
            'isp'              => $geo['isp'],
            'method'           => $request->method(),
            'path'             => $path,
            'user_agent'       => $request->userAgent(),
            'payload'          => json_encode($request->except(['_token', 'password'])),
            'risk_score'       => 75,
        ]);

        $trap->increment('interactions_count');
        $trap->update(['last_triggered_at' => now(), 'status' => 'triggered']);
    }

    private function captureCredentials(Request $request, string $type): void
    {
        $ip   = $request->ip() ?? '0.0.0.0';
        $geo  = GeoService::lookup($ip);
        $trap = HoneypotTrap::where('type', $type)->first();
        if (!$trap) return;

        $creds = [
            'username' => $request->input('username') ?? $request->input('log') ?? $request->input('user') ?? '?',
            'password' => $request->input('password') ?? $request->input('pwd') ?? '?',
        ];

        HoneypotInteraction::create([
            'honeypot_trap_id'      => $trap->id,
            'source_ip'             => $ip,
            'country'               => $geo['country'],
            'city'                  => $geo['city'],
            'latitude'              => $geo['lat'],
            'longitude'             => $geo['lon'],
            'isp'                   => $geo['isp'],
            'method'                => 'POST',
            'path'                  => $request->path(),
            'user_agent'            => $request->userAgent(),
            'payload'               => $request->except(['_token', 'password']),
            'credentials_attempted' => $creds,
            'actions_taken'         => ['Soumission formulaire', 'Tentative authentification'],
            'risk_score'            => 90,
        ]);

        \App\Models\Alert::create([
            'title'    => "🍯 CREDENTIALS CAPTURÉS — {$trap->name}",
            'message'  => "IP: {$ip} ({$geo['city']}, {$geo['country']}) a soumis user='{$creds['username']}' pass='{$creds['password']}'",
            'severity' => 'critical',
            'type'     => 'honeypot',
        ]);
    }
}
