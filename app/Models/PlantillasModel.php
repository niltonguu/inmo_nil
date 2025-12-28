<?php
// app/Models/LotesModel.php

require_once __DIR__ . '/../Config/Database.php';

class PlantillasModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getById($templateID)
    {
        $query = "
            SELECT *
            FROM plantillas_doc
            WHERE id = :id
        ";

        $stm = $this->db->prepare($query);
        $stm->bindValue(":id", $templateID);
        $stm->execute();

        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    public function resolveActive(string $tipoDocumento, string $tipoPersona): array
    {
        $sql = "
            SELECT id, tipo_documento, tipo_persona, asunto
            FROM plantillas_doc
            WHERE tipo_documento = :td
          AND tipo_persona   = :tp
          AND estado         = 'ACTIVO'
        ORDER BY id DESC
        LIMIT 1
    ";

    $stm = $this->db->prepare($sql);
    $stm->bindValue(':td', $tipoDocumento);
    $stm->bindValue(':tp', $tipoPersona);
    $stm->execute();

    $row = $stm->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : [];
}

}
