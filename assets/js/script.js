// Delete confirmation
function confirmDelete() {
    return confirm("Are you sure you want to delete this student?");
}


// Auto-hide success/error messages
document.addEventListener("DOMContentLoaded", function () {

    const messages = document.querySelectorAll(".success, .error");

    messages.forEach(function (message) {

        setTimeout(function () {
            message.style.opacity = "0";

            setTimeout(function () {
                message.style.display = "none";
            }, 500);

        }, 3000);

    });

});