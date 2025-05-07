<div class="medical-document">
    <h6 class="text-primary mb-4">{{ $data->survey->title }}</h6>

    @if(count($data->answers) > 0)
        <div class="answers-container">
            @foreach($data->answers as $index => $answer)
                <div class="mb-3 form-check">
                    <label class="form-check-label">
                        <strong>{{ $index + 1 }}. {{ $answer->question->question }}</strong>
                        <br>
                        Ответ: <span class="text-success">
                            {{ $answer->answer === 'yes' ? 'Да' : ($answer->answer === 'no' ? 'Нет' : $answer->answer) }}
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Ответов нет</p>
    @endif
</div>
