let currentUser = null;

async function checkLoginStatus() {
  try {
    const response = await fetch("auth.php?action=check", {
      credentials: "same-origin",
    });
    const data = await response.json();

    if (data.loggedIn && data.user) {
      showDashboard(data.user);
    } else {
      showHome();
    }
  } catch (error) {
    console.error("Fout bij controleren login status:", error);
    showHome();
  }
}

function showHome() {
  const mainContent = document.getElementById("mainContent");
  const homePage = document.getElementById("homePage");
  const navButtons = document.getElementById("navButtons");

  if (mainContent && homePage) {
    mainContent.innerHTML = homePage.innerHTML;
  }

  if (navButtons) {
    navButtons.innerHTML =
      '<button class="btn-login" onclick="showLogin()">Inloggen</button>';
  }
}

function showLogin() {
  window.location.href = "login.php";
}

function showRegister() {
  window.location.href = "login.php#register";
}

function showDashboard(user) {
  currentUser = user;

  const mainContent = document.getElementById("mainContent");
  const dashboard = document.getElementById("dashboard");
  const navButtons = document.getElementById("navButtons");

  if (!mainContent || !dashboard) {
    return;
  }

  mainContent.innerHTML = dashboard.innerHTML;

  const userName = document.getElementById("userName");
  if (userName) {
    userName.textContent = user.name;
  }

  if (navButtons) {
    const firstLetter = user.name ? user.name.charAt(0).toUpperCase() : "?";
    navButtons.innerHTML = `
            <div class="user-profile">
                <div class="user-avatar">${escapeHtml(firstLetter)}</div>
                <span>${escapeHtml(user.name)}</span>
                <button class="btn-logout" onclick="handleLogout()" style="width: auto;">Uitloggen</button>
            </div>
        `;
  }

  if (user.role === "admin") {
    const adminPanel = document.getElementById("adminPanel");
    if (adminPanel) {
      adminPanel.classList.remove("hidden");
    }
  }

  const uploadForm = document.getElementById("uploadForm");
  if (uploadForm) {
    uploadForm.addEventListener("submit", handleVideoUpload);
  }
}

async function handleVideoUpload(event) {
  event.preventDefault();

  const form = event.currentTarget;
  const submitButton = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);
  const title = String(formData.get("title") || "").trim();
  const file = formData.get("fileToUpload");

  if (!title || !(file instanceof File) || file.size === 0) {
    alert("Vul een titel in en kies een videobestand.");
    return;
  }

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = "⏳ Uploaden...";
  }

  try {
    const response = await fetch("upload.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });
    const data = await parseJsonResponse(response);

    if (data.success) {
      alert("🎉 " + data.message);
      form.reset();
      window.location.reload();
    } else {
      alert("❌ Fout bij uploaden: " + data.message);
    }
  } catch (error) {
    console.error("Upload fout:", error);
    alert(
      "Er is iets misgegaan tijdens het uploaden. Controleer de verbinding of server-limieten.",
    );
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.textContent = "📤 Video Uploaden";
    }
  }
}

async function deleteVideo(videoId) {
  if (!confirm("Weet je zeker dat je deze video permanent wilt verwijderen?")) {
    return;
  }

  const formData = new FormData();
  formData.append("video_id", videoId);

  try {
    const response = await fetch("delete_video.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });
    const data = await parseJsonResponse(response);

    if (data.success) {
      alert("🗑️ " + data.message);
      window.location.reload();
    } else {
      alert("❌ Fout: " + data.message);
    }
  } catch (error) {
    console.error("Verwijder fout:", error);
    alert("Er is een fout opgetreden bij het verwijderen van de video.");
  }
}

function loadAllUsers() {
  const usersList = document.getElementById("usersList");
  if (!usersList) {
    return;
  }

  usersList.innerHTML = `
        <div class="user-item">
            <div>
                <div style="color: #FF6B00; font-weight: bold;">admin@streamhive.com</div>
                <div style="font-size: 12px; color: #888;">Admin - 2 video's</div>
            </div>
            <span style="color: #FF8C00; font-size: 12px;">👑 Admin</span>
        </div>
        <div class="user-item">
            <div>
                <div style="color: #ccc; font-weight: bold;">user@example.com</div>
                <div style="font-size: 12px; color: #888;">User - 0 video's</div>
            </div>
            <span style="color: #6bff6b; font-size: 12px;">👤 User</span>
        </div>
    `;
}

async function handleLogout() {
  try {
    await fetch("auth.php?action=logout", {
      credentials: "same-origin",
    });
  } catch (error) {
    console.error("Logout fout:", error);
  } finally {
    window.location.href = "index.php";
  }
}

async function parseJsonResponse(response) {
  let data;

  try {
    data = await response.json();
  } catch (error) {
    throw new Error("Ongeldige server response.");
  }

  if (!response.ok && data && !data.message) {
    data.message = "Serverfout: " + response.status;
  }

  return data;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

window.addEventListener("load", checkLoginStatus);
