// resources/js/Pages/Mc/ServiceOptions.js

export function serviceOptions() {
    return {
        allServices: [],
        services: [],
        selectedServices: [],
        calculatedTotal: 0,
        initialPackage: "",
        isLoading: true,

        async init() {
            console.log("ServiceOptions component initializing...");

            try {
                const response = await fetch("/api/mc/services");
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(
                        `HTTP error! status: ${response.status}, response: ${errorText}`
                    );
                }
                const data = await response.json();
                this.allServices = data.services || [];
                console.log("Fetched MC Services:", this.allServices);
            } catch (error) {
                console.error("Error fetching MC services:", error);
                this.allServices = [];
                // Show user-friendly error
                alert("Gagal memuat data layanan MC. Silakan refresh halaman.");
            }

            // Get package parameter from URL
            const urlParams = new URLSearchParams(window.location.search);
            this.initialPackage = urlParams.get("package") || "";
            console.log("Initial package from URL:", this.initialPackage);

            // Filter and preselect services
            this.filterServices();
            this.preselectServices();
            this.calculateTotal();

            this.isLoading = false;
            console.log("ServiceOptions component initialized successfully");
        },

        filterServices() {
            if (!this.allServices || this.allServices.length === 0) {
                this.services = [];
                console.warn("No services available to filter");
                return;
            }

            if (this.initialPackage === "standard") {
                this.services = this.allServices.filter(
                    (service) =>
                        service.type === "individual" &&
                        [
                            "lamaran",
                            "akad_nikah",
                            "resepsi",
                            "other_events",
                        ].includes(service.slug)
                );
            } else if (this.initialPackage === "exclusive") {
                this.services = this.allServices.filter(
                    (service) =>
                        service.type === "package" &&
                        ["paket_akad_resepsi", "paket_full_wedding"].includes(
                            service.slug
                        )
                );
            } else {
                // Default: show all individual services
                this.services = this.allServices.filter(
                    (service) => service.type === "individual"
                );
            }

            console.log("Filtered services:", this.services);
        },

        preselectServices() {
            this.selectedServices = [];

            if (this.initialPackage === "exclusive") {
                const fullWeddingPackage = this.services.find(
                    (s) => s.slug === "paket_full_wedding"
                );
                if (fullWeddingPackage) {
                    this.selectedServices.push(fullWeddingPackage.slug);
                    console.log("Pre-selected full wedding package");
                } else {
                    const akadResepsiPackage = this.services.find(
                        (s) => s.slug === "paket_akad_resepsi"
                    );
                    if (akadResepsiPackage) {
                        this.selectedServices.push(akadResepsiPackage.slug);
                        console.log("Pre-selected akad resepsi package");
                    }
                }
            }

            console.log("Pre-selected services:", this.selectedServices);
        },

        calculateTotal() {
            let total = 0;

            // Reset total first
            this.calculatedTotal = 0;

            if (!this.selectedServices || this.selectedServices.length === 0) {
                console.log("No services selected, total = 0");
                return;
            }

            // Check for package services first (they override individual services)
            if (this.selectedServices.includes("paket_full_wedding")) {
                const pkg = this.allServices.find(
                    (s) => s.slug === "paket_full_wedding"
                );
                if (pkg) {
                    total = pkg.price;
                    console.log("Using full wedding package price:", total);
                }
            } else if (this.selectedServices.includes("paket_akad_resepsi")) {
                const pkg = this.allServices.find(
                    (s) => s.slug === "paket_akad_resepsi"
                );
                if (pkg) {
                    total = pkg.price;
                    console.log("Using akad resepsi package price:", total);
                }
            } else {
                // Calculate individual services total
                this.selectedServices.forEach((selectedSlug) => {
                    const service = this.allServices.find(
                        (s) => s.slug === selectedSlug
                    );
                    if (service && service.type === "individual") {
                        total += service.price;
                        console.log(
                            `Added service ${service.name}: ${service.price}, running total: ${total}`
                        );
                    }
                });
            }

            this.calculatedTotal = total;
            console.log("Final calculated total:", this.calculatedTotal);

            // Trigger reactivity update
            this.$nextTick(() => {
                console.log("Total updated in UI:", this.calculatedTotal);
            });
        },

        // Helper method to check if a service is selected
        isServiceSelected(serviceSlug) {
            return this.selectedServices.includes(serviceSlug);
        },

        // Method to handle service selection change
        onServiceChange() {
            console.log("Service selection changed:", this.selectedServices);
            this.calculateTotal();
        },
    };
}
