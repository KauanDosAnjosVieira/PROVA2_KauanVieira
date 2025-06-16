<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (perfil 1 = admin, perfil 2 = secretaria)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2)) {
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit;
}

$clientes = []; // Inicializa a variável

// Busca todos os clientes somente quando clicado "Mostrar Todos"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mostrar_todos'])) {
    $sql = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 
// Busca específica quando um termo é fornecido
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])) {
    $busca = trim($_POST['busca']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY nome_cliente ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM cliente WHERE nome_cliente LIKE :busca_nome ORDER BY nome_cliente ASC";
        $stmt = $pdo->prepare($sql);
        $busca_nome = "%$busca%";
        $stmt->bindValue(':busca_nome', $busca_nome, PDO::PARAM_STR);
    }

    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Clientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/busca_cliente.css">      
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-users"></i> Lista de Clientes</h2>
        
        <form action="buscar_cliente.php" method="POST" class="search-form">
            <div class="form-group">
                <label for="busca"><i class="fas fa-search"></i> Buscar Cliente:</label>
                <input type="text" id="busca" name="busca" placeholder="Digite nome ou ID">
            </div>
            <button type="submit">Pesquisar</button>
            <button type="submit" name="mostrar_todos" class="show-all-btn">Mostrar Todos</button>
        </form> 

        <?php if (!empty($clientes)): ?>
            <table class="results-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Endereço</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                <td><?= htmlspecialchars($cliente['nome_cliente']) ?></td>
                <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                <td><?= htmlspecialchars($cliente['email']) ?></td>
                <td class="action-links">
                    <a href="alterar_cliente.php?id=<?= $cliente['id_cliente'] ?>" class="edit-link">
                        <i class="fas fa-edit"></i> Alterar
                    </a>
                    <?php if ($_SESSION['perfil'] == 1): ?>
                        <a href="excluir_cliente.php?id=<?= $cliente['id_cliente'] ?>" class="delete-link">
                     <i class="fas fa-trash-alt"></i> Excluir
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['mostrar_todos'])): ?>
            <div class="no-results">
                <i class="fas fa-info-circle"></i> Nenhum cliente encontrado com os critérios informados.
            </div>
        <?php endif; ?>

        <a href="principal.php" class="back-button"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</body>
</html>