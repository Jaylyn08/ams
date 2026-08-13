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
        var grade = document.getElementById('grade');
        var section = document.getElementById('section');
        var allowedGenders = ['Male', 'Female'];

        if (!full || full.value.trim() === '') {
            errors.push('Full name is required.');
        }
        if (!gender || allowedGenders.indexOf(gender.value) === -1) {
            errors.push('Please select a valid gender.');
        }
        // Grade/section options are loaded from the database (grade/section
        // tables), so the client can only check that something was picked;
        // the server validates the ids are real and belong together.
        if (!grade || grade.value === '') {
            errors.push('Please select a valid grade.');
        }
        if (!section || section.value === '') {
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
    // add.php / edit.php: keep the Grade and Section dropdowns in sync.
    // Each <option> in #section carries data-grade="<its grade_id>" (see
    // includes/sections.php / the section table). Picking a grade hides any
    // section option that doesn't belong to it; picking a section snaps the
    // grade dropdown to match. This is purely a UX convenience — the server
    // (functions/add.php / functions/edit.php) always re-validates that the
    // submitted section_id and grade_id actually belong together.
    var gradeSelect = document.getElementById('grade');
    var sectionSelect = document.getElementById('section');
    if (!gradeSelect || !sectionSelect) return;

    function filterSectionsByGrade(keepCurrentSelection) {
        var selectedGrade = gradeSelect.value;
        var currentValue = sectionSelect.value;
        var currentStillMatches = false;

        Array.prototype.forEach.call(sectionSelect.options, function (option) {
            if (option.value === '') return; // always keep the placeholder option
            var matches = !selectedGrade || option.getAttribute('data-grade') === selectedGrade;
            option.hidden = !matches;
            option.disabled = !matches;
            if (matches && option.value === currentValue) {
                currentStillMatches = true;
            }
        });

        if (!keepCurrentSelection || !currentStillMatches) {
            sectionSelect.value = '';
        }
    }

    gradeSelect.addEventListener('change', function () {
        filterSectionsByGrade(false);
    });

    sectionSelect.addEventListener('change', function () {
        var chosen = sectionSelect.options[sectionSelect.selectedIndex];
        var chosenGrade = chosen ? chosen.getAttribute('data-grade') : '';
        if (chosenGrade && gradeSelect.value !== chosenGrade) {
            gradeSelect.value = chosenGrade;
            filterSectionsByGrade(true);
        }
    });

    // Run once on load: on edit.php this narrows the list to the student's
    // existing grade while keeping their current section selected.
    filterSectionsByGrade(true);
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

    // Attendance selection persistence across paginated pages.
    (function() {
        var getKey = function() {
            var sectionEl = document.querySelector('input[name="section"]') || document.getElementById('section');
            var section = sectionEl ? (sectionEl.value || '') : '';
            var dateEl = document.querySelector('input[name="attendance_date"]');
            var date = dateEl ? (dateEl.value || '') : '';
            return 'attendance:' + section + ':' + date;
        };

        var loadData = function() {
            var key = getKey();
            try {
                var raw = localStorage.getItem(key);
                if (!raw) return { seen: {}, present: {} };
                return JSON.parse(raw) || { seen: {}, present: {} };
            } catch (e) {
                return { seen: {}, present: {} };
            }
        };

        var saveData = function(data) {
            var key = getKey();
            try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
        };

        var markSeenFromPage = function() {
            var data = loadData();
            document.querySelectorAll('input[type="hidden"][name="student_ids[]"]').forEach(function(h){
                var id = h.value && String(h.value);
                if (id) data.seen[id] = true;
            });
            saveData(data);
        };

        var syncVisibleToStorage = function() {
            var data = loadData();
            document.querySelectorAll('input[type="hidden"][name="student_ids[]"]').forEach(function(h){
                var id = h.value && String(h.value);
                if (id) data.seen[id] = true;
            });
            document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(cb){
                var id = cb.id.replace('present_', '');
                if (!id) return;
                if (cb.checked) {
                    data.present[id] = true;
                } else {
                    delete data.present[id];
                }
                data.seen[id] = true;
            });
            saveData(data);
        };

        var restoreFromStorage = function() {
            var data = loadData();
            document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(cb){
                var id = cb.id.replace('present_', '');
                if (data.present && data.present[id]) {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
            });
            // ensure seen includes all currently-visible ids
            markSeenFromPage();
        };

        // Attach change listeners to update storage immediately
        var attachCheckboxListeners = function() {
            document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(cb){
                cb.addEventListener('change', function() {
                    syncVisibleToStorage();
                });
            });
        };

        // Before leaving page or navigating, persist current visible selections
        window.addEventListener('beforeunload', function() {
            syncVisibleToStorage();
        });

        var getForm = function() { return document.querySelector('form[method="get"]'); };
        var getAttendanceForm = function() { return document.getElementById('attendanceForm'); };

        var form = getForm();
        if (form) {
            form.addEventListener('submit', function() {
                syncVisibleToStorage();
            });
        }

        // Intercept attendance form submit to include all stored (seen) student ids and statuses
        var attendanceForm = getAttendanceForm();
        if (attendanceForm) {
            attendanceForm.addEventListener('submit', function(e) {
                // persist visible first
                syncVisibleToStorage();
                var data = loadData();
                // disable visible checkboxes so they don't double-submit
                document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(cb){ cb.disabled = true; });
                // remove existing hidden student_ids[] inputs (they reflect only current page)
                attendanceForm.querySelectorAll('input[name="student_ids[]"]').forEach(function(h){ h.remove(); });
                // also remove any present[]/absent[] hidden inputs to avoid duplicates
                attendanceForm.querySelectorAll('input').forEach(function(h){
                    var n = h.name || '';
                    if (n.indexOf('present[') === 0 || n.indexOf('absent[') === 0) {
                        h.remove();
                    }
                });

                // Add inputs for each seen id
                Object.keys(data.seen || {}).forEach(function(id){
                    // create student_ids[]
                    var inEl = document.createElement('input');
                    inEl.type = 'hidden';
                    inEl.name = 'student_ids[]';
                    inEl.value = id;
                    attendanceForm.appendChild(inEl);

                    if (data.present && data.present[id]) {
                        var p = document.createElement('input'); p.type = 'hidden'; p.name = 'present[' + id + ']'; p.value = '1'; attendanceForm.appendChild(p);
                    } else {
                        var a = document.createElement('input'); a.type = 'hidden'; a.name = 'absent[' + id + ']'; a.value = '1'; attendanceForm.appendChild(a);
                    }
                });
                // allow form to submit normally after modifications
            });
        }

        // Initialize
        restoreFromStorage();
        attachCheckboxListeners();

        // existing auto-submit handlers for page and page_size
        (function() {
            var pageSelect = document.getElementById('page');
            var pageSize = document.getElementById('page_size');
            var getFormEl = getForm();
            if (pageSelect) {
                pageSelect.addEventListener('change', function() {
                    // save current visible selections before navigating
                    syncVisibleToStorage();
                    if (getFormEl) getFormEl.submit();
                });
            }
            if (pageSize) {
                pageSize.addEventListener('change', function() {
                    // reset to first page when page size changes
                    var pageInput = document.querySelector('select[name="page"]');
                    if (pageInput) {
                        pageInput.value = 1;
                    }
                    syncVisibleToStorage();
                    if (getFormEl) getFormEl.submit();
                });
            }
        })();

    })();

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
