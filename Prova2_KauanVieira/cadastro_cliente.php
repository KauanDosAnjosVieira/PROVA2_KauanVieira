<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (perfis 1, 2 ou 4 podem cadastrar clientes)
$perfis_permitidos = [1, 2, 4];
if (!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], $perfis_permitidos)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter dados do formulário
    $nome_cliente = $_POST['nome_cliente'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $endereco = $_POST['endereco'];
    
    // Obter ID do funcionário logado (se existir na sessão)
    $id_funcionario_responsavel = isset($_SESSION['id_funcionario']) ? $_SESSION['id_funcionario'] : null;

    try {
        $sql = "INSERT INTO cliente (nome_cliente, telefone, email, endereco, id_funcionario_responsavel) 
                VALUES (:nome_cliente, :telefone, :email, :endereco, :id_funcionario_responsavel)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nome_cliente', $nome_cliente);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':id_funcionario_responsavel', $id_funcionario_responsavel, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>alert('Cliente cadastrado com sucesso!'); window.location.href='principal.php';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar cliente!');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro no banco de dados: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="css/cadastro_cliente.css">
</head>
<body>
    <div class="container">
        <h2>Cadastrar Cliente</h2>
        <form action="cadastro_cliente.php" method="POST" class="form-cliente">
            <div class="form-group">
                <label for="nome_cliente">Nome Completo:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" required placeholder="(00) 00000-0000">
            </div>
            
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Salvar</button>
                <button type="reset" class="btn-secondary">Limpar</button>
                <a href="principal.php" class="btn-back">Voltar</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#telefone').mask('(00) 00000-0000');
        });
    </script>
</body>
</html>