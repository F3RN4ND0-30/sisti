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
    const aceptar = document.getElementById('aceptar');

    async function buscarDNI(dni) {
        if (dni.length !== 8) return;

        if (loader) loader.style.display = 'flex';

        try {
            const response = await fetch('/sisti/backend/php/api/reniec-dni.php', {
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
            if (loader) loader.style.display = 'none';
        }
    }

    if (dniInput) {
        dniInput.addEventListener('input', function () {
            const dni = dniInput.value.trim();
            if (dni.length === 8) {
                buscarDNI(dni);
            } else {
                nombreInput.value = '';
                apPaternoInput.value = '';
                apMaternoInput.value = '';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch('/sisti/backend/php/tickets/crear_ticket.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
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

    if (cerrarModal) {
        cerrarModal.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    if (aceptar) {
        aceptar.addEventListener('click', function () {
            window.location.href = '/sisti/frontend/sisvis/escritorio.php';
        });
    }

    if (modal) {
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});
