<?php
session_start();

if (!empty($_SESSION['is_admin'])) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'apexmotors' && $password === 'ApeXMotorS') {
        $_SESSION['is_admin'] = true;
        header('Location: /dashboard.php');
        exit;
    }

    $error = 'Login ou senha incorretos. Verifique e tente novamente.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | ApexMotors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="login-page">
        <div class="login-card">
            <h1>Admin ApexMotors</h1>
            <p>Faça login para acessar o painel de gerenciamento de veículos.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="post" class="form-grid login-form">
                <div class="form-group full-width">
                    <label for="username">Usuário</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES) ?>" required autofocus>
                </div>
                <div class="form-group full-width">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit full-width">Entrar</button>
            </form>

            <p class="login-hint">Usuário: <strong>apexmotors</strong> | Senha: <strong>ApeXMotorS</strong></p>
        </div>
    </main>
</body>
</html>
