// public/assets/js/lotes_documentos.js
(function () {
  if (window.lotesDocumentosInitialized) return;
  window.lotesDocumentosInitialized = true;

  console.log('[lotes_documentos] cargado ✅');

  $(function () {
    const modalEl = document.getElementById('modalLoteDocumentos');
    if (!modalEl) {
      console.warn('[lotes_documentos] ❌ No existe #modalLoteDocumentos. Asegúrate de incluir app/Views/lotes/modals/documentos_lotes.php');
      return;
    }

    const modalDocumento = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });

    // ----------------------------
    // DOM
    // ----------------------------
    const $form = $('#formLoteDocumento');
    const $idLote = $('#doc_id_lote');

    const $lblLote = $('#doc_lbl_lote');
    const $lblProyecto = $('#doc_lbl_proyecto');
    const $lblCliente = $('#doc_lbl_cliente');

    // ✅ Soporta ambos IDs (tu modal tiene doc_lbl_estado_lote, tu JS antiguo usaba doc_lbl_estado)
    const $lblEstado = $('#doc_lbl_estado_lote').length ? $('#doc_lbl_estado_lote') : $('#doc_lbl_estado');

    // select principal (tipo_documento)
    const $tipoDocumento = $('#doc_tipo_documento');

    // ✅ NUEVO: tipo_persona (si no existe en el HTML lo creamos dinámicamente arriba del tipo_documento)
    let $tipoPersona = $('#doc_tipo_persona');

    // título y plantilla: en tu modal NO existen, pero tu controlador los espera → los aseguramos como inputs hidden
    let $titulo = $('#doc_titulo');
    let $plantilla = $('#doc_plantilla');

    const $camposContainer = $('#doc_campos_container');
    const $docsBody = $('#doc_docs_body');

    // ✅ Botón submit: tu HTML no tiene id btnGenerarDocumento → fallback robusto
    const $btnGenerar = $('#btnGenerarDocumento').length ? $('#btnGenerarDocumento') : $form.find('button[type="submit"]').first();

    const $resultContainer = $('#doc_result_container');
    const $resultView = $('#doc_result_view');
    const $resultHtml = $('#doc_result_html');
    const $resultPdf = $('#doc_result_pdf');

    const $alertReserva = $('#alert-reserva');

    // ----------------------------
    // Helpers
    // ----------------------------
    const esc = (s) => String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');

    const toastOk = (msg) => {
      if (window.toastr) toastr.success(msg || 'OK');
      else if (window.Swal) Swal.fire('OK', msg || 'OK', 'success');
      else alert(msg || 'OK');
    };

    const toastErr = (msg) => {
      if (window.toastr) toastr.error(msg || 'Error');
      else if (window.Swal) Swal.fire('Error', msg || 'Error', 'error');
      else alert(msg || 'Error');
    };

    const setLoadingDocs = (text) => {
      if (!$docsBody.length) return;
      $docsBody.html(`
        <tr>
          <td colspan="6" class="text-center text-muted small py-3">${esc(text || 'Cargando...')}</td>
        </tr>
      `);
    };

    const hideResultPanel = () => {
      if ($resultContainer.length) $resultContainer.addClass('d-none');
      if ($resultView.length) $resultView.attr('href', '#');
      if ($resultHtml.length) $resultHtml.attr('href', '#');
      if ($resultPdf.length) $resultPdf.attr('href', '#');
    };

    const showResultPanel = (docId) => {
      if (!$resultContainer.length || !docId) return;

      const viewUrl = `index.php?c=documentos&a=view&id=${encodeURIComponent(docId)}`;
      const htmlUrl = `index.php?c=documentos&a=download&id=${encodeURIComponent(docId)}&format=html`;
      const pdfUrl = `index.php?c=documentos&a=download&id=${encodeURIComponent(docId)}&format=pdf`;

      $resultView.attr('href', viewUrl);
      $resultHtml.attr('href', htmlUrl);
      $resultPdf.attr('href', pdfUrl);

      $resultContainer.removeClass('d-none');

      try { $resultContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
    };

    const extractDocId = (resp) => {
      if (!resp) return 0;
      if (resp.id) return parseInt(resp.id, 10) || 0;
      if (resp.data && resp.data.id) return parseInt(resp.data.id, 10) || 0;
      if (resp.documento_id) return parseInt(resp.documento_id, 10) || 0;
      return 0;
    };

    // ----------------------------
    // ✅ Asegurar inputs requeridos por backend
    // ----------------------------
    const ensureHiddenInput = (id, name) => {
      let $el = $('#' + id);
      if ($el.length) return $el;

      // si no existe lo creamos
      $el = $(`<input type="hidden" id="${esc(id)}" name="${esc(name)}">`);
      $form.append($el);
      return $el;
    };

    const initDynamicFields = () => {
      // titulo y plantilla (backend los espera)
      $titulo = ensureHiddenInput('doc_titulo', 'titulo');
      $plantilla = ensureHiddenInput('doc_plantilla', 'plantilla');

      // tipo_persona (no existe en tu modal actual → lo insertamos encima del select de tipo_documento)
      if (!$('#doc_tipo_persona').length) {
        const personaHtml = `
          <div class="mb-3" id="wrap_doc_tipo_persona">
            <label for="doc_tipo_persona" class="form-label">Tipo de persona</label>
            <select class="form-select form-select-sm" name="tipo_persona" id="doc_tipo_persona" required>
              <option value="">-- Seleccione --</option>
              <option value="PN">Persona Natural</option>
              <option value="PN_CONYUGE">Persona Natural con cónyuge</option>
              <option value="PJ">Persona Jurídica</option>
            </select>
            <div class="form-text">Primero elige el tipo de persona; luego se habilita el tipo de contrato.</div>
          </div>
        `;

        // intenta insertarlo antes del bloque del tipo_documento
        const $wrapTipoDoc = $tipoDocumento.closest('.mb-3');
        if ($wrapTipoDoc.length) $wrapTipoDoc.before(personaHtml);
        else $form.prepend(personaHtml);

        $tipoPersona = $('#doc_tipo_persona');
      } else {
        $tipoPersona = $('#doc_tipo_persona');
      }

      // cascada: tipo_documento inicia deshabilitado hasta elegir tipo_persona
      if ($tipoDocumento.length) {
        $tipoDocumento.prop('disabled', true);
      }
    };

    // ----------------------------
    // Render tabla documentos
    // ----------------------------
    const renderDocsTable = (rows) => {
      if (!$docsBody.length) return;

      if (!Array.isArray(rows) || rows.length === 0) {
        $docsBody.html(`
          <tr>
            <td colspan="6" class="text-center text-muted small py-3">
              Aún no hay documentos generados.
            </td>
          </tr>
        `);
        return;
      }

      const html = rows.map(r => {
        const vigenteBadge = (String(r.vigente) === '1')
          ? '<span class="badge bg-success">VIGENTE</span>'
          : '<span class="badge bg-secondary">HIST</span>';

        const docId = r.id ?? '';

        return `
          <tr>
            <td class="small text-muted">${esc(r.created_at || '')}</td>
            <td><span class="badge bg-dark">${esc(r.tipo_documento || '')}</span></td>
            <td>${esc(r.titulo || '')}</td>
            <td class="text-center">${vigenteBadge}</td>
            <td>${esc(r.usuario || '')}</td>
            <td class="text-end">
              <div class="btn-group btn-group-sm" role="group">
                <a class="btn btn-outline-primary" target="_blank" rel="noopener"
                   href="index.php?c=documentos&a=view&id=${esc(docId)}">Ver</a>
                <a class="btn btn-outline-secondary"
                   href="index.php?c=documentos&a=download&id=${esc(docId)}&format=html">HTML</a>
                <a class="btn btn-outline-secondary"
                   href="index.php?c=documentos&a=download&id=${esc(docId)}&format=pdf">PDF</a>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      $docsBody.html(html);
    };

    // ----------------------------
    // Cargar backend
    // ----------------------------
    const cargarInfoLote = (idLote) => {
      if (!idLote) return $.Deferred().resolve().promise();

      return $.getJSON('index.php?c=lotes&a=get&id=' + encodeURIComponent(idLote))
        .done(function (resp) {
          const d = (resp && resp.data) ? resp.data : resp;
          if (!d) return;

          const loteTxt = d.codigo || d.lote_codigo || d.lote_codigo_snapshot || d.numero || ('ID ' + idLote);
          const proyectoTxt = d.proyecto_nombre || d.proyecto || '—';
          const clienteTxt = d.cliente_nombre || d.cliente || d.cliente_fullname || '—';
          const estadoTxt = d.estado || d.estado_lote || '—';

          $lblLote.text(loteTxt);
          $lblProyecto.text(proyectoTxt);
          $lblCliente.text(clienteTxt);
          $lblEstado.text(estadoTxt);
        })
        .fail(function (xhr) {
          console.error('[docs] lotes/get fail', xhr.responseText);
        });
    };

    const cargarListaDocumentos = (idLote) => {
      if (!idLote) return;

      setLoadingDocs('Cargando documentos...');
      $alertReserva.html('');

      $.getJSON('index.php?c=documentos&a=list_by_lote&id_lote=' + encodeURIComponent(idLote))
        .done(function (resp) {
          if (Array.isArray(resp)) {
            renderDocsTable(resp);
            return;
          }

          if (resp && typeof resp === 'object') {
            if (resp.numero_documento || resp.nombres) {
              $lblCliente.html(`${esc(resp.numero_documento || '')} - ${esc(resp.nombres || '')} ${esc(resp.apellidos || '')}`);
            }

            if (String(resp.tipo_documento_generado || '').toUpperCase() === 'RESERVA' && resp.id_lote_documento) {
              $alertReserva.html(`
                <div class="alert alert-success d-flex flex-column align-items-center text-center" role="alert">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <h5 class="mb-0">¡Documento generado!</h5>
                  </div>

                  <button type="button"
                          id="btnDownloadPDF"
                          data-id="${esc(resp.id_lote_documento)}"
                          class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                    Ver / Descargar PDF
                  </button>
                </div>
              `);
            }

            const rows = resp.data || resp.rows || resp.items;
            if (Array.isArray(rows)) renderDocsTable(rows);
            else renderDocsTable([]);
            return;
          }

          renderDocsTable([]);
        })
        .fail(function (xhr) {
          console.error('[docs] list_by_lote fail', xhr.responseText);
          toastErr('No se pudo cargar la lista de documentos.');
          setLoadingDocs('Error al cargar documentos.');
        });
    };

    // ----------------------------
    // Campos por tipo (NO plantillas.html)
    // ----------------------------
    const renderCamposPorTipo = (tipo) => {
      let html = '';

      switch (tipo) {
        case 'RESERVA':
          html = `
            <div class="border rounded p-2">
              <div class="fw-semibold small mb-2">Datos de reserva</div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Monto</label>
                  <input type="number" step="0.01" min="0" name="pago_monto" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Medio</label>
                  <select name="pago_medio" class="form-select form-select-sm" required>
                    <option value="">--</option>
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    <option value="YAPE/PLIN">YAPE/PLIN</option>
                    <option value="OTRO">OTRO</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Fecha</label>
                  <input type="date" name="pago_fecha" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Plazo (días)</label>
                  <input type="number" min="1" name="reserva_plazo_dias" class="form-control form-control-sm" value="7" required>
                </div>
              </div>
            </div>
          `;
          break;

        case 'SEPARACION':
          html = `
            <div class="border rounded p-2">
              <div class="fw-semibold small mb-2">Datos de separación</div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Monto</label>
                  <input type="number" step="0.01" min="0" name="pago_monto" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Medio</label>
                  <select name="pago_medio" class="form-select form-select-sm" required>
                    <option value="">--</option>
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    <option value="YAPE/PLIN">YAPE/PLIN</option>
                    <option value="OTRO">OTRO</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Fecha</label>
                  <input type="date" name="pago_fecha" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Plazo (días)</label>
                  <input type="number" min="1" name="separacion_plazo_dias" class="form-control form-control-sm" value="30">
                </div>
              </div>
            </div>
          `;
          break;

        case 'COMPRAVENTA':
          html = `
            <div class="border rounded p-2">
              <div class="fw-semibold small mb-2">Datos de compraventa</div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Inicial</label>
                  <input type="number" step="0.01" min="0" name="precio_inicial_pago" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Medio</label>
                  <select name="pago_medio_inicial" class="form-select form-select-sm" required>
                    <option value="">--</option>
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    <option value="YAPE/PLIN">YAPE/PLIN</option>
                    <option value="OTRO">OTRO</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Fecha</label>
                  <input type="date" name="pago_fecha_inicial" class="form-control form-control-sm" required>
                </div>
              </div>
            </div>
          `;
          break;

        case 'ANULACION':
          html = `
            <div class="border rounded p-2">
              <div class="fw-semibold small mb-2">Datos de anulación</div>
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label form-label-sm">Motivo</label>
                  <input type="text" name="motivo_anulacion" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label form-label-sm">Tipo documento anulado</label>
                  <select name="tipo_documento_anulado" class="form-select form-select-sm" required>
                    <option value="RESERVA">RESERVA</option>
                    <option value="SEPARACION">SEPARACION</option>
                    <option value="COMPRAVENTA">COMPRAVENTA</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label form-label-sm">Fecha doc. anulado</label>
                  <input type="date" name="fecha_documento_anulado" class="form-control form-control-sm" required>
                </div>
              </div>
            </div>
          `;
          break;

        default:
          html = `
            <div class="alert alert-info small mb-0">
              Selecciona un tipo de documento para mostrar los campos necesarios.
            </div>
          `;
      }

      $camposContainer.html(html);
    };

    // ----------------------------
    // ✅ Resolver plantilla por (tipo_persona + tipo_documento)
    // ----------------------------
    const getTipoPersona = () => (String($tipoPersona.val() || '').trim().toUpperCase());
    const getTipoDocumento = () => (String($tipoDocumento.val() || '').trim().toUpperCase());

    const lockTipoDocumento = (locked) => {
      if (!$tipoDocumento.length) return;
      $tipoDocumento.prop('disabled', !!locked);
      if (locked) $tipoDocumento.val('');
    };

    const setPlantillaInfo = ({ id, asunto }) => {
      if (id) $plantilla.val(String(id));
      if (asunto && !$titulo.val()) $titulo.val(String(asunto)); // si ya escribiste titulo manual, no lo pisa
      if (asunto && $titulo.val().trim() === '') $titulo.val(String(asunto));
    };

    const limpiarPlantillaInfo = () => {
      $plantilla.val('');
      $titulo.val('');
    };

    // Endpoint esperado:
    // GET index.php?c=plantillas&a=resolve&tipo_documento=RESERVA&tipo_persona=PN
    const resolvePlantilla = () => {
      const tp = getTipoPersona();
      const td = getTipoDocumento();

      if (!tp || !td) {
        limpiarPlantillaInfo();
        return $.Deferred().resolve({ status: false, msg: 'incompleto' }).promise();
      }

      // feedback simple
      $btnGenerar.prop('disabled', true);
      $btnGenerar.data('orig-text', $btnGenerar.text());
      $btnGenerar.text('Cargando plantilla...');

      return $.getJSON('index.php?c=plantillas&a=resolve', {
        tipo_persona: tp,
        tipo_documento: td
      })
        .done(function (resp) {
          // Formatos aceptados:
          // {status:true, data:{id, asunto}}
          // {status:true, id, asunto}
          // {id, asunto}
          const ok = (resp && (resp.status === true || resp.id || (resp.data && resp.data.id)));
          const d = (resp && resp.data) ? resp.data : resp;

          if (!ok || !d || !d.id) {
            limpiarPlantillaInfo();
            toastErr(resp?.msg || 'No se encontró plantilla ACTIVA para esta combinación.');
            return;
          }

          setPlantillaInfo({ id: d.id, asunto: d.asunto });
        })
        .fail(function (xhr) {
          // Si aún no creaste el endpoint, no revienta el flujo, pero te avisa.
          console.warn('[plantillas/resolve] fail', xhr.status, xhr.responseText);
          limpiarPlantillaInfo();
          toastErr('No pude resolver la plantilla (endpoint plantillas/resolve no disponible).');
        })
        .always(function () {
          const orig = $btnGenerar.data('orig-text');
          $btnGenerar.prop('disabled', false);
          if (orig) $btnGenerar.text(orig);
        });
    };

    // ----------------------------
    // Abrir modal
    // ----------------------------
    const limpiarModal = () => {
      if ($form.length) $form[0].reset();

      $idLote.val('');
      $lblLote.text('—');
      $lblProyecto.text('—');
      $lblCliente.text('—');
      $lblEstado.text('—');

      $alertReserva.html('');

      $camposContainer.html(`
        <div class="alert alert-info small mb-0">
          Selecciona un tipo de documento para mostrar los campos necesarios.
        </div>
      `);

      setLoadingDocs('Selecciona un lote para ver sus documentos.');
      hideResultPanel();

      // cascada
      if ($tipoPersona && $tipoPersona.length) $tipoPersona.val('');
      lockTipoDocumento(true);
      limpiarPlantillaInfo();
    };

    const detectarIdLoteDesdeElemento = (el) => {
      let id =
        $(el).data('id') || $(el).data('lote') || $(el).data('id_lote') ||
        $(el).attr('data-id') || $(el).attr('data-lote') || $(el).attr('data-id_lote');

      id = parseInt(id, 10);
      if (id && !isNaN(id)) return id;

      const $tr = $(el).closest('tr');
      id = parseInt($tr.data('id') || $tr.data('lote') || $tr.data('id_lote'), 10);
      if (id && !isNaN(id)) return id;

      try {
        const tableEl = $tr.closest('table')[0];
        if (tableEl && $.fn.dataTable && $.fn.dataTable.isDataTable(tableEl)) {
          const dt = $(tableEl).DataTable();
          const rowData = dt.row($tr).data();
          const cand = rowData?.id || rowData?.id_lote || rowData?.lote_id || rowData?.ID || rowData?.Id;
          const parsed = parseInt(cand, 10);
          if (parsed && !isNaN(parsed)) return parsed;
        }
      } catch (e) {}

      return 0;
    };

    const abrirModalParaLote = (idLote) => {
      if (!idLote) return;
      limpiarModal();
      $idLote.val(idLote);

      modalDocumento.show();

      $.when(cargarInfoLote(idLote)).always(function () {
        cargarListaDocumentos(idLote);
      });
    };

    $(document).on('click', '.btnDocs, .btnDocumentos, .btn-documentos, .btnLoteDocs, [data-action="documentos"]', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const idLote = detectarIdLoteDesdeElemento(this);
      if (!idLote) return toastErr('No detecté el ID del lote.');
      abrirModalParaLote(idLote);
    });

    // fallback suave (menos agresivo)
    $(document).on('click', 'a,button', function (e) {
      const $el = $(this);
      if ($el.is('.btnDocs, .btnDocumentos, .btn-documentos, .btnLoteDocs, [data-action="documentos"]')) return;
      if ($el.closest('#modalLoteDocumentos, nav, .navbar, .topbar, header').length) return;

      const txt = ($el.text() || '').trim().toLowerCase();
      if (txt !== 'documentos') return;

      const idLote = detectarIdLoteDesdeElemento(this);
      if (!idLote) return;

      e.preventDefault();
      e.stopPropagation();
      abrirModalParaLote(idLote);
    });

    // ----------------------------
    // ✅ Cascada: tipo_persona -> habilita tipo_documento
    // ----------------------------
    const onPersonaChange = () => {
      hideResultPanel();
      renderCamposPorTipo(''); // limpia campos
      limpiarPlantillaInfo();

      const tp = getTipoPersona();
      if (!tp) {
        lockTipoDocumento(true);
        return;
      }

      lockTipoDocumento(false);
    };

    // ----------------------------
    // ✅ Al cambiar tipo_documento: render campos + resolver plantilla
    // ----------------------------
    const onDocumentoChange = () => {
      hideResultPanel();

      const tipo = getTipoDocumento();
      renderCamposPorTipo(tipo);

      // Resolver plantilla (id y asunto) según tipo_persona + tipo_documento
      resolvePlantilla();
    };

    // ----------------------------
    // submit
    // ----------------------------
    $form.on('submit', function (e) {
      e.preventDefault();

      const idLote = parseInt($idLote.val(), 10);
      const tipoPersona = getTipoPersona();
      const tipoDoc = getTipoDocumento();

      if (!idLote) return toastErr('Falta id_lote.');
      if (!tipoPersona) return toastErr('Selecciona el tipo de persona.');
      if (!tipoDoc) return toastErr('Selecciona el tipo de documento.');
      if (!$plantilla.val()) return toastErr('No hay plantilla resuelta (ID en BD).');

      hideResultPanel();

      $btnGenerar.prop('disabled', true).text('Generando...');

      $.ajax({
        url: 'index.php?c=documentos&a=save',
        method: 'POST',
        data: $form.serialize(), // incluye tipo_persona + tipo_documento + plantilla + titulo + campos
        dataType: 'json'
      })
        .done(function (resp) {
          if (!resp || resp.status !== true) {
            toastErr(resp?.msg || 'No se pudo generar el documento.');
            return;
          }

          const docId = extractDocId(resp);

          cargarListaDocumentos(idLote);
          if (docId) showResultPanel(docId);

          if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Documento generado', text: resp.msg || 'Listo', timer: 900, showConfirmButton: false });
          } else {
            toastOk(resp.msg || 'Documento generado');
          }
        })
        .fail(function (xhr) {
          console.error('[docs] save fail', xhr.responseText);
          toastErr('Error interno al guardar/generar.');
        })
        .always(function () {
          $btnGenerar.prop('disabled', false).text('Generar documento');
        });
    });

    // Ver/Descargar PDF (tu botón)
    $(document).on('click', '#btnDownloadPDF', function () {
      const documentId = $(this).data('id');
      if (!documentId) return;

      const redirectUrl = `index.php?c=documentos&a=generatePDF&documentID=${encodeURIComponent(documentId)}`;

      const width = 1200, height = 650;
      const left = (window.screen.width / 2) - (width / 2);
      const top = (window.screen.height / 2) - (height / 2);

      window.open(
        redirectUrl,
        '_blank',
        `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`
      );
    });

    // ----------------------------
    // Init
    // ----------------------------
    initDynamicFields();

    // bind events
    $(document).on('change', '#doc_tipo_persona', onPersonaChange);
    $tipoDocumento.on('change', onDocumentoChange);

    modalEl.addEventListener('hidden.bs.modal', limpiarModal);
    limpiarModal();
  });
})();
