<?php
require_once __DIR__ . '/supabase.php';

$brandMap = [
    'ferrari' => 'Ferrari',
    'lamborghini' => 'Lamborghini',
    'porsche' => 'Porsche',
    'koenigsegg' => 'Koenigsegg',
    'bugatti' => 'Bugatti',
    'mclaren' => 'McLaren',
    'rolls-royce' => 'Rolls-Royce',
    'pagani' => 'Pagani',
];

$brandSlug = strtolower(trim($_GET['brand'] ?? 'ferrari'));
$brandName = $brandMap[$brandSlug] ?? null;

if (!$brandName) {
    http_response_code(404);
    $pageTitle = 'Marca inválida | ApexMotors';
    $vehicles = [];
    $errorMessage = 'Não encontramos essa marca. Verifique o link ou volte para a página inicial.';
} else {
    $pageTitle = sprintf('%s Collection | ApexMotors', $brandName);
    $vehicles = buscarVeiculosPorMarca($brandName);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES) ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
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
                <li><a href="/index.php">Home</a></li>
                <li><a href="/categoria.php?category=supercars">Supercars</a></li>
                <li><a href="/categoria.php?category=hypercars">Hypercars</a></li>
                <li><a href="/categoria.php?category=luxury-cars">Luxury Cars</a></li>
                <li><a href="/categoria.php?category=luxury-suvs">Luxury SUVs</a></li>
                <li><a href="/categoria.php?category=grand-tourers">Grand Tourers</a></li>
                <li><a href="/categoria.php?category=exclusive">Exclusive</a></li>
                <li><a href="/categoria.php?category=limited-edition">Limited Edition</a></li>
                <li><a href="/servicos.html">Serviços</a></li>
                <li><a href="/quemnossomos.html">Quem somos</a></li>
                <li><a href="/noticias.html">Blog</a></li>
            </ul>
        </div>
    </nav>

    <header class="about-hero" style="background-image: linear-gradient(rgba(11, 11, 11, 0.7), rgba(11, 11, 11, 0.95)), url('https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <span class="category-subtitle">Marca</span>
            <h1 class="hero-title"><span><?= htmlspecialchars($brandName ?? 'Marca', ENT_QUOTES) ?></span> <br>Collection</h1>
        </div>
    </header>

    <main>
        <section class="category-section">
            <div class="section-header">
                <h2 class="category-title"><?= htmlspecialchars($brandName ? 'Modelos em Destaque de ' . $brandName : 'Marca não encontrada', ENT_QUOTES) ?></h2>
            </div>

            <div class="centered-grid">
                <?php if (!$brandName): ?>
                    <div style="color:#fff; padding:40px; text-align:center; width:100%;">
                        <h3>Marca inválida</h3>
                        <p><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></p>
                    </div>
                <?php elseif (empty($vehicles)): ?>
                    <div style="color:#fff; padding:40px; text-align:center; width:100%;">
                        <h3>Nenhum veículo encontrado</h3>
                        <p>Não há veículos cadastrados no banco de dados para esta marca.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($vehicles as $carro): ?>
                        <?php $urlImagem = !empty($carro['imagem_url']) ? $carro['imagem_url'] : 'https://via.placeholder.com/600x400?text=Imagem+Indispon%C3%ADvel'; ?>
                        <div class="card-carro">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($urlImagem, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($carro['marca'] . ' ' . $carro['modelo'], ENT_QUOTES) ?>" class="img-fluida">
                            </div>
                            <div class="card-body">
                                <h3><?= htmlspecialchars($carro['marca'] . ' ' . $carro['modelo'], ENT_QUOTES) ?></h3>
                                <p class="status-preco"><?= htmlspecialchars($carro['status'] ?? 'Consultar', ENT_QUOTES) ?></p>
                                <a href="/detalhes.php?id=<?= urlencode($carro['id']) ?>" class="btn-detalhes">Ver Detalhes</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <a href="/index.php#home" class="logo">Apex<span>Motors</span></a>
        <p>© 2026 Apex Motors. A excelência automotiva reimaginada.</p>
    </footer>
</body>
</html>
