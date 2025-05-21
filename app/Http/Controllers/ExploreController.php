<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Display a listing of public repositories.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $ownerType = $request->get('owner_type');
        $sortBy = $request->get('sort', 'latest');

        $repositories = Repository::query()
            ->public()
            ->with(['user', 'organization'])
            ->search($search)
            ->byOwnerType($ownerType)
            ->when($sortBy === 'latest', fn ($q) => $q->latest())
            ->when($sortBy === 'oldest', fn ($q) => $q->oldest())
            ->when($sortBy === 'name', fn ($q) => $q->orderBy('name'))
            ->when($sortBy === 'updated', fn ($q) => $q->orderBy('updated_at', 'desc'))
            ->paginate(12)
            ->withQueryString();

        $totalPublicRepositories = Repository::public()->count();

        return view('explore.index', compact(
            'repositories',
            'search',
            'ownerType',
            'sortBy',
            'totalPublicRepositories'
        ));
    }
}
