// Глобальный ID Telegram-бота (публичный ID, не токен!)
window.__TELEGRAM_BOT_ID__ = '8267596067';

// Патч для Telegram Login Widget: подставляем корректный bot_id
(function patchTelegramAuthBotId() {
  var intervalId = setInterval(function () {
    if (!window.Telegram || !window.Telegram.Login) {
      return;
    }

    if (window.Telegram.Login.__subcloudyPatched) {
      clearInterval(intervalId);
      return;
    }

    var originalAuth = window.Telegram.Login.auth;
    if (typeof originalAuth !== 'function') {
      return;
    }

    window.Telegram.Login.auth = function (options, callback) {
      options = options || {};
      if (!options.bot_id) {
        options.bot_id = window.__TELEGRAM_BOT_ID__ || options.bot_id;
      }
      return originalAuth.call(this, options, callback);
    };

    window.Telegram.Login.__subcloudyPatched = true;
    clearInterval(intervalId);
  }, 500);
})();
