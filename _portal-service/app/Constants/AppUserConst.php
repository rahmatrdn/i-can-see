<?php

namespace App\Constants;

class AppUserConst
{
    // Approval Statuses
    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    public const STATUS_REJECTED = 2;

    // Visual Impairment Levels
    public const LEVEL_TOTAL_BLIND = 1;

    public const LEVEL_LOW_VISION = 2;

    // Gender
    public const GENDER_MALE = 1;

    public const GENDER_FEMALE = 2;

    // Active Status
    public const ACTIVE = 1;

    public const INACTIVE = 0;

    /**
     * Get all approval statuses with English labels.
     *
     * @return array<int, string>
     */
    public static function getApprovalStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get label for an approval status.
     */
    public static function getApprovalStatusLabel(?int $status): string
    {
        return self::getApprovalStatuses()[$status] ?? 'Unknown';
    }

    /**
     * Get visual impairment levels with English labels.
     *
     * @return array<int, string>
     */
    public static function getVisualImpairmentLevels(): array
    {
        return [
            self::LEVEL_TOTAL_BLIND => 'Totally Blind',
            self::LEVEL_LOW_VISION => 'Low Vision',
        ];
    }

    /**
     * Get label for visual impairment level.
     */
    public static function getVisualImpairmentLevelLabel(?int $level): string
    {
        return self::getVisualImpairmentLevels()[$level] ?? '-';
    }

    /**
     * Get genders with English labels.
     *
     * @return array<int, string>
     */
    public static function getGenders(): array
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
        ];
    }

    /**
     * Get label for gender.
     */
    public static function getGenderLabel(?int $gender): string
    {
        return self::getGenders()[$gender] ?? '-';
    }
}
