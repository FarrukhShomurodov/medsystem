/*
| The survey page of an examination map (maps/surveys): the questions of the chosen survey, and the
| answers of a survey filled in earlier, which can be copied into the form.
*/
import { Modal } from '@tabler/core';
import $ from 'jquery';
import { applySelectedAnswers, loadSurveyQuestions, loadSurveyResponse } from './patients/surveys';
import { fillUrl } from './patients/urls';

const form = document.getElementById('survey-form');
const questions = document.getElementById('questions-container');
const modal = document.getElementById('responseModal');
const answers = document.getElementById('response-content');
const types = { choice: questions.dataset.choiceType, boolean: questions.dataset.booleanType };

// select2 reports a choice as a jQuery event only.
$('#survey-select').on('change', function () {
    if (this.value) {
        loadSurveyQuestions(questions, fillUrl(this.dataset.questionsUrl, { survey: this.value }), types);
    } else {
        questions.replaceChildren();
    }
});

modal.addEventListener('show.bs.modal', (event) => {
    const url = event.relatedTarget?.dataset.url;

    if (url) {
        loadSurveyResponse(answers, url, { selectable: true });
    }
});

answers.addEventListener('click', (event) => {
    if (event.target.closest('[data-apply-answers]')) {
        applySelectedAnswers(answers, form);
        Modal.getOrCreateInstance(modal).hide();
    }
});
