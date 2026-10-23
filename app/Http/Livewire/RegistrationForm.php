<?php

namespace App\Http\Livewire;

use App\Enums\AccessTypes;
use App\Mail\RegistrationFree;
use App\Mail\RegistrationUnpaid;
use Livewire\Component;
use App\Models\Member as Members;
use App\Models\PromoCode as PromoCodes;
use App\Models\MainDelegate as MainDelegates;
use App\Models\AdditionalDelegate as AdditionalDelegates;
use App\Models\EventDelegateFee;
use App\Models\Transaction as Transactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;


class RegistrationForm extends Component
{
    public $countries;
    public $companySectors;
    public $salutations;

    public $members, $event, $delegateFees;

    public $finalEbEndDate, $finalStdStartDate;
    public $finalWoEbEndDate, $finalWoStdStartDate;
    public $finalCoEbEndDate, $finalCoStdStartDate;
    public $currentStep = 1;
    public $showAddDelegateModal = false;
    public $showEditDelegateModal = false;
    public $additionalDelegates = [];
    public $invoiceDescription;


    // DELEGATE PASS TYPE
    public $accessType, $delegatePassType, $rateType, $termsCondition;

    // COMPANY INFO
    public $companyName, $companySector, $companyAddress, $companyCountry, $companyCity, $companyLandlineNumber, $companyMobileNumber, $assistantEmailAddress, $heardWhere, $attendingTo = [], $optionalInterests, $receiveWhatsappNotification = [];

    // MAIN DELEGATE
    public $salutation, $firstName, $middleName, $lastName, $emailAddress, $mobileNumber, $nationality, $jobTitle, $badgeType, $promoCode, $promoCodeDiscount, $discountType, $isMainFree = false, $country, $mainDelegateInterests = [];

    // SUB DELEGATE
    public $subSalutation, $subFirstName, $subMiddleName, $subLastName, $subEmailAddress, $subMobileNumber, $subNationality, $subJobTitle, $subBadgeType, $subPromoCode, $subPromoCodeDiscount, $subDiscountType, $subCountry, $subDelegateInterests = [];

    // SUB DELEGATE EDIT
    public $subIdEdit, $subSalutationEdit, $subFirstNameEdit, $subMiddleNameEdit, $subLastNameEdit, $subEmailAddressEdit, $subMobileNumberEdit, $subNationalityEdit, $subJobTitleEdit, $subBadgeTypeEdit, $subPromoCodeEdit, $subPromoCodeDiscountEdit, $subDiscountTypeEdit, $subCountryEdit, $subDelegateInterestsEdit = [];

    // 3RD PAGE
    public $paymentMethod, $finalEventStartDate, $finalEventEndDate, $finalQuantity, $finalUnitPrice, $finalNetAmount, $finalDiscount, $finalVat, $finalTotal;
    public $delegatInvoiceDetails = array();
    public $currentMainDelegateId;

    // 4TH PAGE
    public $sessionId, $cardDetails, $orderId, $transactionId, $htmlCodeOTP;

    // ERROR CHECKER
    public $emailMainExistingError, $emailSubExistingError, $emailMainAlreadyUsedError, $emailSubAlreadyUsedError;
    public $delegatePassTypeError, $paymentMethodError, $rateTypeString;
    public $promoCodeFailMain, $promoCodeSuccessMain, $promoCodeFailSub, $promoCodeSuccessSub;
    public $promoCodeFailSubEdit, $promoCodeSuccessSubEdit;

    // BANK DETAILS
    public $bankDetails;

    // ADDITIONAL FIELDS
    public $pcAttendingND;
    public $sccAttendingND;

    public $carParkNeeded;

    public $eventFormattedDate;

    public $ccEmailNotif;

    protected $listeners = ['registrationConfirmed' => 'addtoDatabase', 'emitInitiateAuth' => 'initiateAuthenticationCC', 'emitSubmit' => 'submitBankTransfer', 'emitSubmitStep3' => 'submitStep3'];

    public function mount($data)
    {
        $this->countries = config('app.countries');
        $this->companySectors = config('app.companySectors');
        $this->salutations = config('app.salutations');

        if ($data->category == "AF" || $data->category == "AFS" || $data->category == "AFV") {
            $this->bankDetails = config('app.bankDetails.AF');
        } else {
            $this->bankDetails = config('app.bankDetails.DEFAULT');
        }

        if ($data->category == "DAW") {
            $this->ccEmailNotif = config('app.ccEmailNotif.daw');
        } else if ($data->category == "GLF") {
            $this->ccEmailNotif = config('app.ccEmailNotif.glf');
        } else {
            $this->ccEmailNotif = config('app.ccEmailNotif.default');
        }

        $this->event = $data;
        $this->accessType = AccessTypes::FULL_EVENT->value;
        $this->currentStep = 1;

        $this->badgeType = "Delegate";
        $this->subBadgeType = "Delegate";
        $this->subBadgeTypeEdit = "Delegate";

        $this->members = Members::where('active', true)->orderBy('name', 'ASC')->get();
        $this->delegateFees = EventDelegateFee::where('event_id', $data->id)->where('event_category', $data->category)->get();

        $this->cardDetails = false;

        $today = Carbon::today();

        if ($this->event->eb_end_date != null && $this->event->eb_member_rate != null && $this->event->eb_nmember_rate != null) {
            if ($today->lte(Carbon::parse($this->event->eb_end_date))) {
                $this->finalEbEndDate = Carbon::parse($this->event->eb_end_date)->format('d M Y');
            } else {
                $this->finalEbEndDate = null;
            }
        } else {
            $this->finalEbEndDate = null;
        }

        if ($this->event->wo_eb_end_date != null && $this->event->wo_eb_member_rate != null && $this->event->wo_eb_nmember_rate != null) {
            if ($today->lte(Carbon::parse($this->event->wo_eb_end_date))) {
                $this->finalWoEbEndDate = Carbon::parse($this->event->wo_eb_end_date)->format('d M Y');
            } else {
                $this->finalWoEbEndDate = null;
            }
        } else {
            $this->finalWoEbEndDate = null;
        }

        if ($this->event->co_eb_end_date != null && $this->event->co_eb_member_rate != null && $this->event->co_eb_nmember_rate != null) {
            if ($today->lte(Carbon::parse($this->event->co_eb_end_date))) {
                $this->finalCoEbEndDate = Carbon::parse($this->event->co_eb_end_date)->format('d M Y');
            } else {
                $this->finalCoEbEndDate = null;
            }
        } else {
            $this->finalCoEbEndDate = null;
        }

        $this->finalStdStartDate = Carbon::parse($this->event->std_start_date)->format('d M Y');
        $this->finalWoStdStartDate = Carbon::parse($this->event->wo_std_start_date)->format('d M Y');
        $this->finalCoStdStartDate = Carbon::parse($this->event->co_std_start_date)->format('d M Y');

        $this->finalEventStartDate = Carbon::parse($this->event->event_start_date)->format('d M Y');
        $this->finalEventEndDate = Carbon::parse($this->event->event_end_date)->format('d M Y');

        if ($this->event->category == "PSW" && $this->event->year == "2025") {
            $this->eventFormattedDate = Carbon::parse($this->event->event_start_date)->format('j F') . ' - ' . Carbon::parse($this->event->event_end_date)->format('j F Y');
        } else {
            $this->eventFormattedDate = Carbon::parse($this->event->event_start_date)->format('j') . '-' . Carbon::parse($this->event->event_end_date)->format('j F Y');
        }
    }

    public function render()
    {
        return view('livewire.registration.registration-form');
    }

    public function checkUnitPrice()
    {
        $today = Carbon::today();

        // CHECK UNIT PRICE
        if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
            if ($this->event->co_eb_end_date != null && $this->event->co_eb_member_rate != null && $this->event->co_eb_nmember_rate != null) {
                if ($today->lte(Carbon::parse($this->event->co_eb_end_date))) {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_eb_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_eb_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_eb_nmember_rate;
                    }
                    $this->rateType = "Early Bird";
                } else {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_std_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_std_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->co_std_nmember_rate;
                    }
                    $this->rateType = "Standard";
                }
            } else {
                if ($this->delegatePassType == "fullMember") {
                    $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->co_std_full_member_rate;
                } else if ($this->delegatePassType == "member") {
                    $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->co_std_member_rate;
                } else {
                    $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->co_std_nmember_rate;
                }
                $this->rateType = "Standard";
            }
        } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
            if ($this->event->wo_eb_end_date != null && $this->event->wo_eb_member_rate != null && $this->event->wo_eb_nmember_rate != null) {
                if ($today->lte(Carbon::parse($this->event->wo_eb_end_date))) {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_eb_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_eb_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_eb_nmember_rate;
                    }
                    $this->rateType = "Early Bird";
                } else {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_std_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_std_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->wo_std_nmember_rate;
                    }
                    $this->rateType = "Standard";
                }
            } else {
                if ($this->delegatePassType == "fullMember") {
                    $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->wo_std_full_member_rate;
                } else if ($this->delegatePassType == "member") {
                    $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->wo_std_member_rate;
                } else {
                    $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->wo_std_nmember_rate;
                }
                $this->rateType = "Standard";
            }
        } else {
            if ($this->event->eb_end_date != null && $this->event->eb_member_rate != null && $this->event->eb_nmember_rate != null) {
                if ($today->lte(Carbon::parse($this->event->eb_end_date))) {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->eb_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->eb_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member early bird rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->eb_nmember_rate;
                    }
                    $this->rateType = "Early Bird";
                } else {
                    if ($this->delegatePassType == "fullMember") {
                        $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->std_full_member_rate;
                    } else if ($this->delegatePassType == "member") {
                        $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->std_member_rate;
                    } else {
                        $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                        $this->finalUnitPrice = $this->event->std_nmember_rate;
                    }
                    $this->rateType = "Standard";
                }
            } else {
                if ($this->delegatePassType == "fullMember") {
                    $this->rateTypeString = "Full member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->std_full_member_rate;
                } else if ($this->delegatePassType == "member") {
                    $this->rateTypeString = "Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->std_member_rate;
                } else {
                    $this->rateTypeString = "Non-Member standard rate" . $this->getAccessTypesDescription();
                    $this->finalUnitPrice = $this->event->std_nmember_rate;
                }
                $this->rateType = "Standard";
            }
        }
    }

    public function getAccessTypesDescription()
    {
        if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
            return " - Conference only";
        } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
            return " - Workshop only";
        } else {
            if (
                $this->event->co_eb_full_member_rate != null ||
                $this->event->co_eb_member_rate != null ||
                $this->event->co_eb_nmember_rate != null ||
                $this->event->co_std_full_member_rate != null ||
                $this->event->co_std_member_rate != null ||
                $this->event->co_std_nmember_rate != null ||
                $this->event->wo_eb_full_member_rate != null ||
                $this->event->wo_eb_member_rate != null ||
                $this->event->wo_eb_nmember_rate != null ||
                $this->event->wo_std_full_member_rate != null ||
                $this->event->wo_std_member_rate != null ||
                $this->event->wo_std_nmember_rate != null
            ) {
                return " - Full event";
            } else {
                return null;
            }
        }
    }

    public function calculateAmount()
    {
        $tempTotalNetAmount = 0;
        if ($this->promoCodeDiscount == null) {
            $this->promoCode = null;
            $delegateDescription = "Delegate registration fee - {$this->rateTypeString} - {$this->badgeType}";

            $tempUnitPrice = $this->finalUnitPrice;
            $tempTotalDiscount = 0;
            $tempTotalNetAmount = $this->finalUnitPrice;
        } else {

            if ($this->discountType == "percentage") {
                $tempUnitPrice = $this->finalUnitPrice;
                $tempTotalDiscount = $this->finalUnitPrice * ($this->promoCodeDiscount / 100);
                $tempTotalNetAmount = $this->finalUnitPrice - ($this->finalUnitPrice * ($this->promoCodeDiscount / 100));
                $delegateDescription = "Delegate Registration Fee - {$this->rateTypeString} - {$this->badgeType} - {$this->promoCodeDiscount} % discount";
            } else if ($this->discountType == "price") {
                $tempUnitPrice = $this->finalUnitPrice;
                $tempTotalDiscount = $this->promoCodeDiscount;
                $tempTotalNetAmount = $this->finalUnitPrice - $this->promoCodeDiscount;
                $delegateDescription = "Delegate Registration Fee - {$this->rateTypeString} - {$this->badgeType} - $ {$this->promoCodeDiscount} discount";
            } else {
                // FIXED RATE
                $promoCode = PromoCodes::where('event_id', $this->event->id)->where('promo_code', $this->promoCode)->first();
                $tempUnitPrice = $promoCode->new_rate;
                $tempTotalDiscount = 0;
                $tempTotalNetAmount = $promoCode->new_rate;
                $delegateDescription = $promoCode->new_rate_description;
            }
        }

        if ($tempTotalNetAmount == 0) {
            $this->isMainFree = true;
        }

        array_push($this->delegatInvoiceDetails, [
            'delegateDescription' => $delegateDescription,
            'delegateNames' => [
                $this->firstName . " " . $this->middleName . " " . $this->lastName,
            ],
            'badgeType' => $this->badgeType,
            'quantity' => 1,
            'totalUnitPrice' => $tempUnitPrice,
            'totalDiscount' => $tempTotalDiscount,
            'totalNetAmount' =>  $tempTotalNetAmount,
            'promoCode' => $this->promoCode,
            'promoCodeDiscount' => $this->promoCodeDiscount,
        ]);


        if (count($this->additionalDelegates) > 0) {
            for ($i = 0; $i < count($this->additionalDelegates); $i++) {
                $checkIfExisting = false;
                $existingIndex = 0;
                for ($j = 0; $j < count($this->delegatInvoiceDetails); $j++) {
                    if ($this->additionalDelegates[$i]['subBadgeType'] == $this->delegatInvoiceDetails[$j]['badgeType'] && $this->additionalDelegates[$i]['subPromoCodeDiscount'] == $this->delegatInvoiceDetails[$j]['promoCodeDiscount']) {
                        $existingIndex = $j;
                        $checkIfExisting = true;
                        break;
                    }
                }

                if ($checkIfExisting) {
                    array_push(
                        $this->delegatInvoiceDetails[$existingIndex]['delegateNames'],
                        $this->additionalDelegates[$i]['subFirstName'] . " " . $this->additionalDelegates[$i]['subMiddleName'] . " " . $this->additionalDelegates[$i]['subLastName']
                    );

                    $quantityTemp = $this->delegatInvoiceDetails[$existingIndex]['quantity'] + 1;

                    if ($this->additionalDelegates[$i]['subDiscountType'] != null) {
                        if ($this->additionalDelegates[$i]['subDiscountType'] == "percentage") {
                            $totalDiscountTemp = ($this->finalUnitPrice * ($this->delegatInvoiceDetails[$existingIndex]['promoCodeDiscount'] / 100)) * $quantityTemp;
                            $totalNetAmountTemp = ($this->finalUnitPrice * $quantityTemp) - $totalDiscountTemp;
                        } else if ($this->additionalDelegates[$i]['subDiscountType'] == "price") {
                            $totalDiscountTemp = $this->delegatInvoiceDetails[$existingIndex]['promoCodeDiscount'] * $quantityTemp;
                            $totalNetAmountTemp = ($this->finalUnitPrice * $quantityTemp) - $totalDiscountTemp;
                        } else {
                            $totalDiscountTemp = 0;
                            $totalNetAmountTemp = $this->delegatInvoiceDetails[$existingIndex]['totalUnitPrice'] * $quantityTemp;
                        }
                    } else {
                        $totalDiscountTemp = 0;
                        $totalNetAmountTemp = $this->finalUnitPrice * $quantityTemp;
                    }


                    $this->delegatInvoiceDetails[$existingIndex]['quantity'] = $quantityTemp;
                    $this->delegatInvoiceDetails[$existingIndex]['totalDiscount'] = $totalDiscountTemp;
                    $this->delegatInvoiceDetails[$existingIndex]['totalNetAmount'] = $totalNetAmountTemp;
                } else {
                    if ($this->additionalDelegates[$i]['subPromoCodeDiscount'] == null) {
                        $this->additionalDelegates[$i]['subPromoCode'] = null;
                        $delegateSubDescription = "Delegate registration fee - {$this->rateTypeString} - {$this->additionalDelegates[$i]['subBadgeType']}";

                        $tempSubUnitPrice = $this->finalUnitPrice;
                        $tempSubTotalDiscount = 0;
                        $tempSubTotalNetAmount = $this->finalUnitPrice;
                    } else {
                        if ($this->additionalDelegates[$i]['subDiscountType'] == "percentage") {
                            $tempSubUnitPrice = $this->finalUnitPrice;
                            $tempSubTotalDiscount = $this->finalUnitPrice * ($this->additionalDelegates[$i]['subPromoCodeDiscount'] / 100);
                            $tempSubTotalNetAmount = $this->finalUnitPrice - ($this->finalUnitPrice * ($this->additionalDelegates[$i]['subPromoCodeDiscount'] / 100));
                            $delegateSubDescription = "Delegate Registration Fee - {$this->rateTypeString} - {$this->additionalDelegates[$i]['subBadgeType']} - {$this->additionalDelegates[$i]['subPromoCodeDiscount']} % discount";
                        } else if ($this->additionalDelegates[$i]['subDiscountType'] == "price") {
                            $tempSubUnitPrice = $this->finalUnitPrice;
                            $tempSubTotalDiscount = $this->additionalDelegates[$i]['subPromoCodeDiscount'];
                            $tempSubTotalNetAmount = $this->finalUnitPrice - $this->additionalDelegates[$i]['subPromoCodeDiscount'];
                            $delegateSubDescription = "Delegate Registration Fee - {$this->rateTypeString} - {$this->additionalDelegates[$i]['subBadgeType']} - $ {$this->additionalDelegates[$i]['subPromoCodeDiscount']} discount";
                        } else {
                            // FIXED RATE
                            $promoCode = PromoCodes::where('event_id', $this->event->id)->where('promo_code', $this->additionalDelegates[$i]['subPromoCode'])->first();
                            $tempSubTotalDiscount = 0;
                            $tempSubUnitPrice = $promoCode->new_rate;
                            $tempSubTotalNetAmount = $promoCode->new_rate;
                            $delegateSubDescription = $promoCode->new_rate_description;
                        }
                    }

                    array_push($this->delegatInvoiceDetails, [
                        'delegateDescription' => $delegateSubDescription,
                        'delegateNames' => [
                            $this->additionalDelegates[$i]['subFirstName'] . " " . $this->additionalDelegates[$i]['subMiddleName'] . " " . $this->additionalDelegates[$i]['subLastName'],
                        ],
                        'badgeType' => $this->additionalDelegates[$i]['subBadgeType'],
                        'quantity' => 1,
                        'totalUnitPrice' => $tempSubUnitPrice,
                        'totalDiscount' => $tempSubTotalDiscount,
                        'totalNetAmount' =>  $tempSubTotalNetAmount,
                        'promoCode' => $this->additionalDelegates[$i]['subPromoCode'],
                        'promoCodeDiscount' => $this->additionalDelegates[$i]['subPromoCodeDiscount'],
                    ]);
                }
            }
        }

        foreach ($this->delegatInvoiceDetails as $delegatInvoiceDetail) {
            $this->finalQuantity += $delegatInvoiceDetail['quantity'];
            $this->finalDiscount += $delegatInvoiceDetail['totalDiscount'];
            $this->finalNetAmount += $delegatInvoiceDetail['totalNetAmount'];
        }
        $this->finalVat = $this->finalNetAmount * ($this->event->event_vat / 100);
        $this->finalTotal = $this->finalNetAmount + $this->finalVat;
    }

    public function resetCalculations()
    {
        $this->delegatInvoiceDetails = array();
        $this->finalQuantity = 0;
        $this->finalDiscount = 0;
        $this->finalNetAmount = 0;
        $this->finalVat = 0;
        $this->finalTotal = 0;
        $this->isMainFree = false;
    }

    public function increaseStep()
    {
        if ($this->currentStep == 1) {
            if ($this->delegatePassType != null) {
                $this->delegatePassTypeError = null;
                $this->validate(
                    [
                        'companyName' => 'required',
                        'accessType' => 'required',
                        'termsCondition' => 'accepted',
                    ],
                    [
                        'companyName.required' => "Company name is required",
                        'accessType.required' => "Access type is required",
                        'termsCondition.accepted' => "You must agree to the Terms and Conditions to proceed",
                    ]
                );
                $this->currentStep += 1;
            } else {
                $this->delegatePassTypeError = "Delegate pass type is required";
            }
        } else if ($this->currentStep == 2) {
            if ($this->event->category == "AF" && ($this->event->year == '2023' || $this->event->year == '2024')) {
                $this->validate(
                    [
                        'companySector' => 'required',
                        'companyAddress' => 'required',
                        'companyCountry' => 'required',
                        'companyCity' => 'required',
                        'companyMobileNumber' => 'required',
                        'assistantEmailAddress' => 'nullable|email',
                        'attendingTo' => 'required',
                    ],
                    [
                        'companySector.required' => 'Company sector is required',
                        'companyAddress.required' => 'Company address is required',
                        'companyCountry.required' => 'Country is required',
                        'companyCity.required' => 'City is required',
                        'companyMobileNumber.required' => 'Mobile number is required',
                        'assistantEmailAddress.email' => 'Assistant\'s email address must be a valid email',
                        'attendingTo.required' => 'Please choose at least one',
                    ]
                );
            } else {
                if ($this->event->category == "PSC" && $this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->validate(
                        [
                            'companySector' => 'required',
                            'companyAddress' => 'required',
                            'companyCountry' => 'required',
                            'companyCity' => 'required',
                            'companyMobileNumber' => 'required',
                            'assistantEmailAddress' => 'nullable|email',
                            'optionalInterests' => 'required',
                        ],
                        [
                            'companySector.required' => 'Company sector is required',
                            'companyAddress.required' => 'Company address is required',
                            'companyCountry.required' => 'Country is required',
                            'companyCity.required' => 'City is required',
                            'companyMobileNumber.required' => 'Mobile number is required',
                            'assistantEmailAddress.email' => 'Assistant\'s email address must be a valid email',
                            'optionalInterests.required' => 'Please choose at least one',
                        ]
                    );
                } else if ($this->event->category == "IPAW" && $this->event->year == '2025') {
                    $this->validate(
                        [
                            'companySector' => 'required',
                            'companyAddress' => 'required',
                            'companyCountry' => 'required',
                            'companyCity' => 'required',
                            'companyMobileNumber' => 'required',
                            'assistantEmailAddress' => 'nullable|email',
                            'carParkNeeded' => 'required',
                        ],
                        [
                            'companySector.required' => 'Company sector is required',
                            'companyAddress.required' => 'Company address is required',
                            'companyCountry.required' => 'Country is required',
                            'companyCity.required' => 'City is required',
                            'companyMobileNumber.required' => 'Mobile number is required',
                            'assistantEmailAddress.email' => 'Assistant\'s email address must be a valid email',
                            'carParkNeeded.required' => 'Please choose at least one',
                        ]
                    );
                } else {
                    $this->validate(
                        [
                            'companySector' => 'required',
                            'companyAddress' => 'required',
                            'companyCountry' => 'required',
                            'companyCity' => 'required',
                            'companyMobileNumber' => 'required',
                            'assistantEmailAddress' => 'nullable|email',
                        ],
                        [
                            'companySector.required' => 'Company sector is required',
                            'companyAddress.required' => 'Company address is required',
                            'companyCountry.required' => 'Country is required',
                            'companyCity.required' => 'City is required',
                            'companyMobileNumber.required' => 'Mobile number is required',
                            'assistantEmailAddress.email' => 'Assistant\'s email address must be a valid email',
                        ]
                    );
                }
            }
            $this->currentStep += 1;
        } else if ($this->currentStep == 3) {
            $this->resetCalculations();
            $this->paymentMethod = null;
            $this->emailMainAlreadyUsedError = null;
            $this->emailMainExistingError = null;

            $this->validate(
                [
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'emailAddress' => 'required|email',
                    'nationality' => 'required',
                    'mobileNumber' => 'required',
                    'jobTitle' => 'required',
                    'badgeType' => 'required',
                    'country' => 'required',
                    'mainDelegateInterests' => $this->event->category === 'AF'
                        ? 'required|array|min:1'
                        : 'nullable|array',
                ],
                [
                    'firstName.required' => "First name is required",
                    'lastName.required' => "Last name is required",
                    'emailAddress.required' => "Email address is required",
                    'emailAddress.email' => "Email address must be a valid email",
                    'nationality.required' => "Nationality is required",
                    'mobileNumber.required' => "Mobile number is required",
                    'jobTitle.required' => "Job title is required",
                    'badgeType.required' => "Registration type is required",
                    'country.required' => "Country is required",
                    'mainDelegateInterests.required' => 'Please select at least one feature.',
                    'mainDelegateInterests.array' => 'Please select a valid feature.',
                    'mainDelegateInterests.min' => 'Please select at least one feature.',

                ]
            );

            if ($this->event->category == "ANC" && $this->event->year == "2024") {
                if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                    $this->invoiceDescription = $this->event->name . ' – 11-12 September 2024  at ' . $this->event->location;
                } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->invoiceDescription = "Operational Excellence in the GCC Agri-Nutrients Industry Workshop – 10th September 2024 at " .  $this->event->location;
                } else {
                    $this->invoiceDescription = "Operational Excellence in the GCC Agri-Nutrients Industry Workshop and " . $this->event->name . ' – ' . $this->eventFormattedDate . ' at ' . $this->event->location;
                }
            } else if ($this->event->category == "PSC" && $this->event->year == "2024") {
                if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                    $this->invoiceDescription = $this->event->name . ' – 08-10 October 2024 at ' . $this->event->location;
                } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->invoiceDescription = "Process Safety Workshops – 7th October 2024 at " .  $this->event->location;
                } else {
                    $this->invoiceDescription = "Process Safety Workshops and " . $this->event->name . ' – ' . $this->eventFormattedDate . ' at ' . $this->event->location;
                }
            } else if ($this->event->category == "SCC" && $this->event->year == "2025") {
                if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                    $this->invoiceDescription = $this->event->name . ' – 27-28 May 2025 at ' . $this->event->location;
                } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->invoiceDescription = "Gulf SQAS Workshop – 26th May 2025 at the Sofitel Dubai Downtown";
                } else {
                    $this->invoiceDescription = "Gulf SQAS Workshop – 26th May 2025 at the Sofitel Dubai Downtown and " . $this->event->name . ' – ' . $this->eventFormattedDate . ' at ' . $this->event->location;
                }
            } else  if ($this->event->category == "ANC" && $this->event->year == "2025") {
                if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                    $this->invoiceDescription = $this->event->name . ' – 30 September-01 October 2025  at ' . $this->event->location;
                } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->invoiceDescription = "3rd Operational Excellence Workshop – 29th September 2025 at " .  $this->event->location;
                } else {
                    $this->invoiceDescription = "3rd Operational Excellence Workshop – 29th September 2025 and " . $this->event->name . ' - 30 September-01 October 2025  at ' . $this->event->location;
                }
            } else  if ($this->event->category == "RCC" && $this->event->year == "2025") {
                if ($this->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                    $this->invoiceDescription = $this->event->name . ' – 14-15 October 2025 at ' . $this->event->location;
                } else if ($this->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                    $this->invoiceDescription = "Pre-Conference Workshops – 13th October 2025 at " .  $this->event->location;
                } else {
                    $this->invoiceDescription = "Pre-Conference Workshops – 13th October 2025 and " . $this->event->name . ' - 14-15 October 2025 at ' . $this->event->location;
                }
            } else {
                $this->invoiceDescription = $this->event->name . ' – ' . $this->eventFormattedDate . ' at ' . $this->event->location;
            }

            $this->dispatchBrowserEvent('swal:add-step3-registration-loading-screen');
        } else if ($this->currentStep == 4) {
            if ($this->paymentMethod == null) {
                $this->paymentMethodError = "Please choose your payment method first";
            } else {
                if ($this->paymentMethod == "creditCard") {
                    $this->dispatchBrowserEvent('swal:registration-confirmation', [
                        'type' => 'warning',
                        'message' => 'Are you sure you want to pay via Credit card?',
                        'text' => "",
                    ]);
                } else {
                    if ($this->event->category == "GLF" || $this->event->category == "DFCLW1") {
                        $this->dispatchBrowserEvent('swal:registration-confirmation', [
                            'type' => 'warning',
                            'message' => 'Are you sure all the details are correct?',
                            'text' => "",
                        ]);
                    } else {
                        $this->dispatchBrowserEvent('swal:registration-confirmation', [
                            'type' => 'warning',
                            'message' => 'Are you sure you want to pay via Bank transfer?',
                            'text' => "",
                        ]);
                    }
                }
            }
        }
    }

    public function submitStep3()
    {
        if ($this->checkEmailIfUsedAlreadyMain($this->emailAddress)) {
            $this->emailMainAlreadyUsedError = "You already used this email!";
        } else {
            if ($this->checkEmailIfExistsInDatabase($this->emailAddress)) {
                $this->emailMainAlreadyUsedError = null;
                $this->emailMainExistingError = "Email is already registered, please use another email!";
            } else {
                $this->emailMainAlreadyUsedError = null;
                $this->emailMainExistingError = null;
                $this->checkUnitPrice();
                $this->calculateAmount();
                $this->currentStep += 1;
                if ($this->event->category == "GLF" || $this->event->category == "DFCLW1") {
                    $this->paymentMethod = 'bankTransfer';
                }
            }
        }

        $this->dispatchBrowserEvent('swal:remove-registration-loading-screen');
    }

    public function addtoDatabase()
    {
        if ($this->promoCode != null) {
            PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('active', true)->where('promo_code', $this->promoCode)->where('badge_type', $this->badgeType)->increment('total_usage');
        }

        if ($this->finalTotal == 0) {
            $paymentStatus = "free";
        } else {
            $paymentStatus = "unpaid";
        }

        $attending_plenary = false;
        $attending_sustainability = false;
        $attending_solxchange = false;
        $attending_yf = false;
        $attending_welcome_dinner = false;
        $attending_gala_dinner = false;

        if (count($this->attendingTo) > 0) {
            foreach ($this->attendingTo as $attendTo) {
                if ($attendTo == 1) {
                    $attending_plenary = true;
                } else if ($attendTo == 2) {
                    $attending_sustainability = true;
                } else if ($attendTo == 3) {
                    $attending_solxchange = true;
                } else if ($attendTo == 4) {
                    $attending_yf = true;
                } else if ($attendTo == 5) {
                    $attending_welcome_dinner = true;
                } else if ($attendTo == 6) {
                    $attending_gala_dinner = true;
                } else {
                }
            }
        }

        $receiveWhatsappNotification = false;
        if (count($this->receiveWhatsappNotification) > 0) {
            $receiveWhatsappNotification = true;
        }

        if ($this->accessType != AccessTypes::WORKSHOP_ONLY->value && $this->event->category == "PSC") {
            $finalOptionalInterests = null;
        } else {
            $finalOptionalInterests = $this->optionalInterests;
        }

        $newRegistrant = MainDelegates::create([
            'event_id' => $this->event->id,
            'access_type' => $this->accessType,
            'pass_type' => $this->delegatePassType,
            'rate_type' => $this->rateType,
            'rate_type_string' => $this->rateTypeString,

            'company_name' => $this->companyName,
            'company_sector' => $this->companySector,
            'company_address' => $this->companyAddress,
            'company_country' => $this->companyCountry,
            'company_city' => $this->companyCity,
            'company_telephone_number' => $this->companyLandlineNumber,
            'company_mobile_number' => $this->companyMobileNumber,
            'assistant_email_address' => $this->assistantEmailAddress,

            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'email_address' => $this->emailAddress,
            'mobile_number' => $this->mobileNumber,
            'nationality' => $this->nationality,
            'job_title' => $this->jobTitle,
            'badge_type' => $this->badgeType,
            'pcode_used' => $this->promoCode,
            'country' => $this->country,

            'heard_where' => $this->heardWhere,

            'attending_plenary' => $attending_plenary,
            'attending_sustainability' => $attending_sustainability,
            'attending_solxchange' => $attending_solxchange,
            'attending_yf' => $attending_yf,
            'attending_welcome_dinner' => $attending_welcome_dinner,
            'attending_gala_dinner' => $attending_gala_dinner,

            'receive_whatsapp_notifications' => $receiveWhatsappNotification,

            'optional_interests' => $finalOptionalInterests,

            'interests' => json_encode($this->mainDelegateInterests),

            'quantity' => $this->finalQuantity,
            'unit_price' => $this->finalUnitPrice,
            'net_amount' => $this->finalNetAmount,
            'vat_price' => $this->finalVat,
            'discount_price' => $this->finalDiscount,
            'total_amount' => $this->finalTotal,
            'mode_of_payment' => $this->paymentMethod,
            'registration_status' => "droppedOut",
            'payment_status' => $paymentStatus,
            'registered_date_time' => Carbon::now(),
            'paid_date_time' => null,

            'pc_attending_nd' => $this->pcAttendingND,
            'scc_attending_nd' => $this->sccAttendingND,
            'car_park_needed' => $this->carParkNeeded,
        ]);

        $transaction = Transactions::create([
            'event_id' => $this->event->id,
            'event_category' => $this->event->category,
            'delegate_id' => $newRegistrant->id,
            'delegate_type' => "main",
        ]);

        $tempYear = substr(Carbon::now()->year, -2);
        $lastDigit = 1000 + intval($transaction->id);
        $tempOrderId = $this->event->category . "$tempYear" . "$lastDigit";

        $this->currentMainDelegateId = $newRegistrant->id;

        if (!empty($this->additionalDelegates)) {
            foreach ($this->additionalDelegates as $additionalDelegate) {

                if ($additionalDelegate['subPromoCode'] != null) {
                    PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('active', true)->where('promo_code', $additionalDelegate['subPromoCode'])->where('badge_type', $additionalDelegate['subBadgeType'])->increment('total_usage');
                }

                $newAdditionDelegate = AdditionalDelegates::create([
                    'main_delegate_id' => $newRegistrant->id,
                    'salutation' => $additionalDelegate['subSalutation'],
                    'first_name' => $additionalDelegate['subFirstName'],
                    'middle_name' => $additionalDelegate['subMiddleName'],
                    'last_name' => $additionalDelegate['subLastName'],
                    'job_title' => $additionalDelegate['subJobTitle'],
                    'email_address' => $additionalDelegate['subEmailAddress'],
                    'nationality' => $additionalDelegate['subNationality'],
                    'mobile_number' => $additionalDelegate['subMobileNumber'],
                    'badge_type' => $additionalDelegate['subBadgeType'],
                    'pcode_used' => $additionalDelegate['subPromoCode'],
                    'country' => $additionalDelegate['subCountry'],
                    'interests' => json_encode($additionalDelegate['subDelegateInterests']),
                ]);

                Transactions::create([
                    'event_id' => $this->event->id,
                    'event_category' => $this->event->category,
                    'delegate_id' => $newAdditionDelegate->id,
                    'delegate_type' => "sub",
                ]);
            }
        }

        if ($this->paymentMethod == "creditCard") {
            $this->setSessionCC();
            $this->orderId = $tempOrderId;
            $this->currentStep += 1;
        } else {
            $this->dispatchBrowserEvent('swal:remove-registration-loading-screen');
            if ($this->event->category == "GLF" || $this->event->category == "DFCLW1") {
                $this->submitBankTransfer();
            } else {
                $this->currentStep += 1;
            }
        }
    }

    public function decreaseStep()
    {
        if ($this->currentStep == 2) {
            $this->members = Members::where('active', true)->orderBy('name', 'ASC')->get();
            $this->resetCalculations();
        }

        if ($this->currentStep == 3) {
            $this->resetCalculations();
        }

        if ($this->currentStep == 4) {
            $this->resetCalculations();
        }

        $this->currentStep -= 1;
    }

    public function submit()
    {
        if ($this->currentStep == 5) {
            $this->dispatchBrowserEvent('swal:add-registration-loading-screen');
        }
    }

    public function submitBankTransfer()
    {
        // UPDATE DETAILS
        if ($this->finalTotal == 0) {
            $paymentStatus = "free";
            $registrationStatus = "pending";
        } else {
            $paymentStatus = "unpaid";
            $registrationStatus = "pending";
        }

        MainDelegates::find($this->currentMainDelegateId)->fill([
            'registration_status' => $registrationStatus,
            'payment_status' => $paymentStatus,
            'paid_date_time' => null,
        ])->save();

        $transaction = Transactions::where('delegate_id', $this->currentMainDelegateId)->where('delegate_type', "main")->first();

        $eventFormattedData = Carbon::parse($this->event->event_start_date)->format('d') . '-' . Carbon::parse($this->event->event_end_date)->format('d M Y');
        $lastDigit = 1000 + intval($transaction->id);

        foreach (config('app.eventCategories') as $eventCategoryC => $code) {
            if ($this->event->category == $eventCategoryC) {
                $getEventcode = $code;
            }
        }

        $tempTransactionId = $this->event->year . "$getEventcode" . "$lastDigit";
        $invoiceLink = env('APP_URL') . '/' . $this->event->category . '/' . $this->event->id . '/view-invoice/' . $this->currentMainDelegateId;

        if ($this->event->eb_end_date == null) {
            $earlyBirdValidityDate = Carbon::createFromFormat('Y-m-d', $this->event->std_start_date)->subDay();
        } else {
            $earlyBirdValidityDate = Carbon::createFromFormat('Y-m-d', $this->event->eb_end_date);
        }

        $combinedStringPrint = "gpca@reg" . ',' . $this->event->id . ',' . $this->event->category . ',' . $this->currentMainDelegateId . ',' . 'main';
        $finalCryptStringPrint = base64_encode($combinedStringPrint);
        $qrCodeForPrint = 'ca' . $finalCryptStringPrint . 'gp';

        $details1 = [
            'name' => $this->salutation . " " . $this->firstName . " " . $this->middleName . " " . $this->lastName,
            'eventLink' => $this->event->link,
            'eventName' => $this->event->name,
            'eventDates' => $eventFormattedData,
            'eventLocation' => $this->event->location,
            'eventCategory' => $this->event->category,
            'eventYear' => $this->event->year,

            'accessType' => $this->accessType,
            'jobTitle' => $this->jobTitle,
            'companyName' => $this->companyName,
            'badgeType' => $this->badgeType,
            'amountPaid' => 0,
            'transactionId' => $tempTransactionId,
            'invoiceLink' => $invoiceLink,
            'earlyBirdValidityDate' => $earlyBirdValidityDate->format('jS F'),
            'badgeLink' => env('APP_URL') . "/" . $this->event->category . "/" . $this->event->id . "/view-badge" . "/" . "main" . "/" . $this->currentMainDelegateId,
            'qrCodeForPrint' => $qrCodeForPrint,
        ];


        //$this->event->category != "GLF" && $this->event->category != "DFCLW1"
        if ($this->event->category != "DFCLW1") {
            if ($this->isMainFree) {
                try {
                    Mail::to($this->emailAddress)->cc($this->ccEmailNotif)->send(new RegistrationFree($details1));
                    MainDelegates::find($this->currentMainDelegateId)->fill([
                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationFree($details1));
                }
            } else {
                try {
                    Mail::to($this->emailAddress)->cc($this->ccEmailNotif)->send(new RegistrationUnpaid($details1));
                    MainDelegates::find($this->currentMainDelegateId)->fill([
                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationUnpaid($details1));
                }
            }

            if ($this->assistantEmailAddress != null) {
                if ($this->isMainFree) {
                    try {
                        Mail::to($this->assistantEmailAddress)->send(new RegistrationFree($details1));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationFree($details1));
                    }
                } else {
                    try {
                        Mail::to($this->assistantEmailAddress)->send(new RegistrationUnpaid($details1));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationUnpaid($details1));
                    }
                }
            }

            $addtionalDelegates = AdditionalDelegates::where('main_delegate_id', $this->currentMainDelegateId)->get();
            if (!empty($addtionalDelegates)) {
                foreach ($addtionalDelegates as $additionalDelegate) {

                    $transaction = Transactions::where('delegate_id', $additionalDelegate->id)->where('delegate_type', "sub")->first();

                    $lastDigit = 1000 + intval($transaction->id);
                    $tempTransactionId = $this->event->year . "$getEventcode" . "$lastDigit";


                    $combinedStringPrintSub = "gpca@reg" . ',' . $this->event->id . ',' . $this->event->category . ',' . $additionalDelegate->id . ',' . 'sub';
                    $finalCryptStringPrintSub = base64_encode($combinedStringPrintSub);
                    $qrCodeForPrintSub = 'ca' . $finalCryptStringPrintSub . 'gp';

                    $details1 = [
                        'name' => $additionalDelegate->salutation . " " . $additionalDelegate->first_name . " " . $additionalDelegate->middle_name . " " . $additionalDelegate->last_name,
                        'eventLink' => $this->event->link,
                        'eventName' => $this->event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $this->event->location,
                        'eventCategory' => $this->event->category,
                        'eventYear' => $this->event->year,

                        'accessType' => $this->accessType,
                        'jobTitle' => $additionalDelegate->job_title,
                        'companyName' => $this->companyName,
                        'badgeType' => $additionalDelegate->badge_type,
                        'amountPaid' => 0,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'earlyBirdValidityDate' => $earlyBirdValidityDate->format('jS F'),
                        'badgeLink' => env('APP_URL') . "/" . $this->event->category . "/" . $this->event->id . "/view-badge" . "/" . "sub" . "/" . $additionalDelegate->id,
                        'qrCodeForPrint' => $qrCodeForPrintSub,
                    ];

                    $isSubFree = false;

                    $promoCode = PromoCodes::where('event_id', $this->event->id)->where('promo_code', $additionalDelegate->pcode_used)->first();

                    if ($promoCode != null) {
                        if ($promoCode->discount_type == "percentage") {
                            $tempTotalNetAmount = $this->finalUnitPrice - ($this->finalUnitPrice * ($promoCode->discount / 100));
                        } else if ($promoCode->discount_type == "price") {
                            $tempTotalNetAmount = $this->finalUnitPrice - $promoCode->discount;
                        } else {
                            $tempTotalNetAmount = $promoCode->new_rate;
                        }

                        if ($tempTotalNetAmount == 0) {
                            $isSubFree = true;
                        }
                    } else {
                        $isSubFree = false;
                    }


                    if ($isSubFree) {
                        try {
                            Mail::to($additionalDelegate->email_address)->cc($this->ccEmailNotif)->send(new RegistrationFree($details1));
                            AdditionalDelegates::find($additionalDelegate->id)->fill([
                                'registration_confirmation_sent_count' => 1,
                                'registration_confirmation_sent_datetime' => Carbon::now(),
                            ])->save();
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationFree($details1));
                        }
                    } else {
                        try {
                            Mail::to($additionalDelegate->email_address)->cc($this->ccEmailNotif)->send(new RegistrationUnpaid($details1));
                            AdditionalDelegates::find($additionalDelegate->id)->fill([
                                'registration_confirmation_sent_count' => 1,
                                'registration_confirmation_sent_datetime' => Carbon::now(),
                            ])->save();
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationUnpaid($details1));
                        }
                    }
                }
            }
        }
        return redirect()->route('register.success.view', ['eventCategory' => $this->event->category, 'eventId' => $this->event->id, 'eventYear' => $this->event->year, 'mainDelegateId' => $this->currentMainDelegateId]);
    }

    public function btClicked()
    {
        $this->paymentMethodError = null;
        if ($this->paymentMethod == 'creditCard') {
            $this->paymentMethod = 'bankTransfer';
        } else if ($this->paymentMethod == 'bankTransfer') {
            $this->paymentMethod = null;
        } else {
            $this->paymentMethod = 'bankTransfer';
        }
    }

    public function ccClicked()
    {
        $this->paymentMethodError = null;
        if ($this->paymentMethod == 'bankTransfer') {
            $this->paymentMethod = 'creditCard';
        } else if ($this->paymentMethod == 'creditCard') {
            $this->paymentMethod = null;
        } else {
            $this->paymentMethod = 'creditCard';
        }
    }

    public function setSessionCC()
    {
        $apiEndpoint = env('MERCHANT_API_URL');
        $merchantId = env('MERCHANT_ID');
        $authPass = env('MERCHANT_AUTH_PASSWORD');

        // GET SESSION ID
        $clientGetSession = new Client();
        $responseGetSession = $clientGetSession->request('POST', $apiEndpoint . '/session', [
            'auth' => [
                'merchant.' . $merchantId,
                $authPass
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);
        $bodyGetSession = $responseGetSession->getBody()->getContents();
        $dataGetSession = json_decode($bodyGetSession, true);
        if ($dataGetSession['result'] == "SUCCESS") {
            $this->sessionId =  $dataGetSession['session']['id'];
            Session::put('sessionId', $dataGetSession['session']['id']);

            // UPDATE SESSION
            $clientUpdateSession = new Client();
            $responseUpdateSession = $clientUpdateSession->request('PUT', $apiEndpoint . '/session/' . $this->sessionId, [
                'auth' => [
                    'merchant.' . $merchantId,
                    $authPass
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    "order" => [
                        'amount' => $this->finalTotal,
                        // 'amount' => 0.27,
                        'currency' => 'USD',
                    ],
                ]
            ]);
            $bodyUpdateSession = $responseUpdateSession->getBody()->getContents();
            $dataUpdateSession = json_decode($bodyUpdateSession, true);

            $this->dispatchBrowserEvent('swal:remove-registration-loading-screen');

            if ($dataUpdateSession['session']['updateStatus'] == "SUCCESS") {
                $this->cardDetails = true;
            } else {
                $this->cardDetails = false;
            }
        }
    }

    public function initiateAuthenticationCC()
    {
        $apiEndpoint = env('MERCHANT_API_URL');
        $merchantId = env('MERCHANT_ID');
        $authPass = env('MERCHANT_AUTH_PASSWORD');

        $generateUniqId = substr(uniqid(), -4);

        $this->transactionId = $this->orderId . "$generateUniqId";

        $clientInitiateAuth = new Client();
        $responseInitiateAuth = $clientInitiateAuth->request('PUT', $apiEndpoint . '/order/' . $this->orderId . '/transaction/' . $this->transactionId, [
            'auth' => [
                'merchant.' . $merchantId,
                $authPass
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                "session" => [
                    'id' => $this->sessionId,
                ],
                "authentication" => [
                    "acceptVersions" => "3DS1,3DS2",
                    "channel" => "PAYER_BROWSER",
                    "purpose" => "PAYMENT_TRANSACTION"
                ],
                "correlationId" => "test",
                "apiOperation" => "INITIATE_AUTHENTICATION"
            ],
        ]);
        $bodyInitiateAuth = $responseInitiateAuth->getBody()->getContents();
        $dataInitiateAuth = json_decode($bodyInitiateAuth, true);

        if (
            $dataInitiateAuth['response']['gatewayCode'] == "AUTHENTICATION_IN_PROGRESS" &&
            $dataInitiateAuth['response']['gatewayRecommendation'] == "PROCEED" &&
            $dataInitiateAuth['transaction']['authenticationStatus'] == "AUTHENTICATION_AVAILABLE" &&
            $dataInitiateAuth['transaction']['type'] == "AUTHENTICATION"
        ) {
            $clientAuthPayer = new Client();
            $appUrl = env('APP_URL');
            $responseAuthPayer = $clientAuthPayer->request('PUT', $apiEndpoint . '/order/' . $this->orderId . '/transaction/' . $this->transactionId, [
                'auth' => [
                    'merchant.' . $merchantId,
                    $authPass
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    "session" => [
                        'id' => $this->sessionId,
                    ],
                    "authentication" => [
                        "redirectResponseUrl" => "$appUrl/capturePayment?sessionId=$this->sessionId&mainDelegateId=$this->currentMainDelegateId&registrationFormType=events",
                    ],
                    "correlationId" => "test",
                    "device" =>  [
                        "browser" =>  "MOZILLA",
                        "browserDetails" =>  [
                            "3DSecureChallengeWindowSize" =>  "FULL_SCREEN",
                            "acceptHeaders" =>  "application/json",
                            "colorDepth" =>  24,
                            "javaEnabled" =>  true,
                            "language" =>  "en-US",
                            "screenHeight" =>  1640,
                            "screenWidth" =>  1480,
                            "timeZone" =>  273
                        ],
                        "ipAddress" =>  "127.0.0.1"
                    ],
                    "apiOperation" => "AUTHENTICATE_PAYER",
                ],
            ]);
            $bodyAuthPayer = $responseAuthPayer->getBody()->getContents();
            $dataAuthPayer = json_decode($bodyAuthPayer, true);

            if (
                $dataAuthPayer['response']['gatewayCode'] == "PENDING" &&
                $dataAuthPayer['response']['gatewayRecommendation'] == "PROCEED" &&
                $dataAuthPayer['transaction']['authenticationStatus'] == "AUTHENTICATION_PENDING" &&
                $dataAuthPayer['transaction']['type'] == "AUTHENTICATION"
            ) {
                $this->htmlCodeOTP = $dataAuthPayer['authentication']['redirect']['html'];
                Session::put('paymentStatus', 'pendingOTP');
                Session::put('htmlOTP', $dataAuthPayer['authentication']['redirect']['html']);
                Session::put('orderId', $this->orderId);

                $this->dispatchBrowserEvent('swal:hide-pay-button');
                return redirect()->route('register.otp.view', ['eventCategory' => $this->event->category, 'eventId' => $this->event->id, 'eventYear' => $this->event->year]);
            } else {
                $this->dispatchBrowserEvent('swal:remove-loading-button');
                $this->dispatchBrowserEvent('swal:registration-error-authentication', [
                    'type' => 'error',
                    'message' => 'Authentication Error 2',
                    'text' => 'There was a problem authenticating your card, please try again!'
                ]);
            }
        } else {
            $this->dispatchBrowserEvent('swal:remove-loading-button');
            $this->dispatchBrowserEvent('swal:registration-error-authentication', [
                'type' => 'error',
                'message' => 'Authentication Error 1',
                'text' => 'There was a problem authenticating your card, please try again!'
            ]);
        }
    }

    public function fullMemberClicked()
    {
        $this->companyName = null;
        if ($this->delegatePassType == 'member' || $this->delegatePassType == 'nonMember') {
            $this->delegatePassType = 'fullMember';
        } else if ($this->delegatePassType == 'fullMember') {
            $this->delegatePassType = null;
        } else {
            $this->delegatePassType = 'fullMember';
        }
    }

    public function memberClicked()
    {
        $this->companyName = null;
        if ($this->delegatePassType == 'fullMember' || $this->delegatePassType == 'nonMember') {
            $this->delegatePassType = 'member';
        } else if ($this->delegatePassType == 'member') {
            $this->delegatePassType = null;
        } else {
            $this->delegatePassType = 'member';
        }
    }

    public function nonMemberClicked()
    {
        $this->companyName = null;
        if ($this->delegatePassType == 'fullMember' || $this->delegatePassType == 'member') {
            $this->delegatePassType = 'nonMember';
        } else if ($this->delegatePassType == 'nonMember') {
            $this->delegatePassType = null;
        } else {
            $this->delegatePassType = 'nonMember';
        }
    }

    public function openAddModal()
    {
        $this->showAddDelegateModal = true;
    }

    public function resetAddModalFields()
    {
        $this->subSalutation = null;
        $this->subFirstName = null;
        $this->subMiddleName = null;
        $this->subLastName = null;
        $this->subEmailAddress = null;
        $this->subMobileNumber = null;
        $this->subNationality = null;
        $this->subJobTitle = null;
        $this->subBadgeType = "Delegate";
        $this->promoCodeSuccessSub = null;
        $this->promoCodeFailSub = null;
        $this->subPromoCode = null;
        $this->subPromoCodeDiscount = null;
        $this->subDiscountType = null;

        $this->emailSubExistingError = null;
        $this->emailSubAlreadyUsedError = null;

        $this->subCountry = null;
        $this->subDelegateInterests = [];
    }

    public function closeAddModal()
    {
        $this->showAddDelegateModal = false;
        $this->resetAddModalFields();
    }

    public function saveAdditionalDelegate()
    {
        $this->emailSubAlreadyUsedError = null;
        $this->emailSubExistingError = null;

        $this->validate(
            [
                'subFirstName' => 'required',
                'subLastName' => 'required',
                'subEmailAddress' => 'required|email',
                'subMobileNumber' => 'required',
                'subNationality' => 'required',
                'subJobTitle' => 'required',
                'subBadgeType' => 'required',
                'subCountry' => 'required',
            ],
            [
                'subFirstName.required' => "First name is required",
                'subLastName.required' => "Last name is required",
                'subEmailAddress.required' => "Email address is required",
                'subEmailAddress.email' => "Email address must be a valid email",
                'subMobileNumber.required' => "Mobile number is required",
                'subNationality.required' => "Nationality is required",
                'subJobTitle.required' => "Job title is required",
                'subBadgeType.required' => "Badge type is required",
                'subCountry.required' => "Country is required",
            ]
        );

        if ($this->checkEmailIfUsedAlreadySub($this->subEmailAddress)) {
            $this->emailSubAlreadyUsedError = "You already used this email!";
        } else {
            if ($this->checkEmailIfExistsInDatabase($this->subEmailAddress)) {
                $this->emailSubAlreadyUsedError = null;
                $this->emailSubExistingError = "Email is already registered, please use another email!";
            } else {
                $this->emailSubAlreadyUsedError = null;
                $this->emailSubExistingError = null;

                $uuid = Str::uuid();
                array_push($this->additionalDelegates, [
                    'subDelegateId' => $uuid->toString(),
                    'subSalutation' => $this->subSalutation,
                    'subFirstName' => $this->subFirstName,
                    'subMiddleName' => $this->subMiddleName,
                    'subLastName' => $this->subLastName,
                    'subEmailAddress' => $this->subEmailAddress,
                    'subMobileNumber' => $this->subMobileNumber,
                    'subNationality' => $this->subNationality,
                    'subJobTitle' => $this->subJobTitle,
                    'subBadgeType' => $this->subBadgeType,
                    'subPromoCode' => ($this->promoCodeSuccessSub != null) ? $this->subPromoCode : null,
                    'subPromoCodeDiscount' => $this->subPromoCodeDiscount,
                    'subDiscountType' => $this->subDiscountType,
                    'promoCodeSuccessSub' => $this->promoCodeSuccessSub,
                    'promoCodeFailSub' => $this->promoCodeFailSub,
                    'subCountry' => $this->subCountry,
                    'subDelegateInterests' => $this->subDelegateInterests,
                ]);

                $this->resetAddModalFields();
                $this->showAddDelegateModal = false;
            }
        }
    }

    public function openEditModal($subDelegateId)
    {
        $this->showEditDelegateModal = true;
        foreach ($this->additionalDelegates as $additionalDelegate) {
            if ($additionalDelegate['subDelegateId'] == $subDelegateId) {
                $this->subIdEdit = $additionalDelegate['subDelegateId'];
                $this->subSalutationEdit = $additionalDelegate['subSalutation'];
                $this->subFirstNameEdit = $additionalDelegate['subFirstName'];
                $this->subMiddleNameEdit = $additionalDelegate['subMiddleName'];
                $this->subLastNameEdit = $additionalDelegate['subLastName'];
                $this->subEmailAddressEdit = $additionalDelegate['subEmailAddress'];
                $this->subMobileNumberEdit = $additionalDelegate['subMobileNumber'];
                $this->subNationalityEdit = $additionalDelegate['subNationality'];
                $this->subJobTitleEdit = $additionalDelegate['subJobTitle'];
                $this->subBadgeTypeEdit = $additionalDelegate['subBadgeType'];
                $this->subPromoCodeEdit = $additionalDelegate['subPromoCode'];
                $this->subPromoCodeDiscountEdit = $additionalDelegate['subPromoCodeDiscount'];
                $this->subDiscountTypeEdit = $additionalDelegate['subDiscountType'];
                $this->promoCodeSuccessSubEdit = $additionalDelegate['promoCodeSuccessSub'];
                $this->promoCodeFailSubEdit = $additionalDelegate['promoCodeFailSub'];

                $this->subCountryEdit = $additionalDelegate['subCountry'];
                $this->subDelegateInterestsEdit = $additionalDelegate['subDelegateInterests'];
            }
        }
    }

    public function resetEditModalFields()
    {
        $this->subIdEdit = null;
        $this->subSalutationEdit = null;
        $this->subFirstNameEdit = null;
        $this->subMiddleNameEdit = null;
        $this->subLastNameEdit = null;
        $this->subEmailAddressEdit = null;
        $this->subMobileNumberEdit = null;
        $this->subNationalityEdit = null;
        $this->subJobTitleEdit = null;
        $this->subBadgeTypeEdit = "Delegate";
        $this->subPromoCodeEdit = null;
        $this->subPromoCodeDiscountEdit = null;
        $this->subDiscountTypeEdit = null;
        $this->promoCodeSuccessSubEdit = null;
        $this->promoCodeFailSubEdit = null;

        $this->emailSubExistingError = null;
        $this->emailSubAlreadyUsedError = null;

        $this->subCountryEdit = null;
        $this->subDelegateInterestsEdit = [];
    }

    public function closeEditModal()
    {
        $this->showEditDelegateModal = false;
        $this->resetEditModalFields();
    }


    public function removeAdditionalDelegate($subDelegateId)
    {
        $arrayTemp = array_filter($this->additionalDelegates, function ($item) use ($subDelegateId) {
            return $item['subDelegateId'] != $subDelegateId;
        });

        $this->additionalDelegates = [];

        foreach ($arrayTemp as $delegate) {
            array_push($this->additionalDelegates, $delegate);
        }
    }

    public function editAdditionalDelegate($subDelegateId)
    {
        $this->emailSubAlreadyUsedError = null;
        $this->emailSubExistingError = null;

        $this->validate(
            [
                'subFirstNameEdit' => 'required',
                'subLastNameEdit' => 'required',
                'subEmailAddressEdit' => 'required|email',
                'subMobileNumberEdit' => 'required',
                'subNationalityEdit' => 'required',
                'subJobTitleEdit' => 'required',
                'subBadgeTypeEdit' => 'required',
                'subCountryEdit' => 'required',
            ],
            [
                'subFirstNameEdit.required' => "First name is required",
                'subLastNameEdit.required' => "Last name is required",
                'subEmailAddressEdit.required' => "Email address is required",
                'subEmailAddressEdit.email' => "Email address must be a valid email",
                'subMobileNumberEdit.required' => "Mobile number is required",
                'subNationalityEdit.required' => "Nationality is required",
                'subJobTitleEdit.required' => "Job title is required",
                'subBadgeTypeEdit.required' => "Badge type is required",
                'subCountryEdit.required' => "Country is required",
            ]
        );

        $tempCheckEmail = false;

        if ($this->subEmailAddressEdit == $this->emailAddress) {
            $tempCheckEmail = true;
        } else {
            foreach ($this->additionalDelegates as $additionalDelegate) {
                if ($additionalDelegate['subDelegateId'] != $subDelegateId) {
                    if ($additionalDelegate['subEmailAddress'] == $this->subEmailAddressEdit) {
                        $tempCheckEmail = true;
                    }
                }
            }
        }

        if ($tempCheckEmail) {
            $this->emailSubAlreadyUsedError = "You already used this email!";
        } else {
            if ($this->checkEmailIfExistsInDatabase($this->subEmailAddressEdit)) {
                $this->emailSubAlreadyUsedError = null;
                $this->emailSubExistingError = "Email is already registered, please use another email!";
            } else {
                $this->emailSubAlreadyUsedError = null;
                $this->emailSubExistingError = null;

                for ($i = 0; $i < count($this->additionalDelegates); $i++) {
                    if ($this->additionalDelegates[$i]['subDelegateId'] == $subDelegateId) {
                        $this->additionalDelegates[$i]['subSalutation'] = $this->subSalutationEdit;
                        $this->additionalDelegates[$i]['subFirstName'] = $this->subFirstNameEdit;
                        $this->additionalDelegates[$i]['subMiddleName'] = $this->subMiddleNameEdit;
                        $this->additionalDelegates[$i]['subLastName'] = $this->subLastNameEdit;
                        $this->additionalDelegates[$i]['subEmailAddress'] = $this->subEmailAddressEdit;
                        $this->additionalDelegates[$i]['subMobileNumber'] = $this->subMobileNumberEdit;
                        $this->additionalDelegates[$i]['subNationality'] = $this->subNationalityEdit;
                        $this->additionalDelegates[$i]['subJobTitle'] = $this->subJobTitleEdit;
                        $this->additionalDelegates[$i]['subBadgeType'] = $this->subBadgeTypeEdit;
                        $this->additionalDelegates[$i]['subPromoCode'] = ($this->promoCodeSuccessSubEdit != null) ? $this->subPromoCodeEdit : null;
                        $this->additionalDelegates[$i]['subPromoCodeDiscount'] = $this->subPromoCodeDiscountEdit;
                        $this->additionalDelegates[$i]['subDiscountType'] = $this->subDiscountTypeEdit;
                        $this->additionalDelegates[$i]['promoCodeSuccessSub'] = $this->promoCodeSuccessSubEdit;
                        $this->additionalDelegates[$i]['promoCodeFailSub'] = $this->promoCodeFailSubEdit;
                        $this->additionalDelegates[$i]['subCountry'] = $this->subCountryEdit;
                        $this->additionalDelegates[$i]['subDelegateInterests'] = $this->subDelegateInterestsEdit;

                        $this->resetEditModalFields();
                        $this->showEditDelegateModal = false;
                    }
                }
            }
        }
    }

    public function applyPromoCodeMain()
    {
        if ($this->promoCode == null) {
            $this->promoCodeFailMain = "Promo code is required.";
        } else {
            $promoCode = PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('active', true)->where('promo_code', $this->promoCode)->first();
            if ($promoCode == null) {
                $this->promoCodeFailMain = "Invalid code";
            } else {
                if ($promoCode->total_usage < $promoCode->number_of_codes) {
                    $validityDateTime = Carbon::parse($promoCode->validity);
                    if (Carbon::now()->lt($validityDateTime)) {
                        $this->promoCodeFailMain = null;
                        $this->badgeType = $promoCode->badge_type;
                        $this->discountType = $promoCode->discount_type;

                        if ($promoCode->discount_type == "percentage") {
                            $this->promoCodeSuccessMain = "$promoCode->discount% discount will be availed upon submitting the registration";
                            $this->promoCodeDiscount = $promoCode->discount;
                        } else if ($promoCode->discount_type == "price") {
                            $this->promoCodeSuccessMain = "$$promoCode->discount discount will be availed upon submitting the registration";
                            $this->promoCodeDiscount = $promoCode->discount;
                        } else {
                            $this->promoCodeSuccessMain = "promo code applied";
                            $this->promoCodeDiscount = $promoCode->new_rate;
                        }
                    } else {
                        $this->promoCodeFailMain = "Code is expired already";
                    }
                } else {
                    $this->promoCodeFailMain = "Code has reached its capacity";
                }
            }
        }
    }

    public function removePromoCodeMain()
    {
        $this->promoCode = null;
        $this->promoCodeDiscount = null;
        $this->discountType = null;
        $this->promoCodeFailMain = null;
        $this->promoCodeSuccessMain = null;
    }

    public function applyPromoCodeSub()
    {
        if ($this->subPromoCode == null) {
            $this->promoCodeFailSub = "Promo code is required.";
        } else {
            $promoCode = PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('active', true)->where('promo_code', $this->subPromoCode)->first();
            if ($promoCode == null) {
                $this->promoCodeFailSub = "Invalid code";
            } else {
                if ($promoCode->total_usage < $promoCode->number_of_codes) {
                    $validityDateTime = Carbon::parse($promoCode->validity);
                    if (Carbon::now()->lt($validityDateTime)) {
                        $this->subBadgeType = $promoCode->badge_type;
                        $this->subDiscountType = $promoCode->discount_type;

                        if ($promoCode->discount_type == "percentage") {
                            $this->promoCodeSuccessSub = "$promoCode->discount% discount will be availed upon submitting the registration";
                            $this->subPromoCodeDiscount = $promoCode->discount;
                        } else if ($promoCode->discount_type == "price") {
                            $this->promoCodeSuccessSub = "$$promoCode->discount discount will be availed upon submitting the registration";
                            $this->subPromoCodeDiscount = $promoCode->discount;
                        } else {
                            $this->promoCodeSuccessSub = "promo code applied";
                            $this->subPromoCodeDiscount = $promoCode->new_rate;
                        }
                        $this->promoCodeFailSub = null;
                    } else {
                        $this->promoCodeFailSub = "Code is expired already";
                    }
                } else {
                    $this->promoCodeFailSub = "Code has reached its capacity";
                }
            }
        }
    }

    public function removePromoCodeSub()
    {
        $this->subPromoCode = null;
        $this->subPromoCodeDiscount = null;
        $this->subDiscountType = null;
        $this->subBadgeType = "Delegate";
        $this->promoCodeFailSub = null;
        $this->promoCodeSuccessSub = null;
    }

    public function applyPromoCodeSubEdit()
    {
        if ($this->subPromoCodeEdit == null) {
            $this->promoCodeFailSubEdit = "Promo code is required.";
        } else {
            $promoCode = PromoCodes::where('event_id', $this->event->id)->where('event_category', $this->event->category)->where('active', true)->where('promo_code', $this->subPromoCodeEdit)->first();
            if ($promoCode == null) {
                $this->promoCodeFailSubEdit = "Invalid code";
            } else {
                if ($promoCode->total_usage < $promoCode->number_of_codes) {
                    $validityDateTime = Carbon::parse($promoCode->validity);
                    if (Carbon::now()->lt($validityDateTime)) {
                        $this->subBadgeTypeEdit = $promoCode->badge_type;
                        $this->subDiscountTypeEdit = $promoCode->discount_type;

                        if ($promoCode->discount_type == "percentage") {
                            $this->promoCodeSuccessSubEdit = "$promoCode->discount% discount will be availed upon submitting the registration";
                            $this->subPromoCodeDiscountEdit = $promoCode->discount;
                        } else if ($promoCode->discount_type == "price") {
                            $this->promoCodeSuccessSubEdit = "$$promoCode->discount discount will be availed upon submitting the registration";
                            $this->subPromoCodeDiscountEdit = $promoCode->discount;
                        } else {
                            $this->promoCodeSuccessSubEdit = "promo code applied";
                            $this->subPromoCodeDiscountEdit = $promoCode->new_rate;
                        }
                        $this->promoCodeFailSubEdit = null;
                    } else {
                        $this->promoCodeFailSubEdit = "Code is expired already";
                    }
                } else {
                    $this->promoCodeFailSubEdit = "Code has reached its capacity";
                }
            }
        }
    }

    public function removePromoCodeSubEdit()
    {
        $this->subPromoCodeEdit = null;
        $this->subPromoCodeDiscountEdit = null;
        $this->subDiscountTypeEdit = null;
        $this->subBadgeTypeEdit = "Delegate";
        $this->promoCodeFailSubEdit = null;
        $this->promoCodeSuccessSubEdit = null;
    }

    public function checkEmailIfExistsInDatabase($emailAddress)
    {
        $allDelegates = Transactions::where('event_id', $this->event->id)->where('event_category', $this->event->category)->get();

        $countMainDelegate = 0;
        $countSubDelegate = 0;

        if (!$allDelegates->isEmpty()) {
            foreach ($allDelegates as $delegate) {
                if ($delegate->delegate_type == "main") {
                    $mainDelegate = MainDelegates::where('id', $delegate->delegate_id)->where('email_address', $emailAddress)->where('registration_status', '!=', 'droppedOut')->first();
                    if ($mainDelegate != null) {
                        $countMainDelegate++;
                    }
                } else {
                    $subDelegate = AdditionalDelegates::where('id', $delegate->delegate_id)->where('email_address', $emailAddress)->first();
                    if ($subDelegate != null) {
                        $registrationStatsMain = MainDelegates::where('id', $subDelegate->main_delegate_id)->value('registration_status');
                        if ($registrationStatsMain != "droppedOut") {
                            $countSubDelegate++;
                        }
                    }
                }
            }
        }

        if ($countMainDelegate == 0 && $countSubDelegate == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function checkEmailIfUsedAlreadyMain($emailAddress)
    {
        if (count($this->additionalDelegates) == 0) {
            return false;
        } else {
            foreach ($this->additionalDelegates as $additionalDelegate) {
                if ($emailAddress == $additionalDelegate['subEmailAddress']) {
                    return true;
                    break;
                }
            }
        }
    }

    public function checkEmailIfUsedAlreadySub($emailAddress)
    {
        if ($this->emailAddress == $emailAddress) {
            return true;
        } else {
            if (count($this->additionalDelegates) == 0) {
                return false;
            } else {
                foreach ($this->additionalDelegates as $additionalDelegate) {
                    if ($emailAddress == $additionalDelegate['subEmailAddress']) {
                        return true;
                        break;
                    }
                }
            }
        }
    }
}
