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

Подробные инструкции см. в:
- [Инструкция по деплою (RU)](DEPLOYMENT_INSTRUCTIONS_RU.md)
- [Deployment Guide (Full)](DEPLOYMENT_GUIDE.md)

### Краткие пути на сервере:
- **Frontend Root:** `/var/www/subcloudy/public/`
- **Backend Root:** `/var/www/subcloudy/ai-bot-main/`


