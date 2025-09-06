# Symfony Vitrine 🚀

Un template **Symfony + Twig + TailwindCSS** pour créer rapidement des sites vitrines modernes, scalables et accessibles.

![License: Commercial](https://img.shields.io/badge/License-Commercial-blue.svg)
![Starter License](https://img.shields.io/badge/License-Starter-green.svg)
![Pro License](https://img.shields.io/badge/License-Pro-orange.svg)
![Agency License](https://img.shields.io/badge/License-Agency-purple.svg)

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
Distribué sous **licence commerciale**.  
Consultez le fichier [LICENSE.md](./LICENSE.md) pour les conditions d’utilisation (Starter, Pro, Agency).

---

## 📊 License Comparison Table

| Feature                     | ![Starter](https://img.shields.io/badge/Starter-green.svg) | ![Pro](https://img.shields.io/badge/Pro-orange.svg) | ![Agency](https://img.shields.io/badge/Agency-purple.svg) |
|-----------------------------|------------------------------------------------------------|-----------------------------------------------------|----------------------------------------------------------|
| **Usage scope**             | 1 project   | Unlimited projects | Unlimited projects |
| **Target users**            | Individuals | Freelancers / small studios | Agencies / teams |
| **Code modification**       | ✔ Allowed   | ✔ Allowed | ✔ Allowed |
| **Internal team sharing**   | ✖ Not allowed | ✖ Not allowed | ✔ Allowed |
| **Redistribution / resale** | ✖ Forbidden | ✖ Forbidden | ✖ Forbidden |
| **Support**                 | Basic       | Priority email | Advanced + Early access |
| **Updates**                 | 12 months   | 12 months | 12 months |
| **Best for**                | Personal / single site | Devs serving multiple clients | Agencies building at scale |


---




