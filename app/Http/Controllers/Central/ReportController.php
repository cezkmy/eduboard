<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\BillingHistory;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function downloadReport(string $category)
    {
        $category = strtolower($category);

        if (!in_array($category, ['revenue', 'tenant'], true)) {
            abort(404);
        }

        $months = [];
        $revenueTrend = [];
        $tenantTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $months[] = $start->format('M Y');

            if ($category === 'revenue') {
                $revenueTrend[] = BillingHistory::where('payment_status', 'paid')
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('amount');
            } else {
                $tenantTrend[] = Tenant::whereBetween('created_at', [$start, $end])->count();
            }
        }

        $data = [
            'kind' => $category,
            'title' => $category === 'revenue' ? 'Revenue Report' : 'Tenant Report',
            'periodLabel' => 'Last 6 months',
            'months' => $months,
            'revenueTrend' => $revenueTrend,
            'tenantTrend' => $tenantTrend,
            'summary' => [
                'totalRevenue' => BillingHistory::where('payment_status', 'paid')->sum('amount'),
                'monthlyRevenueCurrent' => BillingHistory::where('payment_status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
                'totalTenants' => Tenant::count(),
                'activeTenants' => Tenant::where('status', 'Active')->count(),
            ],
        ];

        $filename = 'eduboard_' . $category . '_report_' . now()->format('Y-m-d') . '.pdf';

        return Pdf::loadView('central.admin.reports.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function downloadPaymentsReport(string $type)
    {
        $type = strtolower($type);

        if (!in_array($type, ['monthly', 'yearly'], true)) {
            abort(404);
        }

        if ($type === 'monthly') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $periodLabel = now()->format('F Y');
            $title = 'Monthly Payments Report';
        } else {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
            $periodLabel = now()->format('Y');
            $title = 'Yearly Payments Report';
        }

        $payments = BillingHistory::with('tenant')
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->orderByDesc('paid_at')
            ->get();

        $totalAmount = (float) $payments->sum('amount');

        // Simple plan distribution for the summary (optional in PDF, but useful).
        $planTotals = $payments
            ->groupBy('plan')
            ->map(fn ($items) => (float) $items->sum('amount'))
            ->toArray();

        $data = [
            'kind' => 'payments',
            'paymentsType' => $type,
            'title' => $title,
            'periodLabel' => $periodLabel,
            'payments' => $payments,
            'summary' => [
                'totalAmount' => $totalAmount,
                'planTotals' => $planTotals,
            ],
        ];

        $filename = 'eduboard_payments_' . $type . '_report_' . now()->format('Y-m-d') . '.pdf';

        return Pdf::loadView('central.admin.reports.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}

