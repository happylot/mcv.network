<?php

namespace App\Http\Controllers;

use App\Models\PublisherWebsite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPublisherWebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $status = $request->query('status', 'pending_review');
        $query = PublisherWebsite::query()->with('account.owner')->latest();

        if (is_string($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.publisher-websites.index', [
            'status' => $status,
            'websites' => $query->get(),
        ]);
    }

    public function approve(Request $request, PublisherWebsite $website): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $website->update(['status' => 'approved']);

        return back()->with('status', $website->domain.' is now live in the marketplace.');
    }

    public function reject(Request $request, PublisherWebsite $website): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $website->update(['status' => 'rejected']);

        return back()->with('status', $website->domain.' has been rejected.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_if(! $request->user()->currentAccount()?->isAdmin(), 403);
    }
}
