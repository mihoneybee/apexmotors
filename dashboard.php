<?php
session_start();
require_once __DIR__ . '/supabase.php';

if (empty($_SESSION['is_admin'])) {
    header('Location: /login.php');
    exit;
}

$message = '';
$error = '';
$action = $_POST['action'] ?? null;

function redirectToDashboard(array $params = []) {
    $query = http_build_query($params);
    header('Location: /dashboard.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $sql = sprintf('DELETE FROM %s WHERE id = :id', SUPABASE_CARS_TABLE);
        $pdo = supabaseConnect();

        try {
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                $message = 'Veículo excluído com sucesso.';
            } else {
                $error = 'A conexão com o banco não está disponível.';
            }
        } catch (Exception $e) {
            $error = 'Falha ao excluir o veículo. Verifique se ele ainda existe.';
        }
    }

    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $preco = trim($_POST['preco'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $imagem_url = trim($_POST['imagem_url'] ?? '');

        if ($marca === '' || $modelo === '') {
            $error = 'Marca e modelo são obrigatórios.';
        } else {
            $pdo = supabaseConnect();
            if ($pdo instanceof PDO) {
                if ($id !== '') {
                    $sql = sprintf(
                        'UPDATE %s SET marca = :marca, modelo = :modelo, categoria = :categoria, status = :status, preco = :preco, descricao = :descricao, imagem_url = :imagem_url WHERE id = :id',
                        SUPABASE_CARS_TABLE
                    );
                    $params = [
                        'marca' => $marca,
                        'modelo' => $modelo,
                        'categoria' => $categoria,
                        'status' => $status,
                        'preco' => $preco,
                        'descricao' => $descricao,
                        'imagem_url' => $imagem_url,
                        'id' => intval($id),
                    ];
                } else {
                    $sql = sprintf(
                        'INSERT INTO %s (marca, modelo, categoria, status, preco, descricao, imagem_url) VALUES (:marca, :modelo, :categoria, :status, :preco, :descricao, :imagem_url)',
                        SUPABASE_CARS_TABLE
                    );
                    $params = [
                        'marca' => $marca,
                        'modelo' => $modelo,
                        'categoria' => $categoria,
                        'status' => $status,
                        'preco' => $preco,
                        'descricao' => $descricao,
                        'imagem_url' => $imagem_url,
                    ];
                }

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = ($id !== '' ? 'Veículo atualizado com sucesso.' : 'Veículo cadastrado com sucesso.');
                } catch (Exception $e) {
                    $error = 'Erro ao salvar veículo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
                }
            } else {
                $error = 'A conexão com o banco não está disponível.';
            }
        }
    }
}

$vehicles = supabaseQueryRows(sprintf('SELECT * FROM %s ORDER BY id DESC', SUPABASE_CARS_TABLE));

$editing = null;
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {
    $editing = buscarVeiculoById(intval($_GET['edit']));
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | ApexMotors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="navbar-left"><a href="/index.php" class="logo">Apex<span>Motors</span></a></div>
        <div class="navbar-center">
            <ul class="nav-links">
                <li><a href="/dashboard.php">Dashboard</a></li>
                <li><a href="/logout.php">Sair</a></li>
            </ul>
        </div>
    </nav>

    <main class="dashboard-page">
        <section class="dashboard-header">
            <div>
                <h1>Painel de Controle</h1>
                <p>Gerencie os veículos cadastrados no banco de dados.</p>
            </div>
            <a href="/dashboard.php" class="btn btn-outline">Atualizar</a>
        </section>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <section class="dashboard-grid">
            <div class="dashboard-panel">
                <h2><?= $editing ? 'Editar veículo' : 'Novo veículo' ?></h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id'] ?? '', ENT_QUOTES) ?>">

                    <div class="form-group full-width">
                        <label for="marca">Marca</label>
                        <input type="text" id="marca" name="marca" value="<?= htmlspecialchars($editing['marca'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($editing['modelo'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="categoria">Categoria</label>
                        <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($editing['categoria'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="status">Status</label>
                        <input type="text" id="status" name="status" value="<?= htmlspecialchars($editing['status'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="preco">Preço</label>
                        <input type="text" id="preco" name="preco" value="<?= htmlspecialchars($editing['preco'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="imagem_url">URL da imagem</label>
                        <input type="url" id="imagem_url" name="imagem_url" value="<?= htmlspecialchars($editing['imagem_url'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="6"><?= htmlspecialchars($editing['descricao'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Salvar veículo</button>
                </form>
            </div>

            <div class="dashboard-panel dashboard-table-panel">
                <h2>Veículos cadastrados</h2>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vehicle['id'], ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($vehicle['marca'] ?? '-', ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($vehicle['modelo'] ?? '-', ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($vehicle['categoria'] ?? '-', ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($vehicle['status'] ?? '-', ENT_QUOTES) ?></td>
                                    <td class="actions-cell">
                                        <a href="/dashboard.php?edit=<?= urlencode($vehicle['id']) ?>" class="action-link">Editar</a>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir este veículo?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($vehicle['id'], ENT_QUOTES) ?>">
                                            <button type="submit" class="action-delete">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
