@props(['name', 'id' => null])
@error($name)
    <p class="field-error" id="{{ $id ?? str_replace(['.', '[', ']'], '-', $name).'-error' }}">
        <x-icon name="circle-alert" class="mt-0.5 size-4" />
        <span>{{ $message }}</span>
    </p>
@enderror
