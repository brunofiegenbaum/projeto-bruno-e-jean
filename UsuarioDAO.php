<?php
class UsuarioDAO {
    private $conexao;
    function __construct($conexao) {
        $this->conexao = $conexao;
    }
    function listar() {
        $sql = "SELECT * FROM usuarios";
        $comando = $this->conexao->prepare($sql);
        $comando->execute();
        return $comando->fetchAll(PDO::FETCH_ASSOC);
    }
    function buscarPorId($id) {
        $comando = $this->conexao->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $comando->execute([$id]);
        return $comando->fetch(PDO::FETCH_ASSOC);
    }
    function inserir($nome, $email) {
        $comando = $this->conexao->prepare("INSERT INTO usuarios (nome,email) VALUES (?,?)");
        return $comando->execute([$nome,$email]);
    }
    function atualizar($id, $nome, $email) {
        $comando = $this->conexao->prepare("UPDATE usuarios SET nome=?,email=? WHERE id_usuario=?");
        $comando->execute([$nome,$email,$id]);
        return $comando->rowCount();
    }
    function excluir($id) {
        $comando = $this->conexao->prepare("DELETE FROM usuarios WHERE id_usuario=?");
        $comando->execute([$id]);
        return $comando->rowCount();
    }
}
