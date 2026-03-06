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

    public function index(Project $project)
    {
        $invoices = $this->invoiceService->getProjectInvoices($project->id);

        return response()->json($invoices);
    }
}
