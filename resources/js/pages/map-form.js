/*
| The examination map wizard (maps/create, maps/edit). The steps, the pickers and the confirmation
| of the delete form come from app.js; this only keeps the wizard buttons in step.
*/
import { setupWizardButtons } from './patients/wizard';

setupWizardButtons(document.querySelector('.bs-stepper'));
