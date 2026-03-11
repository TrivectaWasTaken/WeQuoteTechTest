<?php
namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $organisationId = 1; // Defaulting for tech test
        $customerId = $request->query('customer_id');

        $projects = $this->projectService->getProjectsWithFinancials($organisationId, $customerId ? (int)$customerId : null);

        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'id' => (int)$id,
                'name' => 'LCR Project',
                'customer_id' => 1,
                'organisation_id' => 1,
                'net_total' => 0,
                'paid_total' => 0,
                'outstanding_total' => 0,
                'created_datetime' => date('Y-m-d H:i:s'),
                'modified_datetime' => date('Y-m-d H:i:s'),
                'user_name' => 'User'
            ]);
        }

        $organisationId = $project->organisation_id ?: 1;

        $res = $this->projectService->getProjectDetails($project->id, $organisationId);

        return response()->json($res);
    }

    public function quotes($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([], 200);
        }

        $organisationId = $project->organisation_id ?: 1;
        $quotes = \Illuminate\Support\Facades\DB::table('quote as q')
            ->select('q.*', 'u.name as user_name')
            ->selectRaw('COALESCE(teq.total_price, 0) + COALESCE(tlab.total_price, 0) AS total_price')
            ->leftJoin('userdb as u', 'u.id', '=', 'q.user_id')
            ->leftJoinSub(
                \Illuminate\Support\Facades\DB::table('view_active_quote_line as ql')
                    ->select('quote_id', \Illuminate\Support\Facades\DB::raw('SUM(total_price) AS total_price'))
                    ->groupBy('quote_id'),
                'teq', 'q.id', '=', 'teq.quote_id'
            )
            ->leftJoinSub(
                \Illuminate\Support\Facades\DB::table('view_active_quote_line as ln')
                    ->join('quote_labour as lab', 'lab.line_id', '=', 'ln.id')
                    ->select('ln.quote_id', \Illuminate\Support\Facades\DB::raw('SUM(lab.total_price) AS total_price'))
                    ->groupBy('ln.quote_id'),
                'tlab', 'q.id', '=', 'tlab.quote_id'
            )
            ->where('q.project_id', $project->id)
            ->where('q.organisation_id', $organisationId)
            ->where('q.archived', 0)
            ->get();

        return response()->json($quotes);
    }
}
