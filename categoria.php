<?php
require_once __DIR__ . '/supabase.php';

$categoryMap = [
    'supercars' => 'Supercars',
    'hypercars' => 'Hypercars',
    'luxury-cars' => 'Luxury Cars',
    'luxury-suvs' => 'Luxury SUVs',
    'grand-tourers' => 'Grand Tourers',
    'exclusive' => 'Exclusive',
    'limited-edition' => 'Limited Edition',
];

$categorySlug = strtolower(trim($_GET['category'] ?? $_GET['categoria'] ?? 'supercars'));
$categoryName = $categoryMap[$categorySlug] ?? null;

if (!$categoryName) {
    http_response_code(404);
    $pageTitle = 'Categoria inválida | ApexMotors';
    $vehicles = [];
    $errorMessage = 'Não encontramos essa categoria. Use uma categoria válida ou volte para a página inicial.';
} else {
    $pageTitle = sprintf('%s Collection | ApexMotors', $categoryName);
    $vehicles = buscarVeiculosPorCategoria($categoryName);
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
    <style>
        .category-section { padding: 60px 5vw; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .section-header { text-align: center; margin-bottom: 40px; }
        .centered-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; width: 100%; }
        .centered-grid .card { flex: 1 1 calc(33.333% - 20px); max-width: calc(33.333% - 20px); min-width: 320px; }
        @media (max-width: 1024px) { .centered-grid .card { flex: 1 1 calc(50% - 15px); max-width: calc(50% - 15px); } }
        @media (max-width: 768px) { .centered-grid .card { flex: 1 1 100%; max-width: 100%; } }
        .card-carro { background: rgba(14,14,14,.9); border: 1px solid rgba(255,255,255,.08); border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
        .img-container { width: 100%; min-height: 220px; overflow: hidden; }
        .img-fluida { width: 100%; height: 100%; object-fit: cover; display: block; }
        .card-body { padding: 22px; }
        .card-body h3 { margin: 0 0 12px; font-size: 1.2rem; }
        .status-preco { margin: 0 0 12px; color: #f9b600; font-weight: 600; }
        .btn-detalhes { display: inline-block; padding: 12px 20px; background: #f7a600; color: #111; border-radius: 999px; text-decoration: none; font-weight: 700; transition: transform .2s ease, background .2s ease; }
        .btn-detalhes:hover { transform: translateY(-2px); background: #ffb933; }
    </style>
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="navbar-left"><a href="/index.html" class="logo">Apex<span>Motors</span></a></div>
        <div class="navbar-center">
            <ul class="nav-links">
                <li><a href="/index.html">Home</a></li>
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
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="category-subtitle">Categorias</span>
            <h1 class="hero-title"><span><?= htmlspecialchars($categoryName ?? 'Categoria', ENT_QUOTES) ?></span><br>Collection</h1>
        </div>
    </header>

    <main>
        <section class="category-section">
            <div class="section-header">
                <h2 class="category-title"><?= htmlspecialchars($categoryName ? 'Modelos em Destaque' : 'Categoria não encontrada', ENT_QUOTES) ?></h2>
            </div>
            <div class="centered-grid">
                <?php if (!$categoryName): ?>
                    <div style="color:#fff; padding:40px; text-align:center; width:100%;">
                        <h3>Categoria inválida</h3>
                        <p><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></p>
                    </div>
                <?php elseif (empty($vehicles)): ?>
                    <div style="color:#fff; padding:40px; text-align:center; width:100%;">
                        <h3>Nenhum veículo encontrado</h3>
                        <p>Não há veículos cadastrados nesta categoria no banco de dados.</p>
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
        <a href="/index.html#home" class="logo">Apex<span>Motors</span></a>
        <p>© 2026 Apex Motors. A excelência automotiva reimaginada.</p>
    </footer>
</body>
</html>
