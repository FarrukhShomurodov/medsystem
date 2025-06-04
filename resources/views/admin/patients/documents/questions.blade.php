@extends('admin.layouts.app')

@section('content')
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
    </div>

    <div class="container mt-4">
        <h3 class="mb-4">Заполнение документа: {{ $document->title }}</h3>

        <form action="{{ route('document.response.store') }}" method="POST">
            @csrf

            <input type="hidden" name="document_id" value="{{ $document->id }}">
            <input type="hidden" name="patient_id" value="{{ $patientId }}">

            @foreach($document->questions as $question)
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        {{ $question->question }}
                    </label>

                    @if($question->type === 'text')
                        <input type="text" name="answers[{{ $question->id }}]" class="form-control" required>
                    @elseif($question->type === 'boolean')
                        <select name="answers[{{ $question->id }}]" class="form-select" required>
                            <option value="">Выберите</option>
                            <option value="1">Да</option>
                            <option value="0">Нет</option>
                        </select>
                    @elseif($question->type === 'choice')
                        <select name="answers[{{ $question->id }}]" class="form-select" required>
                            <option value="">Выберите</option>
                            @foreach($question->options ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Назад</a>
        </form>
    </div>
@endsection
