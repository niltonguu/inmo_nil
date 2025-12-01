<?php
// app/Models/LotesModel.php

require_once __DIR__ . '/../Config/Database.php';

class LotesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // =========================================================
    // LISTADOS
    // =========================================================

    public function getLotesAdmin(array $filtros = []): array
    {
        $sql = "
            SELECT 
                l.id,
                p.nombre     AS proyecto,
                e.nombre     AS etapa,
                m.codigo     AS manzana,
                l.numero,
                l.area_m2,
                l.precio_base,
                l.factor_pct_total,
                l.precio_final,
                l.estado_lote,
                l.estado_comercial,
                l.id_cliente,
                COALESCE(
                    NULLIF(TRIM(CONCAT(c.nombres, ' ', c.apellidos)), ''),
                    c.razon_social
                ) AS cliente_nombre
            FROM lotes l
            INNER JOIN proyectos p ON p.id = l.id_proyecto
            INNER JOIN etapas e    ON e.id = l.id_etapa
            INNER JOIN manzanas m  ON m.id = l.id_manzana
            LEFT JOIN clientes c   ON c.id = l.id_cliente
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($filtros['id_proyecto'])) {
            $sql .= " AND l.id_proyecto = :id_proyecto";
            $params[':id_proyecto'] = (int)$filtros['id_proyecto'];
        }
        if (!empty($filtros['id_etapa'])) {
            $sql .= " AND l.id_etapa = :id_etapa";
            $params[':id_etapa'] = (int)$filtros['id_etapa'];
        }
        if (!empty($filtros['id_manzana'])) {
            $sql .= " AND l.id_manzana = :id_manzana";
            $params[':id_manzana'] = (int)$filtros['id_manzana'];
        }
        if (!empty($filtros['estado_lote'])) {
            $sql .= " AND l.estado_lote = :estado_lote";
            $params[':estado_lote'] = $filtros['estado_lote'];
        }

        $sql .= "
            ORDER BY p.nombre, e.numero, m.codigo, l.numero
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLotesUsuario(array $filtros = []): array
    {
        $sql = "
            SELECT 
                l.id,
                p.nombre     AS proyecto,
                e.nombre     AS etapa,
                m.codigo     AS manzana,
                l.numero,
                l.area_m2,
                l.precio_final,
                l.estado_lote,
                l.id_cliente,
                COALESCE(
                    NULLIF(TRIM(CONCAT(c.nombres, ' ', c.apellidos)), ''),
                    c.razon_social
                ) AS cliente_nombre
            FROM lotes l
            INNER JOIN proyectos p ON p.id = l.id_proyecto
            INNER JOIN etapas e    ON e.id = l.id_etapa
            INNER JOIN manzanas m  ON m.id = l.id_manzana
            LEFT JOIN clientes c   ON c.id = l.id_cliente
            WHERE 1 = 1
              AND l.estado_comercial = 'HABILITADO'
              AND p.estado = 'ACTIVO'
              AND e.habilitada_venta = 1
        ";

        $params = [];

        if (!empty($filtros['id_proyecto'])) {
            $sql .= " AND l.id_proyecto = :id_proyecto";
            $params[':id_proyecto'] = (int)$filtros['id_proyecto'];
        }
        if (!empty($filtros['id_etapa'])) {
            $sql .= " AND l.id_etapa = :id_etapa";
            $params[':id_etapa'] = (int)$filtros['id_etapa'];
        }
        if (!empty($filtros['id_manzana'])) {
            $sql .= " AND l.id_manzana = :id_manzana";
            $params[':id_manzana'] = (int)$filtros['id_manzana'];
        }
        if (!empty($filtros['estado_lote'])) {
            $sql .= " AND l.estado_lote = :estado_lote";
            $params[':estado_lote'] = $filtros['estado_lote'];
        }

        $sql .= "
            ORDER BY p.nombre, e.numero, m.codigo, l.numero
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // DETALLE
    // =========================================================

    public function getLoteById(int $id): ?array
    {
        $sql = "
            SELECT 
                l.*,
                p.nombre   AS proyecto_nombre,
                e.nombre   AS etapa_nombre,
                m.codigo   AS manzana_codigo,
                p.precio_m2_base,
                p.factor_min_pct,
                p.factor_max_pct
            FROM lotes l
            INNER JOIN proyectos p ON p.id = l.id_proyecto
            INNER JOIN etapas e    ON e.id = l.id_etapa
            INNER JOIN manzanas m  ON m.id = l.id_manzana
            WHERE l.id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // =========================================================
    // CRUD LOTE
    // =========================================================

    public function saveLote(array $data, int $idUsuario): bool
    {
        $id          = isset($data['id']) ? (int)$data['id'] : 0;
        $idProyecto  = (int)($data['id_proyecto'] ?? 0);
        $idEtapa     = (int)($data['id_etapa'] ?? 0);
        $idManzana   = (int)($data['id_manzana'] ?? 0);
        $numero      = (int)($data['numero'] ?? 0);
        $codigo      = trim($data['codigo'] ?? '');
        $areaM2      = (float)($data['area_m2'] ?? 0);
        $precioM2    = isset($data['precio_m2']) && $data['precio_m2'] !== '' ? (float)$data['precio_m2'] : null;
        $frente      = isset($data['frente_m']) ? (float)$data['frente_m'] : null;
        $fondo       = isset($data['fondo_m']) ? (float)$data['fondo_m'] : null;
        $ladoIzq     = isset($data['lado_izq_m']) ? (float)$data['lado_izq_m'] : null;
        $ladoDer     = isset($data['lado_der_m']) ? (float)$data['lado_der_m'] : null;
        $estadoCom   = $data['estado_comercial'] ?? 'HABILITADO';
        $estadoLote  = $data['estado_lote'] ?? 'DISPONIBLE';
        $idCliente   = isset($data['id_cliente']) && $data['id_cliente'] !== '' ? (int)$data['id_cliente'] : null;

        if ($codigo === '') {
            $codigo = 'L-' . $numero;
        }

        if ($id > 0) {
            $sql = "
                UPDATE lotes
                SET 
                    id_proyecto          = :id_proyecto,
                    id_etapa             = :id_etapa,
                    id_manzana           = :id_manzana,
                    codigo               = :codigo,
                    numero               = :numero,
                    area_m2              = :area_m2,
                    precio_m2            = :precio_m2,
                    frente_m             = :frente_m,
                    fondo_m              = :fondo_m,
                    lado_izq_m           = :lado_izq_m,
                    lado_der_m           = :lado_der_m,
                    estado_comercial     = :estado_comercial,
                    estado_lote          = :estado_lote,
                    id_cliente           = :id_cliente,
                    updated_at           = NOW()
                WHERE id = :id
            ";
        } else {
            $sql = "
                INSERT INTO lotes (
                    id_proyecto,
                    id_etapa,
                    id_manzana,
                    codigo,
                    numero,
                    area_m2,
                    precio_m2,
                    frente_m,
                    fondo_m,
                    lado_izq_m,
                    lado_der_m,
                    estado_comercial,
                    estado_lote,
                    id_cliente,
                    id_usuario_responsable,
                    created_at
                ) VALUES (
                    :id_proyecto,
                    :id_etapa,
                    :id_manzana,
                    :codigo,
                    :numero,
                    :area_m2,
                    :precio_m2,
                    :frente_m,
                    :fondo_m,
                    :lado_izq_m,
                    :lado_der_m,
                    :estado_comercial,
                    :estado_lote,
                    :id_cliente,
                    :id_usuario_responsable,
                    NOW()
                )
            ";
        }

        $params = [
            ':id_proyecto'         => $idProyecto,
            ':id_etapa'            => $idEtapa,
            ':id_manzana'          => $idManzana,
            ':codigo'              => $codigo,
            ':numero'              => $numero,
            ':area_m2'             => $areaM2,
            ':precio_m2'           => $precioM2,
            ':frente_m'            => $frente,
            ':fondo_m'             => $fondo,
            ':lado_izq_m'          => $ladoIzq,
            ':lado_der_m'          => $ladoDer,
            ':estado_comercial'    => $estadoCom,
            ':estado_lote'         => $estadoLote,
            ':id_cliente'          => $idCliente,
        ];

        if ($id > 0) {
            $params[':id'] = $id;
        } else {
            $params[':id_usuario_responsable'] = $idUsuario;
        }

        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute($params);

        // Recalcular importes
        if ($ok) {
            if ($id === 0) {
                $id = (int)$this->db->lastInsertId();
            }
            $this->recalcularPrecioLote($id);
        }

        return $ok;
    }

    public function deleteLote(int $id): bool
    {
        $sql = "DELETE FROM lotes WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            // Claves foráneas pueden impedir la eliminación
            return false;
        }
    }

    // =========================================================
    // FACTORES / PRECIO
    // =========================================================

    public function recalcularPrecioLote(int $idLote): bool
    {
        // Obtenemos datos del lote + proyecto
        $sql = "
            SELECT 
                l.id,
                l.area_m2,
                l.precio_m2,
                l.id_proyecto,
                p.precio_m2_base,
                p.factor_min_pct,
                p.factor_max_pct
            FROM lotes l
            INNER JOIN proyectos p ON p.id = l.id_proyecto
            WHERE l.id = :id_lote
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_lote' => $idLote]);
        $lote = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lote) {
            return false;
        }

        $area        = (float)$lote['area_m2'];
        $precioM2    = $lote['precio_m2'] !== null ? (float)$lote['precio_m2'] : (float)$lote['precio_m2_base'];
        $factorMin   = (float)$lote['factor_min_pct'];
        $factorMax   = (float)$lote['factor_max_pct'];

        // Sumatoria de factores activos asociados al lote
        $sqlFact = "
            SELECT COALESCE(SUM(f.valor_pct), 0) AS total_pct
            FROM lote_factores lf
            INNER JOIN factores f ON f.id = lf.id_factor
            WHERE lf.id_lote = :id_lote
              AND f.activo = 1
        ";

        $stmtF = $this->db->prepare($sqlFact);
        $stmtF->execute([':id_lote' => $idLote]);
        $rowF = $stmtF->fetch(PDO::FETCH_ASSOC);

        $factorTotal = (float)($rowF['total_pct'] ?? 0);

        // Clampeamos al rango del proyecto
        if ($factorTotal < $factorMin) $factorTotal = $factorMin;
        if ($factorTotal > $factorMax) $factorTotal = $factorMax;

        $precioBase  = $area * $precioM2;
        $precioFinal = $precioBase * (1 + $factorTotal / 100);

        $sqlUpd = "
            UPDATE lotes
            SET 
                precio_base      = :precio_base,
                factor_pct_total = :factor_total,
                precio_final     = :precio_final,
                updated_at       = NOW()
            WHERE id = :id_lote
        ";

        $stmtU = $this->db->prepare($sqlUpd);
        return $stmtU->execute([
            ':precio_base'  => $precioBase,
            ':factor_total' => $factorTotal,
            ':precio_final' => $precioFinal,
            ':id_lote'      => $idLote,
        ]);
    }

    // =========================================================
    // VÉRTICES
    // =========================================================

    public function getVerticesByLote(int $idLote): array
    {
        $sql = "
            SELECT id, id_lote, orden, lat, lng, created_at
            FROM lote_vertices
            WHERE id_lote = :id_lote
            ORDER BY orden
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_lote' => $idLote]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveVertices(int $idLote, array $vertices): bool
    {
        $this->db->beginTransaction();

        try {
            $del = $this->db->prepare("DELETE FROM lote_vertices WHERE id_lote = :id_lote");
            $del->execute([':id_lote' => $idLote]);

            $ins = $this->db->prepare("
                INSERT INTO lote_vertices (id_lote, orden, lat, lng, created_at)
                VALUES (:id_lote, :orden, :lat, :lng, NOW())
            ");

            foreach ($vertices as $v) {
                $ins->execute([
                    ':id_lote' => $idLote,
                    ':orden'   => (int)$v['orden'],
                    ':lat'     => (float)$v['lat'],
                    ':lng'     => (float)$v['lng'],
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteVerticesByLote(int $idLote): bool
    {
        $sql = "DELETE FROM lote_vertices WHERE id_lote = :id_lote";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([':id_lote' => $idLote]);
        } catch (Exception $e) {
            return false;
        }
    }

    // =========================================================
    // HISTORIAL
    // =========================================================

    public function getHistorialByLote(int $idLote, string $role): array
    {
        $sql = "
            SELECT 
                h.id,
                h.created_at,
                h.estado_lote_anterior,
                h.estado_lote_nuevo,
                h.id_cliente_anterior,
                h.id_cliente_nuevo,
                h.motivo,
                u.fullname AS usuario_nombre,
                ca.numero_documento AS cli_ant_doc,
                COALESCE(NULLIF(TRIM(CONCAT(ca.nombres, ' ', ca.apellidos)), ''), ca.razon_social) AS cli_ant_nombre,
                cn.numero_documento AS cli_nvo_doc,
                COALESCE(NULLIF(TRIM(CONCAT(cn.nombres, ' ', cn.apellidos)), ''), cn.razon_social) AS cli_nvo_nombre
            FROM lotes_historial h
            INNER JOIN users u ON u.id = h.id_usuario_responsable
            LEFT JOIN clientes ca ON ca.id = h.id_cliente_anterior
            LEFT JOIN clientes cn ON cn.id = h.id_cliente_nuevo
            WHERE h.id_lote = :id_lote
            ORDER BY h.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_lote' => $idLote]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // CAMBIO DE ESTADO
    // =========================================================

    public function cambiarEstadoLote(
        int $idLote,
        string $estadoAnterior,
        string $estadoNuevo,
        ?int $idClienteNuevo,
        int $idUsuario,
        string $motivo
    ): bool {
        $this->db->beginTransaction();

        try {
            // Obtenemos cliente actual
            $stmtSel = $this->db->prepare("
                SELECT id_cliente
                FROM lotes
                WHERE id = :id_lote
                FOR UPDATE
            ");
            $stmtSel->execute([':id_lote' => $idLote]);
            $row = $stmtSel->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->db->rollBack();
                return false;
            }
            $idClienteAnterior = $row['id_cliente'] !== null ? (int)$row['id_cliente'] : null;

            // Regla: si estado != DISPONIBLE, debe tener cliente
            if ($estadoNuevo !== 'DISPONIBLE' && !$idClienteNuevo) {
                $this->db->rollBack();
                return false;
            }

            // Si se libera (DISPONIBLE), cliente debe quedar NULL
            if ($estadoNuevo === 'DISPONIBLE') {
                $idClienteNuevo = null;
            }

            // Actualizamos lote
            $stmtUpd = $this->db->prepare("
                UPDATE lotes
                SET 
                    estado_lote          = :estado_nuevo,
                    id_cliente           = :id_cliente_nuevo,
                    id_usuario_responsable = :id_usuario,
                    updated_at           = NOW()
                WHERE id = :id_lote
            ");
            $stmtUpd->execute([
                ':estado_nuevo'      => $estadoNuevo,
                ':id_cliente_nuevo'  => $idClienteNuevo,
                ':id_usuario'        => $idUsuario,
                ':id_lote'           => $idLote,
            ]);

            // Insertamos historial
            $stmtHist = $this->db->prepare("
                INSERT INTO lotes_historial (
                    id_lote,
                    estado_lote_anterior,
                    estado_lote_nuevo,
                    id_cliente_anterior,
                    id_cliente_nuevo,
                    id_usuario_responsable,
                    motivo,
                    created_at
                ) VALUES (
                    :id_lote,
                    :estado_anterior,
                    :estado_nuevo,
                    :id_cliente_anterior,
                    :id_cliente_nuevo,
                    :id_usuario,
                    :motivo,
                    NOW()
                )
            ");
            $stmtHist->execute([
                ':id_lote'            => $idLote,
                ':estado_anterior'    => $estadoAnterior,
                ':estado_nuevo'       => $estadoNuevo,
                ':id_cliente_anterior'=> $idClienteAnterior,
                ':id_cliente_nuevo'   => $idClienteNuevo,
                ':id_usuario'         => $idUsuario,
                ':motivo'             => $motivo !== '' ? $motivo : null,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // =========================================================
    // CREACIÓN MASIVA
    // =========================================================

    public function crearLotesMasivos(
        int $idProyecto,
        ?int $idEtapa,
        int $idManzana,
        int $numDesde,
        int $numHasta,
        ?float $areaDefault,
        ?float $precioM2Default,
        int $idUsuario
    ): array {
        if ($idProyecto <= 0 || $idManzana <= 0 || $numDesde <= 0 || $numHasta <= 0 || $numHasta < $numDesde) {
            return [
                'status' => false,
                'msg'    => 'Parámetros inválidos para creación masiva',
            ];
        }

        // Si no se envía etapa, intentamos obtenerla desde la manzana
        if (!$idEtapa) {
            $stmtE = $this->db->prepare("SELECT id_etapa FROM manzanas WHERE id = :id_manzana");
            $stmtE->execute([':id_manzana' => $idManzana]);
            $rowE = $stmtE->fetch(PDO::FETCH_ASSOC);
            if (!$rowE) {
                return [
                    'status' => false,
                    'msg'    => 'Manzana no encontrada',
                ];
            }
            $idEtapa = (int)$rowE['id_etapa'];
        }

        $insertados = 0;
        $existentes = 0;

        $sql = "
            INSERT INTO lotes (
                id_proyecto,
                id_etapa,
                id_manzana,
                codigo,
                numero,
                area_m2,
                precio_m2,
                estado_comercial,
                estado_lote,
                id_usuario_responsable,
                created_at
            ) VALUES (
                :id_proyecto,
                :id_etapa,
                :id_manzana,
                :codigo,
                :numero,
                :area_m2,
                :precio_m2,
                'HABILITADO',
                'DISPONIBLE',
                :id_usuario,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        for ($n = $numDesde; $n <= $numHasta; $n++) {
            $codigo = 'L-' . $n;

            try {
                $stmt->execute([
                    ':id_proyecto' => $idProyecto,
                    ':id_etapa'    => $idEtapa,
                    ':id_manzana'  => $idManzana,
                    ':codigo'      => $codigo,
                    ':numero'      => $n,
                    ':area_m2'     => $areaDefault ?? 0,
                    ':precio_m2'   => $precioM2Default,
                    ':id_usuario'  => $idUsuario,
                ]);

                $lastId = (int)$this->db->lastInsertId();
                if ($areaDefault !== null || $precioM2Default !== null) {
                    $this->recalcularPrecioLote($lastId);
                }

                $insertados++;
            } catch (Exception $e) {
                // Uniqueness (id_manzana, numero) -> ya existía
                if ((int)$e->getCode() === 23000) {
                    $existentes++;
                    continue;
                }
                // Otro error, paramos
                return [
                    'status' => false,
                    'msg'    => 'Error al crear lotes: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'status' => true,
            'msg'    => "Lotes creados: {$insertados}. Ya existentes: {$existentes}.",
        ];
    }
}
