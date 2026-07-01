<?php

namespace App\Http\Controllers;

use App\Models\AgencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAgencyServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $status = $request->query('status', 'pending_review');
        $query = AgencyService::query()->with('agencyAccount')->latest();

        if (is_string($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.agency-services.index', [
            'status' => $status,
            'services' => $query->get(),
        ]);
    }

    public function approve(Request $request, AgencyService $service): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $service->update(['status' => 'approved']);

        return back()->with('status', $service->title.' is now live in the services marketplace.');
    }

    public function reject(Request $request, AgencyService $service): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $service->update(['status' => 'rejected']);

        return back()->with('status', $service->title.' has been rejected.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_if(! $request->user()->currentAccount()?->isAdmin(), 403);
    }
}
