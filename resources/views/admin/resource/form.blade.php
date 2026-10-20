<x-layouts.admin :title="($model->exists ? 'Edit ' : 'New ').$singular">
    <a href="{{ route($route.'.index') }}" class="text-sm text-muted hover:text-ink">← {{ $title }}</a>
    <h1 class="display-lg mt-2">{{ $model->exists ? 'Edit' : 'New' }} {{ strtolower($singular) }}</h1>
    <form method="POST" action="{{ $model->exists ? route($route.'.update', $model) : route($route.'.store') }}" class="card mt-8 max-w-3xl space-y-5 p-6 sm:p-8" novalidate>
        @csrf @if($model->exists) @method('PUT') @endif
        @foreach($fields as $f)
            @php
                $name = $f['name']; $type = $f['type'];
                $opts = isset($f['options']) ? (is_callable($f['options']) ? ($f['options'])() : $f['options']) : [];
                $val = isset($f['value']) ? ($f['value'])($model) : data_get($model, $name);
                if ($val instanceof \DateTimeInterface) { $val = $val->format('Y-m-d\TH:i'); }
            @endphp
            @if($type === 'checkbox')
                <label class="flex items-center gap-2.5 text-sm font-medium"><input type="hidden" name="{{ $name }}" value="0"><input type="checkbox" name="{{ $name }}" value="1" class="size-4 accent-[var(--ink)]" @checked(old($name, $model->exists ? $val : true))> {{ $f['label'] }}</label>
            @elseif($type === 'multiselect')
                <div><label class="label" for="f-{{ $name }}">{{ $f['label'] }}</label>
                    <select id="f-{{ $name }}" name="{{ $name }}[]" multiple size="10" class="input h-auto">@foreach($opts as $k => $l)<option value="{{ $k }}" @selected(in_array($k, (array) old($name, $val)))>{{ $l }}</option>@endforeach</select>
                    <p class="field-help">Hold Ctrl/Cmd to select several.</p></div>
            @elseif($type === 'select')
                <x-field :name="$name" type="select" :label="$f['label']" :help="$f['help'] ?? null">@foreach($opts as $k => $l)<option value="{{ $k }}" @selected((string) old($name, $val) === (string) $k)>{{ $l }}</option>@endforeach</x-field>
            @else
                <x-field :name="$name" :type="$type" :label="$f['label']" :value="$val" :help="$f['help'] ?? null" :step="$f['step'] ?? null" />
            @endif
        @endforeach
        <div class="flex gap-3"><button class="btn btn-primary">Save</button><a href="{{ route($route.'.index') }}" class="btn btn-ghost">Cancel</a></div>
    </form>
</x-layouts.admin>
