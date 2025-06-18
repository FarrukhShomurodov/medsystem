<div class="mb-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-bold">Основной медикамент:</label>
                <p class="form-control-plaintext">{{ $data->medication->name }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Активное вещество:</label>
                <p class="form-control-plaintext">{{ $data->medication->composition->active_substance_name }}</p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-bold">Дозировка:</label>
                <p class="form-control-plaintext">{{ $data->dosage }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Группа препарата:</label>
                <p class="form-control-plaintext">{{ $data->medication->group->name ?? 'Не указана' }}</p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold">Время приёма:</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(explode(',', $data->admission_period) as $time)
                <span class="badge bg-primary p-2">{{ $time }}</span>
            @endforeach
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Совместимые медикаменты:</label>
        @if($data->compatibleMedications->count() > 0)
            <div class="list-group">
                @foreach($data->compatibleMedications as $compatible)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $compatible->name }}</h6>
                                <div class="d-flex gap-3">
                                    <small>Активное
                                        вещество: {{ $compatible->composition->active_substance_name }}</small>
                                    <small>Дозировка: {{ $compatible->pivot->dosage }}</small>
                                </div>
                            </div>
                            <span class="badge bg-success">Совместим</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mb-0">Совместимые медикаменты не назначены</div>
        @endif
    </div>
</div>
