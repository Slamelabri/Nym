import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        // Charger le thème sauvegardé ou utiliser le thème par défaut
        const savedTheme = localStorage.getItem('theme') || 'light'
        this.setTheme(savedTheme)
    }

    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme')
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark'
        this.setTheme(newTheme)
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme)
        localStorage.setItem('theme', theme)
        
        // Mettre à jour l'icône du bouton
        const icon = this.element.querySelector('i')
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon'
        }
    }
}