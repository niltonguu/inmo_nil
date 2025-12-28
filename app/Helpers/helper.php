<?php
/**
 * app/Helpers/helper.php
 * ------------------------------------------------------------
 * Archivo único de helpers "pequeños y repetidos".
 *
 * Objetivo:
 * - Evitar repetir código común (strings, números, fechas, ubigeo, etc.)
 * - Mantener funciones genéricas (SIN lógica de negocio de módulos)
 *
 * Convención:
 * - Prefijo "h_" en todas las funciones para evitar conflictos.
 * - Cada helper está protegido con if (!function_exists(...)) para evitar redeclare.
 */

// ============================================================
// 1) STRINGS / SEGURIDAD
// ============================================================

if (!function_exists('h_e')) {
    /**
     * h_e()
     * Escapa texto para HTML (XSS-safe)
     *
     * Uso:
     *   echo h_e($nombre);
     */
    function h_e($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('h_s')) {
    /**
     * h_s()
     * String safe: convierte null a '', hace trim, y aplica default si queda vacío.
     *
     * Uso:
     *   $nombre = h_s($_POST['nombre'], 'SIN NOMBRE');
     */
    function h_s($v, string $default = ''): string
    {
        $v = is_null($v) ? '' : (string)$v;
        $v = trim($v);
        return $v === '' ? $default : $v;
    }
}

if (!function_exists('h_title')) {
    /**
     * h_title()
     * Convierte texto a "Title Case" (UTF-8) para nombres/ubicaciones.
     *
     * Ej:
     *   "CHORRILLOS" -> "Chorrillos"
     *   "lima" -> "Lima"
     */
    function h_title(string $text): string
    {
        $text = trim($text);
        if ($text === '') return '';
        return mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}

// ============================================================
// 2) NÚMEROS / DINERO
// ============================================================

if (!function_exists('h_n')) {
    /**
     * h_n()
     * Number safe: devuelve float válido o un default si no es numérico.
     *
     * Uso:
     *   $monto = h_n($_POST['monto']);
     */
    function h_n($v, float $default = 0): float
    {
        if ($v === null || $v === '' || !is_numeric($v)) return $default;
        return (float)$v;
    }
}

if (!function_exists('h_money')) {
    /**
     * h_money()
     * Formatea un número como dinero con separador decimal '.' y miles ','.
     *
     * Uso:
     *   echo h_money(12345.5); // 12,345.50
     */
    function h_money($v, int $decimals = 2): string
    {
        return number_format(h_n($v, 0), $decimals, '.', ',');
    }
}

// ============================================================
// 3) FECHAS
// ============================================================

if (!function_exists('h_date')) {
    /**
     * h_date()
     * Formatea una fecha de manera segura.
     *
     * Acepta:
     * - string tipo "2025-12-21"
     * - timestamp numérico
     *
     * Uso:
     *   echo h_date('2025-12-21'); // 21/12/2025
     *   echo h_date(time(), 'd/m/Y H:i');
     */
    function h_date($date, string $format = 'd/m/Y', string $default = ''): string
    {
        if (!$date) return $default;

        try {
            $dt = is_numeric($date)
                ? (new DateTime('@' . $date))
                : new DateTime((string)$date);

            return $dt->format($format);
        } catch (Throwable $e) {
            return $default;
        }
    }
}

// ============================================================
// 4) UBIGEO (Perú) - PARSEO Y FORMATO LEGAL
// ============================================================
// Contexto:
// - En tu tabla ubigeos, la columna "descripcion" tiene el formato:
//     "DISTRITO - PROVINCIA - DEPARTAMENTO"
// - Este bloque permite:
//   a) obtener partes separadas: distrito, provincia, departamento
//   b) formatear para contratos: "Distrito de X, Provincia de Y, Departamento de Z"

if (!function_exists('h_ubigeo_parts')) {
    /**
     * h_ubigeo_parts()
     * Parsea una descripción de ubigeo:
 *   "DISTRITO - PROVINCIA - DEPARTAMENTO"
     *
     * Retorna un array de 3 posiciones:
     *   [0] distrito
     *   [1] provincia
     *   [2] departamento
     *
     * Si el formato no calza, retorna ['', '', ''] para evitar errores.
     *
     * Uso:
     *   [$d, $p, $dep] = h_ubigeo_parts($desc);
     */
    function h_ubigeo_parts(?string $descripcion): array
    {
        $descripcion = h_s($descripcion);
        if ($descripcion === '') return ['', '', ''];

        $parts = array_map('trim', explode('-', $descripcion));
        if (count($parts) !== 3) return ['', '', ''];

        return [$parts[0], $parts[1], $parts[2]];
    }
}

if (!function_exists('h_ubigeo_distrito')) {
    /**
     * h_ubigeo_distrito()
     * Devuelve el distrito (parte [0]) capitalizado bonito.
     *
     * Uso:
     *   echo h_ubigeo_distrito("CHORRILLOS - LIMA - LIMA"); // Chorrillos
     */
    function h_ubigeo_distrito(?string $descripcion): string
    {
        $parts = h_ubigeo_parts($descripcion);
        return h_title($parts[0] ?? '');
    }
}

if (!function_exists('h_ubigeo_provincia')) {
    /**
     * h_ubigeo_provincia()
     * Devuelve la provincia (parte [1]) capitalizada bonito.
     *
     * Uso:
     *   echo h_ubigeo_provincia("CHORRILLOS - LIMA - LIMA"); // Lima
     */
    function h_ubigeo_provincia(?string $descripcion): string
    {
        $parts = h_ubigeo_parts($descripcion);
        return h_title($parts[1] ?? '');
    }
}

if (!function_exists('h_ubigeo_departamento')) {
    /**
     * h_ubigeo_departamento()
     * Devuelve el departamento (parte [2]) capitalizado bonito.
     *
     * Uso:
     *   echo h_ubigeo_departamento("CHORRILLOS - LIMA - LIMA"); // Lima
     */
    function h_ubigeo_departamento(?string $descripcion): string
    {
        $parts = h_ubigeo_parts($descripcion);
        return h_title($parts[2] ?? '');
    }
}

if (!function_exists('h_ubigeo_legal')) {
    /**
     * h_ubigeo_legal()
     * Formatea la descripción del ubigeo en un formato "legal" para contratos:
     *
     *   "Distrito de X, Provincia de Y, Departamento de Z"
     *
     * Si no se puede parsear, retorna '' para que no metas basura al contrato.
     *
     * Uso:
     *   echo h_ubigeo_legal("CHORRILLOS - LIMA - LIMA");
     *   // Distrito de Chorrillos, Provincia de Lima, Departamento de Lima
     */
    function h_ubigeo_legal(?string $descripcion): string
    {
        [$d, $p, $dep] = h_ubigeo_parts($descripcion);

        if ($d === '' || $p === '' || $dep === '') return '';

        return 'Distrito de ' . h_title($d)
            . ', Provincia de ' . h_title($p)
            . ', Departamento de ' . h_title($dep);
    }
}
