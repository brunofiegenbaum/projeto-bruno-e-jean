<?php
require_once "conexao.php";
require_once "UsuarioDAO.php";
require_once "VeiculoDAO.php";
header("Content-Type: text/plain; charset=utf-8");

$usuarioDAO = new UsuarioDAO($conexao);
$veiculoDAO = new VeiculoDAO($conexao);

echo "Bruno e Jean - Projeto Semestre\n";
echo "Teste do banco e DAO\n\n";
try {
    echo "USUÁRIOS:\n";
    print_r($usuarioDAO->listar());
    echo "\nVEÍCULOS:\n";
    print_r($veiculoDAO->listar());

    $conexao->beginTransaction();
    $usuarioDAO->inserir("Teste", "teste@exemplo.com");
    $idUsuario = $conexao->lastInsertId();
    $veiculoDAO->inserir($idUsuario, "Fiat", "Uno", 2012, 20000, 100000, null);
    $idVeiculo = $conexao->lastInsertId();

    echo "\nBUSCAR:\n";
    print_r($usuarioDAO->buscarPorId($idUsuario));
    print_r($veiculoDAO->buscarPorId($idVeiculo));

    $usuarioDAO->atualizar($idUsuario, "Teste Editado", "teste2@exemplo.com");
    $veiculoDAO->atualizar($idVeiculo, $idUsuario, "Fiat", "Palio", 2013, 22000, 90000, null);
    echo "\nATUALIZAR:\n";
    print_r($veiculoDAO->buscarPorId($idVeiculo));

    $veiculoDAO->excluir($idVeiculo);
    $usuarioDAO->excluir($idUsuario);
    echo "\nEXCLUSÃO REALIZADA.\n";
    $conexao->rollBack();
    echo "Teste concluído. Dados originais mantidos.";
} catch (Exception $erro) {
    if ($conexao->inTransaction())
        $conexao->rollBack();
    echo "Erro: " . $erro->getMessage();
}
