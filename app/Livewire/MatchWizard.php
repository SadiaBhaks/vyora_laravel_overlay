<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\Match\ProductMatcher;
use App\Services\Match\RuleBasedMatcher;
use Livewire\Component;

/** VYORA MATCH — a rule-based (not AI) guided picker over the real catalogue. */
class MatchWizard extends Component
{
    public int $step = 1;
    public ?string $category = null;
    public string $budget = 'any';
    public ?string $style = null;
    public array $features = [];
    public string $recipient = 'me';

    public const BUDGETS = [
        'under-50' => ['Under $50', 0, 50],
        '50-150' => ['$50 – $150', 50, 150],
        '150-400' => ['$150 – $400', 150, 400],
        '400-plus' => ['$400 and up', 400, null],
        'any' => ['No limit', 0, null],
    ];

    public const TOTAL = 5;

    public function mount(): void
    {
        $this->style = auth()->user()?->preferences?->style;
    }

    public function next(): void
    {
        if ($this->step === 1 && ! $this->category) {
            $this->addError('category', 'Pick a category, or choose “Surprise me”.');

            return;
        }
        $this->resetErrorBag();
        $this->step = min(self::TOTAL + 1, $this->step + 1);
    }

    public function back(): void { $this->step = max(1, $this->step - 1); }

    public function choose(string $field, string $value): void
    {
        if (in_array($field, ['category', 'budget', 'style', 'recipient'], true)) {
            $this->{$field} = $value;
            if ($this->step < self::TOTAL) {
                $this->next();
            }
        }
    }

    public function toggleFeature(string $feature): void
    {
        $this->features = in_array($feature, $this->features, true)
            ? array_values(array_diff($this->features, [$feature]))
            : [...$this->features, $feature];
    }

    public function refine(): void { $this->step = 1; }

    public function restart(): void
    {
        $this->reset(['step', 'category', 'budget', 'style', 'features', 'recipient']);
    }

    public function render(ProductMatcher $matcher)
    {
        $results = null;
        if ($this->step > self::TOTAL) {
            [, $min, $max] = self::BUDGETS[$this->budget] ?? self::BUDGETS['any'];
            $results = $matcher->match([
                'category' => $this->category === 'any' ? null : $this->category,
                'budget_min' => $min, 'budget_max' => $max,
                'style' => $this->style, 'features' => $this->features, 'recipient' => $this->recipient,
            ], 6);
        }

        return view('livewire.match-wizard', [
            'categories' => Category::active()->get(),
            'results' => $results,
            'styles' => RuleBasedMatcher::STYLES,
            'featureOptions' => RuleBasedMatcher::FEATURES,
            'budgets' => self::BUDGETS,
        ]);
    }
}
