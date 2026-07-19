<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Property extends Model
{
    use HasFactory, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'is_pkp' => 'boolean',
        'is_active' => 'boolean',
        'theme' => 'array',
        'settings' => 'array',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    // Inventory & rates
    public function roomTypes()      { return $this->hasMany(RoomType::class); }
    public function rooms()          { return $this->hasMany(Room::class); }
    public function ratePlans()      { return $this->hasMany(RatePlan::class); }
    public function inventories()    { return $this->hasMany(Inventory::class); }

    // Front Office
    public function reservations()   { return $this->hasMany(Reservation::class); }
    public function folios()         { return $this->hasMany(Folio::class); }
    public function nightAudits()    { return $this->hasMany(NightAudit::class); }
    public function cashierShifts()  { return $this->hasMany(CashierShift::class); }
    public function groupBlocks()    { return $this->hasMany(GroupBlock::class); }
    public function waitlistEntries(){ return $this->hasMany(WaitlistEntry::class); }

    // CRM
    public function companies()      { return $this->hasMany(Company::class); }
    public function travelAgents()   { return $this->hasMany(TravelAgent::class); }

    // Housekeeping
    public function hkTasks()        { return $this->hasMany(HkTask::class); }
    public function lostAndFound()   { return $this->hasMany(LostAndFound::class); }

    // POS
    public function posOutlets()     { return $this->hasMany(PosOutlet::class); }

    // Channel
    public function channels()       { return $this->hasMany(Channel::class); }

    // Accounting
    public function chartOfAccounts(){ return $this->hasMany(ChartOfAccount::class); }
    public function accountingPeriods(){ return $this->hasMany(AccountingPeriod::class); }
    public function journalEntries() { return $this->hasMany(JournalEntry::class); }
    public function arAccounts()     { return $this->hasMany(ArAccount::class); }
    public function arInvoices()     { return $this->hasMany(ArInvoice::class); }
    public function apSuppliers()    { return $this->hasMany(ApSupplier::class); }
    public function apBills()        { return $this->hasMany(ApBill::class); }

    // Indonesia compliance
    public function eFakturRecords() { return $this->hasMany(EFakturRecord::class); }
    public function wnaLogs()        { return $this->hasMany(WnaLog::class); }
    public function nsfpPools()      { return $this->hasMany(NsfpPool::class); }

    // Integrations
    public function providers()      { return $this->hasMany(Provider::class); }
    public function providerAssignments() { return $this->hasMany(ProviderFeatureAssignment::class); }

    // Marketing & SEO
    public function reviews()        { return $this->hasMany(Review::class); }
    public function promoCodes()     { return $this->hasMany(PromoCode::class); }
    public function seoPages()       { return $this->hasMany(SeoPage::class); }
    public function seoRedirects()   { return $this->hasMany(SeoRedirect::class); }

    // Banquet
    public function functionRooms()  { return $this->hasMany(FunctionRoom::class); }
    public function events()         { return $this->hasMany(Event::class); }

    // Spa
    public function spaTreatments()  { return $this->hasMany(SpaTreatment::class); }
    public function spaTherapists()  { return $this->hasMany(SpaTherapist::class); }
    public function spaCabins()      { return $this->hasMany(SpaCabin::class); }
    public function spaAppointments(){ return $this->hasMany(SpaAppointment::class); }

    // HR
    public function employees()      { return $this->hasMany(Employee::class); }
    public function serviceChargeDistributions() { return $this->hasMany(ServiceChargeDistribution::class); }

    // Asset & Maintenance
    public function assets()         { return $this->hasMany(Asset::class); }
    public function workOrders()     { return $this->hasMany(WorkOrder::class); }
    public function ppmSchedules()   { return $this->hasMany(PreventiveMaintenanceSchedule::class); }

    // Loyalty
    public function loyaltyTiers()   { return $this->hasMany(LoyaltyTier::class); }
    public function loyaltyMembers() { return $this->hasMany(LoyaltyMember::class); }

    // Communication
    public function messageThreads() { return $this->hasMany(MessageThread::class); }
    public function messageTemplates(){ return $this->hasMany(MessageTemplate::class); }
    public function marketingCampaigns(){ return $this->hasMany(MarketingCampaign::class); }

    // Webhooks & approvals
    public function webhooks()       { return $this->hasMany(Webhook::class); }
    public function approvalRequests(){ return $this->hasMany(ApprovalRequest::class); }

    // Bank & finance
    public function bankAccounts()   { return $this->hasMany(BankAccount::class); }
    public function budgetPeriods()  { return $this->hasMany(BudgetPeriod::class); }
    public function ownerStatements(){ return $this->hasMany(OwnerStatement::class); }

    // Vouchers
    public function giftVouchers()   { return $this->hasMany(GiftVoucher::class); }

    // Stock
    public function stockItems()     { return $this->hasMany(StockItem::class); }

    // Door lock + rate shopper
    public function doorLockEvents() { return $this->hasMany(DoorLockEvent::class); }
    public function rateShopperSnapshots() { return $this->hasMany(RateShopperSnapshot::class); }
    public function gdsBookings()    { return $this->hasMany(GdsBooking::class); }

    // Phase AT-AY tables
    public function outOfOrderPeriods()      { return $this->hasMany(OutOfOrderPeriod::class); }
    public function allotments()             { return $this->hasMany(Allotment::class); }
    public function dailyFlashReports()      { return $this->hasMany(DailyFlashReport::class); }
    public function guestRequests()          { return $this->hasMany(GuestRequest::class); }
    public function cancellationPolicies()   { return $this->hasMany(CancellationPolicy::class); }
    public function otaVirtualCards()        { return $this->hasMany(OtaVirtualCard::class); }
    public function surveys()                { return $this->hasMany(Survey::class); }
    public function referralCodes()          { return $this->hasMany(ReferralCode::class); }
    public function documentTemplates()      { return $this->hasMany(DocumentTemplate::class); }
    public function kbArticles()             { return $this->hasMany(KbArticle::class); }
    public function pointsOfInterest()       { return $this->hasMany(PointOfInterest::class); }
    public function carbonFootprints()       { return $this->hasMany(CarbonFootprint::class); }
    public function sustainabilityMetrics()  { return $this->hasMany(SustainabilityMetric::class); }
    public function translations()           { return $this->hasMany(PropertyTranslation::class); }

    // Open Pricing & Dynamic Pricing
    public function rateOverrides()          { return $this->hasMany(RateOverride::class); }
    public function dynamicPricingRules()    { return $this->hasMany(DynamicPricingRule::class); }

    // Notification Logs
    public function notificationLogs()       { return $this->hasMany(NotificationLog::class); }

    // Channel Parity
    public function parityAlerts()           { return $this->hasMany(ChannelParityAlert::class); }
    public function digitalRegistrations()    { return $this->hasMany(\App\Models\DigitalRegistration::class); }
}
