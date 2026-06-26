import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";
import "../css/style.css";

import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();

// =========================
// INIT FLATPICKR AMAN
// =========================
const datepickers = document.querySelectorAll(".datepicker");

if (datepickers.length) {
  flatpickr(".datepicker", {
    mode: "range",
    static: true,
    monthSelectorType: "static",
    dateFormat: "M j",
    defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
    prevArrow:
      '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    nextArrow:
      '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    onReady: (selectedDates, dateStr, instance) => {
      instance.element.value = dateStr.replace("to", "-");

      const customClass = instance.element.getAttribute("data-class");

      if (customClass && instance.calendarContainer) {
        instance.calendarContainer.classList.add(customClass);
      }
    },
    onChange: (selectedDates, dateStr, instance) => {
      instance.element.value = dateStr.replace("to", "-");
    },
  });
}

// =========================
// INIT DROPZONE AMAN
// =========================
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
  Dropzone.autoDiscover = false;

  try {
    new Dropzone("#demo-upload", {
      url: "/file/post",
    });
  } catch (err) {
    console.warn("Dropzone gagal init:", err);
  }
}

// =========================
// INIT CHART / MAP AMAN
// =========================
document.addEventListener("DOMContentLoaded", () => {
  try {
    const chartOne = document.getElementById("chartOne") || document.querySelector("#chart01");
    if (chartOne) chart01();
  } catch (err) {
    console.warn("chart01 dilewati:", err);
  }

  try {
    const chartTwo = document.getElementById("chartTwo") || document.querySelector("#chart02");
    if (chartTwo) chart02();
  } catch (err) {
    console.warn("chart02 dilewati:", err);
  }

  try {
    const chartThree = document.getElementById("chartThree") || document.querySelector("#chart03");
    if (chartThree) chart03();
  } catch (err) {
    console.warn("chart03 dilewati:", err);
  }

  try {
    const mapOne =
      document.getElementById("mapOne") ||
      document.getElementById("map01") ||
      document.querySelector("#mapOne");

    if (mapOne) map01();
  } catch (err) {
    console.warn("map01 dilewati:", err);
  }
});

// =========================
// CURRENT YEAR AMAN
// =========================
const year = document.getElementById("year");

if (year) {
  year.textContent = new Date().getFullYear();
}

// =========================
// COPY BUTTON AMAN
// =========================
document.addEventListener("DOMContentLoaded", () => {
  const copyInput = document.getElementById("copy-input");
  const copyButton = document.getElementById("copy-button");
  const copyText = document.getElementById("copy-text");
  const websiteInput = document.getElementById("website-input");

  if (!copyInput || !copyButton || !copyText || !websiteInput) {
    return;
  }

  copyButton.addEventListener("click", () => {
    navigator.clipboard.writeText(websiteInput.value).then(() => {
      copyText.textContent = "Copied";

      setTimeout(() => {
        copyText.textContent = "Copy";
      }, 2000);
    });
  });
});

// =========================
// SEARCH SHORTCUT AMAN
// =========================
document.addEventListener("DOMContentLoaded", function () {
  const searchInput =
    document.getElementById("search-input") ||
    document.getElementById("searchInput") ||
    document.querySelector("[data-search-input]");

  const searchButton =
    document.getElementById("search-button") ||
    document.querySelector("[data-search-button]");

  function focusSearchInput() {
    if (!searchInput) return;

    searchInput.focus();

    if (typeof searchInput.select === "function") {
      searchInput.select();
    }
  }

  if (searchButton) {
    searchButton.addEventListener("click", focusSearchInput);
  }

  document.addEventListener("keydown", function (event) {
    if (!searchInput) return;

    const activeElement = document.activeElement;
    const tagName = activeElement?.tagName?.toLowerCase();

    const isTyping =
      tagName === "input" ||
      tagName === "textarea" ||
      activeElement?.isContentEditable;

    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      focusSearchInput();
      return;
    }

    if (event.key === "/" && !isTyping) {
      event.preventDefault();
      focusSearchInput();
    }
  });
});