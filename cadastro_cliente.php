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
    $nome_cliente = trim($_POST['nome_cliente']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $endereco = trim($_POST['endereco']);
    
    // Validação básica
    if (empty($nome_cliente)) {
        $erro = "O nome do cliente é obrigatório!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } else {
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
                $sucesso = "Cliente cadastrado com sucesso!";
                // Limpa os campos do formulário após cadastro bem-sucedido
                $nome_cliente = $telefone = $email = $endereco = '';
            } else {
                $erro = "Erro ao cadastrar cliente!";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $erro = "Este e-mail já está cadastrado no sistema!";
            } else {
                $erro = "Erro no banco de dados: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/cadastro_cliente.css">
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-user-plus"></i> Cadastrar Cliente</h2>
        
        <?php if(isset($sucesso)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($erro)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form action="cadastro_cliente.php" method="POST" class="form-cliente">
            <div class="form-group full-width">
                <label for="nome_cliente"><i class="fas fa-user"></i> Nome Completo:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" required 
                 pattern="[A-Za-zÀ-ÿ\s]+" title="Apenas letras e espaços são permitidos"
                 value="<?php echo isset($nome_cliente) ? htmlspecialchars($nome_cliente) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="telefone"><i class="fas fa-phone"></i> Telefone:</label>
                <input type="tel" id="telefone" name="telefone" required 
                       placeholder="(00) 00000-0000" 
                       value="<?php echo isset($telefone) ? htmlspecialchars($telefone) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group full-width">
                <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço:</label>
                <input type="text" id="endereco" name="endereco" required 
                       value="<?php echo isset($endereco) ? htmlspecialchars($endereco) : ''; ?>">
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-broom"></i> Limpar
                </button>
                <a href="principal.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#telefone').mask('(00) 00000-0000');
            
            // Foco automático no primeiro campo
            $('#nome_cliente').focus();
            
            $('form').on('submit', function(e) {
            let isValid = true;

            // Verifica se todos os campos obrigatórios estão preenchidos
             $(this).find('[required]').each(function() {
                  if (!$(this).val().trim()) {
            $(this).css('border-color', 'var(--danger-color)');
            isValid = false;
             } else {
            $(this).css('border-color', '');
             }
             });

            // Validação extra: nome não pode conter números
            let nomeVal = $('#nome_cliente').val().trim();
            if (/\d/.test(nomeVal)) {
                $('#nome_cliente').css('border-color', 'var(--danger-color)');
                alert('O nome não pode conter números!');
                e.preventDefault();
                return;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios!');
            }
        });
                });
    </script>
</body>
</html>