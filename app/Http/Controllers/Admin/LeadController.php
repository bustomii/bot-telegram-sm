<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Services\ConversationHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $query = Lead::query()->latest('last_activity_at');

        if ($status) {
            $query->where('status', $status);
        }

        $leads = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
            'filters' => ['status' => $status],
            'statuses' => collect(LeadStatus::dashboardStatuses())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function show(Lead $lead): Response
    {
        $lead->load(['activities.user', 'supportCases', 'assignedAdmin']);

        return Inertia::render('Admin/Leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function approve(Lead $lead): RedirectResponse
    {
        app(ConversationHandler::class)->approveMember($lead);
        AuditLog::record('approve', "Member {$lead->name} disetujui", auth()->id(), $lead->id);

        return back()->with('success', 'Member berhasil disetujui.');
    }

    public function reject(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate(['reject_reason' => 'required|string']);
        $lead->update(['reject_reason' => $request->reject_reason]);
        app(ConversationHandler::class)->rejectMember($lead);
        AuditLog::record('reject', "Member {$lead->name} ditolak", auth()->id(), $lead->id);

        return back()->with('success', 'Member ditolak.');
    }

    public function addNote(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate(['admin_notes' => 'required|string']);
        $lead->update(['admin_notes' => $request->admin_notes]);
        AuditLog::record('add_note', 'Catatan admin ditambahkan', auth()->id(), $lead->id);

        return back()->with('success', 'Catatan berhasil disimpan.');
    }

    public function export(): StreamedResponse
    {
        $filename = 'leads_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Nama', 'Telegram', 'Status', 'Wallet ID', 'MT5 ID', 'Deposit', 'Dibuat']);

            Lead::chunk(100, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->telegram_username,
                        $lead->status?->value,
                        $lead->wallet_id,
                        $lead->mt5_id,
                        $lead->hfm_deposit,
                        $lead->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
