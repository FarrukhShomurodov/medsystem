@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Переводы</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Переводы</span>
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Переводы</h5>
            <a href="{{ route('transfers.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Со склада</th>
                    <th>В аптеку</th>
                    <th>Статус</th>
                    <th>Препараты</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->id }}</td>
                        <td>{{ $transfer->fromWarehouse->name }}</td>
                        <td>{{ $transfer->toPharmacy->name }}</td>
                        <td>
                            <select class="form-select update-status"
                                    data-transfer-id="{{ $transfer->id }}">
                                <option value="pending" {{ $transfer->status == 'pending' ? 'selected' : '' }}>В
                                    ожидании
                                </option>
                                <option value="completed" {{ $transfer->status == 'completed' ? 'selected' : '' }}>
                                    Завершен
                                </option>
                                {{--                                <option value="canceled" {{ $transfer->status == 'canceled' ? 'selected' : '' }}>Отменен</option>--}}
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                    onclick="location.href='{{ route('transfer.medications', $transfer->id) }}'">
                                Просмотреть
                            </button>
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-warning"
                                        onclick="location.href='{{ route('transfers.edit', $transfer->id) }}'">
                                    Редактировать
                                </button>
                                <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4 p-1">
                {{ $transfers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.update-status').forEach(select => {
            select.addEventListener('change', function () {
                let transferId = this.dataset.transferId;
                let newStatus = this.value;

                fetch(`/api/transfers/${transferId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({status: newStatus})
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Статус успешно обновлен');
                        } else {
                            console.log('Ошибка: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.log('Ошибка обновления статуса');
                        console.error(error);
                    });
            });
        });
    </script>
@endsection
