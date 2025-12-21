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
}
