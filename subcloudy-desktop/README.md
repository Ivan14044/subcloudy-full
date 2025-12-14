# SubCloudy Desktop

Secure desktop application for accessing premium services through SubCloudy platform.

## 🛡️ Security Features

- **Isolated Browser Sessions** - Each service runs in a completely isolated session
- **DevTools Blocking** - Developer tools are completely disabled
- **Cookie Protection** - Cookies cannot be accessed or extracted
- **Watermarking** - Invisible user ID watermarks on all sessions
- **Activity Monitoring** - Automatic session timeout after 30 minutes of inactivity
- **Secure Communication** - All data is encrypted and transmitted securely

## 🚀 Development

### Prerequisites

- Node.js 18+ 
- npm or yarn
- Backend API running (Laravel)

### Setup

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Configure Backend URL**
   
   Create `.env` file in the root directory:
   ```env
   API_URL=http://127.0.0.1:8000/api
   ```

3. **Run in development mode**
   ```bash
   npm run dev
   ```

   This will:
   - Start Vite dev server for renderer (http://localhost:5175)
   - Compile and start Electron app

### Development Scripts

```bash
# Start development mode
npm run dev

# Build for production
npm run build

# Build and package for current platform
npm run dist

# Build for specific platforms
npm run dist:win   # Windows
npm run dist:mac   # macOS
npm run dist:linux # Linux
```

## 📦 Building for Production

### Windows

```bash
npm run dist:win
```

Output: `dist-electron/SubCloudy-Setup-{version}.exe`

### macOS

```bash
npm run dist:mac
```

Output: `dist-electron/SubCloudy-{version}.dmg`

### Linux

```bash
npm run dist:linux
```

Output: 
- `dist-electron/SubCloudy-{version}.AppImage`
- `dist-electron/SubCloudy-{version}.deb`

## 🏗️ Project Structure

```
subcloudy-desktop/
├── src/
│   ├── main/              # Electron Main Process
│   │   ├── index.ts       # Entry point
│   │   ├── auth.ts        # Authentication manager
│   │   ├── security.ts    # Security policies
│   │   ├── services.ts    # Service launcher
│   │   └── windows/       # Window management
│   ├── preload/           # Preload scripts (IPC bridge)
│   └── renderer/          # Vue.js frontend
│       └── src/
│           ├── pages/     # App pages
│           ├── stores/    # Pinia stores
│           └── components/# Vue components
├── resources/             # App resources (icons, etc)
└── dist/                  # Compiled files
```

## 🔐 Security Architecture

### Session Isolation

Each service runs in an isolated Electron `BrowserView` with:
- Unique session partition
- Disabled Node.js integration
- Context isolation enabled
- Sandbox mode enabled
- DevTools completely disabled

### Protection Mechanisms

1. **DevTools Prevention**
   - Disabled at BrowserView creation
   - Keyboard shortcuts blocked (F12, Ctrl+Shift+I, etc.)
   - Context menu disabled

2. **Cookie Protection**
   - JavaScript access to cookies blocked
   - Cookie Store API disabled
   - Session data cleared after use

3. **Network Monitoring**
   - Suspicious requests blocked
   - Authentication endpoints filtered
   - Extension loading prevented

4. **Activity Tracking**
   - User watermarks on all pages
   - Inactivity timeout (30 minutes)
   - Session logging for audit

## 🔌 Backend Integration

The desktop app requires these endpoints on the Laravel backend:

### Required Endpoints

```php
// Authentication
POST /api/login
POST /api/logout
GET  /api/user

// Desktop-specific
POST /api/desktop/auth           // Get desktop token
POST /api/desktop/service-url    // Get secure service URL
GET  /api/desktop/my-services    // Get user's active services
POST /api/desktop/log            // Log activity
```

### Setup Backend

1. Add `DesktopController` to Laravel backend
2. Add desktop routes to `routes/api.php`
3. Ensure CORS allows desktop app origin
4. Configure Browser API URL in `.env`

## 📝 Configuration

### Environment Variables

Create `.env` file:

```env
# Backend API URL
API_URL=http://127.0.0.1:8000/api

# Development mode
NODE_ENV=development
```

### Build Configuration

Edit `electron-builder.yml` for customizing:
- App ID and name
- Icon paths
- Target platforms
- Code signing
- Auto-update settings

## 🧪 Testing

### Manual Testing Checklist

- [ ] Login with valid credentials
- [ ] Display of available services
- [ ] Launch service in isolated view
- [ ] Verify DevTools are blocked
- [ ] Test keyboard shortcut blocking
- [ ] Check watermark visibility
- [ ] Verify session timeout
- [ ] Test logout functionality

### Security Testing

Try to bypass security:
- [ ] Open DevTools (should be impossible)
- [ ] Extract cookies via console (should fail)
- [ ] Copy page source (blocked)
- [ ] Screenshot credentials (watermarked)
- [ ] Keep session open 30+ min (auto-close)

## 📱 Distribution

### Code Signing

For production builds, configure code signing:

**Windows:**
```bash
export CSC_LINK=path/to/certificate.pfx
export CSC_KEY_PASSWORD=your_password
npm run dist:win
```

**macOS:**
```bash
export APPLE_ID=your@email.com
export APPLE_ID_PASSWORD=@keychain:AC_PASSWORD
npm run dist:mac
```

### Auto-Updates

Configure update server in `electron-builder.yml`:

```yaml
publish:
  provider: generic
  url: https://updates.subcloudy.com
```

Implement update server to serve:
- `latest.yml` (Windows/Linux)
- `latest-mac.yml` (macOS)
- Application packages

## 🐛 Troubleshooting

### App won't start

- Check if port 5175 is available
- Verify Node.js version (18+)
- Delete `node_modules` and reinstall

### Can't connect to backend

- Verify API_URL in `.env`
- Check backend is running
- Verify CORS settings
- Check network firewall

### Build fails

- Clear build cache: `rm -rf dist dist-electron`
- Update electron-builder: `npm update electron-builder`
- Check icon files exist in `resources/icons/`

## 📄 License

Copyright © 2024 SubCloudy. All rights reserved.

## 🤝 Support

For issues or questions:
- Email: support@subcloudy.com
- Telegram: @subcloudy_support


