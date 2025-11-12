document.addEventListener('DOMContentLoaded', function () {
    const tabla = $('#tablaDesactivar').DataTable({
        ajax: '/sisti/backend/php/admin/listar_tickets.php',
        columns: [
            { data: 'Id_Incidentes' },
            { data: 'Id_Tickets' },
            { data: 'Descripcion' },
            { data: 'Fecha_Creacion' },
            {
                data: 'EstadoIncidente',
                render: function (data, type, row) {
                    const estado = data == 1 ? 'Activo' : 'Inactivo';
                    const color = data == 1 ? 'success' : 'secondary';
                    return `
                        <button class="btn btn-${color} btn-sm toggle-estado" 
                                data-id="${row.Id_Incidentes}" 
                                data-estado="${data}">
                            ${estado}
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });

    // Detectar clic en el botón de estado
    $('#tablaDesactivar').on('click', '.toggle-estado', function () {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado');
        const nuevoEstado = estadoActual == 1 ? 0 : 1;

        // Confirmación
        if (!confirm('¿Deseas cambiar el estado de este ticket?')) return;

        $.ajax({
            url: '/sisti/backend/php/admin/cambiar_estado.php',
            method: 'POST',
            data: { id: id, estado: nuevoEstado },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    tabla.ajax.reload(null, false); // recarga sin resetear la página
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function () {
                alert('Error al conectar con el servidor.');
            }
        });
    });
});
