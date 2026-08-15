@props(['value'])

<label {{ $attributes->merge(['class' => 'field-label mb-1']) }}>
    {{ $value ?? $slot }}
</label>
