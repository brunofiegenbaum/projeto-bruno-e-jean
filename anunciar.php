<?php
require_once "api_cliente.php";
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$veiculo = false;
$usuarios = [];
try {
    $usuarios = chamarApi('usuarios');
    if ($id > 0) $veiculo = chamarApi('veiculos', 'GET', null, ['id'=>$id]);
} catch (RuntimeException $e) { $erro = $e->getMessage(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) ($_POST["id"] ?? 0);
    $id_usuario = (int) ($_POST["id_usuario"] ?? 0);
    $marca = trim($_POST["marca"] ?? "");
    $modelo = trim($_POST["modelo"] ?? "");
    $ano = (int) ($_POST["ano"] ?? 0);
    $preco = (float) ($_POST["preco"] ?? 0);
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
                if (!is_dir(__DIR__ . "/fotos")) mkdir(__DIR__ . "/fotos", 0755);
                $arquivo = bin2hex(random_bytes(12)) . "." . $extensoes[$tipo];
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], __DIR__ . "/fotos/" . $arquivo)) {
                    $foto = "fotos/" . $arquivo;
                } else {
                    $erroFoto = "Não foi possível salvar a foto.";
                }
            }
        }
    }
    if ($erroFoto == "" && $quilometragem >= 0 && $id_usuario > 0 && $marca != "" && $modelo != "" && $ano >= 1900 && $preco > 0 && strlen($marca) <= 50 && strlen($modelo) <= 50) {
        try {
            $dados = ['id_usuario'=>$id_usuario, 'marca'=>$marca, 'modelo'=>$modelo, 'ano'=>$ano, 'preco'=>$preco, 'quilometragem'=>$quilometragem, 'foto'=>$foto];
            chamarApi('veiculos', $id > 0 ? 'PUT' : 'POST', $dados, $id > 0 ? ['id'=>$id] : []);
            header("Location: index.php");
            exit;
        } catch (RuntimeException $e) { $erroFoto = $e->getMessage(); }
    }
    $erro = $erroFoto != "" ? $erroFoto : "Preencha os campos corretamente.";
}
function valor($campo, $veiculo)
{
    return htmlspecialchars((string) ($veiculo[$campo] ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-tema="escuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? "Editar veículo" : "Cadastrar veículo"; ?> | Bruno e Jean</title>
    <link rel="stylesheet" href="style.css">
    <script src="tema.js" defer></script>
</head>
<body>
    <header class="cabecalho">
        <a class="marca-site" href="index.php">Marketplace</a>
        <nav class="navegacao">
            <a href="index.php">Início</a>
            <a href="usuarios.php">Usuários</a>
            <a class="ativo" href="anunciar.php">Anunciar</a>
            <button id="botao-tema" class="botao-tema" type="button">Modo claro</button>
        </nav>
    </header>
    <main class="pagina-formulario">
        <a class="link-voltar" href="index.php">Voltar ao Marketplace</a>
        <div class="cabecalho-pagina">
            
            <h1><?php echo $id ? "Editar veículo" : "Anunciar veículo"; ?></h1>
            
        </div>
        <?php if (isset($erro)) { ?>
            <div class="mensagem-erro"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <form class="formulario-principal" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <h2>Informações do veículo</h2>
            <div class="form-grade">
                <div class="campo-largo">
                    <label for="id_usuario">Vendedor</label>
                    <select id="id_usuario" name="id_usuario" required>
                        <?php foreach ($usuarios as $usuario) { ?>
                            <option value="<?php echo (int) $usuario["id_usuario"]; ?>" <?php if (($veiculo["id_usuario"] ?? 0) == $usuario["id_usuario"]) echo "selected"; ?>><?php echo htmlspecialchars($usuario["nome"], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="marca">Marca</label>
                    <input id="marca" name="marca" required maxlength="50" placeholder="Ex.: Volkswagen" value="<?php echo valor("marca", $veiculo); ?>">
                </div>
                <div>
                    <label for="modelo">Modelo</label>
                    <input id="modelo" name="modelo" required maxlength="50" placeholder="Ex.: Gol" value="<?php echo valor("modelo", $veiculo); ?>">
                </div>
                <div>
                    <label for="ano">Ano</label>
                    <input id="ano" type="number" name="ano" min="1900" required placeholder="Ex.: 2018" value="<?php echo valor("ano", $veiculo); ?>">
                </div>
                <div>
                    <label for="preco">Preço (R$)</label>
                    <input id="preco" type="number" name="preco" step="0.01" min="0.01" required placeholder="Ex.: 35000" value="<?php echo valor("preco", $veiculo); ?>">
                </div>
                <div class="campo-largo">
                    <label for="quilometragem">Quilometragem</label>
                    <input id="quilometragem" type="number" name="quilometragem" min="0" required placeholder="Ex.: 75000" value="<?php echo valor("quilometragem", $veiculo); ?>">
                </div>
                <div class="campo-largo">
                    <label for="foto">Foto do veículo</label>
                    <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                    <small>Formatos JPG, PNG ou WEBP. Tamanho máximo: 3 MB.</small>
                    <?php if (!empty($veiculo["foto"])) { ?>
                        <p>Foto atual:</p>
                        <img class="miniatura" src="<?php echo htmlspecialchars($veiculo["foto"], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto atual">
                    <?php } ?>
                </div>
            </div>
            <div class="acoes-formulario">
                <a class="botao-secundario" href="index.php">Cancelar</a>
                <button type="submit" class="botao-primario">Salvar anúncio</button>
            </div>
        </form>
    </main>
</body>
</html>
