/*
| The examination map page (maps/show): the answers of a medical document of the stay, in a modal.
*/
import { loadSurveyResponse } from './patients/surveys';

document.getElementById('responseModal')?.addEventListener('show.bs.modal', (event) => {
    const url = event.relatedTarget?.dataset.url;

    if (url) {
        loadSurveyResponse(document.getElementById('response-content'), url);
    }
});
