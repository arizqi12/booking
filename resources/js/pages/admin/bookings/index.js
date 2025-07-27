// resources/js/Pages/Admin/Bookings/Index.js

document.addEventListener("DOMContentLoaded", function () {
    // Logika untuk modal tolak pemesanan
    window.openRejectModal = function (bookingId) {
        const form = document.getElementById("rejectForm");
        if (form) {
            form.action = `/admin/bookings/${bookingId}/reject`;
            document.getElementById("rejectModal").classList.remove("hidden");
            document.getElementById("rejectModal").classList.add("flex");
        }
    };

    window.closeRejectModal = function () {
        const rejectModal = document.getElementById("rejectModal");
        const reasonInput = document.getElementById("reason");
        if (rejectModal) {
            rejectModal.classList.add("hidden");
            rejectModal.classList.remove("flex");
            if (reasonInput) {
                reasonInput.value = "";
            }
        }
    };
});
