<?php
require_once "conexao.php";
require_once "VeiculoDAO.php";
$dao = new VeiculoDAO($conexao);
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$veiculo = $dao->buscarPorId($id);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Detalhes do veículo</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h2>Bruno e Jean - Projeto Semestre</h2><a href="index.php">Voltar</a>
    </header>
    <main>
        <?php if ($veiculo) { ?>
            <h1><?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"]); ?></h1>
            <div class="card">
                <?php if (!empty($veiculo["foto"])) { ?><img class="foto-grande"
                        src="<?php echo htmlspecialchars($veiculo["foto"]); ?>" alt="Foto do veículo"><?php } ?>
                <p>Ano: <?php echo $veiculo["ano"]; ?></p>
                <p>Quilometragem: <?php echo number_format($veiculo["quilometragem"], 0, ",", "."); ?> km</p>
                <h2>R$ <?php echo number_format($veiculo["preco"], 2, ",", "."); ?></h2>
                <a href="anunciar.php?id=<?php echo $id; ?>">Editar</a> |
                <form method="post" action="excluir.php" onsubmit="return confirm('Excluir este veículo?')"><input
                        type="hidden" name="id" value="<?php echo $id; ?>"><button type="submit">Excluir</button></form>
            </div>
        <?php } else { ?>
            <p>Veículo não encontrado.</p><?php } ?>
    </main>
</body>

</html>