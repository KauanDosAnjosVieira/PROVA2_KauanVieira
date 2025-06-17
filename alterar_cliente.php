<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$cliente = null;
$erro = '';
$sucesso = '';

// Se veio por GET com ID, busca cliente
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) $erro = "Cliente não encontrado!";
    } catch (PDOException $e) {
        $erro = "Erro ao buscar cliente: " . $e->getMessage();
    }
}

// Busca ou atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Busca cliente
    if (isset($_POST['busca_cliente']) && !empty(trim($_POST['busca_cliente']))) {
        $busca = trim($_POST['busca_cliente']);

        try {
            if (preg_match('/^(\d+)\s*-\s*(.+)/', $busca, $matches)) {
                $id_busca = $matches[1];
                $nome_busca = trim($matches[2]);

                $sql = "SELECT * FROM cliente WHERE id_cliente = :id OR nome_cliente LIKE :nome";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id_busca, PDO::PARAM_INT);
                $nome_param = "%" . $nome_busca . "%";
                $stmt->bindParam(':nome', $nome_param, PDO::PARAM_STR);
            } elseif (is_numeric($busca)) {
                $sql = "SELECT * FROM cliente WHERE id_cliente = :busca";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
            } else {
                $sql = "SELECT * FROM cliente WHERE nome_cliente LIKE :busca";
                $stmt = $pdo->prepare($sql);
                $nome_param = "%" . $busca . "%";
                $stmt->bindParam(':busca', $nome_param, PDO::PARAM_STR);
            }

            $stmt->execute();
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cliente) $erro = "Cliente não encontrado!";
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }

    // Atualização
    if (isset($_POST['id_cliente'])) {
        $id_cliente = $_POST['id_cliente'];
        $nome = trim($_POST['nome']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);
        $endereco = trim($_POST['endereco']);

        try {
            $sql = "UPDATE cliente SET 
                    nome_cliente = :nome,
                    telefone = :telefone,
                    email = :email,
                    endereco = :endereco
                    WHERE id_cliente = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':id', $id_cliente);

            if ($stmt->execute()) {
                $sucesso = "Cliente atualizado com sucesso!";
                $cliente = null; // Limpa dados
            } else {
                $erro = "Erro ao atualizar cliente!";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/alterar_cliente.css">
</head>
<body>
<h1>Kauan Dos Anjos Vieira</h1>
    <div class="container">
        <h2><i class="fas fa-user-edit"></i> Alterar Cliente</h2>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $sucesso ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE BUSCA -->
        <?php if (!$sucesso): ?>
        <form action="alterar_cliente.php" method="POST" id="buscaForm">
            <div class="form-group">
                <label for="busca_cliente"><i class="fas fa-search"></i> Digite o ID ou Nome:</label>
                <input type="text" id="busca_cliente" name="busca_cliente" required
                    value="<?= isset($_POST['busca_cliente']) ? htmlspecialchars($_POST['busca_cliente']) : '' ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>
        <?php endif; ?>

        <!-- FORMULÁRIO DE EDIÇÃO -->
        <?php if (isset($cliente) && $cliente && !$sucesso): ?>
        <div class="form-section">
            <form action="alterar_cliente.php" method="POST" id="editarForm">
                <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">

                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                    <input type="text" id="nome" name="nome"
                        value="<?= htmlspecialchars($cliente['nome_cliente']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="telefone"><i class="fas fa-phone"></i> Telefone:</label>
                    <input type="tel" id="telefone" name="telefone"
                        value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($cliente['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço:</label>
                    <input type="text" id="endereco" name="endereco"
                        value="<?= htmlspecialchars($cliente['endereco']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
        <a href="alterar_cliente.php" class="btn btn-secondary">
            <i class="fas fa-user-edit"></i> Editar outro cliente
        </a>
        <?php endif; ?>

        <a href="principal.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="js/alterar_cliente.js"></script>
</body>
</html>
