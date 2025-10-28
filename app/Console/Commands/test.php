<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class test extends Command
{
    use HasATenantsOption;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = $this->getTenants();

        tenancy()->runForMultiple(
            $tenants,
            function ($tenant) {
                Artisan::call('db:seed', [
                    '--class' => 'AnnouncementPermissionsSeeder',
                    '--force' => true,
                ]);
            }
        );

        return Command::SUCCESS;
    }
}
