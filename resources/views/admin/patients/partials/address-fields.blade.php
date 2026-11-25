{{--
    An address of the patient wizard (patients/partials/form). Rendered inside a .row.

    @include('admin.patients.partials.address-fields', ['prefix' => 'registered', 'region' => 'registered_region_id', 'city' => 'registered_city_id', 'value' => $value])

    prefix: residential or registered (the street, house, apartment and postcode fields are named after it);
    region, city: the names of the two selects; value: fn (name) => the field's value. A region or
    city typed into its select is created by the server (.select2-tags).
--}}
@foreach ([[$region, 'Область', 'Выберите область', $regions], [$city, 'Город', 'Выберите город', $cities]] as [$selectName, $selectLabel, $selectPlaceholder, $selectOptions])
    <div class="col-sm-6">
        <label class="form-label" for="{{ $selectName }}">{{ $selectLabel }}</label>
        <select class="form-select select2 select2-tags @error($selectName) is-invalid @enderror" id="{{ $selectName }}" name="{{ $selectName }}"
                data-placeholder="{{ $selectPlaceholder }}">
            <option value="" disabled @selected(blank($value($selectName)))>{{ $selectPlaceholder }}</option>
            @foreach ($selectOptions as $option)
                <option value="{{ $option->id }}" @selected($option->id == $value($selectName))>{{ $option->name }}</option>
            @endforeach
        </select>
        @error($selectName)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach

@include('admin.patients.partials.field', ['field' => [
    'name' => "{$prefix}_address_street", 'label' => 'Улица', 'icon' => 'bx-map', 'col' => 'col-sm-6', 'value' => $value("{$prefix}_address_street"),
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => "{$prefix}_address_house", 'label' => 'Дом', 'icon' => 'bx-home', 'col' => 'col-sm-6 col-lg-2', 'value' => $value("{$prefix}_address_house"),
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => "{$prefix}_address_apartment", 'label' => 'Квартира', 'icon' => 'bx-door-open', 'col' => 'col-sm-6 col-lg-2', 'value' => $value("{$prefix}_address_apartment"),
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => "{$prefix}_postcode", 'label' => 'Почтовый индекс', 'icon' => 'bx-envelope', 'type' => 'number', 'col' => 'col-sm-6 col-lg-2',
    'value' => $value("{$prefix}_postcode"),
]])
