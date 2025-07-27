import "./bootstrap";

import Alpine from "alpinejs";

import { serviceOptions } from "./pages/mc/ServiceOption";

window.Alpine = Alpine;

window.serviceOptions = serviceOptions;

Alpine.start();

import "./pages/mc/show";
import "./pages/mc/ServiceOption";
import "./pages/booking/success";
import "./pages/admin/bookings/index";
