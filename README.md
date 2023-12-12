<p align="center"><a href='https://postimg.cc/dkm3tsZP' target='_blank'><a href='https://postimages.org/' target='_blank'><img src='https://i.postimg.cc/RFmrHLtV/oliges-logo-1.png' border='0' alt='oliges-logo-1' width=300/></a></p>
<img src='https://badgen.net/github/release/toskabnk/OligesAPI'/><img src='https://badgen.net/github/tag/toskabnk/OligesAPI'/><img src='https://badgen.net/github/open-issues/toskabnk/OligesAPI'/><img src='https://badgen.net/github/last-commit/toskabnk/OligesAPI'/><img src='https://badgen.net/static/Laravel/10.10'/><img src='https://badgen.net/badge/icon/php?icon=php&label'>

## About Oliges

Oliges, an easy-to-use web application, is designed to streamline the management of olive oil cooperatives, covering everything from olive intake management to the automated generation of Delivery Note (DAT) documents. Oliges stands out for its ability to:

- Manage information about farmers and their properties.
- Record olive intake and generate receipts automatically.
- Handle lots sent to the mills.
- Simplify the creation of necessary documentation.
- Provide detailed statistics visualization.

With Oliges, you'll efficiently and swiftly digitize your cooperative.

## Requirements
- PHP 8.1, extensions Ctype, cURL, DOM PHP, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML.
- Composer
- Apache2 o Nginx
- MariaDB 10.10.2

## Installation

Clona el repositorio en una carpeta con el nombre del proyecto:
```shell
git clone https://github.com/toskabnk/OligesAPI.git
```
O en la carpeta actual con:
```shell
git clone https://github.com/toskabnk/OligesAPI.git .
```

Rename the file `.env.example` to `.env` and modify the database user values as well as the name. Modify any other values you deem necessary

Install the dependencies with:
```shell
composer install
```

Once the dependencies are installed, generate a new application key by executing:
```shell
php artisan key:generate
```

Create migrations in the database and generate the necessary minimal data:
```shell
php artisan migrate --seed
```

Install the necessary keys for Laravel Passport authentication with:
```shell
php artisan passport:install
```

## Run

Run the project in a local environment with:
```shell
php artisan serve
```
