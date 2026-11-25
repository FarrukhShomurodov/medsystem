@extends('admin.layouts.app')

@section('title', 'Переводы')

@section('header')
    <x-page-header title="Переводы" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_transfers')
                <a href="{{ route('transfers.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Дата</th>
                    <th>Со склада</th>
                    <th>В аптеку</th>
                    <th>Препаратов</th>
                    <th>Статус</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transfers as $transfer)
                    @php $status = $transfer->status; @endphp
                    <tr>
                        <td>
                            @can('view_transfer_medications')
                                <a href="{{ route('transfer.medications', $transfer) }}">{{ $transfer->id }}</a>
                            @else
                                {{ $transfer->id }}
                            @endcan
                        </td>
                        <td>{{ $transfer->transfer_date?->format('d.m.Y') }}</td>
                        <td>{{ $transfer->fromWarehouse->name }}</td>
                        <td>{{ $transfer->toPharmacy->name }}</td>
                        <td>
                            @can('view_transfer_medications')
                                <a href="{{ route('transfer.medications', $transfer) }}">{{ $transfer->medications_count }}</a>
                            @else
                                {{ $transfer->medications_count }}
                            @endcan
                        </td>
                        <td>
                            @if ($status->isEditable() && auth()->user()->can('edit_transfers'))
                                {{-- Completing moves the stock; the server refuses a transfer the warehouse cannot cover.
                                     Both next statuses are final (the transfer can no longer be edited or deleted),
                                     so each asks its question first. --}}
                                @php
                                    $confirmations = [
                                        \App\Enums\TransferStatus::Completed->value => ['Завершить перевод? Препараты будут списаны со склада и зачислены в аптеку.', 'Завершить перевод'],
                                        \App\Enums\TransferStatus::Canceled->value => ["Отменить перевод №{$transfer->id}? Отменённый перевод нельзя изменить или вернуть.", 'Отменить перевод'],
                                    ];
                                @endphp
                                <select class="form-select form-select-sm w-auto transfer-status" aria-label="Статус перевода №{{ $transfer->id }}"
                                        data-url="{{ route('api.transfers.update-status', $transfer) }}"
                                        data-current="{{ $status->value }}">
                                    <option value="{{ $status->value }}" selected>{{ $status->label() }}</option>
                                    @foreach ($status->nextStatuses() as $next)
                                        @php [$question, $accept] = $confirmations[$next->value] ?? [null, null]; @endphp
                                        <option value="{{ $next->value }}" @if ($question) data-question="{{ $question }}" data-accept="{{ $accept }}" @endif>
                                            {{ $next->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <x-status-badge :status="$status" />
                            @endif
                        </td>
                        <td>
                            @if ($status->isEditable())
                                <div class="table-actions">
                                    @can('edit_transfers')
                                        <a href="{{ route('transfers.edit', $transfer) }}" class="btn btn-sm">Изменить</a>
                                    @endcan
                                    @can('delete_transfers')
                                        <x-delete-button :action="route('transfers.destroy', $transfer)"
                                                         :confirm="'Удалить перевод №'.$transfer->id.'? Действие нельзя отменить.'" />
                                    @endcan
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><x-empty-state text="Переводы препаратов со складов в аптеки появятся здесь." icon="bx-transfer" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($transfers->hasPages())
            <div class="card-footer">{{ $transfers->links() }}</div>
        @endif
    </div>
@endsection

@section('scripts')
    @can('edit_transfers')
        <script>
            // Changing the status of a pending transfer; a final status (completed, cancelled) asks first,
            // and a refusal puts the select back.
            $(document).on('change', '.transfer-status', async function () {
                const $select = $(this);
                const $option = $select.find(':selected');
                const question = $option.data('question');
                const restore = () => $select.val($select.data('current'));

                if (question && !(await MedSystem.confirm(question, {accept: $option.data('accept')}))) {
                    restore();

                    return;
                }

                $.ajax({url: $select.data('url'), type: 'PATCH', data: {status: $select.val()}})
                    .done(() => window.location.reload())
                    .fail((xhr) => {
                        MedSystem.flash(xhr.responseJSON?.message ?? 'Не удалось изменить статус перевода.', 'danger');
                        restore();
                    });
            });
        </script>
    @endcan
@endsection
