<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Get invoices for a project with total amounts.
     */
    public function getProjectInvoices(int $projectId)
    {
        return DB::table('invoice as i')
            ->select('i.id', 'i.invoice_no', 'i.status', 'u.name as user_name', 'i.bill_date')
            ->selectRaw('COALESCE(iline.total_amount, 0) + COALESCE(ilab.total_amount, 0) AS total_amount')
            ->join('project as p', 'p.id', '=', 'i.project_id')
            ->leftJoin('userdb as u', 'u.id', '=', 'p.user_id')
            ->leftJoinSub(
                DB::table('invoice_quote_line')
                    ->select('invoice_id', DB::raw('SUM(amount_invoiced) AS total_amount'))
                    ->groupBy('invoice_id'),
                'iline', 'i.id', '=', 'iline.invoice_id'
            )
            ->leftJoinSub(
                DB::table('invoice_quote_labour')
                    ->select('invoice_id', DB::raw('SUM(amount_invoiced) AS total_amount'))
                    ->groupBy('invoice_id'),
                'ilab', 'i.id', '=', 'ilab.invoice_id'
            )
            ->where('i.project_id', $projectId)
            ->where('i.status', '<>', 'void')
            ->orderBy('i.bill_date', 'DESC')
            ->get();
    }
}
