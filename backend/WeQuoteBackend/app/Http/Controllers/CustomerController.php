<?php
namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Models\Customer;

class CustomerController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function projects(Customer $customer)
    {
        $organisationId = 1;
        $projects = $this->projectService->getProjectsWithFinancials($organisationId, $customer->id);

        return response()->json($projects);
    }
}
