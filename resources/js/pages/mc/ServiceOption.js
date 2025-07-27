// resources/js/Pages/Mc/ServiceOptions.js

export function serviceOptions() {
    return {
        services: [
            { label: "Lamaran", value: "lamaran", price: 650000 },
            { label: "Akad Nikah", value: "akad_nikah", price: 800000 },
            { label: "Resepsi", value: "resepsi", price: 800000 },
            {
                label: "Paket Akad & Resepsi",
                value: "paket_akad_resepsi",
                price: 1000000,
            },
            {
                label: "Paket Lamaran, Akad & Resepsi",
                value: "paket_full_wedding",
                price: 1500000,
            },
            {
                label: "Seminar, Gathering, Event, Grand Opening, Birthday, dll.",
                value: "other_events",
                price: 700000,
            },
        ],
        selectedServices: [],
        calculatedTotal: 0,

        init() {
            this.calculateTotal();
        },

        calculateTotal() {
            let total = 0;
            let hasLamaran = this.selectedServices.includes("lamaran");
            let hasAkad = this.selectedServices.includes("akad_nikah");
            let hasResepsi = this.selectedServices.includes("resepsi");
            let hasOther = this.selectedServices.includes("other_events");
            let hasPaketAkadResepsi =
                this.selectedServices.includes("paket_akad_resepsi");
            let hasPaketFullWedding =
                this.selectedServices.includes("paket_full_wedding");

            if (hasPaketFullWedding) {
                total = this.services.find(
                    (s) => s.value === "paket_full_wedding"
                ).price;
            } else if (hasPaketAkadResepsi) {
                total = this.services.find(
                    (s) => s.value === "paket_akad_resepsi"
                ).price;
            } else {
                if (hasLamaran) {
                    total += this.services.find(
                        (s) => s.value === "lamaran"
                    ).price;
                }
                if (hasAkad) {
                    total += this.services.find(
                        (s) => s.value === "akad_nikah"
                    ).price;
                }
                if (hasResepsi) {
                    total += this.services.find(
                        (s) => s.value === "resepsi"
                    ).price;
                }
                if (hasOther) {
                    total += this.services.find(
                        (s) => s.value === "other_events"
                    ).price;
                }
            }
            this.calculatedTotal = total;
        },
    };
}
