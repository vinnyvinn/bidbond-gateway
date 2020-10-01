# Beedee V2


Beedee V2 is built on laravel and lumen. It has 4 microservices:
- Company : (Lumen) Deals with Company registration
- Bidbond : (Lumen) Deals with bid bond applications
- Payment : (Laravel) Deals with payments
- Gateway: (Laravel) This actual project. This is the main project. 
All requests are channeled from here to the microservices.

## SetUp Documentation

- Run composer update
- Run php artisan migrate --seed
- Run php artisan passport:install
- Update .env PASSPORT_SECRET with the second passport secret
- Update .env PASSPORT_ID with the second passport ID
- Add ACCEPTED_KEYS to .env matching the GATEWAY_SECRET in company .env.
This is  required to enable (Authenticate Access middleware) access for search user by id and phone from company when adding directors.
- Add company, bidbond, payment urls based on domain or subdomain.On local hosting I have them as bidbond.test, payment.test etc. 
- Copy .htaccess from src to dist folder in front end app.
- Update .env on frontend app to use url on which it is hosted.
- Update config/cors.php to allow url on which it is hosted in gateway.
- Set proper laravel permissions
sudo chown -R mpf:www-data /theproject
sudo find /path/to/your/laravel/root/directory -type f -exec chmod 664 {} \;    
sudo find /path/to/your/laravel/root/directory -type d -exec chmod 775 {} \;
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
- Setting env remember to set AFT_ENABLE_SMS=true to enable sms
- Update config/aft with 'enable_sms' => env('AFT_ENABLE_SMS',false)  

## Beedee External Services

Beedee V2 uses the following external services

- Informa : Used for company and user search
- Tenders.go.ke : Used to fetch tenders
- Mpesa: For Payments
- AFT/Africa's Talking - For Sending SMS

## Testing
Run tests in gateway and company to make sure all microservices are working perfectly as well as integrations
use vendor/bin/phpunit.

##Permissions
If user is assigned a list permission don't assign the owned permission.
- To allow view all use list. eg. list-companies
- To allow view only their own use owned eg. list-companies-owned
- "*" shows that user has all permissions
- "*" and other permissions indicates use has all permissions except those particular permissions
