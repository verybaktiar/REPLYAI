module.exports = {
  apps : [{
    name   : "wa-service",
    script : "./index.js",
    watch  : true,
    env: {
      LARAVEL_WEBHOOK_URL: "http://127.0.0.1:8000/api/whatsapp/webhook",
      PORT: 3001,
      WA_SERVICE_KEY: "replyai-wa-secret"
    }
  }]
}
