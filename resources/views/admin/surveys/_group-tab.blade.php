{{-- The tab of a question group in the survey editor. $index: the group's index in the form ("__GROUP__" in the template). --}}
<li class="nav-item" role="presentation">
    <button type="button" class="nav-link" id="group-tab-{{ $index }}" data-bs-toggle="tab" data-bs-target="#group-pane-{{ $index }}"
            role="tab" aria-controls="group-pane-{{ $index }}" aria-selected="false" data-group-label>{{ $title }}</button>
</li>
