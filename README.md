# Subcloudy Project Documentation

## Architecture Overview

Subcloudy is a multi-platform ecosystem for service management and browser-based automation.

### Components

1.  **Backend (`ai-bot-main`)**:
    *   **Framework**: Laravel 10+
    *   **Database**: MySQL
    *   **Key Features**: User management, Subscription handling, Support Ticket System, API for Frontend and Desktop apps.
    *   **Architecture**: Controller -> Service -> Model (Clean Architecture).

2.  **Frontend (`ai-bot-front-main`)**:
    *   **Framework**: Vue 3 + Vite
    *   **State Management**: Pinia (TypeScript)
    *   **Styling**: Tailwind CSS
    *   **Key Features**: Modern UI/UX, Multi-language support, Real-time support chat.

3.  **Desktop App (`subcloudy-desktop`)**:
    *   **Framework**: Electron + Vite
    *   **Frontend**: Shared Vue 3 components
    *   **Key Features**: Native system interactions, Secure service access.

## Project Structure

```text
.
├── ai-bot-main/           # Laravel Backend
├── ai-bot-front-main/     # Vue 3 Frontend
├── subcloudy-desktop/     # Electron Application
├── docs/                  # Detailed Documentation
└── README.md              # Main Entry Point
```

## Deployment

### Backend
1. `cd ai-bot-main`
2. `composer install`
3. `php artisan migrate`
4. `php artisan serve`

### Frontend
1. `cd ai-bot-front-main`
2. `npm install`
3. `npm run dev` (for development)
4. `npm run build` (for production)

### Desktop
1. `cd subcloudy-desktop`
2. `npm install`
3. `npm run dev`

