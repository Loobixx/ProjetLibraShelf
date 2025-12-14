/* assets/app.js */

// ==========================================
// 1. LES IMPORTS (Indispensables !)
// ==========================================

/* assets/app.js */
import './styles/app.css';

console.log('App.js chargé !');

// ==========================================
// 2. VOTRE LOGIQUE JAVASCRIPT
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');

            // On vérifie si l'input et l'icône existent bien pour éviter des erreurs
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });
});

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
