<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class TenantOption
{
    use HasATenantsOption;
    public function TenantOption()
    {
        $tenants = $this->getTenants();
        tenancy()->runForMultiple(
                    $tenants,
                    function ($tenant) {
                        Artisan::call('db:seed', [
                    '--class' => 'LessonTemplateSeeder',
                    '--force' => true,
                ]);
            }
        );
    }
}