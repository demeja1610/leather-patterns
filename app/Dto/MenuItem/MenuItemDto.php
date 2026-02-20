<?php

declare(strict_types=1);

namespace App\Dto\MenuItem;

use App\Dto\Dto;

class MenuItemDto extends Dto
{
    public function __construct(
        protected readonly ?string $text,
        protected readonly ?string $route,
        protected readonly ?string $icon,
        protected readonly ?MenuItemListDto $subMenu = null,
        protected readonly bool $isActive = false,
        protected readonly ?string $directUrl = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            text: $data['text'],
            route: $data['route'],
            icon: $data['icon'],
            subMenu: $data['sub_menu'] === null
                ? null
                : MenuItemListDto::fromArray(data: $data['sub_menu']),
            isActive: $data['is_active'],
            directUrl: $data['direct_url'],
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'route' => $this->route,
            'icon' => $this->icon,
            'sub_menu' => $this->subMenu?->toArray(),
            'is_active' => $this->isActive,
            'direct_url' => $this->directUrl,
        ];
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getSubMenu(): ?MenuItemListDto
    {
        return $this->subMenu;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getDirectUrl(): ?string
    {
        return $this->directUrl;
    }
}
