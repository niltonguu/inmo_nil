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

    const modalProyecto       = new bootstrap.Modal(document.getElementById('modalProyecto'));
    const modalEtapa          = new bootstrap.Modal(document.getElementById('modalEtapa'));
    const modalManzana        = new bootstrap.Modal(document.getElementById('modalManzana'));
    const modalGenerarEtapas  = new bootstrap.Modal(document.getElementById('modalGenerarEtapas'));
    const modalGenerarManzanas= new bootstrap.Modal(document.getElementById('modalGenerarManzanas'));

    const $tblEtapasBody    = $('#tblEtapasBody');
    const $tblManzanasBody  = $('#tblManzanasBody');
    const $filtroEtapaManza = $('#filtroEtapaManzana');

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
          width: '12%',
          render: function(row) {
            return `
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary btn-proy-edit">Editar</button>
                <button class="btn btn-outline-danger btn-proy-del">Eliminar</button>
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

      if (!confirm('¿Eliminar este proyecto y todo su contenido (etapas, manzanas, etc.)?')) return;

      $.post('index.php?c=proyectos&a=delete', {id: data.id}, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          tableProyectos.ajax.reload(null, false);
          if (currentProyectoId == data.id) {
            $panelDetalle.hide();
            currentProyectoId = null;
          }
        } else {
          alert(r.msg || 'No se pudo eliminar');
        }
      });
    });

    // Guardar desde modal proyecto
    $('#btnSaveProyectoModal').on('click', function() {
      const formData = $('#formProyectoModal').serialize();

      $.post('index.php?c=proyectos&a=save', formData, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          modalProyecto.hide();
          tableProyectos.ajax.reload(null, false);
        } else {
          alert(r.msg || 'No se pudo guardar');
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
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          tableProyectos.ajax.reload(null, false);
          alert('Proyecto actualizado');
        } else {
          alert(r.msg || 'No se pudo guardar');
        }
      });
    });

    /* =========================
     * ETAPAS
     * =======================*/

    $('#btnNewEtapa').on('click', function() {
      if (!currentProyectoId) {
        alert('Selecciona un proyecto primero');
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
      console.log('Enviando etapa:', formData);

      $.post('index.php?c=proyectos&a=etapas_save', formData)
        .done(function(resp) {
          console.log('Respuesta etapas_save:', resp);

          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){
            console.error('JSON inválido en etapas_save:', resp);
            alert('Respuesta inválida del servidor (etapas_save). Mira la consola.');
            return;
          }

          if (r.status) {
            modalEtapa.hide();
            loadEtapas();
            loadEtapasEnSelects();
          } else {
            alert(r.msg || 'No se pudo guardar etapa');
          }
        })
        .fail(function(xhr) {
          console.error('Error AJAX etapas_save:', xhr.status, xhr.responseText);
          alert('Error en el servidor (etapas_save ' + xhr.status + '). Mira la consola.');
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

      if (!confirm('¿Eliminar esta etapa? Se eliminarán también sus manzanas y lotes asociados.')) return;

      $.post('index.php?c=proyectos&a=etapas_delete', {id}, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          loadEtapas();
          loadEtapasEnSelects();
          loadManzanas();
        } else {
          alert(r.msg || 'No se pudo eliminar etapa');
        }
      });
    });

    // Generar etapas
    $('#btnGenerarEtapas').on('click', function() {
      if (!currentProyectoId) {
        alert('Selecciona un proyecto primero');
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
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          modalGenerarEtapas.hide();
          loadEtapas();
          loadEtapasEnSelects();
        } else {
          alert(r.msg || 'No se pudieron generar etapas');
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
        alert('Selecciona un proyecto primero');
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
      console.log('Enviando manzana:', formData);

      $.post('index.php?c=proyectos&a=manzanas_save', formData)
        .done(function(resp) {
          console.log('Respuesta manzanas_save:', resp);

          let r;
          try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
          catch(e){
            console.error('JSON inválido en manzanas_save:', resp);
            alert('Respuesta inválida del servidor (manzanas_save). Mira la consola.');
            return;
          }

          if (r.status) {
            modalManzana.hide();
            loadManzanas();
          } else {
            alert(r.msg || 'No se pudo guardar manzana');
          }
        })
        .fail(function(xhr) {
          console.error('Error AJAX manzanas_save:', xhr.status, xhr.responseText);
          alert('Error en el servidor (manzanas_save ' + xhr.status + '). Mira la consola.');
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

      if (!confirm('¿Eliminar esta manzana? Se eliminarán también sus lotes.')) return;

      $.post('index.php?c=proyectos&a=manzanas_delete', {id}, function(resp) {
        let r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          loadManzanas();
        } else {
          alert(r.msg || 'No se pudo eliminar manzana');
        }
      });
    });

    // Generar manzanas
    $('#btnGenerarManzanas').on('click', function() {
      if (!currentProyectoId) {
        alert('Selecciona un proyecto primero');
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
        catch(e){ alert('Respuesta inválida'); return; }

        if (r.status) {
          modalGenerarManzanas.hide();
          loadManzanas();
        } else {
          alert(r.msg || 'No se pudieron generar manzanas');
        }
      });
    });

  });
}
