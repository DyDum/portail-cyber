document.addEventListener('DOMContentLoaded', () => {
    const contentContainer = document.querySelector('#page-content');

    // Fonction de chargement partiel
    async function loadPage(url, addToHistory = true) {
        try {
            // Animation de chargement optionnelle
            contentContainer.classList.add('loading');
            document.querySelector('#loading-spinner').style.display = 'block';

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Erreur HTTP : ' + response.status);
            const html = await response.text();

            // Parse le HTML pour extraire seulement le contenu du <main>
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#page-content');

            if (newContent) {
                contentContainer.innerHTML = newContent.innerHTML;
                contentContainer.classList.remove('loading');

                // Met à jour l’URL dans la barre d’adresse
                if (addToHistory) history.pushState(null, '', url);

                // Scroll en haut
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (err) {
            console.error('Erreur de chargement de page :', err);
            window.location.href = url; // fallback classique
        }
    }

    // Interception des clics sur les liens internes
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        const isExternal = href.startsWith('http') || href.startsWith('mailto:') || href.startsWith('#');

        if (!isExternal && !link.hasAttribute('data-no-ajax')) {
            e.preventDefault();
            loadPage(href);
        }
    });

    // Gestion du bouton “Précédent / Suivant” du navigateur
    window.addEventListener('popstate', () => {
        loadPage(window.location.href, false);
    });
});
