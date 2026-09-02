<?php

namespace App\Http\Controllers;

use App\Models\ClientPic;
use App\Models\ClientRack;
use App\Models\Invoice;
use App\Models\RackDivice;
use App\Models\Service;
use App\Models\Ticket;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function superAdmin(Request $request)
    {
        $year = $request->get('year', now()->year);

        $now = Carbon::now();

        /**
         * =========================================
         * REVENUE (MONTHLY + YEAR FILTER)
         * =========================================
         */
        $rawRevenue = Invoice::selectRaw('MONTH(paid_at) as month, SUM(total) as total')
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        /**
         * NORMALIZE 12 BULAN (INI FIX UTAMA)
         */
        $revenuePerMonth = collect(range(1, 12))->map(function ($month) use ($rawRevenue) {
            return (float) ($rawRevenue[$month] ?? 0);
        });

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', $now->month)
            ->whereYear('paid_at', $now->year)
            ->sum('total');

        $yearlyRevenue = Invoice::where('status', 'paid')
            ->whereYear('paid_at', $now->year)
            ->sum('total');

        /**
         * =========================================
         * INVOICE STATUS
         * =========================================
         */
        $invoiceStats = Invoice::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        /**
         * =========================================
         * OVERDUE
         * =========================================
         */
        $overdueInvoices = Invoice::with('client', 'service')
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $now)
            ->latest()
            ->limit(5)
            ->get();

        /**
         * =========================================
         * UPCOMING SERVICES
         * =========================================
         */
        $upcomingServices = Service::with('client')
            ->where('status', 'active')
            ->whereBetween('next_due_date', [
                $now,
                $now->copy()->addDays(7)
            ])
            ->orderBy('next_due_date')
            ->limit(5)
            ->get();

        /**
         * =========================================
         * RECENT INVOICES
         * =========================================
         */
        $recentInvoices = Invoice::with('client', 'service')
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('dashboard/Admin', [
            'title' => 'Dashboard Admin',

            'year' => $year,

            'monthlyRevenue' => $monthlyRevenue,
            'yearlyRevenue' => $yearlyRevenue,

            'invoiceStats' => $invoiceStats,
            'overdueInvoices' => $overdueInvoices,
            'upcomingServices' => $upcomingServices,
            'recentInvoices' => $recentInvoices,

            'revenueChart' => $revenuePerMonth,
        ]);
    }
    public function client()
    {
        // Get client ID from authenticated user
        $clientPic = ClientPic::where('user_id', Auth::id())->first();
        $clientId = $clientPic?->client_id;

        if (!$clientId) {
            return Inertia::render('dashboard/Client', [
                'title' => 'Dashboard Client',
                'user' => Auth::user(),
                'stats' => [],
                'recentActivities' => [],
            ]);
        }

        // Get total racks
        $totalRacks = ClientRack::where('client_id', $clientId)->count();

        // Get active racks
        $activeRacks = ClientRack::where('client_id', $clientId)
            ->where('status', 'active')
            ->count();

        // Get total rented units
        $totalRentedUnits = ClientRack::where('client_id', $clientId)
            ->sum('rented_units');

        // Get total devices
        $totalDevices = RackDivice::where('client_id', $clientId)->count();

        // Get devices by type
        $devicesByType = RackDivice::where('client_id', $clientId)
            ->select('divice_type', DB::raw('count(*) as total'))
            ->groupBy('divice_type')
            ->get();

        // Get total services
        $totalServices = Service::where('client_id', $clientId)->count();

        // Get services by status
        $servicesByStatus = Service::where('client_id', $clientId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Get active services
        $activeServices = Service::where('client_id', $clientId)
            ->where('status', 'active')
            ->count();

        // Get total invoices
        $totalInvoices = Invoice::where('client_id', $clientId)
            ->whereNotIn('status', ['draft', 'pending'])
            ->count();

        // Get invoice statistics
        $paidInvoices = Invoice::where('client_id', $clientId)
            ->where('status', 'paid')
            ->count();

        $overdueInvoices = Invoice::where('client_id', $clientId)
            ->where('status', 'overdue')
            ->count();

        $waitingInvoices = Invoice::where('client_id', $clientId)
            ->where('status', 'waiting')
            ->count();

        // Get total amount paid
        $totalPaidAmount = Invoice::where('client_id', $clientId)
            ->where('status', 'paid')
            ->sum('total');

        // Get total outstanding amount
        $totalOutstanding = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'waiting', 'overdue'])
            ->sum('total');

        // Get total tickets
        $totalTickets = Ticket::where('client_id', $clientId)->count();

        // Get tickets by status
        $ticketsByStatus = Ticket::where('client_id', $clientId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Get open tickets
        $openTickets = Ticket::where('client_id', $clientId)
            ->whereIn('status', ['open', 'on_progress'])
            ->count();

        // Get recent tickets
        $recentTickets = Ticket::where('client_id', $clientId)
            ->with(['category', 'priority'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent invoices
        $recentInvoices = Invoice::where('client_id', $clientId)
            ->with(['service'])
            ->whereNotIn('status', ['draft', 'pending'])
            ->latest()
            ->limit(5)
            ->get();

        // Get upcoming due invoices
        $upcomingDueInvoices = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'waiting'])
            ->where('due_date', '>=', Carbon::now())
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->count();

        // Get monthly rack usage trend (last 6 months)
        $monthlyRackTrend = ClientRack::where('client_id', $clientId)
            ->where('rental_start_date', '>=', Carbon::now()->subMonths(6))
            ->select(DB::raw('MONTH(rental_start_date) as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Combine stats
        $stats = [
            'racks' => [
                'total' => $totalRacks,
                'active' => $activeRacks,
                'total_units' => $totalRentedUnits,
            ],
            'devices' => [
                'total' => $totalDevices,
                'by_type' => $devicesByType,
            ],
            'services' => [
                'total' => $totalServices,
                'active' => $activeServices,
                'by_status' => $servicesByStatus,
            ],
            'invoices' => [
                'total' => $totalInvoices,
                'paid' => $paidInvoices,
                'overdue' => $overdueInvoices,
                'waiting' => $waitingInvoices,
                'total_paid' => $totalPaidAmount,
                'total_outstanding' => $totalOutstanding,
                'upcoming_due' => $upcomingDueInvoices,
            ],
            'tickets' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'by_status' => $ticketsByStatus,
            ],
            'trends' => [
                'monthly_racks' => $monthlyRackTrend,
            ],
            'recent' => [
                'tickets' => $recentTickets,
                'invoices' => $recentInvoices,
            ],
        ];

        return Inertia::render('dashboard/Client', [
            'title' => 'Dashboard Client',
            'user' => Auth::user(),
            'stats' => $stats,
        ]);
    }
    public function marketing()
    {
        return Inertia::render('dashboard/Admin', [
            'title' => 'Dashboard Marketing',
        ]);
    }
    public function teknisi()
    {
        return Inertia::render('dashboard/Admin', [
            'title' => 'Dashboard Teknisi',
        ]);
    }

}
