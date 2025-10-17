function showToast(message, type = 'success', duration = 3000) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const config = {
        success: { icon: 'bi-check-circle-fill', title: 'Succès' },
        error: { icon: 'bi-x-circle-fill', title: 'Erreur' },
        warning: { icon: 'bi-exclamation-triangle-fill', title: 'Attention' },
        info: { icon: 'bi-info-circle-fill', title: 'Information' }
    };
    
    const toastConfig = config[type] || config.info;
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="bi ${toastConfig.icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${toastConfig.title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Fermer">
            <i class="bi bi-x"></i>
        </button>
        <div class="toast-progress"></div>
    `;
    
    container.appendChild(toast);
    
    // Variable pour stocker le timeout
    let autoCloseTimeout = null;
    
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
    });
    
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
        removeToast(toast);
    });
    
    // Fonction pour démarrer l'auto-fermeture
    function startAutoClose() {
        autoCloseTimeout = setTimeout(() => removeToast(toast), duration);
    }
    
    // Démarrer l'auto-fermeture
    startAutoClose();
    
    // Pause au survol
    toast.addEventListener('mouseenter', () => {
        if (autoCloseTimeout) {
            clearTimeout(autoCloseTimeout);
            autoCloseTimeout = null;
        }
        const progressBar = toast.querySelector('.toast-progress');
        if (progressBar) {
            progressBar.style.animationPlayState = 'paused';
        }
    });
    
    // Reprendre au survol
    toast.addEventListener('mouseleave', () => {
        const progressBar = toast.querySelector('.toast-progress');
        if (progressBar) {
            progressBar.style.animationPlayState = 'running';
        }
        // Relancer avec le temps restant (1 seconde)
        autoCloseTimeout = setTimeout(() => removeToast(toast), 1000);
    });
}

function removeToast(toast) {
    toast.classList.remove('show');
    toast.classList.add('hide');

    setTimeout(() => {
        if (toast.parentNode) toast.remove();
    }, 400);
}