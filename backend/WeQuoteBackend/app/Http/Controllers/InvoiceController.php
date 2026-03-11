<?php
namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Models\Project;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([], 200);
        }

        $invoices = $this->invoiceService->getProjectInvoices($project->id);

        return response()->json($invoices);
    }
}
