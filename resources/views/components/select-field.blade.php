@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'hint' => null,
    'optional' => false,
    'placeholder' => 'Choose…',
])
@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $selected = (string) old($name, $value);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp
<div class="field">
    <label for="{{ $id }}" class="label">{{ $label }}@if ($optional)<span class="label-optional">(optional)</span>@endif</label>
    @if ($hint)
        <p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>
    @endif
    <select name="{{ $name }}" id="{{ $id }}"
        @if (! $optional) required aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->merge(['class' => 'input']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            @php $optionValue = is_int($optionValue) ? $optionLabel : $optionValue; @endphp
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    <x-field-error :name="$name" :id="$id.'-error'" />
</div>
