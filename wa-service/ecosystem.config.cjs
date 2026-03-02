module.exports = {
  apps : [{
    name   : "wa-service",
    script : "./index.js",
    watch  : true,
    env: {
      LARAVEL_WEBHOOK_URL: "http://replai.my.id/api/whatsapp/webhook",
      PORT: 3001,
      WA_SERVICE_KEY: "replyai-wa-secret"
    }
  }]
}
