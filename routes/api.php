<?php

use App\Services\BidBondService;
use App\Services\CompanyService;
use App\Services\PaymentService;
use App\Services\ReportsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('email-test', function () {
    Mail::send('emails.test', [], function ($message) {
        $message
            ->to('kituyiv@gmail.com', 'VinnyVinny')
            ->subject('Test Message ❤');
    });
});

if (config('app.env') !== 'production') {
    Route::get('migrate', function () {

        Artisan::call('migrate:fresh --force --seed');
        shell_exec('php ../artisan passport:install');
        $bidBondService = new BidBondService();
        $company_service = new CompanyService();
        $paymentService = new PaymentService();
        $reportsService = new ReportsService();
        $bidBondService->migrate();
        $secret = DB::table('oauth_clients')
            ->where('password_client', 1)
            ->orderBy('id', 'desc')
            ->first();
        $company_service->migrate();
        $paymentService->migrate();
        $reportsService->migrate();
        put_permanent_env('PASSPORT_ID', $secret->id);
        put_permanent_env('PASSPORT_SECRET', $secret->secret);

        return response()->json(["message" => "Migrate fresh success "]);

    });
}

Route::post('password-reset-email', 'RegisterController@SendVerifyUserEmail');
Route::post('password-set-new', 'RegisterController@SetNewPassword');
Route::post('verify-company', 'CompanyController@approveCompany');
Route::get('bid-bonds/{secret}/{company}', 'BidBondController@show');


Route::group(['prefix' => 'v1'], function () {
    Route::resource('core-banking', 'SoapController');
    Route::post('register', 'RegisterController@register');
    Route::post('login', 'LoginController@login');
    Route::post('auth/google', 'LoginController@authLogin');
    Route::post('email/activate', 'RegisterController@verifyAccount');
    Route::post('update-password', 'LoginController@updatePassword');
    Route::post('resend-activation-email', 'RegisterController@resendActivationEmailCode');
    Route::post('verify/director', 'DirectorController@verifyDirectorCode');
    Route::post('activate/director', 'DirectorController@activateDirector');
    Route::post('verify-director-sms', 'DirectorController@verifyDirectorSms');


    Route::get('tender-listings', 'BidBondController@getTenderListings');
    Route::get('tender', 'BidBondController@getTenderInfo');

    Route::post('initialize', 'LoginController@initialize');
    Route::post('getCharge', 'BidBondController@getCharge');
    Route::post('resend-email', 'RegisterController@resendEmail');

    Route::get('counterparties', 'CounterPartyController@index');

    Route::post('quote', 'QuoteController@postQuote');
    Route::post('send-quote', 'QuoteController@sendQuote');
    Route::post('get-quote/{id}', 'QuoteController@getQuote');

    Route::get('company/account-balance/{user_id}/{role}', 'CompanyController@getBalance');

    //Reports
    Route::group(['prefix' => 'reports'], function () {
        Route::get('dashboard', 'ReportsController@getDashboard');
        Route::get('quotes', 'ReportsController@getQuotes');
        Route::get('companies', 'ReportsController@getCompanies');
        Route::get('bidbonds', 'ReportsController@getBidbonds');
    });
});

Route::post('companies/by-unique-id', 'CompanyController@getCompanyById');
Route::group(['middleware' => ['auth:api'], 'prefix' => 'v1'], function () {
    Route::post('logout', 'LoginController@logout');
    Route::post('verify/personal', 'LoginController@activateUserAccount');
    Route::post('user/getotp', 'LoginController@userOTP');
    Route::post('verify/phone', 'LoginController@activatePhoneOTP');
    Route::post('create/director', 'DirectorController@createDirector');
    Route::post('manual-create/director', 'DirectorController@createDirectorManual');

    Route::get('roles', 'RoleController@index');
    Route::get('other-roles', 'RoleController@otherRoles');


    Route::post('role/create', 'RoleController@store');
    Route::post('role/update', 'RoleController@update');
    Route::post('role/delete', 'RoleController@destroy');


    Route::get('permissions', 'PermissionController@index');
    Route::post('attach/permissions', 'PermissionController@attach')->middleware('can:attach-permissions');
    Route::post('role/abilities', 'PermissionController@getAbilities');

    Route::group(['middleware' => ['can:manage-settings']], function () {
        Route::get('kyc-status', 'KycController@index');
        Route::post('kyc-status', 'KycController@store');
        Route::put('kyc-status', 'KycController@update');
        Route::post('kyc-status/delete', 'KycController@destroy');

        Route::get('settings', 'SettingController@index');
        Route::post('settings', 'SettingController@update');

        Route::resource('price-settings', 'PriceSettingController')->only(['index', 'update']);
    });

    Route::get('customers', 'UserController@getCustomers');
    Route::get('relationship_managers', 'UserController@relationship_managers');
    Route::get('users', 'UserController@index')->middleware('can:list-users');
    Route::get('users/deleted', 'UserController@listDeleted')->middleware('can:restore-user');
    Route::post('user/create', 'UserController@store');
    Route::post('user/delete', 'UserController@destroy')->middleware('can:delete-users');
    Route::get('user/{id}', 'UserController@show');
    Route::post('user/{id}/restore', 'UserController@restore')->where('id', '[0-9]+')->middleware('can:restore-user');
    Route::post('change-user-role', 'UserController@changeRole')->middleware('can:suspend-user');
    Route::post('suspend-user', 'UserController@suspend')->middleware('can:suspend-user');
    Route::post('activate-user', 'UserController@activate')->middleware('can:suspend-user');
    Route::put('update-user/{id}', 'UserController@update')->middleware('can:update-user');

    Route::get('agents/options', 'AgentController@options');
    Route::get('agents/deleted', 'AgentController@listDeleted')->middleware('can:restore-agents');
    Route::post('agent/{id}/restore', 'AgentController@restore')->where('id', '[0-9]+')->middleware('can:restore-agents');
    Route::post('agent/{id}/linkUser', 'AgentController@linkUser')->where('id', '[0-9]+');
    Route::post('agent/{id}/unlinkUser', 'AgentController@unlinkUser')->where('id', '[0-9]+');
    Route::resource('agents', 'AgentController')->except(['edit', 'create']);

    Route::get('companies/agent', 'AgentCompanyController@index');
    Route::post('companies/agent', 'AgentCompanyController@store');
    Route::put('companies/agent', 'AgentCompanyController@update');
    Route::delete('companies/agent', 'AgentCompanyController@destroy');

    Route::get('postalcodes', 'CompanyController@getPostalCodes');

    Route::get('companies/deleted', 'CompanyController@getDeletedCompanies')->middleware('can:restore-companies');

    Route::get('companies/bid/{company_id}', 'CompanyController@getCompanyFromBidService');
    Route::get('companies/{company_unique_id}/account', 'CompanyController@getAccountBalance');
    Route::post('companies/{company}/restore', 'CompanyController@restore')->middleware('can:restore-companies');
    Route::post('companies/limit', 'CompanyController@updateLimit')->middleware('can:update-companies-limit');
    Route::get('approved-companies/options', 'CompanyController@approvedCompanyOptions');
    Route::post('approve-by-admin', 'CompanyController@approveByAdmin')->middleware('can:approve-companies');
    Route::get('companysearch/{companyid}', 'CompanyController@brsSearch');
    Route::post('search-company', 'CompanyController@CompanySearch');
    Route::post('upload-company-document', 'CompanyController@uploadCompanyDocument');
    Route::post('download-resolution', 'CompanyController@downloadResolution');
    Route::post('company/attachments', 'CompanyController@attachments');
    Route::post("company-approval", 'CompanyController@checkAndApproveCompany');

    Route::get('companies/{company_id}/users', 'CompanyUserController@index');
    Route::post('companies/{company_id}/users', 'CompanyUserController@store');
    Route::post('companies/users/unlink', 'CompanyUserController@destroy');

    Route::resource('companies', 'CompanyController')->except(['create']);

    Route::get('counterparty', 'CounterPartyController@creationDetails');
    Route::resource('counterparty', 'CounterPartyController')->only(['store', 'update', 'destroy']);

    Route::resource('bid-bond-templates', 'BidBondTemplatesController');
    Route::resource('bidbond-pricing', 'BidBondPricingController')->except(['show', 'create']);


    Route::post('groups/agent', 'GroupController@assignAgent')->middleware('can:update-agent');
    Route::resource('groups', 'GroupController')->except(['show', 'create']);

    Route::resource('categories', 'CategoriesController')->except(['create']);


    Route::get('bid-bonds', 'BidBondController@index');
    Route::put('bid-bonds/{id}', 'BidBondController@update');
    Route::post('bid-bonds', 'BidBondController@store');
    Route::post('apply-bidbond', 'BidBondController@applyBidBond');
    Route::get('bid-bonds/id/{id}', 'BidBondController@getById');
    Route::post('bid-bonds/tender', 'BidBondController@getByTender');
    Route::get('bid-bonds/{userid}', 'BidBondController@getUserBidbonds');
    Route::post('bid-bonds/preview', 'BidBondController@preview');
    Route::post('bid-bonds/exists', 'BidBondController@checkExists');
    Route::get('{secret}/bid-bonds', 'BidBondController@show');
    Route::post('getpricing', 'BidBondController@getPricing');
    Route::post('bidbond-cost', 'BidBondController@bidBondCost');
    Route::post('update/bid-bond', 'BidBondController@updateBidBond');
    Route::post('download-bidbond', 'BidBondController@downloadBidbond');
    Route::get('company-registration-details', 'MPESAController@companyRegistrationDetails');
    Route::post('confirm-payment', 'MPESAController@confirmPayment');
    Route::post('confirm-transaction', 'MPESAController@confirmTransaction');
    Route::post('initiate-stk', 'MPESAController@paymentRequest');

    Route::get('dashboard-stats', 'DashboardController@getStats');
    Route::get('quotes', 'QuoteController@index');

    Route::get('agents-reporting', 'ReportsController@getAgentsBidbonds');
    Route::get('t24-report', 'ReportsController@t24Report');

    Route::post('create-group', 'MarketingController@createGroup');
    Route::post('attach-group', 'MarketingController@attachGroup');
    Route::post('detach-group', 'MarketingController@detachGroup');
    Route::get('list-groups', 'MarketingController@listGroups');
    Route::get('company-by-groupid/{id}', 'MarketingController@companyByGroupId');
    Route::post('send-message', 'MarketingController@sendMessage');

    Route::get('payments', 'PaymentController@index');
    Route::post('initiate-wallet-payment-atm', 'PaymentController@payFromAtm');
    Route::post('add-amount-wallet', 'PaymentController@AddWalletAmount');

    Route::get('wallets', 'WalletController@getByUser');
    Route::get('wallet-transactions/{type}/{type_id}', 'WalletController@transactions');
    Route::post('wallet/balance', 'WalletController@balance');

    Route::get('reports/bidbonds/summary', 'ReportsController@bidbondSummary');
    Route::get('reports/bidbonds/expiry', 'ReportsController@expiredBidbonds');
    Route::get('reports/bidbonds/company-summary', 'ReportsController@companySummary');

    Route::get('commission', 'CommissionController@index');

});

