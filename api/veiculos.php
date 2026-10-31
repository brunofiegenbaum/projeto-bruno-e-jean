<?php
header('Content-Type: application/json; charset=utf-8');

function responder($codigo, $dados)
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    responder(405, ['erro' => 'Método não permitido.']);
}

// A autenticação Bearer será implementada no Commit 5.
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../VeiculoDAO.php';
$dao = new VeiculoDAO($conexao);

try {
    if ($metodo === 'GET') {
        if (!array_key_exists('id', $_GET)) {
            responder(200, $dao->listar());
        }
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            responder(400, ['erro' => 'ID inválido.']);
        }
        $veiculo = $dao->buscarPorId($id);
        if (!$veiculo) {
            responder(404, ['erro' => 'Veículo não encontrado.']);
        }
        responder(200, $veiculo);
    }

    if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
        responder(400, ['erro' => 'Envie os dados em JSON.']);
    }
    $objeto = json_decode(file_get_contents('php://input'));
    if (!($objeto instanceof stdClass)) {
        responder(400, ['erro' => 'JSON inválido: informe um objeto.']);
    }
    $dados = (array) $objeto;

    foreach (['id_usuario', 'marca', 'modelo', 'ano', 'preco'] as $campo) {
        if (!array_key_exists($campo, $dados)) {
            responder(400, ['erro' => 'Campo obrigatório ausente: ' . $campo]);
        }
    }

    $idUsuario = filter_var($dados['id_usuario'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $ano = filter_var($dados['ano'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900]]);
    $km = filter_var($dados['quilometragem'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $preco = $dados['preco'];
    $marca = $dados['marca'];
    $modelo = $dados['modelo'];
    $foto = $dados['foto'] ?? null;

    if (
        $idUsuario === false || $ano === false || $km === false ||
        !is_string($marca) || trim($marca) === '' || strlen($marca) > 50 ||
        !is_string($modelo) || trim($modelo) === '' || strlen($modelo) > 50 ||
        !is_numeric($preco) || !is_scalar($preco) || !is_finite((float) $preco) || (float) $preco <= 0 || (float) $preco > 99999999.99 ||
        ($foto !== null && (!is_string($foto) || strlen($foto) > 255))
    ) {
        responder(400, ['erro' => 'Dados do veículo inválidos.']);
    }

    // Confere a FK antes de inserir para devolver um erro de validação amigável.
    $consulta = $conexao->prepare('SELECT id_usuario FROM usuarios WHERE id_usuario = ?');
    $consulta->execute([$idUsuario]);
    if (!$consulta->fetchColumn()) {
        responder(400, ['erro' => 'Usuário informado não existe.']);
    }

    $dao->inserir($idUsuario, trim($marca), trim($modelo), $ano, (float) $preco, $km, $foto);
    $novoId = (int) $conexao->lastInsertId();
    responder(201, ['mensagem' => 'Veículo cadastrado com sucesso.', 'id_veiculo' => $novoId, 'veiculo' => $dao->buscarPorId($novoId)]);
} catch (PDOException $erro) {
    error_log('Erro na API de veículos: ' . $erro->getMessage());
    responder(500, ['erro' => 'Erro interno ao acessar o banco de dados.']);
}
