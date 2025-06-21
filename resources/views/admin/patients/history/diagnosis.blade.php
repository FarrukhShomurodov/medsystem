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
        <div class="col-md-6">
            <small class="text-muted d-block">
                <i class="bx bx-time-five text-info me-1"></i>
                Время</small>
            <div class="fs-5">
                {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i') }}
            </div>
        </div>

        <!-- Комментарий -->
        @if($data->diagnosis->name)
            <div class="col-12 mt-3">
                <div class="medical-comment-block">
                    <div class="comment-header">
                        <h6 class="mb-0"><i class="bx bx-comment-detail me-1"></i>Диагноз</h6>
                    </div>
                    <div class="fs-5">
                        {{ $data->diagnosis->name }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
