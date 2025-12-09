// public/assets/js/proyectos.js

if (window.proyectosModuleInitialized) {
  console.log('Módulo Proyectos ya inicializado, no se vuelve a ejecutar.');
} else {
  window.proyectosModuleInitialized = true;

  $(function () {

    if (!$('#tblProyectos').length) return;

    console.log('Inicializando módulo Proyectos');

    let currentProyectoId = null;

    const $panelDetalle = $('#panelDetalleProyecto');
    const $lblProyectoTitulo = $('#lblProyectoTitulo');

    const modalProyecto        = new bootstrap.Modal(document.getElementById('modalProyecto'));
    const modalEtapa           = new bootstrap.Modal(document.getElementById('modalEtapa'));
    const modalManzana         = new bootstrap.Modal(document.getElementById('modalManzana'));
    const modalGenerarEtapas   = new bootstrap.Modal(document.getElementById('modalGenerarEtapas'));
    const modalGenerarManzanas = new bootstrap.Modal(document.getElementById('modalGenerarManzanas'));

    const $tblEtapasBody    = $('#tblEtapasBody');
    const $tblManzanasBody  = $('#tblManzanasBody');
    const $filtroEtapaManza = $('#filtroEtapaManzana');

    /* =========================
     * Helper de mensajes
     * =======================*/
    function showMsg(type, text, title) {
      title = title || (type === 'success'
        ? 'Correcto'
        : type === 'error'
        ? 'Error'
        : type === 'warning'
        ? 'Atención'
        : 'Info');

      if (window.Swal) {
        Swal.fire({
          icon: type,
          title: title,
          text: text || ''
        });
      } else {
        alert((title ? title + ': ' : '') + (text || ''));
      }
    }

    /* =========================
     * Helpers
     * =======================*/

    function loadUbigeos($select, selected) {
      $.get('index.php?c=api&a=ubigeos_list', function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Ubigeos JSON inválido', resp); return; }

        $select.empty();
        $select.append('<option value="">-- Seleccione --</option>');
        data.forEach(u => {
          $select.append(`<option value="${u.id}">${u.descripcion}</option>`);
        });

        if (selected) $select.val(selected);
      });
    }

    function showDetalleProyecto(proy) {
      currentProyectoId = proy.id;
      $panelDetalle.show();

      $lblProyectoTitulo.text(`Proyecto: ${proy.nombre} (${proy.codigo})`);

      // rellenar form detalle
      $('#detalle_id').val(proy.id);
      $('#detalle_codigo').val(proy.codigo);
      $('#detalle_nombre').val(proy.nombre);
      $('#detalle_latitud').val(proy.latitud || '');
      $('#detalle_longitud').val(proy.longitud || '');
      $('#detalle_zoom_mapa').val(proy.zoom_mapa || '');
      $('#detalle_precio_m2_base').val(proy.precio_m2_base || 0);
      $('#detalle_factor_min_pct').val(proy.factor_min_pct || -40);
      $('#detalle_factor_max_pct').val(proy.factor_max_pct || 50);
      $('#detalle_estado_legal').val(proy.estado_legal || 'EN_TRAMITE');
      $('#detalle_estado').val(proy.estado || 'ACTIVO');

      loadUbigeos($('#detalle_id_ubigeo'), proy.id_ubigeo);

      loadEtapas();
      loadEtapasEnSelects();
      loadManzanas();
    }

    function loadEtapas() {
      if (!currentProyectoId) return;

      $.get('index.php?c=proyectos&a=etapas_list&id_proyecto=' + currentProyectoId, function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Etapas JSON inválido', resp); return; }

        $tblEtapasBody.empty();

        if (!data.length) {
          $tblEtapasBody.append('<tr><td colspan="5" class="text-muted small text-center">Sin etapas</td></tr>');
          return;
        }

        data.forEach((e, idx) => {
          const check = e.habilitada_venta == 1
            ? '<span class="badge bg-success">Sí</span>'
            : '<span class="badge bg-secondary">No</span>';

          const row = `
            <tr data-id="${e.id}">
              <td>${idx+1}</td>
              <td>${e.nombre}</td>
              <td>${e.numero !== null ? e.numero : ''}</td>
              <td>${check}</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary btn-etapa-edit">Editar</button>
                  <button class="btn btn-outline-danger btn-etapa-del">Eliminar</button>
                </div>
              </td>
            </tr>
          `;
          $tblEtapasBody.append(row);
        });
      });
    }

    function loadEtapasEnSelects() {
      if (!currentProyectoId) return;

      $.get('index.php?c=proyectos&a=etapas_list&id_proyecto=' + currentProyectoId, function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Etapas JSON inválido', resp); return; }

        // filtro etapa en tab Manzanas
        $filtroEtapaManza.empty();
        $filtroEtapaManza.append('<option value="">-- Todas --</option>');
        data.forEach(e => {
          $filtroEtapaManza.append(`<option value="${e.id}">${e.nombre}</option>`);
        });

        // select etapa en modal manzana
        const $manzanaEtapa = $('#manzana_id_etapa');
        $manzanaEtapa.empty();
        data.forEach(e => {
          $manzanaEtapa.append(`<option value="${e.id}">${e.nombre}</option>`);
        });

        // select etapa en modal generar manzanas
        const $genEtapa = $('#gen_manzanas_id_etapa');
        $genEtapa.empty();
        data.forEach(e => {
          $genEtapa.append(`<option value="${e.id}">${e.nombre}</option>`);
        });

      });
    }

    function loadManzanas() {
      if (!currentProyectoId) return;

      const idEtapa = $filtroEtapaManza.val() || '';

      const url = 'index.php?c=proyectos&a=manzanas_list'
                + '&id_proyecto=' + currentProyectoId
                + '&id_etapa=' + encodeURIComponent(idEtapa);

      $.get(url, function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Manzanas JSON inválido', resp); return; }

        $tblManzanasBody.empty();

        if (!data.length) {
          $tblManzanasBody.append('<tr><td colspan="4" class="text-muted small text-center">Sin manzanas</td></tr>');
          return;
        }

        data.forEach(m => {
          const row = `
            <tr data-id="${m.id}">
              <td>${m.etapa_nombre}</td>
              <td>${m.codigo}</td>
              <td>${m.descripcion || ''}</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary btn-manzana-edit">Editar</button>
                  <button class="btn btn-outline-danger btn-manzana-del">Eliminar</button>
                </div>
              </td>
            </tr>
          `;
          $tblManzanasBody.append(row);
        });
      });
    }

    /* =========================
     * DataTable de Proyectos
     * =======================*/

    const tableProyectos = $('#tblProyectos').DataTable({
      ajax: 'index.php?c=proyectos&a=list',
      columns: [
        { data: 'id', width: '5%' },
        { data: 'codigo' },
        { data: 'nombre' },
        { data: 'ubigeo_descripcion', defaultContent: '' },
        {
          data: 'precio_m2_base',
          render: function(d) {
            return d ? parseFloat(d).toFixed(2) : '0.00';
          }
        },
        { data: 'estado_legal' },
        { data: 'estado' },
        {
          data: null,
          orderable: false,
          width: '18%',
          render: function(row) {
            const nombreEsc = (row.nombre || '').replace(/"/g, '&quot;');
            return `
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary btn-proy-edit">Editar</button>
                <button class="btn btn-outline-danger btn-proy-del">Eliminar</button>
                <button class="btn btn-outline-warning btn-factores"
                        data-id="${row.id}"
                        data-nombre="${nombreEsc}">
                  Factores
                </button>
              </div>
            `;
          }
        }
      ],
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
      }
    });

    /* =========================
     * Eventos Proyectos
     * =======================*/

    // Selección fila → mostrar detalle
    $('#tblProyectos tbody').on('click', 'tr', function(e) {
      // evitar dispararse cuando se hace click en un botón de acción
      if ($(e.target).closest('button').length) return;

      const data = tableProyectos.row(this).data();
      if (!data) return;

      $.get('index.php?c=proyectos&a=get&id=' + data.id, function(resp) {
        let proy;
        try { proy = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Proyecto JSON inválido', resp); return; }

        if (!proy) return;
        showDetalleProyecto(proy);
      });
    });

    $('#btnCollapseDetalleProyecto').on('click', function() {
      $panelDetalle.hide();
      currentProyectoId = null;
    });

    // Nuevo proyecto
    $('#btnNewProyecto').on('click', function() {
      $('#formProyectoModal')[0].reset();
      $('#proy_id').val('');
      $('#proy_zoom_mapa').val('16');
      $('#proy_factor_min_pct').val('-40');
      $('#proy_factor_max_pct').val('50');
      $('#proy_estado_legal').val('EN_TRAMITE');
      $('#proy_estado').val('ACTIVO');

      loadUbigeos($('#proy_id_ubigeo'), null);

      $('#modalProyectoLabel').text('Nuevo proyecto');
      modalProyecto.show();
    });

    // Editar proyecto desde tabla
    $('#tblProyectos').on('click', '.btn-proy-edit', function() {
      const data = tableProyectos.row($(this).closest('tr')).data();
      if (!data) return;

      $.get('index.php?c=proyectos&a=get&id=' + data.id, function(resp) {
        let proy;
        try { proy = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Proyecto JSON inválido', resp); return; }

        $('#formProyectoModal')[0].reset();

        $('#proy_id').val(proy.id);
        $('#proy_codigo').val(proy.codigo);
        $('#proy_nombre').val(proy.nombre);
        $('#proy_latitud').val(proy.latitud || '');
        $('#proy_longitud').val(proy.longitud || '');
        $('#proy_zoom_mapa').val(proy.zoom_mapa || '');
        $('#proy_precio_m2_base').val(proy.precio_m2_base || 0);
        $('#proy_factor_min_pct').val(proy.factor_min_pct || -40);
        $('#proy_factor_max_pct').val(proy.factor_max_pct || 50);
        $('#proy_estado_legal').val(proy.estado_legal || 'EN_TRAMITE');
        $('#proy_estado').val(proy.estado || 'ACTIVO');

        loadUbigeos($('#proy_id_ubigeo'), proy.id_ubigeo);

        $('#modalProyectoLabel').text('Editar proyecto');
        modalProyecto.show();
      });
    });

    // Eliminar proyecto
    $('#tblProyectos').on('click', '.btn-proy-del', function() {
      const data = tableProyectos.row($(this).closest('tr')).data();
      if (!data) return;

      const doDelete = () => {
        $.post('index.php?c=proyectos&a=delete', {id: data.id}, function(resp) {
          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

          const ok = r && (r.status === true || r.status === 1 || r.status === '1');

          if (ok) {
            tableProyectos.ajax.reload(null, false);
            if (currentProyectoId == data.id) {
              $panelDetalle.hide();
              currentProyectoId = null;
            }
            showMsg('success', r.msg || 'Proyecto eliminado correctamente.');
          } else {
            showMsg('warning', r.msg || 'No se pudo eliminar el proyecto.');
          }
        });
      };

      if (window.Swal) {
        Swal.fire({
          title: 'Eliminar proyecto',
          text: '¿Deseas eliminar este proyecto y todo su contenido (etapas, manzanas, etc.)?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) doDelete();
        });
      } else {
        if (confirm('¿Eliminar este proyecto y todo su contenido (etapas, manzanas, etc.)?')) doDelete();
      }
    });

    // Guardar desde modal proyecto (nuevo / editar)
    $('#btnSaveProyectoModal').on('click', function() {
      const formData = $('#formProyectoModal').serialize();

      $.post('index.php?c=proyectos&a=save', formData, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

        const ok = r && (r.status === true || r.status === 1 || r.status === '1');

        if (ok) {
          modalProyecto.hide();
          tableProyectos.ajax.reload(null, false);
          showMsg('success', r.msg || 'Proyecto guardado correctamente.');
        } else {
          showMsg('warning', r.msg || 'No se pudo guardar el proyecto.');
        }
      });
    });

    // Guardar desde detalle proyecto
    $('#btnSaveProyectoDetalle').on('click', function() {
      if (!currentProyectoId) return;

      const formData = $('#formProyectoDetalle').serialize();

      $.post('index.php?c=proyectos&a=save', formData, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

        const ok = r && (r.status === true || r.status === 1 || r.status === '1');

        if (ok) {
          tableProyectos.ajax.reload(null, false);
          showMsg('success', r.msg || 'Proyecto actualizado correctamente.');
        } else {
          showMsg('warning', r.msg || 'No se pudo guardar el proyecto.');
        }
      });
    });

    /* =========================
     * ETAPAS
     * =======================*/

    $('#btnNewEtapa').on('click', function() {
      if (!currentProyectoId) {
        showMsg('warning', 'Selecciona un proyecto primero.');
        return;
      }

      $('#formEtapa')[0].reset();
      $('#etapa_id').val('');
      $('#etapa_id_proyecto').val(currentProyectoId);
      $('#etapa_habilitada_venta').prop('checked', false);

      $('#modalEtapaLabel').text('Nueva etapa');
      modalEtapa.show();
    });

    $('#btnSaveEtapa').on('click', function() {
      const formData = $('#formEtapa').serialize();

      $.post('index.php?c=proyectos&a=etapas_save', formData)
        .done(function(resp) {
          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){
            console.error('JSON inválido en etapas_save:', resp);
            showMsg('error', 'Respuesta inválida del servidor (etapas_save).');
            return;
          }

          const ok = r && (r.status === true || r.status === 1 || r.status === '1');

          if (ok) {
            modalEtapa.hide();
            loadEtapas();
            loadEtapasEnSelects();
            showMsg('success', r.msg || 'Etapa guardada correctamente.');
          } else {
            showMsg('warning', r.msg || 'No se pudo guardar la etapa.');
          }
        })
        .fail(function(xhr) {
          console.error('Error AJAX etapas_save:', xhr.status, xhr.responseText);
          showMsg('error', 'Error en el servidor (etapas_save ' + xhr.status + ').');
        });
    });


    $('#tblEtapas').on('click', '.btn-etapa-edit', function() {
      const $tr = $(this).closest('tr');
      const id  = $tr.data('id');
      if (!id) return;

      $.get('index.php?c=proyectos&a=etapas_list&id_proyecto=' + currentProyectoId, function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Etapas JSON inválido', resp); return; }

        const etapa = data.find(e => parseInt(e.id) === parseInt(id));
        if (!etapa) return;

        $('#formEtapa')[0].reset();
        $('#etapa_id').val(etapa.id);
        $('#etapa_id_proyecto').val(etapa.id_proyecto);
        $('#etapa_nombre').val(etapa.nombre);
        $('#etapa_numero').val(etapa.numero || '');
        $('#etapa_habilitada_venta').prop('checked', etapa.habilitada_venta == 1);

        $('#modalEtapaLabel').text('Editar etapa');
        modalEtapa.show();
      });
    });

    $('#tblEtapas').on('click', '.btn-etapa-del', function() {
      const $tr = $(this).closest('tr');
      const id  = $tr.data('id');
      if (!id) return;

      const doDelete = () => {
        $.post('index.php?c=proyectos&a=etapas_delete', {id}, function(resp) {
          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

          const ok = r && (r.status === true || r.status === 1 || r.status === '1');

          if (ok) {
            loadEtapas();
            loadEtapasEnSelects();
            loadManzanas();
            showMsg('success', r.msg || 'Etapa eliminada correctamente.');
          } else {
            showMsg('warning', r.msg || 'No se pudo eliminar la etapa.');
          }
        });
      };

      if (window.Swal) {
        Swal.fire({
          title: 'Eliminar etapa',
          text: 'Se eliminarán también sus manzanas y lotes asociados. ¿Continuar?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) doDelete();
        });
      } else {
        if (confirm('¿Eliminar esta etapa? Se eliminarán también sus manzanas y lotes asociados.')) doDelete();
      }
    });

    // Generar etapas
    $('#btnGenerarEtapas').on('click', function() {
      if (!currentProyectoId) {
        showMsg('warning', 'Selecciona un proyecto primero.');
        return;
      }

      $('#formGenerarEtapas')[0].reset();
      $('#gen_etapas_id_proyecto').val(currentProyectoId);
      $('#gen_etapas_cantidad').val(3);

      modalGenerarEtapas.show();
    });

    $('#btnGenerateEtapas').on('click', function() {
      const formData = $('#formGenerarEtapas').serialize();

      $.post('index.php?c=proyectos&a=etapas_generate', formData, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

        const ok = r && (r.status === true || r.status === 1 || r.status === '1');

        if (ok) {
          modalGenerarEtapas.hide();
          loadEtapas();
          loadEtapasEnSelects();
          showMsg('success', r.msg || 'Etapas generadas correctamente.');
        } else {
          showMsg('warning', r.msg || 'No se pudieron generar las etapas.');
        }
      });
    });

    /* =========================
     * MANZANAS
     * =======================*/

    $filtroEtapaManza.on('change', function() {
      loadManzanas();
    });

    $('#btnNewManzana').on('click', function() {
      if (!currentProyectoId) {
        showMsg('warning', 'Selecciona un proyecto primero.');
        return;
      }

      $('#formManzana')[0].reset();
      $('#manzana_id').val('');

      // asegurar que los selects tengan etapas
      loadEtapasEnSelects();

      $('#modalManzanaLabel').text('Nueva manzana');
      modalManzana.show();
    });

    $('#btnSaveManzana').on('click', function() {
      const formData = $('#formManzana').serialize();

      $.post('index.php?c=proyectos&a=manzanas_save', formData)
        .done(function(resp) {
          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){
            console.error('JSON inválido en manzanas_save:', resp);
            showMsg('error', 'Respuesta inválida del servidor (manzanas_save).');
            return;
          }

          const ok = r && (r.status === true || r.status === 1 || r.status === '1');

          if (ok) {
            modalManzana.hide();
            loadManzanas();
            showMsg('success', r.msg || 'Manzana guardada correctamente.');
          } else {
            showMsg('warning', r.msg || 'No se pudo guardar la manzana.');
          }
        })
        .fail(function(xhr) {
          console.error('Error AJAX manzanas_save:', xhr.status, xhr.responseText);
          showMsg('error', 'Error en el servidor (manzanas_save ' + xhr.status + ').');
        });
    });


    $('#tblManzanas').on('click', '.btn-manzana-edit', function() {
      const $tr = $(this).closest('tr');
      const id  = $tr.data('id');
      if (!id) return;

      $.get('index.php?c=proyectos&a=manzanas_list&id_proyecto=' + currentProyectoId, function(resp) {
        let data;
        try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ console.error('Manzanas JSON inválido', resp); return; }

        const m = data.find(x => parseInt(x.id) === parseInt(id));
        if (!m) return;

        $('#formManzana')[0].reset();
        $('#manzana_id').val(m.id);

        loadEtapasEnSelects();

        // pequeño delay para asegurar que se llenó el select
        setTimeout(function(){
          $('#manzana_id_etapa').val(m.id_etapa);
        }, 150);

        $('#manzana_codigo').val(m.codigo);
        $('#manzana_descripcion').val(m.descripcion || '');

        $('#modalManzanaLabel').text('Editar manzana');
        modalManzana.show();
      });
    });

    $('#tblManzanas').on('click', '.btn-manzana-del', function() {
      const $tr = $(this).closest('tr');
      const id  = $tr.data('id');
      if (!id) return;

      const doDelete = () => {
        $.post('index.php?c=proyectos&a=manzanas_delete', {id}, function(resp) {
          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

          const ok = r && (r.status === true || r.status === 1 || r.status === '1');

          if (ok) {
            loadManzanas();
            showMsg('success', r.msg || 'Manzana eliminada correctamente.');
          } else {
            showMsg('warning', r.msg || 'No se pudo eliminar la manzana.');
          }
        });
      };

      if (window.Swal) {
        Swal.fire({
          title: 'Eliminar manzana',
          text: 'Se eliminarán también sus lotes asociados. ¿Continuar?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) doDelete();
        });
      } else {
        if (confirm('¿Eliminar esta manzana? Se eliminarán también sus lotes.')) doDelete();
      }
    });

    // Generar manzanas
    $('#btnGenerarManzanas').on('click', function() {
      if (!currentProyectoId) {
        showMsg('warning', 'Selecciona un proyecto primero.');
        return;
      }

      $('#formGenerarManzanas')[0].reset();
      $('#gen_manzanas_letra_inicio').val('A');
      $('#gen_manzanas_letra_fin').val('H');

      loadEtapasEnSelects();

      modalGenerarManzanas.show();
    });

    $('#btnGenerateManzanas').on('click', function() {
      const formData = $('#formGenerarManzanas').serialize();

      $.post('index.php?c=proyectos&a=manzanas_generate', formData, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ showMsg('error', 'Respuesta inválida del servidor.'); return; }

        const ok = r && (r.status === true || r.status === 1 || r.status === '1');

        if (ok) {
          modalGenerarManzanas.hide();
          loadManzanas();
          showMsg('success', r.msg || 'Manzanas generadas correctamente.');
        } else {
          showMsg('warning', r.msg || 'No se pudieron generar las manzanas.');
        }
      });
    });

    /* =====================================================
     * FACTORES POR PROYECTO (CRUD) - ahora en Proyectos
     * =====================================================*/

    if ($('#modalFactoresProyecto').length) {

      const fpAjaxJSON = (url, method, data, onOk) => {
        $.ajax({
          url: url,
          method: method || 'GET',
          data: data || {},
          dataType: 'json'
        })
          .done(function (resp) {
            if (onOk) onOk(resp);
          })
          .fail(function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX factores error', textStatus, errorThrown, jqXHR.responseText);
            showMsg('error', 'Ocurrió un error al procesar factores.');
          });
      };

      const $modalFactores       = $('#modalFactoresProyecto');
      const $fpNombreProyecto    = $('#fp_nombreProyecto');
      const $fpIdProyecto        = $('#fp_idProyecto');
      const $fpTablaBody         = $('#fp_tablaFactores tbody');
      const $fpForm              = $('#fp_formFactor');
      const $fpFormTitulo        = $('#fp_formTitulo');
      const $fpIdFactor          = $('#fp_idFactor');
      const $fpCatFactor         = $('#fp_catFactor');
      const $fpNombre            = $('#fp_nombre');
      const $fpCodigo            = $('#fp_codigo');
      const $fpValorPct          = $('#fp_valorPct');
      const $fpActivo            = $('#fp_activo');
      const $modalEjemplos       = $('#modalFactoresEjemplos');
      const $fpEjemplosContainer = $('#fp_ejemplosContainer');

      // Botón "Factores"
      $('#tblProyectos').on('click', '.btn-factores', function (e) {
        e.stopPropagation();

        const idProyecto = $(this).data('id');
        const nombre     = $(this).data('nombre');

        $fpIdProyecto.val(idProyecto);
        $fpNombreProyecto.text(nombre || '');
        fpLimpiarFormulario();
        fpCargarFactores(idProyecto);

        const modal = new bootstrap.Modal($modalFactores[0]);
        modal.show();
      });

      function fpCargarFactores(idProyecto) {
        $fpTablaBody.html('<tr><td colspan="5" class="text-center small text-muted">Cargando...</td></tr>');

        fpAjaxJSON(
          'index.php?c=proyectos&a=factores_list',
          'GET',
          { id_proyecto: idProyecto },
          function (resp) {
            const factores = resp.data || resp;

            if (!factores || !factores.length) {
              $fpTablaBody.html('<tr><td colspan="5" class="text-center small text-muted">Sin factores registrados.</td></tr>');
              return;
            }

            const rows = factores.map(f => {
              const activoBadge = (f.activo == 1 || f.activo === '1')
                ? '<span class="badge bg-success">Sí</span>'
                : '<span class="badge bg-secondary">No</span>';

              const codigo = f.codigo || '';

              return `
                <tr data-id="${f.id}" data-codigo="${codigo.replace(/"/g, '&quot;')}">
                  <td>${f.cat_factor || ''}</td>
                  <td>${f.nombre || ''}</td>
                  <td class="text-end">${(parseFloat(f.valor_pct) || 0).toFixed(2)}%</td>
                  <td class="text-center">${activoBadge}</td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary fp-btn-editar">Editar</button>
                    <button type="button" class="btn btn-sm btn-outline-danger fp-btn-eliminar">Eliminar</button>
                  </td>
                </tr>
              `;
            });

            $fpTablaBody.html(rows.join(''));
          }
        );
      }

      function fpLimpiarFormulario() {
        $fpFormTitulo.text('Nuevo factor');
        $fpIdFactor.val('');
        $fpCatFactor.val('');
        $fpNombre.val('');
        $fpCodigo.val('');
        $fpValorPct.val('');
        $fpActivo.prop('checked', true);
      }

      $('#fp_btnLimpiar').on('click', function () {
        fpLimpiarFormulario();
      });

      // Editar factor
      $fpTablaBody.on('click', '.fp-btn-editar', function () {
        const $tr       = $(this).closest('tr');
        const id        = $tr.data('id');
        const cat       = $tr.find('td').eq(0).text().trim();
        const nombre    = $tr.find('td').eq(1).text().trim();
        const valorTexto= $tr.find('td').eq(2).text().replace('%', '').trim();
        const activo    = $tr.find('.badge.bg-success').length > 0;
        const codigo    = $tr.data('codigo') || '';

        $fpFormTitulo.text('Editar factor');
        $fpIdFactor.val(id);
        $fpCatFactor.val(cat);
        $fpNombre.val(nombre);
        $fpCodigo.val(codigo);
        $fpValorPct.val(valorTexto);
        $fpActivo.prop('checked', activo);
      });

      // Eliminar factor
      $fpTablaBody.on('click', '.fp-btn-eliminar', function () {
        const $tr = $(this).closest('tr');
        const id  = $tr.data('id');
        const nombre = $tr.find('td').eq(1).text().trim();

        const doDelete = () => {
          fpAjaxJSON(
            'index.php?c=proyectos&a=factores_delete',
            'POST',
            { id },
            function (resp) {
              const ok = resp && (resp.status === true || resp.status === 1 || resp.status === '1');

              if (ok) {
                showMsg('success', resp.msg || 'Factor eliminado correctamente.');
                const idProyecto = $fpIdProyecto.val();
                fpCargarFactores(idProyecto);
              } else {
                showMsg('warning', (resp && resp.msg) || 'No se pudo eliminar el factor.');
              }
            }
          );
        };

        if (window.Swal) {
          Swal.fire({
            title: 'Eliminar factor',
            text: '¿Seguro que deseas eliminar el factor "' + nombre + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
          }).then(res => {
            if (res.isConfirmed) doDelete();
          });
        } else {
          if (confirm('¿Eliminar el factor "' + nombre + '"?')) doDelete();
        }
      });

      // Guardar factor
      $fpForm.on('submit', function (e) {
        e.preventDefault();

        const idProyecto = $fpIdProyecto.val();
        if (!idProyecto) {
          showMsg('error', 'No se encontró el proyecto para asociar el factor.');
          return;
        }

        const payload = {
          id         : $fpIdFactor.val(),
          id_proyecto: idProyecto,
          cat_factor : $fpCatFactor.val(),
          codigo     : $fpCodigo.val(),
          nombre     : $fpNombre.val(),
          valor_pct  : $fpValorPct.val(),
          activo     : $fpActivo.is(':checked') ? 1 : 0
        };

        fpAjaxJSON(
          'index.php?c=proyectos&a=factores_save',
          'POST',
          payload,
          function (resp) {
            const ok = resp && (resp.status === true || resp.status === 1 || resp.status === '1');

            if (ok) {
              showMsg('success', resp.msg || 'Factor guardado correctamente.');
              fpLimpiarFormulario();
              fpCargarFactores(idProyecto);
            } else {
              showMsg('warning', (resp && resp.msg) || 'No se pudo guardar el factor.');
            }
          }
        );
      });

      // -------- Ejemplos de factores (factores.json) --------
      $('#fp_btnEjemplos').on('click', function () {
        if ($fpEjemplosContainer.children().length > 0) {
          const modalEj = new bootstrap.Modal($modalEjemplos[0]);
          modalEj.show();
          return;
        }

        $.getJSON('app/Views/lotes/factores.json')
          .done(function (data) {
            const categorias = Object.keys(data || {});
            if (!categorias.length) {
              $fpEjemplosContainer.html('<p class="text-muted">No se encontraron ejemplos.</p>');
            } else {
              let html = '<div class="accordion" id="fp_ejemplosAccordion">';
              categorias.forEach((cat, idx) => {
                const catId = 'fpCat' + idx;
                html += `
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="${catId}_header">
                      <button class="accordion-button ${idx > 0 ? 'collapsed' : ''}" type="button"
                              data-bs-toggle="collapse" data-bs-target="#${catId}_body">
                        ${cat}
                      </button>
                    </h2>
                    <div id="${catId}_body" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}"
                         data-bs-parent="#fp_ejemplosAccordion">
                      <div class="accordion-body p-2">
                `;
                const arr = data[cat] || [];
                if (!arr.length) {
                  html += '<p class="text-muted small mb-0">Sin ejemplos.</p>';
                } else {
                  html += '<ul class="list-group list-group-flush">';
                  arr.forEach((item) => {
                    const nombre = item.nombre || '';
                    const desc   = item.descripcion || '';
                    const codigo = item.codigo || '';

                    html += `
                      <li class="list-group-item list-group-item-action fp-ejemplo-item"
                          data-cat="${cat}"
                          data-nombre="${nombre.replace(/"/g, '&quot;')}"
                          data-codigo="${codigo.replace(/"/g, '&quot;')}"
                          data-descripcion="${(desc || '').replace(/"/g, '&quot;')}">
                        <div class="fw-semibold">
                          ${nombre}${codigo ? ' <span class="text-muted">(' + codigo + ')</span>' : ''}
                        </div>
                        <div class="small text-muted">${desc}</div>
                      </li>
                    `;
                  });
                  html += '</ul>';
                }
                html += `
                      </div>
                    </div>
                  </div>
                `;
              });
              html += '</div>';
              $fpEjemplosContainer.html(html);
            }

            const modalEj = new bootstrap.Modal($modalEjemplos[0]);
            modalEj.show();
          })
          .fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Error cargando factores.json', textStatus, errorThrown);
            showMsg('error', 'No se pudieron cargar los ejemplos de factores.');
          });
      });

      // Click en ejemplo → llenar formulario
      $fpEjemplosContainer.on('click', '.fp-ejemplo-item', function () {
        const cat    = $(this).data('cat');
        const nombre = $(this).data('nombre');
        const codigo = $(this).data('codigo');

        $fpCatFactor.val(cat);
        $fpNombre.val(nombre);
        $fpCodigo.val(codigo);
        $fpFormTitulo.text('Nuevo factor (desde ejemplo)');

        const modalEj = bootstrap.Modal.getInstance($modalEjemplos[0]);
        if (modalEj) modalEj.hide();
      });

    } // fin if #modalFactoresProyecto

  });
}
