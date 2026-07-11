// ============ LedgerGo SPA Router ============
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

const routes = {
  dashboard: "pages/dashboard.php",
  customers: "pages/customers.php",
  suppliers: "pages/suppliers.php",
  products: "pages/products.php",
  purchase: "pages/purchase.php",
  sales: "pages/sales.php",
  expenses: "pages/expenses.php",
  reports: "pages/reports.php",
  settings: "pages/settings.php",
};

const pageContainer = document.getElementById("pageContainer");
const routeLoader = document.getElementById("routeLoader");

// ===== Core Navigation =====
async function loadRoute(routeName) {
  if (!routes[routeName]) routeName = "dashboard";

  routeLoader.classList.add("show");

  try {
    const res = await fetch(routes[routeName], {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (res.status === 401) {
      window.location.href = "login.php";
      return;
    }

    const html = await res.text();
    pageContainer.innerHTML = html;

    // Animate page transition
    gsap.fromTo(
      pageContainer,
      { opacity: 0, y: 10 },
      { opacity: 1, y: 0, duration: 0.35 },
    );

    highlightActiveSidebarLink(routeName);

    // Trigger a custom event so each page's own script can init itself
    document.dispatchEvent(
      new CustomEvent("routeLoaded", { detail: { route: routeName } }),
    );
  } catch (err) {
    pageContainer.innerHTML = `<div class="alert alert-danger">Failed to load page. Please try again.</div>`;
  } finally {
    routeLoader.classList.remove("show");
  }
}

function highlightActiveSidebarLink(routeName) {
  document.querySelectorAll(".sidebar-link").forEach((link) => {
    link.classList.toggle("active", link.dataset.route === routeName);
  });
}

function getCurrentRoute() {
  const hash = window.location.hash.replace("#/", "");
  return hash || "dashboard";
}

// ===== Hash Change Listener =====
window.addEventListener("hashchange", () => loadRoute(getCurrentRoute()));

// ===== Initial Load =====
window.addEventListener("DOMContentLoaded", () => {
  if (!window.location.hash) {
    window.location.hash = "#/dashboard";
  } else {
    loadRoute(getCurrentRoute());
  }
});

// ===== Sidebar Toggle (Mobile) =====
const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
const mainSidebar = document.getElementById("mainSidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");

sidebarToggleBtn?.addEventListener("click", () => {
  mainSidebar.classList.toggle("show");
  sidebarOverlay.classList.toggle("show");
});
sidebarOverlay?.addEventListener("click", () => {
  mainSidebar.classList.remove("show");
  sidebarOverlay.classList.remove("show");
});

// ===== Logout =====
document.getElementById("logoutBtn")?.addEventListener("click", async (e) => {
  e.preventDefault();
  const result = await Swal.fire({
    title: "Logout?",
    text: "Are you sure you want to logout?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#2F5BE0",
    confirmButtonText: "Yes, Logout",
  });

  if (result.isConfirmed) {
    try {
      await auth.signOut();
    } catch (e) {}
    await fetch("api/auth/logout.php", { method: "POST" });
    window.location.href = "login.php";
  }
});

// ===== Language Switch =====
document.querySelectorAll(".lang-switch").forEach((el) => {
  el.addEventListener("click", async (e) => {
    e.preventDefault();
    const lang = e.target.dataset.lang;
    await fetch("api/settings/update-language.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ lang, csrf_token: CSRF_TOKEN }),
    });
    window.location.reload();
  });
});

// ===== Global AJAX Helper (used by every page) =====
async function apiRequest(url, method = "GET", body = null) {
  const options = {
    method,
    headers: { "Content-Type": "application/json" },
  };
  if (body) {
    body.csrf_token = CSRF_TOKEN;
    options.body = JSON.stringify(body);
  }
  const res = await fetch(url, options);
  if (res.status === 401) {
    window.location.href = "login.php";
    return null;
  }
  return res.json();
}

// ===== Global Toast Helper =====
function showToast(message, icon = "success") {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon,
    title: message,
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });
}
