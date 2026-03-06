<?php
namespace App\Http\Controllers;

use App\Services\OrganisationService;
use App\Models\Organisation;

class OrganisationController extends Controller
{
    protected $organisationService;

    public function __construct(OrganisationService $organisationService)
    {
        $this->organisationService = $organisationService;
    }

    public function show(Organisation $organisation)
    {
        return response()->json($organisation);
    }

    public function stats(Organisation $organisation, \Illuminate\Http\Request $request)
    {
        $customerId = $request->query('customer_id');
        return response()->json($this->organisationService->getStats($organisation->id, $customerId ? (int)$customerId : null));
    }
}
