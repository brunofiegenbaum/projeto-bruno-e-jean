<?php
require_once "conexao.php";
require_once "VeiculoDAO.php";
require_once "UsuarioDAO.php";
$dao = new VeiculoDAO($conexao);
$usuarioDAO = new UsuarioDAO($conexao);
$usuarios = $usuarioDAO->listar();
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$veiculo = $id ? $dao->buscarPorId($id) : false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"];
    $id_usuario = (int) $_POST["id_usuario"];
    $marca = trim($_POST["marca"]);
    $modelo = trim($_POST["modelo"]);
    $ano = (int) $_POST["ano"];
    $preco = (float) $_POST["preco"];
    $quilometragem = (int) ($_POST["quilometragem"] ?? -1);
    $foto = $veiculo["foto"] ?? null;
    $erroFoto = "";
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK || $_FILES["foto"]["size"] > 3000000) {
            $erroFoto = "A foto deve ter até 3 MB.";
        } else {
            $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES["foto"]["tmp_name"]);
            $extensoes = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
            if (!isset($extensoes[$tipo])) {
                $erroFoto = "Use JPG, PNG ou WEBP.";
            } else {
                if (!is_dir(__DIR__ . "/fotos"))
                    mkdir(__DIR__ . "/fotos", 0755);
                $arquivo = bin2hex(random_bytes(12)) . "." . $extensoes[$tipo];
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], __DIR__ . "/fotos/" . $arquivo)) {
                    $foto = "fotos/" . $arquivo;
                } else {
                    $erroFoto = "Não foi possível salvar a foto.";
                }
            }
        }
    }
    if ($erroFoto == "" && $quilometragem >= 0 && $id_usuario > 0 && $marca != "" && $modelo != "" && $ano >= 1900 && $preco > 0) {
        if ($id > 0) {
            $dao->atualizar($id, $id_usuario, $marca, $modelo, $ano, $preco, $quilometragem, $foto);
        } else {
            $dao->inserir($id_usuario, $marca, $modelo, $ano, $preco, $quilometragem, $foto);
        }
        header("Location: index.php");
        exit;
    }
    $erro = $erroFoto != "" ? $erroFoto : "Preencha os campos corretamente.";
}
function valor($campo, $veiculo)
{
    return htmlspecialchars((string) ($veiculo[$campo] ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar veículo</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h2>Bruno e Jean - Projeto Semestre</h2><a href="index.php">Voltar</a>
    </header>
    <main>
        <h1><?php echo $id ? "Editar veículo" : "Cadastrar veículo"; ?></h1>
        <?php if (isset($erro))
            echo "<p>$erro</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <label>Vendedor</label>
            <select name="id_usuario" required>
                <?php foreach ($usuarios as $usuario) { ?>
                    <option value="<?php echo $usuario["id_usuario"]; ?>" <?php if (($veiculo["id_usuario"] ?? 0) == $usuario["id_usuario"])
                           echo "selected"; ?>>
                        <?php echo htmlspecialchars($usuario["nome"]); ?>
                    </option>
                <?php } ?>
            </select>
            <label>Marca</label><input name="marca" required value="<?php echo valor("marca", $veiculo); ?>">
            <label>Modelo</label><input name="modelo" required value="<?php echo valor("modelo", $veiculo); ?>">
            <label>Ano</label><input type="number" name="ano" min="1900" required
                value="<?php echo valor("ano", $veiculo); ?>">
            <label>Preço</label><input type="number" name="preco" step="0.01" min="1" required
                value="<?php echo valor("preco", $veiculo); ?>">
            <label>Quilometragem</label><input type="number" name="quilometragem" min="0" required
                value="<?php echo valor("quilometragem", $veiculo); ?>">
            <label>Foto do veículo (JPG, PNG ou WEBP)</label><input type="file" name="foto"
                accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($veiculo["foto"])) { ?>
                <p>Foto atual: <img class="miniatura" src="<?php echo htmlspecialchars($veiculo["foto"]); ?>"
                        alt="Foto atual"></p><?php } ?>
            <button type="submit">Salvar</button>
        </form>
    </main>
</body>

</html>