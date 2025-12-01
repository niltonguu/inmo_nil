<?php
// app/Models/ContactModel.php
require_once __DIR__ . '/../Config/Database.php';

class ContactModel {
    private $db;
    public function __construct(){
        $this->db = (new Database())->connect();
    }

    public function listAll(){
        $sql = "
          SELECT c.id,
                 CONCAT_WS(' ', c.nombres, c.apellidos) as nombres,
                 u.descripcion as ubigeo,
                 c.estado,
                 c.aceptacion,
                 (SELECT nota FROM notas n WHERE n.id_contacto = c.id ORDER BY created_at DESC LIMIT 1) as ultima_nota
          FROM contactos c
          LEFT JOIN ubigeos u ON u.id = c.id_ubigeo
          ORDER BY c.id DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function insertNote(int $id_contacto, string $nota){
        $sql = "INSERT INTO notas (id_contacto, nota) VALUES (:idc, :nota)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':idc' => $id_contacto, ':nota' => $nota]);
    }
}
