@extends('admin.layouts.app')

@section('title', 'Опасные комбинации препаратов')

@section('header')
    <x-page-header title="Опасные комбинации препаратов" :breadcrumbs="['Препараты' => route('medications.index')]" />
@endsection

@section('content')
    <div class="card mb-3">
        <form method="POST" action="{{ route('dangerous.combination.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <label class="form-label required" for="file">Файл</label>
                        <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror"
                               accept=".xlsx,.xls,.csv" aria-describedby="file-hint" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint" id="file-hint">Таблица Excel (xlsx, xls) или CSV, до 10 МБ.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bx bx-import" aria-hidden="true"></i>Импортировать</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Загруженные комбинации ({{ $combinations->total() }})</h2>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th lang="en">Drug 1</th>
                    <th>Препарат 1</th>
                    <th lang="en">Drug 2</th>
                    <th>Препарат 2</th>
                    <th lang="en">Severity</th>
                    <th>Серьёзность</th>
                    <th lang="en">Mechanism</th>
                    <th>Механизм</th>
                    <th lang="en">Interaction/Comment</th>
                    <th>Взаимодействие/Комментарий</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($combinations as $combination)
                    <tr>
                        <td class="text-secondary">{{ $combination->id }}</td>
                        <td lang="en">{{ $combination->drug1_en }}</td>
                        <td>{{ $combination->drug1_ru }}</td>
                        <td lang="en">{{ $combination->drug2_en }}</td>
                        <td>{{ $combination->drug2_ru }}</td>
                        <td lang="en">{{ $combination->severity_en }}</td>
                        <td>{{ $combination->severity_ru }}</td>
                        <td lang="en">{{ $combination->mechanism_en }}</td>
                        <td>{{ $combination->mechanism_ru }}</td>
                        <td lang="en">{{ $combination->comment_en }}</td>
                        <td>{{ $combination->comment_ru }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11"><x-empty-state title="Комбинации ещё не загружены" text="Загрузите таблицу выше." icon="bx-error" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($combinations->hasPages())
            <div class="card-footer">{{ $combinations->links() }}</div>
        @endif
    </div>
@endsection
