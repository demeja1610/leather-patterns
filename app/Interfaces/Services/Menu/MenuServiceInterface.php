<?php

declare(strict_types=1);

namespace App\Interfaces\Services\Menu;

use App\Dto\MenuItem\MenuItemListDto;

interface MenuServiceInterface
{
    public function getAdminMenu(): MenuItemListDto;
}
