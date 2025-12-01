// public/assets/js/clientes.js
console.log('clientes.js cargado');

$(function () {

  // -------------------------------------------------
  // Helpers
  // -------------------------------------------------
  const ajaxJSON = (url, method, data, onOk, onErr) => {
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
        console.error('AJAX error', textStatus, errorThrown, jqXHR.responseText);
        if (onErr) onErr(jqXHR);
        Swal.fire('Error', 'Error interno', 'error');
      });
  };

  // -------------------------------------------------
  // Modales
  // -------------------------------------------------
  const modalClienteEl = document.getElementById('modalCliente');
  const modalCliente = modalClienteEl ? new bootstrap.Modal(modalClienteEl) : null;

  const modalCopropEl = document.getElementById('modalCopropietarios');
  const modalCoprop = modalCopropEl ? new bootstrap.Modal(modalCopropEl) : null;

  // -------------------------------------------------
  // UBIGEO CLIENTES (igual lógica que PERSONAS, búsqueda local)
  // -------------------------------------------------
  let UBIGEOS_CLIENTES_CACHE = null;

  function cargarUbigeosCliente(selectedId = null) {
    const $ubigeo = $('#cli_id_ubigeo');
    if (!$ubigeo.length) return;

    const inicializarSelect2 = () => {
      // Reiniciar si ya estaba inicializado
      if ($ubigeo.hasClass('select2-hidden-accessible')) {
        $ubigeo.select2('destroy');
      }

      $ubigeo.select2({
        dropdownParent: $('#modalCliente'),
        width: '100%',
        placeholder: 'Buscar ubigeo...',
        allowClear: true
      });
    };

    const llenarDesdeCache = () => {
      $ubigeo.empty();
      $ubigeo.append('<option value="">-- Ubigeo --</option>');

      (UBIGEOS_CLIENTES_CACHE || []).forEach(row => {
        $ubigeo.append(
          $('<option>', {
            value: row.id,
            text: row.descripcion
          })
        );
      });

      if (selectedId) {
        $ubigeo.val(String(selectedId));
      } else {
        $ubigeo.val('');
      }

      inicializarSelect2();
    };

    // Si ya tenemos la cache, no volvemos a pedir al servidor
    if (UBIGEOS_CLIENTES_CACHE) {
      llenarDesdeCache();
      return;
    }

    // Primera vez: pedimos al API y guardamos en cache
    $.get('index.php?c=api&a=ubigeos_list', function (resp) {
      let data;
      try {
        data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
      } catch (e) {
        console.error('Error parseando ubigeos_list:', resp);
        return;
      }

      UBIGEOS_CLIENTES_CACHE = data || [];
      llenarDesdeCache();
    }).fail(xhr => {
      console.error('Error cargando ubigeos_list:', xhr.status, xhr.responseText);
    });
  }

  // -------------------------------------------------
  // Cargar lista de usuarios para "Responsable"
  // -------------------------------------------------
  const cargarResponsables = () => {
    const $sel = $('#cli_id_user_responsable');
    if (!$sel.length) return;

    ajaxJSON('index.php?c=api&a=users_list', 'GET', {}, function (rows) {
      $sel.empty();
      $sel.append('<option value="">-- Sin responsable --</option>');
      (rows || []).forEach(u => {
        $sel.append(
          $('<option>', {
            value: u.id,
            text: `${u.fullname} (${u.role})`
          })
        );
      });

      // Si viene valor precargado en el input hidden, selecciónalo
      const valActual = $sel.data('value');
      if (valActual) {
        $sel.val(valActual);
      }
    });
  };

  cargarResponsables();

  // -------------------------------------------------
  // DataTable de clientes
  // -------------------------------------------------
  let tblClientes = $('#tblClientes').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: 'index.php?c=clientes&a=list',
      dataSrc: 'data'
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id' },
      { data: 'numero_documento' },
      {
        data: null,
        render: function (row) {
          if (row.tipo_persona == 2) {
            return row.razon_social || '';
          }
          const nom = row.nombres || '';
          const ape = row.apellidos || '';
          return (nom + ' ' + ape).trim();
        }
      },
      { data: 'tipo_cliente' },
      { data: 'nivel_interes' },
      { data: 'telefono' },
      { data: 'estado' },
      { data: 'responsable' },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          return `
            <button class="btn btn-sm btn-primary me-1 btn-edit" data-id="${row.id}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-danger me-1 btn-delete" data-id="${row.id}">
              <i class="bi bi-trash"></i>
            </button>
            <button class="btn btn-sm btn-secondary btn-coprop" data-id="${row.id}" data-nombre="${(row.nombres || '') + ' ' + (row.apellidos || row.razon_social || '')}">
              <i class="bi bi-people"></i>
            </button>
          `;
        }
      }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    }
  });

  // -------------------------------------------------
  // Filtros (Documento, Tipo cliente, Nivel interés, Estado)
  // -------------------------------------------------
  $('#filtroDocumento').on('keyup change', function () {
    const val = this.value || '';
    // Columna 1 = Documento
    tblClientes.column(1).search(val).draw();
  });

  $('#filtroTipoCliente').on('change', function () {
    const val = this.value || '';
    // Columna 3 = Tipo cliente
    tblClientes.column(3).search(val).draw();
  });

  $('#filtroNivelInteres').on('change', function () {
    const val = this.value || '';
    // Columna 4 = Nivel interés
    tblClientes.column(4).search(val).draw();
  });

  $('#filtroEstado').on('change', function () {
    const val = this.value || '';
    // Columna 6 = Estado
    tblClientes.column(6).search(val).draw();
  });

  // -------------------------------------------------
  // Limpiar formulario cliente
  // -------------------------------------------------
  const limpiarFormCliente = () => {
    $('#formCliente')[0].reset();
    $('#cli_id').val('');
    $('#cli_id_user').val(APP.currentUser ? APP.currentUser.id : '');

    // Ubigeo: limpiar selección (cuando ya exista select2)
    const $ubigeo = $('#cli_id_ubigeo');
    if ($ubigeo.length && $ubigeo.hasClass('select2-hidden-accessible')) {
      $ubigeo.val('').trigger('change');
    }

    // responsable
    $('#cli_id_user_responsable').val('').trigger('change');
  };

  // -------------------------------------------------
  // Nuevo cliente
  // -------------------------------------------------
  $('#btnNewCliente').on('click', function () {
    limpiarFormCliente();
    $('#modalClienteLabel').text('Nuevo cliente');

    // Cargar ubigeos sin selección previa
    cargarUbigeosCliente(null);

    if (modalCliente) modalCliente.show();
  });

  // -------------------------------------------------
  // Editar cliente
  // -------------------------------------------------
  $('#tblClientes').on('click', '.btn-edit', function () {
    const id = $(this).data('id');
    if (!id) return;

    ajaxJSON('index.php?c=clientes&a=get', 'GET', { id }, function (row) {
      if (!row) {
        Swal.fire('Atención', 'No se encontró el cliente', 'warning');
        return;
      }

      limpiarFormCliente();
      $('#modalClienteLabel').text('Editar cliente');

      $('#cli_id').val(row.id);
      $('#cli_id_user').val(row.id_user || (APP.currentUser ? APP.currentUser.id : ''));

      $('#cli_tipo_persona').val(row.tipo_persona);
      $('#cli_tipo_documento').val(row.tipo_documento);
      $('#cli_numero_documento').val(row.numero_documento);
      $('#cli_estado_civil').val(row.estado_civil);

      $('#cli_nombres').val(row.nombres);
      $('#cli_apellidos').val(row.apellidos);
      $('#cli_razon_social').val(row.razon_social);

      $('#cli_fecha_nacimiento').val(row.fecha_nacimiento);
      $('#cli_telefono').val(row.telefono);
      $('#cli_telefono_alt').val(row.telefono_alt);
      $('#cli_email').val(row.email);

      $('#cli_direccion').val(row.direccion);
      $('#cli_referencia_direccion').val(row.referencia_direccion);

      $('#cli_tipo_cliente').val(row.tipo_cliente);
      $('#cli_nivel_interes').val(row.nivel_interes);
      $('#cli_medio_contacto').val(row.medio_contacto_preferido);
      $('#cli_origen').val(row.origen);
      $('#cli_estado').val(row.estado);
      $('#cli_observaciones').val(row.observaciones);
      $('#cli_fecha_ultimo_contacto').val(row.fecha_ultimo_contacto);

      // Responsable
      $('#cli_id_user_responsable')
        .data('value', row.id_user_responsable)
        .val(row.id_user_responsable || '')
        .trigger('change');

      // Ubigeo: usamos la misma función para poblar y preseleccionar
      cargarUbigeosCliente(row.id_ubigeo);

      if (modalCliente) modalCliente.show();
    });
  });

  // -------------------------------------------------
  // Guardar cliente (submit form)
  // -------------------------------------------------
  $('#formCliente').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    ajaxJSON('index.php?c=clientes&a=save', 'POST', formData, function (resp) {
      if (!resp) {
        Swal.fire('Error', 'Respuesta vacía del servidor', 'error');
        return;
      }
      if (resp.status) {
        Swal.fire('Éxito', resp.msg || 'Cliente guardado', 'success');
        if (modalCliente) modalCliente.hide();
        tblClientes.ajax.reload(null, false);
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo guardar el cliente', 'error');
      }
    });
  });

  // -------------------------------------------------
  // Eliminar cliente
  // -------------------------------------------------
  $('#tblClientes').on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    if (!id) return;

    Swal.fire({
      title: '¿Eliminar cliente?',
      text: 'El registro será marcado como inactivo.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (!result.isConfirmed) return;

      ajaxJSON('index.php?c=clientes&a=delete', 'POST', { id }, function (resp) {
        if (resp.status) {
          Swal.fire('Éxito', resp.msg || 'Cliente eliminado', 'success');
          tblClientes.ajax.reload(null, false);
        } else {
          Swal.fire('Error', resp.msg || 'No se pudo eliminar el cliente', 'error');
        }
      });
    });
  });

  // -------------------------------------------------
  // Copropietarios (abrir modal)
  // -------------------------------------------------
  $('#tblClientes').on('click', '.btn-coprop', function () {
    const id = $(this).data('id');
    const nombre = $(this).data('nombre') || '';
    if (!id) return;

    $('#coprop_id_cliente').val(id);
    $('#coprop_form_id_cliente').val(id);
    $('#coprop_cliente_nombre').text(nombre);

    cargarCopropietarios(id);

    if (modalCoprop) modalCoprop.show();
  });

  // -------------------------------------------------
  // Tabla simple de copropietarios (sin DataTable)
  // -------------------------------------------------
  const renderCopropRow = (cp) => {
    const nombre = ((cp.nombres || '') + ' ' + (cp.apellidos || '')).trim();
    return `
      <tr>
        <td>${cp.id}</td>
        <td>${cp.numero_documento || ''}</td>
        <td>${nombre || ''}</td>
        <td>${cp.parentesco || ''}</td>
        <td>${cp.porcentaje_participacion || ''}</td>
        <td>${cp.estado || ''}</td>
        <td>
          <button class="btn btn-sm btn-primary me-1 btn-coprop-edit" data-id="${cp.id}">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn btn-sm btn-danger btn-coprop-del" data-id="${cp.id}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  };

  const cargarCopropietarios = (idCliente) => {
    ajaxJSON('index.php?c=clientes&a=coprop_list', 'GET', { id_cliente: idCliente }, function (rows) {
      const $tbody = $('#tblCopropietarios tbody');
      $tbody.empty();
      (rows || []).forEach(cp => {
        $tbody.append(renderCopropRow(cp));
      });
    });
  };

  // -------------------------------------------------
  // Nuevo copropietario
  // -------------------------------------------------
  $('#btnCopropNuevo').on('click', function () {
    $('#formCopropietario')[0].reset();
    $('#coprop_id').val('');
  });

  // -------------------------------------------------
  // Editar copropietario
  // -------------------------------------------------
  $('#tblCopropietarios').on('click', '.btn-coprop-edit', function () {
    const id = $(this).data('id');
    if (!id) return;

    ajaxJSON('index.php?c=clientes&a=coprop_get', 'GET', { id }, function (cp) {
      if (!cp) {
        Swal.fire('Atención', 'No se encontró el copropietario', 'warning');
        return;
      }

      $('#coprop_id').val(cp.id);
      $('#coprop_form_id_cliente').val(cp.id_cliente);
      $('#coprop_tipo_persona').val(cp.tipo_persona);
      $('#coprop_tipo_documento').val(cp.tipo_documento);
      $('#coprop_numero_documento').val(cp.numero_documento);
      $('#coprop_parentesco').val(cp.parentesco);
      $('#coprop_nombres').val(cp.nombres);
      $('#coprop_apellidos').val(cp.apellidos);
      $('#coprop_fecha_nacimiento').val(cp.fecha_nacimiento);
      $('#coprop_telefono').val(cp.telefono);
      $('#coprop_email').val(cp.email);
      $('#coprop_direccion').val(cp.direccion);
      $('#coprop_porcentaje').val(cp.porcentaje_participacion);
      $('#coprop_estado').val(cp.estado);
      $('#coprop_observaciones').val(cp.observaciones);
    });
  });

  // -------------------------------------------------
  // Guardar copropietario
  // -------------------------------------------------
  $('#formCopropietario').on('submit', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    ajaxJSON('index.php?c=clientes&a=coprop_save', 'POST', formData, function (resp) {
      if (resp.status) {
        Swal.fire('Éxito', resp.msg || 'Copropietario guardado', 'success');
        const idCliente = $('#coprop_form_id_cliente').val();
        cargarCopropietarios(idCliente);
        $('#formCopropietario')[0].reset();
        $('#coprop_id').val('');
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo guardar el copropietario', 'error');
      }
    });
  });

  // -------------------------------------------------
  // Eliminar copropietario
  // -------------------------------------------------
  $('#tblCopropietarios').on('click', '.btn-coprop-del', function () {
    const id = $(this).data('id');
    if (!id) return;

    Swal.fire({
      title: '¿Eliminar copropietario?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (!result.isConfirmed) return;

      ajaxJSON('index.php?c=clientes&a=coprop_delete', 'POST', { id }, function (resp) {
        if (resp.status) {
          Swal.fire('Éxito', resp.msg || 'Copropietario eliminado', 'success');
          const idCliente = $('#coprop_form_id_cliente').val();
          cargarCopropietarios(idCliente);
        } else {
          Swal.fire('Error', resp.msg || 'No se pudo eliminar el copropietario', 'error');
        }
      });
    });
  });

});
