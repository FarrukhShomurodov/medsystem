@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Импорт</title>
@endsection

@section('content')
    <form action="{{ route('dangerous.combination.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-primary mt-2">Импортировать</button>
    </form>

    <div class="card shadow-sm mt-3">
        <div class="card-datatable table-responsive">
            <table class="datatables-users table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Drug 1</th>
                    <th>Препарат 1</th>
                    <th>Drug 2</th>
                    <th>Препарат 2</th>
                    <th>Severity</th>
                    <th>Серьёзность</th>
                    <th>Mechanism</th>
                    <th>Механизм</th>
                    <th>Interaction/Comment</th>
                    <th>Взаимодействие/Комментарий</th>
                </tr>
                </thead>
                <tbody>
                @foreach($combinations as $combination)
                    <tr class="cursor-pointer">
                        <td>{{ $combination->id }}</td>
                        <td>{{ $combination->drug1_en }}</td>
                        <td>{{ $combination->drug1_ru }}</td>
                        <td>{{ $combination->drug2_en }}</td>
                        <td>{{ $combination->drug2_ru }}</td>
                        <td>{{ $combination->severity_en }}</td>
                        <td>{{ $combination->severity_ru }}</td>
                        <td>{{ $combination->mechanism_en }}</td>
                        <td>{{ $combination->mechanism_ru }}</td>
                        <td>{{ $combination->comment_en }}</td>
                        <td>{{ $combination->comment_ru }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
