// resources/js/Pages/Mc/Show.js

import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";

document.addEventListener("DOMContentLoaded", function () {
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
                    // Hanya izinkan Sabtu (6) dan Minggu (0)
                    info.el.classList.add("fc-day-disabled");
                    info.el.style.backgroundColor = "#f0f0f0"; // Tailwind gray-100
                    info.el.style.cursor = "not-allowed";
                }
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                fetch(`/api/mc/${mcId}/schedules`)
                    .then((response) => response.json())
                    .then((data) => {
                        successCallback(data.events);
                    })
                    .catch((error) => {
                        console.error("Error fetching MC schedules:", error);
                        failureCallback(error);
                    });
            },
            dateClick: function (info) {
                // Pastikan tanggal yang diklik adalah Sabtu/Minggu DAN tidak dinonaktifkan
                if (
                    (info.date.getDay() === 6 || info.date.getDay() === 0) &&
                    !info.dayEl.classList.contains("fc-day-disabled")
                ) {
                    var clickedDate = info.dateStr;
                    document.getElementById("event_date").value = clickedDate;

                    // Hapus highlight dari tanggal yang sebelumnya dipilih
                    document
                        .querySelectorAll(".fc-daygrid-day.fc-day-selected")
                        .forEach(function (el) {
                            el.classList.remove("fc-day-selected");
                            // Opsional: kembalikan background jika sebelumnya ada warna kustom
                            // Misalnya, jika kamu ingin menghapus style langsung yang ditambahkan
                            // el.style.backgroundColor = '';
                        });

                    // Tambahkan kelas untuk highlight ke tanggal yang baru diklik
                    info.dayEl.classList.add("fc-day-selected");
                    // Opsional: Tambahkan style langsung untuk warna biru muda
                    // info.dayEl.style.backgroundColor = '#BFDBFE'; // Tailwind blue-200
                } else {
                    // Beri tahu pengguna jika tanggal tidak bisa dipilih
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
});
