# Symfony Vitrine 🚀

Un template **Symfony + Twig + TailwindCSS** pour créer rapidement des sites vitrines modernes, scalables et accessibles.

![License: Apache 2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)

---

## ✨ Fonctionnalités
- ⚡️ Basé sur **Symfony 7**
- 🎨 Styles avec **TailwindCSS v4**
- 📦 Compilation via **Webpack Encore**
- 🧩 Structure modulaire avec **Twig Components & Partials**
- 🚀 Intégration **Symfony UX** (Stimulus, Turbo, Twig Components)
- 📈 Optimisé pour la performance et le SEO
- ♿ Accessibilité mise en avant
- 🔒 Sécurité et bonnes pratiques intégrées (`.env`, `.gitignore`, etc.)

---

## 📂 Structure du projet
```bash
    .
    ├── assets/             # JS, CSS, Stimulus controllers
    ├── config/             # Config Symfony
    ├── public/             # Fichiers publics (index.php, assets compilés)
    ├── src/                # Code PHP (Controllers, Entities, Components)
    ├── templates/          # Vues Twig (components + partials)
    ├── translations/       # Traductions
    └── ...
```

---

## 🚀 Installation
1. Cloner le dépôt :
```bash
    git clone https://github.com/Aleks-DC/Symfony_Vitrine.git
    cd Symfony_Vitrine
```

2. Installer les dépendances :
```bash
    composer install
    npm install
```

3. Compiler les assets :
```bash
    npm run dev
```

4. Lancer le serveur Symfony :
```bash
    symfony serve
```

---

## 🛠️ Développement
1. Créer un composant Twig :
```bash
    symfony console make:twig-component NomDuComposant
```

2. Compiler Tailwind en mode watch :
```bash
    npm run watch
```

3. Compiler pour la prod :
```bash
    npm run build
```

---

## 👤 Auteur
Développé par [Aleks-DC](https://github.com/Aleks-DC)

---

## 📜 Licence
Distribué sous licence **Apache 2.0**.  
Voir le fichier [LICENSE](./LICENSE) pour plus de détails.

---




