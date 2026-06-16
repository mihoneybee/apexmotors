<?php
// Adicionado temporariamente para mostrar erros na tela se algo falhar
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;
$veiculo = null;

// 1. Busca por ID
if ($id !== null && ctype_digit((string)$id)) {
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = :id");
    $stmt->execute(['id' => intval($id)]);
    $veiculo = $stmt->fetch();
} 
// 2. Busca por Slug (via link da home)
elseif ($slug !== null) {
    $modeloBusca = str_replace('-', ' ', $slug);
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE modelo LIKE :modelo LIMIT 1");
    $stmt->execute(['modelo' => "%$modeloBusca%"]);
    $veiculo = $stmt->fetch();
}
// Converte os escapes unicode (ex: \u00e9) de volta para texto legível (ex: é)
if ($veiculo) {
    foreach ($veiculo as $key => $value) {
        if (is_string($value)) {
            $veiculo[$key] = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return json_decode('"' . $match[0] . '"');
            }, $value);
        }
    }
}
// 3. Tratamento de Exibição
if (!$veiculo) {
    http_response_code(404);
    $pageTitle = 'Veículo não encontrado | ApexMotors';
    $message = 'Não foi possível localizar o veículo solicitado. Verifique o ID ou o nome do veículo e tente novamente.';
    $gallery = [];
    $mainImage = 'https://via.placeholder.com/1200x700?text=Imagem+Indispon%C3%ADvel';
} else {
    $pageTitle = htmlspecialchars(($veiculo['marca'] ?? 'Veículo') . ' ' . ($veiculo['modelo'] ?? ''), ENT_QUOTES);
    
    // Transforma o texto com várias URLs em uma lista (array)
    // Aceita que as URLs venham separadas por nova linha, vírgula, ponto-e-vírgula ou pipe
    $imagensTexto = $veiculo['imagens_urls'] ?? '';

    // Modo de teste: se passar ?test_images=1 preenche com imagens de exemplo
    if (isset($_GET['test_images']) && $_GET['test_images'] == '1') {
        $imagensTexto = implode("\n", [
            'https://images.unsplash.com/photo-1542367597-0b3b7a3b6e8a?auto=format&fit=crop&w=1400&q=80',
            'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1400&q=80',
            'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1400&q=80',
            'https://images.unsplash.com/photo-1517949908114-7ef8a6d9b2d6?auto=format&fit=crop&w=1400&q=80'
        ]);
    }
    $gallery = [];
    if (!empty(trim($imagensTexto))) {
        $parts = preg_split('/[\r\n,;|]+/', $imagensTexto);
        $gallery = array_values(array_filter(array_map('trim', $parts)));
    }
    
    // A primeira imagem da lista vira a imagem principal
    $mainImage = !empty($gallery) ? array_values($gallery)[0] : 'https://via.placeholder.com/1200x700?text=Imagem+Indispon%C3%ADvel';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Detalhes do veículo ApexMotors.">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="navbar-left">
            <a href="/index.php" class="logo">Apex<span>Motors</span></a>
        </div>
        <div class="navbar-center">
            <ul class="nav-links">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/servicos.html">Serviços</a></li>
                <li><a href="/quemnossomos.html">Quem somos</a></li>
                <li><a href="/noticias.html">Blog</a></li>
            </ul>
        </div>
    </nav>

    <main class="detail-hero">
        <?php if (!$veiculo): ?>
            <section class="detail-panel detail-body detail-not-found">
                <h1>Veículo não encontrado</h1>
                <p><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
                <a class="btn btn-outline" href="/index.php">Voltar ao catálogo</a>
            </section>
        <?php else: ?>
            <div class="car-details-page">
                <section class="gallery-fullwidth">
                    <div class="main-image-wrapper main-image-fixed">
                        <img src="<?= htmlspecialchars($mainImage, ENT_QUOTES) ?>" alt="<?= $pageTitle ?>" id="main-car-img" loading="lazy">
                    </div>

                    <?php if (count($gallery) > 1): ?>
                        <div class="gallery-thumbs">
                            <?php foreach (array_values($gallery) as $index => $img): ?>
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="Thumb" loading="lazy" class="thumb-img <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" onclick="goToImage(<?= $index ?>)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="car-content-centered">
                    <div class="car-header-info">
                        <h1><?= $pageTitle ?></h1>
                        <?php if (!empty($veiculo['preco'])): ?>
                            <div class="car-price"><?= htmlspecialchars($veiculo['preco'], ENT_QUOTES) ?></div>
                        <?php endif; ?>
                        <p class="car-short-desc"><?= nl2br(htmlspecialchars($veiculo['descricao'] ?? 'Descrição não cadastrada.', ENT_QUOTES)) ?></p>
                        <div style="margin-top:20px;">
                            <button class="btn btn-solicitar btn-primary-action">Solicitar Aquisição</button>
                        </div>
                    </div>

                    <div class="specs-grid" style="margin-top:10px;">
                        <div class="spec-item"><div class="spec-label">Marca</div><div class="spec-value"><?= htmlspecialchars($veiculo['marca'] ?? 'N/D', ENT_QUOTES) ?></div></div>
                        <div class="spec-item"><div class="spec-label">Modelo</div><div class="spec-value"><?= htmlspecialchars($veiculo['modelo'] ?? 'N/D', ENT_QUOTES) ?></div></div>
                        <div class="spec-item"><div class="spec-label">Categoria</div><div class="spec-value"><?= htmlspecialchars($veiculo['categoria'] ?? 'N/D', ENT_QUOTES) ?></div></div>
                        <div class="spec-item"><div class="spec-label">Status</div><div class="spec-value"><?= htmlspecialchars($veiculo['status'] ?? 'Consultar', ENT_QUOTES) ?></div></div>
                        <?php if (!empty($veiculo['preco'])): ?>
                            <div class="spec-item"><div class="spec-label">Preço</div><div class="spec-value"><?= htmlspecialchars($veiculo['preco'], ENT_QUOTES) ?></div></div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($veiculo['tem_piloto'])): ?>
                        <div class="driver-content" style="margin-top:30px;">
                            <?php if (!empty($veiculo['imagem_piloto_url'])): ?>
                                <div class="driver-image"><img src="<?= htmlspecialchars($veiculo['imagem_piloto_url'], ENT_QUOTES) ?>" alt="Piloto"></div>
                            <?php endif; ?>
                            <div class="driver-text">
                                <h3>Experiência em Pista</h3>
                                <?php if (!empty($veiculo['video_piloto_url'])): ?>
                                    <a href="<?= htmlspecialchars($veiculo['video_piloto_url'], ENT_QUOTES) ?>" target="_blank" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        Assistir Piloto em Ação
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="car-long-desc" style="margin-top:40px;">
                        <h2>Descrição</h2>
                        <p><?= nl2br(htmlspecialchars($veiculo['descricao'] ?? 'Descrição não cadastrada.', ENT_QUOTES)) ?></p>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <a href="/index.php#home" class="logo">Apex<span>Motors</span></a>
        <p>© 2026 Apex Motors. A excelência automotiva reimaginada.</p>
    </footer>
    
    <script src="script.js"></script>
    <script>
        // Lógica simples para trocar as imagens da galeria (caso script.js não carregue)
        let carImages = <?= json_encode(array_values($gallery)) ?>;

        function goToImage(index) {
            if (carImages.length > 0 && carImages[index]) {
                const mainImg = document.getElementById('main-car-img');
                mainImg.style.opacity = 0;
                setTimeout(() => {
                    mainImg.src = carImages[index];
                    mainImg.style.opacity = 1;
                }, 100);

                // Atualiza a miniatura ativa
                let thumbs = document.querySelectorAll('.thumb-img');
                thumbs.forEach((t, i) => {
                    t.classList.toggle('active', i === index);
                    t.style.borderColor = (i === index) ? 'var(--primary)' : 'transparent';
                });
            }
        }
    </script>
</body>
</html>
