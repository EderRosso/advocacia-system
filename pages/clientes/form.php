<?php
$page_title = 'Cadastro de Cliente';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$cliente = null;
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $rg = trim($_POST['rg']);
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $endereco = trim($_POST['endereco']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $cep = trim($_POST['cep']);
    $observacoes = trim($_POST['observacoes']);

    if (empty($nome) || empty($cpf)) {
        $erro = 'Nome e CPF são obrigatórios.';
    } else {
        try {
            if ($id) {
                // Atualizar
                $stmt = $pdo->prepare("UPDATE clientes SET nome=?, cpf=?, rg=?, data_nascimento=?, telefone=?, email=?, endereco=?, cidade=?, estado=?, cep=?, observacoes=? WHERE id=?");
                $stmt->execute([$nome, $cpf, $rg, $data_nascimento, $telefone, $email, $endereco, $cidade, $estado, $cep, $observacoes, $id]);
                $sucesso = 'Cliente atualizado com sucesso.';
            } else {
                // Inserir
                $stmt = $pdo->prepare("INSERT INTO clientes (nome, cpf, rg, data_nascimento, telefone, email, endereco, cidade, estado, cep, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $cpf, $rg, $data_nascimento, $telefone, $email, $endereco, $cidade, $estado, $cep, $observacoes]);
                $sucesso = 'Cliente cadastrado com sucesso.';
                $id = $pdo->lastInsertId(); // Pega ID inserido para popular form
            }
        } catch(PDOException $e) {
            // Verificar duplicate entry de CPF
            if($e->getCode() == 23000) {
                $erro = "Já existe um cliente cadastrado com este CPF.";
            } else {
                $erro = "Erro ao salvar cliente: " . $e->getMessage();
            }
        }
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    if($cliente) {
        $page_title = 'Editar Cliente - ' . htmlspecialchars($cliente['nome']);
    }
}
?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-user-edit"></i> Informações do Cliente</h3>
        <a href="index.php" class="btn btn-sm btn-blue">Voltar</a>
    </div>
    <div class="panel-body">
        <?php if($erro): ?> <div class="alert alert-danger"><?php echo $erro; ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="alert alert-success"><?php echo $sucesso; ?></div> <?php endif; ?>

        <form action="form.php<?php echo $id ? "?id=$id" : ""; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($cliente['nome'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>CPF *</label>
                    <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($cliente['cpf'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>RG</label>
                    <input type="text" name="rg" class="form-control" value="<?php echo htmlspecialchars($cliente['rg'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" value="<?php echo htmlspecialchars($cliente['data_nascimento'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" value="<?php echo htmlspecialchars($cliente['cep'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control" value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" value="<?php echo htmlspecialchars($cliente['cidade'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" id="estado" maxlength="2" class="form-control" placeholder="SP" value="<?php echo htmlspecialchars($cliente['estado'] ?? ''); ?>">
                </div>
                <div class="form-group full-width">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control"><?php echo htmlspecialchars($cliente['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Cliente</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cep');
    
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, ''); // Remove tudo que não for número
            
            if (cep.length === 8) {
                // Feedback visual de carregamento
                const enderecoInput = document.getElementById('endereco');
                const prevEndereco = enderecoInput.value;
                enderecoInput.value = 'Buscando endereço...';
                
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.erro) {
                            // Define logradouro + bairro no campo Endereço
                            let rua_bairro = data.logradouro;
                            if (data.bairro) rua_bairro += ' - ' + data.bairro;
                            
                            document.getElementById('endereco').value = rua_bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                            
                            // Move o foco para o endereço para o usuário apenas digitar o número
                            document.getElementById('endereco').focus();
                        } else {
                            alert("CEP não encontrado.");
                            enderecoInput.value = prevEndereco;
                        }
                    })
                    .catch(error => {
                        console.error('Erro na consulta do CEP:', error);
                        enderecoInput.value = prevEndereco;
                        alert('Erro ao buscar o CEP na rede.');
                    });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
