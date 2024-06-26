<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\Data\{
    AreaSeeder,
    AssetSeeder,
    CertificateSeeder,
    DistrictSeeder,
    DivisionSeeder,
    HolderSeeder,
    ProvinceSeeder,
    RoleSeeder,
    SubDistrictSeeder,
    UrbanVillageSeeder,
    UserSeeder
};
use App\Models\{
    Role,
    Division,
    User,
    AreaType,
    Area,
    AssetType,
    Asset,
    AssetLossDamage,
    AssetLossDamageImage,
    AssetSubmission,
    AssetSubmissionImage,
    UrbanVillage,
    SubDistrict,
    District,
    Province,
    Holder,
    Certificate
};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        if (config('app.truncate_on_db_seed')) {
            $this->reset();
        }
        $this->seeds();
    }

    protected function reset(): void
    {
        Schema::disableForeignKeyConstraints();
        Role::truncate();
        Division::truncate();
        User::truncate();
        AreaType::truncate();
        Area::truncate();
        AssetType::truncate();
        Asset::truncate();
        AssetLossDamage::truncate();
        AssetLossDamageImage::truncate();
        AssetSubmission::truncate();
        AssetSubmissionImage::truncate();
        UrbanVillage::truncate();
        SubDistrict::truncate();
        District::truncate();
        Province::truncate();
        Holder::truncate();
        Certificate::truncate();
        Schema::enableForeignKeyConstraints();
    }

    protected function seeds(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AreaSeeder::class);
        $this->call(AssetSeeder::class);
        $this->call(UrbanVillageSeeder::class);
        $this->call(SubDistrictSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(ProvinceSeeder::class);
        $this->call(HolderSeeder::class);
        $this->call(CertificateSeeder::class);
    }
}
