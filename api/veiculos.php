<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($metodo === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function responder($codigo, $dados)
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Defina API_TOKEN no ambiente do Apache. Nao coloque o segredo no Git.
$tokenEsperado = getenv('API_TOKEN');
if (!is_string($tokenEsperado) || $tokenEsperado === '') {
    error_log('API_TOKEN nao configurado.');
    responder(500, ['erro' => 'Autenticacao da API nao configurada.']);
}

$autorizacao = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if ($autorizacao === '' && function_exists('getallheaders')) {
    foreach (getallheaders() as $nome => $valor) {
        if (strcasecmp($nome, 'Authorization') === 0) {
            $autorizacao = $valor;
            break;
        }
    }
}

if (!is_string($autorizacao) || !preg_match('/^Bearer\s+(\S+)$/i', trim($autorizacao), $partes) || !hash_equals($tokenEsperado, $partes[1])) {
    header('WWW-Authenticate: Bearer');
    responder(401, ['erro' => 'Token ausente ou invalido.']);
}

if (!in_array($metodo, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    responder(405, ['erro' => 'Método não permitido.']);
}

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

    if ($metodo === 'PUT' || $metodo === 'DELETE') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $id === null) {
            responder(400, ['erro' => 'ID inválido.']);
        }

        $veiculo = $dao->buscarPorId($id);
        if (!$veiculo) {
            responder(404, ['erro' => 'Veículo não encontrado.']);
        }

        if ($metodo === 'DELETE') {
            $dao->excluir($id);
            responder(200, ['mensagem' => 'Veículo excluído com sucesso.']);
        }
    }

    // Aceita corpo JSON ou formulario (application/x-www-form-urlencoded).
    $corpo = file_get_contents('php://input');
    $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($tipo === 'application/x-www-form-urlencoded') {
        parse_str($corpo, $dados);
    } else {
        $dados = json_decode($corpo, true);
        if (!is_array($dados) || array_is_list($dados)) {
            responder(400, ['erro' => 'JSON inválido: informe um objeto.']);
        }
    }

    foreach (['id_usuario', 'marca', 'modelo', 'ano', 'preco'] as $campo) {
        if (!array_key_exists($campo, $dados)) {
            responder(400, ['erro' => 'Campo obrigatório ausente: ' . $campo]);
        }
    }

    $idUsuario = filter_var($dados['id_usuario'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $ano = filter_var($dados['ano'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900]]);
    $km = filter_var($dados['quilometragem'] ?? ($veiculo['quilometragem'] ?? 0), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $preco = $dados['preco'];
    $marca = $dados['marca'];
    $modelo = $dados['modelo'];
    $foto = $dados['foto'] ?? ($veiculo['foto'] ?? null);

    if (
        $idUsuario === false || $ano === false || $km === false ||
        !is_string($marca) || trim($marca) === '' || strlen($marca) > 50 ||
        !is_string($modelo) || trim($modelo) === '' || strlen($modelo) > 50 ||
        !is_numeric($preco) || !is_scalar($preco) || !is_finite((float) $preco) || (float) $preco <= 0 || (float) $preco > 99999999.99 ||
        ($foto !== null && (!is_string($foto) || strlen($foto) > 255))
    ) {
        responder(400, ['erro' => 'Dados do veículo inválidos.']);
    }

    // Confere a FK antes de inserir ou atualizar.
    $consulta = $conexao->prepare('SELECT id_usuario FROM usuarios WHERE id_usuario = ?');
    $consulta->execute([$idUsuario]);
    if (!$consulta->fetchColumn()) {
        responder(400, ['erro' => 'Usuário informado não existe.']);
    }

    if ($metodo === 'PUT') {
        $dao->atualizar($id, $idUsuario, trim($marca), trim($modelo), $ano, (float) $preco, $km, $foto);
        responder(200, ['mensagem' => 'Veículo atualizado com sucesso.', 'veiculo' => $dao->buscarPorId($id)]);
    }

    $dao->inserir($idUsuario, trim($marca), trim($modelo), $ano, (float) $preco, $km, $foto);
    $novoId = (int) $conexao->lastInsertId();
    responder(201, ['mensagem' => 'Veículo cadastrado com sucesso.', 'id_veiculo' => $novoId, 'veiculo' => $dao->buscarPorId($novoId)]);
} catch (PDOException $erro) {
    error_log('Erro na API de veículos: ' . $erro->getMessage());
    responder(500, ['erro' => 'Erro interno ao acessar o banco de dados.']);
}
