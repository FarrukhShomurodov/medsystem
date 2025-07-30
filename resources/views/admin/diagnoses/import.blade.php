@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Импорт</title>
@endsection

@section('content')
    <form action="{{ route('diagnoses.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-primary mt-2">Импортировать</button>
    </form>
@endsection
