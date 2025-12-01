// public/assets/js/lotes.js
if (window.lotesModuleInitialized) {
    console.log('Módulo Lotes ya inicializado, no se vuelve a ejecutar.');
} else {
    window.lotesModuleInitialized = true;

    $(function () {

        if (!$('#lotesContainer').length) return;

        console.log('Inicializando módulo Lotes');

        let currentProyectoId = null;
        let currentEtapaId    = null;
        let currentManzanaId  = null;

        const $filtroProyecto = $('#filtroProyecto');
        const $filtroEtapa    = $('#filtroEtapa');
        const $filtroManzana  = $('#filtroManzana');
        const $lotesContainer = $('#lotesContainer');

        const $modalLote         = $('#modalLote');
        const $modalGenerarLotes = $('#modalGenerarLotes');

        const modalLote         = $modalLote.length ? new bootstrap.Modal($modalLote[0]) : null;
        const modalGenerarLotes = $modalGenerarLotes.length ? new bootstrap.Modal($modalGenerarLotes[0]) : null;

        /* ==============
         * Helpers
         * ============*/

        function loadEtapas() {
            const idp = $filtroProyecto.val();
            $filtroEtapa.empty().append('<option value="">-- Todas --</option>');
            $filtroManzana.empty().append('<option value="">-- Todas --</option>').prop('disabled', true);

            if (!idp) {
                $filtroEtapa.prop('disabled', true);
                return;
            }

            $.get('index.php?c=lotes&a=etapas_by_proyecto&id_proyecto=' + idp, function (resp) {
                let data;
                try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch (e) { console.error('JSON inválido etapas', resp); return; }

                data.forEach(e => {
                    $filtroEtapa.append(
                        `<option value="${e.id}">${e.nombre} (N° ${e.numero})</option>`
                    );
                });

                $filtroEtapa.prop('disabled', false);
            });
        }

        function loadManzanas() {
            const idp = $filtroProyecto.val();
            const ide = $filtroEtapa.val();

            $filtroManzana.empty().append('<option value="">-- Todas --</option>');

            if (!idp || !ide) {
                $filtroManzana.prop('disabled', true);
                return;
            }

            $.get('index.php?c=lotes&a=manzanas_by_etapa&id_proyecto=' + idp + '&id_etapa=' + ide, function (resp) {
                let data;
                try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch (e) { console.error('JSON inválido manzanas', resp); return; }

                data.forEach(m => {
                    $filtroManzana.append(
                        `<option value="${m.id}">${m.codigo}${m.descripcion ? ' - ' + m.descripcion : ''}</option>`
                    );
                });

                $filtroManzana.prop('disabled', false);
            });
        }

        function badgeEstado(estado) {
            if (estado === 'DESHABILITADO') {
                return '<span class="badge bg-secondary">Deshabilitado</span>';
            }
            return '<span class="badge bg-success">Habilitado</span>';
        }

        function cardColor(estado) {
            return (estado === 'DESHABILITADO') ? 'border-secondary' : 'border-success';
        }

        function loadLotes() {
            currentProyectoId = $filtroProyecto.val() || null;
            currentEtapaId    = $filtroEtapa.val() || null;
            currentManzanaId  = $filtroManzana.val() || null;

            if (!currentProyectoId) {
                $lotesContainer.html(
                    '<div class="col-12"><div class="alert alert-info py-2 mb-0">Seleccione un proyecto para ver sus lotes.</div></div>'
                );
                return;
            }

            const params = $.param({
                id_proyecto: currentProyectoId,
                id_etapa: currentEtapaId || '',
                id_manzana: currentManzanaId || ''
            });

            $.get('index.php?c=lotes&a=list&' + params, function (resp) {
                let lotes;
                try { lotes = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch (e) {
                    console.error('JSON inválido lotes', resp);
                    $lotesContainer.html('<div class="col-12">Error cargando lotes</div>');
                    return;
                }

                if (!lotes.length) {
                    $lotesContainer.html(
                        '<div class="col-12"><div class="alert alert-warning py-2 mb-0">No hay lotes para los filtros seleccionados.</div></div>'
                    );
                    return;
                }

                const html = lotes.map(l => {
                    const clsBorder = cardColor(l.estado_comercial);
                    const badge = badgeEstado(l.estado_comercial);
                    const titulo = `Mz ${l.manzana_codigo || ''} - Lote ${l.numero}`;
                    const area   = l.area_m2 ? (l.area_m2 + ' m²') : 'Área no definida';

                    return `
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                          <div class="card ${clsBorder} border-2 h-100">
                            <div class="card-body py-2 px-3">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 small fw-bold">${titulo}</h6>
                                ${badge}
                              </div>
                              <div class="small text-muted mb-1">
                                Etapa: ${l.etapa_nombre || '-'}
                              </div>
                              <div class="small">
                                Área: <span class="fw-semibold">${area}</span>
                              </div>
                            </div>
                            <div class="card-footer py-1 px-2 d-flex justify-content-between">
                              <button class="btn btn-sm btn-outline-primary btn-edit-lote" data-id="${l.id}">
                                <i class="bi bi-pencil-square"></i>
                              </button>
                              <button class="btn btn-sm btn-outline-danger btn-del-lote" data-id="${l.id}">
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                    `;
                }).join('');

                $lotesContainer.html(html);
            });
        }

        /* ==============
         * Eventos filtros
         * ============*/

        $filtroProyecto.on('change', function () {
            loadEtapas();
            $filtroManzana.prop('disabled', true);
            loadLotes();
        });

        $filtroEtapa.on('change', function () {
            loadManzanas();
            loadLotes();
        });

        $filtroManzana.on('change', function () {
            loadLotes();
        });

        $('#btnFiltrarLotes').on('click', function () {
            loadLotes();
        });

        /* ==============
         * Nuevo Lote
         * ============*/

        $('#btnNewLote').on('click', function () {
            const idp = $filtroProyecto.val();
            const ide = $filtroEtapa.val();
            const idm = $filtroManzana.val();

            if (!idp || !ide || !idm) {
                Swal.fire('Atención', 'Debe seleccionar Proyecto, Etapa y Manzana.', 'info');
                return;
            }

            $('#formLote')[0].reset();
            $('#lote_id').val('');
            $('#lote_id_proyecto').val(idp);
            $('#lote_id_etapa').val(ide);
            $('#lote_id_manzana').val(idm);

            const txtProyecto = $filtroProyecto.find('option:selected').text();
            const txtEtapa    = $filtroEtapa.find('option:selected').text();
            const txtManzana  = $filtroManzana.find('option:selected').text();
            $('#lote_contexto').text(`${txtProyecto} / ${txtEtapa} / ${txtManzana}`);

            modalLote.show();
        });

        /* ==============
         * Editar Lote
         * ============*/

        $('#lotesContainer').on('click', '.btn-edit-lote', function () {
            const id = $(this).data('id');

            $.get('index.php?c=lotes&a=get&id=' + id, function (resp) {
                let l;
                try { l = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch (e) {
                    Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                    return;
                }

                if (!l) {
                    Swal.fire('Error', 'No se encontró el lote', 'error');
                    return;
                }

                $('#formLote')[0].reset();

                $('#lote_id').val(l.id);
                $('#lote_id_proyecto').val(l.id_proyecto);
                $('#lote_id_etapa').val(l.id_etapa);
                $('#lote_id_manzana').val(l.id_manzana);

                $('#lote_numero').val(l.numero);
                $('#lote_codigo').val(l.codigo);
                $('#lote_area_m2').val(l.area_m2);
                $('#lote_frente_m').val(l.frente_m);
                $('#lote_fondo_m').val(l.fondo_m);
                $('#lote_lado_izq_m').val(l.lado_izq_m);
                $('#lote_lado_der_m').val(l.lado_der_m);
                $('#lote_estado_comercial').val(l.estado_comercial);

                const txtProyecto = $filtroProyecto.find('option:selected').text();
                const txtEtapa    = l.etapa_nombre || '';
                const txtManzana  = l.manzana_codigo ? 'Mz ' + l.manzana_codigo : '';
                $('#lote_contexto').text(`${txtProyecto} / ${txtEtapa} / ${txtManzana}`);

                modalLote.show();
            });
        });

        /* ==============
         * Guardar Lote
         * ============*/

        $('#btnSaveLote').on('click', function () {
            const formData = $('#formLote').serialize();

            $.post('index.php?c=lotes&a=save', formData)
                .done(function (resp) {
                    let r;
                    try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                    catch (e) {
                        Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                        return;
                    }

                    if (r.status) {
                        Swal.fire('OK', 'Lote guardado correctamente', 'success');
                        modalLote.hide();
                        loadLotes();
                    } else {
                        Swal.fire('Error', r.msg || 'No se pudo guardar el lote', 'error');
                    }
                })
                .fail(function (xhr) {
                    console.error('Error AJAX save lote', xhr.status, xhr.responseText);
                    Swal.fire('Error', 'Error en el servidor al guardar el lote', 'error');
                });
        });

        /* ==============
         * Eliminar Lote
         * ============*/

        $('#lotesContainer').on('click', '.btn-del-lote', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Eliminar lote?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post('index.php?c=lotes&a=delete', { id }, function (resp) {
                    let r;
                    try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                    catch (e) {
                        Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                        return;
                    }

                    if (r.status) {
                        Swal.fire('OK', 'Lote eliminado', 'success');
                        loadLotes();
                    } else {
                        Swal.fire('Error', r.msg || 'No se pudo eliminar', 'error');
                    }
                });
            });
        });

        /* ==============
         * Botón mágico: Generar lotes
         * ============*/

        $('#btnGenerarLotes').on('click', function () {
            const idp = $filtroProyecto.val();
            const ide = $filtroEtapa.val();
            const idm = $filtroManzana.val();

            if (!idp || !ide || !idm) {
                Swal.fire('Atención', 'Debe seleccionar Proyecto, Etapa y Manzana.', 'info');
                return;
            }

            $('#formGenerarLotes')[0].reset();
            $('#gen_id_proyecto').val(idp);
            $('#gen_id_etapa').val(ide);
            $('#gen_id_manzana').val(idm);

            const txtProyecto = $filtroProyecto.find('option:selected').text();
            const txtEtapa    = $filtroEtapa.find('option:selected').text();
            const txtManzana  = $filtroManzana.find('option:selected').text();
            $('#gen_contexto').text(`${txtProyecto} / ${txtEtapa} / ${txtManzana}`);

            modalGenerarLotes.show();
        });

        $('#btnGenerarLotesConfirm').on('click', function () {
            const formData = $('#formGenerarLotes').serialize();

            $.post('index.php?c=lotes&a=generate_range', formData)
                .done(function (resp) {
                    let r;
                    try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                    catch (e) {
                        Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                        return;
                    }

                    if (r.status) {
                        Swal.fire('OK', 'Lotes generados correctamente', 'success');
                        modalGenerarLotes.hide();
                        loadLotes();
                    } else {
                        Swal.fire('Error', r.msg || 'No se pudo generar los lotes', 'error');
                    }
                })
                .fail(function (xhr) {
                    console.error('Error AJAX generate_range', xhr.status, xhr.responseText);
                    Swal.fire('Error', 'Error en el servidor al generar los lotes', 'error');
                });
        });

        // Carga inicial
        loadLotes();
    });
}
