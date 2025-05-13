document.addEventListener("DOMContentLoaded", () => {
  const teamLeaveList = document.getElementById("teamLeaveList");
  const noLeavesMessage = document.getElementById("noLeavesMessage");

  const teamLeaves = [
    { name: "Alice Johnson", type: "Sick Leave" },
    { name: "Ravi Kumar", type: "Casual Leave" },
    { name: "Maria Lopez", type: "Work from Home" }
    // Add or fetch dynamically
  ];

  if (teamLeaves.length === 0) {
    noLeavesMessage.style.display = "block";
  } else {
    noLeavesMessage.style.display = "none";

    teamLeaves.forEach(member => {
      const li = document.createElement("li");
      li.innerHTML = `
        <span class="name">${member.name}</span>
        <span class="type">${member.type}</span>
      `;
      teamLeaveList.appendChild(li);
    });
  }
});
