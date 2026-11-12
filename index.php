<?php
require_once "api_cliente.php";
$busca = trim($_GET["busca"] ?? "");
$ordem = $_GET["ordem"] ?? "recentes";
$precoMaximo = $_GET["preco_maximo"] ?? "";
$erroApi = "";
try { $veiculos = chamarApi('veiculos', 'GET', null, ['busca'=>$busca, 'ordem'=>$ordem, 'preco_maximo'=>$precoMaximo]); }
catch (RuntimeException $e) { $veiculos = []; $erroApi = $e->getMessage(); }
$requisicaoAjax = isset($_GET['api_busca']) && $_GET['api_busca'] === '1';
if ($requisicaoAjax) ob_start();
?>
<!DOCTYPE html>
<html lang="pt-br" data-tema="escuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace | Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
    <script src="tema.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const formulario = document.querySelector('form.filtros');
        if (!formulario) return;
        async function atualizarBusca(url, atualizarHistorico = true) {
            const destino = new URL(url, window.location.href);
            const requisicao = new URL(destino);
            requisicao.searchParams.set('api_busca', '1');
            const botao = formulario.querySelector('button[type="submit"]');
            botao.disabled = true;
            try {
                const resposta = await fetch(requisicao, {headers: {'Accept': 'application/json'}});
                if (!resposta.ok) throw new Error('Falha HTTP ' + resposta.status);
                const dados = await resposta.json();
                const pagina = new DOMParser().parseFromString(dados.html, 'text/html');
                const conteudo = pagina.querySelector('main.conteudo-marketplace');
                if (!conteudo) throw new Error('Resposta da busca inválida.');
                document.querySelector('main.conteudo-marketplace').replaceWith(conteudo);
                if (atualizarHistorico) history.pushState(null, '', destino);
            } catch (erro) {
                console.error('Erro ao buscar veículos pela API:', erro);
                window.location.assign(destino.href);
            } finally {
                botao.disabled = false;
            }
        }
        formulario.addEventListener('submit', evento => {
            evento.preventDefault();
            const url = new URL(formulario.action, window.location.href);
            url.search = new URLSearchParams(new FormData(formulario)).toString();
            atualizarBusca(url);
        });
        window.addEventListener('popstate', () => window.location.reload());
    });
    </script>
</head>
<body>
    <header class="cabecalho">
        <a class="marca-site" href="index.php">Marketplace</a>
        <nav class="navegacao">
            <a class="ativo" href="index.php">Início</a>
            <a href="usuarios.php">Usuários</a>
            <a class="botao-primario" href="anunciar.php">Criar anúncio</a>
            <button id="botao-tema" class="botao-tema" type="button">Modo claro</button>
        </nav>
    </header>

    <div class="layout-marketplace">
        <aside class="menu-lateral">
            <h2>Marketplace</h2>
            
            <form method="get" action="index.php" class="filtros">
                <label for="busca">Pesquisar veículos</label>
                <input id="busca" type="search" name="busca" placeholder="Marca ou modelo" value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="preco_maximo">Preço máximo</label>
                <input id="preco_maximo" type="number" name="preco_maximo" min="1" step="0.01" placeholder="Ex.: 50000" value="<?php echo htmlspecialchars((string) $precoMaximo, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="ordem">Ordenar por</label>
                <select id="ordem" name="ordem">
                    <option value="recentes" <?php if ($ordem == "recentes") echo "selected"; ?>>Mais recentes</option>
                    <option value="menor" <?php if ($ordem == "menor") echo "selected"; ?>>Menor preço</option>
                    <option value="maior" <?php if ($ordem == "maior") echo "selected"; ?>>Maior preço</option>
                </select>
                <button type="submit" class="botao-primario largura-total">Aplicar filtros</button>
                <a class="limpar-filtros" href="index.php">Limpar filtros</a>
            </form>
            <div class="links-laterais">
                <a class="selecionado" href="index.php">Todos os anúncios</a>
                <a href="anunciar.php">Vender veículo</a>
                <a href="usuarios.php">Vendedores</a>
            </div>
        </aside>

        <main class="conteudo-marketplace">
            <div class="titulo-listagem"><h1>Veículos à venda</h1><p><?php echo count($veiculos); ?> anúncio(s)</p></div>

            <?php if ($erroApi !== "") { ?><div class="mensagem-erro"><?php echo htmlspecialchars($erroApi, ENT_QUOTES, "UTF-8"); ?></div><?php } ?>
            <?php if (count($veiculos) == 0 && $erroApi === "") { ?>
                <div class="sem-anuncios">
                    
                    <h2>Nenhum veículo encontrado</h2>
                    <p>Experimente outros filtros ou veja todos os anúncios.</p>
                    <a class="botao-primario" href="index.php">Ver todos os veículos</a>
                </div>
            <?php } elseif (count($veiculos) > 0) { ?>
                <div class="grade-veiculos">
                    <?php foreach ($veiculos as $veiculo) { ?>
                        <article class="cartao-veiculo">
                            <a href="anuncio.php?id=<?php echo (int) $veiculo["id_veiculo"]; ?>" class="link-cartao">
                                <div class="capa-cartao">
                                    <?php if (!empty($veiculo["foto"])) { ?>
                                        <img class="foto-anuncio" src="<?php echo htmlspecialchars($veiculo["foto"], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de <?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                                    <?php } else { ?>
                                        <div class="foto-sem-imagem">Sem foto</div>
                                    <?php } ?>
                                    
                                </div>
                                <div class="dados-anuncio">
                                    <strong class="preco-anuncio">R$ <?php echo number_format($veiculo["preco"], 2, ",", "."); ?></strong>
                                    <h3><?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p><?php echo (int) $veiculo["ano"]; ?> <span>•</span> <?php echo number_format($veiculo["quilometragem"], 0, ",", "."); ?> km</p>
                                    <div class="vendedor-cartao">Vendedor: <?php echo htmlspecialchars($veiculo["nome"], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </a>
                            <div class="rodape-cartao">
                                <a href="anuncio.php?id=<?php echo (int) $veiculo["id_veiculo"]; ?>">Detalhes</a>
                                <a href="anunciar.php?id=<?php echo (int) $veiculo["id_veiculo"]; ?>">Editar</a>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        </main>
    </div>
</body>
</html>

<?php
// Resposta AJAX: a busca continua passando pela API REST no servidor.
// O navegador enxerga esta requisição em Network > Fetch/XHR, sem receber o token.
if ($requisicaoAjax) {
    $html = ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['html' => $html], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
?>