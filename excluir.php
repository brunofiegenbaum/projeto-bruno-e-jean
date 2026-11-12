<?php
require_once "api_cliente.php";
$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
if ($_SERVER["REQUEST_METHOD"] === "POST" && $id > 0) {
    try { chamarApi('veiculos', 'DELETE', null, ['id'=>$id]); }
    catch (RuntimeException $e) { http_response_code(500); exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')); }
}
header("Location: index.php");
exit;
