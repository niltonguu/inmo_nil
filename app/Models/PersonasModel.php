<?php
// app/Models/PersonasModel.php
require_once __DIR__ . '/../Config/Database.php';

class PersonasModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Lista general (admin ve todo)
    public function getAll() {
        $sql = "
            SELECT 
                p.id,
                p.nombres,
                p.apellidos,
                p.numero_documento,
                p.telefono,
                p.email,
                p.estado,
                p.etiqueta,
                p.asignado,
                u.fullname AS asignado_nombre,
                ub.descripcion AS ubigeo_descripcion,
                (
                    SELECT n.nota 
                    FROM notas n 
                    WHERE n.id_persona = p.id 
                    ORDER BY n.id DESC 
                    LIMIT 1
                ) AS ultima_nota
            FROM personas p
            LEFT JOIN ubigeos ub ON ub.id = p.id_ubigeo
            LEFT JOIN users  u  ON u.id = p.asignado
            ORDER BY p.id DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByEtiqueta(string $etiqueta) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id, p.nombres, p.apellidos, p.numero_documento,
                p.telefono, p.email, p.estado, p.etiqueta,
                p.asignado, u.fullname AS asignado_nombre,
                ub.descripcion AS ubigeo_descripcion,
                (SELECT n.nota FROM notas n WHERE n.id_persona=p.id ORDER BY n.id DESC LIMIT 1) AS ultima_nota
            FROM personas p
            LEFT JOIN ubigeos ub ON ub.id=p.id_ubigeo
            LEFT JOIN users u ON u.id=p.asignado
            WHERE p.etiqueta = :etiqueta
            ORDER BY p.id DESC
        ");
        $stmt->execute([':etiqueta'=>$etiqueta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByEtiquetaAndUser(string $etiqueta, int $id_user) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id, p.nombres, p.apellidos, p.numero_documento,
                p.telefono, p.email, p.estado, p.etiqueta,
                p.asignado, u.fullname AS asignado_nombre,
                ub.descripcion AS ubigeo_descripcion,
                (SELECT n.nota FROM notas n WHERE n.id_persona=p.id ORDER BY n.id DESC LIMIT 1) AS ultima_nota
            FROM personas p
            LEFT JOIN ubigeos ub ON ub.id=p.id_ubigeo
            LEFT JOIN users u ON u.id=p.asignado
            WHERE p.etiqueta = :etiqueta
            AND p.asignado = :id_user
            ORDER BY p.id DESC
        ");
        $stmt->execute([':etiqueta'=>$etiqueta, ':id_user'=>$id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Lista filtrada por asignado (usuario/visita)
    public function getAllByAssigned(int $id_user) {
        $sql = "
            SELECT 
                p.id,
                p.nombres,
                p.apellidos,
                p.numero_documento,
                p.telefono,
                p.email,
                p.estado,
                p.etiqueta,
                p.asignado,
                u.fullname AS asignado_nombre,
                ub.descripcion AS ubigeo_descripcion,
                (
                    SELECT n.nota 
                    FROM notas n 
                    WHERE n.id_persona = p.id 
                    ORDER BY n.id DESC 
                    LIMIT 1
                ) AS ultima_nota
            FROM personas p
            LEFT JOIN ubigeos ub ON ub.id = p.id_ubigeo
            LEFT JOIN users  u  ON u.id = p.asignado
            WHERE p.asignado = :id_user
            ORDER BY p.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM personas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(array $data, int $id_user)
    {
        $isUpdate = !empty($data['id']);

        // Si es UPDATE y no viene "asignado" en el form,
        // tomamos el valor actual de la BD para NO perderlo.
        if ($isUpdate && !array_key_exists('asignado', $data)) {
            $stmt = $this->db->prepare("SELECT asignado FROM personas WHERE id = :id");
            $stmt->execute([':id' => (int)$data['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['asignado'] = $row ? $row['asignado'] : null;
        }

        $payload = [
            ':id_user'          => $id_user, // solo se usará en INSERT
            ':tipo_persona'     => (int)($data['tipo_persona'] ?? 1),
            ':tipo_documento'   => (int)($data['tipo_documento'] ?? 2),
            ':numero_documento' => trim($data['numero_documento'] ?? ''),
            ':nombres'          => trim($data['nombres'] ?? ''),
            ':apellidos'        => trim($data['apellidos'] ?? ''),
            ':id_ubigeo'        => ($data['id_ubigeo'] ?? null) ?: null,
            ':telefono'         => trim($data['telefono'] ?? ''),
            ':email'            => trim($data['email'] ?? ''),
            ':estado'           => $data['estado'] ?? 'ACTIVO',
            ':asignado'         => (isset($data['asignado']) && $data['asignado'] !== '')
                                    ? (int)$data['asignado']
                                    : null,
            ':etiqueta'         => isset($data['etiqueta']) && $data['etiqueta'] !== ''
                                    ? $data['etiqueta']
                                    : 'NULL',
        ];

        if (!$isUpdate) {
            // INSERT
            $sql = "
                INSERT INTO personas
                (id_user, asignado, tipo_persona, tipo_documento, numero_documento,
                 nombres, apellidos, id_ubigeo, telefono, email, estado, etiqueta)
                VALUES
                (:id_user, :asignado, :tipo_persona, :tipo_documento, :numero_documento,
                 :nombres, :apellidos, :id_ubigeo, :telefono, :email, :estado, :etiqueta)
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        } else {
            // UPDATE (aquí ya no usamos :id_user)
            unset($payload[':id_user']);
            $payload[':id'] = (int)$data['id'];

            $sql = "
                UPDATE personas SET
                    asignado         = :asignado,
                    tipo_persona     = :tipo_persona,
                    tipo_documento   = :tipo_documento,
                    numero_documento = :numero_documento,
                    nombres          = :nombres,
                    apellidos        = :apellidos,
                    id_ubigeo        = :id_ubigeo,
                    telefono         = :telefono,
                    email            = :email,
                    estado           = :estado,
                    etiqueta         = :etiqueta
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($payload);
        }
    }


    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM personas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // NOTAS
    public function getNotes(int $id_persona, int $limit = 100) {
        $stmt = $this->db->prepare("
            SELECT id, nota, created_at 
            FROM notas 
            WHERE id_persona = :idp 
            ORDER BY created_at DESC, id DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':idp', $id_persona, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertNote(int $id_persona, string $nota) {
        $stmt = $this->db->prepare("
            INSERT INTO notas (id_persona, nota, created_at)
            VALUES (:idp, :nota, NOW())
        ");
        return $stmt->execute([
            ':idp'  => $id_persona,
            ':nota' => $nota
        ]);
    }

    // Asignar persona a usuario
    public function updateAssigned(int $id_persona, ?int $id_user) {
        $stmt = $this->db->prepare("
            UPDATE personas 
            SET asignado = :asignado
            WHERE id = :id
        ");
        return $stmt->execute([
            ':asignado' => $id_user,
            ':id'       => $id_persona
        ]);
    }

    // Registrar click en teléfono / WhatsApp
    public function logPhoneClick(int $id_user, int $id_persona)
    {
        $stmt = $this->db->prepare("
            INSERT INTO actividad_clicks (id_user, id_persona, tipo)
            VALUES (:u, :p, 'telefono')
        ");
        $stmt->execute([
            ':u' => $id_user,
            ':p' => $id_persona
        ]);
    }

    // Actualizar etiqueta (solo etiqueta)
    public function updateEtiqueta(int $id_persona, string $etiqueta)
    {
        $stmt = $this->db->prepare("
            UPDATE personas
            SET etiqueta = :etiqueta
            WHERE id = :id
        ");
        return $stmt->execute([
            ':etiqueta' => $etiqueta,
            ':id'       => $id_persona
        ]);
    }
}
