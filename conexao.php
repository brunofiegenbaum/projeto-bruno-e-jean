<?php
$servidor = getenv('DB_HOST') ?: 'localhost';
$banco = getenv('DB_NAME') ?: 'projetosemestre';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASSWORD');
if ($senha === false) {
    $senha = '';
}

try {
    $conexao = new PDO(
        "mysql:host=$servidor;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $erro) {
    error_log('Erro de conexao com o banco de dados: ' . $erro->getMessage());
    http_response_code(500);
    die('Erro ao conectar ao banco de dados.');
}
