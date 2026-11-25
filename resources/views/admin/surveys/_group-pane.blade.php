{{--
    The questions of a group in the survey editor.
    $index: the group's index in the form ("__GROUP__" in the template); $group: the group, or null.
--}}
<div class="tab-pane" id="group-pane-{{ $index }}" role="tabpanel" aria-labelledby="group-tab-{{ $index }}" tabindex="0" data-group-pane>
    @if ($group?->id)
        <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}">
    @endif

    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-8">
            <label class="form-label required" for="group-title-{{ $index }}">Название группы</label>
            <input type="text" id="group-title-{{ $index }}" name="groups[{{ $index }}][title]"
                   class="form-control @error("groups.{$index}.title") is-invalid @enderror"
                   value="{{ old("groups.{$index}.title", $group?->title) }}" data-group-title required>
            @error("groups.{$index}.title")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 text-md-end">
            <button type="button" class="btn btn-outline-danger" data-group-remove><i class="bx bx-trash" aria-hidden="true"></i>Удалить группу</button>
        </div>
    </div>

    <div data-question-list data-prefix="groups[{{ $index }}][questions]">
        @foreach ($group?->questions ?? [] as $questionIndex => $question)
            @include('admin.surveys._question', ['question' => $question, 'prefix' => "groups[{$index}][questions][{$questionIndex}]"])
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary" data-question-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить вопрос</button>
</div>
