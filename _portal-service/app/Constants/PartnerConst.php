<?php

namespace App\Constants;

class PartnerConst
{
    const TYPE_FOUNDATION = 'foundation';

    const TYPE_COMMUNITY = 'community';

    const TYPE_SCHOOL = 'school';

    const TYPE_INSTITUTION = 'institution';

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_OTHER = 'other';

    const STATUS_PENDING = 0;

    const STATUS_VERIFIED = 1;

    const STATUS_REJECTED = 2;

    public static function getTypes(): array
    {
        return [
            self::TYPE_FOUNDATION => 'Foundation',
            self::TYPE_COMMUNITY => 'Community',
            self::TYPE_SCHOOL => 'Special Education School / Institution',
            self::TYPE_INSTITUTION => 'Organization / Institution',
            self::TYPE_INDIVIDUAL => 'Individual',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Verification',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }
}
