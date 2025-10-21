document.addEventListener('DOMContentLoaded', () => {

    // Clic sur les étoiles (sidebar + pages RSS/Favoris)
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.toggle-fav, .favorite-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const feedIndex = btn.dataset.feedIndex;
        const isFavorite = btn.dataset.favorite === 'true';
        const icon = btn.querySelector('i');

        try {
            const response = await fetch(`/rss/favorites/toggle/${feedIndex}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) throw new Error('HTTP error ' + response.status);
            const data = await response.json();

            if (data.status === 'success') {
                // Gestion visuelle des icônes
                if (isFavorite) {
                    btn.dataset.favorite = 'false';
                    icon.classList.remove('bi-star-fill', 'text-warning');
                    icon.classList.add('bi-star');

                } else {
                    btn.dataset.favorite = 'true';
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill', 'text-warning', 'star-animate');
                    setTimeout(() => icon.classList.remove('star-animate'), 400);
                }

                // Met à jour la sidebar
                updateSidebarFavorites(data.favorites);
                updateSidebarRSS(data.favorites);

                // Condition principale selon la page actuelle
                await refreshRssUI(data);

                if(isFavorite) {
                    showToast('Retiré des favoris', 'success');
                }else{
                    showToast('Ajouté aux favoris', 'success');
                }
            }

        } catch (err) {
            console.error('Erreur AJAX Favoris:', err);
            showToast('Erreur réseau', 'error');
        }
    });

    async function refreshRssUI(data) {
        const currentPath = window.location.pathname;

        if (currentPath === '/rss' || currentPath === '/rss/') {
            updateRSSPageFilters(data.favorites);
        } else {
            updateRSSPageFilters(data.favorites);
            await Promise.all([
                updateFavoriteFilters(currentPath),
                updateRssFeeds(currentPath)
            ]);
        }
    }

    // 🔹 Mise à jour dynamique de la sidebar
    function updateSidebarFavorites(favorites) {
        const favMenu = document.querySelector('#favorisMenu');
        const feeds = JSON.parse(document.body.dataset.rssFeeds || '[]');
        if (!favMenu) return;

        // Récupère l'URL actuelle
        const currentUrl = window.location.pathname + window.location.search;

        // Vider complètement le menu
        favMenu.innerHTML = '';

        // Si aucun favori, ne rien afficher
        if (favorites.length === 0) {
            const emptyMsg = document.createElement('div');
            emptyMsg.className = 'sidebar-item';
            emptyMsg.innerHTML = '<span class="small fst-italic">Aucun favori</span>';
            favMenu.appendChild(emptyMsg);
            return;
        }

        // Bouton "Tous les favoris"
        const allFav = document.createElement('a');
        allFav.href = '/rss/favorites';
        allFav.className = 'sidebar-item fade-in';
        allFav.innerHTML = `<i class="bi bi-circle-fill me-2"></i>Tous les favoris`;

        // Vérifie si on est sur /rss/favorites (sans paramètre)
        if (currentUrl === '/rss/favorites' || currentUrl === '/rss/favorites/') {
            allFav.classList.add('active');
        }

        favMenu.appendChild(allFav);

        // Ajouter tous les favoris
        favorites.forEach(index => {
            if (!feeds[index]) return;
            const feed = feeds[index];
            const a = document.createElement('a');
            a.href = `/rss/favorites?fav=${index}`;
            a.className = 'sidebar-item d-flex justify-content-between align-items-center fade-in';
            a.dataset.feedIndex = index;

            // Si l'URL correspond à ce favori : active
            if (currentUrl === `/rss/favorites?fav=${index}`) {
                a.classList.add('active');
            }

            a.innerHTML = `
                <span><i class="bi bi-circle me-2"></i>${feed.name}</span>
                <button class="toggle-fav" data-feed-index="${index}" data-favorite="true">
                    <i class="bi bi-star-fill text-warning"></i>
                </button>
            `;
            favMenu.appendChild(a);
        });
    }

    function updateSidebarRSS(favorites) {
        const rssMenu = document.querySelector('#rssMenu');
        if (!rssMenu) return;

        // Mettre à jour toutes les étoiles dans la section RSS
        rssMenu.querySelectorAll('.toggle-fav').forEach(btn => {
            const feedIndex = parseInt(btn.dataset.feedIndex);
            const icon = btn.querySelector('i');
            const isFav = favorites.includes(feedIndex);

            btn.dataset.favorite = isFav ? 'true' : 'false';

            if (isFav) {
                icon.classList.remove('bi-star');
                icon.classList.add('bi-star-fill', 'text-warning');
            } else {
                icon.classList.remove('bi-star-fill', 'text-warning');
                icon.classList.add('bi-star');
            }
        });
    }

    function updateRSSPageFilters(favorites) {
        document.querySelectorAll('.rss-filter-item .favorite-btn, .favorite-btn').forEach(btn => {
            const feedIndex = parseInt(btn.dataset.feedIndex);
            const icon = btn.querySelector('i');
            const isFav = favorites.includes(feedIndex);

            if (!icon) return;

            btn.dataset.favorite = isFav ? 'true' : 'false';

            if (isFav) {
                icon.classList.remove('bi-star');
                icon.classList.add('bi-star-fill', 'text-warning');
            } else {
                icon.classList.remove('bi-star-fill', 'text-warning');
                icon.classList.add('bi-star');
            }
        });
    }

    async function updateFavoriteFilters(url) {
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);

            const html = await response.text();

            // Remplace uniquement le bloc de filtres (tu peux adapter le sélecteur selon ton HTML)
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newFilters = doc.querySelector('.rss-filter-item')?.parentElement;

            if (newFilters) {
                const filterContainer = document.querySelector('.rss-filter-item')?.parentElement;
                if (filterContainer) {
                    filterContainer.replaceWith(newFilters);
                }
            }

            console.log('✅ Filtres favoris mis à jour.');
        } catch (err) {
            console.error('Erreur lors de la mise à jour des filtres favoris :', err);
        }
    }


    /**
     * Recharge les articles RSS affichés
     * @param {string} url - URL du flux RSS à charger (ex: /rss?feed=2)
     */
    async function updateRssFeeds(url) {
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);

            const html = await response.text();

            // Récupère les nouveaux articles dans le HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newArticles = doc.querySelector('.list-group');

            // Remplace le bloc des articles
            const articleContainer = document.querySelector('.list-group');
            if (articleContainer && newArticles) {
                articleContainer.replaceWith(newArticles);
            }

            console.log('✅ Flux RSS mis à jour.');
        } catch (err) {
            console.error('Erreur lors de la mise à jour des flux RSS :', err);
        }
    }
});