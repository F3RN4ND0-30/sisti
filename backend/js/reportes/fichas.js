document.addEventListener('DOMContentLoaded', () => {
    fetch('/sisti/backend/php/reportes/listar_fichas.php')
        .then(response => response.json())
        .then(data => {
            const tabla = $('#tabla-fichas');
            const tbody = tabla.find('tbody');

            if (data.length === 0) {
                tbody.html('<tr><td colspan="4">No hay fichas registradas.</td></tr>');
                return;
            }

            data.forEach(ficha => {
                const fechaSinMs = ficha.Fecha ? ficha.Fecha.split('.')[0] : 'Sin fecha';
                const fila = `
                    <tr>
                        <td data-label="ID Ficha">${ficha.Id_Ficha}</td>
                        <td data-label="Número">${ficha.Numero.toString().padStart(6, '0')}</td>
                        <td data-label="Nombre del Usuario">${ficha.Nombre} ${ficha.Apellido_Paterno} ${ficha.Apellido_Materno}</td>
                        <td data-label="Fecha">${fechaSinMs}</td>
                    </tr>
                `;
                tbody.append(fila);
            });

            // Solo inicializa DataTable si aún no lo está
            if (!$.fn.DataTable.isDataTable('#tabla-fichas')) {
                tabla.DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    responsive: true,
                    order: [[0, 'desc']],
                    pagingType: "simple_numbers"
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar las fichas:', error);
        });
});