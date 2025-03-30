@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Просмотр опросника</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('surveys.index') }}">Опросники </a> /</span>Просмотр
    </h6>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $survey->title }}</h4>
        </div>
        <div class="card-body">
            <p><strong>Тип:</strong> <span class="badge bg-info">{{ $survey->type->name }}</span></p>

            @foreach($survey->questions as $question)
                <div class="mb-3 border-bottom pb-2">
                    <h6 class="mb-1">{{ $question->question }}</h6>
                    <span class="badge bg-primary">
                        @php
                            $types = ['choice' => 'Список', 'boolean' => 'Да / Нет', 'text' => 'Текст'];
                        @endphp
                        {{ $types[$question->type] ?? 'Неизвестный тип' }}
                    </span>

                    @if($question->type === 'choice' && $question->options)
                        <div class="mt-2">
                            @foreach($question->options as $option)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="question_{{ $question['id'] }}"
                                           id="option_{{ $loop->index }}" value="{{ $option['text'] }}">
                                    <label class="form-check-label" for="option_{{ $loop->index }}">
                                        {{ $option['text'] }} <small>(Бал: {{ $option['score'] }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <a href="{{ route('surveys.index') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back"></i> Назад
            </a>
        </div>
    </div>
@endsection
