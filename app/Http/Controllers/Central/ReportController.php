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
        try {
            // Increase limits for PDF generation
            ini_set('max_execution_time', '300');
            ini_set('memory_limit', '512M');

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

            // Ensure the view exists and data is correctly passed
            $pdf = Pdf::loadView('central.admin.reports.pdf.report', $data);
            
            return $pdf->setPaper('a4', 'portrait')->download($filename);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function downloadPaymentsReport($type)
    {
        try {
            // Increase limits
            ini_set('max_execution_time', '300');
            ini_set('memory_limit', '512M');

            $query = \App\Models\BillingHistory::with('tenant');

            if ($type === 'monthly') {
                $query->whereMonth('paid_at', now()->month)
                      ->whereYear('paid_at', now()->year);
                $title = 'Monthly Revenue Report - ' . now()->format('F Y');
            } elseif ($type === 'yearly') {
                $query->whereYear('paid_at', now()->year);
                $title = 'Yearly Revenue Report - ' . now()->year;
            } else {
                $title = 'All Payments Report';
            }

            $payments = $query->get();
            $totalAmount = (float) $payments->sum('amount');

            $data = [
                'kind' => 'payments',
                'paymentsType' => $type,
                'title' => $title,
                'periodLabel' => ($type === 'monthly' ? now()->format('F Y') : ($type === 'yearly' ? now()->format('Y') : 'All Time')),
                'date' => now()->format('F d, Y'),
                'payments' => $payments,
                'summary' => [
                    'totalAmount' => $totalAmount,
                ],
            ];

            $filename = 'eduboard_payments_' . $type . '_report_' . now()->format('Y-m-d') . '.pdf';

            $pdf = Pdf::loadView('central.admin.reports.pdf.report', $data);
            
            return $pdf->setPaper('a4', 'portrait')->download($filename);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function downloadInvoice($id)
    {
        try {
            // Increase limits for PDF generation
            ini_set('max_execution_time', '300');
            ini_set('memory_limit', '512M');

            $payment = BillingHistory::with('tenant')->findOrFail($id);

            $data = [
                'kind' => 'invoice',
                'title' => 'Invoice #' . $payment->invoice_number,
                'payment' => $payment,
                'periodLabel' => 'Single Transaction',
                'date' => now()->format('F d, Y'),
                'type' => 'invoice'
            ];

            $filename = 'invoice_' . $payment->invoice_number . '.pdf';

            $pdf = Pdf::loadView('central.admin.invoice-pdf', $data);
            
            return $pdf->setPaper('a4', 'portrait')->download($filename);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Invoice Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate Invoice PDF: ' . $e->getMessage());
        }
    }
}

