<?php
// Adicionado temporariamente para diagnosticar qualquer erro se necessário
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Puxa a conexão do arquivo único de configuração
require_once __DIR__ . '/config.php';

$brandSlug = trim($_GET['brand'] ?? '');

$vehicles = [];
$brandName = '';
$errorMessage = '';

if (empty($brandSlug)) {
    http_response_code(404);
    $pageTitle = 'Marca inválida | ApexMotors';
    $errorMessage = 'Nenhuma marca especificada. Volte para a página inicial ou use o menu de navegação.';
} else {
    try {
        // Busca os veículos filtrando pela marca de forma case-insensitive (independente de maiúsculas/minúsculas)
        $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE LOWER(marca) = LOWER(:brand) ORDER BY id DESC");
        $stmt->execute(['brand' => $brandSlug]);
        $vehicles = $stmt->fetchAll();

        if (empty($vehicles)) {
            http_response_code(404);
            // Formata o slug apenas para exibição visual caso não encontre nada cadastrado
            $brandName = ucwords(str_replace('-', ' ', $brandSlug));
            $pageTitle = 'Nenhum modelo encontrado | ApexMotors';
            $errorMessage = "Não encontramos nenhum veículo cadastrado para a marca '" . htmlspecialchars($brandName, ENT_QUOTES) . "' no momento.";
        } else {
            // Pega o nome exato da marca do primeiro registro para preservar a grafia correta (ex: Rolls-Royce, McLaren)
            $brandName = $vehicles[0]['marca'];
            $pageTitle = sprintf('%s Collection | ApexMotors', $brandName);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $pageTitle = 'Erro interno | ApexMotors';
        $errorMessage = 'Ocorreu um erro ao processar a consulta no banco de dados.';
    }
}

// Busca todas as marcas exclusivas no banco para manter o menu Dropdown da Navbar dinâmico nesta página também
$marcasDisponiveis = [];
try {
    $stmtMarcas = $pdo->query("SELECT DISTINCT marca FROM $tabela WHERE marca IS NOT NULL AND marca != '' ORDER BY marca ASC");
    $marcasDisponiveis = $stmtMarcas->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback caso a tabela ainda não esteja configurada
}

// Auxiliar: Obtém a primeira URL do campo de texto de múltiplas imagens
function obterPrimeiraImagem(?string $imagensUrls) {
    if (empty(trim($imagensUrls))) {
        return 'https://via.placeholder.com/600x400?text=Imagem+Indispon%C3%ADvel';
    }
    $linhas = explode("\n", str_replace("\r", "", $imagensUrls));
    return trim($linhas[0]);
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
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Categorias<span class="arrow"></span></a>
                    <ul class="dropdown-content">
                        <li><a href="/categoria.php?category=supercars">Supercars</a></li>
                        <li><a href="/categoria.php?category=hypercars">Hypercars</a></li>
                        <li><a href="/categoria.php?category=luxury-cars">Luxury Cars</a></li>
                        <li><a href="/categoria.php?category=luxury-suvs">Luxury SUVs</a></li>
                        <li><a href="/categoria.php?category=grand-tourers">Grand Tourers (GT)</a></li>
                        <li><a href="/categoria.php?category=exclusive">Exclusive</a></li>
                        <li><a href="/categoria.php?category=limited-edition">Limited Edition</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Marcas<span class="arrow"></span></a>
                    <ul class="dropdown-content">
                        <?php if (empty($marcasDisponiveis)): ?>
                            <li><a href="javascript:void(0)">Nenhuma marca ativa</a></li>
                        <?php else: ?>
                            <?php foreach ($marcasDisponiveis as $m): ?>
                                <li><a href="/marca.php?brand=<?= urlencode(strtolower($m)) ?>"><?= htmlspecialchars($m, ENT_QUOTES) ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <li><a href="/servicos.html">Serviços</a></li>
                <li><a href="/quemnossomos.html">Quem somos</a></li>
                <li><a href="/noticias.html">Blog</a></li>
            </ul>
        </div>
    </nav>

    <header class="about-hero" style="background-image: linear-gradient(rgba(11, 11, 11, 0.7), rgba(11, 11, 11, 0.95)), url('https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <span class="category-subtitle">Marca</span>
            <h1 class="hero-title"><span><?= htmlspecialchars($brandName ?: 'Acurácia', ENT_QUOTES) ?></span> <br>Collection</h1>
        </div>
    </header>

    <main>
        <section class="category-section">
            <div class="section-header">
                <h2 class="category-title"><?= htmlspecialchars(!empty($vehicles) ? 'Modelos em Destaque de ' . $brandName : 'Marca não encontrada', ENT_QUOTES) ?></h2>
            </div>

            <div class="centered-grid">
                <?php if (!empty($errorMessage)): ?>
                    <div style="color:#fff; padding:40px; text-align:center; width:100%;">
                        <h3>Aviso</h3>
                        <p><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></p>
                        <a href="/index.php" class="btn btn-outline" style="margin-top: 20px; display: inline-block; text-decoration: none;">Voltar para a Home</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($vehicles as $carro): ?>
                        <?php $urlImagem = obterPrimeiraImagem($carro['imagens_urls'] ?? ''); ?>
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

    <!-- Forma CORRETA de carregar o JavaScript -->
    <script src="script.js"></script>
</body>
</html>
