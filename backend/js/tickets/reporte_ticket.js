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
                } else {
                    datos.fecha = new Date().toISOString().slice(0, 10);
                }

                $.ajax({
                    url: '/helpdesk_mpp2.0/backend/php/api/api_reporte.php',
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
                                ticket.fecha_creacion
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

            cargarDatos();
        });