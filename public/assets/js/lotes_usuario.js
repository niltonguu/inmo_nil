// public/assets/js/lotes_usuario.js
console.log('lotes_usuario.js cargado');

$(function () {

  const ajaxJSON        = LOTES.ajaxJSON;
  const loadProyectos   = LOTES.loadProyectosSelect;
  const loadEtapas      = LOTES.loadEtapasSelect;
  const loadManzanas    = LOTES.loadManzanasSelect;
  const loadClientesSel = LOTES.loadClientesSelect;
  const estadoBadge     = LOTES.formatEstadoLoteBadge;

  const $fProyecto = $('#filtroProyectoUsr');
  const $fEtapa    = $('#filtroEtapaUsr');
  const $fManzana  = $('#filtroManzanaUsr');
  const $fEstado   = $('#filtroEstadoUsr');

  const $tabla     = $('#tablaLotesUsr');

  let dt = null;

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

  function initDataTable() {
    dt = $tabla.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: 'index.php?c=lotes&a=list_usuario',
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
          render: (d) => d ? d : '-'
        },
        {
          data: null,
          title: 'Acciones',
          orderable: false,
          render: (data, type, row) => {
            return `
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-success btn-estado-lote-usr" data-id="${row.id}">Cambiar estado</button>
              </div>
            `;
          }
        }
      ]
    });

    $tabla.on('click', '.btn-estado-lote-usr', function () {
      const id = $(this).data('id');
      cambiarEstadoSwalUsuario(id);
    });
  }

  function recargarTabla() {
    if (dt) dt.ajax.reload(null, true);
  }

  // -------------------------------------------------
  // Cambio de estado (Vendedor)
  // Backend ya restringe el flujo:
  // DISPONIBLE -> RESERVADO -> SEPARADO -> VENDIDO
  // -------------------------------------------------

  function cambiarEstadoSwalUsuario(idLote) {
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
        let opciones = [];

        // Flujo permitido desde el backend, pero acá guiamos al usuario:
        switch (estadoActual) {
          case 'DISPONIBLE':
            opciones = ['RESERVADO'];
            break;
          case 'RESERVADO':
            opciones = ['SEPARADO'];
            break;
          case 'SEPARADO':
            opciones = ['VENDIDO'];
            break;
          default:
            // Si es VENDIDO u otro, que no pueda avanzar más
            Swal.fire('Info', 'No hay cambios de estado permitidos desde este estado.', 'info');
            return;
        }

        let html = `
          <div class="mb-3 text-start">
            <label class="form-label">Estado actual</label>
            <div>${estadoBadge(estadoActual)}</div>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Nuevo estado</label>
            <select id="swalEstadoNuevoUsr" class="form-select">
        `;

        opciones.forEach(op => {
          html += `<option value="${op}">${op}</option>`;
        });

        html += `
            </select>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Cliente</label>
            <select id="swalClienteUsr" class="form-select"></select>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label">Motivo / Nota</label>
            <textarea id="swalMotivoUsr" class="form-control" rows="2" placeholder="Ej: cliente confirmó separación, envío boleta, etc."></textarea>
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
            const $cli = $('#swalClienteUsr');
            loadClientesSel($cli, 'Seleccione cliente');

            if (lote.id_cliente) {
              setTimeout(() => {
                $cli.val(lote.id_cliente).trigger('change');
              }, 300);
            }
          },
          preConfirm: () => {
            const estadoNuevo = $('#swalEstadoNuevoUsr').val();
            const idCliente   = $('#swalClienteUsr').val();
            const motivo      = $('#swalMotivoUsr').val().trim();

            if (!estadoNuevo) {
              Swal.showValidationMessage('Seleccione el nuevo estado');
              return false;
            }

            if (!idCliente) {
              Swal.showValidationMessage('Debe seleccionar un cliente');
              return false;
            }

            return { estadoNuevo, idCliente, motivo };
          }
        }).then(result => {
          if (!result.isConfirmed || !result.value) return;

          const payload = {
            id_lote:      idLote,
            estado_nuevo: result.value.estadoNuevo,
            id_cliente:   result.value.idCliente,
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
  // INIT
  // -------------------------------------------------

  initCombosFiltros();
  initDataTable();

});
