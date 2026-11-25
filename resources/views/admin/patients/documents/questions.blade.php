@extends('admin.layouts.app')

@section('title', $document->title)

@section('header')
    <x-page-header :title="$document->title" pretitle="Заполнение документа" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('document.response.store') }}" method="POST">
            @csrf
            <input type="hidden" name="document_id" value="{{ $document->id }}">
            <input type="hidden" name="patient_id" value="{{ $patientId }}">
            <input type="hidden" name="previous_url" value="{{ $back }}">

            <div class="card-body">
                <div class="row g-3">
                    @foreach ($document->questions as $question)
                        @php $field = 'answers.'.$question->id; @endphp
                        <div class="col-md-6">
                            @if ($question->type === \App\Enums\QuestionType::Text)
                                <label class="form-label required" for="answer-{{ $question->id }}">{{ $loop->iteration }}. {{ $question->question }}</label>
                                <input type="text" id="answer-{{ $question->id }}" name="answers[{{ $question->id }}]"
                                       class="form-control @error($field) is-invalid @enderror" value="{{ old($field) }}" required>
                            @elseif ($question->type === \App\Enums\QuestionType::Boolean)
                                <div class="form-label">{{ $loop->iteration }}. {{ $question->question }}</div>
                                <input type="hidden" name="answers[{{ $question->id }}]" value="0">
                                <label class="form-check form-switch" for="checkbox_{{ $question->id }}">
                                    <input class="form-check-input" type="checkbox" id="checkbox_{{ $question->id }}"
                                           name="answers[{{ $question->id }}]" value="1" @checked(old($field) === '1')>
                                    <span class="form-check-label">Да</span>
                                </label>
                            @elseif ($question->type === \App\Enums\QuestionType::Choice)
                                <fieldset>
                                    <legend class="form-label required">{{ $loop->iteration }}. {{ $question->question }}</legend>
                                    @foreach ($question->options ?? [] as $option)
                                        <label class="form-check" for="radio_{{ $question->id }}_{{ $loop->index }}">
                                            <input class="form-check-input @error($field) is-invalid @enderror" type="radio"
                                                   name="answers[{{ $question->id }}]" value="{{ $option }}"
                                                   id="radio_{{ $question->id }}_{{ $loop->index }}" @checked(old($field) === $option) required>
                                            <span class="form-check-label">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </fieldset>
                            @endif
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ $back }}" class="btn">Назад</a>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
