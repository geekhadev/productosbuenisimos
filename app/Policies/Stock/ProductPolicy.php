<?php

namespace App\Policies\Stock;

use App\Enums\UserType;
use App\Models\Stock\Product;
use App\Models\User;
use App\Support\SelectedCompanySession;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->granted($user, 'stock.products.list');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->granted($user, 'stock.products.list') && $this->forSessionCompany($product);
    }

    public function create(User $user): bool
    {
        return $this->granted($user, 'stock.products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->granted($user, 'stock.products.update')
            && $this->forSessionCompany($product)
            && $product->is_active;
    }

    /**
     * Desactivar un producto aún activo (no confundir con {@see update}: el formulario de edición exige producto activo).
     */
    public function deactivate(User $user, Product $product): bool
    {
        return $this->granted($user, 'stock.products.update')
            && $this->forSessionCompany($product)
            && $product->is_active;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->granted($user, 'stock.products.delete')
            && $this->forSessionCompany($product)
            && ! $product->is_active;
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($product);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($product);
    }

    private function granted(User $user, string $slug): bool
    {
        if ($user->type === UserType::Root) {
            return true;
        }

        $companyId = SelectedCompanySession::selectedCompanyId(request());

        if ($companyId === null || $companyId === '') {
            return false;
        }

        return $user->hasCompanyPermission($slug, $companyId);
    }

    private function forSessionCompany(Product $product): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null && $companyId !== '' && $product->company_id === $companyId;
    }
}
