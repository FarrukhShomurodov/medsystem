{{--
    The times of intake of a prescription (resources/js/pages/patients/prescriptions.js): an hour
    and a minute are added as chips, posted as one comma-separated admission_periods[] value.

    @include('admin.patients.partials.admission-times', ['times' => $medication->admission_period])
--}}
<fieldset class="admission-times">
    <legend class="form-label required">Время приёма</legend>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <select class="form-select w-auto admission-hour" aria-label="Час">
            @for ($hour = 0; $hour < 24; $hour++)
                <option value="{{ sprintf('%02d', $hour) }}">{{ sprintf('%02d', $hour) }}</option>
            @endfor
        </select>
        <span aria-hidden="true">:</span>
        <select class="form-select w-auto admission-minute" aria-label="Минуты">
            @for ($minute = 0; $minute < 60; $minute += 5)
                <option value="{{ sprintf('%02d', $minute) }}">{{ sprintf('%02d', $minute) }}</option>
            @endfor
        </select>
        <button type="button" class="btn btn-outline-success btn-add-time">
            <i class="bx bx-time-five" aria-hidden="true"></i>Добавить время
        </button>
    </div>
    <div class="tag-list" aria-live="polite">
        @foreach (array_filter(explode(',', (string) $times)) as $time)
            <span class="tag" data-time="{{ $time }}">{{ $time }}<button type="button" class="btn-close" data-remove-time aria-label="Удалить время {{ $time }}"></button></span>
        @endforeach
    </div>
    <input type="hidden" name="admission_periods[]" class="admission-hidden-input" value="{{ $times }}">
</fieldset>
