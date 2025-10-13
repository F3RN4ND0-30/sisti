document.addEventListener('DOMContentLoaded', function () {
    const dniInput = document.getElementById('dni');
    const nombreInput = document.getElementById('nombre');
    const apPaternoInput = document.getElementById('apPaterno');
    const apMaternoInput = document.getElementById('apMaterno');
    const loader = document.getElementById('dni-loader');
    const form = document.getElementById('formTicket');
    const modal = document.getElementById('modalTicket');
    const mensajeModal = document.getElementById('mensajeModal');
    const irInicio = document.getElementById('irInicio');
    const aceptar = document.getElementById('aceptar');
    const copiarTicket = document.getElementById('copiarTicket');
    const checkExtranjero = document.getElementById('checkExtranjero');

    async function buscarCedulaExtranjero(cedula) {
        if (!cedula || cedula.length < 5) return;

        if (loader) loader.style.display = 'flex';

        try {
            const response = await fetch('/sisti/backend/php/api/buscar-cedula-extranjero.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ cedula })
            });

            const data = await response.json();

            if (data.success) {
                nombreInput.value = data.nombre;
                apPaternoInput.value = data.apPaterno;
                apMaternoInput.value = data.apMaterno;
            } else {
                alert('❌ Cédula no registrada. Por favor acérquese a la oficina de sistemas.');
                nombreInput.value = '';
                apPaternoInput.value = '';
                apMaternoInput.value = '';
            }
        } catch (error) {
            console.error('Error al consultar la cédula:', error);
            alert('❌ Error al consultar la cédula. Intente nuevamente.');
        } finally {
            if (loader) loader.style.display = 'none';
        }
    }

    async function buscarDNI(dni) {
        // Salir si está marcado como extranjero
        if (checkExtranjero.checked) return;

        if (dni.length !== 8) {
            alert('El DNI debe tener 8 dígitos.');
            return;
        }

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
            let valor = dniInput.value.trim();

            // Limpiar campos al editar
            nombreInput.value = '';
            apPaternoInput.value = '';
            apMaternoInput.value = '';

            if (checkExtranjero.checked) {
                // Extranjero: permitir hasta 15 caracteres alfanuméricos
                dniInput.maxLength = 15;
                dniInput.value = valor.replace(/[^a-zA-Z0-9]/g, '');

                valor = dniInput.value;
                if (valor.length >= 10) {
                    buscarCedulaExtranjero(valor);
                }
            } else {
                // Nacional: permitir solo 8 dígitos
                dniInput.maxLength = 8;
                dniInput.value = valor.replace(/\D/g, '');

                valor = dniInput.value;
                if (valor.length === 8) {
                    buscarDNI(valor);
                }
            }
        });
    }

    if (checkExtranjero) {
        checkExtranjero.addEventListener('change', function () {
            dniInput.value = '';
            nombreInput.value = '';
            apPaternoInput.value = '';
            apMaternoInput.value = '';
            dniInput.dispatchEvent(new Event('input'));
        });
    }

    let ultimaSolicitud = 0;

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const ahora = Date.now();
            const tiempoTranscurrido = ahora - ultimaSolicitud;

            if (tiempoTranscurrido < 5000) {
                alert('⚠️ Por favor espera unos segundos antes de enviar nuevamente.');
                return;
            }

            const dniValor = dniInput.value.trim();

            // Validación adicional solo si NO es extranjero
            if (!checkExtranjero.checked && dniValor.length !== 8) {
                alert('El DNI debe tener exactamente 8 dígitos.');
                return;
            }

            // Validación adicional si ES extranjero
            if (checkExtranjero.checked && dniValor.length < 10) {
                alert('La cédula del extranjero debe tener al menos 10 caracteres.');
                return;
            }

            ultimaSolicitud = ahora;

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

    if (copiarTicket) {
        copiarTicket.addEventListener('click', function () {
            const texto = mensajeModal.textContent.trim();
            if (!texto) {
                alert('⚠️ No hay ticket para copiar.');
                return;
            }

            const ticket = texto.replace(/^Código:\s*/, '');

            navigator.clipboard.writeText(ticket).then(() => {
                copiarTicket.textContent = '✔️ Copiado';
                setTimeout(() => {
                    copiarTicket.textContent = 'Copiar Ticket';
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar al portapapeles:', err);
                alert('❌ No se pudo copiar el ticket.');
            });
        });
    }

    if (irInicio) {
        irInicio.addEventListener('click', function () {
            window.location.href = '/sisti/';
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
                window.location.href = '/sisti/';
            }
        });
    }
});
