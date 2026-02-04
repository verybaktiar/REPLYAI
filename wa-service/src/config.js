/**
 * Configuration for WhatsApp Service
 */

export const config = {
    // Port for Express server
    port: process.env.WA_SERVICE_PORT || 3001,

    // Laravel backend URL for webhooks
    laravelWebhookUrl: process.env.LARAVEL_WEBHOOK_URL || 'http://127.0.0.1:8000/api/whatsapp/webhook',

    // Session storage path
    sessionPath: './sessions',

    // Auto-reconnect settings
    reconnectInterval: 5000, // 5 seconds
    maxReconnectAttempts: Infinity, // Unlimited attempts to auto-heal connection

    // Message settings
    typingDelay: 1000, // Delay before sending (simulate typing)

    // Logging level
    logLevel: process.env.LOG_LEVEL || 'info'
};
