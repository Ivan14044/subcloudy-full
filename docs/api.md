# API Documentation

## Support System

### 1. Get or Create Ticket
`MATCH /api/support/ticket`
*   **Params**: `email` (string, optional), `source` (string, optional: 'web', 'telegram')
*   **Response**: `Ticket` object with messages.

### 2. Send Message
`POST /api/support/ticket/{id}/message`
*   **Body**: `text` (string), `image` (file, optional), `source` (string)

### 3. Polling Messages
`GET /api/support/ticket/{id}/messages`
*   **Params**: `last_message_id` (int, optional)

## Authentication

*   `POST /api/register`
*   `POST /api/login`
*   `GET /api/user` (requires Sanctum token)

