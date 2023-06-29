<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitRepoApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        $sortBy = $request->input('sortBy', 'name');
        $sortOrder = $request->input('sortOrder', "1");
        $sortOrder = ($sortOrder === '1') ? 'asc' : 'desc';
        $searchTerm = $request->input('search', '');

        $response = Http::get('https://api.github.com/search/repositories', [
            'q' => 'topic:php',
            'per_page' => 500,
        ]);

        $repositories = collect($response->json()['items'])->map(function ($item) {
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
        });

        if (!empty($searchTerm)) {
            $repositories = $repositories->filter(function ($repository) use ($searchTerm) {
                return str_contains(strtolower($repository['name']), strtolower($searchTerm));
            });
        }

        $repositories = $this->sortRepositories($repositories, $sortBy, $sortOrder);

        $totalRecords = $repositories->count();
        $repositories = $this->paginateRepositories($repositories, $perPage);

        return response()->json(['repositories' => $repositories->values(), 'totalRecords' => $totalRecords]);
    }

    private function sortRepositories($repositories, $sortBy, $sortOrder = 'asc')
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