# Bruno e Jean - Projeto Semestre

Projeto de aula em PHP, HTML, CSS e MySQL para cadastrar usuários e veículos.

## Como executar

1. Coloque a pasta `projeto-bruno-e-jean` dentro de `C:\xampp\htdocs`.
2. Ligue o Apache e o MySQL no XAMPP.
3. No phpMyAdmin, crie ou selecione o banco `projetosemestre`.
4. Se estiver instalando do zero, importe `banco.sql`.
5. Abra `http://localhost/projeto-bruno-e-jean/`.

**Importante:** `banco.sql` é para criar o banco do zero. Não execute novamente em um banco com dados que deseja manter, pois o arquivo limpa os registros de veículos.

## Arquivos

- `index.php`: lista de veículos.
- `usuarios.php`: cadastro, listagem, edição e exclusão de usuários.
- `anunciar.php`: cadastro e edição de veículos e envio de foto.
- `anuncio.php`: detalhes de um veículo.
- `excluir.php`: exclusão de veículo.
- `conexao.php`: conexão com MySQL usando PDO.
- `UsuarioDAO.php` e `VeiculoDAO.php`: consultas ao banco.
- `style.css`: aparência do site.
- `banco.sql`: tabelas e registros iniciais.
- `exemplo.php`: teste básico do CRUD com transação para não deixar registros de teste.

A pasta `fotos/` é criada pelo PHP quando uma imagem é enviada. Ela fica no computador onde o XAMPP está rodando.

## Observação

Esta versão é uma demonstração simples. As telas usam um cliente HTTP PHP no servidor (`api_cliente.php`) para consumir os endpoints REST autenticados de veículos e usuários. O token nunca é enviado ao navegador. Configure a variável de ambiente `API_TOKEN` no Apache e ative a extensão PHP cURL. As APIs estão em `api/veiculos.php` e `api/usuarios.php`. O formulário de anúncio mantém o upload de fotos no servidor antes de enviar os dados à API. O endpoint GET de veículos aceita `busca`, `preco_maximo` e `ordem` (`recentes`, `menor`, `maior`). Teste no XAMPP antes de entregar o projeto.
