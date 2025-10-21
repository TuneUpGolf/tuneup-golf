<?php

namespace App\Http\Middleware;

use App\Facades\UtilityFacades;
use Closure;
use Illuminate\Support\Facades\Storage;

class Setting
{
    public function handle($request, Closure $next)
    {
        if (tenant('domains') == null) {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            }
        }

        if (tenant('domains') == null) {
            config([
                'chatify.routes.middleware' => env('CHATIFY_ROUTES_MIDDLEWARE', ['web', 'auth', 'Setting'])
            ]);
        } else {
            config([
                'chatify.routes.middleware' => env('CHATIFY_ROUTES_MIDDLEWARE', ['web', 'auth', 'Setting', Middleware\InitializeTenancyByDomain::class])
            ]);
        }

        // Cache frequently used settings to avoid multiple queries
        $settings = [
            'app_name' => UtilityFacades::getsettings('app_name'),
            'storage_type' => UtilityFacades::getsettings('storage_type'),
            's3_key' => UtilityFacades::getsettings('s3_key'),
            's3_secret' => UtilityFacades::getsettings('s3_secret'),
            's3_region' => UtilityFacades::getsettings('s3_region'),
            's3_bucket' => UtilityFacades::getsettings('s3_bucket'),
            'wasabi_key' => UtilityFacades::getsettings('wasabi_key'),
            'wasabi_secret' => UtilityFacades::getsettings('wasabi_secret'),
            'wasabi_region' => UtilityFacades::getsettings('wasabi_region'),
            'wasabi_bucket' => UtilityFacades::getsettings('wasabi_bucket'),
            'wasabi_root' => UtilityFacades::getsettings('wasabi_root'),
            'pusher_key' => UtilityFacades::getsettings('pusher_key'),
            'pusher_secret' => UtilityFacades::getsettings('pusher_secret'),
            'pusher_id' => UtilityFacades::getsettings('pusher_id'),
            'pusher_cluster' => UtilityFacades::getsettings('pusher_cluster'),
            'recaptcha_key' => UtilityFacades::getsettings('recaptcha_key'),
            'recaptcha_secret' => UtilityFacades::getsettings('recaptcha_secret'),
            'paypal_mode' => UtilityFacades::getsettings('paypal_mode'),
            'paypal_client_id' => UtilityFacades::getsettings('paypal_client_id'),
            'paypal_client_secret' => UtilityFacades::getsettings('paypal_client_secret'),
            'paytab_profile_id' => UtilityFacades::getsettings('paytab_profile_id'),
            'paytab_server_key' => UtilityFacades::getsettings('paytab_server_key'),
            'paytab_region' => UtilityFacades::getsettings('paytab_region'),
            'paytm_environment' => UtilityFacades::getsettings('paytm_environment'),
            'paytm_merchant_id' => UtilityFacades::getsettings('paytm_merchant_id'),
            'paytm_merchant_key' => UtilityFacades::getsettings('paytm_merchant_key'),
            'google_client_id' => UtilityFacades::getsettings('google_client_id'),
            'google_client_secret' => UtilityFacades::getsettings('google_client_secret'),
            'google_redirect' => UtilityFacades::getsettings('google_redirect'),
            'FACEBOOK_CLIENT_ID' => UtilityFacades::getsettings('FACEBOOK_CLIENT_ID'),
            'FACEBOOK_CLIENT_SECRET' => UtilityFacades::getsettings('FACEBOOK_CLIENT_SECRET'),
            'FACEBOOK_REDIRECT' => UtilityFacades::getsettings('FACEBOOK_REDIRECT'),
            'GITHUB_CLIENT_ID' => UtilityFacades::getsettings('GITHUB_CLIENT_ID'),
            'GITHUB_CLIENT_SECRET' => UtilityFacades::getsettings('GITHUB_CLIENT_SECRET'),
            'GITHUB_REDIRECT' => UtilityFacades::getsettings('GITHUB_REDIRECT'),
            'LINKEDIN_CLIENT_ID' => UtilityFacades::getsettings('LINKEDIN_CLIENT_ID'),
            'LINKEDIN_CLIENT_SECRET' => UtilityFacades::getsettings('LINKEDIN_CLIENT_SECRET'),
            'LINKEDIN_REDIRECT' => UtilityFacades::getsettings('LINKEDIN_REDIRECT'),
            'google_calender_json_file' => UtilityFacades::getsettings('google_calender_json_file'),
            'google_calender_id' => UtilityFacades::getsettings('google_calender_id'),
        ];

        config([
            'app.name' => $settings['app_name'],

            'filesystems.default' => ($settings['storage_type'] != '') ? $settings['storage_type'] : 'local',
            'filesystems.disks.s3.key' => $settings['s3_key'],
            'filesystems.disks.s3.secret' => $settings['s3_secret'],
            'filesystems.disks.s3.region' => $settings['s3_region'],
            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],

            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
            'filesystems.disks.wasabi.endpoint' => $settings['wasabi_root'],

            'mail.default' => "smtp",
            'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'mail.mailers.smtp.port' =>  env('MAIL_PORT', 587),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail.mailers.smtp.username' =>  env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' =>  env('MAIL_PASSWORD'),
            'mail.from.address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mail.from.name' => env('MAIL_FROM_NAME', 'Example'),

            'chatify.pusher.key' => $settings['pusher_key'],
            'chatify.pusher.secret' => $settings['pusher_secret'],
            'chatify.pusher.app_id' => $settings['pusher_id'],
            'chatify.pusher.options.cluster' => $settings['pusher_cluster'],

            'captcha.sitekey' => $settings['recaptcha_key'],
            'captcha.secret' => $settings['recaptcha_secret'],

            'services.twilio.sid' => env('TWILIO_SID'),
            'services.twilio.token' => env('TWILIO_AUTH_TOKEN'),
            'services.twilio.phone' => env('TWILIO_PHONE_NUMBER'),

            'paypal.mode' => $settings['paypal_mode'],
            'paypal.sandbox.client_id' => $settings['paypal_client_id'],
            'paypal.sandbox.client_secret' => $settings['paypal_client_secret'],
            'paypal.sandbox.app_id' => 'sb-435ylq19779747@business.example.com',
            'paypal.live.app_id' => 'sb-435ylq19779747@business.example.com',

            'paytabs.profile_id' => $settings['paytab_profile_id'],
            'paytabs.server_key' => $settings['paytab_server_key'],
            'paytabs.region' => $settings['paytab_region'],
            'paytabs.currency' => 'INR',

            'services.paytm.env' => $settings['paytm_environment'],
            'services.paytm.merchant_id' => $settings['paytm_merchant_id'],
            'services.paytm.merchant_key' => $settings['paytm_merchant_key'],

            'services.google.client_id' => $settings['google_client_id'],
            'services.google.client_secret' => $settings['google_client_secret'],
            'services.google.redirect' => $settings['google_redirect'],

            'services.facebook.client_id' => $settings['FACEBOOK_CLIENT_ID'],
            'services.facebook.client_secret' => $settings['FACEBOOK_CLIENT_SECRET'],
            'services.facebook.redirect' => $settings['FACEBOOK_REDIRECT'],

            'services.github.client_id' => $settings['GITHUB_CLIENT_ID'],
            'services.github.client_secret' => $settings['GITHUB_CLIENT_SECRET'],
            'services.github.redirect' => $settings['GITHUB_REDIRECT'],

            'services.linkedin.client_id' => $settings['LINKEDIN_CLIENT_ID'],
            'services.linkedin.client_secret' => $settings['LINKEDIN_CLIENT_SECRET'],
            'services.linkedin.redirect' => $settings['LINKEDIN_REDIRECT'],

            'google-calendar.default_auth_profile' => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => Storage::path($settings['google_calender_json_file']),
            'google-calendar.auth_profiles.oauth.credentials_json' => Storage::path($settings['google_calender_json_file']),
            'google-calendar.auth_profiles.oauth.token_json' => Storage::path($settings['google_calender_json_file']),
            'google-calendar.calendar_id' => $settings['google_calender_id'],
            'google-calendar.user_to_impersonate' => '',
        ]);

        return $next($request);
    }
}
