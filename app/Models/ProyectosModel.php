<?php
// app/Models/ProyectosModel.php
require_once __DIR__ . '/../Config/Database.php';

class ProyectosModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /* =========================
     * PROYECTOS
     * =======================*/

    public function getAllProyectos()
    {
        $sql = "
            SELECT p.*, u.descripcion AS ubigeo_descripcion
            FROM proyectos p
            LEFT JOIN ubigeos u ON u.id = p.id_ubigeo
            ORDER BY p.id DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProyectoById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.descripcion AS ubigeo_descripcion
            FROM proyectos p
            LEFT JOIN ubigeos u ON u.id = p.id_ubigeo
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveProyecto(array $data)
    {
        $payload = [
            ':codigo'         => trim($data['codigo'] ?? ''),
            ':nombre'         => trim($data['nombre'] ?? ''),
            ':id_ubigeo'      => trim($data['id_ubigeo'] ?? ''),
            ':latitud'        => ($data['latitud'] ?? '') !== '' ? $data['latitud'] : null,
            ':longitud'       => ($data['longitud'] ?? '') !== '' ? $data['longitud'] : null,
            ':zoom_mapa'      => ($data['zoom_mapa'] ?? '') !== '' ? (int)$data['zoom_mapa'] : null,
            ':precio_m2_base' => ($data['precio_m2_base'] ?? '') !== '' ? (float)$data['precio_m2_base'] : 0,
            ':factor_min_pct' => ($data['factor_min_pct'] ?? '') !== '' ? (float)$data['factor_min_pct'] : -40.0,
            ':factor_max_pct' => ($data['factor_max_pct'] ?? '') !== '' ? (float)$data['factor_max_pct'] : 50.0,
            ':estado_legal'   => $data['estado_legal'] ?? 'EN_TRAMITE',
            ':estado'         => $data['estado'] ?? 'ACTIVO',
        ];

        if (empty($data['id'])) {
            $sql = "
                INSERT INTO proyectos
                    (codigo, nombre, id_ubigeo, latitud, longitud, zoom_mapa,
                     precio_m2_base, factor_min_pct, factor_max_pct,
                     estado_legal, estado)
                VALUES
                    (:codigo, :nombre, :id_ubigeo, :latitud, :longitud, :zoom_mapa,
                     :precio_m2_base, :factor_min_pct, :factor_max_pct,
                     :estado_legal, :estado)
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        } else {
            $sql = "
                UPDATE proyectos SET
                    codigo          = :codigo,
                    nombre          = :nombre,
                    id_ubigeo       = :id_ubigeo,
                    latitud         = :latitud,
                    longitud        = :longitud,
                    zoom_mapa       = :zoom_mapa,
                    precio_m2_base  = :precio_m2_base,
                    factor_min_pct  = :factor_min_pct,
                    factor_max_pct  = :factor_max_pct,
                    estado_legal    = :estado_legal,
                    estado          = :estado
                WHERE id = :id
            ";
            $payload[':id'] = (int)$data['id'];
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        }
    }

    public function deleteProyecto(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM proyectos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* =========================
     * ETAPAS
     * =======================*/

    public function getEtapasByProyecto(int $id_proyecto)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM etapas
            WHERE id_proyecto = :p
            ORDER BY numero ASC, id ASC
        ");
        $stmt->execute([':p' => $id_proyecto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveEtapa(array $data)
    {
        $id          = (int)($data['id'] ?? 0);
        $id_proyecto = (int)($data['id_proyecto'] ?? 0);
        $nombre      = trim($data['nombre'] ?? '');
        $numero      = ($data['numero'] ?? '') !== '' ? (int)$data['numero'] : null;
        $habilitada  = isset($data['habilitada_venta']) ? 1 : 0;

        if ($id_proyecto <= 0 || $nombre === '') {
            return false;
        }

        if ($id <= 0) {
            // INSERT
            $sql = "
                INSERT INTO etapas
                    (id_proyecto, nombre, numero, habilitada_venta)
                VALUES
                    (:id_proyecto, :nombre, :numero, :habilitada_venta)
            ";

            $payload = [
                ':id_proyecto'     => $id_proyecto,
                ':nombre'          => $nombre,
                ':numero'          => $numero,
                ':habilitada_venta'=> $habilitada,
            ];

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        }

        // UPDATE
        $sql = "
            UPDATE etapas SET
                id_proyecto     = :id_proyecto,
                nombre          = :nombre,
                numero          = :numero,
                habilitada_venta= :habilitada_venta
            WHERE id = :id
        ";

        $payload = [
            ':id_proyecto'     => $id_proyecto,
            ':nombre'          => $nombre,
            ':numero'          => $numero,
            ':habilitada_venta'=> $habilitada,
            ':id'              => $id,
        ];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($payload);
    }

    public function deleteEtapa(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM etapas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function generateEtapas(int $id_proyecto, int $cantidad)
    {
        if ($id_proyecto <= 0 || $cantidad <= 0) return false;

        $sql = "
            INSERT INTO etapas (id_proyecto, nombre, numero, habilitada_venta)
            VALUES (:p, :nombre, :numero, 0)
        ";
        $stmt = $this->db->prepare($sql);

        $this->db->beginTransaction();
        try {
            for ($i = 1; $i <= $cantidad; $i++) {
                $nombre = "Etapa " . $i;
                $stmt->execute([
                    ':p'      => $id_proyecto,
                    ':nombre' => $nombre,
                    ':numero' => $i,
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /* =========================
     * MANZANAS
     * =======================*/

    public function getManzanasByProyectoYEtapa(int $id_proyecto, ?int $id_etapa = null)
    {
        $sql = "
            SELECT m.*, e.nombre AS etapa_nombre
            FROM manzanas m
            JOIN etapas e ON e.id = m.id_etapa
            WHERE e.id_proyecto = :p
        ";
        $params = [':p' => $id_proyecto];

        if (!empty($id_etapa)) {
            $sql .= " AND e.id = :e";
            $params[':e'] = $id_etapa;
        }

        $sql .= " ORDER BY e.numero ASC, m.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveManzana(array $data)
    {
        $id        = (int)($data['id'] ?? 0);
        $id_etapa  = (int)($data['id_etapa'] ?? 0);
        $codigo    = trim($data['codigo'] ?? '');
        $desc      = trim($data['descripcion'] ?? '');

        if ($id_etapa <= 0 || $codigo === '') {
            return false;
        }

        if ($id <= 0) {
            // INSERT
            $sql = "
                INSERT INTO manzanas (id_etapa, codigo, descripcion)
                VALUES (:id_etapa, :codigo, :descripcion)
            ";

            $payload = [
                ':id_etapa'    => $id_etapa,
                ':codigo'      => $codigo,
                ':descripcion' => $desc,
            ];

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        }

        // UPDATE
        $sql = "
            UPDATE manzanas SET
                id_etapa    = :id_etapa,
                codigo      = :codigo,
                descripcion = :descripcion
            WHERE id = :id
        ";

        $payload = [
            ':id_etapa'    => $id_etapa,
            ':codigo'      => $codigo,
            ':descripcion' => $desc,
            ':id'          => $id,
        ];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($payload);
    }

    public function deleteManzana(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM manzanas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function generateManzanas(int $id_etapa, string $letra_inicio, string $letra_fin)
    {
        $id_etapa = (int)$id_etapa;
        if ($id_etapa <= 0) return false;

        $start = strtoupper(trim($letra_inicio));
        $end   = strtoupper(trim($letra_fin));

        if (strlen($start) !== 1 || strlen($end) !== 1) return false;

        $ordStart = ord($start);
        $ordEnd   = ord($end);

        if ($ordEnd < $ordStart) return false;

        $sql = "
            INSERT INTO manzanas (id_etapa, codigo, descripcion)
            VALUES (:id_etapa, :codigo, '')
        ";
        $stmt = $this->db->prepare($sql);

        $this->db->beginTransaction();
        try {
            for ($c = $ordStart; $c <= $ordEnd; $c++) {
                $codigo = chr($c);
                $stmt->execute([
                    ':id_etapa' => $id_etapa,
                    ':codigo'   => $codigo,
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
