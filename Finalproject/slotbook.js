const buttons = document.querySelectorAll('.slot-btn');
const hiddenInput = document.getElementById('selected_slot');
const form = document.getElementById('slotForm');

let selectedButton = null;
let timer = null;

/* STEP 1: Slot selection (NO TIMER HERE) */
buttons.forEach(btn => {
    btn.addEventListener('click', () => {

        buttons.forEach(b => b.classList.remove('selected'));
        
        btn.classList.add('selected');
        hiddenInput.value = btn.dataset.spot;
        selectedButton = btn;
    });
});

form.addEventListener('submit', (e) => {

    if (!selectedButton) {
        e.preventDefault();
        alert('Please select a slot first.');
        return;
    }

    // Prevent form submission for testing
    e.preventDefault();

    // Disable ONLY unselected buttons
    buttons.forEach(b => {
        if (b !== selectedButton) {
            b.classList.add('disabled');
        }
    });

    // Clear previous timer if exists
    if (timer) clearTimeout(timer);

    // Start timer (3 seconds for testing)
    timer = setTimeout(() => {
        selectedButton.classList.remove('selected');
        hiddenInput.value = '';
        selectedButton = null;

        // Re-enable all buttons
        buttons.forEach(b => b.classList.remove('disabled'));
    }, 60000);
});