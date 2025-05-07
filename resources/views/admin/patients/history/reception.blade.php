<div class="border-bottom pb-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-data me-2 text-info"></i>
            {{ $data->date }}
        </h5>
        <div>
            <span class="text-muted">
                {{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        <!-- Время приема -->
        <div class="col-md-6">
            <small class="text-muted d-block">
                <i class="bx bx-time-five text-info me-1"></i>
                Время приема</small>
            <div class="fs-5">
                {{ \Carbon\Carbon::parse($data->start_time)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($data->end_time)->format('H:i') }}
            </div>
        </div>

        <!-- Сотрудник -->
        <div class="col-md-6">
            <small class="text-muted d-block">
                <i class="bx bx-user-plus text-info me-1"></i>Ответственный</small>
            <div class="fw-medium">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs">
                        <span class="avatar-initial bg-primary rounded-circle">
                            {{ substr($data->staff->name, 0, 1) }}
                        </span>
                    </div>
                    {{ $data->staff->name }}
                </div>
            </div>
        </div>

        <!-- Комментарий -->
        @if($data->comment)
            <div class="col-12 mt-3">
                <div class="medical-comment-block">
                    <div class="comment-header">
                        <h6 class="mb-0"> <i class="bx bx-comment-detail me-1"></i>Комментарий к приему</h6>
                    </div>
                    <div class="fs-5">
                        {{ $data->comment }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
