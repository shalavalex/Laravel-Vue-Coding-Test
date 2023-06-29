<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitRepoApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage');
        $sortBy = $request->input('sortBy');
        $sortOrder = $request->input('sortOrder') === '1' ? 'asc' : 'desc';
        $searchTerm = $request->input('search');

        $repositories = $this->fetchRepositories();

        $repositories = $repositories
            ->take(500)
            ->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'full_name' => $item['full_name'],
                    'html_url' => $item['html_url'],
                    'language' => $item['language'],
                    'updated_at' => $item['updated_at'],
                    'pushed_at' => $item['pushed_at'],
                    'stargazers_count' => $item['stargazers_count'],
                ];
            })
            ->when(!empty($searchTerm), function ($repositories) use ($searchTerm) {
                return $repositories->filter(function ($repository) use ($searchTerm) {
                    return str_contains(strtolower($repository['name']), strtolower($searchTerm));
                });
            })
            ->when(!empty($sortBy), function ($repositories) use ($sortBy, $sortOrder) {
                return $this->sortRepositories($repositories, $sortBy, $sortOrder);
            });

        $totalRecords = $repositories->count();
        $repositories = $this->paginateRepositories($repositories, $perPage);

        return response()->json([
            'repositories' => $repositories->values(),
            'totalRecords' => $totalRecords,
        ]);
    }
    private function fetchRepositories()
    {
        $page = 1;
        $perPageGit = 100;
        $repositories = collect();

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.github.token'),
            ])->get('https://api.github.com/search/repositories', [
                        'q' => 'topic:php',
                        'per_page' => $perPageGit,
                        'page' => $page,
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $items = $data['items'];

                $repositories = $repositories->concat($items);
            }

            $page++;
        } while ($response->successful() && count($items) >= $perPageGit && count($repositories) < 500);

        return $repositories;
    }
    private function sortRepositories($repositories, $sortBy, $sortOrder)
    {
        return $repositories->sortBy($sortBy, SORT_REGULAR, $sortOrder === 'desc');
    }
    private function paginateRepositories($repositories, $perPage)
    {
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        return $repositories->slice($offset, $perPage);
    }
}