// AMS shared front-end scripts.
// Each block guards on its own element(s) so this file can be loaded on every page.

(function () {
    // login.php: toggle password visibility
    var btn = document.getElementById('togglePass');
    var pass = document.getElementById('login-pass');
    var icon = document.getElementById('iconEye');
    if (!btn || !pass) return;
    btn.addEventListener('click', function () {
        if (pass.type === 'password') {
            pass.type = 'text';
            if (icon) {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        } else {
            pass.type = 'password';
            if (icon) {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    });
})();

(function () {
    // add.php: client-side validation for the add-student form
    var form = document.getElementById('addForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var errors = [];
        var full = document.getElementById('full_name');
        var gender = document.getElementById('gender');
        var section = document.getElementById('section');
        var allowedGenders = ['Male', 'Female'];
        var allowedSections = ['Gumamela', 'Tulip'];

        if (!full || full.value.trim() === '') {
            errors.push('Full name is required.');
        }
        if (!gender || allowedGenders.indexOf(gender.value) === -1) {
            errors.push('Please select a valid gender.');
        }
        if (!section || allowedSections.indexOf(section.value) === -1) {
            errors.push('Please select a valid section.');
        }

        var container = document.getElementById('clientErrors');
        container.innerHTML = '';
        if (errors.length > 0) {
            e.preventDefault();
            var html = '<div class="alert alert-danger"><ul>';
            errors.forEach(function (msg) {
                html += '<li>' + msg + '</li>';
            });
            html += '</ul></div>';
            container.innerHTML = html;
            if (full && full.value.trim() === '') full.focus();
            return false;
        }
        return true;
    });
})();

(function () {
    // student.php: present/absent checkbox exclusivity, reset button, live clock
    var hasPresentCheckboxes = document.querySelector('input[type="checkbox"][id^="present_"]');
    if (!hasPresentCheckboxes) return;

    document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function (presentCheckbox) {
        presentCheckbox.addEventListener('change', function () {
            var studentId = this.id.replace('present_', '');
            var absentCheckbox = document.getElementById('absent_' + studentId);
            if (absentCheckbox && this.checked) {
                absentCheckbox.checked = false;
            }
        });
    });

    document.querySelectorAll('input[type="checkbox"][id^="absent_"]').forEach(function (absentCheckbox) {
        absentCheckbox.addEventListener('change', function () {
            var studentId = this.id.replace('absent_', '');
            var presentCheckbox = document.getElementById('present_' + studentId);
            if (presentCheckbox && this.checked) {
                presentCheckbox.checked = false;
            }
        });
    });

    var resetBtn = document.getElementById('resetAttendance');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function (checkbox) {
                checkbox.checked = false;
            });
            document.querySelectorAll('input[type="checkbox"][id^="absent_"]').forEach(function (checkbox) {
                checkbox.checked = false;
            });
        });
    }

    function updateClock() {
        var display = document.getElementById('dateTimeDisplay');
        if (!display) return;
        var now = new Date();
        var weekday = now.toLocaleDateString('en-US', { weekday: 'long' });
        var month = now.toLocaleDateString('en-US', { month: 'long' });
        var day = now.getDate();
        var year = now.getFullYear();
        var hours = now.getHours();
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');
        var period = hours >= 12 ? 'PM' : 'AM';
        var displayHours = hours % 12 || 12;
        display.textContent = 'Philippine Standard Time: ' + weekday + ', ' + month + ' ' + day + ', ' + year + ', ' + displayHours + ':' + minutes + ':' + seconds + ' ' + period;
    }
    setInterval(updateClock, 1000);
    updateClock();
})();

(function () {
    // includes/sidebar.php: mobile off-canvas toggle
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('appSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar || !overlay) return;

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
        var isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    overlay.addEventListener('click', closeSidebar);
})();
