<?php
session_start();

if (empty($_SESSION['is_admin'])) {
    header('Location: /login.php');
    exit;
}

// Puxa a conexão do arquivo config.php
require_once __DIR__ . '/config.php';

$message = '';
$error = '';
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // Ação de Excluir
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Veículo excluído com sucesso.';
        } catch (Exception $e) {
            $error = 'Falha ao excluir o veículo. Verifique se ele ainda existe.';
        }
    }

    // Ação de Salvar (Inserir ou Atualizar)
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $preco = trim($_POST['preco'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        
        // Novos campos
        $imagens_urls = trim($_POST['imagens_urls'] ?? ''); // Múltiplas imagens (armazenadas em texto)
        $tem_piloto = isset($_POST['tem_piloto']) ? 1 : 0;
        $video_piloto_url = trim($_POST['video_piloto_url'] ?? '');
        $imagem_piloto_url = trim($_POST['imagem_piloto_url'] ?? '');

        if ($marca === '' || $modelo === '') {
            $error = 'Marca e modelo são obrigatórios.';
        } else {
            if ($id !== '') {
                $sql = "UPDATE $tabela SET 
                        marca = :marca, modelo = :modelo, categoria = :categoria, status = :status, 
                        preco = :preco, descricao = :descricao, imagens_urls = :imagens_urls, 
                        tem_piloto = :tem_piloto, video_piloto_url = :video_piloto_url, imagem_piloto_url = :imagem_piloto_url 
                        WHERE id = :id";
                $params = [
                    'marca' => $marca, 'modelo' => $modelo, 'categoria' => $categoria, 
                    'status' => $status, 'preco' => $preco, 'descricao' => $descricao, 
                    'imagens_urls' => $imagens_urls, 'tem_piloto' => $tem_piloto, 
                    'video_piloto_url' => $video_piloto_url, 'imagem_piloto_url' => $imagem_piloto_url, 
                    'id' => intval($id)
                ];
            } else {
                $sql = "INSERT INTO $tabela 
                        (marca, modelo, categoria, status, preco, descricao, imagens_urls, tem_piloto, video_piloto_url, imagem_piloto_url) 
                        VALUES (:marca, :modelo, :categoria, :status, :preco, :descricao, :imagens_urls, :tem_piloto, :video_piloto_url, :imagem_piloto_url)";
                $params = [
                    'marca' => $marca, 'modelo' => $modelo, 'categoria' => $categoria, 
                    'status' => $status, 'preco' => $preco, 'descricao' => $descricao, 
                    'imagens_urls' => $imagens_urls, 'tem_piloto' => $tem_piloto, 
                    'video_piloto_url' => $video_piloto_url, 'imagem_piloto_url' => $imagem_piloto_url
                ];
            }

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = ($id !== '' ? 'Veículo atualizado com sucesso.' : 'Veículo cadastrado com sucesso.');
            } catch (Exception $e) {
                $error = 'Erro ao salvar veículo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
            }
        }
    }
}

// Buscar todos os veículos para a tabela
$vehicles = [];
try {
    $stmt = $pdo->query("SELECT * FROM $tabela ORDER BY id DESC");
    $vehicles = $stmt->fetchAll();
} catch (Exception $e) {
    // Caso a tabela ainda não exista, evita que a página quebre totalmente
    $error = "Erro ao carregar veículos. Certifique-se de que a tabela '$tabela' existe no phpMyAdmin.";
}

// Buscar veículo específico para edição
$editing = null;
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = :id");
    $stmt->execute(['id' => intval($_GET['edit'])]);
    $editing = $stmt->fetch();
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
    <script>
        // Função simples para mostrar/ocultar campos do piloto
        function togglePilotFields() {
            var checkbox = document.getElementById('tem_piloto');
            var pilotFields = document.getElementById('pilot_fields_container');
            if (checkbox.checked) {
                pilotFields.style.display = 'block';
            } else {
                pilotFields.style.display = 'none';
            }
        }
    </script>
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
                <p>Gerencie os veículos cadastrados no banco de dados do InfinityFree.</p>
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
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione a categoria...</option>
                            <option value="Supercars" <?= ($editing['categoria'] ?? '') === 'Supercars' ? 'selected' : '' ?>>Supercars</option>
                            <option value="Hypercars" <?= ($editing['categoria'] ?? '') === 'Hypercars' ? 'selected' : '' ?>>Hypercars</option>
                            <option value="Luxury Cars" <?= ($editing['categoria'] ?? '') === 'Luxury Cars' ? 'selected' : '' ?>>Luxury Cars</option>
                            <option value="Luxury SUVs" <?= ($editing['categoria'] ?? '') === 'Luxury SUVs' ? 'selected' : '' ?>>Luxury SUVs</option>
                            <option value="Grand Tourers" <?= ($editing['categoria'] ?? '') === 'Grand Tourers' ? 'selected' : '' ?>>Grand Tourers</option>
                            <option value="Exclusive" <?= ($editing['categoria'] ?? '') === 'Exclusive' ? 'selected' : '' ?>>Exclusive</option>
                            <option value="Limited Edition" <?= ($editing['categoria'] ?? '') === 'Limited Edition' ? 'selected' : '' ?>>Limited Edition</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="status">Status</label>
                        <input type="text" id="status" name="status" value="<?= htmlspecialchars($editing['status'] ?? '', ENT_QUOTES) ?>" placeholder="Ex: Consulte, Vendido, Pronta Entrega">
                    </div>
                    <div class="form-group full-width">
                        <label for="preco">Preço</label>
                        <input type="text" id="preco" name="preco" value="<?= htmlspecialchars($editing['preco'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>URLs da Galeria de Imagens</label>
                        <div id="images-container" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 15px;">
                            <!-- Imagens serão adicionadas aqui dinamicamente -->
                        </div>
                        <button type="button" class="btn btn-outline" id="add-image-btn" style="width: 100%; margin-bottom: 10px;">+ Adicionar Imagem</button>
                        <textarea id="imagens_urls" name="imagens_urls" style="display: none;"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="6"><?= htmlspecialchars($editing['descricao'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <div class="form-group full-width" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                            <input type="checkbox" id="tem_piloto" name="tem_piloto" value="1" <?= !empty($editing['tem_piloto']) ? 'checked' : '' ?> onchange="togglePilotFields()">
                            Esta página terá um vídeo e foto de um piloto?
                        </label>
                        
                        <div id="pilot_fields_container" style="display: <?= !empty($editing['tem_piloto']) ? 'block' : 'none' ?>; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                            <div style="margin-bottom: 10px;">
                                <label for="video_piloto_url">Link do vídeo do piloto (YouTube, etc)</label>
                                <input type="url" id="video_piloto_url" name="video_piloto_url" value="<?= htmlspecialchars($editing['video_piloto_url'] ?? '', ENT_QUOTES) ?>" style="width: 100%;">
                            </div>
                            <div>
                                <label for="imagem_piloto_url">URL da imagem do piloto</label>
                                <input type="url" id="imagem_piloto_url" name="imagem_piloto_url" value="<?= htmlspecialchars($editing['imagem_piloto_url'] ?? '', ENT_QUOTES) ?>" style="width: 100%;">
                            </div>
                        </div>
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
                                <th>Piloto?</th>
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
                                    <td><?= !empty($vehicle['tem_piloto']) ? 'Sim' : 'Não' ?></td>
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

    <script>
        // Gerenciamento dinâmico de imagens
        const imagesContainer = document.getElementById('images-container');
        const imagensUrlsField = document.getElementById('imagens_urls');
        const addImageBtn = document.getElementById('add-image-btn');

        // Carregar imagens existentes ao abrir
        function loadExistingImages() {
            const imagensTexto = '<?= htmlspecialchars($editing['imagens_urls'] ?? '', ENT_QUOTES) ?>';
            if (imagensTexto.trim()) {
                const urls = imagensTexto.split('\n').filter(url => url.trim());
                urls.forEach(url => addImageField(url.trim()));
            }
            if (imagesContainer.children.length === 0) {
                addImageField('');
            }
        }

        // Adicionar campo de imagem
        function addImageField(initialValue = '') {
            const imageWrapper = document.createElement('div');
            imageWrapper.style.cssText = 'display: flex; gap: 10px; align-items: flex-start;';
            
            const input = document.createElement('input');
            input.type = 'url';
            input.placeholder = 'https://exemplo.com/imagem.jpg';
            input.value = initialValue;
            input.style.cssText = 'flex: 1;';
            input.className = 'image-url-input';
            
            const previewBtn = document.createElement('button');
            previewBtn.type = 'button';
            previewBtn.textContent = 'Preview';
            previewBtn.className = 'btn btn-outline';
            previewBtn.style.cssText = 'padding: 8px 12px; white-space: nowrap;';
            previewBtn.onclick = (e) => {
                e.preventDefault();
                if (input.value) {
                    window.open(input.value, '_blank');
                }
            };

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = 'Remover';
            removeBtn.className = 'btn btn-outline';
            removeBtn.style.cssText = 'padding: 8px 12px; background: rgba(255, 50, 50, 0.1); color: #ff3232; white-space: nowrap;';
            removeBtn.onclick = (e) => {
                e.preventDefault();
                imageWrapper.remove();
                updateImageTextarea();
            };

            imageWrapper.appendChild(input);
            imageWrapper.appendChild(previewBtn);
            imageWrapper.appendChild(removeBtn);
            imagesContainer.appendChild(imageWrapper);

            // Atualizar textarea ao digitar
            input.addEventListener('change', updateImageTextarea);
            input.addEventListener('blur', updateImageTextarea);
        }

        function updateImageTextarea() {
            const urls = [];
            document.querySelectorAll('.image-url-input').forEach(input => {
                if (input.value.trim()) {
                    urls.push(input.value.trim());
                }
            });
            imagensUrlsField.value = urls.join('\n');
        }

        // Event listeners
        addImageBtn.addEventListener('click', (e) => {
            e.preventDefault();
            addImageField();
        });

        // Inicializar ao carregar
        document.addEventListener('DOMContentLoaded', loadExistingImages);
    </script>
</body>
</html>
