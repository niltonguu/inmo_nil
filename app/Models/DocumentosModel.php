<?php
// app/Models/DocumentosModel.php

require_once __DIR__ . '/../Config/Database.php';

class DocumentosModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getByIdLote(int $idLote): array
    {
        $query = "
            SELECT
                c.numero_documento,
                c.nombres,
                c.apellidos,
                c.razon_social,
                ld.tipo_documento AS tipo_documento_generado,
                ld.id AS id_lote_documento,
                c.id AS id_cliente,
                lh.id_lote
            FROM lotes_historial lh
                INNER JOIN clientes c ON c.id = lh.id_cliente_nuevo
                LEFT JOIN lote_documentos ld ON ld.id_lote = lh.id_lote
            WHERE lh.id_lote = :idlote
        ";

        $stm = $this->db->prepare($query);
        $stm->bindValue(":idlote", $idLote);
        $stm->execute();

        return $stm->fetch(PDO::FETCH_ASSOC);
    }

    public function listByLote(int $idLote): array
    {
        return $this->getByIdLote($idLote);


        /*
        $sql = "
            SELECT
                d.id,
                d.id_lote,
                d.lote_codigo_snapshot,
                d.tipo_documento,
                d.vigente,
                d.version,
                d.titulo,
                d.plantilla,
                d.archivo_path,
                d.estado,
                d.created_at,
                u.fullname AS usuario
            FROM lote_documentos d
            LEFT JOIN users u ON u.id = d.id_usuario
            WHERE d.id_lote = :id_lote
            ORDER BY d.created_at DESC, d.id DESC
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id_lote' => $idLote]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        */
    }

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT
                d.*,
                u.fullname AS usuario
            FROM lote_documentos d
            LEFT JOIN users u ON u.id = d.id_usuario
            WHERE d.id = :id
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function setArchivoPath(int $id, string $archivoPath): bool
    {
        $st = $this->db->prepare("
            UPDATE lote_documentos
            SET archivo_path = :p
            WHERE id = :id
            LIMIT 1
        ");
        return $st->execute([':p' => $archivoPath, ':id' => $id]);
    }

    public function saveDocumento(array $data, int $idUsuario): array
    {
        $idLote = (int)($data['id_lote'] ?? 0);
        $tipo   = strtoupper(trim((string)($data['tipo_documento'] ?? '')));
        $titulo = trim((string)($data['titulo'] ?? ''));
        $plant  = trim((string)($data['plantilla'] ?? ''));
        $datos  = $data['datos'] ?? [];

        if ($idLote <= 0 || $tipo === '') {
            return ['status' => false, 'msg' => 'Parámetros inválidos'];
        }

        if ($titulo === '') {
            $titulo = "Documento {$tipo}";
        }

        // Plantilla por defecto (conecta con app/Templates/*.html)
        if ($plant === '') {
            $plant = match ($tipo) {
                'RESERVA'     => 'contrato_reserva_natural.html',
                'SEPARACION'  => 'contrato_separacion_natural.html',
                'COMPRAVENTA' => 'contrato_compraventa_natural.html',
                'ANULACION'   => 'anulacion_operacion.html',
                default       => strtolower($tipo) . '.html',
            };
        }

        $datosJson = json_encode($datos, JSON_UNESCAPED_UNICODE);
        $loteCodigoSnapshot = $this->getLoteCodigoSnapshot($idLote);

        try {
            $this->db->beginTransaction();

            // 1) Obtener versión vigente actual (si existe)
            $stV = $this->db->prepare("
                SELECT id, version
                FROM lote_documentos
                WHERE id_lote = :id_lote
                  AND tipo_documento = :tipo
                  AND vigente = 1
                FOR UPDATE
            ");
            $stV->execute([':id_lote' => $idLote, ':tipo' => $tipo]);
            $vig = $stV->fetch(PDO::FETCH_ASSOC);

            $newVersion = 1;
            if ($vig) {
                $newVersion = ((int)$vig['version']) + 1;

                // 2) Marcar el anterior como no vigente
                $stU = $this->db->prepare("
                    UPDATE lote_documentos
                    SET vigente = 0, estado = 'HISTORICO'
                    WHERE id = :id
                    LIMIT 1
                ");
                $stU->execute([':id' => (int)$vig['id']]);
            }

            // 3) Insertar el nuevo documento como vigente
            $stI = $this->db->prepare("
                INSERT INTO lote_documentos (
                    id_lote,
                    lote_codigo_snapshot,
                    tipo_documento,
                    vigente,
                    version,
                    titulo,
                    plantilla,
                    archivo_path,
                    datos_json,
                    estado,
                    id_usuario,
                    created_at
                ) VALUES (
                    :id_lote,
                    :lote_codigo_snapshot,
                    :tipo_documento,
                    1,
                    :version,
                    :titulo,
                    :plantilla,
                    NULL,
                    :datos_json,
                    'VIGENTE',
                    :id_usuario,
                    NOW()
                )
            ");
            $stI->execute([
                ':id_lote'              => $idLote,
                ':lote_codigo_snapshot' => $loteCodigoSnapshot,
                ':tipo_documento'       => $tipo,
                ':version'              => $newVersion,
                ':titulo'               => $titulo,
                ':plantilla'            => $plant,
                ':datos_json'           => $datosJson,
                ':id_usuario'           => $idUsuario,
            ]);

            $newId = (int)$this->db->lastInsertId();
            $this->db->commit();

            return [
                'status'  => true,
                'msg'     => 'Documento guardado',
                'id'      => $newId,
                'version' => $newVersion,
                'plantilla' => $plant,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['status' => false, 'msg' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    private function getLoteCodigoSnapshot(int $idLote): string
    {
        // Snapshot del código del lote (por si luego cambian códigos)
        try {
            $st = $this->db->prepare("SELECT codigo FROM lotes WHERE id = :id LIMIT 1");
            $st->execute([':id' => $idLote]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return (string)($row['codigo'] ?? '');
        } catch (Exception $e) {
            return '';
        }
    }
}
