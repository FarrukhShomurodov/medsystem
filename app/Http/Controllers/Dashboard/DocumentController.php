<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentResponseRequest;
use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Services\Clinical\DocumentResponseService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentResponseService $service) {}

    public function show(Document $document, Request $request, #[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        $patientId = (int) $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
        ])['patient_id'];

        $document->load('questions');

        return view('admin.patients.documents.questions', [
            'document' => $document,
            'patientId' => $patientId,
            'back' => $this->returnUrl($request, $viewer, $patientId),
        ]);
    }

    /**
     * Only an initial nursing assessment, to the permission of the stay form that links it
     * (DocumentResponsePolicy): one no stay holds yet (new-stay form), or one of an open stay
     * (edit form).
     */
    public function showAnswer(DocumentResponse $documentResponse): View
    {
        Gate::authorize('view', $documentResponse);

        $documentResponse->load(['document.questions', 'answers']);

        return view('admin.patients.documents.answers', compact('documentResponse'));
    }

    public function store(DocumentResponseRequest $request, #[CurrentUser(Admin::GUARD)] Admin $viewer): RedirectResponse
    {
        $submission = $request->documentResponse();
        $this->service->store($submission);

        // The form sends the page it was opened from; anything that is not a page of this
        // application sends the user to the patient's page instead.
        $previousUrl = $request->string('previous_url')->value();
        $back = $this->isPage($previousUrl) ? $previousUrl : $this->patientPage($viewer, $submission['patient_id']);

        return redirect()->to($back)->with('success', 'Мед. документ сохранён!');
    }

    /**
     * Only the initial nursing assessment no stay holds yet (DocumentResponsePolicy): the new-stay
     * form's "Удалить осмотр".
     */
    public function delete(DocumentResponse $documentResponse): RedirectResponse
    {
        Gate::authorize('delete', $documentResponse);

        if (! $this->service->deleteUnattachedAssessment($documentResponse)) {
            return back()->with('error', 'Осмотр уже прикреплён к госпитализации и не может быть удалён.');
        }

        return back()->with('success', 'Мед. документ удалён!');
    }

    /**
     * Where the form returns: the page it was opened from, kept through a refused submission (the
     * flashed previous_url), else the patient's page of the account. Never the form itself: after a
     * refused submission the browser's referer is the form, and a document saved from there would
     * land on a blank copy of the form.
     */
    private function returnUrl(Request $request, Admin $viewer, int $patientId): string
    {
        $kept = $request->old('previous_url');
        $from = is_string($kept) ? $kept : url()->previous();
        $isForm = strtok($from, '?') === $request->url();

        return $this->isPage($from) && ! $isForm ? $from : $this->patientPage($viewer, $patientId);
    }

    /** A page of this application, not another site. */
    private function isPage(string $url): bool
    {
        return Str::startsWith($url, url('/').'/');
    }

    /**
     * The patient's page the account may open: the card, else the nurse window (the nurses fill in
     * documents there and may not open the card), else the dashboard.
     */
    private function patientPage(Admin $viewer, int $patientId): string
    {
        return match (true) {
            $viewer->can('view_patients') => route('patients.show', $patientId),
            $viewer->can('sister_window_show') => route('sister.window.show', $patientId),
            default => route('dashboard'),
        };
    }
}
