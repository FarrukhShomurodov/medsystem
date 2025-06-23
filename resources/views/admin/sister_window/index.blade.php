@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Сестринское окно</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Сестринское окно</span>
    </h6>

    {{-- Блок со списком пациентов --}}
    <div class="card shadow-sm" id="patientListCard">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Пациенты </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ФИО / ID</th>
                    <th>ШРПУ</th>
                    <th>Лист Назначений</th>
                    <th>План лечения</th>
                    <th>Приход / Списание</th>
                </tr>
                </thead>
                <tbody>
                @foreach($patients as $patient)
                    <tr class="patient-row cursor-pointer" data-id="{{ $patient->id }}">
                        <td>{{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->middle_name }}
                            ID{{ $patient->id }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Блок подробной таблицы пациента --}}
    <div class="card shadow-sm d-none" id="patientDetailsCard">
        <div class="card-datatable table-responsive">
            <table class="table table-bordered">
                <thead id="tableHead"></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="createServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Назначение услуги</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('patient.inspection.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bp" class="form-label">АД</label>
                                <input type="text" name="bp" id="bp" class="form-control" placeholder="Напр. 120/80">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="pulse" class="form-label">Пульс</label>
                                <input type="number" name="pulse" id="pulse" class="form-control"
                                       placeholder="Напр. 72">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="saturation" class="form-label">Сатурация</label>
                                <input type="number" name="saturation" id="saturation" class="form-control"
                                       placeholder="Напр. 98">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="temperature" class="form-label">Температура</label>
                                <input type="text" name="temperature" id="temperature" class="form-control"
                                       placeholder="Напр. 36.6">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="chdd" class="form-label">ЧДД</label>
                                <input type="number" name="chdd" id="chdd" class="form-control"
                                       placeholder="Частота дыхания">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="consciousness_level" class="form-label">Уровень сознания</label>
                                <input type="text" name="consciousness_level" id="consciousness_level"
                                       class="form-control" placeholder="Напр. ясное">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="oxygen_status" class="form-label">Кислород или на воздухе</label>
                                <input type="text" name="oxygen_status" id="oxygen_status" class="form-control"
                                       placeholder="Напр. на воздухе">
                            </div>
                        </div>

                        <input type="hidden" name="patient_id" id="modalPatientId">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).on('click', '.open-examination-modal', function () {
            const patientId = $(this).data('patient-id');
            $('#modalPatientId').val(patientId);
        });

        let activePatientId = null;

        function calculateAge(birthDateStr) {
            if (!birthDateStr) return '-';

            const birthDate = new Date(birthDateStr);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            // Уменьшаем возраст, если день рождения ещё не наступил в этом году
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return `${age} лет`;
        }

        function calculateAgeInDays(birthDateStr) {
            if (!birthDateStr) return '-';

            const birthDate = new Date(birthDateStr);
            const today = new Date();

            const timeDiff = today.getTime() - birthDate.getTime();
            const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

            return `${days} дней`;
        }

        $(document).on('click', '.patient-row', function () {
            const patientId = $(this).data('id');

            // Если повторно нажали — вернуть обратно
            if (activePatientId === patientId) {
                $('#patientDetailsCard').addClass('d-none');
                $('#patientListCard').removeClass('d-none');
                activePatientId = null;
                return;
            }

            fetch(`/api/patient/${patientId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка при загрузке пациента');
                    return response.json();
                })
                .then(patient => {
                    const tableHead = `
                    <tr>
                        <th class="cursor-pointer">ФИО</th>
                        <th>Возраст</th>
                        <th>№ Палаты</th>
                        <th>Лист Назначений</th>
                        <th>План лечения</th>
                        <th>Приход / Списание</th>
                        <th>Дней в стационаре</th>
                        <th></th>
                    </tr>`;

                    const tableBody = `
                    <tr>
                        <td>${patient.first_name} ${patient.last_name} ${patient.middle_name}</td>
                        <td>${calculateAge(patient.birth_date)}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>${patient.examination_maps.length > 0 ? calculateAgeInDays(patient.examination_maps[patient.examination_maps.length - 1].admission_date) : '-'}</td>
                        <td>
                            <button type="button"
                                    class="btn btn-primary me-3 open-examination-modal"
                                    data-bs-toggle="modal"
                                    data-patient-id="${patient.id}"
                                    data-bs-target="#createServiceModal">
                                Добавить осмотр
                            </button>
                        </td>
                    </tr>`;

                    $('#tableHead').html(tableHead);
                    $('#tableBody').html(tableBody);

                    $('#patientListCard').addClass('d-none');
                    $('#patientDetailsCard').removeClass('d-none');

                    activePatientId = patientId;
                })
                .catch(error => {
                    console.error(error);
                    alert('Не удалось загрузить данные пациента.');
                });
        });

        // Назад при клике по ячейке
        $(document).on('click', '#tableBody td:first-child', function () {
            $('#patientDetailsCard').addClass('d-none');
            $('#patientListCard').removeClass('d-none');
            activePatientId = null;
        });
    </script>
@endsection
