<?php
require_once "conexao.php";
require_once "UsuarioDAO.php";
$dao = new UsuarioDAO($conexao);
$mensagem = "";
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$usuario = $id ? $dao->buscarPorId($id) : false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") == "excluir") {
    $idExcluir = (int) ($_POST["id_excluir"] ?? 0);
    if ($idExcluir > 0) {
        $verificar = $conexao->prepare("SELECT COUNT(*) FROM veiculos WHERE id_usuario = ?");
        $verificar->execute([$idExcluir]);
        if ($verificar->fetchColumn() > 0) {
            $mensagem = "Este usuário possui veículos cadastrados. Exclua os veículos antes de excluir o usuário.";
        } else {
            $dao->excluir($idExcluir);
            header("Location: usuarios.php");
            exit;
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) ($_POST["id"] ?? 0);
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    if ($nome == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Informe um nome e um e-mail válido.";
    } else {
        if ($id > 0) {
            $dao->atualizar($id, $nome, $email);
        } else {
            $dao->inserir($nome, $email);
        }
        header("Location: usuarios.php");
        exit;
    }
}
$usuarios = $dao->listar();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Usuários - Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h2>Bruno e Jean - Projeto Semestre</h2>
        <nav><a href="index.php">Início</a> | <a href="usuarios.php">Usuários</a> | <a href="anunciar.php">Cadastrar
                veículo</a></nav>
    </header>
    <main>
        <h1><?php echo $usuario ? "Editar usuário" : "Cadastrar usuário"; ?></h1>
        <?php if ($usuario) { ?>
            <p><a href="usuarios.php">Cancelar edição</a></p><?php } ?>
        <?php if ($mensagem != "")
            echo "<p>" . htmlspecialchars($mensagem) . "</p>"; ?>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $usuario["id_usuario"] ?? 0; ?>">
            <label>Nome</label>
            <input type="text" name="nome" required maxlength="100"
                value="<?php echo htmlspecialchars($usuario["nome"] ?? ""); ?>">
            <label>E-mail</label>
            <input type="email" name="email" required maxlength="100"
                value="<?php echo htmlspecialchars($usuario["email"] ?? ""); ?>">
            <button type="submit">Salvar usuário</button>
        </form>
        <h2>Usuários cadastrados</h2>
        <?php if (count($usuarios) == 0) { ?>
            <p>Nenhum usuário cadastrado.</p>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($usuarios as $item) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["nome"]); ?></td>
                        <td><?php echo htmlspecialchars($item["email"]); ?></td>
                        <td class="acoes-usuario"><a class="botao-editar"
                                href="usuarios.php?id=<?php echo $item["id_usuario"]; ?>">Editar</a>
                            <form class="excluir-usuario" method="post"
                                onsubmit="return confirm('Deseja excluir este usuário?')">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id_excluir" value="<?php echo $item["id_usuario"]; ?>">
                                <button type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </main>
</body>

</html>