document.addEventListener('DOMContentLoaded', function () {
    // Inicializa la tabla con DataTables
    const tabla = $('#tablaExtranjeros').DataTable({
        ajax: '/sisti/backend/php/admin/listar_extranjeros.php',
        columns: [
            { data: 'id' },
            { data: 'cedula' },
            { data: 'nombres' },
            { data: 'ap_paterno' },
            { data: 'ap_materno' },
            {
                data: 'estado',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-secondary">Inactivo</span>';
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });

    // Referencia al formulario
    const form = document.getElementById('formExtranjeros');

    // Manejo del envío del formulario
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch('/sisti/backend/php/admin/crear_extranjeros.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('✅ Registro exitoso.');
                form.reset();

                // Cerrar el modal correctamente
                const modalElement = document.getElementById('modalCrearExtranjeros');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                modalInstance.hide();

                // Recargar la tabla sin reiniciar la paginación
                tabla.ajax.reload(null, false);
            } else {
                alert('❌ Error: ' + result.error);
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            alert('❌ Error inesperado al enviar el formulario.');
        }
    });
});
