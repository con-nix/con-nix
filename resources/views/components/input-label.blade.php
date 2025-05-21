@props(['value' => null, 'for' => null])

<label {{ $for ? 'for="'.$for.'"' : '' }} {{ $attributes->merge(['class' => 'block font-medium text-sm text-neutral-900 dark:text-neutral-100']) }}>
    {{ $value ?? $slot }}
</label>