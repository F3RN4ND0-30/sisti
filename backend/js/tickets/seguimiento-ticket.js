document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formBuscarTicket');
    const input = document.getElementById('ticketInput');
    const resultadoDiv = document.getElementById('resultado');

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

            const t = data.ticket || data.data;

            console.log(t);  // <-- Aquí ves las propiedades reales del objeto

            // Extrae el estado numérico correcto
            const estado = parseInt(t.Id_Estados_Incidente || t.estado) || 1; // default a 1 si no viene

            const pasos = ['En espera', 'En atención', 'Concluido'];

            let html = `
    <div class="ticket-info">
        <div><label>Ticket:</label> <span>${t.numero_ticket}</span></div>
        <div><label>Solicitante:</label> <span>${t.nombre} ${t.apellido}</span></div>
        <div><label>DNI:</label> <span>${t.dni}</span></div>
        <div><label>Área:</label> <span>${t.area}</span></div>
        <div style="grid-column: span 2;"><label>Descripción:</label> <span>${t.descripcion}</span></div>
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

    function agregarRedireccion(idBoton, urlDestino) {
        const btn = document.getElementById(idBoton);
        if (btn) {
            btn.addEventListener('click', () => {
                window.location.href = urlDestino;
            });
        }
    }

    agregarRedireccion('btnRegresar', '/sisti/');
    agregarRedireccion('btnRegresarFrontend', '/sisti/frontend/sisvis/escritorio.php');

});
