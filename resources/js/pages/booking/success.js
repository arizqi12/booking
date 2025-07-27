// resources/js/Pages/Booking/Success.js

document.addEventListener("DOMContentLoaded", function () {
    const payButton = document.getElementById("pay-button");
    // Ambil bookingId dari URL (misalnya dari window.location.pathname)
    const urlParts = window.location.pathname.split("/");
    const bookingId = urlParts[urlParts.length - 1]; // Mengambil ID dari URL /booking/success/{id}

    if (payButton && bookingId) {
        payButton.onclick = function () {
            fetch(`/api/payment/snap-token/${bookingId}?type=initial_payment`) // Panggil API endpoint yang baru
                .then((response) => response.json())
                .then((data) => {
                    if (data.snap_token) {
                        if (window.snap) {
                            snap.pay(data.snap_token, {
                                onSuccess: function (result) {
                                    alert("Pembayaran Berhasil!");
                                    console.log(result);
                                    window.location.href = `/my-bookings/${bookingId}`;
                                },
                                onPending: function (result) {
                                    alert("Pembayaran Menunggu!");
                                    console.log(result);
                                    window.location.href = `/my-bookings/${bookingId}`;
                                },
                                onError: function (result) {
                                    alert("Pembayaran Gagal!");
                                    console.log(result);
                                    window.location.href = `/my-bookings/${bookingId}`;
                                },
                                onClose: function () {
                                    alert(
                                        "Anda menutup popup tanpa menyelesaikan pembayaran."
                                    );
                                },
                            });
                        } else {
                            console.error("Midtrans Snap JS not loaded.");
                            alert(
                                "Sistem pembayaran belum siap. Mohon coba lagi nanti."
                            );
                        }
                    } else {
                        alert(
                            "Gagal mendapatkan token pembayaran. Silakan coba lagi."
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching snap token:", error);
                    alert("Terjadi kesalahan. Silakan coba lagi.");
                });
        };
    }

    // Untuk tombol "Bayar Sisa Pembayaran" di user.bookings.show (juga pakai Success.js)
    const payRemainingButton = document.getElementById("pay-remaining-button");
    if (payRemainingButton && bookingId) {
        // bookingId tetap diambil dari URL
        payRemainingButton.onclick = function () {
            fetch(`/api/payment/snap-token/${bookingId}?type=remaining_payment`) // Panggil API endpoint yang baru dengan type
                .then((response) => response.json())
                .then((data) => {
                    if (data.snap_token) {
                        if (window.snap) {
                            snap.pay(data.snap_token, {
                                onSuccess: function (result) {
                                    alert("Pelunasan Berhasil!");
                                    console.log(result);
                                    window.location.reload();
                                },
                                onPending: function (result) {
                                    alert("Pelunasan Menunggu!");
                                    console.log(result);
                                    window.location.reload();
                                },
                                onError: function (result) {
                                    alert("Pelunasan Gagal!");
                                    console.log(result);
                                    window.location.reload();
                                },
                                onClose: function () {
                                    alert(
                                        "Anda menutup popup tanpa menyelesaikan pelunasan."
                                    );
                                },
                            });
                        } else {
                            console.error("Midtrans Snap JS not loaded.");
                            alert(
                                "Sistem pembayaran belum siap. Mohon coba lagi nanti."
                            );
                        }
                    } else {
                        alert(
                            "Gagal mendapatkan token pembayaran. Silakan coba lagi."
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching snap token:", error);
                    alert("Terjadi kesalahan. Silakan coba lagi.");
                });
        };
    }
});
