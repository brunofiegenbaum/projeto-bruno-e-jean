<?php
require_once "conexao.php";
require_once "VeiculoDAO.php";
$dao = new VeiculoDAO($conexao);
$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
if ($_SERVER["REQUEST_METHOD"] == "POST" && $id > 0) {
    $dao->excluir($id);
}
header("Location: index.php");
exit;
