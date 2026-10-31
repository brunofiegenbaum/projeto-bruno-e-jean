<?php
require_once "conexao.php";
require_once "VeiculoDAO.php";
$dao = new VeiculoDAO($conexao);
$veiculos = $dao->listar();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Bruno e Jean - Projeto Semestre</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h2>Bruno e Jean - Projeto Semestre</h2>
        <nav><a href="index.php">Início</a> | <a href="anunciar.php">Cadastrar veículo</a> | <a
                href="usuarios.php">Usuários</a></nav>
    </header>
    <main>
        <h1>Veículos à venda</h1>
        <p>Projeto de aula - anúncios de exemplo</p>
        <?php if (count($veiculos) == 0) {
            echo "<p>Nenhum veículo cadastrado ainda.</p>";
        } ?>
        <div class="tabela-veiculos">
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Veículo</th>
                        <th>Ano</th>
                        <th>Km</th>
                        <th>Preço</th>
                        <th>Vendedor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($veiculos as $veiculo) { ?>
                        <tr>
                            <td><?php if ($veiculo["foto"]) { ?><img class="miniatura"
                                        src="<?php echo htmlspecialchars($veiculo["foto"]); ?>"
                                        alt="Foto do veículo"><?php } else { ?>Sem foto<?php } ?></td>
                            <td><?php echo htmlspecialchars($veiculo["marca"] . " " . $veiculo["modelo"]); ?></td>
                            <td><?php echo (int) $veiculo["ano"]; ?></td>
                            <td><?php echo number_format($veiculo["quilometragem"], 0, ",", "."); ?> km</td>
                            <td>R$ <?php echo number_format($veiculo["preco"], 2, ",", "."); ?></td>
                            <td><?php echo htmlspecialchars($veiculo["nome"]); ?></td>
                            <td><a href="anuncio.php?id=<?php echo $veiculo["id_veiculo"]; ?>">Ver</a> | <a
                                    href="anunciar.php?id=<?php echo $veiculo["id_veiculo"]; ?>">Editar</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>