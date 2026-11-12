<?php
// Requisições à API feitas no servidor, sem expor API_TOKEN no HTML.
function chamarApi(string $recurso, string $metodo = 'GET', ?array $dados = null, array $query = []): array {
    if (!in_array($recurso, ['veiculos', 'usuarios'], true)) throw new InvalidArgumentException('Recurso inválido.');
    $token = getenv('API_TOKEN');
    if (!is_string($token) || $token === '') throw new RuntimeException('API_TOKEN não configurado no Apache.');
    if (!function_exists('curl_init')) throw new RuntimeException('A extensão PHP cURL deve estar ativada.');
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $porta = (int)($_SERVER['SERVER_PORT'] ?? 80);
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $esquema = $https ? 'https' : 'http';
    $diretorio = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/.');
    $url = $esquema.'://'.$host.(($https && $porta !== 443) || (!$https && $porta !== 80) ? ':'.$porta : '').$diretorio.'/api/'.$recurso.'.php';
    if ($query) $url .= '?'.http_build_query($query);
    $curl = curl_init($url);
    $headers = ['Authorization: Bearer '.$token, 'Accept: application/json'];
    $opts = [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CUSTOMREQUEST=>$metodo, CURLOPT_TIMEOUT=>15, CURLOPT_CONNECTTIMEOUT=>5];
    if ($dados !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($curl, $opts);
    $resposta = curl_exec($curl);
    $erro = curl_error($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($resposta === false) throw new RuntimeException('Não foi possível acessar a API: '.$erro);
    $json = json_decode($resposta, true);
    if (!is_array($json)) throw new RuntimeException('Resposta inválida da API (HTTP '.$status.').');
    if ($status < 200 || $status >= 300) throw new RuntimeException((string)($json['erro'] ?? 'Erro na API'), $status);
    return $json;
}
