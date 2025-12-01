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

  let dt = null;

  // -------------------------------------------------
  // Inicialización de filtros
  // -------------------------------------------------

  function initCombosFiltros() {
    loadProyectos($fProyecto, 'Todos los proyectos');

    $fProyecto.on('change', function () {
      const idProyecto = $(this).val();
      loadEtapas($fEtapa, idProyecto, 'Todas las etapas');
      $fManzana.empty().append($('<option>', { value: '' }).text('Todas las manzanas')).trigger('change');
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
          render: d => d ? 'S/ ' + parseFloat(d).toFixed(2) : ''
        },
        {
          data: 'factor_pct_total',
          title: 'Factor (%)',
          render: d => d !== null ? parseFloat(d).toFixed(2) + '%' : ''
        },
        {
          data: 'precio_final',
          title: 'Precio final',
          render: d => d ? 'S/ ' + parseFloat(d).toFixed(2) : ''
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
          render: (d, type, row) => d ? d : '-'
        },
        {
          data: null,
          title: 'Acciones',
          orderable: false,
          render: (data, type, row) => {
            return `
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-primary btn-edit-lote" data-id="${row.id}">Editar</button>
                <button class="btn btn-outline-success btn-estado-lote" data-id="${row.id}">Estado</button>
                <button class="btn btn-outline-danger btn-del-lote" data-id="${row.id}">Eliminar</button>
              </div>
            `;
          }
        }
      ]
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

    // combos dentro del modal
    const $selProyecto = $formLote.find('[name="id_proyecto"]');
    const $selEtapa    = $formLote.find('[name="id_etapa"]');
    const $selManzana  = $formLote.find('[name="id_manzana"]');

    loadProyectos($selProyecto, 'Seleccione proyecto');

    $selProyecto.off('change').on('change', function () {
      const idP = $(this).val();
      loadEtapas($selEtapa, idP, 'Seleccione etapa');
      $selManzana.empty().append($('<option>', { value: '' }).text('Seleccione manzana')).trigger('change');
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

          // cargar combos respetando selección
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
    // Primero obtenemos detalle del lote para saber estado actual
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

        // Opciones de estado para Admin (todas)
        const opciones = [
          'DISPONIBLE',
          'RESERVADO',
          'SEPARADO',
          'VENDIDO',
          'BLOQUEADO'
        ];

        let html = `
          <div class="mb-3 text-start">
            <label class="form-label">Estado actual</label>
            <div>${estadoBadge(estadoActual)}</div>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Nuevo estado</label>
            <select id="swalEstadoNuevo" class="form-select">
              <option value="">Seleccione estado</option>
        `;

        opciones.forEach(op => {
          html += `<option value="${op}">${op}</option>`;
        });

        html += `
            </select>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Cliente (obligatorio si el estado es distinto de DISPONIBLE)</label>
            <select id="swalCliente" class="form-select"></select>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Motivo / Nota</label>
            <textarea id="swalMotivo" class="form-control" rows="2" placeholder="Opcional, pero recomendable"></textarea>
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

            // Si ya tiene cliente, lo dejamos seleccionado visualmente (si coincide ID)
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
      $selManzana.empty().append($('<option>', { value: '' }).text('Seleccione manzana')).trigger('change');
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
