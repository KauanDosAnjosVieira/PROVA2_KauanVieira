<?php
session_start();
require_once 'conexao.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $sql = "SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem sucedido define variáveis de sessão
        $_SESSION['usuario'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['id_perfil'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        // Verifica se a senha é temporária
        if ($usuario['senha_temporaria']) {
            // Redireciona para a troca de senha
            header("Location: alterar_senha.php");
            exit();
        } else {
            // Redireciona para a página principal
            header("Location: principal.php");
            exit();
        }
    } else {
        // Login inválido
        echo "<script>alert('E-mail ou senha incorretos');window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Kauan Dos Anjos Vieira</h1>
    <form action="login.php" method="POST">
         <h2>Login</h2>
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
        <p><a href="recuperar_senha.php">Esqueci minha senha</a></p>

    </form>


    
</body>
</html>
