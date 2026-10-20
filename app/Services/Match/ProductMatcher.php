<?php

namespace App\Services\Match;

use Illuminate\Support\Collection;

/** Swap the bound implementation to introduce a real AI service later; the UI only depends on this contract. */
interface ProductMatcher
{
    /**
     * @param  array{category:?string,budget_min:int,budget_max:?int,style:?string,features:array<int,string>,recipient:string}  $answers
     * @return Collection<int, array{product: \App\Models\Product, score: float, reasons: array<int,string>}>
     */
    public function match(array $answers, int $limit = 6): Collection;
}
