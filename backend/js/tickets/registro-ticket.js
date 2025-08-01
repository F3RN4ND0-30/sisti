document.addEventListener('DOMContentLoaded', function () {
    const dniInput = document.getElementById('dni');
    const nombreInput = document.getElementById('nombre');
    const apPaternoInput = document.getElementById('apPaterno');
    const apMaternoInput = document.getElementById('apMaterno');
    const loader = document.getElementById('dni-loader');
    const form = document.getElementById('formTicket');
    const modal = document.getElementById('modalTicket');
    const mensajeModal = document.getElementById('mensajeModal');
    const cerrarModal = document.getElementById('cerrarModal');
    const irInicioBtn = document.getElementById('irInicio');

    // Función para buscar DNI
    async function buscarDNI(dni) {
        if (dni.length !== 8) return;

        loader.style.display = 'flex';

        try {
            const response = await fetch('../../backend/php/api/reniec-dni.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numdni: dni })
            });

            const data = await response.json();

            if (data.status === 'success') {
                nombreInput.value = data.prenombres;
                apPaternoInput.value = data.apPrimer;
                apMaternoInput.value = data.apSegundo;
            } else {
                nombreInput.value = '';
                apPaternoInput.value = '';
                apMaternoInput.value = '';
                alert('No se encontró información para el DNI ingresado.');
            }
        } catch (error) {
            console.error('Error al consultar el DNI:', error);
            alert('Error al consultar el DNI. Intente nuevamente.');
        } finally {
            loader.style.display = 'none';
        }
    }

    // Evento para que al escribir el DNI se lance la búsqueda automáticamente cuando tenga 8 dígitos
    dniInput.addEventListener('input', function () {
        const dni = dniInput.value.trim();
        if (dni.length === 8) {
            buscarDNI(dni);
        } else {
            // limpiar si no tiene 8 dígitos
            nombreInput.value = '';
            apPaternoInput.value = '';
            apMaternoInput.value = '';
        }
    });

    // Envío de formulario con modal en vez de alert
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch('../../backend/php/tickets/crear_ticket.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Mostrar modal con el código del ticket
                    mensajeModal.textContent = 'Código: ' + result.ticket;
                    modal.style.display = 'flex';
                } else {
                    alert('❌ Error al crear ticket: ' + result.error);
                }
            } catch (error) {
                console.error('Error en el envío del formulario:', error);
                alert('❌ Error inesperado al registrar el ticket.');
            }
        });
    }

    // Cerrar modal al hacer click en la X
    cerrarModal.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Botón "Ir al Inicio" redirige a la raíz del proyecto
    irInicioBtn.addEventListener('click', function () {
        window.location.href = '/sisti/';  // Cambia según tu ruta base real
    });

    // Cerrar modal si el usuario hace click fuera del contenido
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});