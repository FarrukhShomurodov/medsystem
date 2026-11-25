<?php

namespace App\Http\Requests\Clinical;

use App\Models\DocumentResponse;
use App\Models\DocumentScore;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * A recommended action marked as done for a filled-in document the nurse window shows the account
 * (DocumentResponsePolicy). The range must be the one the window shows for the response (the
 * range of its stored total), and the action one of that range.
 */
class StorePatientDocumentScoreRequest extends FormRequest
{
    private ?DocumentResponse $documentResponse = null;

    public function authorize(): Response
    {
        $response = $this->documentResponse();

        // An id that names no response is left to the rules, which refuse it.
        return $response === null ? Response::allow() : Gate::inspect('trackActions', $response);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $response = $this->documentResponse();
        $rangeId = $response === null ? null : DocumentScore::query()
            ->where('document_id', $response->document_id)
            ->containing($response->total_score)
            ->value('id');

        return [
            'document_response_id' => ['required', 'integer', 'exists:document_responses,id'],
            'document_score_id' => ['required', 'integer', Rule::in($rangeId === null ? [] : [$rangeId])],
            'document_action_id' => [
                'required',
                'integer',
                Rule::exists('document_score_actions', 'id')->where('document_score_id', (int) $this->input('document_score_id')),
            ],
        ];
    }

    /**
     * @return array{document_response_id: int, document_score_id: int, document_action_id: int}
     */
    public function scoreAction(): array
    {
        return [
            'document_response_id' => $this->integer('document_response_id'),
            'document_score_id' => $this->integer('document_score_id'),
            'document_action_id' => $this->integer('document_action_id'),
        ];
    }

    /** The response the request names, looked up once for authorize() and rules(). */
    private function documentResponse(): ?DocumentResponse
    {
        $id = filter_var($this->input('document_response_id'), FILTER_VALIDATE_INT);

        return $this->documentResponse ??= ($id === false ? null : DocumentResponse::query()->find($id));
    }
}
