# 🛒 SymfoPop - Mercat de Segona Mà

SymfoPop és una aplicació web de mercat de segona mà construïda amb **Symfony 7**, **Doctrine ORM**, **Twig** i **Bootstrap 5**.

## 🚀 Característiques
- **Autenticació completa**: Registre, login (amb "Remember Me") i logout.
- **Catàleg de productes**: Llistat públic amb imatges generades automàticament (via Picsum).
- **CRUD de productes**: Crea, edita i elimina els teus propis productes amb validacions.
- **Seguretat**: Restricció d'accés segons el propietari de l'article i protecció CSRF.
- **Disseny responsive**: Interfície intuïtiva i adaptada a dispositius mòbils.

## 🛠️ Instal·lació

1. **Clonar el repositori**:
   ```bash
   git clone https://github.com/paunieto23/Symfony-Project.git
   cd Symfony-Project
   ```

2. **Instal·lar dependencies**:
   ```bash
   composer install
   ```

3. **Configurar la base de dades**:
   Edita el fitxer `.env` i configura la teva connexió MySQL:
   ```env
   DATABASE_URL="mysql://USUARI:PASSWORD@127.0.0.1:3306/symfopop?serverVersion=8.0&charset=utf8mb4"
   ```

4. **Crear esquema i carregar dades**:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:schema:update --force
   php bin/console doctrine:fixtures:load --no-interaction
   ```

5. **Iniciar el servidor**:
   ```bash
   symfony serve
   # o bé
   php -S localhost:8000 -t public
   ```

## 🎥 Vídeo Demostratiu
Trobaràs el guió detallat per a la presentació al fitxer `gui_video_complet.md`.

---
*Projecte desenvolupat com a part del mòdul de Servidor.*
