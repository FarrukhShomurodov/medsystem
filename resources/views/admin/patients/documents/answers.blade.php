@extends('admin.layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Ответы на документ: {{ $documentResponse->document->title }}</h3>

        <div class="card">
            <div class="card-body">
                @foreach($documentResponse->document->questions as $question)
                    @php
                        $answer = $documentResponse->answers->firstWhere('question_id', $question->id);
                    @endphp

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ $question->question }}</label>
                        <div class="form-control bg-light">
                            @if($question->type === 'boolean')
                                {{ $answer?->answer ? 'Да' : 'Нет' }}
                            @else
                                {{ $answer?->answer ?? '-' }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-4">Назад</a>
    </div>
@endsection
