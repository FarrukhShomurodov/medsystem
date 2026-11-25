/*
| Patient service form (patient_services/create|edit): the patient field searches the register
| (lib/patient-picker.js); the examination map picker lists the open maps of the patient picked in
| the form, reloaded from api.examination-maps.by-patient whenever the patient changes. The markup
| is in patient_services/_form.blade.php.
*/
import $ from 'jquery';
import { fetchJson } from '../lib/http';
import { initPatientPicker } from '../lib/patient-picker';

const patientSelect = document.getElementById('patient_id');
const mapSelect = document.getElementById('examination_map_id');

let pending = null;

/** Replace the options of the map picker: "Без карты" first, then `maps`, or one status line. */
function showOptions({ maps = [], status = null, selected = '' }) {
    const options = [new Option(status ?? 'Без карты', '')];

    for (const map of maps) {
        options.push(new Option(`${map.patient_name} — ${map.admission_date}`, String(map.id)));
    }

    mapSelect.replaceChildren(...options);
    mapSelect.disabled = status !== null;
    $(mapSelect).val(maps.some((map) => String(map.id) === String(selected)) ? String(selected) : '').trigger('change');
}

async function loadMaps(patientId, selected = '') {
    pending?.abort();

    if (!patientId) {
        showOptions({ status: 'Сначала выберите пациента' });

        return;
    }

    const request = new AbortController();
    pending = request;
    showOptions({ status: 'Загрузка…' });

    try {
        const maps = await fetchJson(mapSelect.dataset.mapsUrl.replace('__PATIENT__', encodeURIComponent(patientId)), {
            signal: request.signal,
        });

        showOptions({ maps: Array.isArray(maps) ? maps : [], selected });
    } catch (error) {
        if (error.name !== 'AbortError') {
            showOptions({ status: 'Не удалось загрузить карты' });
        }
    }
}

if (patientSelect) {
    initPatientPicker(patientSelect, { url: patientSelect.dataset.patientsUrl });
}

if (patientSelect && mapSelect) {
    // select2 reports a pick through jQuery's change event only.
    $(patientSelect).on('change', () => loadMaps(patientSelect.value));

    // The server listed the maps of the order's patient; after a failed submission with another
    // patient picked, load that patient's maps and keep the map that was chosen.
    if (patientSelect.value && patientSelect.value !== mapSelect.dataset.mapsPatient) {
        loadMaps(patientSelect.value, mapSelect.dataset.selected);
    }
}
