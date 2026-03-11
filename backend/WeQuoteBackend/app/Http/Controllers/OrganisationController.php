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

    public function show($id)
    {
        $organisation = Organisation::find($id);

        if (!$organisation) {
            return response()->json([
                'id' => (int)$id,
                'name' => 'LCR Organisation',
                'address_line_1' => '',
                'address_line_2' => '',
                'postcode' => '',
                'logo_url' => null
            ]);
        }

        return response()->json($organisation);
    }

    public function stats($id, \Illuminate\Http\Request $request)
    {
        $organisation = Organisation::find($id);
        $organisationId = $organisation ? $organisation->id : (int)$id;

        $customerId = $request->query('customer_id');
        return response()->json($this->organisationService->getStats($organisationId, $customerId ? (int)$customerId : null));
    }
}
