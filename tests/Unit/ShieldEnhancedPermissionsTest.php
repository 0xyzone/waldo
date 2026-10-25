<?php

namespace Tests\Unit;

use App\Filament\Pages\Changelog;
use App\Filament\Pages\Chat;
use App\Filament\Pages\SsfUploadGenerator;
use App\Filament\Resources\EmployeePromotions\EmployeePromotionResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\EmployeeTransfers\EmployeeTransferResource;
use App\Filament\Resources\IdCardRequests\IdCardRequestResource;
use App\Filament\Resources\NametagFines\NametagFineResource;
use App\Filament\Resources\SalaryIncrementRequests\SalaryIncrementRequestResource;
use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Filament\Resources\Users\UserResource;
use PHPUnit\Framework\TestCase;

class ShieldEnhancedPermissionsTest extends TestCase
{
    public function test_pages_declare_enhanced_permissions(): void
    {
        $ssfPerms = SsfUploadGenerator::getShieldPagePermissions();
        $this->assertArrayHasKey('view', $ssfPerms);
        $this->assertArrayHasKey('download', $ssfPerms);
        $this->assertArrayHasKey('generate', $ssfPerms);

        $changelogPerms = Changelog::getShieldPagePermissions();
        $this->assertArrayHasKey('view', $changelogPerms);

        $chatPerms = Chat::getShieldPagePermissions();
        $this->assertArrayHasKey('view', $chatPerms);
        $this->assertArrayHasKey('createGroupChat', $chatPerms);
    }

    public function test_resources_declare_enhanced_permissions(): void
    {
        $employeePerms = EmployeeResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('Onboard', $employeePerms);
        $this->assertArrayHasKey('Suspend', $employeePerms);
        $this->assertArrayHasKey('Terminate', $employeePerms);
        $this->assertArrayHasKey('ToggleManager', $employeePerms);

        $promoPerms = EmployeePromotionResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('SyncHrms', $promoPerms);

        $transferPerms = EmployeeTransferResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('SyncHrms', $transferPerms);

        $salaryPerms = SalaryIncrementRequestResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('HrAcknowledge', $salaryPerms);
        $this->assertArrayHasKey('FinanceAcknowledge', $salaryPerms);

        $nametagPerms = NametagFineResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('HrAcknowledge', $nametagPerms);
        $this->assertArrayHasKey('FinanceAcknowledge', $nametagPerms);

        $tipsPerms = TipsReportResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('ValidateReport', $tipsPerms);
        $this->assertArrayHasKey('UnlockReport', $tipsPerms);

        $userPerms = UserResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('ToggleActive', $userPerms);
        $this->assertArrayHasKey('ConfigureOtpEmail', $userPerms);
        $this->assertArrayHasKey('Impersonate', $userPerms);

        $idCardPerms = IdCardRequestResource::getShieldResourcePermissions();
        $this->assertArrayHasKey('SendDiscordNotification', $idCardPerms);
    }
}
