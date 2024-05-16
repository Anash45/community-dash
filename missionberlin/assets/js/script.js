(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()

$(document).ready(function () {
    // Toggle sorting order
    var ascending = true;

    // Sort tasks based on data-priority attribute
    function sortTasks(elm) {
        var $tasksContainer = elm.closest('.task-info').siblings('.tasks');
        var $tasks = $tasksContainer.children('.task');
        $tasks.sort(function (a, b) {
            var priorityA = parseInt($(a).attr('data-priority'));
            var priorityB = parseInt($(b).attr('data-priority'));
            return ascending ? priorityA - priorityB : priorityB - priorityA;
        });
        $tasks.detach().appendTo($tasksContainer);
    }

    // Handle sort button click
    $('.sortButton').click(function () {
        let elm = $(this);
        ascending = !ascending; // Toggle sorting order
        sortTasks(elm);
    });
});
// Function to scroll #messages div to the bottom
function scrollMessagesToBottom() {
    var messages = $('#messages');
    if(messages.length > 0) {
        messages.scrollTop(messages[0].scrollHeight);
    }
}

$(document).ready(function () {
    scrollMessagesToBottom();
})
function changeLanguage(languageCode) {
    // Update or create 'lang' key in localStorage with the provided language code
    localStorage.setItem('lang', languageCode);
    console.log('Language updated:', languageCode);
    location.reload();
    // You can perform any additional actions here, such as reloading the page or updating UI elements based on the selected language
}
function applyLangauge() {
    // Check if 'lang' key exists in localStorage
    if (localStorage.getItem('lang')) {
        // Get the language code from localStorage
        var languageCode = localStorage.getItem('lang');

        // Apply language code as class to the body element
        document.body.classList.add(languageCode);
    } else {
        changeLanguage('en');
    }
}
window.onload = function () {
    applyLangauge();
};
