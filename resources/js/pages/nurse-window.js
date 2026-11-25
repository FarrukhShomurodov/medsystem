/*
| The nurse window of a patient (sister_window/show): the vital-sign charts of the open stay, one
| per tab and drawn when its tab is first shown, and the recommended actions of a scored document.
|
| The measurements come from <script type="application/json" id="vital-signs-data"> (one row per
| measurement time: t = epoch milliseconds, then the measured values by key); a row carries only
| the values measured at that time.
*/
import { Modal } from '@tabler/core';
// The chart class with the line type and the features the charts use only (the default bundle has
// every chart type): legend, selection zoom, keyboard navigation of the points.
import ApexCharts from 'apexcharts/core';
import 'apexcharts/line';
import 'apexcharts/features/legend';
import 'apexcharts/features/toolbar';
import 'apexcharts/features/keyboard';
import ru from 'apexcharts/dist/locales/ru.json';
import { pageData } from '../lib/data';
import { escapeHtml } from '../lib/escape';
import { showFlash } from '../lib/flash';
import { fetchJson } from '../lib/http';
import { fillUrl } from './patients/urls';

const records = pageData('vital-signs-data') ?? [];
const page = document.getElementById('nurse-window');

/** The chart of each tab of the vital signs panel (NurseWindowController::MENU). */
const charts = {
    general: {
        title: 'Общие жизненные показатели',
        series: [
            ['systolic', 'Систолическое АД', '#ff7043'],
            ['diastolic', 'Диастолическое АД', '#29b6f6'],
            ['pulse', 'Пульс', '#66bb6a'],
            ['temperature', 'Температура', '#ab47bc'],
            ['saturation', 'Сатурация', '#ffa726'],
        ],
        // Each measure has its own scale (mmHg, beats per minute, °C, %): one y axis per series.
        ownAxes: true,
    },
    breathing_rate: { title: 'Частота дыхания (ЧДД)', series: [['breathing_rate', 'ЧДД', '#42a5f5']] },
    consciousness: { title: 'Уровень сознания', series: [['consciousness', 'Уровень сознания', '#ab47bc']] },
    oxygen_type: { title: 'Кислород/воздух', series: [['oxygen_type', 'Тип подачи кислорода', '#ff7043']] },
    total_score: { title: 'Общий балл', series: [['total_score', 'Общий балл', '#66bb6a']] },
};

/** The points of one measure: only the rows where it was measured. */
function points(key) {
    return records.filter((row) => Number.isFinite(row[key])).map((row) => [row.t, row[key]]);
}

function chartOptions({ title, series, ownAxes = false }) {
    const colors = series.map(([, , color]) => color);

    return {
        chart: {
            type: 'line',
            height: 350,
            locales: [ru],
            defaultLocale: 'ru',
            fontFamily: 'inherit',
            toolbar: { show: false },
            zoom: { enabled: true, type: 'x', autoScaleYaxis: true },
        },
        title: { text: title, align: 'center', style: { fontSize: '16px', fontWeight: 600 } },
        colors,
        series: series.map(([key, name]) => ({ name, data: points(key) })),
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 4, hover: { sizeOffset: 3 } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontSize: '14px' },
        grid: { strokeDashArray: 4 },
        tooltip: { x: { format: 'dd.MM.yyyy HH:mm' } },
        xaxis: { type: 'datetime', title: { text: 'Дата и время' } },
        yaxis: ownAxes
            ? colors.map((color, index) => ({
                  opposite: index % 2 === 1,
                  labels: { style: { colors: color } },
                  axisTicks: { show: true },
                  axisBorder: { show: true, color },
              }))
            : {},
    };
}

function emptyMessage(text) {
    const message = document.createElement('div');
    message.className = 'empty py-5';
    message.innerHTML = '<div class="empty-icon" aria-hidden="true"><i class="bx bx-line-chart"></i></div><p class="empty-title"></p>';
    message.querySelector('.empty-title').textContent = text;

    return message;
}

/** Draw the chart of a tab once. */
function draw(container) {
    if (!container || container.dataset.drawn) {
        return;
    }

    container.dataset.drawn = 'true';
    const chart = charts[container.dataset.chart];

    if (!chart || !chart.series.some(([key]) => points(key).length > 0)) {
        container.replaceChildren(emptyMessage(`Нет данных: ${chart?.title ?? 'показатель'}`));

        return;
    }

    try {
        new ApexCharts(container, chartOptions(chart)).render();
    } catch (error) {
        console.error(error);
        container.replaceChildren(emptyMessage('Не удалось построить график.'));
    }
}

draw(document.querySelector('.tab-pane.active .vital-chart'));

document.querySelectorAll('[data-bs-toggle="pill"]').forEach((tab) => {
    tab.addEventListener('shown.bs.tab', () => draw(document.querySelector(`${tab.dataset.bsTarget} .vital-chart`)));
});

// Recommended actions of a scored document ----------------------------------------------------

const actionsModal = document.getElementById('recommendActionModal');
const actionsBody = document.getElementById('recommendation-content');

function actionsHtml(range) {
    const done = new Set((range.finished ?? []).map((finished) => finished.document_action_id));
    const open = range.actions.filter((action) => !done.has(action.id));
    const finished = range.actions.filter((action) => done.has(action.id));

    const openItems = open
        .map(
            (action) => `
                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <span>${escapeHtml(action.action)}</span>
                    <button type="button" class="btn btn-sm btn-success mark-done-btn"
                            data-action-id="${escapeHtml(action.id)}" data-score-id="${escapeHtml(range.id)}">
                        <i class="bx bx-check" aria-hidden="true"></i>Выполнено
                    </button>
                </li>`,
        )
        .join('');

    const finishedItems = finished
        .map((action) => `<li class="list-group-item text-secondary fst-italic">${escapeHtml(action.action)}</li>`)
        .join('');

    return `
        <h3 class="mb-2">Рекомендованные действия</h3>
        <ul class="list-group mb-4" id="recommendation-list">
            ${openItems || '<li class="list-group-item text-secondary">Все действия выполнены.</li>'}
        </ul>
        <h3 class="mb-2">Выполненные действия</h3>
        <ul class="list-group" id="done-list">
            ${finishedItems || '<li class="list-group-item text-secondary" data-empty>Нет выполненных действий.</li>'}
        </ul>`;
}

actionsModal?.addEventListener('show.bs.modal', async (event) => {
    const { documentId, responseId } = event.relatedTarget?.dataset ?? {};

    if (!documentId) {
        return;
    }

    actionsModal.dataset.responseId = responseId;
    actionsBody.innerHTML = '<p class="text-secondary mb-0">Загрузка…</p>';

    try {
        const range = await fetchJson(fillUrl(page.dataset.actionsUrl, { document: documentId, response: responseId }));

        actionsBody.innerHTML = range?.actions?.length > 0 ? actionsHtml(range) : '<p class="text-secondary mb-0">Рекомендации не найдены.</p>';
    } catch {
        actionsBody.innerHTML = '<p class="text-danger mb-0">Не удалось загрузить рекомендации.</p>';
    }
});

actionsBody?.addEventListener('click', async (event) => {
    const button = event.target.closest('.mark-done-btn');

    if (!button) {
        return;
    }

    const item = button.closest('li');
    button.disabled = true;

    try {
        await fetchJson(page.dataset.finishUrl, {
            method: 'POST',
            body: {
                document_response_id: actionsModal.dataset.responseId,
                document_score_id: button.dataset.scoreId,
                document_action_id: button.dataset.actionId,
            },
        });

        const doneList = document.getElementById('done-list');
        const openList = document.getElementById('recommendation-list');
        doneList.querySelector('[data-empty]')?.remove();
        button.remove();
        item.classList.add('text-secondary', 'fst-italic');
        doneList.append(item);

        if (!openList.querySelector('.mark-done-btn')) {
            openList.innerHTML = '<li class="list-group-item text-secondary">Все действия выполнены.</li>';
        }
    } catch (error) {
        button.disabled = false;
        showFlash(error?.data?.message ?? 'Не удалось сохранить. Повторите попытку.', 'danger');
    }
});

// Open the modal of a measurement form that came back with errors.
const parameterModal = document.getElementById('addParameterModal');

if (parameterModal?.querySelector('.is-invalid')) {
    Modal.getOrCreateInstance(parameterModal).show();
}
