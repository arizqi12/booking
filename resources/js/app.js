import "./bootstrap";

import Alpine from "alpinejs";
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";

window.Alpine = Alpine;

Alpine.start();

import "./pages/mc/show";
import "./pages/mc/ServiceOption";
import "./pages/booking/success";
import "./pages/admin/bookings/index";
