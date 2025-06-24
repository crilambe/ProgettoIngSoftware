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

  function mostraEmailUtente() {
  const params = new URLSearchParams(window.location.search);
  const username = params.get("user");
  if (username) {
    document.getElementById("user-email").textContent = username;
    localStorage.setItem("utenteLoggato", username);
  } else {
    window.location.href = "login.html";
  }
}

function aggiornaSidebarTagsECartelle(note) {
  const tagList = document.getElementById("dynamic-tag-list");
  const folderList = document.getElementById("dynamic-folder-list");
  tagList.innerHTML = '';
  folderList.innerHTML = '';

  const allTags = new Set();
  const allFolders = new Set();

  note.forEach(n => {
    if (n.tag && Array.isArray(n.tag)) {
      n.tag.forEach(tag => allTags.add(tag.toLowerCase()));
    }
    if (n.cartella) {
      allFolders.add(n.cartella.toLowerCase());
    }
  });

  Array.from(allTags).sort().forEach(tag => {
    const li = document.createElement("li");
    li.innerHTML = `<a href="#" data-filter-type="tag" data-filter-value="${tag}">#${tag}</a>`;
    tagList.appendChild(li);
  });

  Array.from(allFolders).sort().forEach(folder => {
    const li = document.createElement("li");
    li.innerHTML = `<a href="#" data-filter-type="folder" data-filter-value="${folder}">${folder}</a>`;
    folderList.appendChild(li);
  });

  document.querySelectorAll('.tags-sidebar a').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const filterType = this.dataset.filterType;
      const filterValue = this.dataset.filterValue;
      mostraNotePubbliche(filterType, filterValue);
    });
  });
}

function mostraNotePubbliche(filterType = null, filterValue = null) {
  const contenitore = document.querySelector(".notes-list");
  contenitore.innerHTML = "";

  const noteSalvate = JSON.parse(localStorage.getItem("notePubbliche") || "[]");
  const username = localStorage.getItem("utenteLoggato");

  let noteDaVisualizzare = noteSalvate;
  if (filterType && filterValue) {
    noteDaVisualizzare = noteSalvate.filter(n => {
      if (filterType === 'tag' && n.tag) return n.tag.includes(filterValue);
      if (filterType === 'folder' && n.cartella) return n.cartella.toLowerCase() === filterValue;
      return false;
    });
  }

  noteDaVisualizzare.sort((a, b) => new Date(b.data) - new Date(a.data));

  if (noteDaVisualizzare.length === 0) {
    contenitore.innerHTML = '<p style="text-align: center; color: #6c757d; padding: 20px;">Nessuna nota trovata per i criteri selezionati.</p>';
    return;
  }

  noteDaVisualizzare.forEach((n, index) => {
    const div = document.createElement("div");
    div.className = "note";

    let tagsHtml = '';
    if (n.tag?.length) {
      tagsHtml = `<div class="note-tags">${n.tag.map(tag => `<span>#${tag}</span>`).join(' ')}</div>`;
    }

    let folderHtml = n.cartella ? `<span class="note-folder">Cartella: ${n.cartella}</span>` : '';

    div.innerHTML = `
      <p class="note-text">${n.testo}</p>
      <div class="note-meta">${tagsHtml} ${folderHtml}</div>
      <p class="note-author">— ${n.autore}</p>
    `;

    if (n.autore === username) {
      const deleteBtn = document.createElement("button");
      deleteBtn.className = "delete-note-btn";
      deleteBtn.textContent = "Elimina";
      deleteBtn.addEventListener("click", () => {
        const tutteNote = JSON.parse(localStorage.getItem("notePubbliche") || "[]");
        const indexToRemove = tutteNote.findIndex(note =>
          note.testo === n.testo &&
          note.data === n.data &&
          note.autore === n.autore
        );
        if (indexToRemove !== -1) {
          tutteNote.splice(indexToRemove, 1);
          localStorage.setItem("notePubbliche", JSON.stringify(tutteNote));
          mostraNotePubbliche();
        }
      });
      div.appendChild(deleteBtn);
    }

    contenitore.appendChild(div);
  });

  aggiornaSidebarTagsECartelle(noteSalvate);
}

document.getElementById("noteForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const noteText = document.getElementById("noteText").value.trim();
  const noteTags = document.getElementById("noteTags").value.trim().split(",").map(tag => tag.trim().toLowerCase()).filter(tag => tag);
  const noteFolder = document.getElementById("noteFolder").value.trim();
  const username = localStorage.getItem("utenteLoggato");

  if (!noteText || !username) return;

  const nota = {
    autore: username,
    testo: noteText,
    tag: noteTags,
    cartella: noteFolder,
    data: new Date().toISOString()
  };

  const noteSalvate = JSON.parse(localStorage.getItem("notePubbliche") || "[]");
  noteSalvate.unshift(nota);
  localStorage.setItem("notePubbliche", JSON.stringify(noteSalvate));

  document.getElementById("noteForm").reset();
  document.getElementById("noteMessage").textContent = "Nota pubblicata con successo!";
  document.getElementById("noteMessage").style.color = "#28a745";

  mostraNotePubbliche();
});

mostraEmailUtente();
mostraNotePubbliche();

});
