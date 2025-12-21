// public/assets/js/lotes_admin.js
console.log('lotes_admin.js cargado');

$(function () {

  const ajaxJSON        = LOTES.ajaxJSON;
  const loadProyectos   = LOTES.loadProyectosSelect;
  const loadEtapas      = LOTES.loadEtapasSelect;
  const loadManzanas    = LOTES.loadManzanasSelect;
  const loadClientesSel = LOTES.loadClientesSelect;
  const estadoBadge     = LOTES.formatEstadoLoteBadge;

  // -------------------------------------------------
  // DOM refs
  // -------------------------------------------------
  const $fProyecto = $('#filtroProyecto');
  const $fEtapa    = $('#filtroEtapa');
  const $fManzana  = $('#filtroManzana');
  const $fEstado   = $('#filtroEstado');
  const $tabla     = $('#tablaLotes');

  // Modal lote (alta/edición)
  const $modalLote = $('#modalLote');
  const $formLote  = $('#formLote');

  // Modal creación masiva
  const $modalMasivo = $('#modalLotesMasivos');
  const $formMasivo  = $('#formLotesMasivos');

  // Modal FACTORES por lote
  const $modalFactores      = $('#modalLoteFactores');
  const $formFactores       = $('#formLoteFactores');
  const $tblFactoresBody    = $('#tblFactoresLoteBody');
  const $lblLoteTituloFact  = $('#lblLoteTituloFactores');
  const $lblFactorTotal     = $('#lblFactorTotal');
  const $lblFactorRango     = $('#lblFactorRango');
  const $lblPrecioBase      = $('#lblPrecioBase');
  const $lblPrecioFinal     = $('#lblPrecioFinal');

  // Modal VÉRTICES por lote
  const $modalVertices     = $('#modalVerticesLote');
  const $formVertices      = $('#formVerticesLote');
  const $tblVerticesBody   = $('#tblVerticesBody');
  const $lblVerticesLote   = $('#vert_lbl_lote');
  const $hiddenVertIdLote  = $('#vert_id_lote');

  // Modal HISTORIAL por lote
  const $modalHistorial    = $('#modalHistorialLote');
  const $tblHistorialBody  = $('#tblHistorialBody');
  const $lblHistorialLote  = $('#hist_lbl_lote');

  let dt = null;

  // -------------------------------------------------
  // Inicialización de filtros
  // -------------------------------------------------
  function initCombosFiltros() {
    loadProyectos($fProyecto, 'Todos los proyectos');

    $fProyecto.on('change', function () {
      const idProyecto = $(this).val();
      loadEtapas($fEtapa, idProyecto, 'Todas las etapas');

      $fManzana
        .empty()
        .append($('<option>', { value: '' }).text('Todas las manzanas'))
        .trigger('change');

      recargarTabla();
    });

    $fEtapa.on('change', function () {
      const idEtapa = $(this).val();
      loadManzanas($fManzana, idEtapa, 'Todas las manzanas');
      recargarTabla();
    });

    $fManzana.on('change', recargarTabla);
    $fEstado.on('change', recargarTabla);
  }

  // -------------------------------------------------
  // DataTable Admin
  // -------------------------------------------------
  function initDataTable() {
    dt = $tabla.DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: 'index.php?c=lotes&a=list_admin',
        data: function (d) {
          d.id_proyecto = $fProyecto.val() || '';
          d.id_etapa    = $fEtapa.val() || '';
          d.id_manzana  = $fManzana.val() || '';
          d.estado_lote = $fEstado.val() || '';
        },
        dataSrc: 'data'
      },
      columns: [
        { data: 'proyecto', title: 'Proyecto' },
        { data: 'etapa',    title: 'Etapa' },
        { data: 'manzana',  title: 'Manzana' },
        { data: 'numero',   title: 'Lote' },
        {
          data: 'area_m2',
          title: 'Área (m²)',
          render: d => d ? parseFloat(d).toFixed(2) : ''
        },
        {
          data: 'precio_base',
          title: 'Precio base',
          render: d => d ? 'S/ ' + parseFloat(d).toFixed(2) : 'S/ 0.00'
        },
        {
          data: 'factor_pct_total',
          title: 'Factor (%)',
          render: d => d !== null ? parseFloat(d).toFixed(2) + '%' : '0.00%'
        },
        {
          data: 'precio_final',
          title: 'Precio final',
          render: d => d ? 'S/ ' + parseFloat(d).toFixed(2) : 'S/ 0.00'
        },
        {
          data: 'estado_lote',
          title: 'Estado',
          orderable: false,
          render: d => estadoBadge(d)
        },
        {
          data: 'cliente_nombre',
          title: 'Cliente',
          render: d => d ? d : '-'
        },
        {
          data: null,
          title: '**',
          orderable: false,
          render: (data, type, row) => {
            let dropDown = "";
            if(data.estado_lote != "DISPONIBLE"){
              dropDown = `
                <li><button class="dropdown-item btn-lote-docs" data-id="${row.id}">Documentos</button></li>
                <li><hr class="dropdown-divider"></li>
              `;
            }

            return `
              <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item btn-edit-lote" href="javascript:void(0)" data-id="${row.id}">
                      Editar
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btn-estado-lote" href="javascript:void(0)" data-id="${row.id}">
                      Cambiar estado
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btn-lote-factores" href="javascript:void(0)" data-id="${row.id}">
                      Factores
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btn-lote-vertices" href="javascript:void(0)" data-id="${row.id}">
                      Vértices
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btn-lote-historial" href="javascript:void(0)" data-id="${row.id}">
                      Historial
                    </a>
                  </li>
                  <!-- AQUÍ AGREGAMOS DOCUMENTOS -->
                  <li><hr class="dropdown-divider"></li>
                  ${ dropDown }
                  <li>
                    <a class="dropdown-item text-danger btn-del-lote" href="javascript:void(0)" data-id="${row.id}">
                      Eliminar
                    </a>
                  </li>
                  
        
                </ul>
              </div>
            `;
          }
        }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
      }
    });

    // Eventos de acciones
    $tabla.on('click', '.btn-edit-lote', function () {
      const id = $(this).data('id');
      abrirModalLote(id);
    });

    $tabla.on('click', '.btn-del-lote', function () {
      const id = $(this).data('id');
      eliminarLote(id);
    });

    $tabla.on('click', '.btn-estado-lote', function () {
      const id = $(this).data('id');
      cambiarEstadoSwal(id);
    });

    $tabla.on('click', '.btn-lote-factores', function () {
      const rowData = dt.row($(this).closest('tr')).data();
      if (!rowData) return;
      abrirModalFactoresLote(rowData);
    });

    $tabla.on('click', '.btn-lote-vertices', function () {
      const rowData = dt.row($(this).closest('tr')).data();
      if (!rowData) return;
      abrirModalVerticesLote(rowData);
    });

    $tabla.on('click', '.btn-lote-historial', function () {
      const rowData = dt.row($(this).closest('tr')).data();
      if (!rowData) return;
      abrirModalHistorialLote(rowData);
    });
  }

  function recargarTabla() {
    if (dt) dt.ajax.reload(null, true);
  }

  // -------------------------------------------------
  // Modal Lote (Admin)
  // -------------------------------------------------
  function limpiarFormLote() {
    $formLote[0].reset();
    $formLote.find('[name="id"]').val('');
  }

  function abrirModalLote(id) {
    limpiarFormLote();

    const $selProyecto = $formLote.find('[name="id_proyecto"]');
    const $selEtapa    = $formLote.find('[name="id_etapa"]');
    const $selManzana  = $formLote.find('[name="id_manzana"]');

    loadProyectos($selProyecto, 'Seleccione proyecto');

    $selProyecto.off('change').on('change', function () {
      const idP = $(this).val();
      loadEtapas($selEtapa, idP, 'Seleccione etapa');
      $selManzana
        .empty()
        .append($('<option>', { value: '' }).text('Seleccione manzana'))
        .trigger('change');
    });

    $selEtapa.off('change').on('change', function () {
      const idE = $(this).val();
      loadManzanas($selManzana, idE, 'Seleccione manzana');
    });

    if (id) {
      // editar
      ajaxJSON(
        'index.php?c=lotes&a=get',
        'GET',
        { id: id },
        function (data) {
          if (!data) {
            Swal.fire('Error', 'Lote no encontrado', 'error');
            return;
          }

          $formLote.find('[name="id"]').val(data.id);
          $formLote.find('[name="numero"]').val(data.numero);
          $formLote.find('[name="codigo"]').val(data.codigo);
          $formLote.find('[name="area_m2"]').val(data.area_m2);
          $formLote.find('[name="precio_m2"]').val(data.precio_m2);
          $formLote.find('[name="frente_m"]').val(data.frente_m);
          $formLote.find('[name="fondo_m"]').val(data.fondo_m);
          $formLote.find('[name="lado_izq_m"]').val(data.lado_izq_m);
          $formLote.find('[name="lado_der_m"]').val(data.lado_der_m);
          $formLote.find('[name="estado_comercial"]').val(data.estado_comercial);
          $formLote.find('[name="estado_lote"]').val(data.estado_lote);

          loadProyectos($selProyecto, 'Seleccione proyecto');
          setTimeout(() => {
            $selProyecto.val(data.id_proyecto).trigger('change');

            setTimeout(() => {
              loadEtapas($selEtapa, data.id_proyecto, 'Seleccione etapa');

              setTimeout(() => {
                $selEtapa.val(data.id_etapa).trigger('change');

                setTimeout(() => {
                  loadManzanas($selManzana, data.id_etapa, 'Seleccione manzana');

                  setTimeout(() => {
                    $selManzana.val(data.id_manzana).trigger('change');
                  }, 200);
                }, 200);
              }, 200);
            }, 200);
          }, 200);

          $modalLote.modal('show');
        }
      );
    } else {
      // nuevo
      $modalLote.modal('show');
    }
  }

  $formLote.on('submit', function (e) {
    e.preventDefault();
    const formData = $formLote.serialize();

    ajaxJSON(
      'index.php?c=lotes&a=save',
      'POST',
      formData,
      function (resp) {
        if (resp.status) {
          Swal.fire('OK', resp.msg || 'Lote guardado', 'success');
          $modalLote.modal('hide');
          recargarTabla();
        } else {
          Swal.fire('Atención', resp.msg || 'No se pudo guardar el lote', 'warning');
        }
      }
    );
  });

  // -------------------------------------------------
  // Eliminar Lote
  // -------------------------------------------------
  function eliminarLote(id) {
    Swal.fire({
      title: '¿Eliminar lote?',
      text: 'Esta acción no se puede deshacer si tiene relaciones.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (!result.isConfirmed) return;

      ajaxJSON(
        'index.php?c=lotes&a=delete',
        'POST',
        { id: id },
        function (resp) {
          if (resp.status) {
            Swal.fire('Eliminado', resp.msg || 'Lote eliminado', 'success');
            recargarTabla();
          } else {
            Swal.fire('Atención', resp.msg || 'No se pudo eliminar el lote', 'warning');
          }
        }
      );
    });
  }

  // -------------------------------------------------
  // Cambio de estado (Admin) usando SweetAlert
  // -------------------------------------------------
  function cambiarEstadoSwal(idLote) {
    ajaxJSON(
      'index.php?c=lotes&a=get',
      'GET',
      { id: idLote },
      function (lote) {
        if (!lote) {
          Swal.fire('Error', 'Lote no encontrado', 'error');
          return;
        }

        const estadoActual = lote.estado_lote || 'DISPONIBLE';

        const opciones = [
          'DISPONIBLE',
          'RESERVADO',
          'SEPARADO',
          'VENDIDO',
          'BLOQUEADO'
        ];

        let html = `
          <div class="mb-2">
            <div class="mb-1"><strong>Estado actual</strong></div>
            <div>${estadoBadge(estadoActual)}</div>
          </div>
          <div class="mb-2">
            <label class="form-label">Nuevo estado</label>
            <select id="swalEstadoNuevo" class="form-select form-select-sm">
              <option value="">Seleccione estado</option>`;

        opciones.forEach(op => {
          html += `<option value="${op}">${op}</option>`;
        });

        html += `
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Cliente (obligatorio si el estado es distinto de DISPONIBLE)</label>
            <select id="swalCliente" class="form-select form-select-sm"></select>
          </div>
          <div class="mb-2">
            <label class="form-label">Motivo / Nota</label>
            <textarea id="swalMotivo" class="form-control form-control-sm" rows="2"></textarea>
          </div>
        `;

        Swal.fire({
          title: 'Cambiar estado del lote',
          html: html,
          width: 600,
          focusConfirm: false,
          showCancelButton: true,
          confirmButtonText: 'Guardar',
          cancelButtonText: 'Cancelar',
          didOpen: () => {
            const $cli = $('#swalCliente');
            loadClientesSel($cli, 'Seleccione cliente');

            if (lote.id_cliente) {
              setTimeout(() => {
                $cli.val(lote.id_cliente).trigger('change');
              }, 300);
            }
          },
          preConfirm: () => {
            const estadoNuevo = $('#swalEstadoNuevo').val();
            const idCliente   = $('#swalCliente').val();
            const motivo      = $('#swalMotivo').val().trim();

            if (!estadoNuevo) {
              Swal.showValidationMessage('Seleccione el nuevo estado');
              return false;
            }
            if (estadoNuevo !== 'DISPONIBLE' && !idCliente) {
              Swal.showValidationMessage('Debe seleccionar un cliente para este estado');
              return false;
            }

            return { estadoNuevo, idCliente, motivo };
          }
        }).then(result => {
          if (!result.isConfirmed || !result.value) return;

          const payload = {
            id_lote:      idLote,
            estado_nuevo: result.value.estadoNuevo,
            id_cliente:   result.value.idCliente || '',
            motivo:       result.value.motivo
          };

          ajaxJSON(
            'index.php?c=lotes&a=cambiar_estado',
            'POST',
            payload,
            function (resp) {
              if (resp.status) {
                Swal.fire('OK', resp.msg || 'Estado actualizado', 'success');
                recargarTabla();
              } else {
                Swal.fire('Atención', resp.msg || 'No se pudo cambiar el estado', 'warning');
              }
            }
          );
        });
      }
    );
  }

  // -------------------------------------------------
  // FACTORES POR LOTE (Admin)
  // -------------------------------------------------
  function recalcularPreviewFactores() {
    const precioBase = parseFloat($('#fact_precio_base').val()) || 0;
    const factorMin  = parseFloat($('#fact_factor_min').val());
    const factorMax  = parseFloat($('#fact_factor_max').val());

    let total = 0;
    $modalFactores.find('.chk-factor-lote:checked').each(function () {
      const v = parseFloat($(this).data('valor-pct'));
      if (!isNaN(v)) total += v;
    });

    const precioFinal = precioBase ? precioBase * (1 + total / 100) : 0;

    $lblFactorTotal.text(total.toFixed(2) + '%');
    $lblPrecioBase.text(
      precioBase ? 'S/ ' + precioBase.toFixed(2) : 'S/ 0.00'
    );
    $lblPrecioFinal.text(
      precioFinal ? 'S/ ' + precioFinal.toFixed(2) : 'S/ 0.00'
    );

    let cls = 'badge bg-secondary';
    if (!isNaN(total) && !isNaN(factorMin) && !isNaN(factorMax)) {
      if (total < factorMin || total > factorMax) {
        cls = 'badge bg-danger';
      } else {
        cls = 'badge bg-success';
      }
    }
    $lblFactorTotal.attr('class', cls);
  }

  function abrirModalFactoresLote(row) {
    if (!$modalFactores.length) {
      Swal.fire('Info', 'No se encontró el modal de factores del lote.', 'info');
      return;
    }

    const idLote     = row.id;
    const idProyecto = row.id_proyecto;
    const precioBase = row.precio_base ? parseFloat(row.precio_base) : 0;

    $('#fact_id_lote').val(idLote);
    $('#fact_id_proyecto').val(idProyecto || '');
    $('#fact_precio_base').val(precioBase.toFixed(2));

    $lblLoteTituloFact.text(
      `Factores del lote ${row.numero} – ${row.proyecto} / ${row.etapa} / ${row.manzana}`
    );

    $tblFactoresBody.empty().append(`
      <tr>
        <td colspan="4" class="text-muted text-center small">
          Cargando factores...
        </td>
      </tr>
    `);

    $lblFactorTotal.text('0.00%').attr('class', 'badge bg-secondary');
    $lblPrecioBase.text(
      precioBase ? 'S/ ' + precioBase.toFixed(2) : 'S/ 0.00'
    );
    $lblPrecioFinal.text('S/ 0.00');
    $lblFactorRango.text('—');

    const reqProyecto  = $.getJSON('index.php?c=proyectos&a=get', { id: idProyecto });
    const reqFactores  = $.getJSON('index.php?c=lotes&a=factores_list', { id_proyecto: idProyecto });
    const reqAplicados = $.getJSON('index.php?c=lotes&a=lote_factores_get', { id_lote: idLote });

    $.when(reqProyecto, reqFactores, reqAplicados)
      .done(function (rProy, rFact, rApl) {
        const proy = rProy[0] || {};

        const rawFact = rFact[0];
        let factores = [];
        if (Array.isArray(rawFact)) {
          factores = rawFact;
        } else if (rawFact && Array.isArray(rawFact.data)) {
          factores = rawFact.data;
        }

        const rawApl = rApl[0];
        const aplicados = Array.isArray(rawApl)
          ? rawApl
          : (rawApl && Array.isArray(rawApl.data) ? rawApl.data : []);

        const appliedIds = aplicados.map(x => parseInt(x.id_factor));

        const factorMin = proy.factor_min_pct !== undefined && proy.factor_min_pct !== null
          ? parseFloat(proy.factor_min_pct)
          : -40;
        const factorMax = proy.factor_max_pct !== undefined && proy.factor_max_pct !== null
          ? parseFloat(proy.factor_max_pct)
          : 50;

        $('#fact_factor_min').val(factorMin);
        $('#fact_factor_max').val(factorMax);
        $lblFactorRango.text(
          `${factorMin.toFixed(2)}% a ${factorMax.toFixed(2)}%`
        );

        $tblFactoresBody.empty();

        if (!factores.length) {
          $tblFactoresBody.append(`
            <tr>
              <td colspan="4" class="text-muted text-center small">
                No hay factores configurados para este proyecto.
              </td>
            </tr>
          `);
        } else {
          factores.forEach(f => {
            const idFactor = parseInt(f.id);
            const checked  = appliedIds.includes(idFactor);
            const valorPct = f.valor_pct ? parseFloat(f.valor_pct) : 0;

            const cat     = f.cat_factor || '';
            const nombre  = f.nombre || '';
            const codigo  = f.codigo ? ` (${f.codigo})` : '';
            const desc    = f.descripcion || '';

            const rowHtml = `
              <tr>
                <td class="text-center">
                  <input type="checkbox"
                         class="form-check-input chk-factor-lote"
                         value="${idFactor}"
                         data-valor-pct="${valorPct}"
                         ${checked ? 'checked' : ''}>
                </td>
                <td>${cat}</td>
                <td>
                  ${nombre}${codigo}
                  ${desc ? `<br><small class="text-muted">${desc}</small>` : ''}
                </td>
                <td class="text-end">${valorPct.toFixed(2)}%</td>
              </tr>
            `;
            $tblFactoresBody.append(rowHtml);
          });

          recalcularPreviewFactores();
        }

        $modalFactores.modal('show');
      })
      .fail(function () {
        $tblFactoresBody.empty().append(`
          <tr>
            <td colspan="4" class="text-danger text-center small">
              Error cargando factores del lote.
            </td>
          </tr>
        `);
        Swal.fire('Error', 'No se pudieron cargar los factores del lote.', 'error');
      });
  }

  if ($modalFactores.length && $formFactores.length) {
    $modalFactores.on('change', '.chk-factor-lote', function () {
      recalcularPreviewFactores();
    });

    $formFactores.on('submit', function (e) {
      e.preventDefault();

      const idLote = $('#fact_id_lote').val();
      if (!idLote) {
        Swal.fire('Atención', 'Lote inválido', 'warning');
        return;
      }

      const factores = [];
      $modalFactores.find('.chk-factor-lote:checked').each(function () {
        factores.push($(this).val());
      });

      ajaxJSON(
        'index.php?c=lotes&a=lote_factores_save',
        'POST',
        { id_lote: idLote, factores: factores },
        function (resp) {
          if (resp.status) {
            Swal.fire('OK', resp.msg || 'Factores del lote actualizados', 'success');
            $modalFactores.modal('hide');
            recargarTabla();
          } else {
            Swal.fire('Atención', resp.msg || 'No se pudieron actualizar los factores', 'warning');
          }
        }
      );
    });
  }

  // -------------------------------------------------
  // VÉRTICES POR LOTE (Admin)
  // -------------------------------------------------
  function addVerticeRow(orden = '', lat = '', lng = '') {
    const tr = `
      <tr>
        <td>
          <input type="number" class="form-control form-control-sm" name="orden[]" value="${orden}" min="1">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm" name="lat[]" value="${lat}">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm" name="lng[]" value="${lng}">
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-danger btn-del-vertice-row">
            &times;
          </button>
        </td>
      </tr>
    `;
    $tblVerticesBody.append(tr);
  }

  function abrirModalVerticesLote(row) {
    if (!$modalVertices.length) {
      Swal.fire('Info', 'No se encontró el modal de vértices del lote.', 'info');
      return;
    }

    const idLote = row.id;

    $hiddenVertIdLote.val(idLote);
    $lblVerticesLote.text(`Lote ${row.numero} – ${row.proyecto} / ${row.etapa} / ${row.manzana}`);
    $tblVerticesBody.empty().append(`
      <tr>
        <td colspan="4" class="text-muted text-center small">
          Cargando vértices...
        </td>
      </tr>
    `);

    $.getJSON('index.php?c=lotes&a=vertices_list', { id_lote: idLote })
      .done(function (rows) {
        $tblVerticesBody.empty();

        if (!rows || !rows.length) {
          addVerticeRow(1, '', '');
          addVerticeRow(2, '', '');
          addVerticeRow(3, '', '');
        } else {
          rows.forEach(v => {
            addVerticeRow(v.orden, v.lat, v.lng);
          });
        }

        $modalVertices.modal('show');
      })
      .fail(function () {
        $tblVerticesBody.empty().append(`
          <tr>
            <td colspan="4" class="text-danger text-center small">
              Error al cargar los vértices del lote.
            </td>
          </tr>
        `);
        Swal.fire('Error', 'No se pudieron cargar los vértices del lote.', 'error');
      });
  }

  if ($modalVertices.length && $formVertices.length) {

    $('#btnAddVerticeRow').on('click', function () {
      const count = $tblVerticesBody.find('tr').length;
      addVerticeRow(count + 1, '', '');
    });

    $tblVerticesBody.on('click', '.btn-del-vertice-row', function () {
      $(this).closest('tr').remove();
    });

    $('#btnClearVertices').on('click', function () {
      const idLote = $hiddenVertIdLote.val();
      if (!idLote) {
        Swal.fire('Atención', 'Lote inválido', 'warning');
        return;
      }

      Swal.fire({
        title: '¿Eliminar todos los vértices?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (!result.isConfirmed) return;

        ajaxJSON(
          'index.php?c=lotes&a=vertices_delete',
          'POST',
          { id_lote: idLote },
          function (resp) {
            if (resp.status) {
              Swal.fire('OK', resp.msg || 'Vértices eliminados', 'success');
              $tblVerticesBody.empty();
              addVerticeRow(1, '', '');
              addVerticeRow(2, '', '');
              addVerticeRow(3, '', '');
            } else {
              Swal.fire('Atención', resp.msg || 'No se pudieron eliminar los vértices', 'warning');
            }
          }
        );
      });
    });

    $formVertices.on('submit', function (e) {
      e.preventDefault();

      const idLote = $hiddenVertIdLote.val();
      if (!idLote) {
        Swal.fire('Atención', 'Lote inválido', 'warning');
        return;
      }

      const filas = [];
      $tblVerticesBody.find('tr').each(function () {
        const orden = $(this).find('input[name="orden[]"]').val();
        const lat   = $(this).find('input[name="lat[]"]').val();
        const lng   = $(this).find('input[name="lng[]"]').val();

        if ((lat + '').trim() === '' && (lng + '').trim() === '') return;
        filas.push({ orden, lat, lng });
      });

      if (filas.length < 3) {
        Swal.fire('Atención', 'Debes ingresar al menos 3 vértices válidos.', 'warning');
        return;
      }

      const data = $formVertices.serialize();

      ajaxJSON(
        'index.php?c=lotes&a=vertices_save',
        'POST',
        data,
        function (resp) {
          if (resp.status) {
            Swal.fire('OK', resp.msg || 'Vértices guardados', 'success');
            $modalVertices.modal('hide');
          } else {
            Swal.fire('Atención', resp.msg || 'No se pudieron guardar los vértices', 'warning');
          }
        }
      );
    });
  }

  // -------------------------------------------------
  // HISTORIAL POR LOTE (Admin)
  // -------------------------------------------------
  function abrirModalHistorialLote(row) {
    if (!$modalHistorial.length) {
      Swal.fire('Info', 'No se encontró el modal de historial del lote.', 'info');
      return;
    }

    const idLote = row.id;

    $lblHistorialLote.text(`Lote ${row.numero} – ${row.proyecto} / ${row.etapa} / ${row.manzana}`);
    $tblHistorialBody.empty().append(`
      <tr>
        <td colspan="5" class="text-muted text-center small">
          Cargando historial...
        </td>
      </tr>
    `);

    $.getJSON('index.php?c=lotes&a=historial_list', { id_lote: idLote })
      .done(function (resp) {
        $tblHistorialBody.empty();

        if (!resp || !resp.status || !Array.isArray(resp.data) || resp.data.length === 0) {
          $tblHistorialBody.append(`
            <tr>
              <td colspan="5" class="text-muted text-center small">
                No hay movimientos registrados para este lote.
              </td>
            </tr>
          `);
        } else {
          resp.data.forEach(h => {
            const fecha   = h.created_at || '';
            const usuario = h.usuario_nombre || '-';

            const estAnt  = h.estado_lote_anterior || '-';
            const estNvo  = h.estado_lote_nuevo || '-';
            const colEstado = `${estAnt} &rarr; ${estNvo}`;

            let cliAnt = '';
            let cliNvo = '';

            if (h.cli_ant_doc || h.cli_ant_nombre) {
              cliAnt = (h.cli_ant_doc ? h.cli_ant_doc + ' - ' : '') + (h.cli_ant_nombre || '');
            }
            if (h.cli_nvo_doc || h.cli_nvo_nombre) {
              cliNvo = (h.cli_nvo_doc ? h.cli_nvo_doc + ' - ' : '') + (h.cli_nvo_nombre || '');
            }

            let colCliente = '-';
            if (cliAnt || cliNvo) {
              colCliente = `${cliAnt || '(sin cliente)'} &rarr; ${cliNvo || '(sin cliente)'}`;
            }

            const motivo = h.motivo ? h.motivo : '—';

            const tr = `
              <tr>
                <td class="small">${fecha}</td>
                <td class="small">${usuario}</td>
                <td class="small">${colEstado}</td>
                <td class="small">${colCliente}</td>
                <td class="small">${motivo}</td>
              </tr>
            `;
            $tblHistorialBody.append(tr);
          });
        }

        $modalHistorial.modal('show');
      })
      .fail(function () {
        $tblHistorialBody.empty().append(`
          <tr>
            <td colspan="5" class="text-danger text-center small">
              Error al cargar el historial del lote.
            </td>
          </tr>
        `);
        Swal.fire('Error', 'No se pudo cargar el historial del lote.', 'error');
      });
  }

  // -------------------------------------------------
  // Creación MASIVA de lotes
  // -------------------------------------------------
  $('#btnAbrirMasivo').on('click', function () {
    if ($modalMasivo.length === 0) {
      Swal.fire('Info', 'No se encontró el modal de creación masiva.', 'info');
      return;
    }

    $formMasivo[0].reset();

    const $selProyecto = $formMasivo.find('[name="id_proyecto"]');
    const $selEtapa    = $formMasivo.find('[name="id_etapa"]');
    const $selManzana  = $formMasivo.find('[name="id_manzana"]');

    loadProyectos($selProyecto, 'Seleccione proyecto');

    $selProyecto.off('change').on('change', function () {
      const idP = $(this).val();
      loadEtapas($selEtapa, idP, 'Seleccione etapa');
      $selManzana
        .empty()
        .append($('<option>', { value: '' }).text('Seleccione manzana'))
        .trigger('change');
    });

    $selEtapa.off('change').on('change', function () {
      const idE = $(this).val();
      loadManzanas($selManzana, idE, 'Seleccione manzana');
    });

    $modalMasivo.modal('show');
  });

  $formMasivo.on('submit', function (e) {
    e.preventDefault();
    const formData = $formMasivo.serialize();

    ajaxJSON(
      'index.php?c=lotes&a=crear_masivos',
      'POST',
      formData,
      function (resp) {
        if (resp.status) {
          Swal.fire('OK', resp.msg || 'Lotes creados', 'success');
          $modalMasivo.modal('hide');
          recargarTabla();
        } else {
          Swal.fire('Atención', resp.msg || 'No se pudo crear los lotes', 'warning');
        }
      }
    );
  });

  // -------------------------------------------------
  // INIT
  // -------------------------------------------------
  initCombosFiltros();
  initDataTable();
});
