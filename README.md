## Setup php config (php.ini)

file_uploads          enable <br>
max_execution_time    300 <br>
max_input_time        300 <br>
memory_limit          -1 <br>
post_max_size         800M <br>
upload_max_filesize   200M <br>

## ENV
Create .env file from .env.example

APP_NAME=Laravel
APP_ENV=production
APP_DIR=/process/rover
APP_DEBUG=false
APP_URL=http://209.97.140.80/process/rover/

## Remove cache from project

`php artisan cache:clear` <br>
`php artisan config:clear` <br>
`php artisan route:clear` <br>

## install new php extension for zip

`sudo apt-get install php8.0-zip`
`service apache2 restart`
