<?php

namespace App\Filament\Pages;

use App\Models\Reservation;
use App\Models\Tool;
use App\Models\User;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    public int $toolsCount = 0;

    public int $reservationsCount = 0;

    public int $usersCount = 0;

    public int $viewsCount = 0;

    public function mount(): void
    {
        $this->toolsCount = Tool::count();
        $this->reservationsCount = Reservation::count();
        $this->usersCount = User::count();
        $this->viewsCount = (int) Tool::sum('views');
    }
}
