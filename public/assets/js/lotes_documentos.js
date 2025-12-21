// public/assets/js/lotes_documentos.js
// Módulo Documentos de Lotes (ADMIN) - versión estable y completa
// Requiere: jQuery, Bootstrap 5, SweetAlert2 (opcional), Toastr (opcional), DataTables (opcional)

(function () {
  if (window.lotesDocumentosInitialized) return;
  window.lotesDocumentosInitialized = true;

  console.log('[lotes_documentos] cargado ✅');

  $(function () {
    // ----------------------------
    // Referencias DOM (modal actual)
    // ----------------------------
    const modalEl = document.getElementById('modalLoteDocumentos');
    if (!modalEl) {
      console.warn('[lotes_documentos] ❌ No existe #modalLoteDocumentos. Revisa include de app/Views/lotes/modals/documentos.php');
      return;
    }

    const modalDocumento = new bootstrap.Modal(modalEl, {
      backdrop: true,
      keyboard: true
    });

    const $form            = $('#formLoteDocumento');
    const $idLote          = $('#doc_id_lote');

    const $lblLote         = $('#doc_lbl_lote');
    const $lblProyecto     = $('#doc_lbl_proyecto');
    const $lblCliente      = $('#doc_lbl_cliente');
    const $lblEstado       = $('#doc_lbl_estado'); // agregado en tu modal

    const $tipoDocumento   = $('#doc_tipo_documento');
    const $titulo          = $('#doc_titulo');
    const $plantilla       = $('#doc_plantilla');

    const $camposContainer = $('#doc_campos_container');
    const $docsBody        = $('#doc_docs_body');

    const $btnGenerar      = $('#btnGenerarDocumento'); // existe en tu modal

    // Panel “Documento generado”
    const $resultContainer = $('#doc_result_container');
    const $resultView      = $('#doc_result_view');
    const $resultHtml      = $('#doc_result_html');
    const $resultPdf       = $('#doc_result_pdf');

    // Si no existe el tbody, nunca veremos la lista
    if (!$docsBody.length) {
      console.warn('[lotes_documentos] ❌ No existe #doc_docs_body dentro del modal. No hay dónde renderizar la lista.');
    }

    // ----------------------------
    // Helpers
    // ----------------------------
    function esc(s) {
      return String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function toastOk(msg) {
      if (window.toastr) toastr.success(msg || 'OK');
      else if (window.Swal) Swal.fire('OK', msg || 'OK', 'success');
      else alert(msg || 'OK');
    }

    function toastErr(msg) {
      if (window.toastr) toastr.error(msg || 'Error');
      else if (window.Swal) Swal.fire('Error', msg || 'Error', 'error');
      else alert(msg || 'Error');
    }

    function setLoadingDocs(text) {
      if (!$docsBody.length) return;
      $docsBody.html(`
        <tr>
          <td colspan="6" class="text-center text-muted small py-3">
            ${esc(text || 'Cargando...')}
          </td>
        </tr>
      `);
    }

    function hideResultPanel() {
      if ($resultContainer.length) $resultContainer.addClass('d-none');
      if ($resultView.length) $resultView.attr('href', '#');
      if ($resultHtml.length) $resultHtml.attr('href', '#');
      if ($resultPdf.length) $resultPdf.attr('href', '#');
    }

    function showResultPanel(docId) {
      if (!$resultContainer.length) return;
      if (!docId) return;

      const viewUrl = `index.php?c=documentos&a=view&id=${encodeURIComponent(docId)}`;
      const htmlUrl = `index.php?c=documentos&a=download&id=${encodeURIComponent(docId)}&format=html`;
      const pdfUrl  = `index.php?c=documentos&a=download&id=${encodeURIComponent(docId)}&format=pdf`;

      if ($resultView.length) $resultView.attr('href', viewUrl);
      if ($resultHtml.length) $resultHtml.attr('href', htmlUrl);
      if ($resultPdf.length) $resultPdf.attr('href', pdfUrl);

      $resultContainer.removeClass('d-none');

      // Scroll suave para que el usuario lo vea
      try {
        $resultContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      } catch (e) { /* no-op */ }
    }

    function limpiarModal() {
      if ($form.length) $form[0].reset();

      $idLote.val('');

      if ($lblLote.length)     $lblLote.text('—');
      if ($lblProyecto.length) $lblProyecto.text('—');
      if ($lblCliente.length)  $lblCliente.text('—');
      if ($lblEstado.length)   $lblEstado.text('—');

      if ($camposContainer.length) {
        $camposContainer.html(`
          <div class="alert alert-info small mb-0">
            Selecciona un tipo de documento para mostrar los campos necesarios.
          </div>
        `);
      }

      setLoadingDocs('Selecciona un lote para ver sus documentos.');
      hideResultPanel();
    }

    // Normaliza respuesta: [] o {status,data:[]} o {rows:[]}
    function normalizeRows(resp) {
      if (Array.isArray(resp)) return resp;
      if (resp && typeof resp === 'object') {
        if (Array.isArray(resp.data)) return resp.data;
        if (Array.isArray(resp.rows)) return resp.rows;
        if (Array.isArray(resp.items)) return resp.items;
      }
      return [];
    }

    // Extrae id documento: {id} o {data:{id}}
    function extractDocId(resp) {
      if (!resp) return 0;
      if (resp.id) return parseInt(resp.id, 10) || 0;
      if (resp.data && resp.data.id) return parseInt(resp.data.id, 10) || 0;
      if (resp.documento_id) return parseInt(resp.documento_id, 10) || 0;
      return 0;
    }

    // ----------------------------
    // Render tabla documentos
    // ----------------------------
    function renderDocsTable(rows) {
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
    }

    // ----------------------------
    // Cargar datos desde backend
    // ----------------------------
    function cargarListaDocumentos(idLote) {
      if (!idLote) return;
      console.log('[docs] cargando list_by_lote, idLote=', idLote);
      setLoadingDocs('Cargando documentos...');

      $.getJSON('index.php?c=documentos&a=list_by_lote&id_lote=' + encodeURIComponent(idLote))
        .done(function (resp) {
          // fillDataPerson(resp);
          console.log(resp)

          if (resp) {
            $("#doc_lbl_cliente").html(`${resp.numero_documento} - ${resp.nombres} ${resp.apellidos}`);

            if(resp.tipo_documento_generado == "RESERVA"){
              $("#alert-reserva").html(`
                <div class="alert alert-success d-flex flex-column align-items-center text-center" role="alert">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <h3>¡Felicitaciones, tu documento ha sido generado!</h3>
                  </div>

                  <button type="button" id="btnDownloadPDF" data-id="${resp.id_lote_documento}" class="btn btn-primary btn-lg mt-3" download>
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                    Descargar PDF
                  </button>
                </div>
              `);
            }else{
              $("#alert-reserva").html("");
            }
          }

          // const rows = normalizeRows(resp);
          // console.log('[docs] respuesta list_by_lote:', resp, '=> rows:', rows.length);
          // renderDocsTable(rows);
        })
        .fail(function (xhr) {
          console.error('[docs] list_by_lote fail', xhr.responseText);
          toastErr('No se pudo cargar la lista de documentos.');
          setLoadingDocs('Error al cargar documentos.');
        });
    }

    function cargarInfoLote(idLote) {
      if (!idLote) return $.Deferred().resolve().promise();

      return $.getJSON('index.php?c=lotes&a=get&id=' + encodeURIComponent(idLote))
        .done(function (resp) {
          // puede venir envuelto {status,data}
          const d = (resp && resp.data) ? resp.data : resp;
          if (!d) return;

          // Ajuste flexible de nombres (para no amarrarnos a un solo key)
          const loteTxt = d.codigo || d.lote_codigo || d.lote_codigo_snapshot || d.numero || ('ID ' + idLote);
          const proyectoTxt = d.proyecto_nombre || d.proyecto || '—';
          const clienteTxt = d.cliente_nombre || d.cliente || d.cliente_fullname || '—';
          const estadoTxt = d.estado || d.estado_lote || '—';

          if ($lblLote.length)     $lblLote.text(loteTxt);
          if ($lblProyecto.length) $lblProyecto.text(proyectoTxt);
          if ($lblCliente.length)  $lblCliente.text(clienteTxt);
          if ($lblEstado.length)   $lblEstado.text(estadoTxt);
        })
        .fail(function (xhr) {
          console.error('[docs] lotes/get fail', xhr.responseText);
        });
    }

    // ----------------------------
    // Form dinámico por tipo
    // (Importante: nombres coherentes con tu JSON histórico)
    // ----------------------------
    function renderCamposPorTipo(tipo) {
      if (!$camposContainer.length) return;

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
                  <input type="number" step="0.01" min="0" name="sep_monto" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Medio</label>
                  <select name="sep_medio" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    <option value="YAPE/PLIN">YAPE/PLIN</option>
                    <option value="OTRO">OTRO</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Fecha</label>
                  <input type="date" name="sep_fecha" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Plazo (días)</label>
                  <input type="number" min="1" name="sep_plazo_dias" class="form-control form-control-sm" value="7">
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
                  <label class="form-label form-label-sm">Precio total</label>
                  <input type="number" step="0.01" min="0" name="cv_precio_total" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Inicial</label>
                  <input type="number" step="0.01" min="0" name="cv_inicial" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Saldo</label>
                  <input type="number" step="0.01" min="0" name="cv_saldo" class="form-control form-control-sm">
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
                <div class="col-md-8">
                  <label class="form-label form-label-sm">Motivo</label>
                  <input type="text" name="an_motivo" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label form-label-sm">Fecha</label>
                  <input type="date" name="an_fecha" class="form-control form-control-sm" required>
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
    }

    // Plantilla por defecto (si está vacío)
    function aplicarPlantillaPorDefecto(tipo) {
      if (!$plantilla.length) return;

      if ($plantilla.val()) return;

      const map = {
        'RESERVA': 'contrato_reserva_natural.html',
        'SEPARACION': 'contrato_separacion_natural.html',
        'COMPRAVENTA': 'contrato_compraventa_natural.html',
        'ANULACION': 'anulacion_operacion.html'
      };

      if (map[tipo]) $plantilla.val(map[tipo]);
    }

    // ----------------------------
    // Detectar idLote (DOM + DataTables)
    // ----------------------------
    function detectarIdLoteDesdeElemento(el) {
      // 1) data-* directo
      let id =
        $(el).data('id') || $(el).data('lote') || $(el).data('id_lote') ||
        $(el).attr('data-id') || $(el).attr('data-lote') || $(el).attr('data-id_lote');

      id = parseInt(id, 10);
      if (id && !isNaN(id)) return id;

      // 2) desde <tr>
      const $tr = $(el).closest('tr');
      id = $tr.data('id') || $tr.data('lote') || $tr.data('id_lote');
      id = parseInt(id, 10);
      if (id && !isNaN(id)) return id;

      // 3) DataTables row().data()
      try {
        const tableEl = $tr.closest('table')[0];
        if (tableEl && $.fn.dataTable && $.fn.dataTable.isDataTable(tableEl)) {
          const dt = $(tableEl).DataTable();
          const rowData = dt.row($tr).data();
          const cand = rowData?.id || rowData?.id_lote || rowData?.lote_id || rowData?.ID || rowData?.Id;
          const parsed = parseInt(cand, 10);
          if (parsed && !isNaN(parsed)) return parsed;
        }
      } catch (e) {
        // no-op
      }

      return 0;
    }

    // ----------------------------
    // ABRIR MODAL: listener robusto
    // - prioridad: si existe data-action="documentos" o clases típicas
    // - fallback: texto "Documentos" (pero evitando navbar)
    // ----------------------------
    function abrirModalParaLote(idLote) {
      if (!idLote) return;

      limpiarModal();
      $idLote.val(idLote);

      // abrir modal primero para feedback visual
      modalDocumento.show();

      // cargar data
      $.when(cargarInfoLote(idLote)).always(function () {
        cargarListaDocumentos(idLote);
      });
    }

    // Listener 1: clases/atributos
    $(document).on('click', '.btnDocs, .btnDocumentos, .btn-documentos, .btnLoteDocs, [data-action="documentos"]', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const idLote = detectarIdLoteDesdeElemento(this);
      console.log('[docs click selector] idLote=', idLote);

      if (!idLote) {
        toastErr('No detecté el ID del lote (data-id / row / DataTables).');
        return;
      }

      abrirModalParaLote(idLote);
    });

    // Listener 2: fallback por texto “Documentos” (evita menu superior)
    $(document).on('click', 'a,button', function (e) {
      const $el = $(this);

      // si ya lo capturó el listener 1, no repetimos
      if ($el.is('.btnDocs, .btnDocumentos, .btn-documentos, .btnLoteDocs, [data-action="documentos"]')) return;

      // evita capturar clicks dentro del modal
      if ($el.closest('#modalLoteDocumentos').length) return;

      const txt = ($el.text() || '').trim().toLowerCase();
      if (!txt || !txt.includes('documentos')) return;

      // evita navbar/topbar: si está en header/nav, lo ignoramos
      if ($el.closest('nav, .navbar, .topbar, header').length) return;

      e.preventDefault();
      e.stopPropagation();

      const idLote = detectarIdLoteDesdeElemento(this);
      console.log('[docs click fallback] idLote=', idLote);

      if (!idLote) return; // si no hay id, no molestamos
      abrirModalParaLote(idLote);
    });

    // ----------------------------
    // Cambio de tipo: mostrar formulario dinámico (EL BUG QUE TENÍAS)
    // ----------------------------
    $tipoDocumento.on('change', function () {
      const tipo = $(this).val();
      console.log('[docs] cambio tipo_documento=', tipo);

      hideResultPanel(); // si cambias tipo, ocultamos panel de resultado anterior
      renderCamposPorTipo(tipo);
      aplicarPlantillaPorDefecto(tipo);

      // título sugerido si está vacío
      if ($titulo.length && !$titulo.val() && tipo) {
        $titulo.val('Documento ' + tipo);
      }
    });

    // ----------------------------
    // Submit: generar documento
    // ----------------------------
    $form.on('submit', function (e) {
      e.preventDefault();

      const idLote = parseInt($idLote.val(), 10);
      const tipo = ($tipoDocumento.val() || '').trim();

      if (!idLote) return toastErr('Falta id_lote.');
      if (!tipo) return toastErr('Selecciona el tipo de documento.');

      // plantilla por defecto si no eligieron
      aplicarPlantillaPorDefecto(tipo);

      // título por defecto si no pusieron
      if ($titulo.length && !$titulo.val()) $titulo.val('Documento ' + tipo);

      hideResultPanel();

      // UX: bloquear botón
      if ($btnGenerar.length) {
        $btnGenerar.prop('disabled', true).text('Generando...');
      }

      $.ajax({
        url: 'index.php?c=documentos&a=save',
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json'
      })
        .done(function (resp) {
          if (!resp || resp.status !== true) {
            toastErr(resp?.msg || 'No se pudo generar el documento.');
            return;
          }

          const docId = extractDocId(resp);

          // Recargar lista
          cargarListaDocumentos(idLote);

          // Mostrar panel de “Documento generado” si tenemos ID
          if (docId) {
            showResultPanel(docId);
          }

          // Mensaje corto
          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Documento generado',
              text: resp.msg || 'Listo',
              timer: 1000,
              showConfirmButton: false
            });
          } else {
            toastOk(resp.msg || 'Documento generado');
          }
        })
        .fail(function (xhr) {
          console.error('[docs] save fail', xhr.responseText);
          toastErr('Error interno al guardar/generar.');
        })
        .always(function () {
          if ($btnGenerar.length) {
            $btnGenerar.prop('disabled', false).text('Generar documento');
          }
        });
    });

    // Descargar PDF
    $(document).on("click", "#btnDownloadPDF", function(){
      const documentId = $(this).data("id");
      const redirectUrl = `index.php?c=documentos&a=generatePDF&documentID=${documentId}`;

      // Tamaño de la ventana
      const width = 1200;   // ancho en píxeles
      const height = 650;  // alto en píxeles

      // Posición centrada en la pantalla
      const left = (window.screen.width / 2) - (width / 2);
      const top = (window.screen.height / 2) - (height / 2);

      // Abrir nueva ventana centrada
      window.open(
          redirectUrl,
          '_blank',
          `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`
      );
    });

    // Limpieza al cerrar modal
    modalEl.addEventListener('hidden.bs.modal', function () {
      limpiarModal();
    });

    // Estado inicial
    limpiarModal();
  });
})();
