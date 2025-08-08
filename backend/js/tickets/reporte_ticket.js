$(document).ready(function () {
    const tabla = $('#tablaTickets').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        order: [[6, 'desc']]
    });

    function toggleSelector() {
        const tipo = $('#filtroPeriodo').val();
        $('#selectorDia').toggle(tipo === 'dia');
        $('#selectorSemana').toggle(tipo === 'semana');
        $('#selectorMes').toggle(tipo === 'mes');
        $('#selectorAnio').toggle(tipo === 'anio');
    }

    toggleSelector();

    $(document).on('change', '#filtroPeriodo, #diaSemana, #mesFiltro, #mesSemana, #semanaDelMes', function () {
        toggleSelector();
        cargarDatos();
    });

    function getEstadoHTML(estadoTexto) {
        const estado = estadoTexto.trim().toLowerCase();
        if (estado.includes('pendiente')) {
            return `<span class="estado-tag estado-pendiente">${estadoTexto}</span>`;
        } else if (estado.includes('proceso')) {
            return `<span class="estado-tag estado-proceso">${estadoTexto}</span>`;
        } else if (estado.includes('resuelto') || estado.includes('cerrado') || estado.includes('finalizado')) {
            return `<span class="estado-tag estado-resuelto">${estadoTexto}</span>`;
        }
        return `<span class="estado-tag">${estadoTexto}</span>`;
    }

    function cargarDatos() {
        const tipo = $('#filtroPeriodo').val();
        let datos = { tipo };

        if (tipo === 'dia') {
            const diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            const hoy = new Date();
            const hoyDia = hoy.getDay();
            const seleccionado = $('#diaSemana').val().toLowerCase();
            const diaSeleccionado = diasSemana.indexOf(seleccionado);
            const diferenciaDias = diaSeleccionado - hoyDia;
            const fechaDia = new Date(hoy);
            fechaDia.setDate(hoy.getDate() + diferenciaDias);
            datos.fecha = fechaDia.toISOString().split('T')[0];
        } else if (tipo === 'semana') {
            const mesSeleccionado = $('#mesSemana').val();
            const semanaNum = parseInt($('#semanaDelMes').val());
            if (!mesSeleccionado || !semanaNum) {
                alert('Seleccione mes y número de semana.');
                return;
            }
            datos.fecha = mesSeleccionado;
            datos.semana = semanaNum;
        } else if (tipo === 'mes') {
            datos.fecha = $('#mesFiltro').val();
        } else if (tipo === 'anio') {
            datos.fecha = $('#anioFiltro').val();
        } else {
            datos.fecha = new Date().toISOString().slice(0, 10);
        }

        $.ajax({
            url: '/sisti/backend/php/api/api_reporte.php',
            method: 'GET',
            data: datos,
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    alert('Error: ' + (res.error || 'Respuesta inválida'));
                    return;
                }

                $('#ticketsAtendidos strong').text(res.total_tickets);
                tabla.clear();

                const procesarTicket = (ticket) => {
                    tabla.row.add([
                        ticket.numero_ticket,
                        `${ticket.nombre} ${ticket.apellido}`,
                        ticket.dni,
                        ticket.area,
                        ticket.descripcion,
                        getEstadoHTML(ticket.estado_texto),
                        ticket.fecha_creacion,
                        ticket.fecha_resuelto || '—'
                    ]);
                };

                if (tipo === 'semana') {
                    res.detalle.forEach(d => d.tickets.forEach(procesarTicket));
                } else {
                    res.detalle.forEach(procesarTicket);
                }

                $.ajax({
                    url: '/sisti/backend/php/api/api_reporte.php',
                    method: 'GET',
                    data: datos,
                    dataType: 'json',
                    success: function (res) {
                        if (!res.success) {
                            alert('Error: ' + (res.error || 'Respuesta inválida'));
                            return;
                        }

                        $('#ticketsAtendidos strong').text(res.total_tickets);
                        tabla.clear();

                        const procesarTicket = (ticket) => {
                            tabla.row.add([
                                ticket.numero_ticket,
                                `${ticket.nombre} ${ticket.apellido}`,
                                ticket.dni,
                                ticket.area,
                                ticket.descripcion,
                                getEstadoHTML(ticket.estado_texto),
                                ticket.fecha_creacion,
                                ticket.fecha_resuelto ?? ''
                            ]);
                        };

                        if (tipo === 'semana') {
                            res.detalle.forEach(d => d.tickets.forEach(procesarTicket));
                        } else {
                            res.detalle.forEach(procesarTicket);
                        }

                        tabla.draw();
                    },
                    error: function () {
                        alert('Error al cargar los datos del reporte.');
                    }
                });
            }
        });
    }

    function exportarExcel() {
        const tipo = document.getElementById('filtroPeriodo').value;
        let url = '/sisti/backend/php/reportes/exportar_excel_reporte_tickets.php?tipo=' + tipo;

        if (tipo === 'dia') {
            const dia = document.getElementById('diaSemana').value;
            const hoy = new Date();
            const diaIndex = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'].indexOf(dia.toLowerCase());
            const diferencia = diaIndex - hoy.getDay();
            hoy.setDate(hoy.getDate() + diferencia);
            const fecha = hoy.toISOString().split('T')[0];
            url += '&fecha=' + fecha;
        } else if (tipo === 'semana') {
            const mes = document.getElementById('mesSemana').value;
            const semana = document.getElementById('semanaDelMes').value;
            url += '&fecha=' + mes + '&semana=' + semana;
        } else if (tipo === 'mes') {
            const mes = document.getElementById('mesFiltro').value;
            url += '&fecha=' + mes;
        } else if (tipo === 'anio') {
            const anio = document.getElementById('anioFiltro').value;
            url += '&fecha=' + anio;
        }

        window.open(url, '_blank');
    }

    $('#btnExportarExcel').on('click', exportarExcel);

    function exportarPDF() {
        const tipo = document.getElementById('filtroPeriodo').value;
        let url = '/sisti/backend/php/reportes/exportar_pdf_reporte_tickets.php?tipo=' + tipo;

        if (tipo === 'dia') {
            const dia = document.getElementById('diaSemana').value;
            const hoy = new Date();
            const diaIndex = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'].indexOf(dia.toLowerCase());
            const diferencia = diaIndex - hoy.getDay();
            hoy.setDate(hoy.getDate() + diferencia);
            const fecha = hoy.toISOString().split('T')[0];
            url += '&fecha=' + fecha;
        } else if (tipo === 'semana') {
            const mes = document.getElementById('mesSemana').value;
            const semana = document.getElementById('semanaDelMes').value;
            url += '&fecha=' + mes + '&semana=' + semana;
        } else if (tipo === 'mes') {
            const mes = document.getElementById('mesFiltro').value;
            url += '&fecha=' + mes;
        } else if (tipo === 'anio') {
            const anio = document.getElementById('anioFiltro').value;
            url += '&fecha=' + anio;
        }

        window.open(url, '_blank');
    }

    $('#btnExportarPDF').on('click', exportarPDF);

    cargarDatos();
});