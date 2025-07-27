// resources/js/Pages/Mc/Show.js - Simplified version

import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";

document.addEventListener("DOMContentLoaded", function () {
    // --- Inisialisasi FullCalendar ---
    var calendarEl = document.getElementById("calendar");
    if (calendarEl) {
        var mcId = calendarEl.dataset.mcId;

        var calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: "dayGridMonth",
            validRange: {
                start: new Date(),
            },
            dayCellDidMount: function (info) {
                if (info.date.getDay() !== 6 && info.date.getDay() !== 0) {
                    info.el.classList.add("fc-day-disabled");
                    info.el.style.backgroundColor = "#f0f0f0";
                    info.el.style.cursor = "not-allowed";
                }
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                fetch(`/api/mc/${mcId}/schedules`)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                `HTTP error! status: ${response.status}`
                            );
                        }
                        return response.json();
                    })
                    .then((data) => {
                        successCallback(data.events);
                    })
                    .catch((error) => {
                        console.error("Error fetching MC schedules:", error);
                        failureCallback(error);
                    });
            },
            dateClick: function (info) {
                if (
                    (info.date.getDay() === 6 || info.date.getDay() === 0) &&
                    !info.dayEl.classList.contains("fc-day-disabled")
                ) {
                    var clickedDate = info.dateStr;
                    document.getElementById("event_date").value = clickedDate;

                    document
                        .querySelectorAll(".fc-daygrid-day.fc-day-selected")
                        .forEach(function (el) {
                            el.classList.remove("fc-day-selected");
                        });
                    info.dayEl.classList.add("fc-day-selected");
                } else {
                    alert(
                        "Tanggal ini tidak tersedia atau bukan hari Sabtu/Minggu yang bisa dipesan."
                    );
                }
            },
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,dayGridWeek",
            },
            height: "auto",
            contentHeight: "auto",
        });
        calendar.render();
    }

    // --- Form Submission Handler ---
    const bookingForm = document.getElementById("bookingForm");
    const validationErrorsDiv = document.getElementById("validation-errors");

    if (bookingForm) {
        bookingForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            // Clear previous validation errors
            if (validationErrorsDiv) {
                validationErrorsDiv.classList.add("hidden");
                const errorList = validationErrorsDiv.querySelector("ul");
                if (errorList) {
                    errorList.innerHTML = "";
                }
            }

            // Collect form data (now includes hidden inputs from Alpine.js)
            const formData = new FormData(bookingForm);

            // Validate service selection before sending
            const selectedServices = formData.get("selected_service_types");
            const calculatedPrice = formData.get("calculated_service_price");

            if (!selectedServices || selectedServices.trim() === "") {
                alert(
                    "Silakan pilih minimal satu jenis layanan MC terlebih dahulu."
                );
                return;
            }

            if (!calculatedPrice || parseFloat(calculatedPrice) <= 0) {
                alert(
                    "Terjadi kesalahan dalam perhitungan harga. Silakan pilih layanan kembali."
                );
                return;
            }

            // Debug: Log form data
            console.log("--- FORM DATA BEING SENT ---");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ": " + pair[1]);
            }
            console.log("--------------------------------");

            // Disable submit button
            const submitButton = bookingForm.querySelector(
                'button[type="submit"]'
            );
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML =
                    '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            }

            try {
                const response = await fetch(bookingForm.action, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        // Display validation errors
                        if (validationErrorsDiv) {
                            let errorHtml = "";
                            for (const key in data.errors) {
                                data.errors[key].forEach((error) => {
                                    errorHtml += `<li>${error}</li>`;
                                });
                            }
                            const errorList =
                                validationErrorsDiv.querySelector("ul");
                            if (errorList) {
                                errorList.innerHTML = errorHtml;
                            }
                            validationErrorsDiv.classList.remove("hidden");

                            // Scroll to errors
                            validationErrorsDiv.scrollIntoView({
                                behavior: "smooth",
                            });
                        }
                        console.error("SERVER VALIDATION ERRORS:", data.errors);
                    } else {
                        console.error("SERVER RESPONSE ERROR:", data);
                        alert(
                            `Terjadi kesalahan: ${
                                data.message || "Server error"
                            }`
                        );
                    }
                } else {
                    // Success
                    console.log("BOOKING SUCCESS:", data);
                    alert(data.message || "Pemesanan berhasil dibuat!");

                    // Redirect to success page
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.booking_id) {
                        window.location.href = `/booking/success/${data.booking_id}`;
                    } else {
                        window.location.href = "/dashboard";
                    }
                }
            } catch (error) {
                console.error("NETWORK ERROR:", error);
                alert("Terjadi kesalahan jaringan. Silakan coba lagi.");
            } finally {
                // Re-enable submit button
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML =
                        '<i class="fas fa-calendar-check mr-2"></i> Proses Pemesanan';
                }
            }
        });
    } else {
        console.error("ERROR: Booking form not found");
    }
});
