<?php
// app/Models/DashboardModel.php
require_once __DIR__ . '/../Config/Database.php';

class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getSummary(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM personas) AS total_personas,
                (SELECT COUNT(*) FROM personas WHERE asignado IS NOT NULL) AS personas_asignadas,
                (SELECT COUNT(*) FROM users) AS total_usuarios,
                (SELECT COUNT(*) FROM users 
                 WHERE last_active IS NOT NULL 
                   AND last_active >= NOW() - INTERVAL 2 MINUTE) AS online_usuarios,
                (SELECT COUNT(*) FROM personas WHERE etiqueta = 'PROSPECTO') AS prospectos,
                (SELECT COUNT(*) FROM personas WHERE etiqueta = 'SEPARADO') AS separados,
                (SELECT COUNT(*) FROM personas WHERE etiqueta = 'VENDIDO') AS vendidos,
                (SELECT COUNT(*) FROM personas WHERE etiqueta = 'PROBLEMAS') AS problemas
        ";
        $stmt = $this->db->query($sql);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_personas'      => (int)($row['total_personas'] ?? 0),
            'personas_asignadas'  => (int)($row['personas_asignadas'] ?? 0),
            'total_usuarios'      => (int)($row['total_usuarios'] ?? 0),
            'online_usuarios'     => (int)($row['online_usuarios'] ?? 0),
            'prospectos'          => (int)($row['prospectos'] ?? 0),
            'separados'           => (int)($row['separados'] ?? 0),
            'vendidos'            => (int)($row['vendidos'] ?? 0),
            'problemas'           => (int)($row['problemas'] ?? 0),
        ];
    }

    public function getUsersActivity(): array
    {
        $sql = "
            SELECT 
                u.id,
                u.fullname,
                u.role,
                COALESCE(u.login_count, 0) AS login_count,
                COUNT(DISTINCT p.id) AS personas_asignadas,
                (
                    SELECT COUNT(*) 
                    FROM actividad_clicks ac 
                    WHERE ac.id_user = u.id
                ) AS clicks,
                (
                    SELECT COUNT(*) 
                    FROM notas n
                    JOIN personas px ON px.id = n.id_persona
                    WHERE px.asignado = u.id
                ) AS notas
            FROM users u
            LEFT JOIN personas p ON p.asignado = u.id
            GROUP BY u.id, u.fullname, u.role, u.login_count
            ORDER BY u.fullname ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
