<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Schema-driven CRUD for simple resources. Subclasses declare columns, fields and rules; views are shared.
 * Staff-only access is enforced by the route group middleware; subclasses can tighten via $adminOnly.
 */
abstract class ResourceController extends Controller
{
    protected string $model;
    protected string $route;      // e.g. admin.categories
    protected string $title;      // e.g. Categories
    protected string $singular;   // e.g. Category
    protected array $searchable = ['name'];
    protected array $with = [];

    abstract protected function columns(): array;                 // [label => callable|attribute]
    abstract protected function fields(): array;                  // [['name','label','type','options','help']]
    abstract protected function rules(?Model $model): array;

    protected function prepare(array $data, ?Model $model): array { return $data; }
    protected function afterSave(Model $model, Request $request): void {}
    protected function canDelete(Model $model): ?string { return null; }

    public function index(Request $request)
    {
        $query = $this->model::query()->with($this->with)->latest('id');
        if ($q = trim((string) $request->query('q'))) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(function ($w) use ($like) {
                foreach ($this->searchable as $col) {
                    $w->orWhereRaw("LOWER($col) LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        return view('admin.resource.index', [
            'rows' => $query->paginate(20)->withQueryString(),
            'columns' => $this->columns(),
            'title' => $this->title, 'singular' => $this->singular, 'route' => $this->route,
            'q' => $request->query('q'),
        ]);
    }

    public function create()
    {
        return view('admin.resource.form', $this->formData(new $this->model));
    }

    public function store(Request $request)
    {
        $data = $this->prepare($request->validate($this->rules(null)), null);
        $model = $this->model::create($data);
        $this->afterSave($model, $request);

        return redirect()->route($this->route.'.index')->with('toast', $this->singular.' created.');
    }

    public function edit(Request $request)
    {
        $model = $this->resolve($request);

        return view('admin.resource.form', $this->formData($model));
    }

    public function update(Request $request)
    {
        $model = $this->resolve($request);
        $data = $this->prepare($request->validate($this->rules($model)), $model);
        $model->update($data);
        $this->afterSave($model, $request);

        return redirect()->route($this->route.'.index')->with('toast', $this->singular.' updated.');
    }

    public function destroy(Request $request)
    {
        $model = $this->resolve($request);
        if ($reason = $this->canDelete($model)) {
            return back()->withErrors(['delete' => $reason]);
        }
        $model->delete();

        return redirect()->route($this->route.'.index')->with('toast', $this->singular.' deleted.');
    }

    private function resolve(Request $request): Model
    {
        $param = Str::singular(Str::afterLast($this->route, '.'));

        return $this->model::findOrFail($request->route($param));
    }

    private function formData(Model $model): array
    {
        return [
            'model' => $model, 'fields' => $this->fields(),
            'title' => $this->title, 'singular' => $this->singular, 'route' => $this->route,
        ];
    }
}
