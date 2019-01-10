# LukiWiki

LukiWikiとは[Laravel](https://laravel.com/)を用いたWikiシステムです。

## インストール

最初に以下のコマンドを実行し、環境を整えてください。

```sh
composer install
cp .example.env .env
php artisan key:generate
npm install
npm run prod
```

完了したら`php artisan serv`でhttp://localhost:8000/からプログラムを走らせることができます。

## ライセンス

[MIT](LISENCE)