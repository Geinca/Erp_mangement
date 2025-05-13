function sendMessage() {
  const input = document.getElementById("user-input");
  const message = input.value.trim();
  if (!message) return;

  appendMessage("user", message);
  input.value = "";

  // Simulated bot response
  setTimeout(() => {
    const response = getBotResponse(message);
    appendMessage("bot", response);
  }, 500);
}

function appendMessage(sender, text) {
  const container = document.getElementById("chat-messages");
  const msg = document.createElement("div");
  msg.className = `message ${sender}`;
  msg.textContent = text;
  container.appendChild(msg);
  container.scrollTop = container.scrollHeight;
}

function getBotResponse(message) {
  // Placeholder response logic
  if (message.toLowerCase().includes("leave")) return "You have 5 casual leaves and 2 sick leaves remaining.";
  if (message.toLowerCase().includes("holiday")) return "Next holiday is on May 20: Independence Day.";
  return "I'm not sure. Please ask about leave, holidays, or policies.";
}
