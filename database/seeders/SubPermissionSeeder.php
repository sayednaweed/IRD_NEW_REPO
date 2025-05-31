<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Models\SubPermission;
use Illuminate\Database\Seeder;
use App\Enums\SubPermissionEnum;

class SubPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->subUserPermissions();
        // $this->subNgoPermissions();
        // $this->subSettingPermissions();
        // $this->subAboutPermissions();
        // $this->subApprovalPermissions();
        // $this->subActivityPermissions();
        // $this->subProjectPermissions();
        $this->subDonorPermissions();
    }
    public function subUserPermissions()
    {
        foreach (SubPermissionEnum::USERS as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::users->value,
                "name" => $role,
            ]);
        }
    }

    public function subNgoPermissions()
    {
        foreach (SubPermissionEnum::NGO as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::ngo->value,
                "name" => $role,
            ]);
        }
    }
    public function subDonorPermissions()
    {
        foreach (SubPermissionEnum::Donor as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::donor->value,
                "name" => $role,
            ]);
        }
    }
    public function subProjectPermissions()
    {
        foreach (SubPermissionEnum::PROJECT as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::ngo->value,
                "name" => $role,
            ]);
        }
    }
    public function subSettingPermissions()
    {
        foreach (SubPermissionEnum::SETTINGS as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::settings->value,
                "name" => $role,
            ]);
        }
    }
    public function subAboutPermissions()
    {
        foreach (SubPermissionEnum::ABOUT as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::about->value,
                "name" => $role,
            ]);
        }
    }
    public function subApprovalPermissions()
    {
        foreach (SubPermissionEnum::APPROVALS as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::approval->value,
                "name" => $role,
            ]);
        }
    }
    public function subActivityPermissions()
    {
        foreach (SubPermissionEnum::ACTIVITY as $id => $role) {
            SubPermission::factory()->create([
                "id" => $id,
                "permission" => PermissionEnum::activity->value,
                "name" => $role,
            ]);
        }
    }
}
