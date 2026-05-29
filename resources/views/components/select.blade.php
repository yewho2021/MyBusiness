@props(['name', 'options' => [], 'value' => null, 'placeholder' => null])
<select name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'sc-select']) }}>
    @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
    @foreach($options as $k => $v)
    <option value="{{ $k }}" {{ old($name, $value) == $k ? 'selected' : '' }}>{{ $v }}</option>
    @endforeach
</select>
