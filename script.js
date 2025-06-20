document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");
  const loginForm = document.getElementById("loginForm");
  const messageDiv = document.getElementById("message");

  if (registerForm) {
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const username = document.getElementById("regUsername").value.trim();
      const password = document.getElementById("regPassword").value;

      if (!username || !password) {
        messageDiv.textContent = "Compila tutti i campi.";
        return;
      }

      // Controllo se l'utente esiste già
      const users = JSON.parse(localStorage.getItem("users") || "{}");
      if (users[username]) {
        messageDiv.textContent = "Utente già registrato.";
        return;
      }

      // Salva l'utente
      users[username] = password;
      localStorage.setItem("users", JSON.stringify(users));

      // Redirect al login
      alert("Registrazione completata! Ora puoi accedere.");
      window.location.href = "login.html";
    });
  }

  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const username = document.getElementById("loginUsername").value.trim();
      const password = document.getElementById("loginPassword").value;

      const users = JSON.parse(localStorage.getItem("users") || "{}");

      if (!users[username]) {
        messageDiv.textContent = "Utente non registrato.";
        return;
      }

      if (users[username] !== password) {
        messageDiv.textContent = "Password errata.";
        return;
      }

      // Login riuscito
      window.location.href = "home.html?user=" + encodeURIComponent(username);
    });
  }
});
