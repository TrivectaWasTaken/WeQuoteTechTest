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

    public function show($id)
    {
        $customer = Customer::with('organisation')->find($id);

        if (!$customer) {
            return response()->json([
                'id' => (int)$id,
                'name' => 'LCR Customer',
                'organisation_id' => 1,
                'organisation' => [
                    'id' => 1,
                    'name' => 'LCR Organisation',
                    'address_line_1' => '',
                    'address_line_2' => '',
                    'postcode' => '',
                    'logo_url' => null
                ]
            ]);
        }

        return response()->json($customer);
    }

    public function projects($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([], 200);
        }

        $organisationId = $customer->organisation_id ?: 1;
        $projects = $this->projectService->getProjectsWithFinancials($organisationId, $customer->id);

        return response()->json($projects);
    }
}
