{{-- Sugerencias del catálogo de ciudades (Paraguay). El campo sigue aceptando texto libre ("Otra");
     al guardar, User vincula city_id si el texto coincide con el catálogo. Uso: <input list="py-cities"> --}}
@once
<datalist id="py-cities">
    @foreach(\App\Models\City::catalog() as $city)
        <option value="{{ $city['name'] }}">{{ $city['department'] }}</option>
    @endforeach
</datalist>
@endonce
