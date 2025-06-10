<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Inicializa variáveis
$cliente = null;

// Se o formulário for enviado, busca o usuário pelo ID ou nome
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['busca_cliente'])) {
        $busca = trim($_POST['busca_cliente']);

        // Verifica se a busca é um número (ID) ou um nome
        if (is_numeric($busca)) {
            $sql = "SELECT * FROM cliente WHERE id_cliente = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM cliente WHERE nome_cliente LIKE :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
        }

        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se o usuário não for encontrado, exibe um alerta
        if (!$cliente) {
            echo "<script>alert('Usuário não encontrado!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Cliente</title>
    <link rel="stylesheet" href="styles.css">
    
    <!-- Certifique-se de que o JavaScript está sendo carregado corretamente -->
    <script src="scripts.js"></script>
</head>
<body>
    <h2>Alterar Cliente</h2>

    <!-- Formulário para buscar usuário pelo ID ou Nome -->
    <form action="alterar_cliente.php" method="POST">
        <label for="buscar_cliente">Digite o ID ou Nome do cliente:</label>
        <input type="text" id="buscar_cliente" name="buscar_cliente" required onkeyup="buscarSugestoes()">
        
        <!-- Div para exibir sugestões de usuários -->
        <div id="sugestoes"></div>
        
        <button type="submit">Buscar</button>
    </form>

    <?php
// Certifique-se de que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se $cliente está definido antes de usá-lo
if (isset($cliente) && $cliente): ?>
    <!-- Formulário para alterar usuário -->
    <form action="processa_alteracao_cliente.php" method="POST">
        <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome_cliente']) ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>" required>

        <!-- Se o usuário logado for ADM, exibir opção de alterar senha -->
        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] == 1): ?>
            <label for="nova_senha">Nova Senha:</label>
            <input type="password" id="nova_senha" name="nova_senha">
        <?php endif; ?>
    </form>
<?php endif; ?>

    <a href="principal.php">Voltar</a>
</body>
</html>