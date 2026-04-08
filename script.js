/* ==========================================================
   1. EFEITO VISUAL NA NAVBAR DURANTE O SCROLL
   ========================================================== */
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 60) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

/* ==========================================================
   2. LÓGICA DO MODAL DE DETALHES (EXPERIÊNCIA DO USUÁRIO)
   ========================================================== */
const modal = document.getElementById('carModal');
const modalTitle = document.getElementById('modalTitle');

function openModal(carName) {
    modalTitle.textContent = carName;
    modal.classList.add('show');
    // Trava o scroll da página ao abrir o modal
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modal.classList.remove('show');
    // Restaura o scroll da página
    document.body.style.overflow = 'auto';
}

// Fechar modal ao clicar na parte escura (fora da caixa de conteúdo)
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModal();
    }
});

/* ==========================================================
   3. SISTEMA DE BUSCA DINÂMICA (LIVE SEARCH)
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.search-bar input');
    const carCards = document.querySelectorAll('.card');
    const catalogGrid = document.querySelector('.grid');

    // 3.1 Criar mensagem de "Nenhum resultado" dinamicamente
    let noResultsMsg = document.createElement('div');
    noResultsMsg.id = 'no-results-feedback';
    noResultsMsg.innerHTML = `
        <div style="text-align: center; grid-column: 1 / -1; padding: 60px 20px;">
            <h3 style="font-family: var(--font-head); color: var(--primary); font-size: 1.5rem; margin-bottom: 10px;">
                NENHUM EXEMPLAR ENCONTRADO
            </h3>
            <p style="color: #888; font-weight: 300;">Tente buscar por outra marca ou modelo do nosso acervo.</p>
        </div>
    `;
    noResultsMsg.style.display = 'none';
    
    if (catalogGrid) {
        catalogGrid.appendChild(noResultsMsg);
    }

    // 3.2 Lógica de Filtro
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            let hasResults = false;

            carCards.forEach(card => {
                // Captura o nome do carro dentro do h3 do card
                const carName = card.querySelector('h3').textContent.toLowerCase();
                
                // Verifica se o termo pesquisado está contido no nome/marca
                if (carName.includes(searchTerm)) {
                    card.style.display = 'block'; // Mostra o card
                    
                    // Pequeno delay para disparar a animação de fade-in
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 10);
                    
                    hasResults = true;
                } else {
                    // Esconde o card com efeito visual
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300); // Tempo da transição CSS
                }
            });

            // 3.3 Exibir/Ocultar feedback de "Nada encontrado"
            if (!hasResults && searchTerm !== "") {
                noResultsMsg.style.display = 'block';
                noResultsMsg.style.opacity = '1';
            } else {
                noResultsMsg.style.display = 'none';
            }
        });
    }
});

/* ==========================================================
   4. ANIMAÇÕES AO ROLAR A PÁGINA (INTERSECTION OBSERVER)
   ========================================================== */
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.15
};

const observerCallback = (entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
            observer.unobserve(entry.target);
        }
    });
};

const scrollObserver = new IntersectionObserver(observerCallback, observerOptions);

document.addEventListener('DOMContentLoaded', () => {
    const elementsToAnimate = document.querySelectorAll('.animate-on-scroll');
    
    elementsToAnimate.forEach(element => {
        scrollObserver.observe(element);
    });
});

/* ==========================================================
   5. CARROSSEL AUTOMÁTICO DE DETALHES DO CARRO
   ========================================================== */
let carImages = [];
let currentImageIndex = 0;

// O array é preenchido no carregamento do DOM extraindo as fotos das miniaturas
document.addEventListener('DOMContentLoaded', () => {
    const thumbs = document.querySelectorAll('.thumb-img');
    if (thumbs.length > 0) {
        thumbs.forEach(thumb => {
            // Substitui 'w=400' da miniatura por 'w=1920' para a imagem grande do banner
            let highResUrl = thumb.src.replace('w=400', 'w=1920');
            carImages.push(highResUrl);
        });
    }
});

function updateCarousel(index) {
    const mainImage = document.getElementById('main-car-img');
    const thumbs = document.querySelectorAll('.thumb-img');
    
    // Proteção para rodar apenas se o elemento existir na página
    if (!mainImage || carImages.length === 0) return;

    // Efeito sutil de fade out
    mainImage.style.opacity = '0.3';
    
    // Aguarda a transição de CSS antes de trocar a imagem
    setTimeout(() => {
        currentImageIndex = index;
        mainImage.src = carImages[currentImageIndex];
        
        // Efeito de fade in
        mainImage.style.opacity = '1';
        
        // Atualiza o destaque vermelho na miniatura ativa
        thumbs.forEach((thumb, i) => {
            if (i === currentImageIndex) {
                thumb.classList.add('active');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('active');
            }
        });
    }, 250);
}

function nextImage() {
    if (carImages.length === 0) return;
    let newIndex = currentImageIndex + 1;
    if (newIndex >= carImages.length) {
        newIndex = 0;
    }
    updateCarousel(newIndex);
}

function prevImage() {
    if (carImages.length === 0) return;
    let newIndex = currentImageIndex - 1;
    if (newIndex < 0) {
        newIndex = carImages.length - 1;
    }
    updateCarousel(newIndex);
}

function goToImage(index) {
    if (index !== currentImageIndex && carImages.length > 0) {
        updateCarousel(index);
    }
}

/* ==========================================================
   6. LÓGICA GLOBAL DO MODAL DE SOLICITAÇÃO DE AQUISIÇÃO
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
    
    // 6.1 Função para Injetar o Modal no HTML dinamicamente (Evita duplicação)
    function injectGlobalModal() {
        // Se o modal já existir na página, não faz nada
        if (document.getElementById('globalAcquisitionModal')) return;

        const modalHTML = `
            <div class="modal" id="globalAcquisitionModal">
                <div class="modal-content form-modal-content">
                    <button class="close-modal" id="closeGlobalAcquisition">&times;</button>
                    
                    <div id="globalAcqFormStage">
                        <h2 class="category-title" style="font-size: 1.8rem; margin-bottom: 10px;">Solicitar Aquisição</h2>
                        <p style="margin-bottom: 25px; color: #888; font-weight: 300;">Preencha seus dados para que um de nossos consultores entre em contato com exclusividade.</p>
                        
                        <form id="globalPurchaseForm">
                            <div class="form-group">
                                <label for="gAcqName">Nome Completo</label>
                                <input type="text" id="gAcqName" required placeholder="Ex: Enzo Ferrari">
                            </div>
                            <div class="form-group">
                                <label for="gAcqPhone">Telefone / WhatsApp</label>
                                <input type="tel" id="gAcqPhone" required placeholder="(11) 99999-9999">
                            </div>
                            <div class="form-group">
                                <label for="gAcqEmail">E-mail</label>
                                <input type="email" id="gAcqEmail" required placeholder="seu@email.com">
                            </div>
                            <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Enviar Solicitação</button>
                        </form>
                    </div>

                    <div id="globalAcqSuccessStage" style="display: none; padding: 30px 0; text-align: center;">
                        <div class="success-icon">✓</div>
                        <h2 class="category-title" style="font-size: 1.8rem; margin-bottom: 15px;">Solicitação Enviada!</h2>
                        <p style="margin-bottom: 30px; color: var(--text-color); font-weight: 300;">Obrigado pelo seu interesse! Nossa equipe entrará em contato com você em algumas horas.</p>
                        <button class="btn btn-outline" id="btnGlobalCloseSuccess">Fechar</button>
                    </div>
                </div>
            </div>
        `;
        
        // Insere o modal no final do body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Executa a injeção ao carregar a página
    injectGlobalModal();

    const globalModal = document.getElementById('globalAcquisitionModal');
    const globalFormStage = document.getElementById('globalAcqFormStage');
    const globalSuccessStage = document.getElementById('globalAcqSuccessStage');
    const globalForm = document.getElementById('globalPurchaseForm');

    // 6.2 Delegação de Eventos para cliques em toda a página
    document.addEventListener('click', (e) => {
        
        // ABRIR MODAL: Verifica se clicou em um botão "Solicitar aquisição" ou com classe ".btn-solicitar"
        const isAcquisitionBtn = e.target.tagName === 'BUTTON' && 
                                (e.target.classList.contains('btn-solicitar') || 
                                 e.target.innerText.trim().toUpperCase() === 'SOLICITAR AQUISIÇÃO');
        
        if (isAcquisitionBtn) {
            globalModal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Trava o scroll
        }

        // FECHAR MODAL: Clique no X, botão de fechar ou fundo escuro
        if (e.target.id === 'closeGlobalAcquisition' || e.target.id === 'btnGlobalCloseSuccess' || e.target === globalModal) {
            globalModal.classList.remove('show');
            document.body.style.overflow = 'auto'; // Destrava o scroll
            
            // Reseta o modal silenciosamente após a animação
            setTimeout(() => {
                globalFormStage.style.display = 'block';
                globalSuccessStage.style.display = 'none';
                if (globalForm) globalForm.reset();
            }, 300);
        }
    });

    // 6.3 Gerenciar o Envio do Formulário Injetado
    document.addEventListener('submit', (e) => {
        if (e.target.id === 'globalPurchaseForm') {
            e.preventDefault(); // Impede recarregamento
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerText;
            
            // Feedback de carregamento
            submitBtn.innerText = 'Processando...';
            submitBtn.style.opacity = '0.7';
            submitBtn.disabled = true;

            // Simula tempo de resposta do servidor
            setTimeout(() => {
                globalFormStage.style.display = 'none';
                globalSuccessStage.style.display = 'block';
                
                // Restaura o botão para uso futuro
                submitBtn.innerText = originalText;
                submitBtn.style.opacity = '1';
                submitBtn.disabled = false;
            }, 1200);
        }
    });
});