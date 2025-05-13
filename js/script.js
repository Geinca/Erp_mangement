
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
document.getElementById("checkInBtn").addEventListener("click", () => {
  const now = new Date();
  document.getElementById("attendanceStatus").textContent = `Checked in at ${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;
  document.getElementById("checkInBtn").disabled = true;
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
