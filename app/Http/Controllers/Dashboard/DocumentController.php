<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentResponseRequest;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Services\DocumentResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected DocumentResponseService $service;

    public function __construct(DocumentResponseService $service)
    {
        $this->service = $service;
    }

    public function show(Document $document, Request $request): View|RedirectResponse
    {
        $patientId = $request->filled('patient_id') ? $request->input('patient_id') : null;

        if (is_null($patientId)) {
            return redirect(back())->withErrors('error', 'Пациент не найден');
        }

        return view('admin.patients.documents.questions', compact('document', 'patientId'));
    }

    public function showAnswer(DocumentResponse $documentResponse): View|RedirectResponse
    {
        return view('admin.patients.documents.answers', compact('documentResponse'));
    }

    public function store(DocumentResponseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store($validated);

//        $previousUrl = url()->previous();
//        $currentRoute = Route::currentRouteName();
//
//        $previousRoute = app('router')->getRoutes()->match(request()->create($previousUrl))->getName();
//
//        if ('document.response.store' === $currentRoute) {
//            return redirect()->route('patient.map.create', $validated['patient_id'])
//                ->with('success', 'Мед. документ назначен!');
//        }

        return redirect($validated['previous_url'])->with('success', 'Мед. документ назначен!');
    }

    public function delete(DocumentResponse $documentResponse): RedirectResponse
    {
        $documentResponse->delete();
        return redirect()->back()->with('success', 'Мед. документ удален!');
    }

}
