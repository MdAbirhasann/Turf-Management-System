document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) navToggle.addEventListener('click', () => navLinks.classList.toggle('open'));

    const bookingForm = document.getElementById('bookingForm');
    const serviceSelect = document.getElementById('service_id');
    const durationSelect = document.getElementById('duration_minutes');
    const peopleInput = document.getElementById('people_count');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('start_time');
    const totalText = document.getElementById('totalAmount');
    const durationField = document.getElementById('durationField');
    const peopleField = document.getElementById('peopleField');
    const liveSlots = document.getElementById('liveSlots');

    function selectedServiceSlug(){
        if (!serviceSelect) return '';
        return serviceSelect.options[serviceSelect.selectedIndex]?.dataset.slug || '';
    }
    function priceFromSelection(){
        const slug = selectedServiceSlug();
        if (slug === 'swimming') {
            const unit = Number(serviceSelect.options[serviceSelect.selectedIndex]?.dataset.personPrice || 0);
            const people = Math.max(1, Number(peopleInput?.value || 1));
            return unit * people;
        }
        const opt = durationSelect?.options[durationSelect.selectedIndex];
        return Number(opt?.dataset.price || 0);
    }
    function updateBookingFields(){
        if (!serviceSelect) return;
        const slug = selectedServiceSlug();
        if (durationField) durationField.style.display = slug === 'swimming' ? 'none' : 'flex';
        if (peopleField) peopleField.style.display = slug === 'swimming' ? 'flex' : 'none';
        if (totalText) totalText.textContent = priceFromSelection().toLocaleString() + ' BDT';
        refreshSlots();
    }
    async function refreshSlots(){
        if (!liveSlots || !serviceSelect || !dateInput) return;
        const service = serviceSelect.value;
        const date = dateInput.value;
        if (!service || !date) return;
        liveSlots.innerHTML = '<div class="alert">Loading live slots...</div>';
        const duration = selectedServiceSlug() === 'swimming' ? 60 : (durationSelect?.value || 60);
        const res = await fetch(`availability.php?service_id=${service}&date=${date}&duration=${duration}`);
        liveSlots.innerHTML = await res.text();
    }
    [serviceSelect,durationSelect,peopleInput,dateInput].forEach(el => el && el.addEventListener('change', updateBookingFields));
    if (peopleInput) peopleInput.addEventListener('input', updateBookingFields);
    if (timeSelect) timeSelect.addEventListener('change', updateBookingFields);
    updateBookingFields();

    document.querySelectorAll('[data-print]').forEach(btn => btn.addEventListener('click', () => window.print()));
});
