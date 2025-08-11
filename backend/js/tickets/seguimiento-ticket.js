document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formBuscarTicket');
    const input = document.getElementById('ticketInput');
    const resultadoDiv = document.getElementById('resultado');

    // Obtener fecha en zona horaria Lima
    const ahoraLima = new Date().toLocaleString('en-US', { timeZone: 'America/Lima' });
    const fechaLima = new Date(ahoraLima);

    const yyyy = fechaLima.getFullYear();
    const mm = String(fechaLima.getMonth() + 1).padStart(2, '0');
    const dd = String(fechaLima.getDate()).padStart(2, '0');
    const prefijoTicket = `TCK-${yyyy}${mm}${dd}-`;

    if (input && !input.value) {
        input.value = prefijoTicket;
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }

    input.addEventListener('input', () => {
        const valor = input.value.trim();
        const regex = new RegExp(`^${prefijoTicket}\\d{4}$`); // TCK-YYYYMMDD-1234

        if (regex.test(valor)) {
            form.requestSubmit(); // Enviar formulario automáticamente
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const ticket = input.value.trim();

        if (!ticket) {
            alert('Por favor ingresa un número de ticket');
            return;
        }

        resultadoDiv.innerHTML = 'Cargando...';

        try {
            const res = await fetch(`/sisti/backend/php/api/seguimiento_ticket.php?ticket=${encodeURIComponent(ticket)}`);
            const data = await res.json();

            if (!data.success) {
                resultadoDiv.innerHTML = `<p class="no-found">${data.error}</p>`;
                return;
            }

            // Usamos el primer incidente del arreglo
            const incidente = data.incidentes[0];

            if (!incidente) {
                resultadoDiv.innerHTML = `<p class="no-found">No hay incidentes para este ticket.</p>`;
                return;
            }

            const estado = parseInt(incidente.id_estado_incidente) || 1; // default 1
            const pasos = ['En espera', 'En atención', 'Concluido'];

            let html = `
<div class="ticket-info">
    <div><label>Ticket:</label> <span>${data.ticket.numero_ticket}</span></div>
    <div><label>Solicitante:</label> <span>${incidente.nombre} ${incidente.apellido}</span></div>
    <div><label>DNI:</label> <span>${incidente.dni}</span></div>
    <div><label>Área:</label> <span>${incidente.area}</span></div>
    <div style="grid-column: span 2;"><label>Descripción:</label> <span>${incidente.descripcion}</span></div>
    <div><label>Fecha de creación:</label> <span>${formatearFecha(incidente.fecha_creacion)}</span></div>
    <div><label>Fecha de resolución:</label> <span>${incidente.fecha_resuelto ? formatearFecha(incidente.fecha_resuelto) : 'Aún no resuelto'}</span></div>
</div>
<div class="estado-container">
`;

            for (let i = 1; i <= 3; i++) {
                const clase = i <= estado ? 'completo' : '';
                html += `
                    <div class="estado-paso ${clase}">
                        <div class="circle">${i}</div>
                        <div class="label">${pasos[i - 1]}</div>
                    </div>
                `;
            }

            html += '</div>';
            resultadoDiv.innerHTML = html;

        } catch (error) {
            resultadoDiv.innerHTML = `<p class="no-found">Error al consultar el ticket.</p>`;
            console.error(error);
        }
    });

    function formatearFecha(fechaString) {
        if (!fechaString) return '';
        const fecha = new Date(fechaString);
        return fecha.toLocaleString('es-PE', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function agregarRedireccion(idBoton, urlDestino) {
        const btn = document.getElementById(idBoton);
        if (btn) {
            btn.addEventListener('click', () => {
                window.location.href = urlDestino;
            });
        }
    }

    // Botón Pegar
    const btnPegar = document.getElementById('btnPegar');
    if (btnPegar) {
        btnPegar.addEventListener('click', async () => {
            try {
                const texto = await navigator.clipboard.readText();
                if (!texto) {
                    alert('No hay texto en el portapapeles');
                    return;
                }
                input.value = texto.trim();

                // Opcional: dispara la búsqueda automáticamente
                form.dispatchEvent(new Event('submit'));
            } catch (err) {
                alert('Error al leer el portapapeles');
                console.error(err);
            }
        });
    }

    agregarRedireccion('btnRegresar', '/sisti/');
    agregarRedireccion('btnRegresarFrontend', '/sisti/frontend/sisvis/escritorio.php');

});
