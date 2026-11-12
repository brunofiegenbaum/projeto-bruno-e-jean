<?php
require_once "api_cliente.php";
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$veiculo = false;
try { if ($id > 0) $veiculo = chamarApi('veiculos', 'GET', null, ['id'=>$id]); }
catch (RuntimeException $e) { if ($e->getCode() !== 404) $erroApi = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="pt-br" data-tema="escuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do veículo | Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
    <script src="tema.js" defer></script>
</head>
<body>
    <header class="cabecalho">
        <a class="marca-site" href="index.php">Marketplace</a>
        <nav class="navegacao">
            <a href="index.php">Início</a>
            <a href="usuarios.php">Usuários</a>
            <a class="botao-primario" href="anunciar.php">Criar anúncio</a>
            <button id="botao-tema" class="botao-tema" type="button">Modo claro</button>
        </nav>
    </header>
    <main class="pagina-interna">
        <a class="link-voltar" href="index.php">Voltar aos anúncios</a>
        <?php if (isset($erroApi)) { ?><div class="mensagem-erro"><?php echo htmlspecialchars($erroApi, ENT_QUOTES, "UTF-8"); ?></div><?php } ?>
        <?php if ($veiculo) { ?>
            <div class="detalhes-layout">
                <section class="painel-foto">
                    <?php if (!empty($veiculo["foto"])) { ?>
                        <img class="foto-detalhe" src="<?php echo htmlspecialchars($veiculo["foto"], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto do veículo">
                    <?php } else { ?>
                        <div class="foto-detalhe-vazia">Sem foto</div>
                    <?php } ?>
                </section>
                <section class="painel-detalhes">
                    
                    <h1><?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <strong class="preco-detalhe">R$ <?php echo number_format($veiculo["preco"], 2, ",", "."); ?></strong>
                    <div class="informacoes-veiculo">
                        <div><span>Ano</span><strong><?php echo (int) $veiculo["ano"]; ?></strong></div>
                        <div><span>Quilometragem</span><strong><?php echo number_format($veiculo["quilometragem"], 0, ",", "."); ?> km</strong></div>
                        <div><span>Marca</span><strong><?php echo htmlspecialchars($veiculo["marca"], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                        <div><span>Modelo</span><strong><?php echo htmlspecialchars($veiculo["modelo"], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                    </div>
                    <div class="bloco-acoes">
                        <h2>Gerenciar anúncio</h2>
                        
                        <div class="acoes-veiculo">
                            <a class="botao-primario" href="anunciar.php?id=<?php echo $id; ?>">Editar veículo</a>
                            <form method="post" action="excluir.php" onsubmit="return confirm('Deseja realmente excluir este veículo?')">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <button type="submit" class="botao-perigo">Excluir anúncio</button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        <?php } else { ?>
            <div class="sem-anuncios">
                <h1>Veículo não encontrado</h1>
                <p>O anúncio pode ter sido removido.</p>
                <a class="botao-primario" href="index.php">Ver anúncios</a>
            </div>
        <?php } ?>
    </main>
</body>
</html>
