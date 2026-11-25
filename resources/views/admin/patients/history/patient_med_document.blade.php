{{-- A filled-in medical document (survey): the patient card history and the services it documents. --}}
<div class="mb-3">
    <h3 class="text-primary mb-3">{{ $data->survey->title }}</h3>

    @forelse ($data->answers as $index => $answer)
        <p class="mb-3">
            <strong>{{ $index + 1 }}. {{ $answer->question->question }}</strong><br>
            Ответ: <span class="text-success">{{ $answer->answer === 'yes' ? 'Да' : ($answer->answer === 'no' ? 'Нет' : $answer->answer) }}</span>
        </p>
    @empty
        <p class="text-secondary">Ответов нет.</p>
    @endforelse
</div>
