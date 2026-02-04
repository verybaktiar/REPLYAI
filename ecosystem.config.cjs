module.exports = {
  apps : [{
    name: "wa-service",
    cwd: "./wa-service",
    script: "index.js",
    watch: true,
    ignore_watch: ["node_modules", "logs", "sessions"],
    env: {
      NODE_ENV: "production",
    }
  }, {
    name: "laravel-queue",
    script: "artisan",
    args: "queue:work --sleep=3 --tries=3",
    interpreter: "php",
    watch: false,
  }]
}
