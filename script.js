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
    const catalog = document.getElementById('catalog');
    
    if (!searchInput) return;

    // Cria a mensagem de "Nada encontrado" globalmente se não existir
    let noResultsMsg = document.getElementById('global-no-results');
    if (!noResultsMsg && catalog) {
        noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'global-no-results';
        noResultsMsg.style.display = 'none';
        noResultsMsg.style.textAlign = 'center';
        noResultsMsg.style.padding = '60px 20px';
        noResultsMsg.style.width = '100%';
        noResultsMsg.innerHTML = `
            <h3 style="color: var(--primary); font-family: var(--font-head); font-size: 1.5rem; margin-bottom: 10px;">NENHUM VEÍCULO ENCONTRADO</h3>
            <p style="color: #888; font-weight: 300;">Tente buscar por outra marca ou modelo do nosso acervo.</p>
        `;
        catalog.appendChild(noResultsMsg);
    }

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        let totalVisibleCards = 0;

        const allSections = document.querySelectorAll('.category-section');

        allSections.forEach(section => {
            // Pega todos os cards de carro dentro da seção atual
            const cards = section.querySelectorAll('.card');
            
            // 1. Se a barra de pesquisa estiver vazia, restaura o layout original limpando o CSS inline
            if (searchTerm === '') {
                section.style.display = ''; 
                cards.forEach(card => {
                    card.style.display = '';
                    card.style.opacity = '1';
                });
                return; // Pula para a próxima seção
            }

            // 2. Se o usuário digitou algo na busca
            let visibleCardsInSection = 0;

            if (cards.length > 0) {
                // A seção tem carros, vamos aplicar o filtro
                cards.forEach(card => {
                    const titleElement = card.querySelector('h3');
                    if (titleElement) {
                        const carName = titleElement.textContent.toLowerCase();
                        
                        if (carName.includes(searchTerm)) {
                            card.style.display = ''; // Mostra o card
                            card.style.opacity = '1';
                            visibleCardsInSection++;
                            totalVisibleCards++;
                        } else {
                            card.style.display = 'none'; // Esconde o card
                        }
                    }
                });

                // Se nenhum carro desta seção bateu com a busca, esconde a seção inteira
                section.style.display = visibleCardsInSection === 0 ? 'none' : '';
                
            } else {
                // A seção NÃO tem carros (ex: categoria vazia, seção de depoimentos, etc)
                // Esconde imediatamente enquanto o usuário estiver buscando
                section.style.display = 'none';
            }
        });

        // 3. Exibe ou oculta a mensagem global de "Nada encontrado"
        if (searchTerm !== '' && totalVisibleCards === 0) {
            if (noResultsMsg) noResultsMsg.style.display = 'block';
        } else {
            if (noResultsMsg) noResultsMsg.style.display = 'none';
        }
    });
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
/* ==========================================================
   MENU HAMBÚRGUER (MOBILE) - INJEÇÃO AUTOMÁTICA
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('navbar');
    
    // 1. Injeta o ícone Hambúrguer no HTML automaticamente
    if (nav && !document.getElementById('hamburger')) {
        nav.insertAdjacentHTML('beforeend', `
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `);
    }

    // 2. Lógica de Abrir/Fechar o Menu Principal
    const hamburger = document.getElementById('hamburger');
    const navCenter = document.querySelector('.navbar-center');
    const navRight = document.querySelector('.navbar-right');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            if (navCenter) navCenter.classList.toggle('active');
            if (navRight) navRight.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }

    // 3. Lógica para expandir as abas (Categorias e Marcas) no Mobile
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const dropbtn = dropdown.querySelector('.dropbtn');
        if (dropbtn) {
            dropbtn.addEventListener('click', (e) => {
                // Apenas executa no mobile
                if (window.innerWidth <= 992) {
                    e.preventDefault(); // Impede do link recarregar a página
                    dropdown.classList.toggle('active');
                }
            });
        }
    });
});
/* ==========================================================
   LÓGICA DO MENU MOBILE DEFINITIVO
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('navbar');
    
    // 1. Injeta o Hambúrguer com segurança dentro da Nav
    if (nav && !document.getElementById('hamburger')) {
        nav.insertAdjacentHTML('beforeend', `
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `);
    }

    const hamburger = document.getElementById('hamburger');
    const navCenter = document.querySelector('.navbar-center');

    // 2. Abre e fecha o menu lateral
    if (hamburger && navCenter) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburger.classList.toggle('active');
            navCenter.classList.toggle('active');
        });
    }

    // 3. Sistema de sanfona para os Dropdowns (Categorias e Marcas)
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        // Encontra o link principal dentro do dropdown (ex: Categorias)
        const link = dropdown.querySelector('.dropbtn, a'); 
        
        if (link) {
            link.addEventListener('click', (e) => {
                if (window.innerWidth <= 992) {
                    e.preventDefault(); // Impede o clique de recarregar a página
                    e.stopPropagation();
                    
                    // Alterna o estado do dropdown atual
                    dropdown.classList.toggle('active');
                }
            });
        }
    });

    // 4. Fecha o menu se o utilizador clicar fora dele
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992 && navCenter && hamburger) {
            if (!navCenter.contains(e.target) && !hamburger.contains(e.target)) {
                hamburger.classList.remove('active');
                navCenter.classList.remove('active');
            }
        }
    });
});
