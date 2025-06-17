<?php
session_start();
require 'conexao.php';

// Verifica permissão admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$erro = '';
$clientes = [];

// Se o ID do cliente está na URL, busca e exibe automaticamente
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idBusca = (int) $_GET['id'];

    try {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idBusca, PDO::PARAM_INT);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$clientes) {
            $erro = "Cliente com ID $idBusca não encontrado.";
        }
    } catch (PDOException $e) {
        $erro = "Erro no banco de dados: " . $e->getMessage();
    }
}


// Exclusão do cliente via POST após confirmação
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirmar_exclusao'])) {
    $idExcluir = (int) $_POST['id_cliente'];

    try {
        $sqlDelete = "DELETE FROM cliente WHERE id_cliente = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $idExcluir, PDO::PARAM_INT);
        $stmtDelete->execute();

        if ($stmtDelete->rowCount() > 0) {
            echo "<script>alert('Cliente excluído com sucesso!'); window.location.href='excluir_cliente.php';</script>";
            exit();
        } else {
            echo "<script>alert('Cliente não encontrado ou já excluído.'); window.location.href='excluir_cliente.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir cliente: " . addslashes($e->getMessage()) . "'); window.location.href='excluir_cliente.php';</script>";
        exit();
    }
}


// Se o formulário foi enviado com o botão "Mostrar Todos"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mostrar_todos'])) {
    try {
        $sql = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erro = "Erro no banco de dados: " . $e->getMessage();
        error_log("PDOException: " . $e->getMessage());
    }
}
// Busca específica quando o campo de busca está preenchido
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca_cliente']) && !empty(trim($_POST['busca_cliente']))) {
    $busca = trim($_POST['busca_cliente']);

    try {
        if (preg_match('/^(\d+)\s*-\s*(.+)/', $busca, $matches)) {
            $id_busca = $matches[1];
            $nome_busca = trim($matches[2]);

            $sql = "SELECT * FROM cliente WHERE id_cliente = :id OR nome_cliente LIKE :nome ORDER BY nome_cliente ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id_busca, PDO::PARAM_INT);
            $nome_param = "%" . $nome_busca . "%";
            $stmt->bindParam(':nome', $nome_param, PDO::PARAM_STR);
        } elseif (is_numeric($busca)) {
            $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY nome_cliente ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM cliente WHERE nome_cliente LIKE :busca ORDER BY nome_cliente ASC";
            $stmt = $pdo->prepare($sql);
            $nome_param = "%" . $busca . "%";
            $stmt->bindParam(':busca', $nome_param, PDO::PARAM_STR);
        }

        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$clientes) {
            $erro = "Nenhum cliente encontrado para: \"$busca\"";
        }
    } catch (PDOException $e) {
        $erro = "Erro no banco de dados: " . $e->getMessage();
        error_log("PDOException: " . $e->getMessage());
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Caso o formulário foi enviado sem nenhum dado de busca
    $erro = "Por favor, digite o ID ou Nome do cliente para buscar, ou clique em Mostrar Todos.";
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Buscar Cliente para Excluir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/alterar_cliente.css" />
    <link rel="stylesheet" href="css/excluir_cliente.css" />
</head>
<body>
<h1>Kauan Dos Anjos Vieira</h1>
    <div class="container">
        <h2><i class="fas fa-user-slash"></i> Buscar Cliente para Excluir</h2>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="excluir_cliente.php" id="buscaForm">
            <div class="form-group">
                <label for="busca_cliente"><i class="fas fa-search"></i> Digite o ID ou Nome do cliente:</label>
                <input type="text" id="busca_cliente" name="busca_cliente" 
                       value="<?= isset($_POST['busca_cliente']) ? htmlspecialchars($_POST['busca_cliente']) : '' ?>">
            </div>
            <button type="submit" name="buscar" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button type="submit" name="mostrar_todos" class="btn btn-secondary">
                <i class="fas fa-list"></i> Mostrar Todos
            </button>
        </form>

        <?php if ($clientes): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Endereço</th>
                        <th>Excluir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cli): ?>
                        <tr>
                            <td><?= htmlspecialchars($cli['id_cliente']) ?></td>
                            <td><?= htmlspecialchars($cli['nome_cliente']) ?></td>
                            <td><?= htmlspecialchars($cli['telefone']) ?></td>
                            <td><?= htmlspecialchars($cli['email']) ?></td>
                            <td><?= htmlspecialchars($cli['endereco']) ?></td>
                            <td>
                            <form method="POST" action="excluir_cliente.php" onsubmit="return confirm('Confirma a exclusão do cliente <?= htmlspecialchars(addslashes($cli['nome_cliente'])) ?>?');" style="display:inline;">
    <input type="hidden" name="id_cliente" value="<?= $cli['id_cliente'] ?>">
    <button type="submit" name="confirmar_exclusao" class="btn-excluir">
        <i class="fas fa-trash-alt"></i> Excluir
    </button>
</form>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="principal.php" class="btn btn-back" style="margin-top:1rem;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</body>
</html>
