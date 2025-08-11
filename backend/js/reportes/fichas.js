document.addEventListener('DOMContentLoaded', () => {
    fetch('/sisti/backend/php/reportes/listar_fichas.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tabla-fichas tbody');
            if (!tbody) {
                console.error('No se encontró el tbody de la tabla');
                return;
            }

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">No hay fichas registradas.</td></tr>';
                return;
            }

            data.forEach(ficha => {
                let fechaSinMs = ficha.Fecha.split('.')[0];
                const fila = document.createElement('tr');

                fila.innerHTML = `
                    <td data-label="ID Ficha">${ficha.Id_Ficha}</td>
                    <td data-label="Número">${ficha.Numero.toString().padStart(6, '0')}</td>
                    <td data-label="Nombre del Usuario">${ficha.Nombre} ${ficha.Apellido_Paterno} ${ficha.Apellido_Materno}</td>
                    <td data-label="Fecha">${fechaSinMs}</td>
                `;

                tbody.appendChild(fila);
            });

            $('#tabla-fichas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true,
                order: [[0, 'desc']]
            });
        })
        .catch(error => {
            console.error('Error al cargar las fichas:', error);
        });
});
