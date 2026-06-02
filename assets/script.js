// assets/script.js

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. CHANGEMENT DE THÈME
    const themeBtn = document.getElementById("btn-theme");
    if (themeBtn) {
        themeBtn.addEventListener("click", () => {
            let themeLink = document.getElementById("theme-style");
            let newTheme = "sombre";
            if (themeLink) {
                themeLink.remove();
                newTheme = "clair";
            } else {
                themeLink = document.createElement("link");
                themeLink.id = "theme-style";
                themeLink.rel = "stylesheet";
                themeLink.href = "assets/dark-mode.css";
                document.head.appendChild(themeLink);
            }
            document.cookie = "theme=" + newTheme + "; path=/; max-age=" + (30*24*60*60);
        });
    }

    // 2. VALIDATION DES FORMULAIRES
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function (e) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }

    const inputsWithCounter = document.querySelectorAll('.count-chars');
    inputsWithCounter.forEach(input => {
        input.addEventListener('input', function() {
            const counterSpan = document.getElementById('counter-' + this.id);
            if(counterSpan) {
                counterSpan.textContent = this.value.length + ' / ' + this.maxLength;
                if (this.value.length >= this.maxLength - 2) counterSpan.style.color = '#e74c3c';
                else counterSpan.style.color = '#7f8c8d';
            }
        });
    });

    // 3. MODIFICATION DU PROFIL
    const btnEditProfil = document.getElementById('btn-edit-profil');
    const formProfil = document.getElementById('form-profil');
    const profilActions = document.getElementById('profil-actions');
    const profilMsg = document.getElementById('profil-msg');

    if (btnEditProfil && formProfil) {
        const inputs = formProfil.querySelectorAll('input');
        btnEditProfil.addEventListener('click', () => {
            inputs.forEach(input => {
                input.removeAttribute('readonly'); 
                input.style.border = "1px solid #3498db";
            });
            profilActions.style.display = 'block';
            btnEditProfil.style.display = 'none';
        });

        formProfil.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const data = {
                nom: document.getElementById('profil-nom').value,
                prenom: document.getElementById('profil-prenom').value,
                email: document.getElementById('profil-email').value,
                telephone: document.getElementById('profil-tel').value,
                adresse: document.getElementById('profil-adresse').value
            };

            fetch('update_profil.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    profilMsg.style.color = '#27ae60';
                    profilMsg.textContent = '✅ ' + result.message;
                    inputs.forEach(input => {
                        input.setAttribute('readonly', true);
                        input.style.border = "1px solid #ccc";
                    });
                    setTimeout(() => {
                        profilActions.style.display = 'none';
                        btnEditProfil.style.display = 'block';
                        profilMsg.textContent = '';
                    }, 3000);
                } else {
                    profilMsg.style.color = '#e74c3c';
                    profilMsg.textContent = '❌ ' + result.message;
                }
            });
        });
    }

    // 4. FILTRES ET TRIS SUR LA CARTE
    const filterBtns = document.querySelectorAll('.btn-filter');
    const menuGrid = document.getElementById('menu-grid');

    if (filterBtns.length > 0 && menuGrid) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => b.style.backgroundColor = '#34495e');
                e.target.style.backgroundColor = '#e74c3c';
                const categorie = e.target.getAttribute('data-categorie');
                
                fetch(`api_plats.php?categorie=${categorie}`)
                    .then(res => res.json())
                    .then(plats => {
                        menuGrid.innerHTML = '';
                        if (plats.length === 0) {
                            menuGrid.innerHTML = '<p style="text-align: center; width: 100%; color: #7f8c8d; font-size: 1.2em;">Aucun plat trouvé.</p>';
                            return;
                        }
                        plats.forEach(plat => {
                            const prixFormate = parseFloat(plat.prix).toFixed(2);
                            let cat = plat.categorie ? plat.categorie.toLowerCase() : (plat.catégorie ? plat.catégorie.toLowerCase() : '');
                            let bgClass = 'bg-rouge';
                            if (cat.includes('boisson')) bgClass = 'bg-vert';
                            else if (cat.includes('entree') || cat.includes('entrée')) bgClass = 'bg-orange';
                            else if (cat.includes('dessert')) bgClass = 'bg-violet';

                            const carte = document.createElement('div');
                            carte.className = 'menu-card plat-card';
                            carte.setAttribute('data-prix', plat.prix); 
                            let imageHTML = plat.image ? `<img src="${plat.image}" alt="${plat.nom}" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">` : '';

                            carte.innerHTML = `
                                ${imageHTML}
                                <div class="menu-flag">${plat.pays || ''}</div>
                                <div class="menu-title ${bgClass}">${plat.nom}</div>
                                <div class="menu-items"><p>${plat.description || ''}</p></div>
                                <div class="menu-footer">
                                    <span class="menu-price">${prixFormate}€</span>
                                    <a href="panier.php?ajouter=${plat.id}" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                                </div>
                            `;
                            menuGrid.appendChild(carte);
                        });
                        document.getElementById('sort-plats').value = 'defaut';
                    });
            });
        });

        const sortSelect = document.getElementById('sort-plats');
        if(sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                const sortBy = e.target.value;
                const cards = Array.from(menuGrid.children);
                if(cards[0] && cards[0].classList.contains('plat-card')) {
                    cards.sort((a, b) => {
                        const prixA = parseFloat(a.getAttribute('data-prix'));
                        const prixB = parseFloat(b.getAttribute('data-prix'));
                        if (sortBy === 'prix-asc') return prixA - prixB;
                        if (sortBy === 'prix-desc') return prixB - prixA;
                        return 0; 
                    });
                    menuGrid.innerHTML = '';
                    cards.forEach(card => menuGrid.appendChild(card));
                }
            });
        }
    }

    // 5. BARRE DE RECHERCHE CORRIGÉE 🔍
    const searchInput = document.getElementById('search-input');

    if (searchInput) {
        if (!menuGrid) {
            const searchBtn = searchInput.nextElementSibling; 
            
            function lancerRedirection() {
                if(searchInput.value.trim() !== '') {
                    window.location.href = `presentation.php?search=${encodeURIComponent(searchInput.value)}`;
                }
            }

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    lancerRedirection();
                }
            });

            if (searchBtn && searchBtn.tagName === 'BUTTON') {
                searchBtn.addEventListener('click', lancerRedirection);
            }
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            const searchURL = urlParams.get('search');

            if (searchURL) {
                searchInput.value = searchURL;
                lancerRecherche(searchURL);
            }

            searchInput.addEventListener('input', (e) => {
                lancerRecherche(e.target.value);
            });

            function lancerRecherche(terme) {
                fetch(`api_plats.php?search=${encodeURIComponent(terme)}`)
                    .then(res => res.json())
                    .then(plats => {
                        menuGrid.innerHTML = ''; 
                        
                        if (plats.length === 0) {
                            menuGrid.innerHTML = '<p style="text-align: center; width: 100%; color: #7f8c8d; font-size: 1.2em;">Aucun plat ne correspond à votre recherche.</p>';
                            return;
                        }

                        plats.forEach(plat => {
                            const prixFormate = parseFloat(plat.prix).toFixed(2);
                            let cat = plat.categorie ? plat.categorie.toLowerCase() : (plat.catégorie ? plat.catégorie.toLowerCase() : '');
                            let bgClass = 'bg-rouge';
                            if (cat.includes('boisson')) bgClass = 'bg-vert';
                            else if (cat.includes('entree') || cat.includes('entrée')) bgClass = 'bg-orange';
                            else if (cat.includes('dessert')) bgClass = 'bg-violet';

                            const carte = document.createElement('div');
                            carte.className = 'menu-card plat-card';
                            carte.setAttribute('data-prix', plat.prix); 
                            
                            let imageHTML = plat.image ? `<img src="${plat.image}" alt="${plat.nom}" style="width:100%; height:200px; object-fit:cover; border-radius: 10px 10px 0 0;">` : '';

                            carte.innerHTML = `
                                ${imageHTML}
                                <div class="menu-flag">${plat.pays || ''}</div>
                                <div class="menu-title ${bgClass}">${plat.nom}</div>
                                <div class="menu-items"><p>${plat.description || ''}</p></div>
                                <div class="menu-footer">
                                    <span class="menu-price">${prixFormate}€</span>
                                    <a href="panier.php?ajouter=${plat.id}" class="btn-commander" style="text-decoration: none; text-align: center; display: block; box-sizing: border-box;">Ajouter</a>
                                </div>
                            `;
                            menuGrid.appendChild(carte);
                        });
                        
                        const sortSelect = document.getElementById('sort-plats');
                        if(sortSelect) sortSelect.value = 'defaut';
                    });
            }
        }
    }

}); // FIN DOMContentLoaded

// --------------------------------------------------------
// FONCTIONS STAFF & NOTATION
// --------------------------------------------------------
function changerStatut(email, index, nouveauStatut) {
    fetch('api_commande.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email_client: email, index_cmd: index, nouveau_statut: nouveauStatut })
    })
    .then(res => res.json())
    .then(data => { if(data.success) location.reload(); });
}

function assignerLivreur(email, index, livreurId) {
    if(!livreurId) return;
    fetch('api_commande.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email_client: email, index_cmd: index, nouveau_statut: 'En cours de livraison', livreur_id: livreurId })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) { alert("Commande assignée au livreur !"); location.reload(); }
    });
}

function noterCommande(index) {
    let note = prompt("Veuillez noter votre commande de 1 à 5 étoiles (1 = Mauvais, 5 = Excellent) :");
    if (note === null || note === "") return;
    note = parseInt(note);
    if (isNaN(note) || note < 1 || note > 5) {
        alert("La note doit être un chiffre compris entre 1 et 5.");
        return;
    }
    fetch('api_notation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index_cmd: index, note: note })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) location.reload(); 
        else alert(data.message);
    });
} 

function bloquerUtilisateur(userId) {
    if(confirm("Voulez-vous vraiment changer le statut d'accès de cet utilisateur ?")) {
        fetch('api_bloquer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) location.reload();
            else alert(data.message);
        });
    }
}

function supprimerUtilisateur(userId) {
    if(confirm("ATTENTION : Cette action est irréversible. Supprimer définitivement cet utilisateur ?")) {
        fetch('api_supprimer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("Utilisateur supprimé avec succès.");
                location.reload(); 
            } else {
                alert(data.message);
            }
        });
    }
}

// --------------------------------------------------------
// ATTRIBUTION DE REMISE PAR L'ADMIN (PHASE 3)
// --------------------------------------------------------
function changerRemise(userId, valeurRemise) {
    fetch('api_remise.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, remise: valeurRemise })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert("Remise de " + valeurRemise + "% appliquée au client !");
        } else {
            alert("Erreur lors de l'application de la remise.");
        }
    });
}