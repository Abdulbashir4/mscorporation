# cPanel deployment — MS Corporation

## GitHub থেকে FTPS আপডেট

`.github/workflows/deploy.yml` main ব্রাঞ্চে push হলে পরীক্ষা, production vendor ও frontend build তৈরি করে `ftp.mscorporation.top:21`-এ explicit FTPS দিয়ে পাঠাবে। GitHub Actions secrets-এ `FTP_USERNAME` এবং `FTP_PASSWORD` লাগবে। FTP account-এর root `/home3/mscorporation/mscorporation-` হতে হবে। বাড়তি `FTP_SERVER` secret প্রয়োজন নেই। Certificate verification বন্ধ করা হয়নি।

Workflow এখনও স্থানীয় ফাইল হিসেবে প্রস্তুত; GitHub-এ commit/push এবং সফল Actions run ছাড়া এটি সক্রিয় নয়। FTP কেবল ফাইল পাঠায়—database import, migration এবং server cache refresh চালায় না। Terminal না থাকলে cPanel Cron Jobs দিয়ে server-side Artisan commands চালানোর ব্যবস্থা করতে হবে। পুরোনো cache থাকলে File Manager থেকে কেবল generated cache ফাইল পরিষ্কার করতে হবে; `.gitignore` মুছবেন না।

আপলোড `.env`, storage এবং database data পরিবর্তন বা remote file delete করে না। একাধিক ফাইল সরাসরি আপডেট হয়; এটি atomic release নয়, তাই প্রথম সেটআপ ও বড় আপডেটের সময় maintenance window ব্যবহার করুন।

## আপনার পরিবর্তন করার জায়গা

1. সার্ভারে `.env.production.example` কপি করে `.env` নাম দিন। স্থানীয় `.env` বদলাবেন না।
2. `.env`-এ `APP_URL` = HTTPS সহ আপনার ডোমেইন।
3. `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` = হোস্টিংয়ের MySQL তথ্য। cPanel-এর সম্পূর্ণ prefixed database ও username ব্যবহার করুন।
4. `APP_ACCESS_USERNAME` ও `APP_ACCESS_PASSWORD` = ওয়েবসাইট খোলার ইউজারনেম ও শক্তিশালী পাসওয়ার্ড। এটি একটি শেয়ার করা প্রবেশপথ, আলাদা user account নয়। এগুলো ছাড়া production সাইট বন্ধ থাকবে। HTTPS চালু রাখুন।
5. `APP_KEY` = বর্তমান ডেটা স্থানান্তর করলে আগের `.env` থেকে একই key দিন। একেবারে নতুন ইনস্টল হলে সার্ভারে `php artisan key:generate` চালান; ব্যবহৃত key বারবার বদলাবেন না।
6. cPanel Domains থেকে Document Root দিন `/home/YOUR_CPANEL_USER/ms-corporation/public`। পুরো প্রজেক্ট `public_html`-এ রাখবেন না। `.cpanel.yml`-এ কোনো username/path বদলানোর প্রয়োজন নেই।

## প্রথমবার আপলোড ও চালু করা

- হোস্টিংয়ে PHP 8.4 বা উপযোগী নতুন সংস্করণ, Composer 2, PDO MySQL, mbstring, openssl, fileinfo, XML/DOM, curl, ctype, tokenizer ও session প্রয়োজন। Composer-এর platform check পাস করতে হবে।
- মূল প্রজেক্ট `/home/YOUR_CPANEL_USER/ms-corporation`-এ আপলোড করুন। `public/build` অবশ্যই আপলোড করবেন (Git-এ ignored)। `.git`, `node_modules`, স্থানীয় `.env`, log, compiled cache ও tests আপলোড করার প্রয়োজন নেই।
- বর্তমান MySQL database phpMyAdmin থেকে SQL হিসেবে export করে সার্ভারের database-এ import করুন—`migrations` টেবিলসহ। পুরোনো ব্যবসার কয়েকটি টেবিল শুধু migration দিয়ে পুনরায় তৈরি হয় না; বর্তমান ডেটাবেস import এই সেটআপের প্রয়োজনীয় অংশ। SQL ফাইল web-accessible জায়গায় রাখবেন না।
- ছবি ও লোগোসহ বর্তমান `storage/app/private` এবং ব্যবহৃত হলে `storage/app/public` সার্ভারে কপি করুন। পরের deployment-এ `.env` ও storage সংরক্ষণ করবেন।
- `storage` ও `bootstrap/cache` PHP process-এর জন্য writable করুন (সাধারণত 775, ownership হোস্টিং অনুযায়ী); 777 দেবেন না।
- সার্ভারের Terminal-এ প্রকল্প ফোল্ডারে গিয়ে `bash deploy.sh` চালান। cPanel Git Deploy একই script ব্যবহার করবে। PHP CLI ও ওয়েবের PHP সংস্করণ একই হতে হবে; দরকারে `PHP_BIN=/path/to/php COMPOSER_BIN=/path/to/composer bash deploy.sh` ব্যবহার করুন।
- বিল্ড নতুন করে করতে স্থানীয় মেশিনে `pnpm install --frozen-lockfile` এবং `pnpm run build` চালিয়ে `public/build` আপলোড করুন। সার্ভারে Node প্রয়োজন নেই।
- HTTPS-এ সাইট খুলে প্রবেশের পাসওয়ার্ড, Customer/Supplier Save ও Update, Product, Purchase এবং Company logo যাচাই করুন। `/up`-ও production পাসওয়ার্ড দ্বারা সুরক্ষিত।

## পরবর্তী আপডেট

আগে সার্ভারের database ও storage backup নিন। তারপর পরিবর্তিত code ও public/build আপলোড করে `bash deploy.sh` চালান। `migrate:fresh`, `db:wipe` বা demo seeder চালাবেন না। স্থানীয় cache/server configuration আপলোড করবেন না।

Laravel deployment reference: https://laravel.com/framework/docs/deployment
