{{--
    The two dialogs of the booking grids (receptions/appointment and receptions/calendar), driven by
    resources/js/pages/receptions/booking.js: booking a free slot, and the details of a booked one
    with its cancellation. The booking dialog opens only for an account with create_receptions (the
    grid offers free slots as buttons only then).
--}}
<div class="modal modal-blur fade" id="appointment-modal" tabindex="-1" aria-labelledby="appointment-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="appointment-form" novalidate>
            <div class="modal-header">
                <h2 class="modal-title" id="appointment-modal-title">Запись на приём</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required" for="patient">Пациент</label>
                    {{-- select2 with a server search, set up by the page script (no .select2 class). --}}
                    <select id="patient" name="patient_id" class="form-select" data-placeholder="Найдите пациента" required
                            aria-describedby="patient-error"></select>
                    <div class="invalid-feedback" id="patient-error" hidden>Выберите пациента.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="appointment-doctor">Врач</label>
                        <input type="text" id="appointment-doctor" class="form-control-plaintext" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="appointment-date">Дата приёма</label>
                        <input type="text" id="appointment-date" class="form-control-plaintext" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="appointment-time">Время приёма</label>
                        <input type="text" id="appointment-time" class="form-control-plaintext" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="appointment-status">Статус</label>
                        <select id="appointment-status" name="status" class="form-select">
                            @foreach(\App\Enums\ReceptionStatus::initial() as $status)
                                {{-- Taking the payment while booking is a cash desk action. --}}
                                @continue($status === \App\Enums\ReceptionStatus::Paid && auth('admin')->user()->cannot('accept_payments'))
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="form-label" for="comment">Комментарий</label>
                <textarea id="comment" name="comment" class="form-control" rows="3" maxlength="2000"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary" data-submit><i class="bx bx-calendar-check" aria-hidden="true"></i>Записать</button>
            </div>
        </form>
    </div>
</div>

<div class="modal modal-blur fade" id="reception-modal" tabindex="-1" aria-labelledby="reception-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="reception-modal-title">Информация о приёме</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    @foreach(['patient' => 'Пациент', 'time' => 'Время приёма', 'doctor' => 'Врач', 'position' => 'Должность', 'date' => 'Дата приёма', 'status' => 'Статус', 'comment' => 'Комментарий'] as $field => $label)
                        <dt class="col-sm-4 text-secondary fw-normal">{{ $label }}</dt>
                        <dd class="col-sm-8" data-reception-field="{{ $field }}"></dd>
                    @endforeach
                </dl>
            </div>
            <div class="modal-footer">
                @can('change_receptions_status')
                    <button type="button" class="btn btn-outline-danger me-auto" data-cancel-reception hidden>
                        <i class="bx bx-x-circle" aria-hidden="true"></i>Отменить приём
                    </button>
                @endcan
                <button type="button" class="btn" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
