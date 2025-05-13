document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#myLeavesTable tbody");

  const myLeaves = [
    {
      from: "2025-05-01",
      to: "2025-05-03",
      type: "Casual Leave",
      status: "Approved",
      reason: "Family function"
    },
    {
      from: "2025-05-10",
      to: "2025-05-10",
      type: "Sick Leave",
      status: "Pending",
      reason: "Fever and rest"
    },
    {
      from: "2025-04-22",
      to: "2025-04-23",
      type: "Earned Leave",
      status: "Rejected",
      reason: "Travel plans"
    },
    {
      from: "2025-06-15",
      to: "2025-06-15",
      type: "Work from Home",
      status: "Approved",
      reason: "Plumber visit"
    }
  ];

  myLeaves.forEach(leave => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${leave.from}</td>
      <td>${leave.to}</td>
      <td>${leave.type}</td>
      <td class="${leave.status.toLowerCase()}">${leave.status}</td>
      <td>${leave.reason}</td>
    `;
    tableBody.appendChild(row);
  });
});
