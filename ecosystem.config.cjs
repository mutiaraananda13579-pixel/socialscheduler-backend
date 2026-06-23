module.exports = {
  apps: [
    {
      name: 'socialscheduler-api',
      script: 'artisan',
      args: 'serve --host=0.0.0.0 --port=8000',
      interpreter: 'C:/xampp/php/php.exe',
      cwd: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend',
      autorestart: true,
      watch: false,
      log_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/api.log',
      error_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/api-error.log',
      out_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/api-out.log',
      time: true,
      env: {
        NODE_ENV: 'production'
      }
    },
    {
      name: 'socialscheduler-cron',
      script: 'artisan',
      args: 'schedule:work',
      interpreter: 'C:/xampp/php/php.exe',
      cwd: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend',
      autorestart: true,
      watch: false,
      log_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/pm2.log',
      error_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/pm2-error.log',
      out_file: 'C:/Users/Hype/Documents/project/SocialScheduler/SocialScheduler/backend/storage/logs/pm2-out.log',
      time: true,
      env: {
        NODE_ENV: 'production'
      }
    }
  ]
};