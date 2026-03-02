<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypes;
use App\Http\Livewire\PromoCode;
use App\Models\AdditionalDelegate;
use App\Models\AdditionalSpouse;
use App\Models\AdditionalVisitor;
use App\Models\AwardsAdditionalParticipant;
use App\Models\AwardsMainParticipant;
use App\Models\Event;
use App\Models\EventRegistrationType;
use App\Models\MainDelegate;
use App\Models\MainSpouse;
use App\Models\MainVisitor;
use App\Models\PrintedBadge;
use App\Models\PromoCode as ModelsPromoCode;
use App\Models\PromoCodeAddtionalBadgeType;
use App\Models\RccAwardsAdditionalParticipant;
use App\Models\RccAwardsMainParticipant;
use App\Models\ScannedDelegate;
use App\Models\ScannedVisitor;
use App\Models\VisitorPrintedBadge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{

    // =========================================================
    //                       RENDER VIEWS
    // =========================================================

    public function manageEventView()
    {
        $events = Event::orderBy('event_start_date', 'desc')->get();
        $finalEvents = array();

        if ($events->isNotEmpty()) {
            foreach ($events as $event) {
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M Y') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');

                array_push($finalEvents, [
                    'eventId' => $event->id,
                    'eventLogo' => $event->logo,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDate' => $eventFormattedDate,
                    'eventLocation' => $event->location,
                    'eventDescription' => $event->description,
                ]);
            }
        }

        return view('admin.events.home.events', [
            "pageTitle" => "Manage Event",
            "finalEvents" => $finalEvents,
        ]);
    }

    public function addEventView()
    {
        return view('admin.events.home.add.add_event', [
            "pageTitle" => "Add Event",
            "eventCategories" => config('app.eventCategories'),
        ]);
    }

    public function eventEditView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

            return view('admin.events.home.edit.edit_event', [
                "pageTitle" => "Edit Event",
                "eventCategories" => config('app.eventCategories'),
                "event" => $event,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function eventDashboardView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

            if ($eventCategory == "AFS") {
                $finalData = $this->eventDasbhoardSpouseData($eventId);
                return view('admin.events.dashboard.spouse.dashboard', [
                    "pageTitle" => "Event Dashboard",
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                    "event" => $event,
                    "finalData" => $finalData,
                ]);
            } else if ($eventCategory == "AFV") {
                $finalData = $this->eventDasbhoardVisitorData($eventId);
                return view('admin.events.dashboard.visitor.dashboard', [
                    "pageTitle" => "Event Dashboard",
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                    "event" => $event,
                    "finalData" => $finalData,
                ]);
            } else if ($eventCategory == "RCCA") {
                $finalData = $this->eventDasbhoardRccAwardsData($eventId);
                return view('admin.events.dashboard.rcca.dashboard', [
                    "pageTitle" => "Event Dashboard",
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                    "event" => $event,
                    "finalData" => $finalData,
                ]);
            } else if ($eventCategory == "SCEA") {
                $finalData = $this->eventDasbhoardAwardsData($eventId);
                return view('admin.events.dashboard.awards.dashboard', [
                    "pageTitle" => "Event Dashboard",
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                    "event" => $event,
                    "finalData" => $finalData,
                ]);
            } else {
                $finalData = $this->eventDasbhoardEventsData($eventId);
                return view('admin.events.dashboard.dashboard', [
                    "pageTitle" => "Event Dashboard",
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                    "event" => $event,
                    "finalData" => $finalData,
                ]);
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function eventDasbhoardEventsData($eventId)
    {
        $totalConfirmedDelegates = 0;
        $totalDelegates = 0;
        $totalRegisteredToday = 0;
        $delegateBadgePrinted = 0;
        $duplicateBadgePrinted = 0;
        $totalBadgePrinted = 0;
        $totalPaidToday = 0;
        $totalAmountPaidToday = 0;
        $totalAmountPaid = 0;
        $totalFullMember = 0;
        $totalMember = 0;
        $totalNonMember = 0;
        $totalBankTransfer = 0;
        $totalCreditCard = 0;
        $totalPaid = 0;
        $totalFree = 0;
        $totalUnpaid = 0;
        $totalRefunded = 0;
        $totalOnline = 0;
        $totalImported = 0;
        $totalOnsite = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalDroppedOut = 0;
        $totalCancelled = 0;
        $totalFEAttendee = 0;
        $totalWOAttendee = 0;
        $totalCOAttendee = 0;
        $arrayCountryTotal = array();
        $arrayCompanyTotal = array();
        $arrayRegistrationTypeTotal = array();

        $dateToday = Carbon::now();
        $noRefund = 0;

        $mainDelegates = MainDelegate::where('event_id', $eventId)->get();

        if ($mainDelegates->isNotEmpty()) {
            foreach ($mainDelegates as $mainDelegate) {

                $delegateRegisteredDate = Carbon::parse($mainDelegate->registered_date_time);
                if ($dateToday->isSameDay($delegateRegisteredDate)) {
                    $totalRegisteredToday++;
                }

                if ($mainDelegate->delegate_replaced_by_id == null && (!$mainDelegate->delegate_refunded)) {
                    $totalDelegates++;

                    if ($mainDelegate->registration_status == "confirmed") {
                        $totalConfirmedDelegates++;
                        if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                            $totalCOAttendee++;
                        } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                            $totalWOAttendee++;
                        } else {
                            $totalFEAttendee++;
                        }


                        if ($mainDelegate->pass_type == "fullMember") {
                            $totalFullMember++;
                        } else if ($mainDelegate->pass_type == "member") {
                            $totalMember++;
                        } else {
                            $totalNonMember++;
                        }

                        if ($mainDelegate->mode_of_payment == "creditCard") {
                            $totalCreditCard++;
                        } else {
                            $totalBankTransfer++;
                        }

                        if ($mainDelegate->payment_status == "paid") {
                            $delegatePaidDate = Carbon::parse($mainDelegate->paid_date_time);
                            if ($dateToday->isSameDay($delegatePaidDate)) {
                                $totalPaidToday++;
                            }

                            $noRefund++;
                            $totalPaid++;
                        } else if ($mainDelegate->payment_status == "free") {
                            $totalFree++;
                        } else if ($mainDelegate->payment_status == "unpaid") {
                            $totalUnpaid++;
                        } else {
                        }


                        if ($mainDelegate->registration_method == "online") {
                            $totalOnline++;
                        } else if ($mainDelegate->registration_method == "imported") {
                            $totalImported++;
                        } else {
                            $totalOnsite++;
                        }

                        if ($this->checkIfCountryExist($mainDelegate->company_country, $arrayCountryTotal)) {
                            foreach ($arrayCountryTotal as $index => $country) {
                                if ($country['name'] == $mainDelegate->company_country) {
                                    $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                }
                            }
                        } else {
                            array_push($arrayCountryTotal, [
                                'name' => $mainDelegate->company_country,
                                'total' => 1,
                            ]);
                        }



                        if ($this->checkIfCompanyExist($mainDelegate->company_name, $arrayCompanyTotal)) {
                            foreach ($arrayCompanyTotal as $index => $company) {
                                if ($company['name'] == $mainDelegate->company_name) {
                                    $arrayCompanyTotal[$index]['total'] = $company['total'] + 1;
                                }
                            }
                        } else {
                            array_push($arrayCompanyTotal, [
                                'name' => $mainDelegate->company_name,
                                'total' => 1,
                            ]);
                        }


                        if ($this->checkIfRegistrationTypeExist($mainDelegate->badge_type, $arrayRegistrationTypeTotal)) {
                            foreach ($arrayRegistrationTypeTotal as $index => $registrationType) {
                                if ($registrationType['name'] == $mainDelegate->badge_type) {
                                    $arrayRegistrationTypeTotal[$index]['total'] = $registrationType['total'] + 1;
                                }
                            }
                        } else {
                            array_push($arrayRegistrationTypeTotal, [
                                'name' => $mainDelegate->badge_type,
                                'total' => 1,
                            ]);
                        }
                    } else if ($mainDelegate->registration_status == "pending") {
                        $totalPending++;
                    } else if ($mainDelegate->registration_status == "droppedOut") {
                        $totalDroppedOut++;
                    }
                } else {
                    if ($mainDelegate->delegate_refunded) {
                        $totalRefunded++;
                    }

                    if ($mainDelegate->delegate_cancelled) {
                        $totalCancelled++;
                    }
                }

                $additionalDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegate->id)->get();
                if ($additionalDelegates->isNotEmpty()) {
                    foreach ($additionalDelegates as $additionalDelegate) {
                        if ($additionalDelegate->delegate_replaced_by_id == null && (!$additionalDelegate->delegate_refunded)) {
                            $totalDelegates++;

                            if ($mainDelegate->registration_status == "confirmed") {
                                $totalConfirmedDelegates++;
                                
                                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                                    $totalCOAttendee++;
                                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                                    $totalWOAttendee++;
                                } else {
                                    $totalFEAttendee++;
                                }

                                if ($mainDelegate->pass_type == "fullMember") {
                                    $totalFullMember++;
                                } else if ($mainDelegate->pass_type == "member") {
                                    $totalMember++;
                                } else {
                                    $totalNonMember++;
                                }

                                if ($mainDelegate->mode_of_payment == "creditCard") {
                                    $totalCreditCard++;
                                } else {
                                    $totalBankTransfer++;
                                }


                                if ($mainDelegate->payment_status == "paid") {
                                    $delegatePaidDate = Carbon::parse($mainDelegate->paid_date_time);
                                    if ($dateToday->isSameDay($delegatePaidDate)) {
                                        $totalPaidToday++;
                                    }

                                    $noRefund++;
                                    $totalPaid++;
                                } else if ($mainDelegate->payment_status == "free") {
                                    $totalFree++;
                                } else if ($mainDelegate->payment_status == "unpaid") {
                                    $totalUnpaid++;
                                } else {
                                }

                                if ($mainDelegate->registration_method == "online") {
                                    $totalOnline++;
                                } else if ($mainDelegate->registration_method == "imported") {
                                    $totalImported++;
                                } else {
                                    $totalOnsite++;
                                }


                                if ($mainDelegate->registration_status == "confirmed") {
                                    $totalConfirmed++;
                                } else if ($mainDelegate->registration_status == "pending") {
                                    $totalPending++;
                                } else if ($mainDelegate->registration_status == "droppedOut") {
                                    $totalDroppedOut++;
                                }




                                if ($this->checkIfCountryExist($mainDelegate->company_country, $arrayCountryTotal)) {
                                    foreach ($arrayCountryTotal as $index => $country) {
                                        if ($country['name'] == $mainDelegate->company_country) {
                                            $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                        }
                                    }
                                } else {
                                    array_push($arrayCountryTotal, [
                                        'name' => $mainDelegate->company_country,
                                        'total' => 1,
                                    ]);
                                }


                                if ($this->checkIfCompanyExist($mainDelegate->company_name, $arrayCompanyTotal)) {
                                    foreach ($arrayCompanyTotal as $index => $company) {
                                        if ($company['name'] == $mainDelegate->company_name) {
                                            $arrayCompanyTotal[$index]['total'] = $company['total'] + 1;
                                        }
                                    }
                                } else {
                                    array_push($arrayCompanyTotal, [
                                        'name' => $mainDelegate->company_name,
                                        'total' => 1,
                                    ]);
                                }


                                if ($this->checkIfRegistrationTypeExist($additionalDelegate->badge_type, $arrayRegistrationTypeTotal)) {
                                    foreach ($arrayRegistrationTypeTotal as $index => $registrationType) {
                                        if ($registrationType['name'] == $additionalDelegate->badge_type) {
                                            $arrayRegistrationTypeTotal[$index]['total'] = $registrationType['total'] + 1;
                                        }
                                    }
                                } else {
                                    array_push($arrayRegistrationTypeTotal, [
                                        'name' => $additionalDelegate->badge_type,
                                        'total' => 1,
                                    ]);
                                }
                            }
                        } else {
                            if ($additionalDelegate->delegate_refunded) {
                                $totalRefunded++;
                            }

                            if ($additionalDelegate->delegate_cancelled) {
                                $totalCancelled++;
                            }
                        }
                    }
                }

                if ($noRefund > 0 && $mainDelegate->payment_status == "paid") {
                    $totalAmountPaid += $mainDelegate->total_amount;

                    $delegatePaidDate = Carbon::parse($mainDelegate->paid_date_time);
                    if ($dateToday->isSameDay($delegatePaidDate)) {
                        $totalAmountPaidToday += $mainDelegate->total_amount;
                    }
                }
            }
        }

        $printedBadgesArray = array();
        $printedBadges = PrintedBadge::where('event_id', $eventId)->get();

        foreach ($printedBadges as $printedBadge) {
            $finalString = $printedBadge->delegate_id . $printedBadge->delegate_type;
            array_push($printedBadgesArray, $finalString);
        }

        $uniquePrintedBadgesArray = array_unique($printedBadgesArray);
        $finalPrintedBadgesArray = array_diff_key($printedBadgesArray, $uniquePrintedBadgesArray);

        $delegateBadgePrinted = count($printedBadges) - count($finalPrintedBadgesArray);
        $duplicateBadgePrinted = count($finalPrintedBadgesArray);
        $totalBadgePrinted = count($printedBadges);




        $scannedDelegatesArray = array();
        $scannedDelegates = ScannedDelegate::where('event_id', $eventId)->get();

        foreach ($scannedDelegates as $scannedDelegate) {
            $finalString = $scannedDelegate->delegate_id . $scannedDelegate->delegate_type;
            array_push($scannedDelegatesArray, $finalString);
        }

        $uniqueScannedDelegateArray = array_unique($scannedDelegatesArray);
        $finalScannedDelegateArray = array_diff_key($scannedDelegatesArray, $uniqueScannedDelegateArray);

        $delegateBadgeScanned = count($scannedDelegates) - count($finalScannedDelegateArray);
        $duplicateBadgeScanned = count($finalScannedDelegateArray);
        $totalBadgeScanned = count($scannedDelegates);

        $finalData = [
            'totalConfirmedDelegates' => $totalConfirmedDelegates,
            'totalDelegates' => $totalDelegates,
            'totalRegisteredToday' => $totalRegisteredToday,

            'delegateBadgePrinted' => $delegateBadgePrinted,
            'duplicateBadgePrinted' => $duplicateBadgePrinted,
            'totalBadgePrinted' => $totalBadgePrinted,

            'delegateBadgeScanned' => $delegateBadgeScanned,
            'duplicateBadgeScanned' => $duplicateBadgeScanned,
            'totalBadgeScanned' => $totalBadgeScanned,

            'totalPaidToday' => $totalPaidToday,
            'totalAmountPaidToday' => $totalAmountPaidToday,
            'totalAmountPaid' => $totalAmountPaid,

            'totalFEAttendee' => $totalFEAttendee,
            'totalCOAttendee' => $totalCOAttendee,
            'totalWOAttendee' => $totalWOAttendee,

            'passType' => [$totalFullMember, $totalMember, $totalNonMember],
            'paymentStatus' => [$totalPaid, $totalFree, $totalUnpaid, $totalRefunded],
            'registrationStatus' => [$totalConfirmed, $totalPending, $totalDroppedOut, $totalCancelled],
            'registrationMethod' => [$totalOnline, $totalImported, $totalOnsite],
            'paymentMethod' => [$totalCreditCard, $totalBankTransfer],

            'arrayCountryTotal' => $arrayCountryTotal,
            'arrayCompanyTotal' => $arrayCompanyTotal,
            'arrayRegistrationTypeTotal' => $arrayRegistrationTypeTotal,
        ];

        return $finalData;
    }


    public function eventDasbhoardSpouseData($eventId)
    {
        $totalConfirmedSpouses = 0;
        $totalSpouses = 0;
        $totalRegisteredToday = 0;
        $totalPaidToday = 0;
        $totalAmountPaidToday = 0;
        $totalAmountPaid = 0;
        $totalBankTransfer = 0;
        $totalCreditCard = 0;
        $totalPaid = 0;
        $totalFree = 0;
        $totalUnpaid = 0;
        $totalRefunded = 0;
        $totalOnline = 0;
        $totalImported = 0;
        $totalOnsite = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalDroppedOut = 0;
        $totalCancelled = 0;
        $arrayCountryTotal = array();

        $dateToday = Carbon::now();
        $noRefund = 0;

        $mainSpouses = MainSpouse::where('event_id', $eventId)->get();

        if ($mainSpouses->isNotEmpty()) {
            foreach ($mainSpouses as $mainSpouse) {

                $spouseRegisteredDate = Carbon::parse($mainSpouse->registered_date_time);
                if ($dateToday->isSameDay($spouseRegisteredDate)) {
                    $totalRegisteredToday++;
                }

                if ($mainSpouse->spouse_replaced_by_id == null && (!$mainSpouse->spouse_refunded)) {
                    if ($mainSpouse->registration_status == "confirmed") {
                        $totalConfirmedSpouses++;
                    }

                    $totalSpouses++;

                    if ($mainSpouse->mode_of_payment == "creditCard") {
                        $totalCreditCard++;
                    } else {
                        $totalBankTransfer++;
                    }

                    if ($mainSpouse->payment_status == "paid") {
                        $spousePaidDate = Carbon::parse($mainSpouse->paid_date_time);
                        if ($dateToday->isSameDay($spousePaidDate)) {
                            $totalPaidToday++;
                        }

                        $noRefund++;
                        $totalPaid++;
                    } else if ($mainSpouse->payment_status == "free") {
                        $totalFree++;
                    } else if ($mainSpouse->payment_status == "unpaid") {
                        $totalUnpaid++;
                    } else {
                    }


                    if ($mainSpouse->registration_method == "online") {
                        $totalOnline++;
                    } else if ($mainSpouse->registration_method == "imported") {
                        $totalImported++;
                    } else {
                        $totalOnsite++;
                    }


                    if ($mainSpouse->registration_status == "confirmed") {
                        $totalConfirmed++;
                    } else if ($mainSpouse->registration_status == "pending") {
                        $totalPending++;
                    } else if ($mainSpouse->registration_status == "droppedOut") {
                        $totalDroppedOut++;
                    }

                    if ($this->checkIfCountryExist($mainSpouse->country, $arrayCountryTotal)) {
                        foreach ($arrayCountryTotal as $index => $country) {
                            if ($country['name'] == $mainSpouse->country) {
                                $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayCountryTotal, [
                            'name' => $mainSpouse->country,
                            'total' => 1,
                        ]);
                    }
                } else {
                    if ($mainSpouse->spouse_refunded) {
                        $totalRefunded++;
                    }

                    if ($mainSpouse->spouse_cancelled) {
                        $totalCancelled++;
                    }
                }

                $additionalSpouses = AdditionalSpouse::where('main_spouse_id', $mainSpouse->id)->get();
                if ($additionalSpouses->isNotEmpty()) {
                    foreach ($additionalSpouses as $additionalSpouse) {
                        if ($additionalSpouse->spouse_replaced_by_id == null && (!$additionalSpouse->spouse_refunded)) {
                            if ($mainSpouse->registration_status == "confirmed") {
                                $totalConfirmedSpouses++;
                            }

                            $totalSpouses++;

                            if ($mainSpouse->mode_of_payment == "creditCard") {
                                $totalCreditCard++;
                            } else {
                                $totalBankTransfer++;
                            }


                            if ($mainSpouse->payment_status == "paid") {
                                $spousePaidDate = Carbon::parse($mainSpouse->paid_date_time);
                                if ($dateToday->isSameDay($spousePaidDate)) {
                                    $totalPaidToday++;
                                }

                                $noRefund++;
                                $totalPaid++;
                            } else if ($mainSpouse->payment_status == "free") {
                                $totalFree++;
                            } else if ($mainSpouse->payment_status == "unpaid") {
                                $totalUnpaid++;
                            } else {
                            }

                            if ($mainSpouse->registration_method == "online") {
                                $totalOnline++;
                            } else if ($mainSpouse->registration_method == "imported") {
                                $totalImported++;
                            } else {
                                $totalOnsite++;
                            }


                            if ($mainSpouse->registration_status == "confirmed") {
                                $totalConfirmed++;
                            } else if ($mainSpouse->registration_status == "pending") {
                                $totalPending++;
                            } else if ($mainSpouse->registration_status == "droppedOut") {
                                $totalDroppedOut++;
                            }




                            if ($this->checkIfCountryExist($mainSpouse->country, $arrayCountryTotal)) {
                                foreach ($arrayCountryTotal as $index => $country) {
                                    if ($country['name'] == $mainSpouse->country) {
                                        $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayCountryTotal, [
                                    'name' => $mainSpouse->country,
                                    'total' => 1,
                                ]);
                            }
                        } else {
                            if ($additionalSpouse->spouse_refunded) {
                                $totalRefunded++;
                            }

                            if ($additionalSpouse->spouse_cancelled) {
                                $totalCancelled++;
                            }
                        }
                    }
                }

                if ($noRefund > 0 && $mainSpouse->payment_status == "paid") {
                    $totalAmountPaid += $mainSpouse->total_amount;

                    $spousePaidDate = Carbon::parse($mainSpouse->paid_date_time);
                    if ($dateToday->isSameDay($spousePaidDate)) {
                        $totalAmountPaidToday += $mainSpouse->total_amount;
                    }
                }
            }
        }

        $finalData = [
            'totalConfirmedSpouses' => $totalConfirmedSpouses,
            'totalSpouses' => $totalSpouses,
            'totalRegisteredToday' => $totalRegisteredToday,
            'totalPaidToday' => $totalPaidToday,
            'totalAmountPaidToday' => $totalAmountPaidToday,
            'totalAmountPaid' => $totalAmountPaid,

            'paymentStatus' => [$totalPaid, $totalFree, $totalUnpaid, $totalRefunded],
            'registrationStatus' => [$totalConfirmed, $totalPending, $totalDroppedOut, $totalCancelled],
            'registrationMethod' => [$totalOnline, $totalImported, $totalOnsite],
            'paymentMethod' => [$totalCreditCard, $totalBankTransfer],

            'arrayCountryTotal' => $arrayCountryTotal,
        ];

        return $finalData;
    }

    public function eventDasbhoardVisitorData($eventId)
    {
        $totalConfirmedVisitors = 0;
        $totalVisitors = 0;
        $totalRegisteredToday = 0;
        $visitorBadgePrinted = 0;
        $duplicateBadgePrinted = 0;
        $totalBadgePrinted = 0;
        $totalPaidToday = 0;
        $totalAmountPaidToday = 0;
        $totalAmountPaid = 0;
        $totalFullMember = 0;
        $totalMember = 0;
        $totalNonMember = 0;
        $totalBankTransfer = 0;
        $totalCreditCard = 0;
        $totalPaid = 0;
        $totalFree = 0;
        $totalUnpaid = 0;
        $totalRefunded = 0;
        $totalOnline = 0;
        $totalImported = 0;
        $totalOnsite = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalDroppedOut = 0;
        $totalCancelled = 0;
        $arrayCountryTotal = array();
        $arrayCompanyTotal = array();
        $arrayRegistrationTypeTotal = array();

        $dateToday = Carbon::now();
        $noRefund = 0;

        $mainVisitors = MainVisitor::where('event_id', $eventId)->get();

        if ($mainVisitors->isNotEmpty()) {
            foreach ($mainVisitors as $mainVisitor) {

                $visitorRegisteredDate = Carbon::parse($mainVisitor->registered_date_time);
                if ($dateToday->isSameDay($visitorRegisteredDate)) {
                    $totalRegisteredToday++;
                }

                if ($mainVisitor->visitor_replaced_by_id == null && (!$mainVisitor->visitor_refunded)) {
                    if ($mainVisitor->registration_status == "confirmed") {
                        $totalConfirmedVisitors++;
                    }

                    $totalVisitors++;

                    if ($mainVisitor->pass_type == "fullMember") {
                        $totalFullMember++;
                    } else if ($mainVisitor->pass_type == "member") {
                        $totalMember++;
                    } else {
                        $totalNonMember++;
                    }

                    if ($mainVisitor->mode_of_payment == "creditCard") {
                        $totalCreditCard++;
                    } else {
                        $totalBankTransfer++;
                    }

                    if ($mainVisitor->payment_status == "paid") {
                        $visitorPaidDate = Carbon::parse($mainVisitor->paid_date_time);
                        if ($dateToday->isSameDay($visitorPaidDate)) {
                            $totalPaidToday++;
                        }

                        $noRefund++;
                        $totalPaid++;
                    } else if ($mainVisitor->payment_status == "free") {
                        $totalFree++;
                    } else if ($mainVisitor->payment_status == "unpaid") {
                        $totalUnpaid++;
                    } else {
                    }


                    if ($mainVisitor->registration_method == "online") {
                        $totalOnline++;
                    } else if ($mainVisitor->registration_method == "imported") {
                        $totalImported++;
                    } else {
                        $totalOnsite++;
                    }


                    if ($mainVisitor->registration_status == "confirmed") {
                        $totalConfirmed++;
                    } else if ($mainVisitor->registration_status == "pending") {
                        $totalPending++;
                    } else if ($mainVisitor->registration_status == "droppedOut") {
                        $totalDroppedOut++;
                    }

                    if ($this->checkIfCountryExist($mainVisitor->company_country, $arrayCountryTotal)) {
                        foreach ($arrayCountryTotal as $index => $country) {
                            if ($country['name'] == $mainVisitor->company_country) {
                                $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayCountryTotal, [
                            'name' => $mainVisitor->company_country,
                            'total' => 1,
                        ]);
                    }

                    if ($this->checkIfCompanyExist($mainVisitor->company_name, $arrayCompanyTotal)) {
                        foreach ($arrayCompanyTotal as $index => $company) {
                            if ($company['name'] == $mainVisitor->company_name) {
                                $arrayCompanyTotal[$index]['total'] = $company['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayCompanyTotal, [
                            'name' => $mainVisitor->company_name,
                            'total' => 1,
                        ]);
                    }

                    if ($this->checkIfRegistrationTypeExist($mainVisitor->badge_type, $arrayRegistrationTypeTotal)) {
                        foreach ($arrayRegistrationTypeTotal as $index => $registrationType) {
                            if ($registrationType['name'] == $mainVisitor->badge_type) {
                                $arrayRegistrationTypeTotal[$index]['total'] = $registrationType['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayRegistrationTypeTotal, [
                            'name' => $mainVisitor->badge_type,
                            'total' => 1,
                        ]);
                    }
                } else {
                    if ($mainVisitor->visitor_refunded) {
                        $totalRefunded++;
                    }

                    if ($mainVisitor->visitor_cancelled) {
                        $totalCancelled++;
                    }
                }

                $additionalVisitors = AdditionalVisitor::where('main_visitor_id', $mainVisitor->id)->get();
                if ($additionalVisitors->isNotEmpty()) {
                    foreach ($additionalVisitors as $additionalVisitor) {
                        if ($additionalVisitor->visitor_replaced_by_id == null && (!$additionalVisitor->visitor_refunded)) {
                            if ($mainVisitor->registration_status == "confirmed") {
                                $totalConfirmedVisitors++;
                            }

                            $totalVisitors++;

                            if ($mainVisitor->pass_type == "fullMember") {
                                $totalFullMember++;
                            } else if ($mainVisitor->pass_type == "member") {
                                $totalMember++;
                            } else {
                                $totalNonMember++;
                            }

                            if ($mainVisitor->mode_of_payment == "creditCard") {
                                $totalCreditCard++;
                            } else {
                                $totalBankTransfer++;
                            }


                            if ($mainVisitor->payment_status == "paid") {
                                $visitorPaidDate = Carbon::parse($mainVisitor->paid_date_time);
                                if ($dateToday->isSameDay($visitorPaidDate)) {
                                    $totalPaidToday++;
                                }

                                $noRefund++;
                                $totalPaid++;
                            } else if ($mainVisitor->payment_status == "free") {
                                $totalFree++;
                            } else if ($mainVisitor->payment_status == "unpaid") {
                                $totalUnpaid++;
                            } else {
                            }

                            if ($mainVisitor->registration_method == "online") {
                                $totalOnline++;
                            } else if ($mainVisitor->registration_method == "imported") {
                                $totalImported++;
                            } else {
                                $totalOnsite++;
                            }


                            if ($mainVisitor->registration_status == "confirmed") {
                                $totalConfirmed++;
                            } else if ($mainVisitor->registration_status == "pending") {
                                $totalPending++;
                            } else if ($mainVisitor->registration_status == "droppedOut") {
                                $totalDroppedOut++;
                            }




                            if ($this->checkIfCountryExist($mainVisitor->company_country, $arrayCountryTotal)) {
                                foreach ($arrayCountryTotal as $index => $country) {
                                    if ($country['name'] == $mainVisitor->company_country) {
                                        $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayCountryTotal, [
                                    'name' => $mainVisitor->company_country,
                                    'total' => 1,
                                ]);
                            }

                            if ($this->checkIfCompanyExist($mainVisitor->company_name, $arrayCompanyTotal)) {
                                foreach ($arrayCompanyTotal as $index => $company) {
                                    if ($company['name'] == $mainVisitor->company_name) {
                                        $arrayCompanyTotal[$index]['total'] = $company['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayCompanyTotal, [
                                    'name' => $mainVisitor->company_name,
                                    'total' => 1,
                                ]);
                            }

                            if ($this->checkIfRegistrationTypeExist($additionalVisitor->badge_type, $arrayRegistrationTypeTotal)) {
                                foreach ($arrayRegistrationTypeTotal as $index => $registrationType) {
                                    if ($registrationType['name'] == $additionalVisitor->badge_type) {
                                        $arrayRegistrationTypeTotal[$index]['total'] = $registrationType['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayRegistrationTypeTotal, [
                                    'name' => $additionalVisitor->badge_type,
                                    'total' => 1,
                                ]);
                            }
                        } else {
                            if ($additionalVisitor->visitor_refunded) {
                                $totalRefunded++;
                            }

                            if ($additionalVisitor->visitor_cancelled) {
                                $totalCancelled++;
                            }
                        }
                    }
                }

                if ($noRefund > 0 && $mainVisitor->payment_status == "paid") {
                    $totalAmountPaid += $mainVisitor->total_amount;

                    $visitorPaidDate = Carbon::parse($mainVisitor->paid_date_time);
                    if ($dateToday->isSameDay($visitorPaidDate)) {
                        $totalAmountPaidToday += $mainVisitor->total_amount;
                    }
                }
            }
        }

        $printedBadgesArray = array();
        $printedBadges = VisitorPrintedBadge::where('event_id', $eventId)->get();

        foreach ($printedBadges as $printedBadge) {
            $finalString = $printedBadge->visitor_id . $printedBadge->visitor_type;
            array_push($printedBadgesArray, $finalString);
        }

        $uniquePrintedBadgesArray = array_unique($printedBadgesArray);
        $finalPrintedBadgesArray = array_diff_key($printedBadgesArray, $uniquePrintedBadgesArray);

        $visitorBadgePrinted = count($printedBadges) - count($finalPrintedBadgesArray);
        $duplicateBadgePrinted = count($finalPrintedBadgesArray);
        $totalBadgePrinted = count($printedBadges);



        $scannedVisitorsArray = array();
        $scannedVisitors = ScannedVisitor::where('event_id', $eventId)->get();

        foreach ($scannedVisitors as $scannedVisitor) {
            $finalString = $scannedVisitor->visitor_id . $scannedVisitor->visitor_type;
            array_push($scannedVisitorsArray, $finalString);
        }

        $uniqueScannedVisitorArray = array_unique($scannedVisitorsArray);
        $finalScannedVisitorArray = array_diff_key($scannedVisitorsArray, $uniqueScannedVisitorArray);

        $visitorBadgeScanned = count($scannedVisitors) - count($finalScannedVisitorArray);
        $duplicateBadgeScanned = count($finalScannedVisitorArray);
        $totalBadgeScanned = count($scannedVisitors);

        $finalData = [
            'totalConfirmedVisitors' => $totalConfirmedVisitors,
            'totalVisitors' => $totalVisitors,
            'totalRegisteredToday' => $totalRegisteredToday,

            'visitorBadgePrinted' => $visitorBadgePrinted,
            'duplicateBadgePrinted' => $duplicateBadgePrinted,
            'totalBadgePrinted' => $totalBadgePrinted,

            'visitorBadgeScanned' => $visitorBadgeScanned,
            'duplicateBadgeScanned' => $duplicateBadgeScanned,
            'totalBadgeScanned' => $totalBadgeScanned,

            'totalPaidToday' => $totalPaidToday,
            'totalAmountPaidToday' => $totalAmountPaidToday,
            'totalAmountPaid' => $totalAmountPaid,

            'passType' => [$totalFullMember, $totalMember, $totalNonMember],
            'paymentStatus' => [$totalPaid, $totalFree, $totalUnpaid, $totalRefunded],
            'registrationStatus' => [$totalConfirmed, $totalPending, $totalDroppedOut, $totalCancelled],
            'registrationMethod' => [$totalOnline, $totalImported, $totalOnsite],
            'paymentMethod' => [$totalCreditCard, $totalBankTransfer],

            'arrayCountryTotal' => $arrayCountryTotal,
            'arrayCompanyTotal' => $arrayCompanyTotal,
            'arrayRegistrationTypeTotal' => $arrayRegistrationTypeTotal,
        ];

        return $finalData;
    }

    public function eventDasbhoardRccAwardsData($eventId)
    {
        $totalConfirmedParticipants = 0;
        $totalParticipants = 0;
        $totalRegisteredToday = 0;
        $totalPaidToday = 0;
        $totalAmountPaidToday = 0;
        $totalAmountPaid = 0;
        $totalBankTransfer = 0;
        $totalCreditCard = 0;
        $totalPaid = 0;
        $totalFree = 0;
        $totalUnpaid = 0;
        $totalRefunded = 0;
        $totalOnline = 0;
        $totalImported = 0;
        $totalOnsite = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalDroppedOut = 0;
        $totalCancelled = 0;
        $arrayCountryTotal = array();

        $dateToday = Carbon::now();
        $noRefund = 0;

        $mainParticipants = RccAwardsMainParticipant::where('event_id', $eventId)->get();

        if ($mainParticipants->isNotEmpty()) {
            foreach ($mainParticipants as $mainParticipant) {

                $participantRegisteredDate = Carbon::parse($mainParticipant->registered_date_time);
                if ($dateToday->isSameDay($participantRegisteredDate)) {
                    $totalRegisteredToday++;
                }

                if ($mainParticipant->participant_replaced_by_id == null && (!$mainParticipant->participant_refunded)) {
                    if ($mainParticipant->registration_status == "confirmed") {
                        $totalConfirmedParticipants++;
                    }

                    $totalParticipants++;

                    if ($mainParticipant->mode_of_payment == "creditCard") {
                        $totalCreditCard++;
                    } else {
                        $totalBankTransfer++;
                    }

                    if ($mainParticipant->payment_status == "paid") {
                        $participantPaidDate = Carbon::parse($mainParticipant->paid_date_time);
                        if ($dateToday->isSameDay($participantPaidDate)) {
                            $totalPaidToday++;
                        }

                        $noRefund++;
                        $totalPaid++;
                    } else if ($mainParticipant->payment_status == "free") {
                        $totalFree++;
                    } else if ($mainParticipant->payment_status == "unpaid") {
                        $totalUnpaid++;
                    } else {
                    }


                    if ($mainParticipant->registration_method == "online") {
                        $totalOnline++;
                    } else if ($mainParticipant->registration_method == "imported") {
                        $totalImported++;
                    } else {
                        $totalOnsite++;
                    }


                    if ($mainParticipant->registration_status == "confirmed") {
                        $totalConfirmed++;
                    } else if ($mainParticipant->registration_status == "pending") {
                        $totalPending++;
                    } else if ($mainParticipant->registration_status == "droppedOut") {
                        $totalDroppedOut++;
                    }

                    if ($this->checkIfCountryExist($mainParticipant->country, $arrayCountryTotal)) {
                        foreach ($arrayCountryTotal as $index => $country) {
                            if ($country['name'] == $mainParticipant->country) {
                                $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayCountryTotal, [
                            'name' => $mainParticipant->country,
                            'total' => 1,
                        ]);
                    }
                } else {
                    if ($mainParticipant->participant_refunded) {
                        $totalRefunded++;
                    }

                    if ($mainParticipant->participant_cancelled) {
                        $totalCancelled++;
                    }
                }

                $additionalParticipants = RccAwardsAdditionalParticipant::where('main_participant_id', $mainParticipant->id)->get();
                if ($additionalParticipants->isNotEmpty()) {
                    foreach ($additionalParticipants as $additionalParticipant) {
                        if ($additionalParticipant->participant_replaced_by_id == null && (!$additionalParticipant->participant_refunded)) {
                            if ($mainParticipant->registration_status == "confirmed") {
                                $totalConfirmedParticipants++;
                            }

                            $totalParticipants++;

                            if ($mainParticipant->mode_of_payment == "creditCard") {
                                $totalCreditCard++;
                            } else {
                                $totalBankTransfer++;
                            }


                            if ($mainParticipant->payment_status == "paid") {
                                $spousePaidDate = Carbon::parse($mainParticipant->paid_date_time);
                                if ($dateToday->isSameDay($spousePaidDate)) {
                                    $totalPaidToday++;
                                }

                                $noRefund++;
                                $totalPaid++;
                            } else if ($mainParticipant->payment_status == "free") {
                                $totalFree++;
                            } else if ($mainParticipant->payment_status == "unpaid") {
                                $totalUnpaid++;
                            } else {
                            }

                            if ($mainParticipant->registration_method == "online") {
                                $totalOnline++;
                            } else if ($mainParticipant->registration_method == "imported") {
                                $totalImported++;
                            } else {
                                $totalOnsite++;
                            }


                            if ($mainParticipant->registration_status == "confirmed") {
                                $totalConfirmed++;
                            } else if ($mainParticipant->registration_status == "pending") {
                                $totalPending++;
                            } else if ($mainParticipant->registration_status == "droppedOut") {
                                $totalDroppedOut++;
                            }




                            if ($this->checkIfCountryExist($additionalParticipant->country, $arrayCountryTotal)) {
                                foreach ($arrayCountryTotal as $index => $country) {
                                    if ($country['name'] == $additionalParticipant->country) {
                                        $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayCountryTotal, [
                                    'name' => $additionalParticipant->country,
                                    'total' => 1,
                                ]);
                            }
                        } else {
                            if ($additionalParticipants->participant_refunded) {
                                $totalRefunded++;
                            }

                            if ($additionalParticipants->participant_cancelled) {
                                $totalCancelled++;
                            }
                        }
                    }
                }

                if ($noRefund > 0 && $mainParticipant->payment_status == "paid") {
                    $totalAmountPaid += $mainParticipant->total_amount;

                    $participantPaidDate = Carbon::parse($mainParticipant->paid_date_time);
                    if ($dateToday->isSameDay($participantPaidDate)) {
                        $totalAmountPaidToday += $mainParticipant->total_amount;
                    }
                }
            }
        }

        $finalData = [
            'totalConfirmedParticipants' => $totalConfirmedParticipants,
            'totalParticipants' => $totalParticipants,
            'totalRegisteredToday' => $totalRegisteredToday,
            'totalPaidToday' => $totalPaidToday,
            'totalAmountPaidToday' => $totalAmountPaidToday,
            'totalAmountPaid' => $totalAmountPaid,

            'paymentStatus' => [$totalPaid, $totalFree, $totalUnpaid, $totalRefunded],
            'registrationStatus' => [$totalConfirmed, $totalPending, $totalDroppedOut, $totalCancelled],
            'registrationMethod' => [$totalOnline, $totalImported, $totalOnsite],
            'paymentMethod' => [$totalCreditCard, $totalBankTransfer],

            'arrayCountryTotal' => $arrayCountryTotal,
        ];

        return $finalData;
    }

    public function eventDasbhoardAwardsData($eventId)
    {
        $totalConfirmedParticipants = 0;
        $totalParticipants = 0;
        $totalRegisteredToday = 0;
        $totalPaidToday = 0;
        $totalAmountPaidToday = 0;
        $totalAmountPaid = 0;
        $totalBankTransfer = 0;
        $totalCreditCard = 0;
        $totalPaid = 0;
        $totalFree = 0;
        $totalUnpaid = 0;
        $totalRefunded = 0;
        $totalOnline = 0;
        $totalImported = 0;
        $totalOnsite = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalDroppedOut = 0;
        $totalCancelled = 0;
        $arrayCountryTotal = array();

        $dateToday = Carbon::now();
        $noRefund = 0;

        $mainParticipants = AwardsMainParticipant::where('event_id', $eventId)->get();

        if ($mainParticipants->isNotEmpty()) {
            foreach ($mainParticipants as $mainParticipant) {

                $participantRegisteredDate = Carbon::parse($mainParticipant->registered_date_time);
                if ($dateToday->isSameDay($participantRegisteredDate)) {
                    $totalRegisteredToday++;
                }

                if ($mainParticipant->participant_replaced_by_id == null && (!$mainParticipant->participant_refunded)) {
                    if ($mainParticipant->registration_status == "confirmed") {
                        $totalConfirmedParticipants++;
                    }

                    $totalParticipants++;

                    if ($mainParticipant->mode_of_payment == "creditCard") {
                        $totalCreditCard++;
                    } else {
                        $totalBankTransfer++;
                    }

                    if ($mainParticipant->payment_status == "paid") {
                        $participantPaidDate = Carbon::parse($mainParticipant->paid_date_time);
                        if ($dateToday->isSameDay($participantPaidDate)) {
                            $totalPaidToday++;
                        }

                        $noRefund++;
                        $totalPaid++;
                    } else if ($mainParticipant->payment_status == "free") {
                        $totalFree++;
                    } else if ($mainParticipant->payment_status == "unpaid") {
                        $totalUnpaid++;
                    } else {
                    }


                    if ($mainParticipant->registration_method == "online") {
                        $totalOnline++;
                    } else if ($mainParticipant->registration_method == "imported") {
                        $totalImported++;
                    } else {
                        $totalOnsite++;
                    }


                    if ($mainParticipant->registration_status == "confirmed") {
                        $totalConfirmed++;
                    } else if ($mainParticipant->registration_status == "pending") {
                        $totalPending++;
                    } else if ($mainParticipant->registration_status == "droppedOut") {
                        $totalDroppedOut++;
                    }

                    if ($this->checkIfCountryExist($mainParticipant->country, $arrayCountryTotal)) {
                        foreach ($arrayCountryTotal as $index => $country) {
                            if ($country['name'] == $mainParticipant->country) {
                                $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                            }
                        }
                    } else {
                        array_push($arrayCountryTotal, [
                            'name' => $mainParticipant->country,
                            'total' => 1,
                        ]);
                    }
                } else {
                    if ($mainParticipant->participant_refunded) {
                        $totalRefunded++;
                    }

                    if ($mainParticipant->participant_cancelled) {
                        $totalCancelled++;
                    }
                }

                $additionalParticipants = AwardsAdditionalParticipant::where('main_participant_id', $mainParticipant->id)->get();
                if ($additionalParticipants->isNotEmpty()) {
                    foreach ($additionalParticipants as $additionalParticipant) {
                        if ($additionalParticipant->participant_replaced_by_id == null && (!$additionalParticipant->participant_refunded)) {
                            if ($mainParticipant->registration_status == "confirmed") {
                                $totalConfirmedParticipants++;
                            }

                            $totalParticipants++;

                            if ($mainParticipant->mode_of_payment == "creditCard") {
                                $totalCreditCard++;
                            } else {
                                $totalBankTransfer++;
                            }


                            if ($mainParticipant->payment_status == "paid") {
                                $spousePaidDate = Carbon::parse($mainParticipant->paid_date_time);
                                if ($dateToday->isSameDay($spousePaidDate)) {
                                    $totalPaidToday++;
                                }

                                $noRefund++;
                                $totalPaid++;
                            } else if ($mainParticipant->payment_status == "free") {
                                $totalFree++;
                            } else if ($mainParticipant->payment_status == "unpaid") {
                                $totalUnpaid++;
                            } else {
                            }

                            if ($mainParticipant->registration_method == "online") {
                                $totalOnline++;
                            } else if ($mainParticipant->registration_method == "imported") {
                                $totalImported++;
                            } else {
                                $totalOnsite++;
                            }


                            if ($mainParticipant->registration_status == "confirmed") {
                                $totalConfirmed++;
                            } else if ($mainParticipant->registration_status == "pending") {
                                $totalPending++;
                            } else if ($mainParticipant->registration_status == "droppedOut") {
                                $totalDroppedOut++;
                            }




                            if ($this->checkIfCountryExist($additionalParticipant->country, $arrayCountryTotal)) {
                                foreach ($arrayCountryTotal as $index => $country) {
                                    if ($country['name'] == $additionalParticipant->country) {
                                        $arrayCountryTotal[$index]['total'] = $country['total'] + 1;
                                    }
                                }
                            } else {
                                array_push($arrayCountryTotal, [
                                    'name' => $additionalParticipant->country,
                                    'total' => 1,
                                ]);
                            }
                        } else {
                            if ($additionalParticipant->participant_refunded) {
                                $totalRefunded++;
                            }

                            if ($additionalParticipant->participant_cancelled) {
                                $totalCancelled++;
                            }
                        }
                    }
                }

                if ($noRefund > 0 && $mainParticipant->payment_status == "paid") {
                    $totalAmountPaid += $mainParticipant->total_amount;

                    $participantPaidDate = Carbon::parse($mainParticipant->paid_date_time);
                    if ($dateToday->isSameDay($participantPaidDate)) {
                        $totalAmountPaidToday += $mainParticipant->total_amount;
                    }
                }
            }
        }

        $finalData = [
            'totalConfirmedParticipants' => $totalConfirmedParticipants,
            'totalParticipants' => $totalParticipants,
            'totalRegisteredToday' => $totalRegisteredToday,
            'totalPaidToday' => $totalPaidToday,
            'totalAmountPaidToday' => $totalAmountPaidToday,
            'totalAmountPaid' => $totalAmountPaid,

            'paymentStatus' => [$totalPaid, $totalFree, $totalUnpaid, $totalRefunded],
            'registrationStatus' => [$totalConfirmed, $totalPending, $totalDroppedOut, $totalCancelled],
            'registrationMethod' => [$totalOnline, $totalImported, $totalOnsite],
            'paymentMethod' => [$totalCreditCard, $totalBankTransfer],

            'arrayCountryTotal' => $arrayCountryTotal,
        ];

        return $finalData;
    }

    public function eventDetailView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

            // $today = Carbon::today();
            // if ($event->eb_end_date != null && $event->eb_member_rate != null && $event->eb_nmember_rate != null) {
            //     if ($today->lte(Carbon::parse($event->eb_end_date))) {
            //         $finalEbEndDate = Carbon::parse($event->eb_end_date)->format('d M Y');
            //     } else {
            //         $finalEbEndDate = null;
            //     }
            // } else {
            //     $finalEbEndDate = null;
            // }

            $finalEbEndDate = $event->eb_end_date ? Carbon::parse($event->eb_end_date)->format('d M Y') : null;
            $finalStdStartDate = $event->std_start_date ? Carbon::parse($event->std_start_date)->format('d M Y') : null;

            $finalWoEbEndDate = $event->wo_eb_end_date ? Carbon::parse($event->wo_eb_end_date)->format('d M Y') : null;
            $finalWoStdStartDate = $event->wo_std_start_date ? Carbon::parse($event->wo_std_start_date)->format('d M Y') : null;

            $finalCoEbEndDate = $event->co_eb_end_date ? Carbon::parse($event->co_eb_end_date)->format('d M Y') : null;
            $finalCoStdStartDate = $event->co_std_start_date ? Carbon::parse($event->co_std_start_date)->format('d M Y') : null;

            $finalEventStartDate = Carbon::parse($event->event_start_date)->format('d M Y');
            $finalEventEndDate = Carbon::parse($event->event_end_date)->format('d M Y');

            $regFormLink = env('APP_URL') . '/register/' . $event->year . '/' . $eventCategory . '/' . $eventId;

            return view('admin.events.details.event_details', [
                "pageTitle" => "Event Details",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
                "event" => $event,
                "finalEbEndDate" => $finalEbEndDate,
                "finalStdStartDate" => $finalStdStartDate,

                "finalWoEbEndDate" => $finalWoEbEndDate,
                "finalWoStdStartDate" => $finalWoStdStartDate,

                "finalCoEbEndDate" => $finalCoEbEndDate,
                "finalCoStdStartDate" => $finalCoStdStartDate,

                "finalEventStartDate" => $finalEventStartDate,
                "finalEventEndDate" => $finalEventEndDate,
                "regFormLink" => $regFormLink,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function eventRegistrationType($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists() && $eventCategory != 'AFS' && $eventCategory != 'RCCA' && $eventCategory != 'SCEA') {
            return view('admin.events.registration-type.registration_type', [
                "pageTitle" => "Event Registration Type",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function eventDelegateFeesView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()  && $eventCategory != 'AFS' && $eventCategory != 'RCCA' && $eventCategory != 'SCEA') {
            return view('admin.events.delegate-fees.delegate_fees', [
                "pageTitle" => "Event Delegate Fees",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function eventPromoCodeView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()  && $eventCategory != "AFS" && $eventCategory != 'RCCA' && $eventCategory != 'SCEA') {
            return view('admin.events.promo-codes.promo_codes', [
                "pageTitle" => "Event Promo Codes",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }


    // =========================================================
    //                       RENDER LOGICS
    // =========================================================

    public function addEvent(Request $request)
    {
        $request->validate(
            [
                'category' => 'required',
                'name' => 'required',
                'location' => 'required',
                'description' => 'required',
                'link' => 'required',
                'event_start_date' => 'required|date',
                'event_end_date' => 'required|date',
                'event_vat' => 'required|numeric|min:0|max:100',
                'logo' => 'required|mimes:jpeg,png,jpg,gif',
                'banner' => 'required|mimes:jpeg,png,jpg,gif',

                'eb_end_date' => 'nullable|date',
                'eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                'std_start_date' => 'required|date',
                'std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'std_member_rate' => 'required|numeric|min:0|max:99999.99',
                'std_nmember_rate' => 'required|numeric|min:0|max:99999.99',

                'wo_eb_end_date' => 'nullable|date',
                'wo_eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'wo_eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'wo_eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                'wo_std_start_date' => 'nullable|date',
                'wo_std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'wo_std_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'wo_std_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                'co_eb_end_date' => 'nullable|date',
                'co_eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'co_eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'co_eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                'co_std_start_date' => 'nullable|date',
                'co_std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'co_std_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                'co_std_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                'badge_footer_link' => 'required',
                'badge_footer_link_color' => 'required',
                'badge_footer_bg_color' => 'required',
                'badge_front_banner' => 'required|mimes:jpeg,png,jpg,gif',
                'badge_back_banner' => 'required|mimes:jpeg,png,jpg,gif',
            ],
            [
                'category.required' => 'Event Category is required',
                'name.required' => 'Event Name is required',
                'location.required' => 'Event Location is required',
                'description.required' => 'Event Description is required',
                'link.required' => 'Event Link is required',
                'event_start_date.required' => 'Event Start Date is required',
                'event_start_date.date' => 'Event Start Date must be a date',
                'event_end_date.required' => 'Event End Date is required',
                'event_end_date.date' => 'Event End Date must be a date',
                'event_vat.required' => 'Vat is required',
                'event_vat.numeric' => 'Vat must be a number.',
                'event_vat.min' => 'Vat must be at least :min.',
                'event_vat.max' => 'Vat may not be greater than :max.',
                'logo.required' => 'Event Logo is required',
                'logo.mimes' => 'Event Logo must be in jpeg, png, jpg, gif format',
                'banner.required' => 'Event Banner is required',
                'banner.mimes' => 'Event Banner must be in jpeg, png, jpg, gif format',



                'eb_end_date.date' => 'Early Bird End Date must be a date',

                'eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                'eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                'eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                'eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                'eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                'eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                'eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                'eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                'eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',



                'std_start_date.required' => 'Standard Start Date is required',
                'std_start_date.date' => 'Standard Start Date must be a date',

                'std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                'std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                'std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                'std_member_rate.required' => 'Standard Member Rate is required',
                'std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                'std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                'std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',

                'std_nmember_rate.required' => 'Standard Non-Member Rate is required',
                'std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                'std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                'std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',


                'wo_eb_end_date.date' => 'Early Bird End Date must be a date',
                'wo_eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                'wo_eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                'wo_eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                'wo_eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                'wo_eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                'wo_eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                'wo_eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                'wo_eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                'wo_eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',

                'wo_std_start_date.date' => 'Standard Start Date must be a date',
                'wo_std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                'wo_std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                'wo_std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                'wo_std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                'wo_std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                'wo_std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',
                'wo_std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                'wo_std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                'wo_std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',


                'co_eb_end_date.date' => 'Early Bird End Date must be a date',
                'co_eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                'co_eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                'co_eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                'co_eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                'co_eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                'co_eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                'co_eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                'co_eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                'co_eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',

                'co_std_start_date.date' => 'Standard Start Date must be a date',
                'co_std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                'co_std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                'co_std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                'co_std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                'co_std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                'co_std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',
                'co_std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                'co_std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                'co_std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',


                'badge_footer_link.required' => 'Badge Footer Link is required',
                'badge_footer_link_color.required' => 'Badge Footer Link Color is required',
                'badge_footer_bg_color.required' => 'Badge Footer Link Background Color is required',
                'badge_front_banner.required' => 'Badge Front Banner is required',
                'badge_front_banner.mimes' => 'Badge Front Banner must be in jpeg, png, jpg, gif format',
                'badge_back_banner.required' => 'Badge Back Banner is required',
                'badge_back_banner.mimes' => 'Badge Back Banner must be in jpeg, png, jpg, gif format',
            ]
        );

        $currentYear = strval(Carbon::parse($request->event_start_date)->year);
        $logoPath = $request->file('logo')->store('public/event/' . $currentYear . '/logos');
        $bannerPath = $request->file('banner')->store('public/event/' . $currentYear . '/banners');
        $badgeFrontBannerPath = $request->file('badge_front_banner')->store('public/event/' . $currentYear . '/badges/front');
        $badgeBackBannerPath = $request->file('badge_back_banner')->store('public/event/' . $currentYear . '/badges/back');

        $newEvent = Event::create([
            'category' => $request->category,
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'link' => $request->link,
            'event_start_date' => $request->event_start_date,
            'event_end_date' => $request->event_end_date,
            'event_vat' => $request->event_vat,
            'logo' => $logoPath,
            'banner' => $bannerPath,

            'eb_end_date' => $request->eb_end_date,
            'eb_full_member_rate' => $request->eb_full_member_rate,
            'eb_member_rate' => $request->eb_member_rate,
            'eb_nmember_rate' => $request->eb_nmember_rate,

            'std_start_date' => $request->std_start_date,
            'std_full_member_rate' => $request->std_full_member_rate,
            'std_member_rate' => $request->std_member_rate,
            'std_nmember_rate' => $request->std_nmember_rate,


            'wo_eb_end_date' => $request->wo_eb_end_date,
            'wo_eb_full_member_rate' => $request->wo_eb_full_member_rate,
            'wo_eb_member_rate' => $request->wo_eb_member_rate,
            'wo_eb_nmember_rate' => $request->wo_eb_nmember_rate,

            'wo_std_start_date' => $request->wo_std_start_date,
            'wo_std_full_member_rate' => $request->wo_std_full_member_rate,
            'wo_std_member_rate' => $request->wo_std_member_rate,
            'wo_std_nmember_rate' => $request->wo_std_nmember_rate,


            'co_eb_end_date' => $request->co_eb_end_date,
            'co_eb_full_member_rate' => $request->co_eb_full_member_rate,
            'co_eb_member_rate' => $request->co_eb_member_rate,
            'co_eb_nmember_rate' => $request->co_eb_nmember_rate,

            'co_std_start_date' => $request->co_std_start_date,
            'co_std_full_member_rate' => $request->co_std_full_member_rate,
            'co_std_member_rate' => $request->co_std_member_rate,
            'co_std_nmember_rate' => $request->co_std_nmember_rate,

            'badge_footer_link' => $request->badge_footer_link,
            'badge_footer_link_color' => $request->badge_footer_link_color,
            'badge_footer_bg_color' => $request->badge_footer_bg_color,
            'badge_front_banner' => $badgeFrontBannerPath,
            'badge_back_banner' => $badgeBackBannerPath,

            'year' => $currentYear,
            'active' => true,
        ]);

        EventRegistrationType::create([
            'event_id' => $newEvent->id,
            'event_category' => $newEvent->category,
            'registration_type' => "Delegate",
            'badge_footer_front_name' => "Delegate",
            'badge_footer_front_bg_color' => "#000000",
            'badge_footer_front_text_color' => "#ffffff",
            'badge_footer_back_name' => "Delegate",
            'badge_footer_back_bg_color' => "#000000",
            'badge_footer_back_text_color' => "#ffffff",
            'active' => true,
        ]);

        EventRegistrationType::create([
            'event_id' => $newEvent->id,
            'event_category' => $newEvent->category,
            'registration_type' => "Organizer",
            'badge_footer_front_name' => "Organizer",
            'badge_footer_front_bg_color' => "#000000",
            'badge_footer_front_text_color' => "#ffffff",
            'badge_footer_back_name' => "Organizer",
            'badge_footer_back_bg_color' => "#000000",
            'badge_footer_back_text_color' => "#ffffff",
            'active' => true,
        ]);

        return redirect()->route('admin.event.view')->with('success', 'Event added successfully.');;
    }

    public function updateEvent(Request $request, $eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $validatedData = $request->validate(
                [
                    'eventId' => 'required',
                    'category' => 'required',
                    'name' => 'required',
                    'location' => 'required',
                    'description' => 'required',
                    'link' => 'required',
                    'event_start_date' => 'required|date',
                    'event_end_date' => 'required|date',
                    'event_vat' => 'required|numeric|min:0|max:100',
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                    'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif',

                    'eb_end_date' => 'nullable|date',
                    'eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                    'std_start_date' => 'required|date',
                    'std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'std_member_rate' => 'required|numeric|min:0|max:99999.99',
                    'std_nmember_rate' => 'required|numeric|min:0|max:99999.99',

                    'wo_eb_end_date' => 'nullable|date',
                    'wo_eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'wo_eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'wo_eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                    'wo_std_start_date' => 'nullable|date',
                    'wo_std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'wo_std_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'wo_std_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                    'co_eb_end_date' => 'nullable|date',
                    'co_eb_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'co_eb_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'co_eb_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',

                    'co_std_start_date' => 'nullable|date',
                    'co_std_full_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'co_std_member_rate' => 'nullable|numeric|min:0|max:99999.99',
                    'co_std_nmember_rate' => 'nullable|numeric|min:0|max:99999.99',


                    'badge_footer_link' => 'required',
                    'badge_footer_link_color' => 'required',
                    'badge_footer_bg_color' => 'required',
                    'badge_front_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                    'badge_back_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                ],
                [
                    'category.required' => 'Event Category is required',
                    'name.required' => 'Event Name is required',
                    'location.required' => 'Event Location is required',
                    'description.required' => 'Event Description is required',
                    'link.required' => 'Event Link is required',
                    'event_start_date.required' => 'Event Start Date is required',
                    'event_start_date.date' => 'Event Start Date must be a date',
                    'event_end_date.required' => 'Event End Date is required',
                    'event_end_date.date' => 'Event End Date must be a date',
                    'event_vat.required' => 'Vat is required',
                    'event_vat.numeric' => 'Vat must be a number.',
                    'event_vat.min' => 'Vat must be at least :min.',
                    'event_vat.max' => 'Vat may not be greater than :max.',
                    'logo.image' => 'Event Logo must be an image',
                    'logo.mimes' => 'Event Logo must be in jpeg, png, jpg, gif format',
                    'banner.image' => 'Event Banner must be an image',
                    'banner.mimes' => 'Event Banner must be in jpeg, png, jpg, gif format',



                    'eb_end_date.date' => 'Early Bird End Date must be a date',

                    'eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                    'eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                    'eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                    'eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                    'eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                    'eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                    'eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                    'eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                    'eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',



                    'std_start_date.required' => 'Standard Start Date is required',
                    'std_start_date.date' => 'Standard Start Date must be a date',

                    'std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                    'std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                    'std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                    'std_member_rate.required' => 'Standard Member Rate is required',
                    'std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                    'std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                    'std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',

                    'std_nmember_rate.required' => 'Standard Non-Member Rate is required',
                    'std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                    'std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                    'std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',

                    'wo_eb_end_date.date' => 'Early Bird End Date must be a date',
                    'wo_eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                    'wo_eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                    'wo_eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                    'wo_eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                    'wo_eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                    'wo_eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                    'wo_eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                    'wo_eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                    'wo_eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',

                    'wo_std_start_date.date' => 'Standard Start Date must be a date',
                    'wo_std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                    'wo_std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                    'wo_std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                    'wo_std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                    'wo_std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                    'wo_std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',
                    'wo_std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                    'wo_std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                    'wo_std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',


                    'co_eb_end_date.date' => 'Early Bird End Date must be a date',
                    'co_eb_full_member_rate.numeric' => 'Early Bird Full Member Rate must be a number.',
                    'co_eb_full_member_rate.min' => 'Early Bird Full Member Rate must be at least :min.',
                    'co_eb_full_member_rate.max' => 'Early Bird Full Member Rate may not be greater than :max.',
                    'co_eb_member_rate.numeric' => 'Early Bird Member Rate must be a number.',
                    'co_eb_member_rate.min' => 'Early Bird Member Rate must be at least :min.',
                    'co_eb_member_rate.max' => 'Early Bird Member Rate may not be greater than :max.',
                    'co_eb_nmember_rate.numeric' => 'Early Bird Non-Member Rate must be a number.',
                    'co_eb_nmember_rate.min' => 'Early Bird Non-Member Rate must be at least :min.',
                    'co_eb_nmember_rate.max' => 'Early Bird Non-Member Rate may not be greater than :max.',

                    'co_std_start_date.date' => 'Standard Start Date must be a date',
                    'co_std_full_member_rate.numeric' => 'Standard Full Member Rate must be a number.',
                    'co_std_full_member_rate.min' => 'Standard Full Member Rate must be at least :min.',
                    'co_std_full_member_rate.max' => 'Standard Full Member Rate may not be greater than :max.',
                    'co_std_member_rate.numeric' => 'Standard Member Rate must be a number.',
                    'co_std_member_rate.min' => 'Standard Member Rate must be at least :min.',
                    'co_std_member_rate.max' => 'Standard Member Rate may not be greater than :max.',
                    'co_std_nmember_rate.numeric' => 'Standard Non-Member Rate must be a number.',
                    'co_std_nmember_rate.min' => 'Standard Non-Member Rate must be at least :min.',
                    'co_std_nmember_rate.max' => 'Standard Non-Member Rate may not be greater than :max.',


                    'badge_footer_link.required' => 'Badge Footer Link is required',
                    'badge_footer_link_color.required' => 'Badge Footer Link Color is required',
                    'badge_footer_bg_color.required' => 'Badge Footer Link Background Color is required',
                    'badge_front_banner.image' => 'Badge Front Banner must be an image',
                    'badge_front_banner.mimes' => 'Badge Front Banner must be in jpeg, png, jpg, gif format',
                    'badge_back_banner.image' => 'Badge Back Banner must be an image',
                    'badge_back_banner.mimes' => 'Badge Back Banner must be in jpeg, png, jpg, gif format',
                ]
            );

            $event = Event::findOrFail($validatedData['eventId']);

            $event->category = $validatedData['category'];
            $event->name = $validatedData['name'];
            $event->location = $validatedData['location'];
            $event->description = $validatedData['description'];
            $event->link = $validatedData['link'];
            $event->event_start_date = $validatedData['event_start_date'];
            $event->event_end_date = $validatedData['event_end_date'];
            $event->event_vat = $validatedData['event_vat'];

            $event->eb_end_date = $validatedData['eb_end_date'];
            $event->eb_full_member_rate = $validatedData['eb_full_member_rate'];
            $event->eb_member_rate = $validatedData['eb_member_rate'];
            $event->eb_nmember_rate = $validatedData['eb_nmember_rate'];

            $event->std_start_date = $validatedData['std_start_date'];
            $event->std_full_member_rate = $validatedData['std_full_member_rate'];
            $event->std_member_rate = $validatedData['std_member_rate'];
            $event->std_nmember_rate = $validatedData['std_nmember_rate'];



            $event->wo_eb_end_date = $validatedData['wo_eb_end_date'];
            $event->wo_eb_full_member_rate = $validatedData['wo_eb_full_member_rate'];
            $event->wo_eb_member_rate = $validatedData['wo_eb_member_rate'];
            $event->wo_eb_nmember_rate = $validatedData['wo_eb_nmember_rate'];

            $event->wo_std_start_date = $validatedData['wo_std_start_date'];
            $event->wo_std_full_member_rate = $validatedData['wo_std_full_member_rate'];
            $event->wo_std_member_rate = $validatedData['wo_std_member_rate'];
            $event->wo_std_nmember_rate = $validatedData['wo_std_nmember_rate'];


            $event->co_eb_end_date = $validatedData['co_eb_end_date'];
            $event->co_eb_full_member_rate = $validatedData['co_eb_full_member_rate'];
            $event->co_eb_member_rate = $validatedData['co_eb_member_rate'];
            $event->co_eb_nmember_rate = $validatedData['co_eb_nmember_rate'];

            $event->co_std_start_date = $validatedData['co_std_start_date'];
            $event->co_std_full_member_rate = $validatedData['co_std_full_member_rate'];
            $event->co_std_member_rate = $validatedData['co_std_member_rate'];
            $event->co_std_nmember_rate = $validatedData['co_std_nmember_rate'];


            $event->badge_footer_link = $validatedData['badge_footer_link'];
            $event->badge_footer_link_color = $validatedData['badge_footer_link_color'];
            $event->badge_footer_bg_color = $validatedData['badge_footer_bg_color'];

            $currentYear = date('Y');
            if ($request->hasFile('logo')) {
                Storage::delete($event->logo);
                $logoPath = $request->file('logo')->store('public/event/' . $currentYear . '/logos');
                $event->logo = $logoPath;
            }

            if ($request->hasFile('banner')) {
                Storage::delete($event->banner);
                $bannerPath = $request->file('banner')->store('public/event/' . $currentYear . '/banners');
                $event->banner = $bannerPath;
            }

            if ($request->hasFile('badge_front_banner')) {
                Storage::delete($event->badge_front_banner);
                $badgeFrontBannerPath = $request->file('badge_front_banner')->store('public/event/' . $currentYear . '/badges/front');
                $event->badge_front_banner = $badgeFrontBannerPath;
            }

            if ($request->hasFile('badge_back_banner')) {
                Storage::delete($event->badge_back_banner);
                $badgeBackBannerPath = $request->file('badge_back_banner')->store('public/event/' . $currentYear . '/badges/back');
                $event->badge_back_banner = $badgeBackBannerPath;
            }

            $event->save();

            return redirect()->route('admin.event.detail.view', ['eventCategory' => $event->category, 'eventId' => $event->id])->with('success', 'Event updated successfully.');
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function updateEventStatus(Request $request, $eventCategory, $eventId, $eventStatus)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {

            Event::where('id', $eventId)->update([
                'active' => !$eventStatus,
            ]);

            return redirect()->route('admin.event.detail.view', ['eventCategory' => $eventCategory, 'eventId' => $eventId])->with('success', 'Event status updated successfully.');
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function checkIfCountryExist($countryTemp, $arrayCountries)
    {
        $checker = 0;
        foreach ($arrayCountries as $country) {
            if ($country['name'] == $countryTemp) {
                $checker++;
            }
        }

        if ($checker > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function checkIfCompanyExist($companyName, $arrayCompanies)
    {
        $checker = 0;
        foreach ($arrayCompanies as $company) {
            if ($company['name'] == $companyName) {
                $checker++;
            }
        }

        if ($checker > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function checkIfRegistrationTypeExist($registrationType, $arrayRegistrationTypes)
    {
        $checker = 0;
        foreach ($arrayRegistrationTypes as $type) {
            if ($type['name'] == $registrationType) {
                $checker++;
            }
        }

        if ($checker > 0) {
            return true;
        } else {
            return false;
        }
    }


    // =========================================================
    //                       RENDER APIS
    // =========================================================

    public function getRegistrationTypes($eventCategory, $year)
    {
        if (Event::where('category', $eventCategory)->where('year', $year)->exists()) {
            $eventId = Event::where('category', $eventCategory)->where('year', $year)->value('id');
            $registrationTypes = EventRegistrationType::where('event_id', $eventId)->where('active', true)->get();

            $finalRegistrationTypes = array();

            if ($registrationTypes->isNotEmpty()) {
                foreach ($registrationTypes as $registrationType) {
                    array_push($finalRegistrationTypes, $registrationType->registration_type);
                }
            }
            return response()->json([
                'status' => '200',
                'data' => $finalRegistrationTypes,
            ], 200);
        } else {
            return response()->json([
                'status' => '404',
                'message' => "Event not found",
            ], 404);
        }
    }

    public function exportListOfPromoCodes($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $finalExcelData = array();

            $eventYear = Event::where('category', $eventCategory)->where('id', $eventId)->value('year');
            $promoCodes = ModelsPromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->get();

            foreach ($promoCodes as $promoCode) {
                $badgeTypesForPromoCode = $promoCode->badge_type;

                $additionalBadgeTypes = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $promoCode->id)->get();

                if ($additionalBadgeTypes->isNotEmpty()) {
                    foreach ($additionalBadgeTypes as $additionalBadgeType) {
                        $badgeTypesForPromoCode = "$badgeTypesForPromoCode, $additionalBadgeType->badge_type";
                    }
                }

                if ($promoCode->discount_type == "percentage") {
                    $discountType = "Percentage";
                    $discount = "$promoCode->discount %";
                    $newRate = 'N/A';
                    $newRateDescription = 'N/A';
                } else if ($promoCode->discount_type == "price") {
                    $discountType = "Price";
                    $discount = "$ $promoCode->discount";
                    $newRate = 'N/A';
                    $newRateDescription = 'N/A';
                } else {
                    $discountType = "Fixed rate";
                    $discount = "N/A";
                    $newRate = "$ $promoCode->new_rate";
                    $newRateDescription = $promoCode->new_rate_description;
                }


                array_push($finalExcelData, [
                    'promo_code' => $promoCode->promo_code,
                    'registration_types' => $badgeTypesForPromoCode,
                    'discount_type' => $discountType,
                    'discount' => $discount,
                    'new_rate' => $newRate,
                    'new_rate_description' => $newRateDescription,
                    'remaining_usage' => $promoCode->number_of_codes - $promoCode->total_usage,
                    'total_usage' => $promoCode->total_usage,
                    'number_of_codes' => $promoCode->number_of_codes,
                    'validity' => $promoCode->validity,
                    'description' => $promoCode->description,
                    'status' => $promoCode->active ? 'Active' : 'Inactive',
                ]);
            }

            $currentDate = Carbon::now()->format('Y-m-d');
            $fileName = $eventCategory . ' ' . $eventYear . ' Promo Codes ' . '[' . $currentDate . '].csv';

            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $columns = array(
                'Promo Code',
                'Registration types',
                'Discount type',
                'Discount',
                'New rate',
                'New rate description',
                'Remaining usage',
                'Total usage',
                'Number of codes',
                'Validity',
                'Description',
                'Status'
            );

            $callback = function () use ($finalExcelData, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($finalExcelData as $data) {
                    fputcsv(
                        $file,
                        array(
                            $data['promo_code'],
                            $data['registration_types'],
                            $data['discount_type'],
                            $data['discount'],
                            $data['new_rate'],
                            $data['new_rate_description'],
                            $data['remaining_usage'],
                            $data['total_usage'],
                            $data['number_of_codes'],
                            $data['validity'],
                            $data['description'],
                            $data['status'],
                        )
                    );
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }
public function downloadPromoCodeTemplate($eventCategory, $eventId)
{
    $filename = "promo_codes_template.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function() {

        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'promo_code',
            'badgeType',
            'description',
            'new_rate_description',
            'discount_type',
            'discount',
            'new_rate',
            'number_of_codes',
            'validity'
        ]);

        // percentage example
        fputcsv($file, [
            'FOCVIP24',
            'VIP/Delegate',
            'Free of charge VIP',
            'N/A',
            'percentage',
            '100',
            '0',
            '100',
            '2026-12-31'
        ]);

        // fixed example
        fputcsv($file, [
            'VIPFIXED24',
            'VIP/Delegate',
            'VIP fixed rate',
            'VIP fixed rate',
            'fixed',
            '0',
            '200',
            '50',
            '2026-12-31'
        ]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
