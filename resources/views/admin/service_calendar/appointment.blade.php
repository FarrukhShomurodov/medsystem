@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Назначение услуги</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Назначение услуги</span>
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif

        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div id="errors_alert"></div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between flex-column">
            <h5 class="card-header">Назначение услуги</h5>

            <div class="m-3">
                <label for="service">Услуги</label>
                <select class="form-control select2" id="service" name="service">
                    <option value="" selected disabled> Выберите услугу</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}"> {{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="m-3">
                <strong>Легенда:</strong>
                <span class="badge bg-success">Свободно</span>
                <span class="badge bg-info">Забронировано</span>
                <span class="badge bg-warning text-dark">Обед</span>
                <span class="badge bg-danger">Недоступно</span>
                <span class="badge bg-secondary">Прошедшая дата</span>
            </div>
        </div>

        <!-- Модальное окно -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">Назначение услуги</h5>
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

        <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel"
             aria-hidden="true">
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

        <div class="table-responsive" id="calendar-container">
            <table class="table table-bordered text-center" id="calendar-table" style="min-width: 1200px;">
                <thead class="table-dark">
                <tr id="calendar-header">
                    <th>Время</th>
                    <!-- Сюда динамически подставим имена врачей -->
                </tr>
                </thead>
                <tbody id="calendar-body">
                <!-- Сюда динамически подставим расписание -->
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let minAdmissionPeriod;
            let staff;

            $('#service').on('change', function () {
                const serviceId = $(this).val();

                $.ajax({
                    url: '/api/staff-by-service/' + serviceId,
                    method: 'GET',
                    success: function (response) {
                        staff = response;

                        // Очистить старую таблицу
                        $('#calendar-header').html('<th>Время</th>');
                        $('#calendar-body').empty();

                        // Найти минимальный admission_period
                        minAdmissionPeriod = Math.min(...staff.map(doc => doc.admission_period));

                        // Построить тайм-слоты с минимальным интервалом
                        let times = generateTimes(minAdmissionPeriod);

                        // Заполнить заголовок
                        staff.forEach(function (doc) {
                            $('#calendar-header').append(
                                '<th data-doctor="' + doc.id + '">' +
                                doc.first_name + ' ' + doc.last_name +
                                ' <a href="/dashboard/receptions-doctor-calendar/' + doc.id + '" class="btn btn-sm calendar-button text-white calendar-icon-button" data-doctor-id="' + doc.id + '"><i class="fas fa-calendar-alt"></i></a>' +
                                '</th>'
                            );
                        });


                        // Заполнить тело таблицы
                        times.forEach(function (time) {
                            let row = '<tr><td>' + time + '</td>';
                            staff.forEach(function (doc) {
                                row += '<td class="status-cell bg-danger text-white fw-semibold" data-time="' + time + '" data-doctor="' + doc.id + '" data-admission-period="' + doc.admission_period + '">Недоступно</td>';
                            });
                            row += '</tr>';
                            $('#calendar-body').append(row);
                        });

                        fetchAvailability(staff, minAdmissionPeriod);
                        fetchBooked(staff, minAdmissionPeriod);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });

            function fetchAvailability(staff, minAdmissionPeriod) {
                staff.forEach(function (doc) {
                    $.ajax({
                        url: '/api/staff-availability-today/' + doc.id,
                        method: 'GET',
                        success: function (response) {
                            let availability = response;

                            availability.forEach(function (slot) {
                                let startTime = moment(slot.time, 'HH:mm');
                                let admissionPeriod = slot.admission_period;
                                let endTime = moment(startTime).add(admissionPeriod, 'minutes');

                                let currentTime = moment(startTime);

                                do {
                                    let cellSelector = 'td.status-cell[data-time="' + currentTime.format('HH:mm') + '"][data-doctor="' + doc.id + '"]';
                                    let cell = $(cellSelector);

                                    if (cell.length) {
                                        if (slot.is_available) {
                                            cell.removeClass('bg-danger bg-info').addClass('bg-success').text('Свободно');
                                        } else {
                                            cell.removeClass('bg-success bg-info').addClass('bg-danger').text('Недоступно');
                                        }
                                    }

                                    currentTime.add(minAdmissionPeriod, 'minutes');
                                } while (currentTime.isBefore(endTime));

                            });
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                });
            }

            function fetchBooked(staff, minAdmissionPeriod) {
                staff.forEach(function (doc) {
                    $.ajax({
                        url: '/api/staff-booked-list-today/' + doc.id,
                        method: 'GET',
                        success: function (response) {
                            let availability = response;
                            availability.forEach(function (slot) {
                                let startTime = moment(slot.time, 'HH:mm');
                                let admissionPeriod = slot.admission_period;
                                let endTime = moment(startTime).add(admissionPeriod, 'minutes');

                                let currentTime = moment(startTime);

                                do {
                                    let cellSelector = 'td.status-cell[data-time="' + currentTime.format('HH:mm') + '"][data-doctor="' + doc.id + '"]';
                                    let cell = $(cellSelector);

                                    if (cell.length) {
                                        if (slot.is_booked) {
                                            cell.removeClass('bg-success')
                                            cell.removeClass('bg-danger')
                                            cell.addClass('bg-info').text('Забронировано');
                                            cell.attr('data-reception-id', slot.reception_id);
                                        }
                                    }

                                    currentTime.add(minAdmissionPeriod, 'minutes');
                                } while (currentTime.isBefore(endTime));

                            });
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                });
            }

            // Функция генерации времени
            function generateTimes(interval) {
                let times = [];
                let startTime = moment('00:00', 'HH:mm');
                let endTime = moment('23:59', 'HH:mm');

                while (startTime <= endTime) {
                    times.push(startTime.format('HH:mm'));
                    startTime.add(interval, 'minutes');
                }
                return times;
            }

            $('#calendar-body').on('click', 'td.bg-success', function () {
                let time = $(this).data('time');
                let doctorId = $(this).data('doctor');
                let adPeriod = $(this).data('admission-period');
                let doctorName = $(this).closest('table').find('th[data-doctor="' + doctorId + '"]').text();
                let position = $('#position option:selected').text();

                $('#appointment-time').val(time + ' - ' + moment(time, 'HH:mm').add(adPeriod, 'minutes').format('HH:mm'));
                $('#start-time').val(time);
                $('#end-time').val(moment(time, 'HH:mm').add(adPeriod, 'minutes').format('HH:mm'));
                $('#doctor-name').val(doctorName);
                $('#doctor-id').val(doctorId);
                $('#doctor-position').val(position);

                let yourDate = new Date()
                yourDate.toISOString().split('T')[0]
                const offset = yourDate.getTimezoneOffset()
                yourDate = new Date(yourDate.getTime() - (offset * 60 * 1000))

                $('#appointment-date').val(yourDate.toISOString().split('T')[0]);

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
                        start_time: startTime,
                        end_time: endTime,
                        date: date,
                        status: status,
                        comment: comment,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        alert('Запись успешно создана!');
                        $('#appointmentModal').modal('hide');
                        fetchBooked(staff, minAdmissionPeriod);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Обработчик клика на забронированную ячейку
            $('#calendar-body').on('click', 'td.bg-info', function () {
                const receptionId = $(this).data('reception-id');

                $.ajax({
                    url: `/api/receptions/${receptionId}`,
                    method: 'GET',
                    success: function (data) {
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
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Обработчик клика по кнопке "Отменить"
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
