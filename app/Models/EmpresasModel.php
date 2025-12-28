<?php
// app/Models/EmpresasModel.php

require_once __DIR__ . '/../Config/Database.php';

class EmpresasModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // =========================================================
    // EMPRESAS
    // =========================================================

    public function listEmpresas(string $estado = '', string $q = ''): array
    {
        $sql = "
        SELECT 
            e.*,
            uf.descripcion AS ubigeo_fiscal_desc,
            uj.descripcion AS ubigeo_jurisdiccion_desc
        FROM empresas e
        LEFT JOIN ubigeos uf ON uf.id = e.id_ubigeo_fiscal
        LEFT JOIN ubigeos uj ON uj.id = e.id_ubigeo_jurisdiccion
        WHERE 1=1
        ";  
        
        $bind = [];

        if ($estado === 'ACTIVO' || $estado === 'INACTIVO') {
            $sql .= " AND estado = :estado ";
            $bind[':estado'] = $estado;
        }

        if ($q !== '') {
            $sql .= " AND (ruc LIKE :q OR razon_social LIKE :q OR nombre_comercial LIKE :q) ";
            $bind[':q'] = "%{$q}%";
        }

        $sql .= " ORDER BY id DESC";

        $st = $this->db->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll();
    }

    public function getEmpresa(int $id): ?array
    {
        $sql = "
            SELECT 
                e.*,
                uf.descripcion AS ubigeo_fiscal_desc,
                uj.descripcion AS ubigeo_jurisdiccion_desc
            FROM empresas e
            LEFT JOIN ubigeos uf ON uf.id = e.id_ubigeo_fiscal
            LEFT JOIN ubigeos uj ON uj.id = e.id_ubigeo_jurisdiccion
            WHERE e.id = ?
            LIMIT 1
        ";

        $st = $this->db->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }


    public function saveEmpresa(array $data): array
    {
        $id = (int)($data['id'] ?? 0);

        $payload = [
            'ruc' => trim($data['ruc'] ?? ''),
            'razon_social' => trim($data['razon_social'] ?? ''),
            'nombre_comercial' => trim($data['nombre_comercial'] ?? ''),
            'partida_registral' => trim($data['partida_registral'] ?? ''),
            'oficina_registral' => trim($data['oficina_registral'] ?? ''),
            'id_ubigeo_fiscal' => trim($data['id_ubigeo_fiscal'] ?? ''),
            'domicilio_fiscal' => trim($data['domicilio_fiscal'] ?? ''),
            'telefono' => trim($data['telefono'] ?? ''),
            'whatsapp' => trim($data['whatsapp'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'web' => trim($data['web'] ?? ''),
            'id_ubigeo_jurisdiccion' => trim($data['id_ubigeo_jurisdiccion'] ?? ''),
            'jurisdiccion_texto' => trim($data['jurisdiccion_texto'] ?? ''),
            'logo_path' => trim($data['logo_path'] ?? ''),
            'firma_digital_path' => trim($data['firma_digital_path'] ?? ''),
            'estado' => ($data['estado'] ?? 'ACTIVO'),
        ];

        // Vacíos a NULL para columnas opcionales
        $nullable = [
            'nombre_comercial','partida_registral','oficina_registral','id_ubigeo_fiscal',
            'domicilio_fiscal','whatsapp','web','id_ubigeo_jurisdiccion','logo_path','firma_digital_path'
        ];
        foreach ($nullable as $k) {
            if ($payload[$k] === '') $payload[$k] = null;
        }

        // Validación mínima
        if ($payload['ruc'] === '') return ['ok'=>false,'msg'=>'RUC es obligatorio'];
        if ($payload['razon_social'] === '') return ['ok'=>false,'msg'=>'Razón social es obligatoria'];
        if ($payload['telefono'] === '') return ['ok'=>false,'msg'=>'Teléfono es obligatorio'];
        if ($payload['email'] === '') return ['ok'=>false,'msg'=>'Email es obligatorio'];
        if ($payload['jurisdiccion_texto'] === '') return ['ok'=>false,'msg'=>'Jurisdicción (texto) es obligatoria'];

        if (!in_array($payload['estado'], ['ACTIVO','INACTIVO'], true)) $payload['estado'] = 'ACTIVO';

        if ($id > 0) {
            $sql = "
                UPDATE empresas SET
                  ruc=:ruc,
                  razon_social=:razon_social,
                  nombre_comercial=:nombre_comercial,
                  partida_registral=:partida_registral,
                  oficina_registral=:oficina_registral,
                  id_ubigeo_fiscal=:id_ubigeo_fiscal,
                  domicilio_fiscal=:domicilio_fiscal,
                  telefono=:telefono,
                  whatsapp=:whatsapp,
                  email=:email,
                  web=:web,
                  id_ubigeo_jurisdiccion=:id_ubigeo_jurisdiccion,
                  jurisdiccion_texto=:jurisdiccion_texto,
                  logo_path=:logo_path,
                  firma_digital_path=:firma_digital_path,
                  estado=:estado
                WHERE id=:id
            ";
            $st = $this->db->prepare($sql);
            $payload['id'] = $id;
            $st->execute($payload);
            return ['ok'=>true,'id'=>$id,'msg'=>'Empresa actualizada'];
        }

        $sql = "
            INSERT INTO empresas
              (ruc, razon_social, nombre_comercial, partida_registral, oficina_registral,
               id_ubigeo_fiscal, domicilio_fiscal,
               telefono, whatsapp, email, web,
               id_ubigeo_jurisdiccion, jurisdiccion_texto,
               logo_path, firma_digital_path, estado)
            VALUES
              (:ruc, :razon_social, :nombre_comercial, :partida_registral, :oficina_registral,
               :id_ubigeo_fiscal, :domicilio_fiscal,
               :telefono, :whatsapp, :email, :web,
               :id_ubigeo_jurisdiccion, :jurisdiccion_texto,
               :logo_path, :firma_digital_path, :estado)
        ";
        $st = $this->db->prepare($sql);
        $st->execute($payload);

        return ['ok'=>true,'id'=>(int)$this->db->lastInsertId(),'msg'=>'Empresa creada'];
    }

    public function inactivarEmpresa(int $id): array
    {
        $st = $this->db->prepare("UPDATE empresas SET estado='INACTIVO' WHERE id=?");
        $st->execute([$id]);
        return ['ok'=>true,'msg'=>'Empresa inactivada'];
    }

    // =========================================================
    // REPRESENTANTES
    // =========================================================

    public function listRepresentantes(int $idEmpresa): array
    {
        $st = $this->db->prepare("SELECT * FROM representantes_legales WHERE id_empresa=? ORDER BY id DESC");
        $st->execute([$idEmpresa]);
        return $st->fetchAll();
    }

    public function getRepresentante(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM representantes_legales WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function saveRepresentante(array $data): array
    {
        $id = (int)($data['id'] ?? 0);

        $payload = [
            'id_empresa' => (int)($data['id_empresa'] ?? 0),
            'tipo_documento' => (int)($data['tipo_documento'] ?? 0),
            'numero_documento' => trim($data['numero_documento'] ?? ''),
            'nombres' => trim($data['nombres'] ?? ''),
            'apellidos' => trim($data['apellidos'] ?? ''),
            'cargo' => trim($data['cargo'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'telefono' => trim($data['telefono'] ?? ''),
            'direccion' => trim($data['direccion'] ?? ''),
            'firma_path' => trim($data['firma_path'] ?? ''),
            'foto_path' => trim($data['foto_path'] ?? ''),
            'estado' => ($data['estado'] ?? 'ACTIVO'),
        ];

        $nullable = ['cargo','email','telefono','direccion','firma_path','foto_path'];
        foreach ($nullable as $k) if ($payload[$k] === '') $payload[$k] = null;

        if ($payload['id_empresa'] <= 0) return ['ok'=>false,'msg'=>'Empresa es obligatoria'];
        if ($payload['tipo_documento'] <= 0) return ['ok'=>false,'msg'=>'Tipo documento es obligatorio'];
        if ($payload['numero_documento'] === '') return ['ok'=>false,'msg'=>'Número documento es obligatorio'];
        if ($payload['nombres'] === '') return ['ok'=>false,'msg'=>'Nombres es obligatorio'];
        if ($payload['apellidos'] === '') return ['ok'=>false,'msg'=>'Apellidos es obligatorio'];

        if (!in_array($payload['estado'], ['ACTIVO','INACTIVO'], true)) $payload['estado'] = 'ACTIVO';

        if ($id > 0) {
            $sql = "
                UPDATE representantes_legales SET
                  id_empresa=:id_empresa,
                  tipo_documento=:tipo_documento,
                  numero_documento=:numero_documento,
                  nombres=:nombres,
                  apellidos=:apellidos,
                  cargo=:cargo,
                  email=:email,
                  telefono=:telefono,
                  direccion=:direccion,
                  firma_path=:firma_path,
                  foto_path=:foto_path,
                  estado=:estado
                WHERE id=:id
            ";
            $st = $this->db->prepare($sql);
            $payload['id'] = $id;
            $st->execute($payload);
            return ['ok'=>true,'id'=>$id,'msg'=>'Representante actualizado'];
        }

        $sql = "
            INSERT INTO representantes_legales
              (id_empresa, tipo_documento, numero_documento, nombres, apellidos,
               cargo, email, telefono, direccion, firma_path, foto_path, estado)
            VALUES
              (:id_empresa, :tipo_documento, :numero_documento, :nombres, :apellidos,
               :cargo, :email, :telefono, :direccion, :firma_path, :foto_path, :estado)
        ";
        $st = $this->db->prepare($sql);
        $st->execute($payload);

        return ['ok'=>true,'id'=>(int)$this->db->lastInsertId(),'msg'=>'Representante creado'];
    }

    public function inactivarRepresentante(int $id): array
    {
        $st = $this->db->prepare("UPDATE representantes_legales SET estado='INACTIVO' WHERE id=?");
        $st->execute([$id]);
        return ['ok'=>true,'msg'=>'Representante inactivado'];
    }

    // =========================================================
    // PODERES
    // =========================================================

    public function listPoderes(int $idRepresentante): array
    {
        $st = $this->db->prepare("
            SELECT *
            FROM poderes_empresa
            WHERE id_representante=?
            ORDER BY es_activo DESC, id DESC
        ");
        $st->execute([$idRepresentante]);
        return $st->fetchAll();
    }

    public function getPoder(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM poderes_empresa WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function savePoder(array $data): array
    {
        $id = (int)($data['id'] ?? 0);

        $payload = [
            'id_representante' => (int)($data['id_representante'] ?? 0),
            'tipo_poder' => trim($data['tipo_poder'] ?? 'GENERAL'),
            'partida_registral' => trim($data['partida_registral'] ?? ''),
            'asiento' => trim($data['asiento'] ?? ''),
            'oficina_registral' => trim($data['oficina_registral'] ?? ''),
            'fecha_inscripcion' => trim($data['fecha_inscripcion'] ?? ''),
            'vigencia_desde' => trim($data['vigencia_desde'] ?? ''),
            'vigencia_hasta' => trim($data['vigencia_hasta'] ?? ''),
            'estado' => trim($data['estado'] ?? 'VIGENTE'),
            'es_activo' => isset($data['es_activo']) ? (int)$data['es_activo'] : 0,
            'documento_path' => trim($data['documento_path'] ?? ''),
            'observaciones' => trim($data['observaciones'] ?? ''),
        ];

        $nullable = ['oficina_registral','fecha_inscripcion','vigencia_desde','vigencia_hasta','documento_path','observaciones'];
        foreach ($nullable as $k) if ($payload[$k] === '') $payload[$k] = null;

        if ($payload['id_representante'] <= 0) return ['ok'=>false,'msg'=>'Representante es obligatorio'];
        if ($payload['partida_registral'] === '') return ['ok'=>false,'msg'=>'Partida registral es obligatoria'];
        if ($payload['asiento'] === '') return ['ok'=>false,'msg'=>'Asiento es obligatorio'];

        if (!in_array($payload['tipo_poder'], ['GENERAL','ESPECIAL'], true)) $payload['tipo_poder'] = 'GENERAL';
        if (!in_array($payload['estado'], ['VIGENTE','VENCIDO','REVOCADO'], true)) $payload['estado'] = 'VIGENTE';
        $payload['es_activo'] = $payload['es_activo'] ? 1 : 0;

        if ($id > 0) {
            $sql = "
                UPDATE poderes_empresa SET
                  id_representante=:id_representante,
                  tipo_poder=:tipo_poder,
                  partida_registral=:partida_registral,
                  asiento=:asiento,
                  oficina_registral=:oficina_registral,
                  fecha_inscripcion=:fecha_inscripcion,
                  vigencia_desde=:vigencia_desde,
                  vigencia_hasta=:vigencia_hasta,
                  estado=:estado,
                  es_activo=:es_activo,
                  documento_path=:documento_path,
                  observaciones=:observaciones
                WHERE id=:id
            ";
            $st = $this->db->prepare($sql);
            $payload['id'] = $id;
            $st->execute($payload);
            return ['ok'=>true,'id'=>$id,'msg'=>'Poder actualizado'];
        }

        $sql = "
            INSERT INTO poderes_empresa
              (id_representante, tipo_poder, partida_registral, asiento, oficina_registral,
               fecha_inscripcion, vigencia_desde, vigencia_hasta, estado, es_activo,
               documento_path, observaciones)
            VALUES
              (:id_representante, :tipo_poder, :partida_registral, :asiento, :oficina_registral,
               :fecha_inscripcion, :vigencia_desde, :vigencia_hasta, :estado, :es_activo,
               :documento_path, :observaciones)
        ";
        $st = $this->db->prepare($sql);
        $st->execute($payload);

        return ['ok'=>true,'id'=>(int)$this->db->lastInsertId(),'msg'=>'Poder creado'];
    }

    public function activarPoder(int $idPoder): array
    {
        $st = $this->db->prepare("UPDATE poderes_empresa SET es_activo=1 WHERE id=?");
        $st->execute([$idPoder]);
        return ['ok'=>true,'msg'=>'Poder marcado como activo'];
    }

    public function getPoderVigenteActivo(int $idRepresentante): ?array
    {
        $st = $this->db->prepare("
            SELECT *
            FROM poderes_empresa
            WHERE id_representante = ?
              AND es_activo = 1
              AND estado = 'VIGENTE'
              AND (vigencia_desde IS NULL OR vigencia_desde <= CURDATE())
              AND (vigencia_hasta IS NULL OR vigencia_hasta >= CURDATE())
            LIMIT 1
        ");
        $st->execute([$idRepresentante]);
        $row = $st->fetch();
        return $row ?: null;
    }
}
