
# SnowTricks Community Website

This project is part of the OpenClassrooms curriculum and involves building a community website for snowboard enthusiasts. The website allows users to share and discover snowboard tricks, along with videos and discussions. The project is built with PHP and Symfony.

## Features

The SnowTricks website consists of two main parts:

1. **Pages Accessible to All Visitors:**

   - A home page displaying a list of snowboard tricks. Users can view each trick's details, including the name, description, and associated media (photos and videos).
   - Trick detail pages where users can see the description, images, and videos, as well as participate in discussions.
   - Paginated trick listings to improve user experience and performance.

2. **Pages Accessible to Authenticated Users:**

   - Authenticated users can create, modify, and delete tricks.
   - Each trick includes a name, description, media (photos/videos), and an optional discussion section where users can post messages.
   - Users can embed videos from platforms like YouTube or Dailymotion and upload images.

## Technologies Used

This project uses the following technologies:

- **Backend**: PHP and Symfony framework.
- **Frontend**: Twig for templating, CSS, and JavaScript.
- **Database**: MySQL for storing user data, tricks, and discussions.
- **Media Handling**: Supports uploaded images and embedded videos.
- **Authentication**: Custom user authentication, password reset functionality.

## Installation

1. Clone the repository from GitHub:

   ```bash
   git clone https://github.com/basilepineau/oc6.git
   cd oc6
   ```

2. Install dependencies using Composer:

   ```bash
   composer install
   ```

3. Configure the environment variables:

   Copy the `.env` file and set up database and mailer settings:

   ```bash
   cp .env .env.local
   ```

4. Run the database migrations:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Load initial data fixtures:

   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. Install frontend assets:

   ```bash
   npm install
   npm run dev
   ```

7. Start the local development server:

   ```bash
   symfony server:start
   ```

## Usage

- Once the installation is complete, access the application at `http://localhost:8000`.
- Visitors can browse tricks and view their details.
- Authenticated users can add, edit, and delete tricks and participate in discussions.

## Security

The SnowTricks website follows best security practices, including protection against vulnerabilities like XSS, CSRF, SQL injection, and session hijacking. Additional measures have been implemented to ensure the security of user-uploaded media.

## Contributions

Contributions are welcome! To contribute to the project, please open an issue or submit a pull request.

## License

This project is licensed under the MIT License.
