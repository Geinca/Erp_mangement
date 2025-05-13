const holidayList = document.getElementById('holidayList');
const countrySelect = document.getElementById('countrySelect');
const yearSelect = document.getElementById('yearSelect');
const monthSelect = document.getElementById('monthSelect');
const darkModeToggle = document.getElementById('darkModeToggle');



// Save user preferences
function savePreferences() {
  localStorage.setItem('country', countrySelect.value);
  localStorage.setItem('year', yearSelect.value);
  localStorage.setItem('month', monthSelect.value);
  localStorage.setItem('darkMode', darkModeToggle.checked ? '1' : '0');
}

// Load preferences
function loadPreferences() {
  if (localStorage.getItem('country')) countrySelect.value = localStorage.getItem('country');
  if (localStorage.getItem('year')) yearSelect.value = localStorage.getItem('year');
  if (localStorage.getItem('month')) monthSelect.value = localStorage.getItem('month');
  if (localStorage.getItem('darkMode') === '1') {
    darkModeToggle.checked = true;
    document.body.classList.add('dark');
  }
}

// Load holidays for selected country/year/month
async function loadHolidays(country, year, month) {
  const apiKey = "E3jWuUecHrPPP3UJD6xgrOWUwZRiNTVW";
  const url = `https://calendarific.com/api/v2/holidays?&api_key=${apiKey}&country=${country}&year=${year}`;
  holidayList.innerHTML = `<li>🔄 Loading holidays...</li>`;

  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
    const data = await res.json();

    if (!data?.response?.holidays) throw new Error("No holidays data found.");

    const holidays = data.response.holidays;
    const filtered = holidays.filter(h => {
      if (!month) return true;
      const holidayMonth = new Date(h.date.iso).getMonth() + 1;
      return holidayMonth === parseInt(month);
    });

    holidayList.innerHTML = filtered.length
      ? filtered.map(h => `
        <li>
          <span>${new Date(h.date.iso).toDateString()}</span>
          <span>${h.name}</span>
          <img src="https://flagcdn.com/24x18/${country.toLowerCase()}.png" alt="flag" style="margin-left:10px;">
        </li>`).join('')
      : `<li>📭 No holidays found for the selected month.</li>`;
  } catch (err) {
    console.error("Error fetching holidays:", err);
    holidayList.innerHTML = `<li>⚠️ Failed to load holidays. Try again later.</li>`;
  }
}

// Dashboard: render next 5 upcoming holidays
async function renderDashboardHolidays(country = 'IN', year = new Date().getFullYear(), maxItems = 5) {
  const apiKey = "E3jWuUecHrPPP3UJD6xgrOWUwZRiNTVW";
  const url = `https://calendarific.com/api/v2/holidays?&api_key=${apiKey}&country=${country}&year=${year}`;
  const holidayList = document.getElementById('dashboardHolidayList');
  if (!holidayList) return;

  holidayList.innerHTML = `<li>🔄 Loading holidays...</li>`;

  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error("Failed to fetch holidays");
    const data = await res.json();

    const today = new Date();
    const upcoming = data.response.holidays
      .map(h => ({ name: h.name, date: new Date(h.date.iso) }))
      .filter(h => h.date >= today)
      .sort((a, b) => a.date - b.date)
      .slice(0, maxItems);

    holidayList.innerHTML = upcoming.length
      ? upcoming.map(h => `
          <li>
            <span>${h.date.toDateString()}</span>
            <span>${h.name}</span>
            <img src="https://flagcdn.com/24x18/${country.toLowerCase()}.png" alt="flag" style="margin-left:10px;">
          </li>
        `).join('')
      : `<li>📭 No upcoming holidays</li>`;
  } catch (err) {
    console.error("Dashboard holidays error:", err);
    holidayList.innerHTML = `<li>⚠️ Error loading holidays</li>`;
  }
}

// Update Next Holiday card
async function updateNextHolidayCard(country, year) {
  const apiKey = "E3jWuUecHrPPP3UJD6xgrOWUwZRiNTVW";
  const url = `https://calendarific.com/api/v2/holidays?&api_key=${apiKey}&country=${country}&year=${year}`;
  const infoBox = document.getElementById('nextHolidayInfo');

  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error("Failed to fetch holidays");
    const data = await res.json();

    const today = new Date();
    const upcoming = data.response.holidays
      .map(h => ({ name: h.name, date: new Date(h.date.iso) }))
      .filter(h => h.date >= today)
      .sort((a, b) => a.date - b.date);

    if (upcoming.length > 0) {
      const next = upcoming[0];
      const formattedDate = next.date.toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
      infoBox.innerHTML = `${next.name} - <strong>${formattedDate}</strong>`;
    } else {
      infoBox.innerText = "📭 No upcoming holidays";
    }
  } catch (err) {
    console.error("Next holiday error:", err);
    infoBox.innerText = "⚠️ Error loading holiday";
  }
}

// Refresh
function refresh() {
  const country = countrySelect.value;
  const year = yearSelect.value;
  const month = monthSelect.value;

  localStorage.setItem('country', country);
  localStorage.setItem('year', year);
  localStorage.setItem('month', month);

  loadHolidays(country, year, month);
  updateNextHolidayCard(country, year);
}

// On page load
window.onload = () => {
  const savedCountry = localStorage.getItem('country') || 'IN';
  const savedYear = localStorage.getItem('year') || '2025';
  const savedMonth = localStorage.getItem('month') || '';

  if (countrySelect) countrySelect.value = savedCountry;
  if (yearSelect) yearSelect.value = savedYear;
  if (monthSelect) monthSelect.value = savedMonth;

  if (holidayList) loadHolidays(savedCountry, savedYear, savedMonth);
  if (document.getElementById('nextHolidayInfo')) updateNextHolidayCard(savedCountry, savedYear);
  if (document.getElementById('dashboardHolidayList')) renderDashboardHolidays(savedCountry, savedYear);

  if (countrySelect) countrySelect.addEventListener('change', refresh);
  if (yearSelect) yearSelect.addEventListener('change', refresh);
  if (monthSelect) monthSelect.addEventListener('change', refresh);
};
