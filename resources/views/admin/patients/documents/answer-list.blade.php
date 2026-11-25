{{--
    The answers of a filled-in nursing document (documents.show.answer and the patient card history).

    @include('admin.patients.documents.answer-list', ['documentResponse' => $documentResponse])
--}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Ответы на документ: {{ $documentResponse->document->title }}</h2>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            @foreach ($documentResponse->document->questions as $question)
                @php $answer = $documentResponse->answers->firstWhere('question_id', $question->id); @endphp
                <dt class="col-md-6 mb-2">{{ $question->question }}</dt>
                <dd class="col-md-6 mb-2">
                    @if ($question->type === \App\Enums\QuestionType::Boolean)
                        {{ $answer?->answer ? 'Да' : 'Нет' }}
                    @else
                        {{ $answer?->answer ?? '—' }}
                    @endif
                </dd>
            @endforeach
        </dl>
    </div>
</div>
