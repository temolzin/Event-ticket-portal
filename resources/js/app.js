import { createPopper } from '@popperjs/core';
import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from 'fullcalendar';



window.Alpine = Alpine;
window.createPopper = createPopper;
window.ApexCharts = ApexCharts;
window.Swal = Swal;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;
window.confirmAction = async (message, options = {}) => {
    const result = await Swal.fire({
        title: options.title ?? window.sweetAlertText?.confirmationTitle ?? 'Are you sure?',
        text: message,
        icon: options.icon ?? 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmButtonText ?? window.sweetAlertText?.confirm ?? 'Yes, continue',
        cancelButtonText: options.cancelButtonText ?? window.sweetAlertText?.cancel ?? 'Cancel',
        reverseButtons: true,
        focusCancel: true,
        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
    });

    return result.isConfirmed;
};

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-swal-flash]').forEach((element) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: element.dataset.icon,
            title: element.dataset.title,
            text: element.dataset.message,
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
        });
    });

    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }

    // Check-in scanner init
    if (document.querySelector('[data-check-in]')) {
        import('./components/check-in-scanner').then(module => module.initCheckInScanner());
    }
});
