<?php
require_once "conexao.php";
require_once "VeiculoDAO.php";
$dao = new VeiculoDAO($conexao);
$veiculos = $dao->listar();
$busca = trim($_GET["busca"] ?? "");

// Busca simples pelo nome da marca ou modelo.
if ($busca != "") {
    $resultado = [];
    foreach ($veiculos as $veiculo) {
        $nomeVeiculo = $veiculo["marca"] . " " . $veiculo["modelo"];
        if (stripos($nomeVeiculo, $busca) !== false) {
            $resultado[] = $veiculo;
        }
    }
    $veiculos = $resultado;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace de Veículos - Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="cabecalho-marketplace">
        <h2><a href="index.php">Bruno e Jean <span>Marketplace</span></a></h2>
        <nav>
            <a class="ativo" href="index.php">Início</a>
            <a href="usuarios.php">Usuários</a>
            <a class="botao-anunciar" href="anunciar.php">+ Anunciar veículo</a>
        </nav>
    </header>

    <div class="pagina-marketplace">
        <aside class="menu-lateral">
            <h2>Marketplace</h2>
            <form class="form-busca" method="get" action="index.php">
                <input type="search" name="busca" placeholder="Buscar veículos" value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit">Buscar</button>
            </form>
            <h3>Navegação</h3>
            <a class="menu-ativo" href="index.php">Todos os veículos</a>
            <a href="anunciar.php">Cadastrar anúncio</a>
            <a href="usuarios.php">Usuários cadastrados</a>
            <p class="texto-ajuda">Encontre carros anunciados no nosso projeto.</p>
        </aside>

        <main class="conteudo-marketplace">
            <div class="titulo-listagem">
                <div>
                    <h1>Veículos à venda</h1>
                    <p><?php echo count($veiculos); ?> anúncio(s) encontrado(s)</p>
                </div>
                <a class="atalho-anunciar" href="anunciar.php">+ Criar anúncio</a>
            </div>

            <?php if (count($veiculos) == 0) { ?>
                <div class="sem-anuncios">
                    <h2>Nenhum veículo encontrado</h2>
                    <p>Tente outra busca ou cadastre um veículo.</p>
                    <a href="index.php">Ver todos os veículos</a>
                </div>
            <?php } else { ?>
                <div class="grade-veiculos">
                    <?php foreach ($veiculos as $veiculo) { ?>
                        <div class="cartao-veiculo">
                            <a href="anuncio.php?id=<?php echo (int) $veiculo['id_veiculo']; ?>">
                                <?php if (!empty($veiculo["foto"])) { ?>
                                    <img class="foto-anuncio" src="<?php echo htmlspecialchars($veiculo['foto'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto do veículo">
                                <?php } else { ?>
                                    <div class="foto-sem-imagem">Sem foto</div>
                                <?php } ?>
                                <div class="dados-anuncio">
                                    <strong class="preco-anuncio">R$ <?php echo number_format($veiculo["preco"], 2, ",", "."); ?></strong>
                                    <h2><?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p><?php echo (int) $veiculo["ano"]; ?> · <?php echo number_format($veiculo["quilometragem"], 0, ",", "."); ?> km</p>
                                    <p>Vendedor: <?php echo htmlspecialchars($veiculo["nome"], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </a>
                            <div class="rodape-cartao">
                                <a href="anuncio.php?id=<?php echo (int) $veiculo['id_veiculo']; ?>">Ver detalhes</a>
                                <a href="anunciar.php?id=<?php echo (int) $veiculo['id_veiculo']; ?>">Editar</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </main>
    </div>
</body>
</html>
