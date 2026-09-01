/**
 * EventSphere - Interactive Frontend Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('is-open');
        });
    }

    // 2. Client-side Form Validation on Registration Form
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const eventSelect = document.getElementById('event_id');

        const validateField = (input, validator, errorMsg) => {
            const wrapper = input.closest('.form-group');
            let errorEl = wrapper.querySelector('.form-error');
            const isValid = validator(input.value.trim());

            if (!isValid) {
                input.classList.add('is-invalid');
                if (!errorEl) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'form-error';
                    wrapper.appendChild(errorEl);
                }
                errorEl.innerHTML = `⚠️ ${errorMsg}`;
            } else {
                input.classList.remove('is-invalid');
                if (errorEl) {
                    errorEl.remove();
                }
            }
            return isValid;
        };

        const isValidEmail = (email) => {
            const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return re.test(String(email).toLowerCase());
        };

        if (nameInput) {
            nameInput.addEventListener('input', () => {
                validateField(nameInput, val => val.length >= 2, 'Name must be at least 2 characters.');
            });
        }

        if (emailInput) {
            emailInput.addEventListener('input', () => {
                validateField(emailInput, isValidEmail, 'Please enter a valid email address.');
            });
        }

        if (eventSelect) {
            eventSelect.addEventListener('change', () => {
                validateField(eventSelect, val => val !== '' && val !== '0', 'Please select an event.');
            });
        }

        registrationForm.addEventListener('submit', (e) => {
            let formValid = true;

            if (nameInput && !validateField(nameInput, val => val.length >= 2, 'Name must be at least 2 characters.')) {
                formValid = false;
            }

            if (emailInput && !validateField(emailInput, isValidEmail, 'Please enter a valid email address.')) {
                formValid = false;
            }

            if (eventSelect && !validateField(eventSelect, val => val !== '' && val !== '0', 'Please select an event.')) {
                formValid = false;
            }

            if (!formValid) {
                e.preventDefault();
            }
        });
    }

    // 3. Homepage Event Filtering (Search & Category)
    const eventSearchInput = document.getElementById('eventSearchInput');
    const eventCategoryFilter = document.getElementById('eventCategoryFilter');
    const eventCards = document.querySelectorAll('.event-card');
    const emptyFilterState = document.getElementById('emptyFilterState');

    function filterEvents() {
        if (!eventCards.length) return;

        const searchTerm = (eventSearchInput ? eventSearchInput.value : '').toLowerCase().trim();
        const selectedCategory = (eventCategoryFilter ? eventCategoryFilter.value : 'all').toLowerCase();
        let visibleCount = 0;

        eventCards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const description = card.getAttribute('data-desc') || '';
            const category = card.getAttribute('data-category') || '';

            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = (selectedCategory === 'all' || category === selectedCategory);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (emptyFilterState) {
            emptyFilterState.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    if (eventSearchInput) {
        eventSearchInput.addEventListener('input', filterEvents);
    }
    if (eventCategoryFilter) {
        eventCategoryFilter.addEventListener('change', filterEvents);
    }

    // 4. Modal Helpers for Admin Portal
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    };

    // Close modal on background click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is-active');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.is-active').forEach(modal => {
                modal.classList.remove('is-active');
                document.body.style.overflow = '';
            });
        }
    });

    // 5. Admin Registration View Details Modal Handler
    window.viewRegistrationDetails = function(data) {
        const modal = document.getElementById('viewDetailsModal');
        if (!modal) return;

        document.getElementById('modalRegId').textContent = data.id;
        document.getElementById('modalRegCode').textContent = data.registration_code;
        document.getElementById('modalRegName').textContent = data.name;
        document.getElementById('modalRegEmail').textContent = data.email;
        document.getElementById('modalRegEvent').textContent = data.event_title;
        document.getElementById('modalRegDate').textContent = data.date_registered;
        document.getElementById('modalEventDate').textContent = data.event_date || 'N/A';
        document.getElementById('modalEventLocation').textContent = data.event_location || 'N/A';

        openModal('viewDetailsModal');
    };

    // 6. Admin Delete Confirmation Handler
    window.confirmDeleteRegistration = function(id, name, eventTitle) {
        const modal = document.getElementById('deleteConfirmModal');
        if (!modal) return;

        document.getElementById('deleteRegIdInput').value = id;
        document.getElementById('deleteParticipantName').textContent = name;
        document.getElementById('deleteEventName').textContent = eventTitle;

        openModal('deleteConfirmModal');
    };

    // 7. Copy Registration Code Helper (Success Page)
    window.copyRegistrationCode = function(code, buttonEl) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                const originalText = buttonEl.innerHTML;
                buttonEl.innerHTML = '✅ Copied!';
                buttonEl.style.background = '#10b981';
                setTimeout(() => {
                    buttonEl.innerHTML = originalText;
                    buttonEl.style.background = '';
                }, 2000);
            });
        }
    };
});
