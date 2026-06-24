<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $statusCounts = [];
        foreach (LeadStatus::dashboardStatuses() as $status) {
            $statusCounts[$status->value] = [
                'label' => $status->label(),
                'count' => Lead::where('status', $status)->count(),
            ];
        }

        $recentLeads = Lead::latest('last_activity_at')
            ->limit(10)
            ->get(['id', 'name', 'telegram_username', 'status', 'last_activity_at']);

        return Inertia::render('Admin/Dashboard', [
            'statusCounts' => $statusCounts,
            'recentLeads' => $recentLeads,
            'totalLeads' => Lead::count(),
            'activeMembers' => Lead::where('status', LeadStatus::Active)->count(),
        ]);
    }
}
