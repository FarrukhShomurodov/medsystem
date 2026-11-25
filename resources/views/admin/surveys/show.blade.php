@extends('admin.layouts.app')

@section('title', $survey->title)

@section('header')
    <x-page-header :title="$survey->title" :breadcrumbs="['Мед. документы' => route('surveys.index')]">
        @can('edit_surveys')
            <x-slot:actions>
                <a href="{{ route('surveys.edit', $survey) }}" class="btn">Изменить</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div>
                <div class="text-secondary small">Тип</div>
                <span class="badge bg-info-lt">{{ $survey->type->name }}</span>
            </div>
        </div>
        <div class="card-body">
            @forelse ($survey->questions->groupBy('group.title') as $groupName => $questions)
                @if ($groupName !== '')
                    <h2 class="h3 text-primary @unless ($loop->first) mt-4 @endunless">{{ $groupName }}</h2>
                @endif

                @foreach ($questions as $question)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h3 class="h4 mb-0">{{ $question->question }}</h3>
                            <span class="badge bg-primary-lt">{{ $question->type->label() }}</span>
                        </div>

                        @if ($question->type === \App\Enums\QuestionType::Choice && $question->options)
                            {{-- A preview of the answers: nothing is submitted from here. --}}
                            <div class="mt-2">
                                @foreach ($question->options as $option)
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="question_{{ $question->id }}" value="{{ $option['text'] }}" disabled>
                                        <span class="form-check-label">{{ $option['text'] }} <small class="text-secondary">(баллы: {{ $option['score'] ?? 0 }})</small></span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @empty
                <x-empty-state title="В документе нет вопросов" icon="bx-list-check" />
            @endforelse
        </div>
        <div class="card-footer">
            <a href="{{ route('surveys.index') }}" class="btn"><i class="bx bx-arrow-back" aria-hidden="true"></i>Назад</a>
        </div>
    </div>
@endsection
