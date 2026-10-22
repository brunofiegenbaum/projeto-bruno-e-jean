<?php
class VeiculoDAO {
    private $conexao;
    function __construct($conexao) {
        $this->conexao = $conexao;
    }
    function listar() {
        $sql = "SELECT veiculos.*, usuarios.nome FROM veiculos INNER JOIN usuarios ON veiculos.id_usuario = usuarios.id_usuario";
        $comando = $this->conexao->prepare($sql);
        $comando->execute();
        return $comando->fetchAll(PDO::FETCH_ASSOC);
    }
    function buscarPorId($id) {
        $comando = $this->conexao->prepare("SELECT * FROM veiculos WHERE id_veiculo=?");
        $comando->execute([$id]);
        return $comando->fetch(PDO::FETCH_ASSOC);
    }
    function inserir($id_usuario, $marca, $modelo, $ano, $preco, $quilometragem, $foto) {
        $comando = $this->conexao->prepare("INSERT INTO veiculos (id_usuario,marca,modelo,ano,preco,quilometragem,foto) VALUES (?,?,?,?,?,?,?)");
        return $comando->execute([$id_usuario,$marca,$modelo,$ano,$preco,$quilometragem,$foto]);
    }
    function atualizar($id, $id_usuario, $marca, $modelo, $ano, $preco, $quilometragem, $foto) {
        $comando = $this->conexao->prepare("UPDATE veiculos SET id_usuario=?,marca=?,modelo=?,ano=?,preco=?,quilometragem=?,foto=? WHERE id_veiculo=?");
        return $comando->execute([$id_usuario,$marca,$modelo,$ano,$preco,$quilometragem,$foto,$id]);
    }
    function excluir($id) {
        $comando = $this->conexao->prepare("DELETE FROM veiculos WHERE id_veiculo=?");
        return $comando->execute([$id]);
    }
}
