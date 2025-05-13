// Get elements
const calendarContainer = document.getElementById("calendarContainer");
const monthSelect = document.getElementById("monthSelect");

// Load today's month initially
window.addEventListener("DOMContentLoaded", () => {
  const today = new Date();
  monthSelect.value = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}`;
  renderCalendar(today.getFullYear(), today.getMonth());
});

monthSelect.addEventListener("change", () => {
  const [year, month] = monthSelect.value.split("-");
  renderCalendar(parseInt(year), parseInt(month) - 1);
});

function renderCalendar(year, month) {
  calendarContainer.innerHTML = ""; // Clear previous content

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const totalDays = lastDay.getDate();

  const startDay = firstDay.getDay(); // 0 = Sunday, 6 = Saturday

  // Add blank cells for days before the first day of the month
  for (let i = 0; i < startDay; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.classList.add("empty-cell");
    calendarContainer.appendChild(emptyCell);
  }

  for (let day = 1; day <= totalDays; day++) {
    const date = new Date(year, month, day);
    const cell = document.createElement("div");
    cell.textContent = day;
    cell.classList.add("calendar-cell");

    const isWeekend = date.getDay() === 0 || date.getDay() === 6;
    if (isWeekend) cell.classList.add("weekend");

    // Load from localStorage
    const key = `leave-${year}-${month + 1}-${day}`;
    const savedStatus = localStorage.getItem(key);
    if (savedStatus === "leave") {
      cell.classList.add("leave");
    }

    // Mark as leave on click
    cell.addEventListener("click", () => {
      if (cell.classList.contains("leave")) {
        cell.classList.remove("leave");
        localStorage.removeItem(key);
      } else {
        cell.classList.add("leave");
        localStorage.setItem(key, "leave");
      }
    });

    calendarContainer.appendChild(cell);
  }
}
