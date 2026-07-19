<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'property_id',
        'two_factor_secret_encrypted', 'two_factor_recovery_codes',
        'two_factor_confirmed_at', 'two_factor_enabled', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret_encrypted', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function enableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret_encrypted' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function generateTwoFactorSecret(): string
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey(32);

        $this->update([
            'two_factor_secret_encrypted' => encrypt($secret),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ]);

        return $secret;
    }

    public function getTwoFactorQrCodeUrl(string $secret): string
    {
        $google2fa = app(Google2FA::class);

        return $google2fa->getQRCodeUrl(
            company: config('app.name'),
            holder: $this->email,
            secret: $secret,
        );
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (! $this->two_factor_secret_encrypted) {
            return false;
        }

        $google2fa = app(Google2FA::class);
        $secret = decrypt($this->two_factor_secret_encrypted);

        return $google2fa->verifyKey($secret, $code, 2);
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }

        return $codes;
    }

    public function consumeRecoveryCode(string $code): bool
    {
        $codes = $this->two_factor_recovery_codes ?? [];

        $index = array_search($code, $codes, true);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $this->update(['two_factor_recovery_codes' => array_values($codes)]);

        return true;
    }

    public function property()           { return $this->belongsTo(Property::class); }
    public function employee()           { return $this->hasOne(Employee::class); }
    public function reservationsCreated(){ return $this->hasMany(Reservation::class, 'created_by_user_id'); }
    public function foliosAsCashier()    { return $this->hasMany(Folio::class, 'cashier_id'); }
    public function foliosChargesPosted(){ return $this->hasMany(FolioCharge::class, 'posted_by_user_id'); }
    public function paymentsCollected()  { return $this->hasMany(FolioPayment::class, 'cashier_id'); }
    public function shifts()             { return $this->hasMany(CashierShift::class, 'cashier_id'); }
    public function nightAuditsRun()     { return $this->hasMany(NightAudit::class, 'run_by_user_id'); }
    public function hkTasksAssigned()    { return $this->hasMany(HkTask::class, 'assignee_id'); }
    public function workOrdersAssigned() { return $this->hasMany(WorkOrder::class, 'assignee_id'); }
    public function approvalsRequested() { return $this->hasMany(ApprovalRequest::class, 'requester_id'); }
    public function approvalsHandled()   { return $this->hasMany(ApprovalRequest::class, 'approver_id'); }
    public function threadsAssigned()    { return $this->hasMany(MessageThread::class, 'assignee_id'); }
    public function vouchersIssued()     { return $this->hasMany(GiftVoucher::class, 'issued_by_user_id'); }
    public function ordersAsServer()     { return $this->hasMany(PosOrder::class, 'server_id'); }
    public function guestRequestsAssigned() { return $this->hasMany(GuestRequest::class, 'assignee_id'); }
    public function vouchersRedeemed()      { return $this->hasMany(VoucherRedemption::class, 'redeemed_by_user_id'); }
    public function kbArticlesAuthored()    { return $this->hasMany(KbArticle::class, 'author_user_id'); }
    public function oooPeriodsCreated()     { return $this->hasMany(OutOfOrderPeriod::class, 'created_by_user_id'); }
    public function stockMovementsPerformed() { return $this->hasMany(StockMovement::class, 'performed_by_user_id'); }
}
