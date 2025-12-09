<?php
// app/Models/FactoresModel.php

require_once __DIR__ . '/../Config/Database.php';

class FactoresModel
{
    /** @var PDO */
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // =========================================================
    // FACTORES POR PROYECTO
    // =========================================================

    public function getFactoresByProyecto(int $idProyecto): array
    {
        $sql = "
            SELECT
                id,
                id_proyecto,
                codigo,
                nombre,
                cat_factor,
                valor_pct,
                activo,
                created_at,
                updated_at
            FROM factores
            WHERE id_proyecto = :id_proyecto
            ORDER BY cat_factor, nombre
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_proyecto' => $idProyecto]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveFactor(array $data): bool
    {
        $id         = isset($data['id']) ? (int)$data['id'] : 0;
        $idProyecto = (int)($data['id_proyecto'] ?? 0);
        $nombre     = trim($data['nombre'] ?? '');
        $catFactor  = trim($data['cat_factor'] ?? '');
        $codigo     = trim($data['codigo'] ?? '');
        $valorPct   = isset($data['valor_pct']) ? (float)$data['valor_pct'] : 0.0;
        $activo     = isset($data['activo']) && $data['activo'] ? 1 : 0;

        if ($id > 0) {
            // UPDATE
            $sql = "
                UPDATE factores
                SET
                    id_proyecto = :id_proyecto,
                    codigo      = :codigo,
                    nombre      = :nombre,
                    cat_factor  = :cat_factor,
                    valor_pct   = :valor_pct,
                    activo      = :activo,
                    updated_at  = NOW()
                WHERE id = :id
            ";
        } else {
            // INSERT
            $sql = "
                INSERT INTO factores (
                    id_proyecto,
                    codigo,
                    nombre,
                    cat_factor,
                    valor_pct,
                    activo,
                    created_at
                ) VALUES (
                    :id_proyecto,
                    :codigo,
                    :nombre,
                    :cat_factor,
                    :valor_pct,
                    :activo,
                    NOW()
                )
            ";
        }

        $params = [
            ':id_proyecto' => $idProyecto,
            ':codigo'      => $codigo,
            ':nombre'      => $nombre,
            ':cat_factor'  => $catFactor,
            ':valor_pct'   => $valorPct,
            ':activo'      => $activo,
        ];

        if ($id > 0) {
            $params[':id'] = $id;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteFactor(int $id): bool
    {
        $sql = "DELETE FROM factores WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // =========================================================
    // FACTORES APLICADOS A LOTE
    // =========================================================

    public function getFactorIdsByLote(int $idLote): array
    {
        $sql = "SELECT id_factor FROM lote_factores WHERE id_lote = :id_lote";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_lote' => $idLote]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out  = [];

        foreach ($rows as $r) {
            $out[] = (int)$r['id_factor'];
        }

        return $out;
    }

    public function saveLoteFactores(int $idLote, array $idsFactores, int $idUsuario): bool
    {
        $this->db->beginTransaction();

        try {
            $del = $this->db->prepare("DELETE FROM lote_factores WHERE id_lote = :id_lote");
            $del->execute([':id_lote' => $idLote]);

            if (!empty($idsFactores)) {
                $ins = $this->db->prepare("
                    INSERT INTO lote_factores (id_lote, id_factor)
                    VALUES (:id_lote, :id_factor)
                ");

                foreach ($idsFactores as $idFactor) {
                    $ins->execute([
                        ':id_lote'   => $idLote,
                        ':id_factor' => (int)$idFactor,
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
