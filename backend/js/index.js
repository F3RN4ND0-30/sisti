// HELPDESK INDEX - JavaScript

document.addEventListener("DOMContentLoaded", function () {
  // Animaciones de entrada
  animateElements();

  // Event listeners para botones
  setupButtonEffects();

  // Responsive adjustments
  handleResize();
});

// Animaciones de entrada
function animateElements() {
  const heroSection = document.querySelector(".hero-section");
  const actionSection = document.querySelector(".action-section");
  const howItWorks = document.querySelector(".how-it-works");

  // Fade in con delay
  setTimeout(() => {
    if (heroSection) heroSection.style.opacity = "1";
  }, 200);

  setTimeout(() => {
    if (actionSection) actionSection.style.opacity = "1";
  }, 400);

  setTimeout(() => {
    if (howItWorks) howItWorks.style.opacity = "1";
  }, 600);
}

// Efectos de botones
function setupButtonEffects() {
  const actionButtons = document.querySelectorAll(".action-btn");
  const stepCards = document.querySelectorAll(".step-card");

  // Efectos hover mejorados para botones
  actionButtons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-3px) scale(1.02)";
    });

    button.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });

    // Efecto click
    button.addEventListener("click", function (e) {
      // Efecto ripple
      createRippleEffect(e, this);
    });
  });

  // Efectos para cards
  stepCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });
}

// Efecto ripple en botones
function createRippleEffect(event, element) {
  const ripple = document.createElement("span");
  const rect = element.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = event.clientX - rect.left - size / 2;
  const y = event.clientY - rect.top - size / 2;

  ripple.style.width = ripple.style.height = size + "px";
  ripple.style.left = x + "px";
  ripple.style.top = y + "px";
  ripple.classList.add("ripple");

  element.appendChild(ripple);

  setTimeout(() => {
    ripple.remove();
  }, 600);
}

// Ajustes responsive
function handleResize() {
  window.addEventListener("resize", function () {
    // Ajustar altura en móviles con teclado virtual
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", `${vh}px`);
  });

  // Ejecutar al cargar
  const vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty("--vh", `${vh}px`);
}

// Detectar orientación en móviles
function handleOrientation() {
  window.addEventListener("orientationchange", function () {
    setTimeout(() => {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty("--vh", `${vh}px`);
    }, 100);
  });
}

// Validación antes de navegar (opcional)
function validateNavigation() {
  const registrarBtn = document.querySelector('a[href="registro-ticket.php"]');
  const consultarBtn = document.querySelector('a[href="seguimiento.php"]');

  if (registrarBtn) {
    registrarBtn.addEventListener("click", function (e) {
      // Aquí puedes agregar validaciones o analytics
      console.log("Usuario navega a registro de ticket");
    });
  }

  if (consultarBtn) {
    consultarBtn.addEventListener("click", function (e) {
      // Aquí puedes agregar validaciones o analytics
      console.log("Usuario navega a consulta de ticket");
    });
  }
}

// Inicializar validaciones
validateNavigation();
handleOrientation();
