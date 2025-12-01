// public/assets/js/lotes_global.js
console.log('lotes_global.js cargado');

window.LOTES = window.LOTES || {};

$(function () {

  // -------------------------------------------------
  // Helper AJAX genérico (JSON + SweetAlert)
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
        let msg = 'Error interno al procesar la solicitud.';
        try {
          const json = JSON.parse(jqXHR.responseText);
          if (json.msg) msg = json.msg;
          if (json.error) msg = json.error;
        } catch (e) {}
        Swal.fire('Error', msg, 'error');
        if (onErr) onErr(jqXHR);
      });
  };

  // Hacemos accesible
  LOTES.ajaxJSON = ajaxJSON;

  // -------------------------------------------------
  // Helper robusto para llenar selects
  // -------------------------------------------------
  const fillSelect = ($select, items, config) => {
    config = config || {};
    const getValue = config.getValue || (r => r.id);
    const getText  = config.getText  || (r => r.nombre || r.label || ('#' + r.id));
    const placeholder = config.placeholder || null;

    // items puede venir como array puro o como {data:[...]}
    let list = [];
    if (Array.isArray(items)) {
      list = items;
    } else if (items && Array.isArray(items.data)) {
      list = items.data;
    } else {
      list = [];
    }

    $select.empty();

    if (placeholder !== null) {
      $select.append(
        $('<option>', { value: '' }).text(placeholder)
      );
    }

    list.forEach(row => {
      const val  = getValue(row);
      const text = getText(row);
      $select.append(
        $('<option>', { value: val }).text(text)
      );
    });

    // Si el select usa select2, refrescamos
    if ($select.hasClass('select2') || $select.data('select2')) {
      $select.trigger('change.select2');
    } else {
      $select.trigger('change');
    }
  };

  LOTES.fillSelect = fillSelect;

  // -------------------------------------------------
  // CARGA DE SELECTS (Proyectos, Etapas, Manzanas, Clientes)
  // -------------------------------------------------

  LOTES.loadProyectosSelect = ($select, placeholder) => {
    ajaxJSON(
      'index.php?c=api&a=proyectos_list',
      'GET',
      {},
      function (resp) {
        fillSelect($select, resp, {
          placeholder: placeholder || 'Todos los proyectos',
          getValue: r => r.id,
          getText: r => `${r.nombre} (${r.codigo})`
        });
      }
    );
  };

  LOTES.loadEtapasSelect = ($select, idProyecto, placeholder) => {
    if (!idProyecto) {
      fillSelect($select, [], { placeholder: placeholder || 'Todas las etapas' });
      return;
    }
    ajaxJSON(
      'index.php?c=api&a=etapas_list',
      'GET',
      { id_proyecto: idProyecto },
      function (resp) {
        fillSelect($select, resp, {
          placeholder: placeholder || 'Todas las etapas',
          getValue: r => r.id,
          getText: r => r.numero
            ? `${r.nombre} (N° ${r.numero})${r.habilitada_venta ? '' : ' [NO VENTA]'}`
            : `${r.nombre}${r.habilitada_venta ? '' : ' [NO VENTA]'}`
        });
      }
    );
  };

  LOTES.loadManzanasSelect = ($select, idEtapa, placeholder) => {
    if (!idEtapa) {
      fillSelect($select, [], { placeholder: placeholder || 'Todas las manzanas' });
      return;
    }
    ajaxJSON(
      'index.php?c=api&a=manzanas_list',
      'GET',
      { id_etapa: idEtapa },
      function (resp) {
        fillSelect($select, resp, {
          placeholder: placeholder || 'Todas las manzanas',
          getValue: r => r.id,
          getText: r => r.descripcion
            ? `${r.codigo} - ${r.descripcion}`
            : r.codigo
        });
      }
    );
  };

  LOTES.loadClientesSelect = ($select, placeholder) => {
    ajaxJSON(
      'index.php?c=api&a=clientes_select',
      'GET',
      {},
      function (resp) {
        fillSelect($select, resp, {
          placeholder: placeholder || 'Seleccione cliente',
          getValue: r => r.id,
          getText: r => r.label
        });
      }
    );
  };

  // -------------------------------------------------
  // Helpers visuales
  // -------------------------------------------------

  LOTES.formatEstadoLoteBadge = (estado) => {
    if (!estado) return '<span class="badge bg-secondary">N/D</span>';
    const e = estado.toUpperCase();
    let cls = 'bg-secondary';

    switch (e) {
      case 'DISPONIBLE': cls = 'bg-success'; break;
      case 'RESERVADO':  cls = 'bg-warning text-dark'; break;
      case 'SEPARADO':   cls = 'bg-info text-dark'; break;
      case 'VENDIDO':    cls = 'bg-primary'; break;
      case 'BLOQUEADO':  cls = 'bg-danger'; break;
    }

    return `<span class="badge ${cls}">${e}</span>`;
  };

});
