let leaveData = JSON.parse(localStorage.getItem("leaveRequests")) || [];
let editingIndex = -1;

function renderLeaveRequests() {
  const leaveTableBody = document.querySelector("#leaveTable tbody");
  leaveTableBody.innerHTML = ""; // Clear existing rows

  leaveData.forEach((leave, index) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${leave.leaveType}</td>
      <td>${leave.fromDate}</td>
      <td>${leave.toDate}</td>
      <td>${leave.reason}</td>
      <td>${leave.status}</td>
      <td>
        <button onclick="editLeave(${index})">✏️ Edit</button>
        <button onclick="deleteLeave(${index})">🗑️ Delete</button>
      </td>
    `;

    leaveTableBody.appendChild(row);
  });
}

function saveLeaveRequests() {
  localStorage.setItem("leaveRequests", JSON.stringify(leaveData));
  renderLeaveRequests();
}

function deleteLeave(index) {
  leaveData.splice(index, 1);
  saveLeaveRequests();
}

function editLeave(index) {
  const leave = leaveData[index];
  document.getElementById("leaveType").value = leave.leaveType;
  document.getElementById("fromDate").value = leave.fromDate;
  document.getElementById("toDate").value = leave.toDate;
  document.getElementById("reason").value = leave.reason;

  editingIndex = index;
  document.querySelector("button[type='submit']").textContent = "Update Leave Request";
}

document.getElementById("leaveForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const leaveType = document.getElementById("leaveType").value;
  const fromDate = document.getElementById("fromDate").value;
  const toDate = document.getElementById("toDate").value;
  const reason = document.getElementById("reason").value;

  const newLeave = {
    leaveType,
    fromDate,
    toDate,
    reason,
    status: "Pending"
  };

  if (editingIndex > -1) {
    leaveData[editingIndex] = newLeave;
    editingIndex = -1;
    document.querySelector("button[type='submit']").textContent = "Submit Leave Request";
  } else {
    leaveData.push(newLeave);
  }

  saveLeaveRequests();
  
  document.getElementById("successMessage").style.display = "block";
  setTimeout(() => {
    document.getElementById("successMessage").style.display = "none";
    document.getElementById("leaveForm").reset();
  }, 1500);
});

renderLeaveRequests();
