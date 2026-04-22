<!-- Ressource: resources/views/components/form-field.blade.php -->
<div class="form-group {{ $errors->has($name ?? '') ? 'has-error' : '' }}">
    <label for="{{ $id ?? $name ?? '' }}">
        @if($required ?? false)
            <span class="required">*</span>
        @endif
        {{ $label ?? ucfirst(str_replace('_', ' ', $name ?? '')) }}
    </label>
    
    @if($type === 'textarea')
        <textarea 
            id="{{ $id ?? $name ?? '' }}"
            name="{{ $name ?? '' }}"
            rows="{{ $rows ?? 4 }}"
            @if($required ?? false) required @endif
            {{ $attributes }}
        >{{ $value ?? old($name ?? '') }}</textarea>
    @elseif($type === 'select')
        <select 
            id="{{ $id ?? $name ?? '' }}"
            name="{{ $name ?? '' }}"
            @if($required ?? false) required @endif
            {{ $attributes }}
        >
            <option value="">{{ $placeholder ?? 'Sélectionner...' }}</option>
            @foreach($options ?? [] as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected(($value ?? old($name)) == $optValue)>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
    @else
        <input 
            type="{{ $type ?? 'text' }}"
            id="{{ $id ?? $name ?? '' }}"
            name="{{ $name ?? '' }}"
            value="{{ $value ?? old($name ?? '') }}"
            placeholder="{{ $placeholder ?? '' }}"
            @if($required ?? false) required @endif
            {{ $attributes }}
        >
    @endif

    @if($errors->has($name ?? ''))
        <div class="form-error">
            {{ $errors->first($name ?? '') }}
        </div>
    @endif
    
    @if($hint ?? null)
        <small class="text-muted">{{ $hint }}</small>
    @endif
</div>
