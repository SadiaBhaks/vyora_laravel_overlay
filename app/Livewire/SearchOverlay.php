<?php

namespace App\Livewire;

use App\Services\SearchService;
use Livewire\Component;

class SearchOverlay extends Component
{
    public string $q = '';

    public function render(SearchService $search)
    {
        $term = SearchService::clean($this->q);
        $result = mb_strlen($term) >= 2 ? $search->suggest($term, 5) : null;

        return view('livewire.search-overlay', [
            'term' => $term,
            'result' => $result,
            'popular' => $search->popular(),
        ]);
    }
}
