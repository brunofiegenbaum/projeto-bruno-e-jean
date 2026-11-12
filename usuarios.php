<?php
require_once "api_cliente.php";
$mensagem = "";
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$usuario = false;
try {
    if ($id > 0) $usuario = chamarApi('usuarios', 'GET', null, ['id'=>$id]);
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "excluir") {
        $idExcluir = (int)($_POST["id_excluir"] ?? 0);
        if ($idExcluir > 0) {
            chamarApi('usuarios', 'DELETE', null, ['id'=>$idExcluir]);
            header("Location: usuarios.php");
            exit;
        }
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = (int)($_POST["id"] ?? 0);
        $dados = ['nome'=>trim($_POST["nome"] ?? ""), 'email'=>trim($_POST["email"] ?? "")];
        chamarApi('usuarios', $id > 0 ? 'PUT' : 'POST', $dados, $id > 0 ? ['id'=>$id] : []);
        header("Location: usuarios.php");
        exit;
    }
} catch (RuntimeException $e) { $mensagem = $e->getMessage(); }
try { $usuarios = chamarApi('usuarios'); }
catch (RuntimeException $e) { $usuarios = []; $mensagem = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="pt-br" data-tema="escuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
    <script src="tema.js" defer></script>
</head>
<body>
    <header class="cabecalho">
        <a class="marca-site" href="index.php">Marketplace</a>
        <nav class="navegacao">
            <a href="index.php">Início</a>
            <a class="ativo" href="usuarios.php">Usuários</a>
            <a class="botao-primario" href="anunciar.php">Criar anúncio</a>
            <button id="botao-tema" class="botao-tema" type="button">Modo claro</button>
        </nav>
    </header>
    <main class="pagina-interna">
        <a class="link-voltar" href="index.php">Voltar ao Marketplace</a>
        <div class="cabecalho-pagina">
            
            <h1>Gerenciar usuários</h1>
            
        </div>
        <?php if ($mensagem != "") { ?>
            <div class="mensagem-erro"><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <div class="usuarios-layout">
            <section class="painel-formulario">
                <h2><?php echo $usuario ? "Editar vendedor" : "Novo vendedor"; ?></h2>
                <p class="texto-suave">Informe o nome e o e-mail.</p>
                <form class="formulario-usuario" method="post">
                    <input type="hidden" name="id" value="<?php echo (int) ($usuario["id_usuario"] ?? 0); ?>">
                    <label for="nome">Nome completo</label>
                    <input id="nome" type="text" name="nome" required maxlength="100" placeholder="Nome do vendedor" value="<?php echo htmlspecialchars($usuario["nome"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" required maxlength="100" placeholder="email@exemplo.com" value="<?php echo htmlspecialchars($usuario["email"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="botao-primario largura-total">Salvar usuário</button>
                    <?php if ($usuario) { ?><a class="limpar-filtros" href="usuarios.php">Cancelar edição</a><?php } ?>
                </form>
            </section>
            <section class="painel-usuarios">
                <div class="titulo-tabela">
                    <h2>Usuários cadastrados</h2>
                    <span class="quantidade-usuarios"><?php echo count($usuarios); ?> cadastrados</span>
                </div>
                <?php if (count($usuarios) == 0) { ?>
                    <p class="texto-suave">Nenhum usuário cadastrado.</p>
                <?php } else { ?>
                    <div class="tabela-responsiva">
                        <table>
                            <thead><tr><th>Nome</th><th>E-mail</th><th>Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($usuarios as $item) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item["nome"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($item["email"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="acoes-usuario">
                                        <a class="botao-editar" href="usuarios.php?id=<?php echo (int) $item["id_usuario"]; ?>">Editar</a>
                                        <form class="excluir-usuario" method="post" onsubmit="return confirm('Deseja excluir este usuário?')">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id_excluir" value="<?php echo (int) $item["id_usuario"]; ?>">
                                            <button type="submit" class="botao-perigo">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </section>
        </div>
    </main>
</body>
</html>
