<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class ProjectService
{
    /**
     * Get projects with financial calculations.
     */
    public function getProjectsWithFinancials(int $organisationId, ?int $customerId = null)
    {
        $query = DB::table('project as p')
            ->select('p.*')
            ->selectRaw('u.name AS user_name, u.email_addr AS user_email')
            ->selectRaw('c.name AS customer_name, c.email_address AS customer_email')
            ->selectRaw('c.address_line_1, c.posttown, c.postcode')
            ->selectRaw('COALESCE(p.updated_datetime, p.created_datetime) AS modified_datetime')
            ->selectRaw('COALESCE(teq.total_price, 0) + COALESCE(tlab.total_price, 0) AS net_total')
            ->selectRaw('COALESCE(iline.draft_total, 0) + COALESCE(ilab.draft_total, 0) AS draft_total')
            ->selectRaw('COALESCE(iline.outstanding_total, 0) + COALESCE(ilab.outstanding_total, 0) AS outstanding_total')
            ->selectRaw('COALESCE(iline.paid_total, 0) + COALESCE(ilab.paid_total, 0) AS paid_total')
            ->selectRaw('COALESCE(teq.total_price, 0) + COALESCE(tlab.total_price, 0) - COALESCE(iline.on_invoice_total, 0) - COALESCE(ilab.on_invoice_total, 0) AS not_invoiced_total')
            ->selectRaw('COALESCE((SELECT COUNT(*) FROM invoice WHERE project_id = p.id AND status <> "void"), 0) AS invoice_count')
            ->selectRaw('COALESCE((SELECT COUNT(*) FROM quote WHERE project_id = p.id AND is_active_revision = 1 AND archived = 0), 0) AS quote_count')
            ->leftJoin('userdb as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('customer as c', 'c.id', '=', 'p.customer_id')
            ->leftJoinSub(
                DB::table('view_active_quote_line as ql')
                    ->join('quote as q', 'q.id', '=', 'ql.quote_id')
                    ->select('q.project_id', DB::raw('SUM(ql.total_price) AS total_price'))
                    ->where('q.organisation_id', $organisationId)
                    ->where('q.is_active_revision', 1)
                    ->where('q.archived', 0)
                    ->where('q.stage', '<>', 'cancelled')
                    ->groupBy('q.project_id'),
                'teq', 'p.id', '=', 'teq.project_id'
            )
            ->leftJoinSub(
                DB::table('view_active_quote_line as ln')
                    ->join('quote as q', 'q.id', '=', 'ln.quote_id')
                    ->join('quote_labour as lab', 'lab.line_id', '=', 'ln.id')
                    ->select('q.project_id', DB::raw('SUM(lab.total_price) AS total_price'))
                    ->where('q.organisation_id', $organisationId)
                    ->where('q.is_active_revision', 1)
                    ->where('q.archived', 0)
                    ->where('q.stage', '<>', 'cancelled')
                    ->groupBy('q.project_id'),
                'tlab', 'p.id', '=', 'tlab.project_id'
            )
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_line as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status IN ("draft", "submitted"), iql.amount_invoiced, 0)) AS draft_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(iql.amount_invoiced) AS on_invoice_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'iline', 'p.id', '=', 'iline.project_id'
            )
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_labour as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status IN ("draft", "submitted"), iql.amount_invoiced, 0)) AS draft_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(iql.amount_invoiced) AS on_invoice_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'ilab', 'p.id', '=', 'ilab.project_id'
            )
            ->where('p.organisation_id', $organisationId)
            ->where('p.archived', 0);

        if ($customerId) {
            $query->where('p.customer_id', $customerId);
        }

        return $query->orderBy('modified_datetime', 'DESC')->get();
    }

    /**
     * Get a single project with details and financials.
     */
    public function getProjectDetails(int $projectId, int $organisationId)
    {
        return DB::table('project as p')
            ->select('p.*')
            ->selectRaw('u.name AS user_name, u.email_addr AS user_email')
            ->selectRaw('c.name AS customer_name, c.email_address AS customer_email')
            ->selectRaw('c.address_line_1, c.posttown, c.postcode')
            ->selectRaw('COALESCE(p.updated_datetime, p.created_datetime) AS modified_datetime')
            ->selectRaw('COALESCE(teq.total_price, 0) + COALESCE(tlab.total_price, 0) AS net_total')
            ->selectRaw('COALESCE(iline.draft_total, 0) + COALESCE(ilab.draft_total, 0) AS draft_total')
            ->selectRaw('COALESCE(iline.outstanding_total, 0) + COALESCE(ilab.outstanding_total, 0) AS outstanding_total')
            ->selectRaw('COALESCE(iline.paid_total, 0) + COALESCE(ilab.paid_total, 0) AS paid_total')
            ->selectRaw('COALESCE(teq.total_price, 0) + COALESCE(tlab.total_price, 0) - COALESCE(iline.on_invoice_total, 0) - COALESCE(ilab.on_invoice_total, 0) AS not_invoiced_total')
            ->selectRaw('COALESCE((SELECT COUNT(*) FROM invoice WHERE project_id = p.id AND status <> "void"), 0) AS invoice_count')
            ->selectRaw('COALESCE((SELECT COUNT(*) FROM quote WHERE project_id = p.id AND is_active_revision = 1 AND archived = 0), 0) AS quote_count')
            ->leftJoin('userdb as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('customer as c', 'c.id', '=', 'p.customer_id')
            ->leftJoinSub(
                DB::table('view_active_quote_line as ql')
                    ->join('quote as q', 'q.id', '=', 'ql.quote_id')
                    ->select('q.project_id', DB::raw('SUM(ql.total_price) AS total_price'))
                    ->where('q.organisation_id', $organisationId)
                    ->where('q.is_active_revision', 1)
                    ->where('q.archived', 0)
                    ->where('q.stage', '<>', 'cancelled')
                    ->groupBy('q.project_id'),
                'teq', 'p.id', '=', 'teq.project_id'
            )
            ->leftJoinSub(
                DB::table('view_active_quote_line as ln')
                    ->join('quote as q', 'q.id', '=', 'ln.quote_id')
                    ->join('quote_labour as lab', 'lab.line_id', '=', 'ln.id')
                    ->select('q.project_id', DB::raw('SUM(lab.total_price) AS total_price'))
                    ->where('q.organisation_id', $organisationId)
                    ->where('q.is_active_revision', 1)
                    ->where('q.archived', 0)
                    ->where('q.stage', '<>', 'cancelled')
                    ->groupBy('q.project_id'),
                'tlab', 'p.id', '=', 'tlab.project_id'
            )
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_line as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status IN ("draft", "submitted"), iql.amount_invoiced, 0)) AS draft_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(iql.amount_invoiced) AS on_invoice_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'iline', 'p.id', '=', 'iline.project_id'
            )
            ->leftJoinSub(
                DB::table('invoice as i')
                    ->join('invoice_quote_labour as iql', 'iql.invoice_id', '=', 'i.id')
                    ->select('i.project_id')
                    ->selectRaw('SUM(IF(i.status IN ("draft", "submitted"), iql.amount_invoiced, 0)) AS draft_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_invoiced - iql.amount_paid, 0)) AS outstanding_total')
                    ->selectRaw('SUM(IF(i.status NOT IN ("draft", "submitted", "void"), iql.amount_paid, 0)) AS paid_total')
                    ->selectRaw('SUM(iql.amount_invoiced) AS on_invoice_total')
                    ->where('i.organisation_id', $organisationId)
                    ->groupBy('i.project_id'),
                'ilab', 'p.id', '=', 'ilab.project_id'
            )
            ->where('p.id', $projectId)
            ->where('p.organisation_id', $organisationId)
            ->first();
    }
}
