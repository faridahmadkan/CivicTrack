<?php

namespace App\Services;

use App\Models\CivicReport;
use App\Models\GuestAccessToken;
use Illuminate\Support\Facades\DB;

class CivicReportAccessService
{
    public function authenticateGuest(CivicReport $report, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $accessToken = GuestAccessToken::query()
            ->where('civic_report_id', $report->id)
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($accessToken === null) {
            return false;
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        return true;
    }

    public function revokeAll(CivicReport $report): int
    {
        return DB::transaction(fn (): int => $report->guestAccessTokens()
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]));
    }
}
