<?php
// app/Models/ClientesModel.php
require_once __DIR__ . '/../Config/Database.php';

class ClientesModel
{
    /** @var PDO */
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /* =======================================================
     * CLIENTES
     * ======================================================= */

    // Para DataTable, con filtro por rol/usuario
    public function getAllClientes(int $userId = 0, string $role = 'visita')
    {
        $sql = "
            SELECT
                c.id,
                c.tipo_persona,
                c.numero_documento,
                c.nombres,
                c.apellidos,
                c.razon_social,
                c.tipo_cliente,
                c.nivel_interes,
                c.telefono,
                c.estado,
                u.fullname AS responsable
            FROM clientes c
            LEFT JOIN users u ON u.id = c.id_user_responsable
            WHERE c.deleted_at IS NULL
        ";

        $params = [];

        // Si NO es admin, solo ve sus clientes
        if ($role !== 'admin' && $userId > 0) {
            $sql .= "
                AND (
                    c.id_user_responsable = :uid
                    OR (c.id_user_responsable IS NULL AND c.id_user = :uid)
                )
            ";
            $params[':uid'] = $userId;
        }

        $sql .= " ORDER BY c.id DESC";

        $st = $this->db->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getClienteById(int $id)
    {
        $sql = "
            SELECT *
            FROM clientes
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }



    public function saveCliente(array $data, int $currentUserId): array
    {
        $id              = (int)($data['id'] ?? 0);
        $tipoPersona     = (int)($data['tipo_persona'] ?? 1);
        $tipoDocumento   = (int)($data['tipo_documento'] ?? 0);
        $numeroDocumento = trim($data['numero_documento'] ?? '');

        if ($tipoDocumento <= 0 || $numeroDocumento === '') {
            return ['status' => false, 'msg' => 'Tipo y número de documento son obligatorios'];
        }

        // --------------------------------------------
        // Validar documento único (no borrado)
        // --------------------------------------------
        $sqlDup = "SELECT id FROM clientes WHERE numero_documento = :doc AND deleted_at IS NULL";
        $paramsDup = [':doc' => $numeroDocumento];
        if ($id > 0) {
            $sqlDup .= " AND id <> :id";
            $paramsDup[':id'] = $id;
        }

        $stDup = $this->db->prepare($sqlDup);
        $stDup->execute($paramsDup);
        if ($stDup->fetch(PDO::FETCH_ASSOC)) {
            return ['status' => false, 'msg' => 'El número de documento ya está registrado'];
        }

        // --------------------------------------------
        // Normalizamos campos
        // --------------------------------------------
        $idUserResponsable    = !empty($data['id_user_responsable']) ? (int)$data['id_user_responsable'] : null;
        $idUbigeo             = !empty($data['id_ubigeo']) ? $data['id_ubigeo'] : null;
        $fechaNacimiento      = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;
        $fechaUltimoContacto  = !empty($data['fecha_ultimo_contacto']) ? $data['fecha_ultimo_contacto'] : null;

        // Base de parámetros (los que se usan tanto en INSERT como en UPDATE)
        $baseParams = [
            ':id_user_responsable'      => $idUserResponsable,
            ':tipo_persona'             => $tipoPersona,
            ':tipo_documento'           => $tipoDocumento,
            ':numero_documento'         => $numeroDocumento,
            ':nombres'                  => trim($data['nombres'] ?? ''),
            ':apellidos'                => trim($data['apellidos'] ?? ''),
            ':razon_social'             => trim($data['razon_social'] ?? ''),
            ':fecha_nacimiento'         => $fechaNacimiento,
            ':estado_civil'             => trim($data['estado_civil'] ?? ''),
            ':telefono'                 => trim($data['telefono'] ?? ''),
            ':telefono_alt'             => trim($data['telefono_alt'] ?? ''),
            ':email'                    => trim($data['email'] ?? ''),
            ':id_ubigeo'                => $idUbigeo,
            ':direccion'                => trim($data['direccion'] ?? ''),
            ':referencia_direccion'     => trim($data['referencia_direccion'] ?? ''),
            ':tipo_cliente'             => trim($data['tipo_cliente'] ?? 'PROSPECTO'),
            ':nivel_interes'            => trim($data['nivel_interes'] ?? 'MEDIO'),
            ':medio_contacto_preferido' => trim($data['medio_contacto_preferido'] ?? 'WHATSAPP'),
            ':origen'                   => trim($data['origen'] ?? ''),
            ':estado'                   => trim($data['estado'] ?? 'ACTIVO'),
            ':observaciones'            => trim($data['observaciones'] ?? ''),
            ':fecha_ultimo_contacto'    => $fechaUltimoContacto,
        ];

        // --------------------------------------------
        // INSERT
        // --------------------------------------------
        if ($id <= 0) {
            $sql = "
                INSERT INTO clientes (
                    id_user,
                    id_user_responsable,
                    tipo_persona,
                    tipo_documento,
                    numero_documento,
                    nombres,
                    apellidos,
                    razon_social,
                    fecha_nacimiento,
                    estado_civil,
                    telefono,
                    telefono_alt,
                    email,
                    id_ubigeo,
                    direccion,
                    referencia_direccion,
                    tipo_cliente,
                    nivel_interes,
                    medio_contacto_preferido,
                    origen,
                    estado,
                    observaciones,
                    fecha_ultimo_contacto,
                    fecha_registro,
                    created_at
                ) VALUES (
                    :id_user,
                    :id_user_responsable,
                    :tipo_persona,
                    :tipo_documento,
                    :numero_documento,
                    :nombres,
                    :apellidos,
                    :razon_social,
                    :fecha_nacimiento,
                    :estado_civil,
                    :telefono,
                    :telefono_alt,
                    :email,
                    :id_ubigeo,
                    :direccion,
                    :referencia_direccion,
                    :tipo_cliente,
                    :nivel_interes,
                    :medio_contacto_preferido,
                    :origen,
                    :estado,
                    :observaciones,
                    :fecha_ultimo_contacto,
                    NOW(),
                    NOW()
                )
            ";

            $params = $baseParams + [
                ':id_user' => $currentUserId,
            ];

            $st = $this->db->prepare($sql);
            try {
                $ok = $st->execute($params);
            } catch (PDOException $e) {
                return ['status' => false, 'msg' => 'Error BD: ' . $e->getMessage()];
            }

            if ($ok) {
                $newId = (int)$this->db->lastInsertId();
                return ['status' => true, 'id' => $newId, 'msg' => 'Cliente creado'];
            }

            return ['status' => false, 'msg' => 'No se pudo insertar el cliente'];
        }

        // --------------------------------------------
        // UPDATE
        // --------------------------------------------
        $sql = "
            UPDATE clientes SET
                id_user_responsable      = :id_user_responsable,
                tipo_persona             = :tipo_persona,
                tipo_documento           = :tipo_documento,
                numero_documento         = :numero_documento,
                nombres                  = :nombres,
                apellidos                = :apellidos,
                razon_social             = :razon_social,
                fecha_nacimiento         = :fecha_nacimiento,
                estado_civil             = :estado_civil,
                telefono                 = :telefono,
                telefono_alt             = :telefono_alt,
                email                    = :email,
                id_ubigeo                = :id_ubigeo,
                direccion                = :direccion,
                referencia_direccion     = :referencia_direccion,
                tipo_cliente             = :tipo_cliente,
                nivel_interes            = :nivel_interes,
                medio_contacto_preferido = :medio_contacto_preferido,
                origen                   = :origen,
                estado                   = :estado,
                observaciones            = :observaciones,
                fecha_ultimo_contacto    = :fecha_ultimo_contacto,
                updated_at               = NOW()
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $params = $baseParams + [
            ':id' => $id,
        ];

        $st = $this->db->prepare($sql);
        try {
            $ok = $st->execute($params);
        } catch (PDOException $e) {
            return ['status' => false, 'msg' => 'Error BD: ' . $e->getMessage()];
        }

        return [
            'status' => $ok,
            'id'     => $id,
            'msg'    => $ok ? 'Cliente actualizado' : 'No se pudo actualizar el cliente'
        ];
    }



    public function deleteCliente(int $id): bool
    {
        $sql = "
            UPDATE clientes
            SET estado = 'INACTIVO',
                deleted_at = NOW()
            WHERE id = :id
              AND deleted_at IS NULL
        ";
        $st = $this->db->prepare($sql);
        return $st->execute([':id' => $id]);
    }

    /* =======================================================
     * COPROPIETARIOS
     * ======================================================= */

    public function getCopropietariosByCliente(int $idCliente): array
    {
        $sql = "
            SELECT cp.*
            FROM copropietarios cp
            WHERE cp.id_cliente = :id_cliente
              AND cp.deleted_at IS NULL
            ORDER BY cp.id DESC
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id_cliente' => $idCliente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCopropietarioById(int $id)
    {
        $sql = "
            SELECT *
            FROM copropietarios
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function saveCopropietario(array $data): array
    {
        $id              = (int)($data['id'] ?? 0);
        $idCliente       = (int)($data['id_cliente'] ?? 0);
        $tipoPersona     = (int)($data['tipo_persona'] ?? 1);
        $tipoDocumento   = (int)($data['tipo_documento'] ?? 0);
        $numeroDocumento = trim($data['numero_documento'] ?? '');
        $parentesco      = trim($data['parentesco'] ?? '');

        if ($idCliente <= 0 || $tipoDocumento <= 0 || $numeroDocumento === '' || $parentesco === '') {
            return ['status' => false, 'msg' => 'Complete los datos obligatorios del copropietario'];
        }

        // Validar documento único por cliente
        $sqlDup = "
            SELECT id
            FROM copropietarios
            WHERE id_cliente = :id_cliente
              AND numero_documento = :doc
              AND deleted_at IS NULL
        ";
        $paramsDup = [
            ':id_cliente' => $idCliente,
            ':doc'        => $numeroDocumento,
        ];
        if ($id > 0) {
            $sqlDup .= " AND id <> :id";
            $paramsDup[':id'] = $id;
        }

        $stDup = $this->db->prepare($sqlDup);
        $stDup->execute($paramsDup);
        if ($stDup->fetch(PDO::FETCH_ASSOC)) {
            return ['status' => false, 'msg' => 'Ya existe un copropietario con ese documento para este cliente'];
        }

        $payload = [
            ':id_cliente'             => $idCliente,
            ':id_cliente_relacionado' => isset($data['id_cliente_relacionado']) && $data['id_cliente_relacionado'] !== ''
                                        ? (int)$data['id_cliente_relacionado']
                                        : null,
            ':tipo_persona'           => $tipoPersona,
            ':tipo_documento'         => $tipoDocumento,
            ':numero_documento'       => $numeroDocumento,
            ':nombres'                => trim($data['nombres'] ?? ''),
            ':apellidos'              => trim($data['apellidos'] ?? ''),
            ':fecha_nacimiento'       => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
            ':telefono'               => trim($data['telefono'] ?? ''),
            ':email'                  => trim($data['email'] ?? ''),
            ':direccion'              => trim($data['direccion'] ?? ''),
            ':parentesco'             => $parentesco,
            ':porcentaje_part'        => isset($data['porcentaje_participacion']) && $data['porcentaje_participacion'] !== ''
                                        ? (float)$data['porcentaje_participacion']
                                        : null,
            ':estado'                 => trim($data['estado'] ?? 'ACTIVO'),
            ':observaciones'          => trim($data['observaciones'] ?? ''),
        ];

        if ($id <= 0) {
            // INSERT
            $sql = "
                INSERT INTO copropietarios (
                    id_cliente,
                    id_cliente_relacionado,
                    tipo_persona,
                    tipo_documento,
                    numero_documento,
                    nombres,
                    apellidos,
                    fecha_nacimiento,
                    telefono,
                    email,
                    direccion,
                    parentesco,
                    porcentaje_participacion,
                    estado,
                    observaciones,
                    fecha_registro,
                    created_at
                ) VALUES (
                    :id_cliente,
                    :id_cliente_relacionado,
                    :tipo_persona,
                    :tipo_documento,
                    :numero_documento,
                    :nombres,
                    :apellidos,
                    :fecha_nacimiento,
                    :telefono,
                    :email,
                    :direccion,
                    :parentesco,
                    :porcentaje_part,
                    :estado,
                    :observaciones,
                    NOW(),
                    NOW()
                )
            ";
            $st = $this->db->prepare($sql);
            try {
                $ok = $st->execute($payload);
            } catch (PDOException $e) {
                return ['status' => false, 'msg' => 'Error BD: ' . $e->getMessage()];
            }

            if ($ok) {
                return ['status' => true, 'id' => (int)$this->db->lastInsertId()];
            }
            return ['status' => false, 'msg' => 'No se pudo insertar el copropietario'];
        }

        // UPDATE
        $payload[':id'] = $id;

        $sql = "
            UPDATE copropietarios SET
                id_cliente               = :id_cliente,
                id_cliente_relacionado   = :id_cliente_relacionado,
                tipo_persona             = :tipo_persona,
                tipo_documento           = :tipo_documento,
                numero_documento         = :numero_documento,
                nombres                  = :nombres,
                apellidos                = :apellidos,
                fecha_nacimiento         = :fecha_nacimiento,
                telefono                 = :telefono,
                email                    = :email,
                direccion                = :direccion,
                parentesco               = :parentesco,
                porcentaje_participacion = :porcentaje_part,
                estado                   = :estado,
                observaciones            = :observaciones,
                updated_at               = NOW()
            WHERE id = :id
              AND deleted_at IS NULL
        ";
        $st = $this->db->prepare($sql);
        try {
            $ok = $st->execute($payload);
        } catch (PDOException $e) {
            return ['status' => false, 'msg' => 'Error BD: ' . $e->getMessage()];
        }

        return [
            'status' => $ok,
            'id'     => $id,
            'msg'    => $ok ? 'Copropietario actualizado' : 'No se pudo actualizar el copropietario'
        ];
    }

    public function deleteCopropietario(int $id): bool
    {
        $sql = "
            UPDATE copropietarios
            SET estado = 'INACTIVO',
                deleted_at = NOW()
            WHERE id = :id
              AND deleted_at IS NULL
        ";
        $st = $this->db->prepare($sql);
        return $st->execute([':id' => $id]);
    }
}
