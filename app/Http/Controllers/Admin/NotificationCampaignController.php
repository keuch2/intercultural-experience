<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationCampaign;
use App\Models\Program;
use App\Models\User;
use App\Services\Notifications\CampaignAudience;
use App\Services\Notifications\CampaignSender;
use App\Support\NotificationCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Avisos especiales (Fase 6): IE envía un aviso a un grupo de participantes, al momento o programado.
 * Queda en Avisos de la app y, según el canal, también como notificación push.
 */
class NotificationCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignAudience $audience,
        private readonly CampaignSender $sender,
    ) {}

    public function index()
    {
        $campaigns = NotificationCampaign::with('creator')->latest('id')->paginate(20);
        $stats = $campaigns->getCollection()->mapWithKeys(fn ($c) => [$c->id => $this->sender->stats($c)]);

        return view('admin.notification-campaigns.index', [
            'campaigns' => $campaigns,
            'stats' => $stats,
            'audience' => $this->audience,
            'devices' => DeviceToken::count(),
            'participantsWithDevice' => DeviceToken::distinct('user_id')->count('user_id'),
        ]);
    }

    public function create(Request $request)
    {
        $campaign = new NotificationCampaign([
            'link_type' => 'none', 'channel' => 'push', 'is_important' => false, 'audience' => ['mode' => 'all'],
        ]);
        // "Duplicar": parte de un aviso anterior.
        if ($source = NotificationCampaign::find($request->integer('from'))) {
            $campaign->fill($source->only(['title', 'message', 'link_type', 'link_target', 'link_program_id', 'link_url', 'audience', 'channel', 'is_important']));
        }

        return view('admin.notification-campaigns.form', $this->formData($campaign));
    }

    public function store(Request $request)
    {
        $campaign = new NotificationCampaign(['created_by' => $request->user()->id, 'status' => NotificationCampaign::STATUS_SCHEDULED]);
        $sendNow = $this->fillFromRequest($campaign, $request);
        $campaign->save();
        $this->log($request, $campaign, 'campaign_created', $sendNow ? 'Aviso especial enviado' : 'Aviso especial programado');

        if ($sendNow) {
            $this->sender->send($campaign);

            return redirect()->route('admin.notification-campaigns.show', $campaign)
                ->with('success', "Aviso enviado a {$campaign->fresh()->recipients_count} participante(s). Las notificaciones push salen en el próximo minuto.");
        }

        return redirect()->route('admin.notification-campaigns.show', $campaign)
            ->with('success', 'Aviso programado para el '.$campaign->scheduled_at->format('d/m/Y H:i').'.');
    }

    public function show(NotificationCampaign $notificationCampaign)
    {
        $campaign = $notificationCampaign->load(['creator', 'linkProgram']);
        $recipients = Notification::with('user:id,name,email')->where('campaign_id', $campaign->id)->orderBy('id')->paginate(50);

        return view('admin.notification-campaigns.show', [
            'campaign' => $campaign,
            'stats' => $this->sender->stats($campaign),
            'recipients' => $recipients,
            'audienceLabel' => $this->audience->describe($campaign->audience ?? []),
            'estimated' => $campaign->isEditable() ? $this->audience->count($campaign->audience ?? []) : null,
        ]);
    }

    public function edit(NotificationCampaign $notificationCampaign)
    {
        abort_unless($notificationCampaign->isEditable(), 404);

        return view('admin.notification-campaigns.form', $this->formData($notificationCampaign));
    }

    public function update(Request $request, NotificationCampaign $notificationCampaign)
    {
        abort_unless($notificationCampaign->isEditable(), 404);
        $sendNow = $this->fillFromRequest($notificationCampaign, $request);
        $notificationCampaign->save();
        $this->log($request, $notificationCampaign, 'campaign_updated', 'Aviso especial modificado');

        if ($sendNow) {
            $this->sender->send($notificationCampaign);

            return redirect()->route('admin.notification-campaigns.show', $notificationCampaign)
                ->with('success', "Aviso enviado a {$notificationCampaign->fresh()->recipients_count} participante(s).");
        }

        return redirect()->route('admin.notification-campaigns.show', $notificationCampaign)->with('success', 'Aviso programado actualizado.');
    }

    public function cancel(Request $request, NotificationCampaign $notificationCampaign)
    {
        $updated = NotificationCampaign::whereKey($notificationCampaign->id)->where('status', NotificationCampaign::STATUS_SCHEDULED)
            ->update(['status' => NotificationCampaign::STATUS_CANCELLED, 'cancelled_at' => now()]);
        if (! $updated) {
            return back()->with('error', 'El aviso ya se envió o estaba cancelado.');
        }
        $this->log($request, $notificationCampaign, 'campaign_cancelled', 'Aviso especial cancelado');

        return redirect()->route('admin.notification-campaigns.show', $notificationCampaign)->with('success', 'Aviso programado cancelado.');
    }

    /** Cantidad de destinatarios para el público elegido (formulario, AJAX). */
    public function preview(Request $request)
    {
        $audience = $this->audienceFromRequest($request);
        $query = $this->audience->query($audience);

        return response()->json([
            'count' => (clone $query)->count(),
            'with_device' => (clone $query)->whereHas('deviceTokens')->count(),
            'sample' => $query->orderBy('name')->limit(5)->pluck('name'),
        ]);
    }

    /** Búsqueda de participantes para "Personas específicas" (AJAX). */
    public function users(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }
        $users = User::query()->where('role', 'user')->whereNull('app_account_deleted_at')
            ->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('ci_number', 'like', "%{$q}%"))
            ->orderBy('name')->limit(15)->get(['id', 'name', 'email']);

        return response()->json(['data' => $users]);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function formData(NotificationCampaign $campaign): array
    {
        $programs = Program::query()->where('is_active', true)->orderBy('name')->get();
        $userIds = $campaign->audience['user_ids'] ?? [];

        return [
            'campaign' => $campaign,
            'programs' => $programs,
            'stagesByProgram' => $this->audience->stagesByProgram($programs),
            'seasons' => $this->audience->seasons(),
            'selectedUsers' => $userIds ? User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name', 'email']) : collect(),
        ];
    }

    /** Valida y carga el aviso. @return bool true si hay que enviarlo ahora */
    private function fillFromRequest(NotificationCampaign $campaign, Request $request): bool
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:1000',
            'link_type' => ['required', Rule::in(array_keys(NotificationCampaign::LINK_TYPES))],
            'link_target' => ['nullable', 'required_if:link_type,screen', Rule::in(array_keys(NotificationCatalog::LINK_TARGETS))],
            'link_program_id' => 'nullable|required_if:link_type,program|exists:programs,id',
            'link_url' => 'nullable|required_if:link_type,url|url|max:500',
            'channel' => ['required', Rule::in(array_keys(NotificationCampaign::CHANNELS))],
            'is_important' => 'boolean',
            'send_mode' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|required_if:send_mode,scheduled|date|after:now',
        ] + $this->audienceRules(), [
            'scheduled_at.after' => 'La fecha de envío tiene que ser futura.',
            'user_ids.required_if' => 'Elegí al menos un participante.',
        ], [
            'title' => 'título', 'message' => 'mensaje', 'link_target' => 'sección', 'link_program_id' => 'programa',
            'link_url' => 'enlace', 'scheduled_at' => 'fecha de envío', 'user_ids' => 'participantes',
        ]);

        $audience = $this->audienceFromRequest($request);
        if ($this->audience->count($audience) === 0) {
            throw ValidationException::withMessages(['audience_mode' => 'El público elegido no tiene participantes. Revisá los filtros.']);
        }

        $campaign->fill([
            'title' => $data['title'],
            'message' => $data['message'],
            'link_type' => $data['link_type'],
            'link_target' => $data['link_type'] === 'screen' ? $data['link_target'] : null,
            'link_program_id' => $data['link_type'] === 'program' ? $data['link_program_id'] : null,
            'link_url' => $data['link_type'] === 'url' ? $data['link_url'] : null,
            'channel' => $data['channel'],
            'is_important' => $request->boolean('is_important'),
            'audience' => $audience,
            'scheduled_at' => $data['send_mode'] === 'scheduled' ? $data['scheduled_at'] : null,
        ]);

        return $data['send_mode'] === 'now';
    }

    private function audienceRules(): array
    {
        return [
            'audience_mode' => 'required|in:all,segment,users',
            'program_ids' => 'nullable|array',
            'program_ids.*' => 'integer|exists:programs,id',
            'application_statuses' => 'nullable|array',
            'application_statuses.*' => Rule::in(array_keys(CampaignAudience::APPLICATION_STATUSES)),
            'stage' => 'nullable|string|max:50',
            'season' => 'nullable|string|max:20',
            'user_ids' => 'nullable|required_if:audience_mode,users|array',
            'user_ids.*' => 'integer|exists:users,id',
        ];
    }

    private function audienceFromRequest(Request $request): array
    {
        $mode = in_array($request->input('audience_mode'), ['all', 'segment', 'users'], true) ? $request->input('audience_mode') : 'all';

        return match ($mode) {
            'users' => ['mode' => 'users', 'user_ids' => array_values(array_unique(array_map('intval', (array) $request->input('user_ids', []))))],
            'segment' => array_filter([
                'mode' => 'segment',
                'program_ids' => array_values(array_map('intval', (array) $request->input('program_ids', []))),
                'application_statuses' => array_values((array) $request->input('application_statuses', [])),
                'stage' => count((array) $request->input('program_ids', [])) === 1 ? ($request->input('stage') ?: null) : null,
                'season' => $request->input('season') ?: null,
            ], fn ($v) => $v !== null && $v !== []),
            default => ['mode' => 'all'],
        };
    }

    private function log(Request $request, NotificationCampaign $campaign, string $action, string $label): void
    {
        ActivityLog::log('notifications')->withAction($action)
            ->withProperties(['campaign_id' => $campaign->id, 'title' => $campaign->title])
            ->causedBy($request->user())->log("{$label}: {$campaign->title}");
    }
}
