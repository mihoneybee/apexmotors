<?php
require_once __DIR__ . '/supabase.php';

$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;
$veiculo = null;

if ($id !== null && ctype_digit((string)$id)) {
    $veiculo = buscarVeiculoById($id);
}

if (!$veiculo && $slug !== null) {
    $veiculo = buscarVeiculoPorSlug($slug);
}

if (!$veiculo) {
    http_response_code(404);
    $pageTitle = 'Veículo não encontrado | ApexMotors';
    $message = 'Não foi possível localizar o veículo solicitado. Verifique o ID ou o nome do veículo e tente novamente.';
} else {
    $pageTitle = htmlspecialchars(($veiculo['marca'] ?? 'Veículo') . ' ' . ($veiculo['modelo'] ?? ''), ENT_QUOTES);
    $gallery = buscarGaleriaPorVeiculoId($veiculo['id']);
    $mainImage = !empty($veiculo['imagem_url']) ? $veiculo['imagem_url'] : 'https://via.placeholder.com/1200x700?text=Imagem+Indispon%C3%ADvel';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Detalhes do veículo ApexMotors carregados do Supabase.">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .detail-hero {
            padding: 80px 5vw 40px;
            max-width: 1180px;
            margin: 0 auto;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 40px;
        }
        .detail-panel {
            background: rgba(14, 14, 14, 0.92);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,.16);
        }
        .detail-panel img {
            width: 100%;
            display: block;
            object-fit: cover;
        }
        .detail-body {
            padding: 32px;
        }
        .detail-body h1 {
            margin: 0 0 18px;
            font-size: 2.8rem;
            line-height: 1.05;
        }
        .detail-body .meta-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            display: grid;
            gap: 12px;
        }
        .detail-body .meta-list li {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #d7d7d7;
            font-size: 0.98rem;
        }
        .detail-body .meta-list li span:last-child {
            color: #fff;
            font-weight: 600;
        }
        .detail-body p { color: #ccc; line-height: 1.8; }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
            margin-top: 24px;
        }
        .gallery-grid img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,.08);
        }
        .btn-primary {
            display: inline-block;
            background: #f7a600;
            color: #111;
            padding: 14px 24px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            margin-top: 18px;
        }
        @media (max-width: 960px) { .detail-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="navbar-left">
            <a href="index.html" class="logo">Apex<span>Motors</span></a>
        </div>
        <div class="navbar-center">
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="/supercars.php">Supercars</a></li>
                <li><a href="servicos.html">Serviços</a></li>
                <li><a href="quemnossomos.html">Quem somos</a></li>
                <li><a href="noticias.html">Blog</a></li>
            </ul>
        </div>
    </nav>

    <main class="detail-hero">
        <?php if (!$veiculo): ?>
            <section class="detail-panel detail-body" style="text-align:center;">
                <h1>Veículo não encontrado</h1>
                <p><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
                <a class="btn-primary" href="index.html">Voltar ao catálogo</a>
            </section>
        <?php else: ?>
            <div class="detail-grid">
                <div class="detail-panel">
                    <img src="<?= htmlspecialchars($mainImage, ENT_QUOTES) ?>" alt="<?= $pageTitle ?>">
                </div>

                <div class="detail-panel detail-body">
                    <h1><?= $pageTitle ?></h1>
                    <ul class="meta-list">
                        <li><span>ID</span><span><?= htmlspecialchars($veiculo['id'], ENT_QUOTES) ?></span></li>
                        <li><span>Marca</span><span><?= htmlspecialchars($veiculo['marca'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Modelo</span><span><?= htmlspecialchars($veiculo['modelo'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Categoria</span><span><?= htmlspecialchars($veiculo['categoria'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Status</span><span><?= htmlspecialchars($veiculo['status'] ?? 'Consultar', ENT_QUOTES) ?></span></li>
                        <?php if (!empty($veiculo['preco'])): ?>
                            <li><span>Preço</span><span><?= htmlspecialchars($veiculo['preco'], ENT_QUOTES) ?></span></li>
                        <?php endif; ?>
                    </ul>

                    <p><?= nl2br(htmlspecialchars($veiculo['descricao'] ?? 'Descrição não cadastrada no banco de dados.', ENT_QUOTES)) ?></p>
                    <a class="btn-primary" href="/supercars.php">Voltar aos Supercars</a>

                    <?php if (count($gallery) > 0): ?>
                        <div class="gallery-grid">
                            <?php foreach ($gallery as $item): ?>
                                <?php if (!empty($item['imagem_url'])): ?>
                                    <img src="<?= htmlspecialchars($item['imagem_url'], ENT_QUOTES) ?>" alt="Imagem da galeria <?= htmlspecialchars($pageTitle, ENT_QUOTES) ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <a href="index.html#home" class="logo">Apex<span>Motors</span></a>
        <p>© 2026 Apex Motors. A excelência automotiva reimaginada.</p>
    </footer>
</body>
</html>
