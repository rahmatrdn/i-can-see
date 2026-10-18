import './bootstrap';
import 'preline';
import Toastify from 'toastify-js';
import "toastify-js/src/toastify.css";

window.Toastify = Toastify;

import flatpickr from "flatpickr";
import { Indonesian } from "flatpickr/dist/l10n/id.js";
import "flatpickr/dist/flatpickr.min.css";

window.flatpickr = flatpickr;
flatpickr.localize(Indonesian);

document.addEventListener('DOMContentLoaded', function () {
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j F Y",
        allowInput: true
    });
});