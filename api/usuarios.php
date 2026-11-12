<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($metodo === 'OPTIONS') { http_response_code(204); exit; }
function respostaUsuario(int $codigo, array $dados): void {
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
$token = getenv('API_TOKEN');
if (!is_string($token) || $token === '') respostaUsuario(500, ['erro'=>'Autenticacao da API nao configurada.']);
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if ($auth === '' && function_exists('getallheaders')) {
    foreach (getallheaders() as $nome=>$valor) if (strcasecmp($nome, 'Authorization') === 0) { $auth=$valor; break; }
}
if (!preg_match('/^Bearer\s+(\S+)$/i', trim((string)$auth), $partes) || !hash_equals($token, $partes[1])) {
    header('WWW-Authenticate: Bearer');
    respostaUsuario(401, ['erro'=>'Token ausente ou invalido.']);
}
if (!in_array($metodo, ['GET','POST','PUT','DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    respostaUsuario(405, ['erro'=>'Método não permitido.']);
}
require_once __DIR__.'/../conexao.php';
require_once __DIR__.'/../UsuarioDAO.php';
$dao = new UsuarioDAO($conexao);
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) : null;
if ($id === false) respostaUsuario(400, ['erro'=>'ID inválido.']);
try {
    if ($metodo === 'GET') {
        if ($id === null) respostaUsuario(200, $dao->listar());
        $usuario = $dao->buscarPorId($id);
        respostaUsuario($usuario ? 200 : 404, $usuario ?: ['erro'=>'Usuário não encontrado.']);
    }
    if ($metodo === 'PUT' || $metodo === 'DELETE') {
        if ($id === null) respostaUsuario(400, ['erro'=>'ID obrigatório.']);
        if (!$dao->buscarPorId($id)) respostaUsuario(404, ['erro'=>'Usuário não encontrado.']);
        if ($metodo === 'DELETE') {
            $consulta = $conexao->prepare('SELECT COUNT(*) FROM veiculos WHERE id_usuario = ?');
            $consulta->execute([$id]);
            if ($consulta->fetchColumn() > 0) respostaUsuario(409, ['erro'=>'Este usuário possui veículos cadastrados. Exclua os veículos antes de excluir o usuário.']);
            $dao->excluir($id);
            respostaUsuario(200, ['mensagem'=>'Usuário excluído com sucesso.']);
        }
    }
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados) || array_is_list($dados)) respostaUsuario(400, ['erro'=>'JSON inválido.']);
    $nome = $dados['nome'] ?? null;
    $email = $dados['email'] ?? null;
    if (!is_string($nome) || trim($nome) === '' || strlen($nome) > 100 || !is_string($email) || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respostaUsuario(400, ['erro'=>'Informe um nome e um e-mail válido.']);
    }
    if ($metodo === 'PUT') {
        $dao->atualizar($id, trim($nome), trim($email));
        respostaUsuario(200, ['mensagem'=>'Usuário atualizado.', 'usuario'=>$dao->buscarPorId($id)]);
    }
    $dao->inserir(trim($nome), trim($email));
    $novoId = (int)$conexao->lastInsertId();
    respostaUsuario(201, ['mensagem'=>'Usuário cadastrado.', 'usuario'=>$dao->buscarPorId($novoId)]);
} catch (PDOException $erro) {
    error_log('Erro API usuarios: '.$erro->getMessage());
    respostaUsuario(500, ['erro'=>'Erro interno ao acessar o banco de dados.']);
}
