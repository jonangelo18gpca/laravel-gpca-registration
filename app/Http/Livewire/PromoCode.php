<?php

namespace App\Http\Livewire;

use App\Models\Event as Events;
use Livewire\Component;
use App\Models\PromoCode as PromoCodes;
use App\Models\EventRegistrationType as EventRegistrationTypes;
use App\Models\PromoCodeAddtionalBadgeType as PromoCodeAddtionalBadgeTypes;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class PromoCode extends Component
{
    use WithFileUploads;

    public $event, $promoCodesPercentPricesArr = array(), $promoCodesFixedRatesArr = array(), $registrationTypes;

    public $importFile;
    public $importPreviewData = [];
    public $importDuplicates = [];
    public $importReady = false;

    public $showImportConfirm = false;
    public $duplicateCount = 0;
    public $newCount = 0;

    // Add promo code
    public $promo_code, $description, $badge_type, $discount_type, $discount, $number_of_codes, $total_usage, $validity, $new_rate, $new_rate_description;

    // Edit promo code
    public $editPromoCodeId, $editPromoCode, $editDescription, $editBadgeType, $editDiscountType, $editDiscount, $editNumberOfCodes, $editTotalUsage, $editValidity, $editNewRate, $editNewRateDescription;

    // Edit promo code registration types
    public $editRegTypeShowPC, $addRegType, $editPromoCodeRegistrationTypesArr = array(), $editRegTypePromoCodeId;

    public $updatePromoCode = false;
    public $updateRegistrationTypes = false;


    protected $listeners = ['updatePromoCodeConfirmed' => 'updatePromoCode', 'addPromoCodeConfirmed' => 'addPromoCode', 'confirmImportPromoCodes' => 'importPromoCodesConfirmed'];

    public function mount($eventCategory, $eventId)
    {
        $this->event = Events::where('id', $eventId)->where('category', $eventCategory)->first();
        $this->registrationTypes = EventRegistrationTypes::where('event_id', $eventId)->where('event_category', $eventCategory)->where('active', true)->get();

        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();
    }

    public function render()
    {
        return view('livewire.admin.events.promo-codes.promo-code');
    }

    public function addPromoCodeConfirmation()
    {
        if ($this->discount_type == 'fixed') {
            $this->discount = 0;
        } else {
            $this->new_rate = 0;
            $this->new_rate_description = 'N/A';
        }

        $this->validate(
            [
                'promo_code' => 'required',
                'badge_type' => 'required',
                'discount_type' => 'required',
                'discount' => 'required|numeric|min:0',
                'new_rate' => 'required|numeric|min:0',
                'new_rate_description' => 'required',
                'number_of_codes' => 'required|numeric|min:1|max:10000',
                'validity' => 'required',
            ],
            [
                'promo_code.required' => 'Code is required',
                'badge_type.required' => 'Badge Type is required',
                'discount_type.required' => 'Discount Type is required',

                'discount.required' => 'Discount is required',
                'discount.numeric' => 'Discount must be a number.',
                'discount.min' => 'Discount must be at least :min.',

                'new_rate.required' => 'New rate is required',
                'new_rate.numeric' => 'New rate must be a number.',
                'new_rate.min' => 'New rate must be at least :min.',

                'new_rate_description.required' => 'New rate description is required',

                'number_of_codes.required' => 'Number of codes is required',
                'number_of_codes.numeric' => 'Number of codes must be a number.',
                'number_of_codes.min' => 'Number of codes must be at least :min.',
                'number_of_codes.max' => 'Number of codes may not be greater than :max.',

                'validity.required' => 'Validity is required',
            ]
        );


        $this->dispatchBrowserEvent('swal:add-promo-code-confirmation', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => "",
        ]);
    }

    public function addPromoCode()
    {
        PromoCodes::create([
            'event_id' => $this->event->id,
            'event_category' => $this->event->category,
            'active' => true,
            'description' => $this->description,
            'badge_type' => $this->badge_type,
            'promo_code' => $this->promo_code,
            'discount_type' => $this->discount_type,
            'discount' => $this->discount,
            'new_rate' => $this->new_rate,
            'new_rate_description' => $this->new_rate_description,
            'total_usage' => 0,
            'number_of_codes' => $this->number_of_codes,
            'validity' => $this->validity,
        ]);

        $this->description = null;
        $this->badge_type = null;
        $this->promo_code = null;
        $this->discount_type = null;
        $this->discount = null;
        $this->new_rate = null;
        $this->new_rate_description = null;
        $this->number_of_codes = null;
        $this->validity = null;

        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();

        $this->dispatchBrowserEvent('swal:add-promo-code', [
            'type' => 'success',
            'message' => 'Promo Code added Successfully!',
            'text' => ''
        ]);
    }

    public function updateStatus($promoCodeId, $promoCodeActive)
    {
        PromoCodes::find($promoCodeId)->fill(
            [
                'active' => !$promoCodeActive,
            ],
        )->save();
        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();
    }

    public function showEditPromoCode($promoCodeId)
    {
        $promoCode = PromoCodes::findOrFail($promoCodeId);
        $this->editPromoCodeId = $promoCode->id;
        $this->editPromoCode = $promoCode->promo_code;
        $this->editDescription = $promoCode->description;
        $this->editBadgeType = $promoCode->badge_type;
        $this->editDiscountType = $promoCode->discount_type;
        $this->editDiscount = $promoCode->discount;
        $this->editNewRate = $promoCode->new_rate;
        $this->editNewRateDescription = $promoCode->new_rate_description;
        $this->editNumberOfCodes = $promoCode->number_of_codes;
        $this->editTotalUsage = $promoCode->total_usage;
        $this->editValidity = $promoCode->validity;
        $this->updatePromoCode = true;
    }

    public function hideEditPromoCode()
    {
        $this->updatePromoCode = false;

        $this->editPromoCodeId = null;
        $this->editPromoCode = null;
        $this->editDescription = null;
        $this->editBadgeType = null;
        $this->editDiscountType = null;
        $this->editDiscount = null;
        $this->editNewRate = null;
        $this->editNewRateDescription = null;
        $this->editNumberOfCodes = null;
        $this->editTotalUsage = null;
        $this->editValidity = null;
    }

    public function updatePromoCodeConfirmation()
    {
        if ($this->editDiscountType == 'fixed') {
            $this->editDiscount = 0;
        } else {
            $this->editNewRate = 0;
            $this->editNewRateDescription = "N/A";
        }

        $this->validate(
            [
                'editPromoCode' => 'required',
                'editBadgeType' => 'required',
                'editDiscountType' => 'required',
                'editDiscount' => 'required|numeric|min:0',
                'editNewRate' => 'required|numeric|min:0',
                'editNewRateDescription' => 'required',
                'editNumberOfCodes' => 'required|numeric|min:' . $this->editTotalUsage . '|max:10000',
                'editValidity' => 'required',
            ],
            [
                'editPromoCode.required' => 'Code is required',
                'editBadgeType.required' => 'Badge Type is required',
                'editDiscountType.required' => 'Discount Type is required',

                'editDiscount.required' => 'Discount is required',
                'editDiscount.numeric' => 'Discount must be a number.',
                'editDiscount.min' => 'Discount must be at least :min.',

                'editNewRate.required' => 'New rate is required',
                'editNewRate.numeric' => 'New rate must be a number.',
                'editNewRate.min' => 'New rate must be at least :min.',

                'editNewRateDescription.required' => 'New rate description is required',

                'editNumberOfCodes.required' => 'Number of codes is required',
                'editNumberOfCodes.numeric' => 'Number of codes must be a number.',
                'editNumberOfCodes.min' => 'Number of codes must be at least :min.',
                'editNumberOfCodes.max' => 'Number of codes may not be greater than :max.',

                'editValidity.required' => 'Validity is required',
            ]
        );

        $this->dispatchBrowserEvent('swal:update-promo-code-confirmation', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => "",
        ]);
    }

    public function updatePromoCode()
    {
        PromoCodes::find($this->editPromoCodeId)->fill([
            'description' => $this->editDescription,
            'badge_type' => $this->editBadgeType,
            'promo_code' => $this->editPromoCode,
            'discount_type' => $this->editDiscountType,
            'discount' => $this->editDiscount,
            'new_rate' => $this->editNewRate,
            'new_rate_description' => $this->editNewRateDescription,
            'number_of_codes' => $this->editNumberOfCodes,
            'validity' => $this->editValidity,
        ])->save();

        $this->hideEditPromoCode();

        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();

        $this->dispatchBrowserEvent('swal:update-promo-code', [
            'type' => 'success',
            'message' => 'Promo Code Updated Successfully!',
            'text' => ''
        ]);
    }


    public function showEditRegistrationTypes($promoCodeId)
    {
        $this->editRegTypePromoCodeId = $promoCodeId;
        $this->editRegTypeShowPC = PromoCodes::where('id', $promoCodeId)->where('event_id', $this->event->id)->value('promo_code');
        $addRegTypes = PromoCodeAddtionalBadgeTypes::where('promo_code_id', $promoCodeId)->get();

        if ($addRegTypes->isNotEmpty()) {
            foreach ($addRegTypes as $regType) {
                array_push($this->editPromoCodeRegistrationTypesArr, [
                    'id' => $regType->id,
                    'badgeType' => $regType->badge_type,
                ]);
            }
        }

        $this->updateRegistrationTypes = true;
    }

    public function cancelEditRegistrationTypes()
    {
        $this->updateRegistrationTypes = false;
        $this->editRegTypePromoCodeId = null;
        $this->editRegTypeShowPC = null;
        $this->editPromoCodeRegistrationTypesArr = array();
    }

    public function addRegistrationType()
    {
        $this->validate([
            'addRegType' => 'required',
        ]);

        $newBadgeType = PromoCodeAddtionalBadgeTypes::create([
            'event_id' => $this->event->id,
            'promo_code_id' => $this->editRegTypePromoCodeId,
            'badge_type' => $this->addRegType,
        ]);

        array_push($this->editPromoCodeRegistrationTypesArr, [
            'id' => $newBadgeType->id,
            'badgeType' => $newBadgeType->badge_type,
        ]);

        $this->addRegType = null;

        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();
    }

    public function deleteRegistrationType($additionalRegTypeId)
    {
        PromoCodeAddtionalBadgeTypes::find($additionalRegTypeId)->delete();

        $regTypeArrTemp = array();

        foreach ($this->editPromoCodeRegistrationTypesArr as $regType) {
            if ($regType['id'] != $additionalRegTypeId) {
                array_push($regTypeArrTemp, [
                    'id' => $regType['id'],
                    'badgeType' => $regType['badgeType'],
                ]);
            }
        }

        $this->editPromoCodeRegistrationTypesArr = $regTypeArrTemp;

        $this->getPromoCodesForPercentPrice();
        $this->getPromoCodeForFixedRate();
    }




    public function getPromoCodesForPercentPrice()
    {
        $promoCodesPercentPricesArrTemp = array();
        $promoCodesPercentPrices = PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('discount_type', '!=', 'fixed')->get();

        if ($promoCodesPercentPrices->isNotEmpty()) {
            foreach ($promoCodesPercentPrices as $promoCodesPercentPrice) {
                $addRegTypesArr = array();
                $addtionalRegistrationTypes = PromoCodeAddtionalBadgeTypes::where('event_id', $this->event->id)->where('promo_code_id', $promoCodesPercentPrice->id)->get();

                if ($addtionalRegistrationTypes->isNotEmpty()) {
                    foreach ($addtionalRegistrationTypes as $addtionalRegistrationType) {
                        array_push($addRegTypesArr, [
                            'addRegTypeId' => $addtionalRegistrationType->id,
                            'badgeType' => $addtionalRegistrationType->badge_type,
                        ]);
                    }
                }

                array_push($promoCodesPercentPricesArrTemp, [
                    'id' => $promoCodesPercentPrice->id,
                    'active' => $promoCodesPercentPrice->active,
                    'description' => $promoCodesPercentPrice->description,
                    'badge_type' => $promoCodesPercentPrice->badge_type,
                    'promo_code' => $promoCodesPercentPrice->promo_code,
                    'discount' => $promoCodesPercentPrice->discount,
                    'total_usage' => $promoCodesPercentPrice->total_usage,
                    'number_of_codes' => $promoCodesPercentPrice->number_of_codes,
                    'validity' => $promoCodesPercentPrice->validity,
                    'discount_type' => $promoCodesPercentPrice->discount_type,
                    'additionalBadgeType' => $addRegTypesArr,
                ]);
            }
        }
        $this->promoCodesPercentPricesArr = $promoCodesPercentPricesArrTemp;
        $promoCodesPercentPricesArrTemp = array();
    }

    public function getPromoCodeForFixedRate()
    {
        $promoCodesFixedRatesArrTemp = array();
        $promoCodesFixedRates = PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('discount_type', 'fixed')->get();

        if ($promoCodesFixedRates->isNotEmpty()) {
            foreach ($promoCodesFixedRates as $promoCodesFixedRate) {
                $addRegTypesArr = array();
                $addtionalRegistrationTypes = PromoCodeAddtionalBadgeTypes::where('event_id', $this->event->id)->where('promo_code_id', $promoCodesFixedRate->id)->get();

                if ($addtionalRegistrationTypes->isNotEmpty()) {
                    foreach ($addtionalRegistrationTypes as $addtionalRegistrationType) {
                        array_push($addRegTypesArr, [
                            'addRegTypeId' => $addtionalRegistrationType->id,
                            'badgeType' => $addtionalRegistrationType->badge_type,
                        ]);
                    }
                }

                array_push($promoCodesFixedRatesArrTemp, [
                    'id' => $promoCodesFixedRate->id,
                    'active' => $promoCodesFixedRate->active,
                    'description' => $promoCodesFixedRate->description,
                    'badge_type' => $promoCodesFixedRate->badge_type,
                    'promo_code' => $promoCodesFixedRate->promo_code,
                    'new_rate' => $promoCodesFixedRate->new_rate,
                    'new_rate_description' => $promoCodesFixedRate->new_rate_description,
                    'total_usage' => $promoCodesFixedRate->total_usage,
                    'number_of_codes' => $promoCodesFixedRate->number_of_codes,
                    'validity' => $promoCodesFixedRate->validity,
                    'discount_type' => $promoCodesFixedRate->discount_type,
                    'additionalBadgeType' => $addRegTypesArr,
                ]);
            }
        }
        $this->promoCodesFixedRatesArr = $promoCodesFixedRatesArrTemp;
        $promoCodesFixedRatesArrTemp = array();
    }



    public function importPromoCodesConfirmed()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $this->importFile->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            session()->flash('error', 'Cannot read CSV');
            return;
        }

        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);
        $index = array_flip($header);

        DB::beginTransaction();

        try {

            while (($row = fgetcsv($handle)) !== false) {

                if (empty($row[0])) continue;

                $promoCode = trim($row[$index['promo_code']]);

                $badgeTypesRaw = trim($row[$index['badgetype']]);

                // split VIP/Delegate/Speaker
                $badgeTypes = explode('/', $badgeTypesRaw);

                $mainBadgeType = trim($badgeTypes[0]);

                $additionalBadgeTypes = array_slice($badgeTypes, 1);

                $description = $row[$index['description']];
                $newRateDescription = $row[$index['new_rate_description']];
                $discountType = strtolower($row[$index['discount_type']]);
                $discount = (float)$row[$index['discount']];
                $newRate = (float)$row[$index['new_rate']];
                $numberOfCodes = (int)$row[$index['number_of_codes']];
                $validityRaw = trim($row[$index['validity']]);

                $validity = null;

                if (!empty($validityRaw)) {

                    // Handle Excel format DD/MM/YYYY
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $validityRaw)) {

                        $parts = explode('/', $validityRaw);

                        $validity = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    } else {

                        // fallback
                        $validity = date('Y-m-d', strtotime($validityRaw));
                    }
                }

                if ($discountType == 'fixed') {
                    $discount = 0;
                } else {
                    $newRate = 0;
                    $newRateDescription = 'N/A';
                }

                $promo = PromoCodes::create([
                    'event_id' => $this->event->id,
                    'event_category' => $this->event->category,
                    'active' => true,
                    'description' => $description,
                    'badge_type' => $mainBadgeType,
                    'promo_code' => $promoCode,
                    'discount_type' => $discountType,
                    'discount' => $discount,
                    'new_rate' => $newRate,
                    'new_rate_description' => $newRateDescription,
                    'total_usage' => 0,
                    'number_of_codes' => $numberOfCodes,
                    'validity' => $validity,
                ]);

                // insert additional badge types
                foreach ($additionalBadgeTypes as $badge) {

                    PromoCodeAddtionalBadgeTypes::create([
                        'event_id' => $this->event->id,
                        'promo_code_id' => $promo->id,
                        'badge_type' => trim($badge)
                    ]);
                }
            }
            fclose($handle);

            DB::commit();

            $this->importFile = null;

            $this->getPromoCodesForPercentPrice();
            $this->getPromoCodeForFixedRate();

            session()->flash('success', 'Import successful');
        } catch (\Exception $e) {

            DB::rollBack();

            session()->flash('error', $e->getMessage());
        }
    }

public function previewImport()
{
    if (!$this->importFile) {

        session()->flash('error', 'Please wait until file upload completes.');

        return;
    }

    $path = $this->importFile->getRealPath();

    if (!$path) {

        session()->flash('error', 'File upload not ready yet.');

        return;
    }

    $handle = fopen($path, 'r');

    if (!$handle) {

        session()->flash('error', 'Cannot read file.');

        return;
    }

    $header = fgetcsv($handle);

    $header = array_map('strtolower', $header);

    $index = array_flip($header);

    $duplicates = 0;
    $new = 0;

    while (($row = fgetcsv($handle)) !== false) {

        if(empty($row[0])) continue;

        $code = trim($row[$index['promo_code']]);

        $exists = PromoCodes::where('event_id', $this->event->id)
            ->where('event_category', $this->event->category)
            ->where('promo_code', $code)
            ->exists();

        if($exists)
            $duplicates++;
        else
            $new++;
    }

    fclose($handle);

    $this->duplicateCount = $duplicates;
    $this->newCount = $new;

    $this->showImportConfirm = true;
}


public function confirmImport()
{
    $this->showImportConfirm = false;

    $this->importPromoCodesConfirmed();
}
}
