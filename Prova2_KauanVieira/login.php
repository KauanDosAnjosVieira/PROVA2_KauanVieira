<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verifica se o usuário existe
    $sql = "SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // LOGIN BEM SUCEDIDO DEFINE VARIAVEIS DE SESSÃO
        $_SESSION['usuario'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['id_perfil'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        //VERIFICA SE A SENHA É TEMPORÁRIA

        if($usuario['senha_temporaria']){
            //redireiciona para a página de alteração de senha
            header("Location: alterar_senha.php");
            exit();
        } else {
            //redireciona para a página principal
            header("Location: principal.php");
            exit();

        }  
    } else {
        // LOGIN FALHOU
        echo"<script>alert('Email ou senha incorretos!');window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <form action="login.php" method="POST">
    <h2>Login</h2>

    <div class="input-with-icon">
    <i class="fas fa-envelope"></i>
    <input type="email" id="email" name="email" required placeholder="Seu e-mail">
</div>
    <div class="input-with-icon">
    <i class="fas fa-key"></i>
    <input type="email" id="email" name="email" required placeholder="Seu e-mail">
    </div>

    <button type="submit">Entrar</button>
    <p><a href="recuperar_senha.php">Esqueci minha senha</a></p>
    </form>


    
</body>
</html>
