// resources/js/Pages/Booking/Success.js

document.addEventListener("DOMContentLoaded", function () {
    const payButton = document.getElementById("pay-button");
    const checkStatusButton = document.getElementById("check-status-button");
    const urlParts = window.location.pathname.split("/");
    const bookingId = urlParts[urlParts.length - 1];

    // Logika untuk tombol "Lanjutkan Pembayaran" (Midtrans Snap)
    if (payButton && bookingId) {
        payButton.onclick = function () {
            // CHANGE THIS: back to /api/...
            fetch(`/api/payment/snap-token/${bookingId}?type=initial_payment`)
                .then((response) => {
                    if (!response.ok) {
                        return response.text().then((text) => {
                            throw new Error(
                                `HTTP error! status: ${response.status}, response: ${text}`
                            );
                        });
                    }
                    return response.json();
                })
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

    // Logika untuk tombol "Cek Status Pembayaran (Manual)"
    if (checkStatusButton && bookingId) {
        checkStatusButton.onclick = function () {
            // CHANGE THIS: back to /api/...
            fetch(`/api/payment/status/${bookingId}`)
                .then((response) => {
                    if (!response.ok) {
                        return response.text().then((text) => {
                            throw new Error(
                                `HTTP error! status: ${response.status}, response: ${text}`
                            );
                        });
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.status) {
                        alert(
                            `Status Transaksi Midtrans: ${data.status}\nStatus Booking Anda: ${data.booking_status}`
                        );
                        window.location.reload();
                    } else {
                        alert("Gagal mendapatkan status transaksi.");
                    }
                })
                .catch((error) => {
                    console.error("Error checking transaction status:", error);
                    alert(
                        "Terjadi kesalahan saat mengecek status. Silakan coba lagi."
                    );
                });
        };
    }

    const payRemainingButton = document.getElementById("pay-remaining-button");
    if (payRemainingButton && bookingId) {
        payRemainingButton.onclick = function () {
            // CHANGE THIS: back to /api/...
            fetch(`/api/payment/snap-token/${bookingId}?type=remaining_payment`)
                .then((response) => {
                    if (!response.ok) {
                        return response.text().then((text) => {
                            throw new Error(
                                `HTTP error! status: ${response.status}, response: ${text}`
                            );
                        });
                    }
                    return response.json();
                })
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
