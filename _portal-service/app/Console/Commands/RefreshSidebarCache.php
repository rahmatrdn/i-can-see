<?php

namespace App\Console\Commands;

use App\Usecase\Admin\SidebarMenuUsecase;
use Illuminate\Console\Command;

class RefreshSidebarCache extends Command
{
    protected $signature = 'sidebar:refresh-cache';

    protected $description = 'Flush and rebuild sidebar menu cache for all roles and groups';

    public function __construct(protected SidebarMenuUsecase $usecase)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Flushing sidebar cache...');
        $this->info('Rebuilding sidebar cache...');

        $result = $this->usecase->refreshSidebarCache();

        if (! $result['success']) {
            $this->error($result['message'] ?? 'Failed to refresh sidebar cache.');

            return self::FAILURE;
        }

        foreach ($result['data']['refreshed'] ?? [] as $item) {
            $this->line("  ✓ access_type={$item['access_type']} group={$item['group']}");
        }

        $this->newLine();
        $this->info($result['message'] ?? 'Sidebar cache refreshed successfully.');

        return self::SUCCESS;
    }
}
