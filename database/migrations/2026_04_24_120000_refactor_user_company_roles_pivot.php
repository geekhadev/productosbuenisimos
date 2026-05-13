<?php

use App\Models\Configuration\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensurePublicOwnerRoleExists();

        Schema::create('user_company_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained('configuration_companies')->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('configuration_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'company_id', 'role_id']);
            $table->index(['company_id', 'role_id']);
            $table->index(['user_id', 'company_id']);
        });

        $ownerRoleId = $this->publicOwnerRoleId();
        if ($ownerRoleId === null) {
            throw new RuntimeException('Public Owner role is missing after ensurePublicOwnerRoleExists().');
        }

        foreach (DB::table('configuration_companies')->select(['id', 'owner_id'])->whereNotNull('owner_id')->cursor() as $row) {
            $now = now();
            DB::table('user_company_roles')->insertOrIgnore([
                'user_id' => $row->owner_id,
                'company_id' => $row->id,
                'role_id' => $ownerRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('users')->select(['id', 'company_id'])->whereNotNull('company_id')->cursor() as $row) {
            $roleId = DB::table('configuration_roles')
                ->where('company_id', $row->company_id)
                ->where('is_public', false)
                ->orderBy('name')
                ->value('id');

            if ($roleId === null) {
                continue;
            }

            $now = now();
            DB::table('user_company_roles')->insertOrIgnore([
                'user_id' => $row->id,
                'company_id' => $row->company_id,
                'role_id' => $roleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('configuration_companies', function (Blueprint $table) {
            $table->dropUnique('configuration_companies_owner_name_unique');
            $table->dropUnique('configuration_companies_owner_alias_unique');
        });

        Schema::table('configuration_companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('configuration_companies', function (Blueprint $table) {
            $table->foreignUuid('owner_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('configuration_companies', function (Blueprint $table) {
            $table->unique(['owner_id', 'name'], 'configuration_companies_owner_name_unique');
            $table->unique(['owner_id', 'alias'], 'configuration_companies_owner_alias_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('company_id')
                ->nullable()
                ->after('type')
                ->constrained('configuration_companies')
                ->nullOnDelete();
        });

        Schema::dropIfExists('user_company_roles');
    }

    private function ensurePublicOwnerRoleExists(): void
    {
        $exists = DB::table('configuration_roles')
            ->where('name', Role::OWNER_SYSTEM_NAME)
            ->where('is_public', true)
            ->whereNull('company_id')
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();

        DB::table('configuration_roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => Role::OWNER_SYSTEM_NAME,
            'is_public' => true,
            'company_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function publicOwnerRoleId(): ?string
    {
        $id = DB::table('configuration_roles')
            ->where('name', Role::OWNER_SYSTEM_NAME)
            ->where('is_public', true)
            ->whereNull('company_id')
            ->value('id');

        return $id !== null ? (string) $id : null;
    }
};
