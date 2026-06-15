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
    $imagensTexto = $veiculo['imagens_urls'] ?? '';
    $gallery = [];
    if (!empty(trim($imagensTexto))) {
        $gallery = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $imagensTexto))));
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
            <section class="detail-panel detail-body" style="text-align:center;">
                <h1>Veículo não encontrado</h1>
                <p><?= htmlspecialchars($message, ENT_QUOTES) ?></p>
                <a class="btn btn-outline" href="/index.php">Voltar ao catálogo</a>
            </section>
        <?php else: ?>
            <div class="detail-grid">
                <div class="detail-panel">
                    <img src="<?= htmlspecialchars($mainImage, ENT_QUOTES) ?>" alt="<?= $pageTitle ?>" id="main-car-img" style="width: 100%; border-radius: 8px; transition: opacity 0.3s ease;">
                    
                    <?php if (count($gallery) > 1): ?>
                        <div class="gallery-thumbs" style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto;">
                            <?php foreach (array_values($gallery) as $index => $img): ?>
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="Thumb" class="thumb-img <?= $index === 0 ? 'active' : '' ?>" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer; border-radius: 4px; border: 2px solid transparent;" onclick="goToImage(<?= $index ?>)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-panel detail-body">
                    <h1><?= $pageTitle ?></h1>
                    <ul class="meta-list">
                        <li><span>Marca</span><span><?= htmlspecialchars($veiculo['marca'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Modelo</span><span><?= htmlspecialchars($veiculo['modelo'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Categoria</span><span><?= htmlspecialchars($veiculo['categoria'] ?? 'N/D', ENT_QUOTES) ?></span></li>
                        <li><span>Status</span><span><?= htmlspecialchars($veiculo['status'] ?? 'Consultar', ENT_QUOTES) ?></span></li>
                        <?php if (!empty($veiculo['preco'])): ?>
                            <li><span>Preço</span><span><?= htmlspecialchars($veiculo['preco'], ENT_QUOTES) ?></span></li>
                        <?php endif; ?>
                    </ul>

                    <p style="line-height: 1.6; margin-bottom: 20px;"><?= nl2br(htmlspecialchars($veiculo['descricao'] ?? 'Descrição não cadastrada.', ENT_QUOTES)) ?></p>
                    
                    <!-- SESSÃO DO PILOTO -->
                    <?php if (!empty($veiculo['tem_piloto'])): ?>
                        <div style="background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); padding: 15px; margin-bottom: 20px; border-radius: 0 8px 8px 0;">
                            <h3 style="margin-bottom: 15px; font-size: 1.2rem;">Experiência em Pista</h3>
                            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                <?php if (!empty($veiculo['imagem_piloto_url'])): ?>
                                    <img src="<?= htmlspecialchars($veiculo['imagem_piloto_url'], ENT_QUOTES) ?>" alt="Piloto" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid var(--primary);">
                                <?php endif; ?>
                                
                                <?php if (!empty($veiculo['video_piloto_url'])): ?>
                                    <a href="<?= htmlspecialchars($veiculo['video_piloto_url'], ENT_QUOTES) ?>" target="_blank" class="btn btn-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        Assistir Piloto em Ação
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-solicitar" style="width: 100%;">Solicitar Aquisição</button>
                </div>
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
                document.getElementById('main-car-img').src = carImages[index];
                
                // Atualiza a borda da miniatura ativa
                let thumbs = document.querySelectorAll('.thumb-img');
                thumbs.forEach(t => t.style.borderColor = 'transparent');
                if (thumbs[index]) {
                    thumbs[index].style.borderColor = 'var(--primary)';
                }
            }
        }
    </script>
</body>
</html>
