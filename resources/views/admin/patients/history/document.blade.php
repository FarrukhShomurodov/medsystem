<div class="container mt-4">
    <h3 class="mb-4">Ответы на документ: {{ $data->document->title }}</h3>

    <div class="card">
        <div class="card-body">
            @foreach($data->document->questions as $question)
                @php
                    $answer = $data->answers->firstWhere('question_id', $question->id);
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
</div>
