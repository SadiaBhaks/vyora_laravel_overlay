@props(['icon' => 'package', 'title', 'text' => null, 'action' => null, 'actionLabel' => 'Continue shopping'])
<div {{ $attributes->merge(['class' => 'card grid place-items-center px-6 py-16 text-center']) }}>
    <span class="mb-5 grid size-14 place-items-center rounded-full bg-surface-2"><x-icon :name="$icon" class="size-6 text-muted" /></span>
    <h3 class="display-lg !text-2xl">{{ $title }}</h3>
    @if($text)<p class="mt-2 max-w-sm text-sm text-muted">{{ $text }}</p>@endif
    @if($action)<a href="{{ $action }}" class="btn btn-primary mt-6">{{ $actionLabel }}</a>@endif
    {{ $slot }}
</div>
