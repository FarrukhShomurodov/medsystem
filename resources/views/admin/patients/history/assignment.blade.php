<div>
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-calendar2-plus text-muted me-2"></i>
                {{ $data->service->name }}
            </h5>
            <small class="text-muted">
                <i class="bi bi-person-circle me-1"></i>
                Назначил: {{ $data->staff->name ?? 'Главный админ' }}
            </small>
        </div>
        <div class="text-end">
            <div class="badge bg-light text-dark">
                <i class="bi bi-clock-history me-1"></i>
                {{ $data->created_at->format('d.m.Y H:i') }}
            </div>
        </div>
    </div>

    @if($data->comment)
        <div class="mt-3 pt-2 border-top">
            <div class="d-flex align-items-start">
                <i class="bi bi-chat-left-text me-2 text-muted"></i>
                <div class="flex-grow-1">
                    <small class="text-muted d-block">Комментарий:</small>
                    <div class="fst-italic text-break">
                        {{ $data->comment }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
