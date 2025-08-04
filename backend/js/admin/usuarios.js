// === URL de la API (Global) ===
const API = "/sisti/backend/php/admin/fcs_usuarios.php";

$(document).ready(function () {
  console.log("Módulo Usuarios conectado a la BD");

  // Inicializar DataTable
  const tabla = $("#tablaUsuarios").DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json" },
    ajax: {
      url: `${API}?action=listar`,
      dataSrc: function (json) {
        if (!json || !json.data) {
          console.error("Respuesta inválida del servidor:", json);
          mostrarAlertaInterna("Error al cargar usuarios", "danger");
          return [];
        }
        return json.data;
      },
    },
    columns: [
      { data: "Dni" },
      { data: "Nombre" },
      { data: "Apellido_Paterno" },
      { data: "Apellido_Materno" },
      { data: "Usuario" },
      { data: "rol_nombre" },
      {
        data: "Activo",
        render: (d) =>
          d == 1
            ? '<span class="usuarios-badge-activo">Activo</span>'
            : '<span class="usuarios-badge-inactivo">Inactivo</span>',
      },
      {
        data: "Fecha_Creacion",
        render: (d) => new Date(d).toLocaleDateString("es-PE"),
      },
      {
        data: null,
        render: (r) => `
          <div class="usuarios-btn-group">
              <button class="usuarios-btn-accion usuarios-btn-editar" 
                onclick="editarUsuario(${r.Id_Usuarios})" 
                title="Editar"><i class="material-icons">edit</i></button>
              <button class="usuarios-btn-accion usuarios-btn-resetear" 
                onclick="cambiarPasswordUsuario(${r.Id_Usuarios})" 
                title="Cambiar Contraseña"><i class="material-icons">lock_reset</i></button>
              <button class="usuarios-btn-accion usuarios-btn-toggle ${
                r.Activo ? "" : "activar"
              }" 
                onclick="toggleUsuario(${r.Id_Usuarios}, ${r.Activo})" 
                title="${r.Activo ? "Desactivar" : "Activar"}">
                <i class="material-icons">${
                  r.Activo ? "person_off" : "person_add"
                }</i></button>
          </div>`,
      },
    ],
    order: [[6, "desc"]],
    responsive: true,
  });

  // Cargar roles al abrir modal de crear usuario
  $("#modalCrearUsuario").on("show.bs.modal", function () {
    cargarRoles("#id_roles");
  });

  // Resetear formulario al cerrar el modal de crear usuario
  $("#modalCrearUsuario").on("hidden.bs.modal", function () {
    $("#formCrearUsuario")[0].reset(); // Limpia todos los campos
    $("#formCrearUsuario .is-invalid").removeClass("is-invalid"); // Limpia validaciones
    $("#usuario").val(""); // Limpia usuario autogenerado
  });

  // Buscar RENIEC al escribir DNI
  $("#dni").on("keyup", function () {
    if ($(this).val().length === 8) buscarReniec($(this).val());
  });

  $("#btnBuscarReniec").click(() => {
    const dni = $("#dni").val();
    dni.length === 8
      ? buscarReniec(dni)
      : mostrarAlertaModal(
          "#modalCrearUsuario",
          "Ingrese un DNI válido",
          "warning"
        );
  });

  // Guardar nuevo usuario
  $("#btnGuardarUsuario").click(() => {
    if (validarFormulario("#formCrearUsuario")) guardarUsuario();
  });

  // Actualizar usuario
  $("#btnActualizarUsuario").click(() => {
    if (validarFormulario("#formEditarUsuario")) actualizarUsuario();
  });

  // Guardar nueva contraseña
  $("#btnGuardarPassword").click(() => {
    const nueva = $("#nueva_password").val();
    const confirmar = $("#confirmar_password").val();
    if (!nueva || !confirmar)
      return mostrarAlertaModal(
        "#modalPasswordUsuario",
        "Complete todos los campos",
        "warning"
      );
    if (nueva !== confirmar)
      return mostrarAlertaModal(
        "#modalPasswordUsuario",
        "Las contraseñas no coinciden",
        "danger"
      );

    fetchPost("password", {
      id_usuarios: $("#pass_id_usuarios").val(),
      nueva,
    }).then(() => {
      $("#modalPasswordUsuario").modal("hide");
      recargarTabla();
    });
  });
});

/* === FUNCIONES AJAX === */
function fetchGet(action) {
  return fetch(`${API}?action=${action}`)
    .then((r) => r.json())
    .catch((err) => {
      console.error("Error en fetchGet:", err);
      mostrarAlertaInterna("Error de conexión con el servidor", "danger");
    });
}

function fetchPost(action, data) {
  return fetch(`${API}?action=${action}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status === "success")
        mostrarAlertaInterna(res.message, "success");
      else mostrarAlertaInterna(res.message, "danger");
      return res;
    })
    .catch((err) => {
      console.error("Error en fetchPost:", err);
      mostrarAlertaInterna("Error de conexión con el servidor", "danger");
    });
}

function recargarTabla() {
  $("#tablaUsuarios").DataTable().ajax.reload(null, false);
}

/* === CRUD === */
function guardarUsuario() {
  const data = {
    dni: $("#dni").val(),
    nombre: $("#nombre").val(),
    apellido_paterno: $("#apellido_paterno").val(),
    apellido_materno: $("#apellido_materno").val(),
    id_roles: $("#id_roles").val(),
    usuario: $("#usuario").val(),
    password: $("#password").val(),
  };
  fetchPost("crear", data).then(() => {
    $("#modalCrearUsuario").modal("hide");
    recargarTabla();
  });
}

function editarUsuario(id) {
  fetchGet(`obtener&id=${id}`).then((res) => {
    if (res.status === "success") {
      const u = res.data;
      $("#edit_id_usuarios").val(u.Id_Usuarios);
      $("#edit_dni").val(u.Dni);
      $("#edit_nombre").val(u.Nombre);
      $("#edit_apellido_paterno").val(u.Apellido_Paterno);
      $("#edit_apellido_materno").val(u.Apellido_Materno);
      cargarRoles("#edit_id_roles", u.Id_Roles);
      $("#edit_activo").val(u.Activo);
      $("#modalEditarUsuario").modal("show");
    }
  });
}

function actualizarUsuario() {
  const data = {
    id_usuarios: $("#edit_id_usuarios").val(),
    id_roles: $("#edit_id_roles").val(),
    activo: $("#edit_activo").val(),
  };
  fetchPost("editar", data).then(() => {
    $("#modalEditarUsuario").modal("hide");
    recargarTabla();
  });
}

function cambiarPasswordUsuario(id) {
  $("#pass_id_usuarios").val(id);
  $("#formPasswordUsuario")[0].reset();
  $("#modalPasswordUsuario").modal("show");
}

function toggleUsuario(id, estado) {
  if (confirm(`¿Desea ${estado ? "desactivar" : "activar"} este usuario?`)) {
    fetchPost("toggle", { id_usuarios: id, estado: estado ? 0 : 1 }).then(() =>
      recargarTabla()
    );
  }
}

/* === UTILITARIOS === */
function cargarRoles(selector, seleccionado = "") {
  fetchGet("roles").then((res) => {
    if (res.status === "success") {
      const roles = res.data;
      $(selector).html('<option value="">Seleccionar rol...</option>');
      roles.forEach((r) => {
        $(selector).append(
          `<option value="${r.Id_Roles}" ${
            seleccionado == r.Id_Roles ? "selected" : ""
          }>${r.Nombre}</option>`
        );
      });
    }
  });
}

function buscarReniec(dni) {
  const btn = $("#btnBuscarReniec");
  btn.html('<div class="usuarios-loading-spinner"></div>');
  fetch("/sisti/backend/php/api/reniec-dni.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ numdni: dni }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.status === "success") {
        $("#nombre").val(data.prenombres);
        $("#apellido_paterno").val(data.apPrimer);
        $("#apellido_materno").val(data.apSegundo);
        $("#usuario").val(
          (data.prenombres.charAt(0) + data.apPrimer).toLowerCase()
        );
        mostrarAlertaModal(
          "#modalCrearUsuario",
          "Datos RENIEC cargados",
          "success"
        );
      } else {
        mostrarAlertaModal("#modalCrearUsuario", data.message, "warning");
      }
      btn.html('<i class="material-icons">search</i>');
    });
}

function validarFormulario(selector) {
  let valido = true;
  $(`${selector} [required]`).each(function () {
    if (!$(this).val()) {
      $(this).addClass("is-invalid");
      valido = false;
    } else $(this).removeClass("is-invalid");
  });
  return valido;
}

/* === ALERTAS === */
function mostrarAlertaInterna(msg, tipo) {
  const cont = $(".container-fluid.p-4");
  cont.find(".usuarios-alert").remove();
  cont.prepend(`<div class="alert alert-${tipo} usuarios-alert">${msg}</div>`);
  setTimeout(() => cont.find(".usuarios-alert").fadeOut(), 4000);
}

function mostrarAlertaModal(modal, msg, tipo) {
  $(`${modal} .usuarios-alert`).remove();
  $(`${modal} .modal-body`).prepend(
    `<div class="alert alert-${tipo} usuarios-alert">${msg}</div>`
  );
  setTimeout(() => $(`${modal} .usuarios-alert`).fadeOut(), 4000);
}
