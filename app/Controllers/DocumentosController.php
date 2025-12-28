<?php
// app/Controllers/DocumentosController.php

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/DocumentosModel.php';
require_once __DIR__ . '/../Models/PlantillasModel.php';
require_once __DIR__ . '/../Models/ClientesModel.php';
require_once __DIR__ . '/../Models/LotesModel.php';
require_once __DIR__ . '/../Models/ProyectosModel.php';
require_once __DIR__ . '/../Models/EmpresasModel.php';
require_once __DIR__ . '/../Helpers/NumberHelper.php';
require_once __DIR__ . '/../Helpers/helper.php';
require_once __DIR__ . '/../Config/Database.php'; // ✅ Re-usa tu Database.php (no “nueva conexión rara”)

class DocumentosController extends Controller
{
    private DocumentosModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=loginForm');
            exit;
        }

        $this->model = new DocumentosModel();
    }

    // =========================================================
    // Helpers base
    // =========================================================
    private function jsonResponse($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    private function currentUserRole(): string
    {
        return (string)($_SESSION['user']['role'] ?? '');
    }

    private function requireAdmin(): void
    {
        if ($this->currentUserRole() !== 'admin') {
            $this->jsonResponse(['status' => false, 'msg' => 'No autorizado'], 403);
        }
    }

    private function normTipoDocumento(string $tipo): string
    {
        $t = strtoupper(trim($tipo));
        $allow = ['RESERVA', 'SEPARACION', 'COMPRAVENTA', 'ANULACION'];
        return in_array($t, $allow, true) ? $t : '';
    }

    private function normTipoPersona(string $tp): string
    {
        $t = strtoupper(trim($tp));
        $allow = ['PN', 'PN_CONYUGE', 'PJ'];
        return in_array($t, $allow, true) ? $t : '';
    }

    /**
     * Resuelve ID de plantilla de forma PRO:
     * - Si llega plantilla numérica => se respeta (compatibilidad / históricos)
     * - Si no llega => se busca en BD por (tipo_documento + tipo_persona) y estado=ACTIVO
     */
    private function resolvePlantillaId(string $tipoDocumento, string $tipoPersona, $plantRaw): int
    {
        // 1) Compat: si llegó numérico o string numérico
        if ($plantRaw !== null && $plantRaw !== '') {
            $cand = is_numeric($plantRaw) ? (int)$plantRaw : 0;
            if ($cand > 0) return $cand;

            // si llegó algo como "ID:6" o "6|..."
            if (is_string($plantRaw) && preg_match('/(\d+)/', $plantRaw, $m)) {
                $cand2 = (int)($m[1] ?? 0);
                if ($cand2 > 0) return $cand2;
            }
        }

        // 2) Nuevo flujo: resolver por tipo_documento + tipo_persona
        $tipoDocumento = $this->normTipoDocumento($tipoDocumento);
        $tipoPersona   = $this->normTipoPersona($tipoPersona);

        if ($tipoDocumento === '' || $tipoPersona === '') return 0;

        try {
            $pdo = (new Database())->connect();
            $sql = "SELECT id
                    FROM plantillas_doc
                    WHERE tipo_documento = :td
                      AND tipo_persona   = :tp
                      AND estado         = 'ACTIVO'
                    ORDER BY id DESC
                    LIMIT 1";
            $st = $pdo->prepare($sql);
            $st->execute([':td' => $tipoDocumento, ':tp' => $tipoPersona]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    // =========================================================
    // Rutas JSON
    // =========================================================

    // GET: index.php?c=documentos&a=list_by_lote&id_lote=123
    public function list_by_lote(): void
    {
        $this->requireAdmin();

        $idLote = (int)($_GET['id_lote'] ?? 0);
        if ($idLote <= 0) $this->jsonResponse([]);

        $rows = $this->model->listByLote($idLote);
        $this->jsonResponse($rows);
    }

    // POST: index.php?c=documentos&a=save
    // Guarda en BD y genera HTML persistido para view/download
    public function save(): void
    {
        $this->requireAdmin();

        $idUsuario = $this->currentUserId();
        if ($idUsuario <= 0) {
            $this->jsonResponse(['status' => false, 'msg' => 'Sesión inválida'], 401);
        }

        $idLote = (int)($_POST['id_lote'] ?? 0);
        $tipo   = $this->normTipoDocumento((string)($_POST['tipo_documento'] ?? ''));
        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $tpers  = $this->normTipoPersona((string)($_POST['tipo_persona'] ?? '')); // ✅ nuevo: PN / PN_CONYUGE / PJ

        // 👇 Compat: por si tu modal viejo no mandaba tipo_persona todavía
        if ($tpers === '') $tpers = 'PN';

        $plantRaw = $_POST['plantilla'] ?? ''; // puede venir o no
        $plantId  = $this->resolvePlantillaId($tipo, $tpers, $plantRaw);

        if ($idLote <= 0) $this->jsonResponse(['status' => false, 'msg' => 'Falta id_lote']);
        if ($tipo === '') $this->jsonResponse(['status' => false, 'msg' => 'Tipo de documento inválido']);
        if ($plantId <= 0) $this->jsonResponse([
            'status' => false,
            'msg' => 'No se encontró una plantilla ACTIVA para: ' . $tipo . ' / ' . $tpers
        ]);

        if ($titulo === '') $titulo = 'Documento ' . $tipo;

        // Armamos datos_json con TODO lo que venga en POST, excepto campos base
        $payload = $_POST;
        unset($payload['id_lote'], $payload['tipo_documento'], $payload['titulo'], $payload['plantilla']);

        // ✅ Guardamos también tipo_persona dentro del JSON (sirve para históricos)
        $payload['tipo_persona'] = $tpers;

        $data = [
            'id_lote'        => $idLote,
            'tipo_documento' => $tipo,
            'titulo'         => $titulo,
            'plantilla'      => (string)$plantId, // se guarda ID plantilla (numérico)
            'datos'          => $payload,         // va a datos_json
        ];

        $resp = $this->model->saveDocumento($data, $idUsuario);
        if (!($resp['status'] ?? false)) $this->jsonResponse($resp);

        // Generar HTML persistido (para view/download)
        $docId = (int)($resp['id'] ?? 0);
        if ($docId > 0) {
            $gen = $this->generarHTMLDesdePlantillaBD($docId);
            if ($gen['status']) {
                $resp['archivo_path'] = $gen['archivo_path'];
                $resp['url_view'] = "index.php?c=documentos&a=view&id=" . $docId;
                $resp['url_download_html'] = "index.php?c=documentos&a=download&id=" . $docId . "&format=html";
                $resp['url_download_pdf']  = "index.php?c=documentos&a=download&id=" . $docId . "&format=pdf";
            } else {
                $resp['msg'] .= ' (guardado, pero no se pudo generar el HTML: ' . ($gen['msg'] ?? 'error') . ')';
            }
        }

        $this->jsonResponse($resp);
    }
    // =========================================================
    // View / Download
    // =========================================================

    // GET: index.php?c=documentos&a=view&id=123
    public function view(): void
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(404); echo "Documento no encontrado"; exit; }

        $doc = $this->model->getById($id);
        if (!$doc) { http_response_code(404); echo "Documento no encontrado"; exit; }

        // Si no hay archivo_path, lo generamos
        if (empty($doc['archivo_path'])) {
            $gen = $this->generarHTMLDesdePlantillaBD($id);
            if ($gen['status']) $doc['archivo_path'] = $gen['archivo_path'];
        }

        $rel = (string)($doc['archivo_path'] ?? '');
        if ($rel === '') { http_response_code(500); echo "No hay HTML generado para este documento."; exit; }

        $abs = $this->publicPath($rel);
        if (!is_file($abs)) { http_response_code(404); echo "Archivo no encontrado"; exit; }

        header('Content-Type: text/html; charset=utf-8');
        readfile($abs);
        exit;
    }

    // GET: index.php?c=documentos&a=download&id=123&format=html|pdf
    public function download(): void
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $format = strtolower(trim((string)($_GET['format'] ?? 'html')));

        if ($id <= 0) { http_response_code(404); echo "Documento no encontrado"; exit; }

        $doc = $this->model->getById($id);
        if (!$doc) { http_response_code(404); echo "Documento no encontrado"; exit; }

        if ($format === 'pdf') {
            $this->outputPDFTCPDF($id);
            exit;
        }

        // HTML download
        if (empty($doc['archivo_path'])) {
            $gen = $this->generarHTMLDesdePlantillaBD($id);
            if ($gen['status']) $doc['archivo_path'] = $gen['archivo_path'];
        }

        $rel = (string)($doc['archivo_path'] ?? '');
        if ($rel === '') { http_response_code(500); echo "No hay HTML generado para este documento."; exit; }

        $abs = $this->publicPath($rel);
        if (!is_file($abs)) { http_response_code(404); echo "Archivo no encontrado"; exit; }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', (string)($doc['tipo_documento'] ?? 'documento'));
        $safeName .= "_v" . (int)($doc['version'] ?? 1) . ".html";

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        readfile($abs);
        exit;
    }

    // =========================================================
    // Motor HTML (plantilla desde BD)
    // =========================================================

    private function generarHTMLDesdePlantillaBD(int $docId): array
    {
        $doc = $this->model->getById($docId);
        if (!$doc) return ['status' => false, 'msg' => 'Documento no existe'];

        // 1) datos_json (lo que viene del modal / formulario)
        $datos = [];
        if (!empty($doc['datos_json'])) {
            $tmp = json_decode((string)$doc['datos_json'], true);
            if (is_array($tmp)) $datos = $tmp;
        }
        $datos['fecha_actual'] = $datos['fecha_actual'] ?? date('d/m/Y');

        // 2) Resolver plantilla (retrocompatible)
        $tipoDoc   = $this->normTipoDocumento((string)($doc['tipo_documento'] ?? ''));
        $tipoPers  = $this->normTipoPersona((string)($datos['tipo_persona'] ?? '')) ?: 'PN';
        $plantillaID = $this->resolvePlantillaId($tipoDoc, $tipoPers, $doc['plantilla'] ?? '');

        if ($plantillaID <= 0) return ['status' => false, 'msg' => 'Plantilla inválida / no encontrada'];

        // 3) datos "del sistema" por plantilla (empresa/proyecto/lote/cliente/pagos/etc.)
        $idLote = (int)($doc['id_lote'] ?? 0);
        $baseReplace = [];
        if ($idLote > 0) {
            $baseReplace = $this->getDataReplaceByPlantilla($plantillaID, $idLote);
            if (!is_array($baseReplace)) $baseReplace = [];
        }

        // 4) Compatibilidad con alias (plantillas antiguas)
        $compat = [
            'cliente_nombre'      => $baseReplace['cliente_nombre_completo'] ?? ($baseReplace['cliente_nombre'] ?? ''),
            'cliente_apellidos'   => $baseReplace['cliente_apellidos'] ?? '',
            'cliente_nombres'     => $baseReplace['cliente_nombres'] ?? '',
            'cliente_dni'         => $baseReplace['cliente_documento_numero'] ?? ($baseReplace['cliente_dni'] ?? ''),
            'cliente_documento'   => trim(($baseReplace['cliente_documento_tipo'] ?? '') . ' ' . ($baseReplace['cliente_documento_numero'] ?? '')),
            'cliente_direccion'   => $baseReplace['cliente_direccion'] ?? '',
            'cliente_estado_civil'=> $baseReplace['cliente_estado_civil'] ?? '',

            'conyuge_nombre'      => $baseReplace['conyuge_nombre_completo'] ?? '',
            'conyuge_dni'         => $baseReplace['conyuge_documento_numero'] ?? '',
            'conyuge_documento'   => trim(($baseReplace['conyuge_documento_tipo'] ?? '') . ' ' . ($baseReplace['conyuge_documento_numero'] ?? '')),
            'conyuge_direccion'   => $baseReplace['conyuge_direccion'] ?? '',

            'empresa_razon'       => $baseReplace['proyecto_empresa_razon_social'] ?? ($baseReplace['empresa_razon_social'] ?? ''),
            'empresa_ruc'         => $baseReplace['proyecto_empresa_ruc'] ?? ($baseReplace['empresa_ruc'] ?? ''),
            'empresa_direccion'   => $baseReplace['proyecto_direccion'] ?? ($baseReplace['empresa_direccion'] ?? ''),
            'representante_nombre'=> $baseReplace['proyecto_representante_nombre'] ?? '',
            'representante_documento' => $baseReplace['proyecto_representante_documento'] ?? '',

            'proyecto_nombre'     => $baseReplace['proyecto_nombre'] ?? '',
            'lote_codigo'         => $baseReplace['lote_codigo'] ?? '',
            'lote_manzana'        => $baseReplace['lote_manzana'] ?? '',
            'lote_etapa'          => $baseReplace['lote_etapa'] ?? '',
            'lote_area'           => $baseReplace['lote_area_m2'] ?? ($baseReplace['lote_area'] ?? ''),
            'lote_precio_final'   => $baseReplace['lote_precio_final'] ?? '',
            'lote_precio_m2'      => $baseReplace['lote_precio_m2'] ?? '',

            'pago_monto'          => $baseReplace['pago_monto'] ?? '',
            'pago_fecha'          => $baseReplace['pago_fecha'] ?? '',
            'pago_medio'          => $baseReplace['pago_medio'] ?? '',
            'pago_recibo_numero'  => $baseReplace['pago_recibo_numero'] ?? '',
            'precio_total_letras' => $baseReplace['precio_total_letras'] ?? '',
            'precio_saldo'        => $baseReplace['precio_saldo'] ?? '',
            'precio_saldo_letras' => $baseReplace['precio_saldo_letras'] ?? '',

            'copropietarios_bloque'=> $baseReplace['copropietarios_bloque'] ?? '',
            'lote_vertices_tabla'  => $baseReplace['lote_vertices_tabla'] ?? '',
            'lote_vertices_texto'  => $baseReplace['lote_vertices_texto'] ?? '',
        ];

        // 5) Prioridad final: sistema -> compat -> datos_json (última palabra)
        $dataReplace = array_merge($baseReplace, $compat, $datos);

        // Obtener plantilla desde BD
        $plantillaModel = new PlantillasModel();
        $plantilla = $plantillaModel->getById($plantillaID);
        if (!$plantilla) return ['status' => false, 'msg' => 'Plantilla no encontrada en BD'];

        $template = (string)($plantilla['contenido'] ?? '');
        if ($template === '') return ['status' => false, 'msg' => 'Contenido de plantilla vacío'];

        // Render con negritas
        $rendered = $this->renderTemplateBold($template, $dataReplace);

        // Convertir a HTML (justificado)
        $html = $this->wrapAsHtml($doc, $rendered);

        // Guardar en /public/uploads/documentos/lote_{idLote}/doc_{id}_v{ver}.html
        $version = (int)($doc['version'] ?? 1);

        $relDir = "uploads/documentos/lote_" . $idLote;
        $absDir = $this->publicPath($relDir);

        if (!is_dir($absDir)) {
            if (!@mkdir($absDir, 0775, true)) {
                return ['status' => false, 'msg' => 'No se pudo crear carpeta de documentos'];
            }
        }

        $fileRel = $relDir . "/doc_" . $docId . "_v" . $version . ".html";
        $fileAbs = $this->publicPath($fileRel);

        if (@file_put_contents($fileAbs, $html) === false) {
            return ['status' => false, 'msg' => 'No se pudo escribir el archivo HTML'];
        }

        // Persistir ruta
        $this->model->setArchivoPath($docId, $fileRel);

        return ['status' => true, 'archivo_path' => $fileRel];
    }

    private function renderTemplateBold(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $template = str_replace('{{' . $key . '}}', '<b>' . (string)$value . '</b>', $template);
        }
        return $template;
    }
    private function wrapAsHtml(array $doc, string $rendered): string
    {
        $titulo = htmlspecialchars((string)($doc['titulo'] ?? 'Documento'), ENT_QUOTES, 'UTF-8');

        $looksHtml = (stripos($rendered, '<html') !== false) || (stripos($rendered, '<body') !== false);
        if ($looksHtml) return $rendered;

        $body = nl2br($rendered);

        return "<!doctype html>
<html lang='es'>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width,initial-scale=1'>
  <title>{$titulo}</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:24px;color:#111}
    .wrap{max-width:900px;margin:0 auto}
    .titulo{font-size:18px;font-weight:700;text-align:center;margin-bottom:18px}
    .contenido{font-size:13.5px;line-height:1.55;text-align:justify}
    b{font-weight:700}
  </style>
</head>
<body>
  <div class='wrap'>
    <div class='titulo'>{$titulo}</div>
    <div class='contenido'>{$body}</div>
  </div>
</body>
</html>";
    }

    // =========================================================
    // PDF oficial con TCPDF (download ?format=pdf)
    // =========================================================
    private function outputPDFTCPDF(int $documentID): void
    {
        $documento = $this->model->getById($documentID);
        if (!$documento) { http_response_code(404); echo "Documento no encontrado"; exit; }

        // ✅ Garantiza que el HTML final exista (MISMA lógica que view/download)
        if (empty($documento['archivo_path'])) {
            $gen = $this->generarHTMLDesdePlantillaBD($documentID);
            if ($gen['status']) $documento['archivo_path'] = $gen['archivo_path'];
        }

        $rel = (string)($documento['archivo_path'] ?? '');
        if ($rel === '') { http_response_code(500); echo "No hay HTML generado para este documento."; exit; }

        $abs = $this->publicPath($rel);
        if (!is_file($abs)) { http_response_code(404); echo "Archivo HTML no encontrado"; exit; }

        $html = (string)@file_get_contents($abs);
        if ($html === '') { http_response_code(500); echo "HTML vacío"; exit; }

        // TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator('Sistema');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Documento');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Render directo del HTML final (más fiel)
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', (string)($documento['tipo_documento'] ?? 'documento'));
        $safeName .= "_v" . (int)($documento['version'] ?? 1) . ".pdf";

        $pdf->Output($safeName, 'D');
        exit;
    }

    // Mantengo la ruta pública que ya tenías (por compatibilidad)
    public function generatePDF(): void
    {
        $this->requireAdmin();
        $documentID = (int)($_GET['documentID'] ?? 0);
        if ($documentID <= 0) { http_response_code(404); echo "Documento no encontrado"; exit; }

        $this->outputPDFTCPDF($documentID);
    }

    private function getDataReplaceByPlantilla(int $plantillaID, int $loteID): array
    {
        switch ($plantillaID) {
            case 1:  return $this->fillDataTemplateAnulacionPN($loteID);

            case 2:  return $this->fillDataTemplateCompraventaPJ($loteID);
            case 3:  return $this->fillDataTemplateCompraventaPN($loteID);
            case 4:  return $this->fillDataTemplateCompraventaPNConyuge($loteID);

            case 5:  return $this->fillDataTemplateReservaPJ($loteID);
            case 6:  return $this->fillDataTemplateReservaPN($loteID);
            case 7:  return $this->fillDataTemplateReservaPNConyuge($loteID);

            case 8:  return $this->fillDataTemplateSeparacionPJ($loteID);
            case 9:  return $this->fillDataTemplateSeparacionPN($loteID);
            case 10: return $this->fillDataTemplateSeparacionPNConyuge($loteID);

            default: return [];
        }
    }

    // =========================================================
    // Utilidad path public
    // =========================================================
    private function publicPath(string $relative): string
    {
        $pub = realpath(__DIR__ . '/../../public');
        if (!$pub) $pub = __DIR__ . '/../../public';
        $relative = ltrim($relative, '/');
        return rtrim($pub, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }
    // =========================================================
    // ---------------------------------------------------------
    // ABAJO QUEDAN TUS fillDataTemplate* (tal cual ya estaban)
    // (No los toco para no romper lo ya funcionando)
    // ---------------------------------------------------------
    // =========================================================

    /**
     * Helpers internos
     */
    private function docTipoTexto(int $tipo): string
    {
        return ($tipo === 1) ? 'DNI' : (($tipo === 2) ? 'CE' : 'DOC');
    }

    private function pickConyugeFromCopropietarios(array $copropietarios): ?array
    {
        foreach ($copropietarios as $c) {
            if (strtoupper(trim($c['parentesco'] ?? '')) === 'CONYUGE' && strtoupper(trim($c['estado'] ?? '')) === 'ACTIVO') {
                return $c;
            }
        }
        foreach ($copropietarios as $c) {
            if (strtoupper(trim($c['parentesco'] ?? '')) === 'CONYUGE') {
                return $c;
            }
        }
        return null;
    }

    private function generarBloqueCopropietarios(array $copropietarios, $excluir = null): string
    {
        if (empty($copropietarios)) return '';

        $bloque = '';
        $numero = 3;

        foreach ($copropietarios as $cop) {
            if ($excluir && ($cop['id'] ?? 0) === ($excluir['id'] ?? -1)) continue;

            $nombreCompleto = trim(($cop['nombres'] ?? '') . ' ' . ($cop['apellidos'] ?? ''));
            if ($nombreCompleto === '') continue;

            $docTipo = $this->docTipoTexto((int)($cop['tipo_documento'] ?? 1));
            $docNum  = $cop['numero_documento'] ?? '';

            $dir = trim((string)($cop['direccion'] ?? ''));
            if ($dir === '') $dir = 'el mismo que el comprador principal';

            $bloque .= "{$numero}. {$nombreCompleto}, identificado(a) con {$docTipo} N.º {$docNum}, con domicilio en {$dir}.\n\n";
            $numero++;
        }

        return $bloque;
    }

    private function ctxByLote(int $loteID): array
    {
        $info = $this->model->listByLote($loteID) ?: [];
        if (isset($info[0]) && is_array($info[0])) $info = $info[0];

        $clientModel = new ClientesModel();
        $cliente = $clientModel->getClienteById((int)($info['id_cliente'] ?? 0)) ?: [];
        $copropietarios = $clientModel->getCopropietariosByCliente((int)($cliente['id'] ?? 0)) ?: [];

        $documentID = (int)($info['id_lote_documento'] ?? ($info['id'] ?? 0));
        $document = $documentID ? ($this->model->getById($documentID) ?: []) : [];

        $loteRealID = (int)($info['id_lote'] ?? $loteID);

        $loteModel = new LotesModel();
        $lote = $loteModel->getLoteById($loteRealID) ?: [];

        $proyectoID = (int)($lote['id_proyecto'] ?? 0);
        $proyectoModel = new ProyectosModel();
        $proyecto = $proyectoModel->getProyectoById($proyectoID) ?: [];

        $json = (object)[];
        if (!empty($document['datos_json'])) {
            $tmp = json_decode((string)$document['datos_json']);
            if (is_object($tmp)) $json = $tmp;
        }

        $empresaID = (int)($proyecto['id_empresa'] ?? 1);
        $empModel = new EmpresasModel();
        $empresa = $empModel->getEmpresa($empresaID) ?: [];

        $rep = null;
        $reps = $empModel->listRepresentantes($empresaID) ?: [];
        if (!empty($reps)) {
            foreach ($reps as $r) {
                if (($r['estado'] ?? '') === 'ACTIVO') { $rep = $r; break; }
            }
            if (!$rep) $rep = $reps[0];
        }

        return [
            'info' => $info,
            'cliente' => $cliente,
            'copropietarios' => $copropietarios,
            'document' => $document,
            'json' => $json,
            'lote' => $lote,
            'proyecto' => $proyecto,
            'empresa' => $empresa,
            'rep' => $rep,
            'loteModel' => $loteModel,
        ];
    }

        // ============================================================
    // BASES (OBLIGATORIAS)
    // ============================================================

    private function baseCommon(int $loteID): array
    {
        $ctx = $this->ctxByLote($loteID);

        $lote    = $ctx['lote'] ?? [];
        $proy    = $ctx['proyecto'] ?? [];
        $empresa = $ctx['empresa'] ?? [];
        $rep     = $ctx['rep'] ?? [];

        // Lote / Proyecto
        $loteCodigo  = $lote['codigo'] ?? ($lote['lote_codigo'] ?? '');
        $manzana     = $lote['manzana'] ?? ($lote['mz'] ?? '');
        $etapa       = $lote['etapa'] ?? '';
        $areaM2      = $lote['area_m2'] ?? ($lote['area'] ?? '');
        $precioFinal = $lote['precio_final'] ?? '';

        // Empresa / Representante
        $repNombre = trim(($rep['nombres'] ?? '') . ' ' . ($rep['apellidos'] ?? ''));
        if ($repNombre === '') $repNombre = (string)($rep['nombre'] ?? '');

        $repDocTipo = $rep['tipo_documento'] ?? ($rep['doc_tipo'] ?? 'DNI');
        $repDocNum  = $rep['numero_documento'] ?? ($rep['doc_num'] ?? '');
        $repDocFull = trim($repDocTipo . ' ' . $repDocNum);

        return [
            // Proyecto / empresa (nombres que ya usas en plantillas)
            'proyecto_nombre' => $proy['nombre'] ?? '',
            'proyecto_direccion' => $proy['direccion'] ?? ($empresa['direccion'] ?? ''),

            'proyecto_empresa_razon_social' => $empresa['razon_social'] ?? ($empresa['nombre'] ?? ''),
            'proyecto_empresa_ruc' => $empresa['ruc'] ?? '',
            'proyecto_representante_nombre' => $repNombre,
            'proyecto_representante_documento' => $repDocFull,

            // Lote
            'lote_codigo' => $loteCodigo,
            'lote_manzana' => $manzana,
            'lote_etapa' => $etapa,
            'lote_area_m2' => $areaM2,
            'lote_precio_final' => $precioFinal,
            'lote_precio_m2' => $lote['precio_m2'] ?? '',

            // Extras útiles
            '_lote' => $lote,
            '_proyecto' => $proy,
            '_empresa' => $empresa,
            '_rep' => $rep,
        ];
    }

    private function basePagos(int $loteID): array
    {
        // Tus JSON del documento ya traen pago_monto, pago_fecha, etc.
        // Aquí solo devolvemos placeholders para que no falle si no existen.
        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'] ?? (object)[];

        $monto = (float)($json->pago_monto ?? 0);
        $saldo = (float)($json->precio_saldo ?? 0);

        $montoLetras = NumberHelper::convertToLetter($monto, 'Soles');
        $saldoLetras = NumberHelper::convertToLetter($saldo, 'Soles');

        return [
            'pago_monto' => $monto > 0 ? number_format($monto, 2, '.', ',') : '',
            'pago_fecha' => $json->pago_fecha ?? '',
            'pago_medio' => $json->pago_medio ?? '',
            'pago_recibo_numero' => $json->pago_recibo_numero ?? '',

            'precio_total_letras' => $monto > 0 ? mb_strtoupper($montoLetras, 'UTF-8') : '',
            'precio_saldo' => $saldo > 0 ? number_format($saldo, 2, '.', ',') : '',
            'precio_saldo_letras' => $saldo > 0 ? mb_strtoupper($saldoLetras, 'UTF-8') : '',
        ];
    }

    private function baseClientePN(int $loteID): array
    {
        $ctx = $this->ctxByLote($loteID);
        $cliente = $ctx['cliente'] ?? [];

        $nombres = $cliente['nombres'] ?? '';
        $apellidos = $cliente['apellidos'] ?? '';
        $nombreCompleto = trim($nombres . ' ' . $apellidos);

        $docTipo = $this->docTipoTexto((int)($cliente['tipo_documento'] ?? 1));
        $docNum  = $cliente['numero_documento'] ?? '';

        return [
            'cliente_nombres' => $nombres,
            'cliente_apellidos' => $apellidos,
            'cliente_nombre_completo' => $nombreCompleto,

            'cliente_documento_tipo' => $docTipo,
            'cliente_documento_numero' => $docNum,

            'cliente_direccion' => $cliente['direccion'] ?? '',
            'cliente_estado_civil' => $cliente['estado_civil'] ?? '',

            'cliente_whatsapp' => $cliente['telefono'] ?? '',
            'cliente_email' => $cliente['email'] ?? '',

            '_cliente' => $cliente,
        ];
    }


    // ... (desde aquí sigues PEGANDO TODO TU RESTO TAL CUAL)
    // baseCommon, basePagos, baseClientePN, baseClientePJ y TODOS tus fillDataTemplate*
    // hasta cerrar la clase.



    private function baseClientePJ(int $loteID): array
    {
        $ctx = $this->ctxByLote($loteID);
        $cliente = $ctx['cliente'] ?? [];
        $copros = $ctx['copropietarios'] ?? [];

        $razon = $cliente['razon_social'] ?? ($cliente['empresa_razon_social'] ?? '');
        $ruc   = $cliente['ruc'] ?? ($cliente['numero_documento'] ?? '');

        $partida = $cliente['partida_registral'] ?? ($cliente['partida'] ?? '');

        $repNombre = $cliente['representante_nombre'] ?? ($cliente['rep_legal_nombre'] ?? '');
        if ($repNombre === '') {
            $repNombre = trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? ''));
        }

        $repDocTipo = $cliente['representante_doc_tipo'] ?? ($cliente['rep_legal_doc_tipo'] ?? 'DNI');
        $repDocNum  = $cliente['representante_doc_num'] ?? ($cliente['rep_legal_doc_num'] ?? '');

        $repDocumento = trim($repDocTipo . ' ' . $repDocNum);

        $dir = $cliente['direccion'] ?? '';
        $bloque = $this->generarBloqueCopropietarios($copros);

        return [
            'cliente_razon_social' => $razon,
            'cliente_ruc' => $ruc,
            'cliente_direccion' => $dir,
            'cliente_partida_registral' => $partida,
            'cliente_representante_nombre' => $repNombre,
            'cliente_representante_documento' => $repDocumento,

            'empresa_razon_social' => $razon,
            'empresa_ruc' => $ruc,
            'empresa_direccion' => $dir,
            'empresa_partida_registral' => $partida,
            'empresa_representante_nombre' => $repNombre,
            'empresa_representante_documento' => $repDocumento,

            'cliente_whatsapp' => $cliente['telefono'] ?? '',
            'cliente_email' => $cliente['email'] ?? '',

            'copropietarios_bloque' => $bloque,

            '_cliente' => $cliente,
            '_copropietarios' => $copros,
        ];
    }

    // ============================================================
    // 1) ANULACION - PN (Plantilla ID: 1)
    // ============================================================
    private function fillDataTemplateAnulacionPN($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $lote = $ctx['lote'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        $pagoMonto = (float)($json->pago_monto ?? 0);
        $tipoDocumentoAnulado = strtoupper((string)($json->tipo_documento_anulado ?? 'RESERVA'));

        $penalidad = 0;
        $devolucion = 0;
        $penalidad_texto = '';

        switch ($tipoDocumentoAnulado) {
            case 'RESERVA':
            case 'SEPARACION':
                $penalidad = $pagoMonto;
                $devolucion = 0;
                $penalidad_texto = '100% del monto entregado';
                break;

            case 'COMPRAVENTA':
                $penalidad = $pagoMonto * 0.30;
                $devolucion = $pagoMonto - $penalidad;
                $penalidad_texto = '30% del monto pagado (S/ ' . number_format($penalidad, 2, '.', ',') . ')';
                break;

            default:
                $penalidad = $pagoMonto;
                $devolucion = 0;
                $penalidad_texto = '100% del monto entregado';
                break;
        }

        $devolucionLetras = NumberHelper::convertToLetter($devolucion, 'Soles');

        return array_merge($base, [
            'tipo_documento_anulado' => $tipoDocumentoAnulado,
            'fecha_documento_anulado' => $json->fecha_documento_anulado ?? '',
            'motivo_anulacion' => $json->motivo_anulacion ?? 'Desistimiento voluntario del cliente',
            'penalidad_aplicada' => $penalidad_texto,
            'monto_devolucion' => number_format($devolucion, 2, '.', ','),
            'monto_devolucion_letras' => mb_strtoupper($devolucionLetras, 'UTF-8'),
            'plazo_devolucion_dias' => $json->plazo_devolucion_dias ?? 30,
            'lote_precio_final' => $lote['precio_final'] ?? '',
        ]);
    }

    // ============================================================
    // 2) COMPRAVENTA - PJ (Plantilla ID: 2)
    // ============================================================
    private function fillDataTemplateCompraventaPJ($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePJ($loteID),
            $this->basePagos($loteID)
        );

        return array_merge($base, [
            'compra_modalidad' => $json->compra_modalidad ?? 'CREDITO',
            'plan_cuotas' => $json->plan_cuotas ?? '',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',
            'plazo_entrega_dias' => $json->plazo_entrega_dias ?? 30,
            'cuotas_impagas_resolutorias' => $json->cuotas_impagas_resolutorias ?? 3,
            'plazo_devolucion_dias' => $json->plazo_devolucion_dias ?? 30,
        ]);
    }

    // ============================================================
    // 3) COMPRAVENTA - PN (Plantilla ID: 3)
    // ============================================================
    private function fillDataTemplateCompraventaPN($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        return array_merge($base, [
            'compra_modalidad' => $json->compra_modalidad ?? 'CREDITO',
            'plan_cuotas' => $json->plan_cuotas ?? '',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',
            'plazo_entrega_dias' => $json->plazo_entrega_dias ?? 30,
            'cuotas_impagas_resolutorias' => $json->cuotas_impagas_resolutorias ?? 3,
            'plazo_devolucion_dias' => $json->plazo_devolucion_dias ?? 30,
        ]);
    }

    // ============================================================
    // 4) COMPRAVENTA - PN_CONYUGE (Plantilla ID: 4)
    // ============================================================
    private function fillDataTemplateCompraventaPNConyuge($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $cliente = $ctx['cliente'] ?? [];
        $copros = $ctx['copropietarios'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        $conyuge = $this->pickConyugeFromCopropietarios($copros) ?: [];

        $conyugeNombre = trim(($conyuge['nombres'] ?? '') . ' ' . ($conyuge['apellidos'] ?? ''));
        $conyugeDocTipo = $this->docTipoTexto((int)($conyuge['tipo_documento'] ?? 1));
        $conyugeDocNum  = $conyuge['numero_documento'] ?? '';

        $bloque = $this->generarBloqueCopropietarios($copros, $conyuge);

        return array_merge($base, [
            'cliente_estado_civil' => $cliente['estado_civil'] ?? 'CASADO',

            'conyuge_nombre_completo' => $conyugeNombre,
            'conyuge_documento_tipo' => $conyugeNombre ? $conyugeDocTipo : '',
            'conyuge_documento_numero' => $conyugeNombre ? $conyugeDocNum : '',

            'copropietarios_bloque' => $bloque,

            'compra_modalidad' => $json->compra_modalidad ?? 'CREDITO',
            'plan_cuotas' => $json->plan_cuotas ?? '',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',
            'plazo_entrega_dias' => $json->plazo_entrega_dias ?? 30,
            'cuotas_impagas_resolutorias' => $json->cuotas_impagas_resolutorias ?? 3,
            'plazo_devolucion_dias' => $json->plazo_devolucion_dias ?? 30,
        ]);
    }

    // ============================================================
    // 5) RESERVA - PJ (Plantilla ID: 5)
    // ============================================================
    private function fillDataTemplateReservaPJ($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePJ($loteID),
            $this->basePagos($loteID)
        );

        return array_merge($base, [
            'reserva_plazo_dias' => $json->reserva_plazo_dias ?? 0,

            'empresa_partida_registral' => $base['empresa_partida_registral'] ?? ($json->partida_registral ?? ''),
            'cliente_partida_registral' => $base['cliente_partida_registral'] ?? ($json->partida_registral ?? ''),
        ]);
    }

    // ============================================================
    // 6) RESERVA - PN (Plantilla ID: 6)
    // ============================================================
    private function fillDataTemplateReservaPN($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        return array_merge($base, [
            'reserva_plazo_dias' => $json->reserva_plazo_dias ?? 0,
        ]);
    }

    // ============================================================
    // 7) RESERVA - PN_CONYUGE (Plantilla ID: 7)
    // ============================================================
    private function fillDataTemplateReservaPNConyuge($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $cliente = $ctx['cliente'] ?? [];
        $copros = $ctx['copropietarios'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        $conyuge = $this->pickConyugeFromCopropietarios($copros) ?: [];

        $conyugeNombre = trim(($conyuge['nombres'] ?? '') . ' ' . ($conyuge['apellidos'] ?? ''));
        $conyugeDocTipo = $this->docTipoTexto((int)($conyuge['tipo_documento'] ?? 1));
        $conyugeDocNum  = $conyuge['numero_documento'] ?? '';

        $bloque = $this->generarBloqueCopropietarios($copros, $conyuge);

        return array_merge($base, [
            'cliente_estado_civil' => $cliente['estado_civil'] ?? 'CASADO',

            'conyuge_nombre_completo' => $conyugeNombre,
            'conyuge_documento_tipo' => $conyugeNombre ? $conyugeDocTipo : '',
            'conyuge_documento_numero' => $conyugeNombre ? $conyugeDocNum : '',
            'conyuge_direccion' => $conyuge['direccion'] ?? ($cliente['direccion'] ?? ''),

            'copropietarios_bloque' => $bloque,

            'reserva_plazo_dias' => $json->reserva_plazo_dias ?? 0,
        ]);
    }

    // ============================================================
    // 8) SEPARACION - PJ (Plantilla ID: 8)
    // ============================================================
    private function fillDataTemplateSeparacionPJ($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $lote = $ctx['lote'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePJ($loteID),
            $this->basePagos($loteID)
        );

        $precioFinal = (float)($lote['precio_final'] ?? 0);
        $pagoMonto   = (float)($json->pago_monto ?? 0);
        $saldo       = $precioFinal - $pagoMonto;

        return array_merge($base, [
            'precio_saldo' => number_format($saldo, 2, '.', ','),
            'plan_cuotas' => $json->plan_cuotas ?? 'Por definir en contrato de compraventa',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',

            'separacion_plazo_dias' => $json->separacion_plazo_dias ?? 30,
        ]);
    }

    // ============================================================
    // 9) SEPARACION - PN (Plantilla ID: 9)
    // ============================================================
    private function fillDataTemplateSeparacionPN($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $lote = $ctx['lote'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        $precioFinal = (float)($lote['precio_final'] ?? 0);
        $pagoMonto   = (float)($json->pago_monto ?? 0);
        $saldo       = $precioFinal - $pagoMonto;

        return array_merge($base, [
            'precio_saldo' => number_format($saldo, 2, '.', ','),
            'plan_cuotas' => $json->plan_cuotas ?? 'Por definir en contrato de compraventa',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',

            'separacion_plazo_dias' => $json->separacion_plazo_dias ?? 30,
        ]);
    }

    // ============================================================
    // 10) SEPARACION - PN_CONYUGE (Plantilla ID: 10)
    // ============================================================
    private function fillDataTemplateSeparacionPNConyuge($loteID): array
    {
        $loteID = (int)$loteID;

        $ctx = $this->ctxByLote($loteID);
        $json = $ctx['json'];
        $lote = $ctx['lote'] ?? [];
        $cliente = $ctx['cliente'] ?? [];
        $copros = $ctx['copropietarios'] ?? [];

        $base = array_merge(
            $this->baseCommon($loteID),
            $this->baseClientePN($loteID),
            $this->basePagos($loteID)
        );

        $conyuge = $this->pickConyugeFromCopropietarios($copros) ?: [];

        $conyugeNombre = trim(($conyuge['nombres'] ?? '') . ' ' . ($conyuge['apellidos'] ?? ''));
        $conyugeDocTipo = $this->docTipoTexto((int)($conyuge['tipo_documento'] ?? 1));
        $conyugeDocNum  = $conyuge['numero_documento'] ?? '';

        $bloque = $this->generarBloqueCopropietarios($copros, $conyuge);

        $precioFinal = (float)($lote['precio_final'] ?? 0);
        $pagoMonto   = (float)($json->pago_monto ?? 0);
        $saldo       = $precioFinal - $pagoMonto;

        return array_merge($base, [
            'cliente_estado_civil' => $cliente['estado_civil'] ?? 'CASADO',

            'conyuge_nombre_completo' => $conyugeNombre,
            'conyuge_documento_tipo' => $conyugeNombre ? $conyugeDocTipo : '',
            'conyuge_documento_numero' => $conyugeNombre ? $conyugeDocNum : '',
            'conyuge_direccion' => $conyuge['direccion'] ?? ($cliente['direccion'] ?? ''),

            'copropietarios_bloque' => $bloque,

            'precio_saldo' => number_format($saldo, 2, '.', ','),
            'plan_cuotas' => $json->plan_cuotas ?? 'Por definir en contrato de compraventa',
            'plan_cuotas_detalle' => $json->plan_cuotas_detalle ?? '',

            'separacion_plazo_dias' => $json->separacion_plazo_dias ?? 30,
        ]);
    }
}
