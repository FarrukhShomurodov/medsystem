<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DocumentResponse;
use App\Queries\Patients\NursePatientsQuery;
use Illuminate\Auth\Access\Response;
use LogicException;

/**
 * Which filled-in medical documents may be opened on their own page, followed up in the nurse
 * window or deleted, on top of the permissions of those routes. A filled-in sheet is part of the
 * patient's medical record: its answers are read on the patient card (view_patients), and the
 * nurse window shows the total score of each sheet of the open stay with the actions recommended
 * for it.
 *
 * - view: the answers page is linked only by the "Смотреть осмотр" button of the stay forms, so it
 *   opens the record that button links, to the permission of the form: an initial nursing
 *   assessment no stay holds yet on the new-stay form (create_patient_map), an assessment of an
 *   open stay on its edit form (edit_maps). An earlier assessment than the linked latest one is
 *   the same record of the same patient and opens too. A closed stay's assessment is refused: its
 *   edit form no longer opens, and the closed record is read on the patient card, not through the
 *   permission to edit stays.
 * - trackActions: the recommended actions of a sheet are read and marked done from the nurse
 *   window, so only for a sheet of an open stay that window shows the account: the stays of the
 *   nurse's own department (NursePatientsQuery).
 * - delete: a closed stay is kept as it was (ADR 0009), so the only sheet that may go is the
 *   initial nursing assessment filled in before the admission, while no stay holds it yet. That
 *   is the sheet the new-stay form offers to delete.
 */
final class DocumentResponsePolicy
{
    public function view(Admin $actor, DocumentResponse $response): Response
    {
        if (! $response->isInitialAssessment()) {
            return Response::deny('Открыть можно только первичный сестринский осмотр из формы госпитализации.');
        }

        // A refused assessment names the form it belongs to, or the card for a closed stay.
        [$formPermission, $refusal] = match (true) {
            $response->isUnattachedInitialAssessment() => ['create_patient_map', 'Осмотр ещё не прикреплён к госпитализации и открывается из формы новой госпитализации или в карте пациента.'],
            $response->map?->is_active === true => ['edit_maps', 'Осмотр прикреплён к открытой госпитализации и открывается из формы её редактирования или в карте пациента.'],
            default => [null, 'Госпитализация закрыта: осмотр можно посмотреть в карте пациента.'],
        };

        return $formPermission !== null && $actor->can($formPermission)
            ? Response::allow()
            : Response::deny($refusal);
    }

    public function trackActions(Admin $actor, DocumentResponse $response): Response
    {
        $patient = $response->patient ?? throw new LogicException("Response {$response->id} has no patient.");
        $stay = NursePatientsQuery::for($actor)->activeMapOf($patient);

        return $stay !== null && $stay->id === $response->examination_map_id
            ? Response::allow()
            : Response::deny('Рекомендации открываются только по документам открытой госпитализации вашего отделения.');
    }

    public function delete(Admin $actor, DocumentResponse $response): Response
    {
        return $response->isUnattachedInitialAssessment()
            ? Response::allow()
            : Response::deny('Удалить можно только первичный сестринский осмотр, ещё не прикреплённый к госпитализации.');
    }
}
