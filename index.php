<?php
// Puxa a conexão do arquivo config.php
require_once __DIR__ . '/config.php';

try {
    // Busca todos os veículos ativos do banco de dados
    $stmt = $pdo->query("SELECT * FROM $tabela ORDER BY id DESC");
    $allVehicles = $stmt->fetchAll();
    // Converte os escapes unicode de volta para texto legível na listagem geral
if ($allVehicles) {
    foreach ($allVehicles as &$carro) {
        foreach ($carro as $key => $value) {
            if (is_string($value)) {
                $carro[$key] = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                    return json_decode('"' . $match[0] . '"');
                }, $value);
            }
        }
    }
    unset($carro); // Limpa a referência
}
} catch (PDOException $e) {
    $allVehicles = [];
}

// 1. Filtra a lista geral de veículos por uma categoria específica
function filtrarPorCategoria(array $veiculos, string $categoriaNome) {
    return array_filter($veiculos, function($carro) use ($categoriaNome) {
        return strtolower(trim($carro['categoria'] ?? '')) === strtolower(trim($categoriaNome));
    });
}

// 2. Transforma as strings de preço em números reais para o sistema poder ordenar
function extrairValorParaOrdenacao($precoStr) {
    $precoLimpo = strtolower(trim((string)$precoStr));
    
    // Se não tiver preço ou for "Sob Consulta", consideramos o valor máximo para ficar no topo
    if (empty($precoLimpo) || strpos($precoLimpo, 'consulta') !== false || strpos($precoLimpo, 'consulte') !== false) {
        return 999999999999.0;
    }
    
    // Remove moedas (R$, $), letras e pontos. Mantém apenas números e a vírgula dos centavos
    $numeros = preg_replace('/[^0-9,]/', '', $precoLimpo);
    $numeros = str_replace(',', '.', $numeros); // Troca vírgula por ponto para o PHP entender como decimal
    
    return (float) $numeros;
}

// 3. Ordena os veículos do mais caro para o mais barato
function ordenarPorPrecoDesc(array $veiculos) {
    usort($veiculos, function($a, $b) {
        $precoA = extrairValorParaOrdenacao($a['preco'] ?? '');
        $precoB = extrairValorParaOrdenacao($b['preco'] ?? '');
        return $precoB <=> $precoA; // Operador Spaceship para ordem decrescente
    });
    return $veiculos;
}

// 4. Obtém a primeira URL da lista de múltiplas imagens salvas
function obterPrimeiraImagem(?string $imagensUrls) {
    if (empty(trim($imagensUrls))) {
        return 'https://via.placeholder.com/600x400?text=Imagem+Indispon%C3%ADvel';
    }
    $linhas = explode("\n", str_replace("\r", "", $imagensUrls));
    return trim($linhas[0]);
}

// Geração dinâmica da lista de Marcas exclusivas
$marcasDisponiveis = [];
if (!empty($allVehicles)) {
    $marcasDisponiveis = array_unique(array_filter(array_column($allVehicles, 'marca')));
    sort($marcasDisponiveis);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApexMotors | Exclusividade em Alta Performance</title>
    
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
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Categorias<span class="arrow"></span></a>
                    <ul class="dropdown-content">
                        <li><a href="#Supercars">Supercars</a></li>
                        <li><a href="#Hypercars">Hypercars</a></li>
                        <li><a href="#Luxury">Luxury Cars</a></li>
                        <li><a href="#SUVs">Luxury SUVs</a></li>
                        <li><a href="#GT">Grand Tourers (GT)</a></li>
                        <li><a href="#Exclusive">Exclusive</a></li>
                        <li><a href="#Limited">Limited Edition</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Marcas<span class="arrow"></span></a>
                    <ul class="dropdown-content">
                        <?php if (empty($marcasDisponiveis)): ?>
                            <li><a href="javascript:void(0)">Nenhuma marca ativa</a></li>
                        <?php else: ?>
                            <?php foreach ($marcasDisponiveis as $marca): ?>
                                <li><a href="/marca.php?brand=<?= urlencode(strtolower($marca)) ?>"><?= htmlspecialchars($marca, ENT_QUOTES) ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <li><a href="servicos.html">Serviços</a></li>
                <li><a href="quemnossomos.html">Quem somos</a></li>
                <li><a href="noticias.html">Blog</a></li>
            </ul>
        </div>
        <div class="navbar-right">
            <div class="search-bar">
                <input type="text" placeholder="Buscar veículo...">
                <button type="button" aria-label="Buscar">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <header class="hero" id="home">
        <div class="video-container">
            <iframe 
                src="https://www.youtube.com/embed/cvu2AxwfOUI?autoplay=1&mute=1&controls=0&showinfo=0&rel=0&loop=1&playlist=cvu2AxwfOUI&playsinline=1" 
                frameborder="0" 
                allow="autoplay; encrypted-media" 
                allowfullscreen>
            </iframe>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content animate-on-scroll">
            <h1>Domine o Asfalto</h1>
            <p>Velocidade, Potência e o Mais Absoluto Luxo.<br>Descubra a elite automotiva.</p>
        </div>
    </header>

    <main id="catalog" class="catalog">
        
        <?php
        // Lista de categorias formatada para redirecionar corretamente para categoria.php
        $categoriasFiltro = [
            'Supercars' => ['id' => 'Supercars', 'titulo' => 'Supercars', 'link' => '/categoria.php?category=supercars'],
            'Hypercars' => ['id' => 'Hypercars', 'titulo' => 'Hypercars', 'link' => '/categoria.php?category=hypercars'],
            'Luxury Cars' => ['id' => 'Luxury', 'titulo' => 'Luxury Cars', 'link' => '/categoria.php?category=luxury-cars'],
            'Luxury SUVs' => ['id' => 'SUVs', 'titulo' => 'Luxury SUVs', 'link' => '/categoria.php?category=luxury-suvs'],
            'Grand Tourers' => ['id' => 'GT', 'titulo' => 'Grand Tourers (GT)', 'link' => '/categoria.php?category=grand-tourers'],
            'Exclusive' => ['id' => 'Exclusive', 'titulo' => 'Exclusive', 'link' => '/categoria.php?category=exclusive'],
            'Limited Edition' => ['id' => 'Limited', 'titulo' => 'Limited Edition', 'link' => '/categoria.php?category=limited-edition']
        ];

        foreach ($categoriasFiltro as $dbCat => $info): 
            $carrosDaCategoria = filtrarPorCategoria($allVehicles, $dbCat);
            
            // LÓGICA NOVA: Ordena do mais caro pro mais barato e corta para exibir apenas 3 carros
            $carrosDaCategoria = ordenarPorPrecoDesc($carrosDaCategoria);
            $carrosDaCategoria = array_slice($carrosDaCategoria, 0, 3);
        ?>
            <section id="<?= $info['id'] ?>" class="category-section">
                <div class="category-header animate-on-scroll">
                    <div class="category-title-group">
                        <h2><?= $info['titulo'] ?></h2>
                    </div>
                    <a href="<?= $info['link'] ?>" class="btn-see-more">Veja mais modelos</a>
                </div>
                
                <div class="grid">
                    <?php if (empty($carrosDaCategoria)): ?>
                        <div style="color: #666; padding: 20px; font-weight: 300; grid-column: 1 / -1;">
                            Nenhum exemplar cadastrado nesta categoria no momento.
                        </div>
                    <?php else: ?>
                        <?php foreach ($carrosDaCategoria as $carro): 
                            $capaCarro = obterPrimeiraImagem($carro['imagens_urls'] ?? '');
                        ?>
                            <div class="card animate-on-scroll">
                                <img src="<?= htmlspecialchars($capaCarro, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($carro['marca'] . ' ' . $carro['modelo'], ENT_QUOTES) ?>" class="card-img">
                                <div class="card-info">
                                    <h3><?= htmlspecialchars($carro['marca'] . ' ' . $carro['modelo'], ENT_QUOTES) ?></h3>
                                    
                                    <p class="price">
                                        <?= htmlspecialchars(!empty($carro['preco']) ? $carro['preco'] : ($carro['status'] ?? 'Consulte'), ENT_QUOTES) ?>
                                    </p>
                                    
                                    <a href="/detalhes.php?id=<?= urlencode($carro['id']) ?>" class="btn btn-outline" style="text-align: center; text-decoration: none;">Ver Detalhes</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section id="Benefits" class="category-section">
            <div class="benefits-grid animate-on-scroll">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div class="benefit-info">
                        <h4>1 Ano de Carência</h4>
                        <p>Pague a primeira parcela só em 2027</p>
                    </div>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div class="benefit-info">
                        <h4>Garantia de 1 Ano</h4>
                        <p>Motor e câmbio - Consulte condições</p>
                    </div>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    </div>
                    <div class="benefit-info">
                        <h4>Procedência Verificada</h4>
                        <p>Histórico completo e laudo cautelar incluso</p>
                    </div>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="benefit-info">
                        <h4>25 Anos de Mercado</h4>
                        <p>Desde 2001, referência em veículos premium</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="Testimonials" class="testimonials-section category-section">
            <div class="testimonials-header animate-on-scroll">
                <span class="subtitle">Depoimentos</span>
                <h2>O Que Nossos Clientes Dizem</h2>
                <div class="rating-summary">
                    <span class="stars-summary">★★★★★</span> 4.8 de 5 • 1300+ avaliações no Google
                </div>
            </div>
            <div class="carousel-container animate-on-scroll">
                <button class="carousel-btn prev-btn" aria-label="Avaliação Anterior">❮</button>
                <div class="carousel-track" id="reviewsTrack">
                    </div>
                <button class="carousel-btn next-btn" aria-label="Próxima Avaliação">❯</button>
            </div>
            <div class="testimonials-footer animate-on-scroll">
                <a href="#">Ver todas as avaliações no Google Maps →</a>
            </div>
        </section>

        <section id="Social" class="social-section category-section">
            <div class="social-header animate-on-scroll">
                <span class="subtitle">Siga-nos</span>
                <h2>Nossas Redes Sociais</h2>
            </div>
            <div class="social-grid animate-on-scroll">
                </div>
        </section>

    </main>

    <section id="Location" class="location-section category-section">
        <div class="location-container animate-on-scroll">
            <div class="location-content">
                <span class="subtitle">Visite a ApexMotors</span>
                <h2>Nossa Localização</h2>
                <p class="location-desc">Nosso showroom premium foi projetado para oferecer uma experiência exclusiva e imersiva...</p>
                <a href="https://maps.google.com" target="_blank" class="btn-location">Traçar Rota</a>
            </div>
            <div class="location-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3656.968270528243!2d-46.67104712390824!3d-23.570222361864114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94ce59d7b420286b%3A0x1d47cebb68c005b6!2sAv.%20Europa%2C%20S%C3%A3o%20Paulo%20-%20SP!5e0!3m2!1spt-BR!2sbr!4v1714088915858!5m2!1spt-BR!2sbr" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <footer>
        <a href="#home" class="logo">Apex<span>Motors</span></a>
        <p>© 2026 Apex Motors. A excelência automotiva reimaginada.</p>
    </footer>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.getElementById('reviewsTrack');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');

            if (track && prevBtn && nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const cardWidth = track.querySelector('.review-card').offsetWidth + 20;
                    track.scrollBy({ left: cardWidth, behavior: 'smooth' });
                });
                prevBtn.addEventListener('click', () => {
                    const cardWidth = track.querySelector('.review-card').offsetWidth + 20;
                    track.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                });
            }
        });
    </script>
</body>
</html>
