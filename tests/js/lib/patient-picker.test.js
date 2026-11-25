import $ from 'jquery';
import { expect, it, vi } from 'vitest';
import { initPatientPicker, patientLabel, patientResults } from '../../../resources/js/lib/patient-picker';

it('labels a patient with the birth date, to tell namesakes apart', () => {
    expect(patientLabel({ full_name: 'Каримова Дильноза', birth_date: '1990-04-12' })).toBe('Каримова Дильноза (12.04.1990)');
    expect(patientLabel({ full_name: 'Каримова Дильноза', birth_date: null })).toBe('Каримова Дильноза');
});

it('turns an answer of api.patients.index into select2 results, each with its own result id', () => {
    // select2 4.1 builds the result id with a detached method that throws in strict module code.
    expect(patientResults([
        { id: 7, full_name: 'Каримова Дильноза', birth_date: '1990-04-12' },
        { id: 9, full_name: 'Каримова Дильноза', birth_date: '1985-01-30' },
    ])).toEqual([
        { id: 7, text: 'Каримова Дильноза (12.04.1990)', _resultId: 'patient-result-7' },
        { id: 9, text: 'Каримова Дильноза (30.01.1985)', _resultId: 'patient-result-9' },
    ]);
});

it('sets the select up to search the endpoint as the user types, never to list the register', () => {
    document.body.innerHTML = '<select id="patient_id" data-placeholder="Начните вводить ФИО пациента"></select>';
    const select2 = vi.fn();
    $.fn.select2 = select2;

    initPatientPicker(document.getElementById('patient_id'), { url: '/api/patients' });

    const [options] = select2.mock.calls[0];

    expect(options.placeholder).toBe('Начните вводить ФИО пациента');
    expect(options.ajax).toMatchObject({ url: '/api/patients', dataType: 'json', delay: 250 });
    expect(options.ajax.processResults([{ id: 3, full_name: 'Иванов Иван', birth_date: null }])).toEqual({
        results: [{ id: 3, text: 'Иванов Иван', _resultId: 'patient-result-3' }],
    });
});
