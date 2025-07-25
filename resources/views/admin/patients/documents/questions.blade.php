@extends('admin.layouts.app')

@section('content')
    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center  py-2 px-3 mb-2" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="container mt-3">
        <h5 class="mb-3">Заполнение документа: <strong>{{ $document->title }}</strong></h5>

        <form action="{{ route('document.response.store') }}" method="POST" class="">
            @csrf

            <input type="hidden" name="document_id" value="{{ $document->id }}">
            <input type="hidden" name="patient_id" value="{{ $patientId }}">
            <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

            <div class="row">
                @foreach($document->questions as $question)
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold mb-1">{{ $loop->iteration }}. {{ $question->question }}</label>

                        @if($question->type === 'text')
                            <input type="text" name="answers[{{ $question->id }}]" class="form-control form-control-sm" required>

                        @elseif($question->type === 'boolean')
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="answers[{{ $question->id }}]" value="0">
                                <input class="form-check-input" type="checkbox"
                                       id="checkbox_{{ $question->id }}"
                                       name="answers[{{ $question->id }}]"
                                       value="1">
                                <label class="form-check-label " for="checkbox_{{ $question->id }}">Да</label>
                            </div>

                        @elseif($question->type === 'choice')
                            <div class="d-flex flex-column gap-1 mt-1">
                                @foreach($question->options ?? [] as $option)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="answers[{{ $question->id }}]"
                                               value="{{ $option }}"
                                               id="radio_{{ $question->id }}_{{ $loop->index }}"
                                               required>
                                        <label class="form-check-label" for="radio_{{ $question->id }}_{{ $loop->index }}">
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Назад</a>
            </div>
        </form>
    </div>
@endsection
