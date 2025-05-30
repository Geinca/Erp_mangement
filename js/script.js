
 fetch('sidebar.html')
    .then(res => res.text())
    .then(html => {
      document.getElementById('sidebar-container').innerHTML = html;
    });


// 🕒 Live Clock
function updateClock() {
  const now = new Date();
  document.getElementById("clock").textContent = now.toLocaleTimeString();
}
setInterval(updateClock, 1000);
updateClock();

// 🟢 Mock Attendance Check-in
const checkInBtn = document.getElementById('checkInBtn');
const checkOutBtn = document.getElementById('checkOutBtn');
const breakStartBtn = document.getElementById('breakStartBtn');
const breakStopBtn = document.getElementById('breakStopBtn');
const status = document.getElementById('attendanceStatus');
const timeDisplay = document.getElementById('attendanceTime');

let isOnBreak = false;
let isCheckedIn = false;

function getCurrentTime() {
  const now = new Date();
  return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

checkInBtn.addEventListener('click', () => {
  isCheckedIn = true;
  status.textContent = "Checked In";
  timeDisplay.textContent = "Checked in at: " + getCurrentTime();
  checkInBtn.disabled = true;
  checkOutBtn.disabled = false;
  breakStartBtn.disabled = false;
});

checkOutBtn.addEventListener('click', () => {
  isCheckedIn = false;
  isOnBreak = false;
  status.textContent = "Checked Out";
  timeDisplay.textContent = "Checked out at: " + getCurrentTime();
  checkInBtn.disabled = false;
  checkOutBtn.disabled = true;
  breakStartBtn.disabled = true;
  breakStopBtn.disabled = true;
});

breakStartBtn.addEventListener('click', () => {
  if (isCheckedIn && !isOnBreak) {
    isOnBreak = true;
    status.textContent = "On Break";
    timeDisplay.textContent = "Break started at: " + getCurrentTime();
    breakStartBtn.disabled = true;
    breakStopBtn.disabled = false;
  }
});

breakStopBtn.addEventListener('click', () => {
  if (isCheckedIn && isOnBreak) {
    isOnBreak = false;
    status.textContent = "Checked In";
    timeDisplay.textContent = "Break ended at: " + getCurrentTime();
    breakStartBtn.disabled = false;
    breakStopBtn.disabled = true;
  }
});


// 📊 Dynamic Leave & Payroll Info
const leaveBalance = 10; // Simulate fetched data
const payrollDate = "May 31, 2025";

document.getElementById("leaveBalance").textContent = `${leaveBalance} Days Remaining`;
document.getElementById("payrollDate").textContent = payrollDate;

// ✅ Sidebar Active Link Toggle
document.querySelectorAll(".sidebar nav a").forEach(link => {
  link.addEventListener("click", () => {
    document.querySelector(".sidebar nav a.active")?.classList.remove("active");
    link.classList.add("active");
  });
});
