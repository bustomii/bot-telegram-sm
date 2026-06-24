<?php

namespace App\Enums;

enum LeadStatus: string
{
    case Lead = 'LEAD';
    case LeadQualified = 'LEAD_QUALIFIED';
    case RegisterPending = 'REGISTER_PENDING';
    case IbStep1Done = 'IB_STEP_1_DONE';
    case IbStep2Done = 'IB_STEP_2_DONE';
    case WaitingVerification = 'WAITING_VERIFICATION';
    case DataNotFound = 'DATA_NOT_FOUND';
    case IbNotMatch = 'IB_NOT_MATCH';
    case WaitingDeposit = 'WAITING_DEPOSIT';
    case Verified = 'VERIFIED';
    case PendingReview = 'PENDING_REVIEW';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Active = 'ACTIVE';
    case NeedHelpRegister = 'NEED_HELP_REGISTER';
    case NeedHelpIb = 'NEED_HELP_IB';
    case NeedHelpDeposit = 'NEED_HELP_DEPOSIT';
    case NeedHelpData = 'NEED_HELP_DATA';
    case NeedHelpOther = 'NEED_HELP_OTHER';
    case UnderReview = 'UNDER_REVIEW';
    case CaseClosed = 'CASE_CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Leads Baru',
            self::LeadQualified => 'Leads Qualified',
            self::RegisterPending => 'Registrasi Baru',
            self::IbStep1Done => 'Ubah IB Step 1',
            self::IbStep2Done => 'Ubah IB Step 2',
            self::WaitingVerification => 'Menunggu Verifikasi',
            self::DataNotFound => 'Data Tidak Ditemukan',
            self::IbNotMatch => 'IB Belum Sesuai',
            self::WaitingDeposit => 'Menunggu Deposit',
            self::Verified => 'Verified',
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Active => 'Member Aktif',
            self::NeedHelpRegister => 'Need Help - Registrasi',
            self::NeedHelpIb => 'Need Help - Ubah IB',
            self::NeedHelpDeposit => 'Need Help - Deposit',
            self::NeedHelpData => 'Need Help - Data',
            self::NeedHelpOther => 'Need Help - Lainnya',
            self::UnderReview => 'Under Review',
            self::CaseClosed => 'Case Closed',
        };
    }

    public static function dashboardStatuses(): array
    {
        return [
            self::Lead,
            self::LeadQualified,
            self::RegisterPending,
            self::IbStep1Done,
            self::IbStep2Done,
            self::WaitingDeposit,
            self::DataNotFound,
            self::IbNotMatch,
            self::WaitingVerification,
            self::Verified,
            self::Approved,
            self::Rejected,
            self::Active,
            self::NeedHelpRegister,
            self::NeedHelpIb,
            self::NeedHelpDeposit,
            self::NeedHelpData,
            self::NeedHelpOther,
            self::UnderReview,
            self::CaseClosed,
        ];
    }
}
