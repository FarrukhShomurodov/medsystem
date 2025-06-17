@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Календарь врачей</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Календарь приемов</span>
    </h6>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3 w-100">
            <button class="btn btn-primary" id="prev-week-btn">Предыдущая неделя</button>
            <h5>Неделя: <span id="current-week"></span></h5>
            <button class="btn btn-primary" id="next-week-btn">Следующая неделя</button>
        </div>
    </div>

    <!-- Модальное окно -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Запись на прием</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointment-form">
                        <div class="m-3">
                            <label for="patient">Пациент</label>
                            <select class="form-control select2-modal" id="patient" name="patient_id" required>
                                <option value="" selected disabled>Выберите пациента</option>
                            </select>
                        </div>

                        <div class="m-3">
                            <label for="appointment-time">Время приема</label>
                            <input type="text" class="form-control" id="appointment-time" disabled>
                            <input type="hidden" id="start-time" disabled>
                            <input type="hidden" id="end-time" disabled>
                        </div>

                        <div class="m-3">
                            <label for="doctor-name">Врач</label>
                            <input type="text" class="form-control" id="doctor-name" disabled>
                            <input type="hidden" class="form-control" id="doctor-id" disabled>
                        </div>

                        <div class="m-3">
                            <label for="appointment-status">Статус</label>
                            <select id="appointment-status" class="form-select">
                                <option value="new">Новое</option>
                                <option value="paid">Оплачено</option>
                            </select>
                        </div>

                        <div class="m-3">
                            <label for="appointment-date">Дата приема</label>
                            <input type="text" class="form-control" id="appointment-date" disabled>
                        </div>

                        <div class="m-3">
                            <label for="comment">Комментарий</label>
                            <textarea class="form-control" id="comment" name="comment"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary m-3">Записаться</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для просмотра приема -->
    <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAppointmentModalLabel">Информация о приеме</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="m-3">
                        <label for="view-patient">Пациент</label>
                        <input type="text" class="form-control" id="view-patient" readonly>
                    </div>

                    <div class="m-3">
                        <label for="view-appointment-time">Время приема</label>
                        <input type="text" class="form-control" id="view-appointment-time" readonly>
                    </div>

                    <div class="m-3">
                        <label for="view-doctor-name">Врач</label>
                        <input type="text" class="form-control" id="view-doctor-name" readonly>
                    </div>

                    <div class="m-3">
                        <label for="view-doctor-position">Должность</label>
                        <input type="text" class="form-control" id="view-doctor-position" readonly>
                    </div>

                    <div class="m-3">
                        <label for="view-appointment-date">Дата приема</label>
                        <input type="text" class="form-control" id="view-appointment-date" readonly>
                    </div>


                    <div class="m-3">
                        <label for="view-appointment-status">Статус</label>
                        <input type="text" class="form-control" id="view-appointment-status" readonly>
                    </div>

                    <div class="m-3">
                        <label for="view-comment">Комментарий</label>
                        <textarea class="form-control" id="view-comment" readonly></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning cancel_reception" data-bs-dismiss="modal"
                            data-reception-id="">Отменить
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div id="doctor-calendars">
        <div class="card shadow-sm mb-5">
            <div class="card-header">
                <div class="d-flex flex-row w-100 align-items-center justify-content-between">
                        <h5>{{ $staff->name }} {{ $staff->last_name }}</h5>
                    <div>
                        <p class="mb-0">Длительность приема: {{ $staff->professionalData->admission_period }}</p>
                        <p class="mb-0">Должность: {{ $staff->professionalData->position->name }}</p>
                        <p class="mb-0">Специализация: {{ $staff->professionalData->speciality->name }}</p>
                    </div>
                </div>

                <div>
                    <strong>Легенда:</strong>
                    <span class="badge bg-success">Свободно</span>
                    <span class="badge bg-info">Забронировано</span>
                    <span class="badge bg-warning text-dark">Обед</span>
                    <span class="badge bg-danger">Недоступно</span>
                    <span class="badge bg-secondary">Прошедшая дата</span>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered text-center doctor-calendar" data-doctor-id="{{ $staff->id }}">
                    <thead class="table-dark">
                    <tr id="days-row">
                        <th>Время</th>
                        <th>Пн</th>
                        <th>Вт</th>
                        <th>Ср</th>
                        <th>Чт</th>
                        <th>Пт</th>
                        <th>Сб</th>
                        <th>Вс</th>
                    </tr>
                    </thead>
                    <tbody id="calendar-body">
                    {{-- Заполняем через JS --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <script>
        $(document).ready(function () {
            let currentStartOfWeek = moment().startOf('isoWeek');

            function updateWeekText() {
                const endOfWeek = moment(currentStartOfWeek).endOf('isoWeek');
                $('#current-week').text(currentStartOfWeek.format('DD.MM') + ' - ' + endOfWeek.format('DD.MM'));
            }

            function loadCalendars() {
                $('.doctor-calendar').each(function () {
                    const doctorId = $(this).data('doctor-id');
                    const $thead = $(this).find('thead');
                    const $tbody = $(this).find('tbody');

                    $tbody.empty();
                    $thead.find('#dates-row').remove();

                    const $daysRow = $(`
                <tr id="days-row">
                    <th>Время</th>
                    <th>Пн</th>
                    <th>Вт</th>
                    <th>Ср</th>
                    <th>Чт</th>
                    <th>Пт</th>
                    <th>Сб</th>
                    <th>Вс</th>
                </tr>
            `);
                    const $datesRow = $('<tr id="dates-row"></tr>');
                    $datesRow.append('<th>Дата</th>');

                    for (let i = 0; i < 7; i++) {
                        const day = moment(currentStartOfWeek).add(i, 'days');
                        const formattedDate = day.format('DD.MM');
                        $datesRow.append(`<th>${formattedDate}</th>`);
                    }

                    $thead.empty().append($daysRow).append($datesRow);

                    const times = generateTimes('{{ $staff->professionalData->admission_period }}');

                    times.forEach(time => {
                        const $row = $('<tr></tr>');
                        $row.append(`<td>${time}</td>`);

                        for (let i = 0; i < 7; i++) {
                            const day = moment(currentStartOfWeek).add(i, 'days').format('YYYY-MM-DD');
                            $row.append(`<td data-doctor="${doctorId}" data-time="${time}" data-day="${day}" class="status-cell bg-danger text-white fw-semibold">Недоступно</td>`);
                        }

                        $tbody.append($row);
                    });

                    fetchDoctorSlots(doctorId);
                });
            }

            function generateTimes(intervalMinutes) {
                const times = [];
                let start = moment('00:00', 'HH:mm');
                const end = moment('23:59', 'HH:mm');

                while (start <= end) {
                    times.push(start.format('HH:mm'));
                    start.add(intervalMinutes, 'minutes');
                }
                return times;
            }

            function fetchDoctorSlots(doctorId) {
                $.ajax({
                    url: `/api/staff-availability-week/${doctorId}?start=${currentStartOfWeek.format('YYYY-MM-DD')}`,
                    method: 'GET',
                    success: function (slots) {
                        slots.forEach(slot => {
                            const selector = `td[data-doctor="${doctorId}"][data-day="${slot.date}"][data-time="${slot.time}"]`;
                            const $cell = $(selector);

                            if ($cell.length) {
                                if (slot.is_available && !slot.is_booked) {
                                    $cell.removeClass('bg-danger')
                                    $cell.addClass('bg-success').text('Свободно');
                                }

                                if (slot.is_booked) {
                                    $cell.removeClass('bg-success')
                                    $cell.removeClass('bg-danger')
                                    $cell.addClass('bg-info').text('Забронировано');
                                    $cell.attr('data-reception-id', slot.reception_id);
                                }
                            }
                        });
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            $('#next-week-btn').click(function () {
                currentStartOfWeek.add(7, 'days').startOf('isoWeek');
                updateWeekText();
                loadCalendars();
            });

            $('#prev-week-btn').click(function () {
                currentStartOfWeek.subtract(7, 'days').startOf('isoWeek');
                updateWeekText();
                loadCalendars();
            });

            updateWeekText();
            loadCalendars();

            $('#calendar-body').on('click', 'td.bg-success', function () {
                let time = $(this).data('time');
                let doctorId = $(this).data('doctor');
                let adPeriod = {{ $staff->professionalData->admission_period }};
                let selectedDate = $(this).data('day');

                console.log(selectedDate)

                $('#appointment-time').val(time + ' - ' + moment(time, 'HH:mm').add(adPeriod, 'minutes').format('HH:mm'));
                $('#start-time').val(time);
                $('#end-time').val(moment(time, 'HH:mm').add(adPeriod, 'minutes').format('HH:mm'));
                $('#doctor-name').val('{{ $staff->name }}');
                $('#doctor-id').val(doctorId);
                $('#doctor-position').val('{{ $staff->professionalData->position->name }}');
                $('#appointment-date').val(selectedDate);

                $('#appointmentModal').modal('show');
            })

            $('#appointmentModal').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    placeholder: "Выберите пациента",
                    dropdownParent: $('#appointmentModal'),
                    ajax: {
                        url: '/api/patients',
                        dataType: 'json',
                        delay: 250,
                        processResults: function (data) {
                            return {
                                results: data.map(function (patient) {
                                    return {
                                        id: patient.id,
                                        text: patient.first_name + ' ' + patient.last_name
                                    }
                                })
                            };
                        },
                        cache: false
                    }
                });
            });

            // Уничтожение Select2 при закрытии модалки
            $('#appointmentModal').on('hidden.bs.modal', function () {
                $('.select2-modal').select2('destroy');
            });

            $('#appointment-form').on('submit', function (e) {
                e.preventDefault();
                let patientId = $('#patient').val();
                let staffId = $('#doctor-id').val()
                let startTime = $('#start-time').val();
                let endTime = $('#end-time').val();
                let date = $('#appointment-date').val();
                let status = $('#appointment-status').val();
                let comment = $('#comment').val();

                if (!patientId || !startTime || !staffId || !endTime || !date) {
                    alert('Пожалуйста, заполните все обязательные поля');
                    return;
                }

                $.ajax({
                    url: '/api/receptions',
                    method: 'POST',
                    data: {
                        patient_id: patientId,
                        staff_id: staffId,
                        status: status,
                        start_time: startTime,
                        end_time: endTime,
                        date: date,
                        comment: comment,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        alert('Запись успешно создана!');
                        $('#appointmentModal').modal('hide');
                        fetchDoctorSlots(staffId);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });


            // Обработчик клика на забронированную ячейку
            $('#calendar-body').on('click', 'td.bg-info', function() {
                const receptionId = $(this).data('reception-id');

                $.ajax({
                    url: `/api/receptions/${receptionId}`,
                    method: 'GET',
                    success: function(data) {
                        let status = {
                            'new': 'Новое',
                            'paid': 'Оплачено',
                            'cancelled': 'Отмена',
                            'done': 'Выполнено'
                        }

                        // Заполняем данные в модальное окно просмотра
                        $('#view-patient').val(data.patient.first_name + ' ' + data.patient.last_name);
                        $('#view-appointment-time').val(
                            moment(data.start_time, 'HH:mm:ss').format('HH:mm') + ' - ' +
                            moment(data.end_time, 'HH:mm:ss').format('HH:mm')
                        );
                        $('#view-doctor-name').val(data.staff.name + ' ' + data.staff.last_name);
                        $('#view-doctor-position').val(data.staff.professional_data.position.name);
                        $('#view-appointment-status').val(status[data.status]);
                        $('#view-appointment-date').val(data.date);
                        $('#view-comment').val(data.comment);

                        if (data.status == 'cancelled') {
                            $('.cancel_reception').hide();
                        } else {
                            $('.cancel_reception').show();
                            $('.cancel_reception').attr('data-reception-id', data.id);
                        }

                        // Показываем модальное окно просмотра
                        $('#viewAppointmentModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.cancel_reception', function () {
                const receptionId = $(this).data('reception-id');

                $.ajax({
                    url: `/api/reception-status-update/${receptionId}`,
                    method: 'PUT',
                    data: {
                        status: 'cancelled',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#viewAppointmentModal').modal('hide');

                        alert('Прием успешно отменен');
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert('Произошла ошибка при отмене приема');
                    }
                });
            });
        });
    </script>
@endsection
