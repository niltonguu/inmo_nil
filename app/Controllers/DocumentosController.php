<?php
// app/Controllers/DocumentosController.php

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Models/DocumentosModel.php';
require_once __DIR__ . '/../Models/PlantillasModel.php';
require_once __DIR__ . '/../Models/ClientesModel.php';
require_once __DIR__ . '/../Models/LotesModel.php';
require_once __DIR__ . '/../Models/ProyectosModel.php';
require_once __DIR__ . '/../Helpers/NumberHelper.php';

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

    private function jsonResponse($data, int $code = 200)
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

    private function requireAdmin()
    {
        if ($this->currentUserRole() !== 'admin') {
            $this->jsonResponse(['status' => false, 'msg' => 'No autorizado'], 403);
        }
    }

    // GET: index.php?c=documentos&a=list_by_lote&id_lote=123
    public function list_by_lote()
    {
        $this->requireAdmin();

        $idLote = (int)($_GET['id_lote'] ?? 0);
        if ($idLote <= 0) {
            $this->jsonResponse([]);
        }

        $rows = $this->model->listByLote($idLote);
        $this->jsonResponse($rows);
    }

    // POST: index.php?c=documentos&a=save
    // Guardar -> genera HTML inmediatamente -> devuelve urls para ver/descargar
    public function save()
    {
        $this->requireAdmin();

        $idUsuario = $this->currentUserId();
        if ($idUsuario <= 0) {
            $this->jsonResponse(['status' => false, 'msg' => 'Sesión inválida'], 401);
        }

        $idLote = (int)($_POST['id_lote'] ?? 0);
        $tipo   = trim($_POST['tipo_documento'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $plant  = trim($_POST['plantilla'] ?? '');

        if ($idLote <= 0) {
            $this->jsonResponse(['status' => false, 'msg' => 'Falta id_lote']);
        }
        if ($tipo === '') {
            $this->jsonResponse(['status' => false, 'msg' => 'Falta tipo_documento']);
        }

        // Armamos datos_json con TODO lo que venga en POST, excepto campos base
        $payload = $_POST;
        unset($payload['id_lote'], $payload['tipo_documento'], $payload['titulo'], $payload['plantilla']);

        $data = [
            'id_lote'        => $idLote,
            'tipo_documento' => $tipo,
            'titulo'         => $titulo,
            'plantilla'      => $plant,
            'datos'          => $payload,
        ];

        $resp = $this->model->saveDocumento($data, $idUsuario);

        if (!($resp['status'] ?? false)) {
            $this->jsonResponse($resp);
        }

        // Generar HTML + guardar archivo_path
        $docId = (int)($resp['id'] ?? 0);
        if ($docId > 0) {
            $gen = $this->generarArchivos($docId);
            if ($gen['status']) {
                $resp['archivo_path'] = $gen['archivo_path'];
                $resp['url_view'] = "index.php?c=documentos&a=view&id=" . $docId;
                $resp['url_download_html'] = "index.php?c=documentos&a=download&id=" . $docId . "&format=html";
                $resp['url_download_pdf'] = "index.php?c=documentos&a=download&id=" . $docId . "&format=pdf";
            } else {
                $resp['msg'] .= ' (guardado, pero no se pudo generar el archivo: ' . ($gen['msg'] ?? 'error') . ')';
            }
        }

        $this->jsonResponse($resp);
    }

    // GET: index.php?c=documentos&a=view&id=123
    public function view()
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo "Documento no encontrado";
            exit;
        }

        $doc = $this->model->getById($id);
        if (!$doc) {
            http_response_code(404);
            echo "Documento no encontrado";
            exit;
        }

        // Si no hay archivo_path, lo generamos en caliente
        if (empty($doc['archivo_path'])) {
            $gen = $this->generarArchivos($id);
            if ($gen['status']) {
                $doc['archivo_path'] = $gen['archivo_path'];
            }
        }

        $rel = (string)($doc['archivo_path'] ?? '');
        if ($rel === '') {
            http_response_code(500);
            echo "No hay archivo generado para este documento.";
            exit;
        }

        $abs = $this->publicPath($rel);
        if (!is_file($abs)) {
            http_response_code(404);
            echo "Archivo no encontrado";
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        readfile($abs);
        exit;
    }

    // GET: index.php?c=documentos&a=download&id=123&format=html|pdf
    public function download()
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $format = strtolower(trim($_GET['format'] ?? 'html'));

        if ($id <= 0) {
            http_response_code(404);
            echo "Documento no encontrado";
            exit;
        }

        if ($format === 'pdf') {
            $doc = $this->model->getById($id);
            if (!$doc) {
                http_response_code(404);
                echo "Documento no encontrado";
                exit;
            }

            // Asegurar HTML generado
            if (empty($doc['archivo_path'])) {
                $gen = $this->generarArchivos($id);
                if ($gen['status']) {
                    $doc['archivo_path'] = $gen['archivo_path'];
                }
            }

            $rel = (string)($doc['archivo_path'] ?? '');
            if ($rel === '') {
                http_response_code(500);
                echo "No hay archivo generado para este documento.";
                exit;
            }

            $abs = $this->publicPath($rel);
            if (!is_file($abs)) {
                http_response_code(404);
                echo "Archivo no encontrado";
                exit;
            }

            $html = file_get_contents($abs);
            $text = $this->htmlToText($html);
            $pdfBytes = $this->textToPdfBytes($text);

            $safeName = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', (string)($doc['tipo_documento'] ?? 'documento'));
            $safeName .= "_v" . (int)($doc['version'] ?? 1) . ".pdf";

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $safeName . '"');
            header('Content-Length: ' . strlen($pdfBytes));
            echo $pdfBytes;
            exit;
        }

        $doc = $this->model->getById($id);
        if (!$doc) {
            http_response_code(404);
            echo "Documento no encontrado";
            exit;
        }

        if (empty($doc['archivo_path'])) {
            $gen = $this->generarArchivos($id);
            if ($gen['status']) {
                $doc['archivo_path'] = $gen['archivo_path'];
            }
        }

        $rel = (string)($doc['archivo_path'] ?? '');
        if ($rel === '') {
            http_response_code(500);
            echo "No hay archivo generado para este documento.";
            exit;
        }

        $abs = $this->publicPath($rel);
        if (!is_file($abs)) {
            http_response_code(404);
            echo "Archivo no encontrado";
            exit;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', (string)($doc['tipo_documento'] ?? 'documento'));
        $safeName .= "_v" . (int)($doc['version'] ?? 1) . ".html";

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        readfile($abs);
        exit;
    }

    // ---------------------------
    // Generación de HTML
    // ---------------------------

    private function generarArchivos(int $docId): array
    {
        $doc = $this->model->getById($docId);
        if (!$doc) return ['status' => false, 'msg' => 'Documento no existe'];

        $plant = (string)($doc['plantilla'] ?? '');
        if ($plant === '') return ['status' => false, 'msg' => 'Plantilla vacía'];

        $datos = [];
        if (!empty($doc['datos_json'])) {
            $tmp = json_decode((string)$doc['datos_json'], true);
            if (is_array($tmp)) $datos = $tmp;
        }

        // Variables base (si faltan en el formulario, al menos fecha)
        $datos['fecha_actual'] = $datos['fecha_actual'] ?? date('d/m/Y');

        // Render
        $html = $this->renderPlantilla($plant, $datos);

        // Guardar en /public/uploads/documentos/lote_{idLote}/doc_{id}_v{ver}.html
        $idLote = (int)($doc['id_lote'] ?? 0);
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
            return ['status' => false, 'msg' => 'No se pudo escribir el archivo'];
        }

        $this->model->setArchivoPath($docId, $fileRel);

        return ['status' => true, 'archivo_path' => $fileRel];
    }

    private function renderPlantilla(string $plantilla, array $vars): string
    {
        $tplPath = __DIR__ . '/../Templates/' . $plantilla;

        if (!is_file($tplPath)) {
            // Si te ponen ruta rara, intentamos basename
            $tplPath = __DIR__ . '/../Templates/' . basename($plantilla);
        }

        $content = '';
        if (is_file($tplPath)) {
            $content = file_get_contents($tplPath);
        } else {
            // fallback: si no existe, mostramos el JSON para no dejar al usuario a ciegas
            $content = "Plantilla no encontrada: " . htmlspecialchars($plantilla) . "\n\n" . json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        // Reemplazo {{clave}} por valor
        $rendered = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\-\.]+)\s*\}\}/', function ($m) use ($vars) {
            $k = $m[1];
            $v = $vars[$k] ?? '';
            if (is_array($v) || is_object($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            return (string)$v;
        }, $content);

        // Si el template no trae HTML, lo envolvemos en un <pre> bonito
        $looksHtml = (stripos($rendered, '<html') !== false) || (stripos($rendered, '<body') !== false);
        if ($looksHtml) return $rendered;

        $safe = htmlspecialchars($rendered, ENT_QUOTES, 'UTF-8');
        return "<!doctype html>
<html lang='es'>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width,initial-scale=1'>
  <title>Documento</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:24px;color:#111}
    .wrap{max-width:900px;margin:0 auto}
    pre{white-space:pre-wrap;word-wrap:break-word;line-height:1.45;font-size:14px}
    .meta{font-size:12px;color:#666;margin-bottom:14px}
  </style>
</head>
<body>
  <div class='wrap'>
    <div class='meta'>Generado: " . date('d/m/Y H:i') . "</div>
    <pre>{$safe}</pre>
  </div>
</body>
</html>";
    }

    private function htmlToText(string $html): string
    {
        // Quitar scripts/styles
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', '', $html);

        // Convertir <br> y </p> en saltos
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);

        // Strip tags y decodificar entidades
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Normalizar saltos
        $text = preg_replace("/\r\n?/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    private function textToPdfBytes(string $text): string
    {
        // PDF mínimo (una página) con Helvetica.
        // No es "bonito", pero cumple: descargable y legible.
        $lines = preg_split("/\n/", $text);
        $maxLines = 55; // por página (simple)
        $lines = array_slice($lines, 0, $maxLines);

        $escape = function ($s) {
            $s = str_replace("\\", "\\\\", $s);
            $s = str_replace("(", "\\(", $s);
            $s = str_replace(")", "\\)", $s);
            return $s;
        };

        $content = "BT\n/F1 11 Tf\n1 0 0 1 50 780 Tm\n14 TL\n";
        foreach ($lines as $i => $ln) {
            $ln = rtrim($ln);
            $content .= "(" . $escape($ln) . ") Tj\n";
            if ($i < count($lines) - 1) {
                $content .= "T*\n";
            }
        }
        $content .= "\nET";

        $objects = [];

        // 1 Catalog
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        // 2 Pages
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        // 3 Page
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        // 4 Font
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        // 5 Contents (stream)
        $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";

        $pdf = "%PDF-1.4\n";
        $xref = [];
        $offset = strlen($pdf);

        for ($i = 0; $i < count($objects); $i++) {
            $objNum = $i + 1;
            $xref[$objNum] = $offset;
            $chunk = $objNum . " 0 obj\n" . $objects[$i] . "\nendobj\n";
            $pdf .= $chunk;
            $offset += strlen($chunk);
        }

        $xrefOffset = $offset;
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $xref[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";
        return $pdf;
    }

    private function publicPath(string $relative): string
    {
        // /Publicidad/public/...
        $pub = realpath(__DIR__ . '/../../public');
        if (!$pub) $pub = __DIR__ . '/../../public';
        $relative = ltrim($relative, '/');
        return rtrim($pub, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }

    ///////////////// PDF CON TCPDF ////////////////////
    public function generatePDF()
    {
        $documentID = isset($_GET['documentID']) ? trim($_GET['documentID']) : 0;

        $documento = $this->model->getById($documentID);

        $loteID = $documento['id_lote'] ?? 0;
        $plantillaID = $documento['plantilla'] ?? 0;

        $plantillaModel = new PlantillasModel();
        $plantilla = $plantillaModel->getById($plantillaID); // 6: ID PLANTILLA

        // echo "<pre>";
        // print_r($plantilla);
        // exit();

        $dataReplace = $this->fillDataTemplateReservaPN($loteID);
        $plantillaContent = $plantilla['contenido'];

        $contenido = $this->renderTemplateReservaBold($plantillaContent, $dataReplace);

        $html = nl2br($contenido);

        $pdf = new TCPDF();
        $pdf->SetCreator('Sistema');
        $pdf->SetAuthor('Mi sistema');
        $pdf->SetTitle('Documento');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Título
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, $documento['titulo'] ?? '-' , 0, 1, 'C');

        // Espacio
        $pdf->Ln(5);

        // Fuente que soporte tildes
        $pdf->SetFont('dejavusans', '', 10);

        $htmlJustificado = '
            <div style="text-align: justify;">
                ' . $html . '
            </div>';

        // Renderizar HTML
        // $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->writeHTML($htmlJustificado, true, false, true, false, '');

        $pdf->Output('documento.pdf', 'I');
    }

    private function renderTemplateReservaBold(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            // $template = str_replace('{{'.$key.'}}', $value, $template);
            $template = str_replace('{{'.$key.'}}', '<b>'.$value.'</b>', $template);
        }

        return $template;
    }


    private function fillDataTemplateReservaPJ()
    {
        return [
            'ciudad' => 'Lima',
            'fecha_actual' => date('d/m/Y'),

            'proyecto_empresa_razon_social' => 'INMOBILIARIA LOS ANDES S.A.C.',
            'proyecto_empresa_ruc' => '20547896541',
            'proyecto_direccion' => 'Av. Principal 123 - Lima',
            'proyecto_representante_nombre' => 'Juan Pérez',
            'proyecto_representante_documento' => 'DNI 41258796',

            'empresa_razon_social' => 'CONSTRUCTORA DEL SUR S.R.L.',
            'empresa_ruc' => '20478965412',
            'empresa_direccion' => 'Jr. Comercio 456 - Arequipa',
            'empresa_representante_nombre' => 'María Gómez',
            'empresa_representante_documento' => 'DNI 78965412',
            'empresa_partida_registral' => '11024587',

            'proyecto_nombre' => 'Residencial Los Pinos',
            'lote_codigo' => 'L-12',
            'lote_manzana' => 'MZ-B',
            'lote_etapa' => 'I',
            'lote_area_m2' => '120.50',
            'lote_precio_m2' => 'S/ 850.00',
            'lote_precio_final' => 'S/ 102,425.00',
            'precio_total_letras' => 'CIENTO DOS MIL CUATROCIENTOS VEINTICINCO CON 00/100 SOLES',

            'pago_monto' => 'S/ 2,000.00',
            'pago_monto_letras' => 'DOS MIL CON 00/100 SOLES',
            'pago_fecha' => '15/12/2025',
            'pago_medio' => 'Transferencia bancaria',
            'pago_recibo_numero' => '000123',

            'reserva_plazo_dias' => '7',

            'cliente_whatsapp' => '999888777',
            'empresa_whatsapp' => '999111222',
            'cliente_email' => 'cliente@email.com',
            'empresa_email' => 'contacto@empresa.com',

            'proyecto_distrito' => 'Santiago de Surco',

            // bloques opcionales
            'copropietarios_bloque' => '',
            'lote_vertices_texto' => ''
        ];
    }

    private function fillDataTemplateReservaPN($loteID)
    {
        $infoLote = $this->model->listByLote($loteID);

        $clientModel = new ClientesModel();
        $cliente = $clientModel->getClienteById($infoLote['id_cliente'] ?? 0);

        $loteID = $infoLote['id_lote'] ?? 0;
        $documentID = $infoLote['id_lote_documento'] ?? 0;

        $document = $this->model->getById($documentID);

        $loteModel = new LotesModel();
        $lote = $loteModel->getLoteById($loteID);

        $numberLetter = NumberHelper::convertToLetter($lote['precio_final'], 'Soles');

        $jsonDataPayment = json_decode($document['datos_json']) ?? '';
        $proyectoID = $lote['id_proyecto'] ?? 0;

        $payNumberLetter = NumberHelper::convertToLetter($jsonDataPayment->pago_monto ?? 0, 'Soles');

        $proyectoModel = new ProyectosModel();
        $proyecto = $proyectoModel->getProyectoById($proyectoID);

        // echo "<pre>";
        // print_r($proyecto);
        // exit;

        return [
            'ciudad' => 'Lima',
            'fecha_actual' => date('d/m/Y'),

            // Datos del proyecto / empresa
            'proyecto_empresa_razon_social' => 'KASSA GRUPO CONSTRUCTOR E INMOBILIARIA S.A.C.',
            'proyecto_empresa_ruc' => '20613768662',
            'proyecto_direccion' => 'AV. LOS INCAS MZA. O LOTE. 37 DPTO. 201 APV. ASOC. LOS INCAS LIMA - LIMA - CHORRILLOS',
            'proyecto_representante_nombre' => 'Julio',
            'proyecto_representante_documento' => 'DNI 11111111',

            // Datos del cliente
            'cliente_nombre_completo' => $cliente['nombres'].' '.$cliente['apellidos'],
            'cliente_documento_tipo' => 'DNI',
            'cliente_documento_numero' => $cliente['numero_documento'],
            'cliente_estado_civil' => $cliente['estado_civil'],
            'cliente_direccion' => $cliente['direccion'],

            // Datos del lote / proyecto
            'proyecto_nombre' => $lote['proyecto_nombre'],
            'lote_codigo' => $lote['codigo'],
            'lote_manzana' => $lote['manzana_codigo'],
            'lote_etapa' => $lote['etapa_nombre'],
            'lote_area_m2' => $lote['area_m2'],
            'lote_precio_m2' => $lote['precio_m2_base'],
            'lote_precio_final' => $lote['precio_final'],
            'precio_total_letras' => mb_strtoupper($numberLetter, 'UTF-8'),
            'lote_vertices_texto' => '', // si necesitas detallar vértices o información extra

            // Datos de pago
            'pago_monto' => $jsonDataPayment->pago_monto ?? 0,
            'pago_monto_letras' => mb_strtoupper($payNumberLetter, 'UTF-8'),
            'pago_fecha' => $jsonDataPayment->pago_fecha ?? '',
            'pago_medio' => $jsonDataPayment->pago_medio ?? '',
            'pago_recibo_numero' => '',

            // Vigencia
            'reserva_plazo_dias' => $jsonDataPayment->reserva_plazo_dias ?? 0,

            // Contactos
            'cliente_whatsapp' => $cliente['telefono'],
            'empresa_whatsapp' => '999111222',
            'cliente_email' => $cliente['email'],
            'empresa_email' => 'contacto@empresa.com',

            // Ubicación / jurisdicción
            'proyecto_distrito' => $proyecto['ubigeo_descripcion'] ?? ''
        ];
    }
}
