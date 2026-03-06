<?php

namespace App\Services;

use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class OrganisationService
{
    /**
     * Get organization-wide financial stats.
     */
    public function getStats(int $organisationId, ?int $customerId = null)
    {
        $query = DB::table('project as p')
            ->selectRaw('SUM(COALESCE(iline.paid_total, 0) + COALESCE(ilab.paid_total, 0)) AS total_paid')
            ->selectRaw('SUM(COALESCE(iline.outstanding_total, 0) + COALESCE(ilab.outstanding_total, 0)) AS outstanding_total')
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_line as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'iline', 'p.id', '=', 'iline.project_id'
            )
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_labour as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'ilab', 'p.id', '=', 'ilab.project_id'
            )
            ->where('p.organisation_id', $organisationId)
            ->where('p.archived', 0);

        if ($customerId) {
            $query->where('p.customer_id', $customerId);
        }

        $res = $query->first();

        $paymentsQuery = DB::table('invoice_payment as ip')
            ->join('invoice as i', 'i.id', '=', 'ip.invoice_id')
            ->selectRaw('DATE_FORMAT(ip.payment_datetime, "%M %Y") as month_year')
            ->selectRaw('SUM(ip.net_paid) as total_paid')
            ->where('i.organisation_id', $organisationId);

        if ($customerId) {
            $paymentsQuery->join('project as p', 'p.id', '=', 'i.project_id')
                ->where('p.customer_id', $customerId);
        }

        $lastPayments = $paymentsQuery->groupBy(DB::raw('month_year'), DB::raw('YEAR(ip.payment_datetime)'), DB::raw('MONTH(ip.payment_datetime)'))
            ->orderBy(DB::raw('YEAR(ip.payment_datetime)'), 'DESC')
            ->orderBy(DB::raw('MONTH(ip.payment_datetime)'), 'DESC')
            ->limit(3)
            ->get();

        $projectsQuery = DB::table('project')
            ->where('project.organisation_id', $organisationId)
            ->where('project.archived', 0);

        if ($customerId) {
            $projectsQuery->where('project.customer_id', $customerId);
        }

        $totalProjects = $projectsQuery->count();

        $newQuotesQuery = DB::table('quote')
            ->where('quote.organisation_id', $organisationId)
            ->where('quote.archived', 0)
            ->where('quote.is_active_revision', 1);

        if ($customerId) {
            $newQuotesQuery->join('project as p', 'p.id', '=', 'quote.project_id')
                ->where('p.customer_id', $customerId);
        }
        $newQuotes = $newQuotesQuery->count();

        $newInvoicesQuery = DB::table('invoice')
            ->where('invoice.organisation_id', $organisationId)
            ->where('invoice.status', '<>', 'void');

        if ($customerId) {
            $newInvoicesQuery->join('project as p', 'p.id', '=', 'invoice.project_id')
                ->where('p.customer_id', $customerId);
        }
        $newInvoices = $newInvoicesQuery->count();

        $currentUser = DB::table('userdb')
            ->where('default_organisation_id', $organisationId)
            ->where('name', 'Lee Roche')
            ->first(['id', 'name']);

        if (!$currentUser) {
            $currentUser = DB::table('userdb')
                ->where('default_organisation_id', $organisationId)
                ->first(['id', 'name']);
        }

        return [
            'total_projects' => $totalProjects,
            'total_paid' => (float)($res->total_paid ?? 0),
            'outstanding_invoices' => (float)($res->outstanding_total ?? 0),
            'remaining_to_pay' => (float)($res->outstanding_total ?? 0),
            'new_quotes' => $newQuotes,
            'new_invoices' => $newInvoices,
            'last_payments' => $lastPayments,
            'current_user' => $currentUser
        ];
    }
}
