<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypes;
use App\Mail\RegistrationCardDeclined;
use App\Mail\RegistrationPaid;
use App\Mail\RegistrationPaymentConfirmation;
use App\Models\AdditionalDelegate;
use App\Models\AdditionalSpouse;
use App\Models\AdditionalVisitor;
use App\Models\AwardsAdditionalParticipant;
use App\Models\AwardsMainParticipant;
use App\Models\AwardsParticipantDocument;
use App\Models\AwardsParticipantTransaction;
use App\Models\PromoCode;
use App\Models\Event;
use App\Models\MainDelegate;
use App\Models\MainSpouse;
use App\Models\MainVisitor;
use App\Models\PrintedBadge;
use App\Models\PromoCodeAddtionalBadgeType;
use App\Models\RccAwardsAdditionalParticipant;
use App\Models\RccAwardsDocument;
use App\Models\RccAwardsMainParticipant;
use App\Models\RccAwardsParticipantTransaction;
use App\Models\ScannedDelegate;
use App\Models\ScannedVisitor;
use App\Models\SpouseTransaction;
use App\Models\Transaction;
use App\Models\VisitorPrintedBadge;
use App\Models\VisitorTransaction;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session as Session;
use Illuminate\Support\Facades\Storage;
use NumberFormatter;
use Illuminate\Support\Str;


class RegistrationController extends Controller
{

    // =========================================================
    //                       RENDER VIEWS
    // =========================================================

    public function homepageView()
    {
        $events = Event::orderBy('event_start_date', 'asc')->get();
        $finalUpcomingEvents = array();
        $finalPastEvents = array();

        if (!$events->isEmpty()) {
            foreach ($events as $event) {
                if ($event->category != "GLF" && $event->category != "DFCLW1") {
                    $eventLink = env('APP_URL') . '/register/' . $event->year . '/' . $event->category . '/' . $event->id;
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M Y') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');

                    $eventEndDate = Carbon::parse($event->event_end_date);

                    if (Carbon::now()->lt($eventEndDate->addDay()) && $event->active) {
                        array_push($finalUpcomingEvents, [
                            'eventLogo' => $event->logo,
                            'eventName' => $event->name,
                            'eventCategory' => $event->category,
                            'eventDate' => $eventFormattedDate,
                            'eventLocation' => $event->location,
                            'eventDescription' => $event->description,
                            'eventLink' => $eventLink,
                        ]);
                    } else {
                        array_push($finalPastEvents, [
                            'eventLogo' => $event->logo,
                            'eventName' => $event->name,
                            'eventCategory' => $event->category,
                            'eventDate' => $eventFormattedDate,
                            'eventLocation' => $event->location,
                            'eventDescription' => $event->description,
                        ]);
                    }
                }
            }
        }
        return view('home.homepage', [
            'upcomingEvents' => $finalUpcomingEvents,
            'pastEvents' => $finalPastEvents,
        ]);
    }

    public function registrationFailedView($eventYear, $eventCategory, $eventId, $mainDelegateId)
    {
        if (Event::where('year', $eventYear)->where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('id', $eventId)->first();

            if ($eventCategory == "AFS") {
                $finalData = $this->registrationFailedViewSpouse($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
            } else if ($eventCategory == "AFV") {
                $finalData = $this->registrationFailedViewVisitor($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
            } else if ($eventCategory == "RCCA") {
                $finalData = $this->registrationFailedViewRccAwards($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('j F Y');
            } else if ($eventCategory == "SCEA") {
                $finalData = $this->registrationFailedViewAwards($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('j F Y');
            } else {
                $finalData = $this->registrationFailedViewEvents($eventCategory, $eventId, $mainDelegateId);
                if ($eventCategory == "GLF" || $eventCategory == "DFCLW1") {
                    $eventFormattedDate =  Carbon::parse($event->event_end_date)->format('d M Y');
                } else if ($eventCategory == "ANC" && $event->year = "2025") {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');
                } else if ($eventCategory == "PSW" && $event->year = "2025") {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');
                } else {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
                }
            }

            return view('registration.success-messages.registration_failed_message', [
                'pageTitle' => "Registration Failed",
                'event' => $event,
                'eventFormattedDate' =>  $eventFormattedDate,
                'invoiceLink' => $finalData['invoiceLink'],
                'bankDetails' => $finalData['bankDetails'],
                'paymentStatus' => $finalData['paymentStatus'],
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationSuccessView($eventYear, $eventCategory, $eventId, $mainDelegateId)
    {
        if (Event::where('year', $eventYear)->where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('id', $eventId)->first();

            if ($eventCategory == "AFS") {
                $finalData = $this->registrationSuccessViewSpouse($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
            } else if ($eventCategory == "AFV") {
                $finalData = $this->registrationSuccessViewVisitor($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
            } else if ($eventCategory == "RCCA") {
                $finalData = $this->registrationSuccessViewRccAwards($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('j F Y');
            } else if ($eventCategory == "SCEA") {
                $finalData = $this->registrationSuccessViewAwards($eventCategory, $eventId, $mainDelegateId);
                $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('j F Y');
            } else {
                $finalData = $this->registrationSuccessViewEvents($eventCategory, $eventId, $mainDelegateId);
                if ($eventCategory == "GLF" || $eventCategory == "DFCLW1") {
                    $eventFormattedDate =  Carbon::parse($event->event_end_date)->format('d M Y');
                } else if ($eventCategory == "ANC" && $event->year = "2025") {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');
                } else if ($eventCategory == "PSW" && $event->year = "2025") {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d M') . ' - ' . Carbon::parse($event->event_end_date)->format('d M Y');
                } else {
                    $eventFormattedDate =  Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');
                }
            }

            return view('registration.success-messages.registration_success_message', [
                'pageTitle' => "Registration Success",
                'event' => $event,
                'eventFormattedDate' =>  $eventFormattedDate,
                'invoiceLink' => $finalData['invoiceLink'],
                'bankDetails' => $finalData['bankDetails'],
                'paymentStatus' => $finalData['paymentStatus'],
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationLoadingView($eventYear, $eventCategory, $eventId, $mainDelegateId, $status)
    {
        $redirectLink = env('APP_URL') . '/register/' . $eventYear . '/' . $eventCategory . '/' . $eventId . '/' . $mainDelegateId . '/' . $status;
        return view('registration.registration_loading', [
            'redirectLink' => $redirectLink,
        ]);
    }

    public function registrationOTPView($eventYear, $eventCategory, $eventId)
    {
        if (Event::where('year', $eventYear)->where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if (Session::has('sessionId') && Session::has('paymentStatus') && Session::has('htmlOTP') && Session::has('orderId')) {
                return view('registration.registration_otp', [
                    'htmlCode' => Session::get('htmlOTP'),
                ]);
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationView($eventYear, $eventCategory, $eventId)
    {
        if (Event::where('year', $eventYear)->where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('year', $eventYear)->where('category', $eventCategory)->where('id', $eventId)->first();

            $eventEndDate = Carbon::parse($event->event_end_date);

            if ($event->active) {
                if (Carbon::now()->lt($eventEndDate->addDay())) {
                    if ($event->category == "DAW") {
                        $mainDelegates = MainDelegate::where('event_id', $eventId)->get();
                        $totalConfirmedDelegates = 0;

                        if ($mainDelegates->isNotEmpty()) {
                            foreach ($mainDelegates as $mainDelegate) {
                                if ($mainDelegate->delegate_replaced_by_id == null && (!$mainDelegate->delegate_refunded)) {
                                    if ($mainDelegate->registration_status == "confirmed") {
                                        $totalConfirmedDelegates++;
                                    }
                                }

                                $additionalDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegate->id)->get();
                                if ($additionalDelegates->isNotEmpty()) {
                                    foreach ($additionalDelegates as $additionalDelegate) {
                                        if ($additionalDelegate->delegate_replaced_by_id == null && (!$additionalDelegate->delegate_refunded)) {
                                            if ($mainDelegate->registration_status == "confirmed") {
                                                $totalConfirmedDelegates++;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($totalConfirmedDelegates >= 50) {
                            abort(404, 'The URL is incorrect');
                        } else {
                            return view('registration.registration', [
                                'pageTitle' => $event->name . " - Registration",
                                'event' => $event,
                            ]);
                        }
                    } else {
                        return view('registration.registration', [
                            'pageTitle' => $event->name . " - Registration",
                            'event' => $event,
                        ]);
                    }
                } else {
                    abort(404, 'The URL is incorrect');
                }
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    // public function eventOnsiteRegistrationView($eventCategory, $eventId){
    //     if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
    //         $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

    //         $eventEndDate = Carbon::parse($event->event_end_date);

    //         if (Carbon::now()->lt($eventEndDate->addDay())) {
    //             return view('registration.registration', [
    //                 'pageTitle' => $event->name . " - Onsite Registration",
    //                 'event' => $event,
    //             ]);
    //         } else {
    //             abort(404, 'The URL is incorrect');
    //         }
    //     } else {
    //         abort(404, 'The URL is incorrect');
    //     }
    // }

    public function eventRegistrantsView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            return view('admin.events.transactions.registrants', [
                "pageTitle" => "Transactions",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantDetailView($eventCategory, $eventId, $registrantId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if ($eventCategory == "AFS") {
                $finalData = $this->registrantDetailSpousesView($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "AFV") {
                $finalData = $this->registrantDetailVisitorsView($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "RCCA") {
                $finalData = $this->registrantDetailRCCAwardsView($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "SCEA") {
                $finalData = $this->registrantDetailAwardsView($eventCategory, $eventId, $registrantId);
            } else {
                $finalData = $this->registrantDetailEventsView($eventCategory, $eventId, $registrantId);
            }

            // dd($finalData);
            return view('admin.events.transactions.registrants_detail', [
                "pageTitle" => "Transaction Details",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
                "registrantId" => $registrantId,
                "finalData" => $finalData,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantDetailEventsView($eventCategory, $eventId, $registrantId)
    {
        if (MainDelegate::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $finalData = array();

            $subDelegatesArray = array();
            $subDelegatesReplacementArray = array();
            $allDelegatesArray = array();
            $allDelegatesArrayTemp = array();

            $countFinalQuantity = 0;

            $eventYear = Event::where('id', $eventId)->value('year');
            $mainDelegate = MainDelegate::where('id', $registrantId)->where('event_id', $eventId)->first();

            $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainDelegate->pcode_used)->first();

            if ($promoCode != null) {
                if ($promoCode->badge_type == $mainDelegate->badge_type) {
                    $mainDiscount = $promoCode->discount;
                    $mainDiscountType = $promoCode->discount_type;
                } else {
                    $promoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $promoCode->id)->where('badge_type', $mainDelegate->badge_type)->first();

                    if ($promoCodeAdditionalBadgeType != null) {
                        $mainDiscount = $promoCode->discount;
                        $mainDiscountType = $promoCode->discount_type;
                    } else {
                        $mainDiscount = 0;
                        $mainDiscountType = null;
                    }
                }
            } else {
                $mainDiscount = 0;
                $mainDiscountType = null;
            }

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $tempYear = Carbon::parse($mainDelegate->registered_date_time)->format('y');
            $transactionId = Transaction::where('delegate_id', $mainDelegate->id)->where('delegate_type', "main")->value('id');
            $lastDigit = 1000 + intval($transactionId);
            $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;


            if ($mainDelegate->delegate_replaced_by_id == null && (!$mainDelegate->delegate_refunded)) {
                $countFinalQuantity++;
            }

            $subDelegates = AdditionalDelegate::where('main_delegate_id', $registrantId)->get();
            if (!$subDelegates->isEmpty()) {
                foreach ($subDelegates as $subDelegate) {
                    if ($subDelegate->delegate_replaced_by_id == null && (!$subDelegate->delegate_refunded)) {
                        $countFinalQuantity++;
                    }

                    $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subDelegate->pcode_used)->first();

                    if ($subPromoCode != null) {
                        if ($subPromoCode->badge_type == $subDelegate->badge_type) {
                            $subDiscount = $subPromoCode->discount;
                            $subDiscountType = $subPromoCode->discount_type;
                        } else {
                            $subPromoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $subPromoCode->id)->where('badge_type', $subDelegate->badge_type)->first();

                            if ($subPromoCodeAdditionalBadgeType != null) {
                                $subDiscount = $subPromoCode->discount;
                                $subDiscountType = $subPromoCode->discount_type;
                            } else {
                                $subDiscount = 0;
                                $subDiscountType = null;
                            }
                        }
                    } else {
                        $subDiscount = 0;
                        $subDiscountType = null;
                    }


                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($eventCategory == $eventCategoryC) {
                            $eventCode = $code;
                        }
                    }

                    if ($subDelegate->delegate_replaced_from_id != null) {
                        array_push($subDelegatesReplacementArray, [
                            'subDelegateId' => $subDelegate->id,
                            'name' => $subDelegate->salutation . " " . $subDelegate->first_name . " " . $subDelegate->middle_name . " " . $subDelegate->last_name,
                            'salutation' => $subDelegate->salutation,
                            'first_name' => $subDelegate->first_name,
                            'middle_name' => $subDelegate->middle_name,
                            'last_name' => $subDelegate->last_name,
                            'email_address' => $subDelegate->email_address,
                            'mobile_number' => $subDelegate->mobile_number,
                            'nationality' => $subDelegate->nationality,
                            'job_title' => $subDelegate->job_title,
                            'badge_type' => $subDelegate->badge_type,
                            'pcode_used' => $subDelegate->pcode_used,
                            'discount' => $subDiscount,
                            'discount_type' => $subDiscountType,
                            'country' => $subDelegate->country,

                            'interests' => $subDelegate->interests ? json_decode($subDelegate->interests, true) : [],

                            'delegate_cancelled' => $subDelegate->delegate_cancelled,
                            'delegate_replaced' => $subDelegate->delegate_replaced,
                            'delegate_refunded' => $subDelegate->delegate_refunded,

                            'delegate_replaced_type' => $subDelegate->delegate_replaced_type,
                            'delegate_original_from_id' => $subDelegate->delegate_original_from_id,
                            'delegate_replaced_from_id' => $subDelegate->delegate_replaced_from_id,
                            'delegate_replaced_by_id' => $subDelegate->delegate_replaced_by_id,

                            'delegate_cancelled_datetime' => $subDelegate->delegate_cancelled_datetime,
                            'delegate_refunded_datetime' => $subDelegate->delegate_refunded_datetime,
                            'delegate_replaced_datetime' => $subDelegate->delegate_replaced_datetime,

                            'registration_confirmation_sent_count' => $subDelegate->registration_confirmation_sent_count,
                            'registration_confirmation_sent_datetime' => $subDelegate->registration_confirmation_sent_datetime,
                        ]);
                    } else {
                        array_push($subDelegatesArray, [
                            'subDelegateId' => $subDelegate->id,
                            'name' => $subDelegate->salutation . " " . $subDelegate->first_name . " " . $subDelegate->middle_name . " " . $subDelegate->last_name,
                            'salutation' => $subDelegate->salutation,
                            'first_name' => $subDelegate->first_name,
                            'middle_name' => $subDelegate->middle_name,
                            'last_name' => $subDelegate->last_name,
                            'email_address' => $subDelegate->email_address,
                            'mobile_number' => $subDelegate->mobile_number,
                            'nationality' => $subDelegate->nationality,
                            'job_title' => $subDelegate->job_title,
                            'badge_type' => $subDelegate->badge_type,
                            'pcode_used' => $subDelegate->pcode_used,
                            'discount' => $subDiscount,
                            'discount_type' => $subDiscountType,
                            'country' => $subDelegate->country,

                            'interests' => $subDelegate->interests ? json_decode($subDelegate->interests, true) : [],

                            'delegate_cancelled' => $subDelegate->delegate_cancelled,
                            'delegate_replaced' => $subDelegate->delegate_replaced,
                            'delegate_refunded' => $subDelegate->delegate_refunded,

                            'delegate_replaced_type' => $subDelegate->delegate_replaced_type,
                            'delegate_original_from_id' => $subDelegate->delegate_original_from_id,
                            'delegate_replaced_from_id' => $subDelegate->delegate_replaced_from_id,
                            'delegate_replaced_by_id' => $subDelegate->delegate_replaced_by_id,

                            'delegate_cancelled_datetime' => $subDelegate->delegate_cancelled_datetime,
                            'delegate_refunded_datetime' => $subDelegate->delegate_refunded_datetime,
                            'delegate_replaced_datetime' => $subDelegate->delegate_replaced_datetime,

                            'registration_confirmation_sent_count' => $subDelegate->registration_confirmation_sent_count,
                            'registration_confirmation_sent_datetime' => $subDelegate->registration_confirmation_sent_datetime,
                        ]);
                    }
                }
            }


            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

            array_push($allDelegatesArrayTemp, [
                'transactionId' => $finalTransactionId,
                'mainDelegateId' => $mainDelegate->id,
                'delegateId' => $mainDelegate->id,
                'delegateType' => "main",

                'name' => $mainDelegate->salutation . " " . $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                'salutation' => $mainDelegate->salutation,
                'first_name' => $mainDelegate->first_name,
                'middle_name' => $mainDelegate->middle_name,
                'last_name' => $mainDelegate->last_name,
                'email_address' => $mainDelegate->email_address,
                'mobile_number' => $mainDelegate->mobile_number,
                'nationality' => $mainDelegate->nationality,
                'job_title' => $mainDelegate->job_title,
                'badge_type' => $mainDelegate->badge_type,
                'pcode_used' => $mainDelegate->pcode_used,
                'discount' => $mainDiscount,
                'discount_type' => $mainDiscountType,
                'country' => $mainDelegate->country,

                'interests' => $mainDelegate->interests ? json_decode($mainDelegate->interests, true) : [],

                'is_replacement' => false,
                'delegate_cancelled' => $mainDelegate->delegate_cancelled,
                'delegate_replaced' => $mainDelegate->delegate_replaced,
                'delegate_refunded' => $mainDelegate->delegate_refunded,

                'delegate_replaced_type' => "main",
                'delegate_original_from_id' => $mainDelegate->id,
                'delegate_replaced_from_id' => null,
                'delegate_replaced_by_id' => $mainDelegate->delegate_replaced_by_id,

                'delegate_cancelled_datetime' => ($mainDelegate->delegate_cancelled_datetime == null) ? "N/A" : Carbon::parse($mainDelegate->delegate_cancelled_datetime)->format('M j, Y g:i A'),
                'delegate_refunded_datetime' => ($mainDelegate->delegate_refunded_datetime == null) ? "N/A" : Carbon::parse($mainDelegate->delegate_refunded_datetime)->format('M j, Y g:i A'),
                'delegate_replaced_datetime' => ($mainDelegate->delegate_replaced_datetime == null) ? "N/A" : Carbon::parse($mainDelegate->delegate_replaced_datetime)->format('M j, Y g:i A'),

                'registration_confirmation_sent_count' => $mainDelegate->registration_confirmation_sent_count,
                'registration_confirmation_sent_datetime' => ($mainDelegate->registration_confirmation_sent_datetime == null) ? "N/A" : Carbon::parse($mainDelegate->registration_confirmation_sent_datetime)->format('M j, Y g:i A'),
            ]);

            if ($mainDelegate->delegate_replaced_by_id != null) {
                foreach ($subDelegatesReplacementArray as $subDelegateReplacement) {
                    if ($mainDelegate->id == $subDelegateReplacement['delegate_original_from_id'] && $subDelegateReplacement['delegate_replaced_type'] == "main") {

                        $transactionId = Transaction::where('delegate_id', $subDelegateReplacement['subDelegateId'])->where('delegate_type', "sub")->value('id');
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                        array_push($allDelegatesArrayTemp, [
                            'transactionId' => $finalTransactionId,
                            'mainDelegateId' => $mainDelegate->id,
                            'delegateId' => $subDelegateReplacement['subDelegateId'],
                            'delegateType' => "sub",

                            'name' => $subDelegateReplacement['salutation'] . " " . $subDelegateReplacement['first_name'] . " " . $subDelegateReplacement['middle_name'] . " " . $subDelegateReplacement['last_name'],
                            'salutation' => $subDelegateReplacement['salutation'],
                            'first_name' => $subDelegateReplacement['first_name'],
                            'middle_name' => $subDelegateReplacement['middle_name'],
                            'last_name' => $subDelegateReplacement['last_name'],
                            'email_address' => $subDelegateReplacement['email_address'],
                            'mobile_number' => $subDelegateReplacement['mobile_number'],
                            'nationality' => $subDelegateReplacement['nationality'],
                            'job_title' => $subDelegateReplacement['job_title'],
                            'badge_type' => $subDelegateReplacement['badge_type'],
                            'pcode_used' => $subDelegateReplacement['pcode_used'],
                            'discount' => $subDelegateReplacement['discount'],
                            'discount_type' => $subDelegateReplacement['discount_type'],
                            'country' => $subDelegateReplacement['country'],

                            'interests' => $subDelegateReplacement['interests'],

                            'is_replacement' => true,
                            'delegate_cancelled' => $subDelegateReplacement['delegate_cancelled'],
                            'delegate_replaced' => $subDelegateReplacement['delegate_replaced'],
                            'delegate_refunded' => $subDelegateReplacement['delegate_refunded'],

                            'delegate_replaced_type' => $subDelegateReplacement['delegate_replaced_type'],
                            'delegate_original_from_id' => $subDelegateReplacement['delegate_original_from_id'],
                            'delegate_replaced_from_id' => $subDelegateReplacement['delegate_replaced_from_id'],
                            'delegate_replaced_by_id' => $subDelegateReplacement['delegate_replaced_by_id'],

                            'delegate_cancelled_datetime' => ($subDelegateReplacement['delegate_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_cancelled_datetime'])->format('M j, Y g:i A'),
                            'delegate_refunded_datetime' => ($subDelegateReplacement['delegate_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_refunded_datetime'])->format('M j, Y g:i A'),
                            'delegate_replaced_datetime' => ($subDelegateReplacement['delegate_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_replaced_datetime'])->format('M j, Y g:i A'),

                            'registration_confirmation_sent_count' => $subDelegateReplacement['registration_confirmation_sent_count'],
                            'registration_confirmation_sent_datetime' => ($subDelegateReplacement['registration_confirmation_sent_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['registration_confirmation_sent_datetime'])->format('M j, Y g:i A'),
                        ]);
                    }
                }
            }

            array_push($allDelegatesArray, $allDelegatesArrayTemp);

            $allDelegatesArrayTemp = array();

            foreach ($subDelegatesArray as $subDelegate) {
                $allDelegatesArrayTemp = array();

                $transactionId = Transaction::where('delegate_id', $subDelegate['subDelegateId'])->where('delegate_type', "sub")->value('id');
                $lastDigit = 1000 + intval($transactionId);
                $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                array_push($allDelegatesArrayTemp, [
                    'transactionId' => $finalTransactionId,
                    'mainDelegateId' => $mainDelegate->id,
                    'delegateId' => $subDelegate['subDelegateId'],
                    'delegateType' => "sub",

                    'name' => $subDelegate['salutation'] . " " . $subDelegate['first_name'] . " " . $subDelegate['middle_name'] . " " . $subDelegate['last_name'],
                    'salutation' => $subDelegate['salutation'],
                    'first_name' => $subDelegate['first_name'],
                    'middle_name' => $subDelegate['middle_name'],
                    'last_name' => $subDelegate['last_name'],
                    'email_address' => $subDelegate['email_address'],
                    'mobile_number' => $subDelegate['mobile_number'],
                    'nationality' => $subDelegate['nationality'],
                    'job_title' => $subDelegate['job_title'],
                    'badge_type' => $subDelegate['badge_type'],
                    'pcode_used' => $subDelegate['pcode_used'],
                    'discount' => $subDelegate['discount'],
                    'discount_type' => $subDelegate['discount_type'],
                    'country' => $subDelegate['country'],

                    'interests' => $subDelegate['interests'],

                    'is_replacement' => false,
                    'delegate_cancelled' => $subDelegate['delegate_cancelled'],
                    'delegate_replaced' => $subDelegate['delegate_replaced'],
                    'delegate_refunded' => $subDelegate['delegate_refunded'],

                    'delegate_replaced_type' => "sub",
                    'delegate_original_from_id' => $subDelegate['subDelegateId'],
                    'delegate_replaced_from_id' => null,
                    'delegate_replaced_by_id' => $subDelegate['delegate_replaced_by_id'],

                    'delegate_cancelled_datetime' => ($subDelegate['delegate_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subDelegate['delegate_cancelled_datetime'])->format('M j, Y g:i A'),
                    'delegate_refunded_datetime' => ($subDelegate['delegate_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subDelegate['delegate_refunded_datetime'])->format('M j, Y g:i A'),
                    'delegate_replaced_datetime' => ($subDelegate['delegate_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subDelegate['delegate_replaced_datetime'])->format('M j, Y g:i A'),

                    'registration_confirmation_sent_count' => $subDelegate['registration_confirmation_sent_count'],
                    'registration_confirmation_sent_datetime' => ($subDelegate['registration_confirmation_sent_datetime'] == null) ? "N/A" : Carbon::parse($subDelegate['registration_confirmation_sent_datetime'])->format('M j, Y g:i A'),
                ]);

                if ($subDelegate['delegate_replaced_by_id'] != null) {
                    foreach ($subDelegatesReplacementArray as $subDelegateReplacement) {
                        if ($subDelegate['subDelegateId']  == $subDelegateReplacement['delegate_original_from_id'] && $subDelegateReplacement['delegate_replaced_type'] == "sub") {

                            $transactionId = Transaction::where('delegate_id', $subDelegateReplacement['subDelegateId'])->where('delegate_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                            array_push($allDelegatesArrayTemp, [
                                'transactionId' => $finalTransactionId,
                                'mainDelegateId' => $mainDelegate->id,
                                'delegateId' => $subDelegateReplacement['subDelegateId'],
                                'delegateType' => "sub",

                                'name' => $subDelegateReplacement['salutation'] . " " . $subDelegateReplacement['first_name'] . " " . $subDelegateReplacement['middle_name'] . " " . $subDelegateReplacement['last_name'],
                                'salutation' => $subDelegateReplacement['salutation'],
                                'first_name' => $subDelegateReplacement['first_name'],
                                'middle_name' => $subDelegateReplacement['middle_name'],
                                'last_name' => $subDelegateReplacement['last_name'],
                                'email_address' => $subDelegateReplacement['email_address'],
                                'mobile_number' => $subDelegateReplacement['mobile_number'],
                                'nationality' => $subDelegateReplacement['nationality'],
                                'job_title' => $subDelegateReplacement['job_title'],
                                'badge_type' => $subDelegateReplacement['badge_type'],
                                'pcode_used' => $subDelegateReplacement['pcode_used'],
                                'discount' => $subDelegateReplacement['discount'],
                                'discount_type' => $subDelegateReplacement['discount_type'],
                                'country' => $subDelegateReplacement['country'],

                                'interests' => $subDelegateReplacement['interests'],

                                'is_replacement' => true,
                                'delegate_cancelled' => $subDelegateReplacement['delegate_cancelled'],
                                'delegate_replaced' => $subDelegateReplacement['delegate_replaced'],
                                'delegate_refunded' => $subDelegateReplacement['delegate_refunded'],

                                'delegate_replaced_type' => "sub",
                                'delegate_original_from_id' => $subDelegate['subDelegateId'],
                                'delegate_replaced_from_id' => $subDelegateReplacement['delegate_replaced_from_id'],
                                'delegate_replaced_by_id' => $subDelegateReplacement['delegate_replaced_by_id'],

                                'delegate_cancelled_datetime' => ($subDelegateReplacement['delegate_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_cancelled_datetime'])->format('M j, Y g:i A'),
                                'delegate_refunded_datetime' => ($subDelegateReplacement['delegate_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_refunded_datetime'])->format('M j, Y g:i A'),
                                'delegate_replaced_datetime' => ($subDelegateReplacement['delegate_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['delegate_replaced_datetime'])->format('M j, Y g:i A'),

                                'registration_confirmation_sent_count' => $subDelegateReplacement['registration_confirmation_sent_count'],
                                'registration_confirmation_sent_datetime' => ($subDelegateReplacement['registration_confirmation_sent_datetime'] == null) ? "N/A" : Carbon::parse($subDelegateReplacement['registration_confirmation_sent_datetime'])->format('M j, Y g:i A'),
                            ]);
                        }
                    }
                }
                array_push($allDelegatesArray, $allDelegatesArrayTemp);
            }

            // dd($allDelegatesArray);

            if ($mainDelegate->rate_type == "standard" || $mainDelegate->rate_type == "Standard") {
                $finalRateType = "Standard";
            } else {
                $finalRateType = "Early Bird";
            }

            $finalData = [
                'mainDelegateId' => $mainDelegate->id,
                'access_type' => $mainDelegate->access_type,
                'pass_type' => $mainDelegate->pass_type,
                'rate_type' => $finalRateType,
                'rate_type_string' => $mainDelegate->rate_type_string,
                'company_name' => $mainDelegate->company_name,
                'alternative_company_name' => $mainDelegate->alternative_company_name,
                'company_sector' => $mainDelegate->company_sector,
                'company_address' => $mainDelegate->company_address,
                'company_country' => $mainDelegate->company_country,
                'company_city' => $mainDelegate->company_city,
                'company_telephone_number' => $mainDelegate->company_telephone_number,
                'company_mobile_number' => $mainDelegate->company_mobile_number,
                'assistant_email_address' => $mainDelegate->assistant_email_address,
                'heard_where' => $mainDelegate->heard_where,
                'quantity' => $mainDelegate->quantity,
                'finalQuantity' => $countFinalQuantity,
                'pc_attending_nd' => $mainDelegate->pc_attending_nd,
                'scc_attending_nd' => $mainDelegate->scc_attending_nd,
                'car_park_needed' => $mainDelegate->car_park_needed,

                'attending_plenary' => $mainDelegate->attending_plenary,
                'attending_symposium' => $mainDelegate->attending_symposium,
                'attending_sustainability' => $mainDelegate->attending_sustainability,
                'attending_solxchange' => $mainDelegate->attending_solxchange,
                'attending_yf' => $mainDelegate->attending_yf,
                'attending_networking_dinner' => $mainDelegate->attending_networking_dinner,
                'attending_welcome_dinner' => $mainDelegate->attending_welcome_dinner,
                'attending_gala_dinner' => $mainDelegate->attending_gala_dinner,

                'receive_whatsapp_notifications' => $mainDelegate->receive_whatsapp_notifications,

                'optional_interests' => $mainDelegate->optional_interests,

                'mode_of_payment' => $mainDelegate->mode_of_payment,
                'registration_status' => "$mainDelegate->registration_status",
                'payment_status' => $mainDelegate->payment_status,
                'registered_date_time' => Carbon::parse($mainDelegate->registered_date_time)->format('M j, Y g:i A'),
                'paid_date_time' => ($mainDelegate->paid_date_time == null) ? "N/A" : Carbon::parse($mainDelegate->paid_date_time)->format('M j, Y g:i A'),

                'registration_method' => $mainDelegate->registration_method,
                'transaction_remarks' => $mainDelegate->transaction_remarks,

                'invoiceNumber' => $invoiceNumber,
                'allDelegates' => $allDelegatesArray,

                'invoiceData' => $this->getInvoice($eventCategory, $eventId, $registrantId),
            ];
            // dd($finalData);
            return $finalData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantDetailSpousesView($eventCategory, $eventId, $registrantId)
    {
        if (MainSpouse::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $finalData = array();

            $subSpousesArray = array();
            $subSpousesReplacementArray = array();
            $allSpousesArray = array();
            $allSpousesArrayTemp = array();

            $countFinalQuantity = 0;

            $eventYear = Event::where('id', $eventId)->value('year');
            $mainSpouse = MainSpouse::where('id', $registrantId)->where('event_id', $eventId)->first();

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $tempYear = Carbon::parse($mainSpouse->registered_date_time)->format('y');
            $transactionId = SpouseTransaction::where('spouse_id', $mainSpouse->id)->where('spouse_type', "main")->value('id');
            $lastDigit = 1000 + intval($transactionId);
            $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;


            if ($mainSpouse->spouse_replaced_by_id == null && (!$mainSpouse->spouse_refunded)) {
                $countFinalQuantity++;
            }

            $subSpouses = AdditionalSpouse::where('main_spouse_id', $registrantId)->get();
            if (!$subSpouses->isEmpty()) {
                foreach ($subSpouses as $subSpouse) {
                    if ($subSpouse->spouse_replaced_by_id == null && (!$subSpouse->spouse_refunded)) {
                        $countFinalQuantity++;
                    }

                    if ($subSpouse->spouse_replaced_from_id != null) {
                        array_push($subSpousesReplacementArray, [
                            'subSpouseId' => $subSpouse->id,
                            'name' => $subSpouse->salutation . " " . $subSpouse->first_name . " " . $subSpouse->middle_name . " " . $subSpouse->last_name,
                            'salutation' => $subSpouse->salutation,
                            'first_name' => $subSpouse->first_name,
                            'middle_name' => $subSpouse->middle_name,
                            'last_name' => $subSpouse->last_name,
                            'email_address' => $subSpouse->email_address,
                            'mobile_number' => $subSpouse->mobile_number,
                            'nationality' => $subSpouse->nationality,
                            'country' => $subSpouse->country,
                            'city' => $subSpouse->city,

                            'spouse_cancelled' => $subSpouse->spouse_cancelled,
                            'spouse_replaced' => $subSpouse->spouse_replaced,
                            'spouse_refunded' => $subSpouse->spouse_refunded,

                            'spouse_replaced_type' => $subSpouse->spouse_replaced_type,
                            'spouse_original_from_id' => $subSpouse->spouse_original_from_id,
                            'spouse_replaced_from_id' => $subSpouse->spouse_replaced_from_id,
                            'spouse_replaced_by_id' => $subSpouse->spouse_replaced_by_id,

                            'spouse_cancelled_datetime' => $subSpouse->spouse_cancelled_datetime,
                            'spouse_refunded_datetime' => $subSpouse->spouse_refunded_datetime,
                            'spouse_replaced_datetime' => $subSpouse->spouse_replaced_datetime,
                        ]);
                    } else {
                        array_push($subSpousesArray, [
                            'subSpouseId' => $subSpouse->id,
                            'name' => $subSpouse->salutation . " " . $subSpouse->first_name . " " . $subSpouse->middle_name . " " . $subSpouse->last_name,
                            'salutation' => $subSpouse->salutation,
                            'first_name' => $subSpouse->first_name,
                            'middle_name' => $subSpouse->middle_name,
                            'last_name' => $subSpouse->last_name,
                            'email_address' => $subSpouse->email_address,
                            'mobile_number' => $subSpouse->mobile_number,
                            'nationality' => $subSpouse->nationality,
                            'country' => $subSpouse->country,
                            'city' => $subSpouse->city,

                            'spouse_cancelled' => $subSpouse->spouse_cancelled,
                            'spouse_replaced' => $subSpouse->spouse_replaced,
                            'spouse_refunded' => $subSpouse->spouse_refunded,

                            'spouse_replaced_type' => $subSpouse->spouse_replaced_type,
                            'spouse_original_from_id' => $subSpouse->spouse_original_from_id,
                            'spouse_replaced_from_id' => $subSpouse->spouse_replaced_from_id,
                            'spouse_replaced_by_id' => $subSpouse->spouse_replaced_by_id,

                            'spouse_cancelled_datetime' => $subSpouse->spouse_cancelled_datetime,
                            'spouse_refunded_datetime' => $subSpouse->spouse_refunded_datetime,
                            'spouse_replaced_datetime' => $subSpouse->spouse_replaced_datetime,
                        ]);
                    }
                }
            }


            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

            array_push($allSpousesArrayTemp, [
                'transactionId' => $finalTransactionId,
                'mainSpouseId' => $mainSpouse->id,
                'spouseId' => $mainSpouse->id,
                'spouseType' => "main",

                'name' => $mainSpouse->salutation . " " . $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name,
                'salutation' => $mainSpouse->salutation,
                'first_name' => $mainSpouse->first_name,
                'middle_name' => $mainSpouse->middle_name,
                'last_name' => $mainSpouse->last_name,
                'email_address' => $mainSpouse->email_address,
                'mobile_number' => $mainSpouse->mobile_number,
                'nationality' => $mainSpouse->nationality,
                'country' => $mainSpouse->country,
                'city' => $mainSpouse->city,

                'is_replacement' => false,
                'spouse_cancelled' => $mainSpouse->spouse_cancelled,
                'spouse_replaced' => $mainSpouse->spouse_replaced,
                'spouse_refunded' => $mainSpouse->spouse_refunded,

                'spouse_replaced_type' => "main",
                'spouse_original_from_id' => $mainSpouse->id,
                'spouse_replaced_from_id' => null,
                'spouse_replaced_by_id' => $mainSpouse->spouse_replaced_by_id,

                'spouse_cancelled_datetime' => ($mainSpouse->spouse_cancelled_datetime == null) ? "N/A" : Carbon::parse($mainSpouse->spouse_cancelled_datetime)->format('M j, Y g:i A'),
                'spouse_refunded_datetime' => ($mainSpouse->spouse_refunded_datetime == null) ? "N/A" : Carbon::parse($mainSpouse->spouse_refunded_datetime)->format('M j, Y g:i A'),
                'spouse_replaced_datetime' => ($mainSpouse->spouse_replaced_datetime == null) ? "N/A" : Carbon::parse($mainSpouse->spouse_replaced_datetime)->format('M j, Y g:i A'),
            ]);

            if ($mainSpouse->spouse_replaced_by_id != null) {
                foreach ($subSpousesReplacementArray as $subSpouseReplacement) {
                    if ($mainSpouse->id == $subSpouseReplacement['spouse_original_from_id'] && $subSpouseReplacement['spouse_replaced_type'] == "main") {

                        $transactionId = SpouseTransaction::where('spouse_id', $subSpouseReplacement['subSpouseId'])->where('spouse_type', "sub")->value('id');
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                        array_push($allSpousesArrayTemp, [
                            'transactionId' => $finalTransactionId,
                            'mainSpouseId' => $mainSpouse->id,
                            'spouseId' => $subSpouseReplacement['subSpouseId'],
                            'spouseType' => "sub",

                            'name' => $subSpouseReplacement['salutation'] . " " . $subSpouseReplacement['first_name'] . " " . $subSpouseReplacement['middle_name'] . " " . $subSpouseReplacement['last_name'],
                            'salutation' => $subSpouseReplacement['salutation'],
                            'first_name' => $subSpouseReplacement['first_name'],
                            'middle_name' => $subSpouseReplacement['middle_name'],
                            'last_name' => $subSpouseReplacement['last_name'],
                            'email_address' => $subSpouseReplacement['email_address'],
                            'mobile_number' => $subSpouseReplacement['mobile_number'],
                            'nationality' => $subSpouseReplacement['nationality'],
                            'country' => $subSpouseReplacement['country'],
                            'city' => $subSpouseReplacement['city'],

                            'is_replacement' => true,
                            'spouse_cancelled' => $subSpouseReplacement['spouse_cancelled'],
                            'spouse_replaced' => $subSpouseReplacement['spouse_replaced'],
                            'spouse_refunded' => $subSpouseReplacement['spouse_refunded'],

                            'spouse_replaced_type' => $subSpouseReplacement['spouse_replaced_type'],
                            'spouse_original_from_id' => $subSpouseReplacement['spouse_original_from_id'],
                            'spouse_replaced_from_id' => $subSpouseReplacement['spouse_replaced_from_id'],
                            'spouse_replaced_by_id' => $subSpouseReplacement['spouse_replaced_by_id'],

                            'spouse_cancelled_datetime' => ($subSpouseReplacement['spouse_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_cancelled_datetime'])->format('M j, Y g:i A'),
                            'spouse_refunded_datetime' => ($subSpouseReplacement['spouse_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_refunded_datetime'])->format('M j, Y g:i A'),
                            'spouse_replaced_datetime' => ($subSpouseReplacement['spouse_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_replaced_datetime'])->format('M j, Y g:i A'),
                        ]);
                    }
                }
            }

            array_push($allSpousesArray, $allSpousesArrayTemp);

            $allSpousesArrayTemp = array();

            foreach ($subSpousesArray as $subSpouse) {
                $allSpousesArrayTemp = array();

                $transactionId = SpouseTransaction::where('spouse_id', $subSpouse['subSpouseId'])->where('spouse_type', "sub")->value('id');
                $lastDigit = 1000 + intval($transactionId);
                $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                array_push($allSpousesArrayTemp, [
                    'transactionId' => $finalTransactionId,
                    'mainSpouseId' => $mainSpouse->id,
                    'spouseId' => $subSpouse['subSpouseId'],
                    'spouseType' => "sub",

                    'name' => $subSpouse['salutation'] . " " . $subSpouse['first_name'] . " " . $subSpouse['middle_name'] . " " . $subSpouse['last_name'],
                    'salutation' => $subSpouse['salutation'],
                    'first_name' => $subSpouse['first_name'],
                    'middle_name' => $subSpouse['middle_name'],
                    'last_name' => $subSpouse['last_name'],
                    'email_address' => $subSpouse['email_address'],
                    'mobile_number' => $subSpouse['mobile_number'],
                    'nationality' => $subSpouse['nationality'],
                    'country' => $subSpouse['country'],
                    'city' => $subSpouse['city'],

                    'is_replacement' => false,
                    'spouse_cancelled' => $subSpouse['spouse_cancelled'],
                    'spouse_replaced' => $subSpouse['spouse_replaced'],
                    'spouse_refunded' => $subSpouse['spouse_refunded'],

                    'spouse_replaced_type' => "sub",
                    'spouse_original_from_id' => $subSpouse['subSpouseId'],
                    'spouse_replaced_from_id' => null,
                    'spouse_replaced_by_id' => $subSpouse['spouse_replaced_by_id'],

                    'spouse_cancelled_datetime' => ($subSpouse['spouse_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subSpouse['spouse_cancelled_datetime'])->format('M j, Y g:i A'),
                    'spouse_refunded_datetime' => ($subSpouse['spouse_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subSpouse['spouse_refunded_datetime'])->format('M j, Y g:i A'),
                    'spouse_replaced_datetime' => ($subSpouse['spouse_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subSpouse['spouse_replaced_datetime'])->format('M j, Y g:i A'),
                ]);

                if ($subSpouse['spouse_replaced_by_id'] != null) {
                    foreach ($subSpousesReplacementArray as $subSpouseReplacement) {
                        if ($subSpouse['subSpouseId'] == $subSpouseReplacement['spouse_original_from_id'] && $subSpouseReplacement['spouse_replaced_type'] == "sub") {

                            $transactionId = SpouseTransaction::where('spouse_id', $subSpouseReplacement['subSpouseId'])->where('spouse_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                            array_push($allSpousesArrayTemp, [
                                'transactionId' => $finalTransactionId,
                                'mainSpouseId' => $mainSpouse->id,
                                'spouseId' => $subSpouseReplacement['subSpouseId'],
                                'spouseType' => "sub",

                                'name' => $subSpouseReplacement['salutation'] . " " . $subSpouseReplacement['first_name'] . " " . $subSpouseReplacement['middle_name'] . " " . $subSpouseReplacement['last_name'],
                                'salutation' => $subSpouseReplacement['salutation'],
                                'first_name' => $subSpouseReplacement['first_name'],
                                'middle_name' => $subSpouseReplacement['middle_name'],
                                'last_name' => $subSpouseReplacement['last_name'],
                                'email_address' => $subSpouseReplacement['email_address'],
                                'mobile_number' => $subSpouseReplacement['mobile_number'],
                                'nationality' => $subSpouseReplacement['nationality'],
                                'country' => $subSpouseReplacement['country'],
                                'city' => $subSpouseReplacement['city'],

                                'is_replacement' => true,
                                'spouse_cancelled' => $subSpouseReplacement['spouse_cancelled'],
                                'spouse_replaced' => $subSpouseReplacement['spouse_replaced'],
                                'spouse_refunded' => $subSpouseReplacement['spouse_refunded'],

                                'spouse_replaced_type' => "sub",
                                'spouse_original_from_id' => $subSpouse['subSpouseId'],
                                'spouse_replaced_from_id' => $subSpouseReplacement['spouse_replaced_from_id'],
                                'spouse_replaced_by_id' => $subSpouseReplacement['spouse_replaced_by_id'],

                                'spouse_cancelled_datetime' => ($subSpouseReplacement['spouse_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_cancelled_datetime'])->format('M j, Y g:i A'),
                                'spouse_refunded_datetime' => ($subSpouseReplacement['spouse_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_refunded_datetime'])->format('M j, Y g:i A'),
                                'spouse_replaced_datetime' => ($subSpouseReplacement['spouse_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subSpouseReplacement['spouse_replaced_datetime'])->format('M j, Y g:i A'),
                            ]);
                        }
                    }
                }
                array_push($allSpousesArray, $allSpousesArrayTemp);
            }

            $finalData = [
                'mainSpouseId' => $mainSpouse->id,
                'reference_delegate_name' => $mainSpouse->reference_delegate_name,
                'heard_where' => $mainSpouse->heard_where,
                'quantity' => $mainSpouse->quantity,
                'finalQuantity' => $countFinalQuantity,

                'mode_of_payment' => $mainSpouse->mode_of_payment,
                'registration_status' => $mainSpouse->registration_status,
                'payment_status' => $mainSpouse->payment_status,
                'registered_date_time' => Carbon::parse($mainSpouse->registered_date_time)->format('M j, Y g:i A'),
                'paid_date_time' => ($mainSpouse->paid_date_time == null) ? "N/A" : Carbon::parse($mainSpouse->paid_date_time)->format('M j, Y g:i A'),

                'registration_method' => $mainSpouse->registration_method,
                'transaction_remarks' => $mainSpouse->transaction_remarks,

                'registration_confirmation_sent_count' => $mainSpouse->registration_confirmation_sent_count,
                'registration_confirmation_sent_datetime' => ($mainSpouse->registration_confirmation_sent_datetime == null) ? "N/A" : Carbon::parse($mainSpouse->registration_confirmation_sent_datetime)->format('M j, Y g:i A'),

                'invoiceNumber' => $invoiceNumber,
                'allSpouses' => $allSpousesArray,

                'invoiceData' => $this->getInvoice($eventCategory, $eventId, $registrantId),
            ];
            // dd($finalData);
            return $finalData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantDetailVisitorsView($eventCategory, $eventId, $registrantId)
    {
        if (MainVisitor::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $finalData = array();

            $subVisitorsArray = array();
            $subVisitorsReplacementArray = array();
            $allVisitorsArray = array();
            $allVisitorsArrayTemp = array();

            $countFinalQuantity = 0;

            $eventYear = Event::where('id', $eventId)->value('year');
            $mainVisitor = MainVisitor::where('id', $registrantId)->where('event_id', $eventId)->first();

            $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainVisitor->pcode_used)->first();

            if ($promoCode != null) {
                if ($promoCode->badge_type == $mainVisitor->badge_type) {
                    $mainDiscount = $promoCode->discount;
                    $mainDiscountType = $promoCode->discount_type;
                } else {
                    $promoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $promoCode->id)->where('badge_type', $mainVisitor->badge_type)->first();

                    if ($promoCodeAdditionalBadgeType != null) {
                        $mainDiscount = $promoCode->discount;
                        $mainDiscountType = $promoCode->discount_type;
                    } else {
                        $mainDiscount = 0;
                        $mainDiscountType = null;
                    }
                }
            } else {
                $mainDiscount = 0;
                $mainDiscountType = null;
            }

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $tempYear = Carbon::parse($mainVisitor->registered_date_time)->format('y');
            $transactionId = VisitorTransaction::where('visitor_id', $mainVisitor->id)->where('visitor_type', "main")->value('id');
            $lastDigit = 1000 + intval($transactionId);
            $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;


            if ($mainVisitor->visitor_replaced_by_id == null && (!$mainVisitor->visitor_refunded)) {
                $countFinalQuantity++;
            }

            $subVisitors = AdditionalVisitor::where('main_visitor_id', $registrantId)->get();
            if (!$subVisitors->isEmpty()) {
                foreach ($subVisitors as $subVisitor) {
                    if ($subVisitor->visitor_replaced_by_id == null && (!$subVisitor->visitor_refunded)) {
                        $countFinalQuantity++;
                    }

                    $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subVisitor->pcode_used)->first();

                    if ($subPromoCode != null) {
                        if ($subPromoCode->badge_type == $subVisitor->badge_type) {
                            $subDiscount = $subPromoCode->discount;
                            $subDiscountType = $subPromoCode->discount_type;
                        } else {
                            $subPromoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $subPromoCode->id)->where('badge_type', $subVisitor->badge_type)->first();

                            if ($subPromoCodeAdditionalBadgeType != null) {
                                $subDiscount = $subPromoCode->discount;
                                $subDiscountType = $subPromoCode->discount_type;
                            } else {
                                $subDiscount = 0;
                                $subDiscountType = null;
                            }
                        }
                    } else {
                        $subDiscount = 0;
                        $subDiscountType = null;
                    }


                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($eventCategory == $eventCategoryC) {
                            $eventCode = $code;
                        }
                    }

                    if ($subVisitor->visitor_replaced_from_id != null) {
                        array_push($subVisitorsReplacementArray, [
                            'subVisitorId' => $subVisitor->id,
                            'name' => $subVisitor->salutation . " " . $subVisitor->first_name . " " . $subVisitor->middle_name . " " . $subVisitor->last_name,
                            'salutation' => $subVisitor->salutation,
                            'first_name' => $subVisitor->first_name,
                            'middle_name' => $subVisitor->middle_name,
                            'last_name' => $subVisitor->last_name,
                            'email_address' => $subVisitor->email_address,
                            'mobile_number' => $subVisitor->mobile_number,
                            'nationality' => $subVisitor->nationality,
                            'job_title' => $subVisitor->job_title,
                            'badge_type' => $subVisitor->badge_type,
                            'pcode_used' => $subVisitor->pcode_used,
                            'discount' => $subDiscount,
                            'discount_type' => $subDiscountType,

                            'visitor_cancelled' => $subVisitor->visitor_cancelled,
                            'visitor_replaced' => $subVisitor->visitor_replaced,
                            'visitor_refunded' => $subVisitor->visitor_refunded,

                            'visitor_replaced_type' => $subVisitor->visitor_replaced_type,
                            'visitor_original_from_id' => $subVisitor->visitor_original_from_id,
                            'visitor_replaced_from_id' => $subVisitor->visitor_replaced_from_id,
                            'visitor_replaced_by_id' => $subVisitor->visitor_replaced_by_id,

                            'visitor_cancelled_datetime' => $subVisitor->visitor_cancelled_datetime,
                            'visitor_refunded_datetime' => $subVisitor->visitor_refunded_datetime,
                            'visitor_replaced_datetime' => $subVisitor->visitor_replaced_datetime,
                        ]);
                    } else {
                        array_push($subVisitorsArray, [
                            'subVisitorId' => $subVisitor->id,
                            'name' => $subVisitor->salutation . " " . $subVisitor->first_name . " " . $subVisitor->middle_name . " " . $subVisitor->last_name,
                            'salutation' => $subVisitor->salutation,
                            'first_name' => $subVisitor->first_name,
                            'middle_name' => $subVisitor->middle_name,
                            'last_name' => $subVisitor->last_name,
                            'email_address' => $subVisitor->email_address,
                            'mobile_number' => $subVisitor->mobile_number,
                            'nationality' => $subVisitor->nationality,
                            'job_title' => $subVisitor->job_title,
                            'badge_type' => $subVisitor->badge_type,
                            'pcode_used' => $subVisitor->pcode_used,
                            'discount' => $subDiscount,
                            'discount_type' => $subDiscountType,

                            'visitor_cancelled' => $subVisitor->visitor_cancelled,
                            'visitor_replaced' => $subVisitor->visitor_replaced,
                            'visitor_refunded' => $subVisitor->visitor_refunded,

                            'visitor_replaced_type' => $subVisitor->visitor_replaced_type,
                            'visitor_original_from_id' => $subVisitor->visitor_original_from_id,
                            'visitor_replaced_from_id' => $subVisitor->visitor_replaced_from_id,
                            'visitor_replaced_by_id' => $subVisitor->visitor_replaced_by_id,

                            'visitor_cancelled_datetime' => $subVisitor->visitor_cancelled_datetime,
                            'visitor_refunded_datetime' => $subVisitor->visitor_refunded_datetime,
                            'visitor_replaced_datetime' => $subVisitor->visitor_replaced_datetime,
                        ]);
                    }
                }
            }


            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

            array_push($allVisitorsArrayTemp, [
                'transactionId' => $finalTransactionId,
                'mainVisitorId' => $mainVisitor->id,
                'visitorId' => $mainVisitor->id,
                'visitorType' => "main",

                'name' => $mainVisitor->salutation . " " . $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                'salutation' => $mainVisitor->salutation,
                'first_name' => $mainVisitor->first_name,
                'middle_name' => $mainVisitor->middle_name,
                'last_name' => $mainVisitor->last_name,
                'email_address' => $mainVisitor->email_address,
                'mobile_number' => $mainVisitor->mobile_number,
                'nationality' => $mainVisitor->nationality,
                'job_title' => $mainVisitor->job_title,
                'badge_type' => $mainVisitor->badge_type,
                'pcode_used' => $mainVisitor->pcode_used,
                'discount' => $mainDiscount,
                'discount_type' => $mainDiscountType,

                'is_replacement' => false,
                'visitor_cancelled' => $mainVisitor->visitor_cancelled,
                'visitor_replaced' => $mainVisitor->visitor_replaced,
                'visitor_refunded' => $mainVisitor->visitor_refunded,

                'visitor_replaced_type' => "main",
                'visitor_original_from_id' => $mainVisitor->id,
                'visitor_replaced_from_id' => null,
                'visitor_replaced_by_id' => $mainVisitor->visitor_replaced_by_id,

                'visitor_cancelled_datetime' => ($mainVisitor->visitor_cancelled_datetime == null) ? "N/A" : Carbon::parse($mainVisitor->visitor_cancelled_datetime)->format('M j, Y g:i A'),
                'visitor_refunded_datetime' => ($mainVisitor->visitor_refunded_datetime == null) ? "N/A" : Carbon::parse($mainVisitor->visitor_refunded_datetime)->format('M j, Y g:i A'),
                'visitor_replaced_datetime' => ($mainVisitor->visitor_replaced_datetime == null) ? "N/A" : Carbon::parse($mainVisitor->visitor_replaced_datetime)->format('M j, Y g:i A'),
            ]);

            if ($mainVisitor->visitor_replaced_by_id != null) {
                foreach ($subVisitorsReplacementArray as $subVisitorReplacement) {
                    if ($mainVisitor->id == $subVisitorReplacement['visitor_original_from_id'] && $subVisitorReplacement['visitor_replaced_type'] == "main") {

                        $transactionId = VisitorTransaction::where('visitor_id', $subVisitorReplacement['subVisitorId'])->where('visitor_type', "sub")->value('id');
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                        array_push($allVisitorsArrayTemp, [
                            'transactionId' => $finalTransactionId,
                            'mainVisitorId' => $mainVisitor->id,
                            'visitorId' => $subVisitorReplacement['subVisitorId'],
                            'visitorType' => "sub",

                            'name' => $subVisitorReplacement['salutation'] . " " . $subVisitorReplacement['first_name'] . " " . $subVisitorReplacement['middle_name'] . " " . $subVisitorReplacement['last_name'],
                            'salutation' => $subVisitorReplacement['salutation'],
                            'first_name' => $subVisitorReplacement['first_name'],
                            'middle_name' => $subVisitorReplacement['middle_name'],
                            'last_name' => $subVisitorReplacement['last_name'],
                            'email_address' => $subVisitorReplacement['email_address'],
                            'mobile_number' => $subVisitorReplacement['mobile_number'],
                            'nationality' => $subVisitorReplacement['nationality'],
                            'job_title' => $subVisitorReplacement['job_title'],
                            'badge_type' => $subVisitorReplacement['badge_type'],
                            'pcode_used' => $subVisitorReplacement['pcode_used'],
                            'discount' => $subVisitorReplacement['discount'],
                            'discount_type' => $subVisitorReplacement['discount_type'],

                            'is_replacement' => true,
                            'visitor_cancelled' => $subVisitorReplacement['visitor_cancelled'],
                            'visitor_replaced' => $subVisitorReplacement['visitor_replaced'],
                            'visitor_refunded' => $subVisitorReplacement['visitor_refunded'],

                            'visitor_replaced_type' => $subVisitorReplacement['visitor_replaced_type'],
                            'visitor_original_from_id' => $subVisitorReplacement['visitor_original_from_id'],
                            'visitor_replaced_from_id' => $subVisitorReplacement['visitor_replaced_from_id'],
                            'visitor_replaced_by_id' => $subVisitorReplacement['visitor_replaced_by_id'],

                            'visitor_cancelled_datetime' => ($subVisitorReplacement['visitor_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_cancelled_datetime'])->format('M j, Y g:i A'),
                            'visitor_refunded_datetime' => ($subVisitorReplacement['visitor_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_refunded_datetime'])->format('M j, Y g:i A'),
                            'visitor_replaced_datetime' => ($subVisitorReplacement['visitor_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_replaced_datetime'])->format('M j, Y g:i A'),
                        ]);
                    }
                }
            }

            array_push($allVisitorsArray, $allVisitorsArrayTemp);

            $allVisitorsArrayTemp = array();

            foreach ($subVisitorsArray as $subVisitor) {
                $allVisitorsArrayTemp = array();

                $transactionId = VisitorTransaction::where('visitor_id', $subVisitor['subVisitorId'])->where('visitor_type', "sub")->value('id');
                $lastDigit = 1000 + intval($transactionId);
                $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                array_push($allVisitorsArrayTemp, [
                    'transactionId' => $finalTransactionId,
                    'mainVisitorId' => $mainVisitor->id,
                    'visitorId' => $subVisitor['subVisitorId'],
                    'visitorType' => "sub",

                    'name' => $subVisitor['salutation'] . " " . $subVisitor['first_name'] . " " . $subVisitor['middle_name'] . " " . $subVisitor['last_name'],
                    'salutation' => $subVisitor['salutation'],
                    'first_name' => $subVisitor['first_name'],
                    'middle_name' => $subVisitor['middle_name'],
                    'last_name' => $subVisitor['last_name'],
                    'email_address' => $subVisitor['email_address'],
                    'mobile_number' => $subVisitor['mobile_number'],
                    'nationality' => $subVisitor['nationality'],
                    'job_title' => $subVisitor['job_title'],
                    'badge_type' => $subVisitor['badge_type'],
                    'pcode_used' => $subVisitor['pcode_used'],
                    'discount' => $subVisitor['discount'],
                    'discount_type' => $subVisitor['discount_type'],

                    'is_replacement' => false,
                    'visitor_cancelled' => $subVisitor['visitor_cancelled'],
                    'visitor_replaced' => $subVisitor['visitor_replaced'],
                    'visitor_refunded' => $subVisitor['visitor_refunded'],

                    'visitor_replaced_type' => "sub",
                    'visitor_original_from_id' => $subVisitor['subVisitorId'],
                    'visitor_replaced_from_id' => null,
                    'visitor_replaced_by_id' => $subVisitor['visitor_replaced_by_id'],

                    'visitor_cancelled_datetime' => ($subVisitor['visitor_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subVisitor['visitor_cancelled_datetime'])->format('M j, Y g:i A'),
                    'visitor_refunded_datetime' => ($subVisitor['visitor_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subVisitor['visitor_refunded_datetime'])->format('M j, Y g:i A'),
                    'visitor_replaced_datetime' => ($subVisitor['visitor_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subVisitor['visitor_replaced_datetime'])->format('M j, Y g:i A'),
                ]);

                if ($subVisitor['visitor_replaced_by_id'] != null) {
                    foreach ($subVisitorsReplacementArray as $subVisitorReplacement) {
                        if ($subVisitor['subVisitorId'] == $subVisitorReplacement['visitor_original_from_id'] && $subVisitorReplacement['visitor_replaced_type'] == "sub") {

                            $transactionId = VisitorTransaction::where('visitor_id', $subVisitorReplacement['subVisitorId'])->where('visitor_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                            array_push($allVisitorsArrayTemp, [
                                'transactionId' => $finalTransactionId,
                                'mainVisitorId' => $mainVisitor->id,
                                'visitorId' => $subVisitorReplacement['subVisitorId'],
                                'visitorType' => "sub",

                                'name' => $subVisitorReplacement['salutation'] . " " . $subVisitorReplacement['first_name'] . " " . $subVisitorReplacement['middle_name'] . " " . $subVisitorReplacement['last_name'],
                                'salutation' => $subVisitorReplacement['salutation'],
                                'first_name' => $subVisitorReplacement['first_name'],
                                'middle_name' => $subVisitorReplacement['middle_name'],
                                'last_name' => $subVisitorReplacement['last_name'],
                                'email_address' => $subVisitorReplacement['email_address'],
                                'mobile_number' => $subVisitorReplacement['mobile_number'],
                                'nationality' => $subVisitorReplacement['nationality'],
                                'job_title' => $subVisitorReplacement['job_title'],
                                'badge_type' => $subVisitorReplacement['badge_type'],
                                'pcode_used' => $subVisitorReplacement['pcode_used'],
                                'discount' => $subVisitorReplacement['discount'],
                                'discount_type' => $subVisitorReplacement['discount_type'],

                                'is_replacement' => true,
                                'visitor_cancelled' => $subVisitorReplacement['visitor_cancelled'],
                                'visitor_replaced' => $subVisitorReplacement['visitor_replaced'],
                                'visitor_refunded' => $subVisitorReplacement['visitor_refunded'],

                                'visitor_replaced_type' => "sub",
                                'visitor_original_from_id' => $subVisitor['subVisitorId'],
                                'visitor_replaced_from_id' => $subVisitorReplacement['visitor_replaced_from_id'],
                                'visitor_replaced_by_id' => $subVisitorReplacement['visitor_replaced_by_id'],

                                'visitor_cancelled_datetime' => ($subVisitorReplacement['visitor_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_cancelled_datetime'])->format('M j, Y g:i A'),
                                'visitor_refunded_datetime' => ($subVisitorReplacement['visitor_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_refunded_datetime'])->format('M j, Y g:i A'),
                                'visitor_replaced_datetime' => ($subVisitorReplacement['visitor_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subVisitorReplacement['visitor_replaced_datetime'])->format('M j, Y g:i A'),
                            ]);
                        }
                    }
                }
                array_push($allVisitorsArray, $allVisitorsArrayTemp);
            }

            $finalData = [
                'mainVisitorId' => $mainVisitor->id,
                'pass_type' => $mainVisitor->pass_type,
                'rate_type' => $mainVisitor->rate_type,
                'rate_type_string' => $mainVisitor->rate_type_string,
                'company_name' => $mainVisitor->company_name,
                'alternative_company_name' => $mainVisitor->alternative_company_name,
                'company_sector' => $mainVisitor->company_sector,
                'company_address' => $mainVisitor->company_address,
                'company_country' => $mainVisitor->company_country,
                'company_city' => $mainVisitor->company_city,
                'company_telephone_number' => $mainVisitor->company_telephone_number,
                'company_mobile_number' => $mainVisitor->company_mobile_number,
                'assistant_email_address' => $mainVisitor->assistant_email_address,
                'heard_where' => $mainVisitor->heard_where,
                'quantity' => $mainVisitor->quantity,
                'finalQuantity' => $countFinalQuantity,

                'mode_of_payment' => $mainVisitor->mode_of_payment,
                'registration_status' => "$mainVisitor->registration_status",
                'payment_status' => $mainVisitor->payment_status,
                'registered_date_time' => Carbon::parse($mainVisitor->registered_date_time)->format('M j, Y g:i A'),
                'paid_date_time' => ($mainVisitor->paid_date_time == null) ? "N/A" : Carbon::parse($mainVisitor->paid_date_time)->format('M j, Y g:i A'),

                'registration_method' => $mainVisitor->registration_method,
                'transaction_remarks' => $mainVisitor->transaction_remarks,

                'registration_confirmation_sent_count' => $mainVisitor->registration_confirmation_sent_count,
                'registration_confirmation_sent_datetime' => ($mainVisitor->registration_confirmation_sent_datetime == null) ? "N/A" : Carbon::parse($mainVisitor->registration_confirmation_sent_datetime)->format('M j, Y g:i A'),

                'invoiceNumber' => $invoiceNumber,
                'allVisitors' => $allVisitorsArray,

                'invoiceData' => $this->getInvoice($eventCategory, $eventId, $registrantId),
            ];
            return $finalData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantDetailRCCAwardsView($eventCategory, $eventId, $registrantId)
    {
        if (RccAwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $finalData = array();

            $subParticipantReplacementArray = array();
            $allParticipantsArray = array();
            $allParticipantsArrayTemp = array();

            $countFinalQuantity = 0;

            $eventYear = Event::where('id', $eventId)->value('year');
            $mainParticipant = RccAwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->first();

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
            $transactionId = RccAwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');
            $lastDigit = 1000 + intval($transactionId);
            $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;

            if ($mainParticipant->participant_replaced_by_id == null && (!$mainParticipant->participant_refunded)) {
                $countFinalQuantity++;
            }

            $subParticipants = RccAwardsAdditionalParticipant::where('main_participant_id', $registrantId)->get();
            if (!$subParticipants->isEmpty()) {
                foreach ($subParticipants as $subParticipant) {
                    if ($subParticipant->participant_replaced_by_id == null && (!$subParticipant->participant_refunded)) {
                        $countFinalQuantity++;
                    }

                    if ($subParticipant->participant_replaced_from_id != null) {
                        array_push($subParticipantReplacementArray, [
                            'subParticipantId' => $subParticipant->id,
                            'name' => $subParticipant->salutation . " " . $subParticipant->first_name . " " . $subParticipant->middle_name . " " . $subParticipant->last_name,
                            'salutation' => $subParticipant->salutation,
                            'first_name' => $subParticipant->first_name,
                            'middle_name' => $subParticipant->middle_name,
                            'last_name' => $subParticipant->last_name,
                            'email_address' => $subParticipant->email_address,
                            'mobile_number' => $subParticipant->mobile_number,
                            'address' => $subParticipant->address,
                            'country' => $subParticipant->country,
                            'city' => $subParticipant->city,
                            'job_title' => $subParticipant->job_title,

                            'participant_cancelled' => $subParticipant->participant_cancelled,
                            'participant_replaced' => $subParticipant->participant_replaced,
                            'participant_refunded' => $subParticipant->participant_refunded,

                            'participant_replaced_type' => $subParticipant->participant_replaced_type,
                            'participant_original_from_id' => $subParticipant->participant_original_from_id,
                            'participant_replaced_from_id' => $subParticipant->participant_replaced_from_id,
                            'participant_replaced_by_id' => $subParticipant->participant_replaced_by_id,

                            'participant_cancelled_datetime' => $subParticipant->participant_cancelled_datetime,
                            'participant_refunded_datetime' => $subParticipant->participant_refunded_datetime,
                            'participant_replaced_datetime' => $subParticipant->participant_replaced_datetime,
                        ]);
                    }
                }
            }


            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

            array_push($allParticipantsArrayTemp, [
                'transactionId' => $finalTransactionId,
                'mainParticipantId' => $mainParticipant->id,
                'participantId' => $mainParticipant->id,
                'participantType' => "main",

                'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                'salutation' => $mainParticipant->salutation,
                'first_name' => $mainParticipant->first_name,
                'middle_name' => $mainParticipant->middle_name,
                'last_name' => $mainParticipant->last_name,
                'email_address' => $mainParticipant->email_address,
                'mobile_number' => $mainParticipant->mobile_number,
                'address' => $mainParticipant->address,
                'country' => $mainParticipant->country,
                'city' => $mainParticipant->city,
                'job_title' => $mainParticipant->job_title,

                'is_replacement' => false,
                'participant_cancelled' => $mainParticipant->participant_cancelled,
                'participant_replaced' => $mainParticipant->participant_replaced,
                'participant_refunded' => $mainParticipant->participant_refunded,

                'participant_replaced_type' => "main",
                'participant_original_from_id' => $mainParticipant->id,
                'participant_replaced_from_id' => null,
                'participant_replaced_by_id' => $mainParticipant->participant_replaced_by_id,

                'participant_cancelled_datetime' => ($mainParticipant->participant_cancelled_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_cancelled_datetime)->format('M j, Y g:i A'),
                'participant_refunded_datetime' => ($mainParticipant->participant_refunded_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_refunded_datetime)->format('M j, Y g:i A'),
                'participant_replaced_datetime' => ($mainParticipant->participant_replaced_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_replaced_datetime)->format('M j, Y g:i A'),
            ]);

            if ($mainParticipant->participant_replaced_by_id != null) {
                foreach ($subParticipantReplacementArray as $subParticipantReplacement) {
                    if ($mainParticipant->id == $subParticipantReplacement['participant_original_from_id'] && $subParticipantReplacement['participant_replaced_type'] == "main") {

                        $transactionId = RccAwardsParticipantTransaction::where('participant_id', $subParticipantReplacement['subParticipantId'])->where('participant_type', "sub")->value('id');
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                        array_push($allParticipantsArrayTemp, [
                            'transactionId' => $finalTransactionId,
                            'mainParticipantId' => $mainParticipant->id,
                            'participantId' => $subParticipantReplacement['subParticipantId'],
                            'participantType' => "sub",

                            'name' => $subParticipantReplacement['salutation'] . " " . $subParticipantReplacement['first_name'] . " " . $subParticipantReplacement['middle_name'] . " " . $subParticipantReplacement['last_name'],
                            'salutation' => $subParticipantReplacement['salutation'],
                            'first_name' => $subParticipantReplacement['first_name'],
                            'middle_name' => $subParticipantReplacement['middle_name'],
                            'last_name' => $subParticipantReplacement['last_name'],
                            'email_address' => $subParticipantReplacement['email_address'],
                            'mobile_number' => $subParticipantReplacement['mobile_number'],
                            'address' => $subParticipantReplacement['address'],
                            'country' => $subParticipantReplacement['country'],
                            'city' => $subParticipantReplacement['city'],
                            'job_title' => $subParticipantReplacement['job_title'],

                            'is_replacement' => true,
                            'participant_cancelled' => $subParticipantReplacement['participant_cancelled'],
                            'participant_replaced' => $subParticipantReplacement['participant_replaced'],
                            'participant_refunded' => $subParticipantReplacement['participant_refunded'],

                            'participant_replaced_type' => $subParticipantReplacement['participant_replaced_type'],
                            'participant_original_from_id' => $subParticipantReplacement['participant_original_from_id'],
                            'participant_replaced_from_id' => $subParticipantReplacement['participant_replaced_from_id'],
                            'participant_replaced_by_id' => $subParticipantReplacement['participant_replaced_by_id'],

                            'participant_cancelled_datetime' => ($subParticipantReplacement['participant_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_cancelled_datetime'])->format('M j, Y g:i A'),
                            'participant_refunded_datetime' => ($subParticipantReplacement['participant_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_refunded_datetime'])->format('M j, Y g:i A'),
                            'participant_replaced_datetime' => ($subParticipantReplacement['participant_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_replaced_datetime'])->format('M j, Y g:i A'),
                        ]);
                    }
                }
            }

            array_push($allParticipantsArray, $allParticipantsArrayTemp);

            $entryFormId = RccAwardsDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('id');

            $entryFormFileName = RccAwardsDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('document_file_name');
            $getSupportingDocumentFiles = RccAwardsDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'supportingDocument')->get();

            $supportingDocumentsDownloadId = [];
            $supportingDocumentsDownloadFileName = [];

            if ($getSupportingDocumentFiles->isNotEmpty()) {
                foreach ($getSupportingDocumentFiles as $supportingDocument) {
                    $supportingDocumentsDownloadId[] = $supportingDocument->id;
                    $supportingDocumentsDownloadFileName[] = $supportingDocument->document_file_name;
                }
            }

            $finalData = [
                'mainParticipantId' => $mainParticipant->id,

                'pass_type' => $mainParticipant->pass_type,
                'rate_type' => $mainParticipant->rate_type,
                'rate_type_string' => $mainParticipant->rate_type_string,

                'category' => $mainParticipant->category,
                'sub_category' => $mainParticipant->sub_category,
                'company_name' => $mainParticipant->company_name,

                'entryFormId' => $entryFormId,
                'entryFormFileName' => $entryFormFileName,
                'supportingDocumentsDownloadId' => $supportingDocumentsDownloadId,
                'supportingDocumentsDownloadFileName' => $supportingDocumentsDownloadFileName,

                'heard_where' => $mainParticipant->heard_where,

                'quantity' => $mainParticipant->quantity,
                'finalQuantity' => $countFinalQuantity,

                'mode_of_payment' => $mainParticipant->mode_of_payment,
                'registration_status' => $mainParticipant->registration_status,
                'payment_status' => $mainParticipant->payment_status,
                'registered_date_time' => Carbon::parse($mainParticipant->registered_date_time)->format('M j, Y g:i A'),
                'paid_date_time' => ($mainParticipant->paid_date_time == null) ? "N/A" : Carbon::parse($mainParticipant->paid_date_time)->format('M j, Y g:i A'),

                'registration_method' => $mainParticipant->registration_method,
                'transaction_remarks' => $mainParticipant->transaction_remarks,

                'registration_confirmation_sent_count' => $mainParticipant->registration_confirmation_sent_count,
                'registration_confirmation_sent_datetime' => ($mainParticipant->registration_confirmation_sent_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->registration_confirmation_sent_datetime)->format('M j, Y g:i A'),

                'invoiceNumber' => $invoiceNumber,
                'allParticipants' => $allParticipantsArray,

                'invoiceData' => $this->getInvoice($eventCategory, $eventId, $registrantId),
            ];
            // dd($finalData);
            return $finalData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }


    public function registrantDetailAwardsView($eventCategory, $eventId, $registrantId)
    {
        if (AwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $finalData = array();

            $subParticipantReplacementArray = array();
            $allParticipantsArray = array();
            $allParticipantsArrayTemp = array();

            $countFinalQuantity = 0;

            $eventYear = Event::where('id', $eventId)->value('year');
            $mainParticipant = AwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->first();

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
            $transactionId = AwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');
            $lastDigit = 1000 + intval($transactionId);
            $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;

            if ($mainParticipant->participant_replaced_by_id == null && (!$mainParticipant->participant_refunded)) {
                $countFinalQuantity++;
            }

            $subParticipants = AwardsAdditionalParticipant::where('main_participant_id', $registrantId)->get();
            if (!$subParticipants->isEmpty()) {
                foreach ($subParticipants as $subParticipant) {
                    if ($subParticipant->participant_replaced_by_id == null && (!$subParticipant->participant_refunded)) {
                        $countFinalQuantity++;
                    }

                    if ($subParticipant->participant_replaced_from_id != null) {
                        array_push($subParticipantReplacementArray, [
                            'subParticipantId' => $subParticipant->id,
                            'name' => $subParticipant->salutation . " " . $subParticipant->first_name . " " . $subParticipant->middle_name . " " . $subParticipant->last_name,
                            'salutation' => $subParticipant->salutation,
                            'first_name' => $subParticipant->first_name,
                            'middle_name' => $subParticipant->middle_name,
                            'last_name' => $subParticipant->last_name,
                            'email_address' => $subParticipant->email_address,
                            'mobile_number' => $subParticipant->mobile_number,
                            'address' => $subParticipant->address,
                            'country' => $subParticipant->country,
                            'city' => $subParticipant->city,
                            'job_title' => $subParticipant->job_title,
                            'nationality' => $subParticipant->nationality,

                            'participant_cancelled' => $subParticipant->participant_cancelled,
                            'participant_replaced' => $subParticipant->participant_replaced,
                            'participant_refunded' => $subParticipant->participant_refunded,

                            'participant_replaced_type' => $subParticipant->participant_replaced_type,
                            'participant_original_from_id' => $subParticipant->participant_original_from_id,
                            'participant_replaced_from_id' => $subParticipant->participant_replaced_from_id,
                            'participant_replaced_by_id' => $subParticipant->participant_replaced_by_id,

                            'participant_cancelled_datetime' => $subParticipant->participant_cancelled_datetime,
                            'participant_refunded_datetime' => $subParticipant->participant_refunded_datetime,
                            'participant_replaced_datetime' => $subParticipant->participant_replaced_datetime,
                        ]);
                    }
                }
            }


            $finalTransactionId = $eventYear . $eventCode . $lastDigit;

            array_push($allParticipantsArrayTemp, [
                'transactionId' => $finalTransactionId,
                'mainParticipantId' => $mainParticipant->id,
                'participantId' => $mainParticipant->id,
                'participantType' => "main",

                'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                'salutation' => $mainParticipant->salutation,
                'first_name' => $mainParticipant->first_name,
                'middle_name' => $mainParticipant->middle_name,
                'last_name' => $mainParticipant->last_name,
                'email_address' => $mainParticipant->email_address,
                'mobile_number' => $mainParticipant->mobile_number,
                'address' => $mainParticipant->address,
                'country' => $mainParticipant->country,
                'city' => $mainParticipant->city,
                'job_title' => $mainParticipant->job_title,
                'nationality' => $mainParticipant->nationality,

                'is_replacement' => false,
                'participant_cancelled' => $mainParticipant->participant_cancelled,
                'participant_replaced' => $mainParticipant->participant_replaced,
                'participant_refunded' => $mainParticipant->participant_refunded,

                'participant_replaced_type' => "main",
                'participant_original_from_id' => $mainParticipant->id,
                'participant_replaced_from_id' => null,
                'participant_replaced_by_id' => $mainParticipant->participant_replaced_by_id,

                'participant_cancelled_datetime' => ($mainParticipant->participant_cancelled_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_cancelled_datetime)->format('M j, Y g:i A'),
                'participant_refunded_datetime' => ($mainParticipant->participant_refunded_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_refunded_datetime)->format('M j, Y g:i A'),
                'participant_replaced_datetime' => ($mainParticipant->participant_replaced_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->participant_replaced_datetime)->format('M j, Y g:i A'),
            ]);

            if ($mainParticipant->participant_replaced_by_id != null) {
                foreach ($subParticipantReplacementArray as $subParticipantReplacement) {
                    if ($mainParticipant->id == $subParticipantReplacement['participant_original_from_id'] && $subParticipantReplacement['participant_replaced_type'] == "main") {

                        $transactionId = AwardsParticipantTransaction::where('participant_id', $subParticipantReplacement['subParticipantId'])->where('participant_type', "sub")->value('id');
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                        array_push($allParticipantsArrayTemp, [
                            'transactionId' => $finalTransactionId,
                            'mainParticipantId' => $mainParticipant->id,
                            'participantId' => $subParticipantReplacement['subParticipantId'],
                            'participantType' => "sub",

                            'name' => $subParticipantReplacement['salutation'] . " " . $subParticipantReplacement['first_name'] . " " . $subParticipantReplacement['middle_name'] . " " . $subParticipantReplacement['last_name'],
                            'salutation' => $subParticipantReplacement['salutation'],
                            'first_name' => $subParticipantReplacement['first_name'],
                            'middle_name' => $subParticipantReplacement['middle_name'],
                            'last_name' => $subParticipantReplacement['last_name'],
                            'email_address' => $subParticipantReplacement['email_address'],
                            'mobile_number' => $subParticipantReplacement['mobile_number'],
                            'address' => $subParticipantReplacement['address'],
                            'country' => $subParticipantReplacement['country'],
                            'city' => $subParticipantReplacement['city'],
                            'job_title' => $subParticipantReplacement['job_title'],
                            'nationality' => $subParticipantReplacement['nationality'],

                            'is_replacement' => true,
                            'participant_cancelled' => $subParticipantReplacement['participant_cancelled'],
                            'participant_replaced' => $subParticipantReplacement['participant_replaced'],
                            'participant_refunded' => $subParticipantReplacement['participant_refunded'],

                            'participant_replaced_type' => $subParticipantReplacement['participant_replaced_type'],
                            'participant_original_from_id' => $subParticipantReplacement['participant_original_from_id'],
                            'participant_replaced_from_id' => $subParticipantReplacement['participant_replaced_from_id'],
                            'participant_replaced_by_id' => $subParticipantReplacement['participant_replaced_by_id'],

                            'participant_cancelled_datetime' => ($subParticipantReplacement['participant_cancelled_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_cancelled_datetime'])->format('M j, Y g:i A'),
                            'participant_refunded_datetime' => ($subParticipantReplacement['participant_refunded_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_refunded_datetime'])->format('M j, Y g:i A'),
                            'participant_replaced_datetime' => ($subParticipantReplacement['participant_replaced_datetime'] == null) ? "N/A" : Carbon::parse($subParticipantReplacement['participant_replaced_datetime'])->format('M j, Y g:i A'),
                        ]);
                    }
                }
            }

            array_push($allParticipantsArray, $allParticipantsArrayTemp);

            $entryFormId = AwardsParticipantDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('id');

            $entryFormFileName = AwardsParticipantDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('document_file_name');
            $getSupportingDocumentFiles = AwardsParticipantDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'supportingDocument')->get();

            $supportingDocumentsDownloadId = [];
            $supportingDocumentsDownloadFileName = [];

            if ($getSupportingDocumentFiles->isNotEmpty()) {
                foreach ($getSupportingDocumentFiles as $supportingDocument) {
                    $supportingDocumentsDownloadId[] = $supportingDocument->id;
                    $supportingDocumentsDownloadFileName[] = $supportingDocument->document_file_name;
                }
            }

            $finalData = [
                'mainParticipantId' => $mainParticipant->id,

                'pass_type' => $mainParticipant->pass_type,
                'rate_type' => $mainParticipant->rate_type,
                'rate_type_string' => $mainParticipant->rate_type_string,

                'category' => $mainParticipant->category,
                'company_name' => $mainParticipant->company_name,
                'alternative_company_name' => $mainParticipant->alternative_company_name,

                'entryFormId' => $entryFormId,
                'entryFormFileName' => $entryFormFileName,
                'supportingDocumentsDownloadId' => $supportingDocumentsDownloadId,
                'supportingDocumentsDownloadFileName' => $supportingDocumentsDownloadFileName,

                'heard_where' => $mainParticipant->heard_where,

                'quantity' => $mainParticipant->quantity,
                'finalQuantity' => $countFinalQuantity,

                'mode_of_payment' => $mainParticipant->mode_of_payment,
                'registration_status' => $mainParticipant->registration_status,
                'payment_status' => $mainParticipant->payment_status,
                'registered_date_time' => Carbon::parse($mainParticipant->registered_date_time)->format('M j, Y g:i A'),
                'paid_date_time' => ($mainParticipant->paid_date_time == null) ? "N/A" : Carbon::parse($mainParticipant->paid_date_time)->format('M j, Y g:i A'),

                'registration_method' => $mainParticipant->registration_method,
                'transaction_remarks' => $mainParticipant->transaction_remarks,

                'registration_confirmation_sent_count' => $mainParticipant->registration_confirmation_sent_count,
                'registration_confirmation_sent_datetime' => ($mainParticipant->registration_confirmation_sent_datetime == null) ? "N/A" : Carbon::parse($mainParticipant->registration_confirmation_sent_datetime)->format('M j, Y g:i A'),

                'invoiceNumber' => $invoiceNumber,
                'allParticipants' => $allParticipantsArray,

                'invoiceData' => $this->getInvoice($eventCategory, $eventId, $registrantId),
            ];
            // dd($finalData);
            return $finalData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }





    // =========================================================
    //                       RENDER LOGICS
    // =========================================================

    public function numberToWords($number)
    {
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }

    public function generateAdminInvoice($eventCategory, $eventId, $registrantId)
    {
        $finalData = $this->getInvoice($eventCategory, $eventId, $registrantId);
        if ($finalData['finalQuantity'] > 0) {
            if ($eventCategory == "RCCA") {
                if ($finalData['paymentStatus'] == "unpaid") {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.rcca.unpaid', $finalData)
                        ->setOption('header-html', view('admin.events.transactions.invoices.header')->render())
                        ->setOption('footer-html', view('admin.events.transactions.invoices.footer')->render());
                } else {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.rcca.paid', $finalData)
                        ->setOption('header-html', view('admin.events.transactions.invoices.header')->render())
                        ->setOption('footer-html', view('admin.events.transactions.invoices.footer')->render());
                }
            } else if ($eventCategory == "SCEA") {
                if ($finalData['paymentStatus'] == "unpaid") {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.awards.unpaid', $finalData);
                } else {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.awards.paid', $finalData);
                }
            } else if ($eventCategory == "AFS") {
                if ($finalData['paymentStatus'] == "unpaid") {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.spouse.unpaid', $finalData);
                } else {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.spouse.paid', $finalData);
                }
            } else if ($eventCategory == "AFV") {
                if ($finalData['paymentStatus'] == "unpaid") {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.afv.unpaid', $finalData);
                } else {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.afv.paid', $finalData);
                }
            } else {
                if ($finalData['paymentStatus'] == "unpaid") {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.unpaid', $finalData);
                } else {
                    $pdf = Pdf::loadView('admin.events.transactions.invoices.paid', $finalData);
                }
            }
            return $pdf->stream('invoice.pdf');
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function generatePublicInvoice($eventCategory, $eventId, $registrantId)
    {
        $finalData = $this->getInvoice($eventCategory, $eventId, $registrantId);
        if ($finalData['finalQuantity'] > 0) {
            if ($finalData['registrationMethod'] != "imported") {
                if ($eventCategory == "RCCA") {
                    if ($finalData['paymentStatus'] == "unpaid") {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.rcca.unpaid', $finalData);
                    } else {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.rcca.paid', $finalData);
                    }
                } else if ($eventCategory == "SCEA") {
                    if ($finalData['paymentStatus'] == "unpaid") {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.awards.unpaid', $finalData);
                    } else {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.awards.paid', $finalData);
                    }
                } else if ($eventCategory == "AFS") {
                    if ($finalData['paymentStatus'] == "unpaid") {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.spouse.unpaid', $finalData);
                    } else {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.spouse.paid', $finalData);
                    }
                } else if ($eventCategory == "AFV") {
                    if ($finalData['paymentStatus'] == "unpaid") {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.afv.unpaid', $finalData);
                    } else {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.afv.paid', $finalData);
                    }
                } else {
                    if ($finalData['paymentStatus'] == "unpaid") {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.unpaid', $finalData);
                    } else {
                        $pdf = Pdf::loadView('admin.events.transactions.invoices.paid', $finalData);
                    }
                }
                return $pdf->stream('invoice.pdf');
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function getInvoice($eventCategory, $eventId, $registrantId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if ($eventCategory == "AFS") {
                $invoiceData = $this->getInvoiceSpouse($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "AFV") {
                $invoiceData = $this->getInvoiceVisitor($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "RCCA") {
                $invoiceData = $this->getInvoiceRccAwards($eventCategory, $eventId, $registrantId);
            } else if ($eventCategory == "SCEA") {
                $invoiceData = $this->getInvoiceAwards($eventCategory, $eventId, $registrantId);
            } else {
                $invoiceData = $this->getInvoiceEvents($eventCategory, $eventId, $registrantId);
            }
            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrantsExportData($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if ($eventCategory == "AFS") {
                $finalData = $this->spouseRegistrantsExportData($eventCategory, $eventId);
            } else if ($eventCategory == "AFV") {
                $finalData = $this->visitorRegistrantsExportData($eventCategory, $eventId);
            } else if ($eventCategory == "RCCA") {
                $finalData = $this->rccAwardsRegistrantsExportData($eventCategory, $eventId);
            } else if ($eventCategory == "SCEA") {
                $finalData = $this->awardsRegistrantsExportData($eventCategory, $eventId);
            } else {
                $finalData = $this->eventRegistrantsExportData($eventCategory, $eventId);
            }

            return response()->stream($finalData['callback'], 200, $finalData['headers']);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function capturePayment()
    {
        $registrationFormType = request()->query('registrationFormType');

        if ($registrationFormType == "events") {
            $mainDelegateId = request()->query('mainDelegateId');
            $sessionId = request()->query('sessionId');
            if (
                request()->input('response_gatewayRecommendation') == "PROCEED" &&
                request()->input('result') == "SUCCESS" &&
                request()->input('order_id') &&
                request()->input('transaction_id') &&
                request()->query('sessionId') &&
                request()->query('mainDelegateId') &&
                request()->query('registrationFormType')
            ) {
                $orderId = request()->input('order_id');
                $oldTransactionId = request()->input('transaction_id');
                $newTransactionId = substr(uniqid(), -8);

                $apiEndpoint = env('MERCHANT_API_URL');
                $merchantId = env('MERCHANT_ID');
                $authPass = env('MERCHANT_AUTH_PASSWORD');

                $client = new Client();
                $response = $client->request('PUT', $apiEndpoint . '/order/' . $orderId . '/transaction/' . $newTransactionId, [
                    'auth' => [
                        'merchant.' . $merchantId,
                        $authPass,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'apiOperation' => "PAY",
                        "authentication" => [
                            "transactionId" => $oldTransactionId,
                        ],
                        "order" => [
                            "reference" => $orderId,
                        ],
                        "session" => [
                            'id' => $sessionId,
                        ],
                        "transaction" => [
                            "reference" => $orderId,
                        ],
                    ]
                ]);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (
                    $data['response']['gatewayCode'] == "APPROVED" &&
                    $data['response']['gatewayRecommendation'] == "NO_ACTION" &&
                    $data['transaction']['authenticationStatus'] == "AUTHENTICATION_SUCCESSFUL" &&
                    $data['transaction']['type'] == "PAYMENT"
                ) {
                    MainDelegate::find($mainDelegateId)->fill([
                        'registration_status' => "confirmed",
                        'payment_status' => "paid",
                        'paid_date_time' => Carbon::now(),
                    ])->save();

                    $mainDelegate = MainDelegate::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainDelegate->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    $transactionId = Transaction::where('delegate_id', $mainDelegateId)->where('delegate_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($event->category == $eventCategoryC) {
                            $getEventcode = $code;
                        }
                    }

                    $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";
                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $mainDelegateVatPrice = $mainDelegate->unit_price * ($event->event_vat / 100);
                    $amountPaid = $mainDelegate->unit_price + $mainDelegateVatPrice;

                    if ($mainDelegate->pcode_used != null) {
                        $promoCode = PromoCode::where('event_id', $mainDelegate->event_id)->where('promo_code', $mainDelegate->pcode_used)->first();

                        if ($promoCode != null) {
                            if ($promoCode->discount_type == "percentage") {
                                $mainDelegateDiscountPrice = $mainDelegate->unit_price * ($promoCode->discount / 100);
                                $mainDelegateDiscountedPrice = $mainDelegate->unit_price - $mainDelegateDiscountPrice;
                                $mainDelegateVatPrice = $mainDelegateDiscountedPrice * ($event->event_vat / 100);
                                $amountPaid = $mainDelegateDiscountedPrice + $mainDelegateVatPrice;
                            } else if ($promoCode->discount_type == "price") {
                                $mainDelegateDiscountedPrice = $mainDelegate->unit_price - $promoCode->discount;
                                $mainDelegateVatPrice = $mainDelegateDiscountedPrice * ($event->event_vat / 100);
                                $amountPaid = $mainDelegateDiscountedPrice + $mainDelegateVatPrice;
                            } else {
                                $mainDelegateVatPrice = $promoCode->new_rate * ($event->event_vat / 100);
                                $amountPaid = $promoCode->new_rate + $mainDelegateVatPrice;
                            }
                        }
                    }

                    $combinedStringPrint = "gpca@reg" . ',' . $event->id . ',' . $event->category . ',' . $mainDelegate->id . ',' . 'main';
                    $finalCryptStringPrint = base64_encode($combinedStringPrint);
                    $qrCodeForPrint = 'ca' . $finalCryptStringPrint . 'gp';

                    $details1 = [
                        'name' => $mainDelegate->salutation . " " . $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'accessType' => $mainDelegate->access_type,
                        'jobTitle' => $mainDelegate->job_title,
                        'companyName' => $mainDelegate->company_name,
                        'badgeType' => $mainDelegate->badge_type,
                        'amountPaid' => $amountPaid,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "main" . "/" . $mainDelegateId,
                        'qrCodeForPrint' => $qrCodeForPrint,
                    ];

                    $details2 = [
                        'name' => $mainDelegate->salutation . " " . $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'invoiceAmount' => $mainDelegate->total_amount,
                        'amountPaid' => $mainDelegate->total_amount,
                        'balance' => 0,
                        'invoiceLink' => $invoiceLink,
                    ];

                    if ($event->category == "DAW") {
                        $ccEmailNotif = config('app.ccEmailNotif.daw');
                    } else {
                        $ccEmailNotif = config('app.ccEmailNotif.default');
                    }

                    try {
                        Mail::to($mainDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationPaid($details1));
                        Mail::to($mainDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationPaymentConfirmation($details2));
                        MainDelegate::find($mainDelegateId)->fill([
                            'registration_confirmation_sent_count' => 1,
                            'registration_confirmation_sent_datetime' => Carbon::now(),
                        ])->save();
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaymentConfirmation($details2));
                    }

                    if ($mainDelegate->assistant_email_address != null) {
                        $details1['amountPaid'] = $mainDelegate->total_amount;

                        try {
                            Mail::to($mainDelegate->assistant_email_address)->send(new RegistrationPaid($details1));
                            Mail::to($mainDelegate->assistant_email_address)->send(new RegistrationPaymentConfirmation($details2));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaymentConfirmation($details2));
                        }
                    }

                    $additionalDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegateId)->get();

                    if (!$additionalDelegates->isEmpty()) {
                        foreach ($additionalDelegates as $additionalDelegate) {
                            $transactionId = Transaction::where('delegate_id', $additionalDelegate->id)->where('delegate_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";

                            $subDelegateVatPrice = $mainDelegate->unit_price * ($event->event_vat / 100);
                            $amountPaidSub = $mainDelegate->unit_price + $subDelegateVatPrice;

                            if ($additionalDelegate->pcode_used != null) {
                                $promoCode = PromoCode::where('event_id', $mainDelegate->event_id)->where('promo_code', $additionalDelegate->pcode_used)->first();

                                if ($promoCode != null) {
                                    if ($promoCode->discount_type == "percentage") {
                                        $subDelegateDiscountPrice = $mainDelegate->unit_price * ($promoCode->discount / 100);
                                        $subDelegateDiscountedPrice = $mainDelegate->unit_price - $subDelegateDiscountPrice;
                                        $subDelegateVatPrice = $subDelegateDiscountedPrice * ($event->event_vat / 100);
                                        $amountPaidSub = $subDelegateDiscountedPrice + $subDelegateVatPrice;
                                    } else if ($promoCode->discount_type == "price") {
                                        $subDelegateDiscountedPrice = $mainDelegate->unit_price - $promoCode->discount;
                                        $subDelegateVatPrice = $subDelegateDiscountedPrice * ($event->event_vat / 100);
                                        $amountPaidSub = $subDelegateDiscountedPrice + $subDelegateVatPrice;
                                    } else {
                                        $subDelegateVatPrice = $promoCode->new_rate * ($event->event_vat / 100);
                                        $amountPaidSub = $promoCode->new_rate + $subDelegateVatPrice;
                                    }
                                }
                            }

                            $combinedStringPrintSub = "gpca@reg" . ',' . $event->id . ',' . $event->category . ',' . $additionalDelegate->id . ',' . 'sub';
                            $finalCryptStringPrintSub = base64_encode($combinedStringPrintSub);
                            $qrCodeForPrintSub = 'ca' . $finalCryptStringPrintSub . 'gp';

                            $details1 = [
                                'name' => $additionalDelegate->salutation . " " . $additionalDelegate->first_name . " " . $additionalDelegate->middle_name . " " . $additionalDelegate->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'eventCategory' => $event->category,
                                'eventYear' => $event->year,

                                'accessType' => $mainDelegate->access_type,
                                'jobTitle' => $additionalDelegate->job_title,
                                'companyName' => $mainDelegate->company_name,
                                'badgeType' => $additionalDelegate->badge_type,
                                'amountPaid' => $amountPaidSub,
                                'transactionId' => $tempTransactionId,
                                'invoiceLink' => $invoiceLink,
                                'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "sub" . "/" . $additionalDelegate->id,
                                'qrCodeForPrint' => $qrCodeForPrintSub,
                            ];
                            try {
                                Mail::to($additionalDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationPaid($details1));
                                AdditionalDelegate::find($additionalDelegate->id)->fill([
                                    'registration_confirmation_sent_count' => 1,
                                    'registration_confirmation_sent_datetime' => Carbon::now(),
                                ])->save();
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "success"]);
                } else {
                    // (This is the part where we will send them email notification failed and redirect them)
                    MainDelegate::find($mainDelegateId)->fill([
                        'registration_status' => "pending",
                        'payment_status' => "unpaid",
                    ])->save();

                    $mainDelegate = MainDelegate::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainDelegate->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    if ($event->category == "AF") {
                        $bankDetails = config('app.bankDetails.AF');
                    } else {
                        $bankDetails = config('app.bankDetails.DEFAULT');
                    }

                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details = [
                        'name' => $mainDelegate->salutation . " " . $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'bankDetails' => $bankDetails,
                        'invoiceLink' => $invoiceLink,
                        'eventYear' => $event->year,
                    ];


                    if ($event->category == "DAW") {
                        $ccEmailNotif = config('app.ccEmailNotif.daw');
                    } else {
                        $ccEmailNotif = config('app.ccEmailNotif.default');
                    }

                    try {
                        Mail::to($mainDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }

                    if ($mainDelegate->assistant_email_address != null) {
                        try {
                            Mail::to($mainDelegate->assistant_email_address)->send(new RegistrationCardDeclined($details));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                        }
                    }

                    $additionalDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegateId)->get();

                    if (!$additionalDelegates->isEmpty()) {
                        foreach ($additionalDelegates as $additionalDelegate) {
                            $details = [
                                'name' => $additionalDelegate->salutation . " " . $additionalDelegate->first_name . " " . $additionalDelegate->middle_name . " " . $additionalDelegate->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventCategory' => $event->category,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'bankDetails' => $bankDetails,
                                'invoiceLink' => $invoiceLink,
                                'eventYear' => $event->year,
                            ];
                            try {
                                Mail::to($additionalDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
                }
            } else {
                // (This is the part where we will send them email notification failed and redirect them)
                MainDelegate::find($mainDelegateId)->fill([
                    'registration_status' => "pending",
                    'payment_status' => "unpaid",
                ])->save();

                $mainDelegate = MainDelegate::where('id', $mainDelegateId)->first();
                $event = Event::where('id', $mainDelegate->event_id)->first();
                $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                if ($event->category == "AF") {
                    $bankDetails = config('app.bankDetails.AF');
                } else {
                    $bankDetails = config('app.bankDetails.DEFAULT');
                }

                $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                $details = [
                    'name' => $mainDelegate->salutation . " " . $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                    'eventLink' => $event->link,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDates' => $eventFormattedData,
                    'eventLocation' => $event->location,
                    'bankDetails' => $bankDetails,
                    'invoiceLink' => $invoiceLink,
                    'eventYear' => $event->year,
                ];

                if ($event->category == "DAW") {
                    $ccEmailNotif = config('app.ccEmailNotif.daw');
                } else {
                    $ccEmailNotif = config('app.ccEmailNotif.default');
                }

                try {
                    Mail::to($mainDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                }

                if ($mainDelegate->assistant_email_address != null) {
                    try {
                        Mail::to($mainDelegate->assistant_email_address)->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }
                }

                $additionalDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegateId)->get();

                if (!$additionalDelegates->isEmpty()) {
                    foreach ($additionalDelegates as $additionalDelegate) {
                        $details = [
                            'name' => $additionalDelegate->salutation . " " . $additionalDelegate->first_name . " " . $additionalDelegate->middle_name . " " . $additionalDelegate->last_name,
                            'eventLink' => $event->link,
                            'eventName' => $event->name,
                            'eventCategory' => $event->category,
                            'eventDates' => $eventFormattedData,
                            'eventLocation' => $event->location,
                            'bankDetails' => $bankDetails,
                            'invoiceLink' => $invoiceLink,
                            'eventYear' => $event->year,
                        ];
                        try {
                            Mail::to($additionalDelegate->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                        }
                    }
                }
                return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
            }
        } else if ($registrationFormType == "spouse") {
            $mainDelegateId = request()->query('mainDelegateId');
            $sessionId = request()->query('sessionId');
            if (
                request()->input('response_gatewayRecommendation') == "PROCEED" &&
                request()->input('result') == "SUCCESS" &&
                request()->input('order_id') &&
                request()->input('transaction_id') &&
                request()->query('sessionId') &&
                request()->query('mainDelegateId') &&
                request()->query('registrationFormType')
            ) {
                $orderId = request()->input('order_id');
                $oldTransactionId = request()->input('transaction_id');
                $newTransactionId = substr(uniqid(), -8);

                $apiEndpoint = env('MERCHANT_API_URL');
                $merchantId = env('MERCHANT_ID');
                $authPass = env('MERCHANT_AUTH_PASSWORD');

                $client = new Client();
                $response = $client->request('PUT', $apiEndpoint . '/order/' . $orderId . '/transaction/' . $newTransactionId, [
                    'auth' => [
                        'merchant.' . $merchantId,
                        $authPass,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'apiOperation' => "PAY",
                        "authentication" => [
                            "transactionId" => $oldTransactionId,
                        ],
                        "order" => [
                            "reference" => $orderId,
                        ],
                        "session" => [
                            'id' => $sessionId,
                        ],
                        "transaction" => [
                            "reference" => $orderId,
                        ],
                    ]
                ]);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (
                    $data['response']['gatewayCode'] == "APPROVED" &&
                    $data['response']['gatewayRecommendation'] == "NO_ACTION" &&
                    $data['transaction']['authenticationStatus'] == "AUTHENTICATION_SUCCESSFUL" &&
                    $data['transaction']['type'] == "PAYMENT"
                ) {
                    MainSpouse::find($mainDelegateId)->fill([
                        'registration_status' => "confirmed",
                        'payment_status' => "paid",
                        'paid_date_time' => Carbon::now(),

                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();

                    $mainSpouse = MainSpouse::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainSpouse->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    $transactionId = SpouseTransaction::where('spouse_id', $mainDelegateId)->where('spouse_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($event->category == $eventCategoryC) {
                            $getEventcode = $code;
                        }
                    }

                    $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";
                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details1 = [
                        'name' => $mainSpouse->salutation . " " . $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'nationality' => $mainSpouse->nationality,
                        'country' => $mainSpouse->country,
                        'city' => $mainSpouse->city,
                        'amountPaid' => $mainSpouse->unit_price,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "main" . "/" . $mainSpouse->id,
                    ];

                    $details2 = [
                        'name' => $mainSpouse->salutation . " " . $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'invoiceAmount' => $mainSpouse->total_amount,
                        'amountPaid' => $mainSpouse->total_amount,
                        'balance' => 0,
                        'invoiceLink' => $invoiceLink,
                    ];

                    try {
                        Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaid($details1));
                        Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaymentConfirmation($details2));
                    } catch (\Exception $e) {
                        Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaid($details1));
                        Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaymentConfirmation($details2));
                    }


                    $additionalSpouses = AdditionalSpouse::where('main_spouse_id', $mainDelegateId)->get();

                    if (!$additionalSpouses->isEmpty()) {
                        foreach ($additionalSpouses as $additionalSpouse) {
                            $transactionId = SpouseTransaction::where('spouse_id', $additionalSpouse->id)->where('spouse_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";

                            $details1 = [
                                'name' => $additionalSpouse->salutation . " " . $additionalSpouse->first_name . " " . $additionalSpouse->middle_name . " " . $additionalSpouse->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'eventCategory' => $event->category,
                                'eventYear' => $event->year,

                                'nationality' => $additionalSpouse->nationality,
                                'country' => $additionalSpouse->country,
                                'city' => $additionalSpouse->city,
                                'amountPaid' => $mainSpouse->unit_price,
                                'transactionId' => $tempTransactionId,
                                'invoiceLink' => $invoiceLink,
                                'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "sub" . "/" . $additionalSpouse->id,
                            ];

                            try {
                                Mail::to($additionalSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaid($details1));
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "success"]);
                } else {
                    // (This is the part where we will send them email notification failed and redirect them)
                    MainSpouse::find($mainDelegateId)->fill([
                        'registration_status' => "pending",
                        'payment_status' => "unpaid",
                    ])->save();

                    $mainSpouse = MainSpouse::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainSpouse->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    if ($event->category == "AF") {
                        $bankDetails = config('app.bankDetails.AF');
                    } else {
                        $bankDetails = config('app.bankDetails.DEFAULT');
                    }

                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details = [
                        'name' => $mainSpouse->salutation . " " . $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'bankDetails' => $bankDetails,
                        'invoiceLink' => $invoiceLink,
                        'eventYear' => $event->year,
                    ];

                    try {
                        Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }

                    $additionalSpouses = AdditionalSpouse::where('main_spouse_id', $mainDelegateId)->get();

                    if (!$additionalSpouses->isEmpty()) {
                        foreach ($additionalSpouses as $additionalSpouse) {
                            $details = [
                                'name' => $additionalSpouse->salutation . " " . $additionalSpouse->first_name . " " . $additionalSpouse->middle_name . " " . $additionalSpouse->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventCategory' => $event->category,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'bankDetails' => $bankDetails,
                                'invoiceLink' => $invoiceLink,
                                'eventYear' => $event->year,
                            ];

                            try {
                                Mail::to($additionalSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
                }
            } else {
                // (This is the part where we will send them email notification failed and redirect them)
                MainSpouse::find($mainDelegateId)->fill([
                    'registration_status' => "pending",
                    'payment_status' => "unpaid",
                ])->save();

                $mainSpouse = MainSpouse::where('id', $mainDelegateId)->first();
                $event = Event::where('id', $mainSpouse->event_id)->first();
                $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                if ($event->category == "AF") {
                    $bankDetails = config('app.bankDetails.AF');
                } else {
                    $bankDetails = config('app.bankDetails.DEFAULT');
                }

                $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                $details = [
                    'name' => $mainSpouse->salutation . " " . $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name,
                    'eventLink' => $event->link,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDates' => $eventFormattedData,
                    'eventLocation' => $event->location,
                    'bankDetails' => $bankDetails,
                    'invoiceLink' => $invoiceLink,
                    'eventYear' => $event->year,
                ];

                try {
                    Mail::to($mainSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                }

                $additionalSpouses = AdditionalSpouse::where('main_spouse_id', $mainDelegateId)->get();

                if (!$additionalSpouses->isEmpty()) {
                    foreach ($additionalSpouses as $additionalSpouse) {
                        $details = [
                            'name' => $additionalSpouse->salutation . " " . $additionalSpouse->first_name . " " . $additionalSpouse->middle_name . " " . $additionalSpouse->last_name,
                            'eventLink' => $event->link,
                            'eventName' => $event->name,
                            'eventCategory' => $event->category,
                            'eventDates' => $eventFormattedData,
                            'eventLocation' => $event->location,
                            'bankDetails' => $bankDetails,
                            'invoiceLink' => $invoiceLink,
                            'eventYear' => $event->year,
                        ];

                        try {
                            Mail::to($additionalSpouse->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                        }
                    }
                }
                return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
            }
        } else if ($registrationFormType == "visitor") {
            $mainDelegateId = request()->query('mainDelegateId');
            $sessionId = request()->query('sessionId');
            if (
                request()->input('response_gatewayRecommendation') == "PROCEED" &&
                request()->input('result') == "SUCCESS" &&
                request()->input('order_id') &&
                request()->input('transaction_id') &&
                request()->query('sessionId') &&
                request()->query('mainDelegateId') &&
                request()->query('registrationFormType')
            ) {
                $orderId = request()->input('order_id');
                $oldTransactionId = request()->input('transaction_id');
                $newTransactionId = substr(uniqid(), -8);

                $apiEndpoint = env('MERCHANT_API_URL');
                $merchantId = env('MERCHANT_ID');
                $authPass = env('MERCHANT_AUTH_PASSWORD');

                $client = new Client();
                $response = $client->request('PUT', $apiEndpoint . '/order/' . $orderId . '/transaction/' . $newTransactionId, [
                    'auth' => [
                        'merchant.' . $merchantId,
                        $authPass,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'apiOperation' => "PAY",
                        "authentication" => [
                            "transactionId" => $oldTransactionId,
                        ],
                        "order" => [
                            "reference" => $orderId,
                        ],
                        "session" => [
                            'id' => $sessionId,
                        ],
                        "transaction" => [
                            "reference" => $orderId,
                        ],
                    ]
                ]);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (
                    $data['response']['gatewayCode'] == "APPROVED" &&
                    $data['response']['gatewayRecommendation'] == "NO_ACTION" &&
                    $data['transaction']['authenticationStatus'] == "AUTHENTICATION_SUCCESSFUL" &&
                    $data['transaction']['type'] == "PAYMENT"
                ) {
                    MainVisitor::find($mainDelegateId)->fill([
                        'registration_status' => "confirmed",
                        'payment_status' => "paid",
                        'paid_date_time' => Carbon::now(),

                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();

                    $mainVisitor = MainVisitor::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainVisitor->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    $transactionId = VisitorTransaction::where('visitor_id', $mainDelegateId)->where('visitor_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($event->category == $eventCategoryC) {
                            $getEventcode = $code;
                        }
                    }

                    $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";
                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $amountPaid = $mainVisitor->unit_price;

                    if ($mainVisitor->pcode_used != null) {
                        $promoCode = PromoCode::where('event_id', $mainVisitor->event_id)->where('promo_code', $mainVisitor->pcode_used)->first();

                        if ($promoCode != null) {
                            if ($promoCode->discount_type == "percentage") {
                                $amountPaid = $mainVisitor->unit_price - ($mainVisitor->unit_price * ($promoCode->discount / 100));
                            } else if ($promoCode->discount_type == "price") {
                                $amountPaid = $mainVisitor->unit_price - $promoCode->discount;
                            } else {
                                $amountPaid = $promoCode->new_rate;
                            }
                        }
                    }

                    $details1 = [
                        'name' => $mainVisitor->salutation . " " . $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'jobTitle' => $mainVisitor->job_title,
                        'companyName' => $mainVisitor->company_name,
                        'amountPaid' => $amountPaid,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "main" . "/" . $mainVisitor->id,
                    ];

                    $details2 = [
                        'name' => $mainVisitor->salutation . " " . $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'invoiceAmount' => $mainVisitor->total_amount,
                        'amountPaid' => $mainVisitor->total_amount,
                        'balance' => 0,
                        'invoiceLink' => $invoiceLink,
                    ];

                    $ccEmailNotif = config('app.ccEmailNotif.default');

                    try {
                        Mail::to($mainVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationPaid($details1));
                        Mail::to($mainVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationPaymentConfirmation($details2));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaymentConfirmation($details2));
                    }

                    if ($mainVisitor->assistant_email_address != null) {
                        $details1['amountPaid'] = $mainVisitor->total_amount;

                        try {
                            Mail::to($mainVisitor->assistant_email_address)->send(new RegistrationPaid($details1));
                            Mail::to($mainVisitor->assistant_email_address)->send(new RegistrationPaymentConfirmation($details2));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaymentConfirmation($details2));
                        }
                    }

                    $additionalVisitors = AdditionalVisitor::where('main_visitor_id', $mainDelegateId)->get();

                    if (!$additionalVisitors->isEmpty()) {
                        foreach ($additionalVisitors as $additionalVisitor) {
                            $transactionId = VisitorTransaction::where('visitor_id', $additionalVisitor->id)->where('visitor_type', "sub")->value('id');
                            $lastDigit = 1000 + intval($transactionId);
                            $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";

                            $amountPaidSub = $mainVisitor->unit_price;

                            if ($additionalVisitor->pcode_used != null) {
                                $promoCode = PromoCode::where('event_id', $mainVisitor->event_id)->where('promo_code', $additionalVisitor->pcode_used)->first();

                                if ($promoCode != null) {
                                    if ($promoCode->discount_type == "percentage") {
                                        $amountPaidSub = $mainVisitor->unit_price - ($mainVisitor->unit_price * ($promoCode->discount / 100));
                                    } else if ($promoCode->discount_type == "price") {
                                        $amountPaidSub = $mainVisitor->unit_price - $promoCode->discount;
                                    } else {
                                        $amountPaidSub = $promoCode->new_rate;
                                    }
                                }
                            }

                            $details1 = [
                                'name' => $additionalVisitor->salutation . " " . $additionalVisitor->first_name . " " . $additionalVisitor->middle_name . " " . $additionalVisitor->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'eventCategory' => $event->category,
                                'eventYear' => $event->year,

                                'jobTitle' => $additionalVisitor->job_title,
                                'companyName' => $mainVisitor->company_name,
                                'amountPaid' => $amountPaidSub,
                                'transactionId' => $tempTransactionId,
                                'invoiceLink' => $invoiceLink,
                                'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "sub" . "/" . $additionalVisitor->id,
                            ];

                            try {
                                Mail::to($additionalVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationPaid($details1));
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "success"]);
                } else {
                    // (This is the part where we will send them email notification failed and redirect them)
                    MainVisitor::find($mainDelegateId)->fill([
                        'registration_status' => "pending",
                        'payment_status' => "unpaid",
                    ])->save();

                    $mainVisitor = MainVisitor::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainVisitor->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                    $bankDetails = config('app.bankDetails.AF');

                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details = [
                        'name' => $mainVisitor->salutation . " " . $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'bankDetails' => $bankDetails,
                        'invoiceLink' => $invoiceLink,
                        'eventYear' => $event->year,
                    ];

                    $ccEmailNotif = config('app.ccEmailNotif.default');

                    try {
                        Mail::to($mainVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }

                    if ($mainVisitor->assistant_email_address != null) {
                        try {
                            Mail::to($mainVisitor->assistant_email_address)->send(new RegistrationCardDeclined($details));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                        }
                    }

                    $additionalVisitors = AdditionalVisitor::where('main_visitor_id', $mainDelegateId)->get();

                    if (!$additionalVisitors->isEmpty()) {
                        foreach ($additionalVisitors as $additionalVisitor) {
                            $details = [
                                'name' => $additionalVisitor->salutation . " " . $additionalVisitor->first_name . " " . $additionalVisitor->middle_name . " " . $additionalVisitor->last_name,
                                'eventLink' => $event->link,
                                'eventName' => $event->name,
                                'eventCategory' => $event->category,
                                'eventDates' => $eventFormattedData,
                                'eventLocation' => $event->location,
                                'bankDetails' => $bankDetails,
                                'invoiceLink' => $invoiceLink,
                                'eventYear' => $event->year,
                            ];

                            try {
                                Mail::to($additionalVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                            } catch (\Exception $e) {
                                Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                            }
                        }
                    }
                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
                }
            } else {
                // (This is the part where we will send them email notification failed and redirect them)
                MainVisitor::find($mainDelegateId)->fill([
                    'registration_status' => "pending",
                    'payment_status' => "unpaid",
                ])->save();

                $mainVisitor = MainVisitor::where('id', $mainDelegateId)->first();
                $event = Event::where('id', $mainVisitor->event_id)->first();
                $eventFormattedData = Carbon::parse($event->event_start_date)->format('d') . '-' . Carbon::parse($event->event_end_date)->format('d M Y');

                $bankDetails = config('app.bankDetails.AF');

                $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                $details = [
                    'name' => $mainVisitor->salutation . " " . $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                    'eventLink' => $event->link,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDates' => $eventFormattedData,
                    'eventLocation' => $event->location,
                    'bankDetails' => $bankDetails,
                    'invoiceLink' => $invoiceLink,
                    'eventYear' => $event->year,
                ];

                $ccEmailNotif = config('app.ccEmailNotif.default');

                try {
                    Mail::to($mainVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                }

                if ($mainVisitor->assistant_email_address != null) {
                    try {
                        Mail::to($mainVisitor->assistant_email_address)->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }
                }

                $additionalVisitors = AdditionalVisitor::where('main_visitor_id', $mainDelegateId)->get();

                if (!$additionalVisitors->isEmpty()) {
                    foreach ($additionalVisitors as $additionalVisitor) {
                        $details = [
                            'name' => $additionalVisitor->salutation . " " . $additionalVisitor->first_name . " " . $additionalVisitor->middle_name . " " . $additionalVisitor->last_name,
                            'eventLink' => $event->link,
                            'eventName' => $event->name,
                            'eventCategory' => $event->category,
                            'eventDates' => $eventFormattedData,
                            'eventLocation' => $event->location,
                            'bankDetails' => $bankDetails,
                            'invoiceLink' => $invoiceLink,
                            'eventYear' => $event->year,
                        ];

                        try {
                            Mail::to($additionalVisitor->email_address)->cc($ccEmailNotif)->send(new RegistrationCardDeclined($details));
                        } catch (\Exception $e) {
                            Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                        }
                    }
                }
                return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
            }
        } else if ($registrationFormType == "awards") {
            $mainDelegateId = request()->query('mainDelegateId');
            $sessionId = request()->query('sessionId');
            if (
                request()->input('response_gatewayRecommendation') == "PROCEED" &&
                request()->input('result') == "SUCCESS" &&
                request()->input('order_id') &&
                request()->input('transaction_id') &&
                request()->query('sessionId') &&
                request()->query('mainDelegateId') &&
                request()->query('registrationFormType')
            ) {
                $orderId = request()->input('order_id');
                $oldTransactionId = request()->input('transaction_id');
                $newTransactionId = substr(uniqid(), -8);

                $apiEndpoint = env('MERCHANT_API_URL');
                $merchantId = env('MERCHANT_ID');
                $authPass = env('MERCHANT_AUTH_PASSWORD');

                $client = new Client();
                $response = $client->request('PUT', $apiEndpoint . '/order/' . $orderId . '/transaction/' . $newTransactionId, [
                    'auth' => [
                        'merchant.' . $merchantId,
                        $authPass,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'apiOperation' => "PAY",
                        "authentication" => [
                            "transactionId" => $oldTransactionId,
                        ],
                        "order" => [
                            "reference" => $orderId,
                        ],
                        "session" => [
                            'id' => $sessionId,
                        ],
                        "transaction" => [
                            "reference" => $orderId,
                        ],
                    ]
                ]);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (
                    $data['response']['gatewayCode'] == "APPROVED" &&
                    $data['response']['gatewayRecommendation'] == "NO_ACTION" &&
                    $data['transaction']['authenticationStatus'] == "AUTHENTICATION_SUCCESSFUL" &&
                    $data['transaction']['type'] == "PAYMENT"
                ) {
                    AwardsMainParticipant::find($mainDelegateId)->fill([
                        'registration_status' => "confirmed",
                        'payment_status' => "paid",
                        'paid_date_time' => Carbon::now(),

                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();

                    $mainParticipant = AwardsMainParticipant::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainParticipant->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                    $transactionId = AwardsParticipantTransaction::where('participant_id', $mainDelegateId)->where('participant_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($event->category == $eventCategoryC) {
                            $getEventcode = $code;
                        }
                    }

                    $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";
                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $entryFormId = AwardsParticipantDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'entryForm')->value('id');
                    $entryFormFileName = AwardsParticipantDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'entryForm')->value('document_file_name');

                    $getSupportingDocumentFiles = AwardsParticipantDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'supportingDocument')->get();

                    $supportingDocumentsDownloadId = [];
                    $supportingDocumentsDownloadFileName = [];

                    if ($getSupportingDocumentFiles->isNotEmpty()) {
                        foreach ($getSupportingDocumentFiles as $supportingDocument) {
                            $supportingDocumentsDownloadId[] = $supportingDocument->id;
                            $supportingDocumentsDownloadFileName[] = $supportingDocument->document_file_name;
                        }
                    }

                    $downloadLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/';

                    $details1 = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'jobTitle' => $mainParticipant->job_title,
                        'companyName' => $mainParticipant->company_name,
                        'emailAddress' => $mainParticipant->email_address,
                        'mobileNumber' => $mainParticipant->mobile_number,
                        'city' => $mainParticipant->city,
                        'country' => $mainParticipant->country,
                        'category' => $mainParticipant->category,
                        'nationality' => $mainParticipant->nationality,
                        'subCategory' => ($mainParticipant->sub_category != null) ? $mainParticipant->sub_category : 'N/A',
                        'entryFormId' => $entryFormId,
                        'entryFormFileName' => $entryFormFileName,
                        'supportingDocumentsDownloadId' => $supportingDocumentsDownloadId,
                        'supportingDocumentsDownloadFileName' => $supportingDocumentsDownloadFileName,
                        'downloadLink' => $downloadLink,

                        'amountPaid' => $mainParticipant->total_amount,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "main" . "/" . $mainParticipant->id,
                    ];

                    $details2 = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'invoiceAmount' => $mainParticipant->total_amount,
                        'amountPaid' => $mainParticipant->total_amount,
                        'balance' => 0,
                        'invoiceLink' => $invoiceLink,
                    ];

                    try {
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.scea'))->send(new RegistrationPaid($details1));
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.scea'))->send(new RegistrationPaymentConfirmation($details2));
                    } catch (\Exception $e) {
                        Mail::to('zaman@gpca.org.ae')->send(new RegistrationPaid($details1));
                        Mail::to('zaman@gpca.org.ae')->send(new RegistrationPaymentConfirmation($details2));
                    }


                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "success"]);
                } else {
                    // (This is the part where we will send them email notification failed and redirect them)
                    AwardsMainParticipant::find($mainDelegateId)->fill([
                        'registration_status' => "pending",
                        'payment_status' => "unpaid",
                    ])->save();

                    $mainParticipant = AwardsMainParticipant::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainParticipant->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                    $bankDetails = config('app.bankDetails.DEFAULT');

                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'bankDetails' => $bankDetails,
                        'invoiceLink' => $invoiceLink,
                        'eventYear' => $event->year,
                    ];

                    try {
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.scea'))->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to('zaman@gpca.org.ae')->send(new RegistrationCardDeclined($details));
                    }

                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
                }
            } else {
                // (This is the part where we will send them email notification failed and redirect them)
                AwardsMainParticipant::find($mainDelegateId)->fill([
                    'registration_status' => "pending",
                    'payment_status' => "unpaid",
                ])->save();

                $mainParticipant = AwardsMainParticipant::where('id', $mainDelegateId)->first();
                $event = Event::where('id', $mainParticipant->event_id)->first();
                $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                $bankDetails = config('app.bankDetails.DEFAULT');

                $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                $details = [
                    'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                    'eventLink' => $event->link,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDates' => $eventFormattedData,
                    'eventLocation' => $event->location,
                    'bankDetails' => $bankDetails,
                    'invoiceLink' => $invoiceLink,
                    'eventYear' => $event->year,
                ];

                try {
                    Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.scea'))->send(new RegistrationCardDeclined($details));
                } catch (\Exception $e) {
                    Mail::to('zaman@gpca.org.ae')->send(new RegistrationCardDeclined($details));
                }

                return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
            }
        } else {
            $mainDelegateId = request()->query('mainDelegateId');
            $sessionId = request()->query('sessionId');
            if (
                request()->input('response_gatewayRecommendation') == "PROCEED" &&
                request()->input('result') == "SUCCESS" &&
                request()->input('order_id') &&
                request()->input('transaction_id') &&
                request()->query('sessionId') &&
                request()->query('mainDelegateId') &&
                request()->query('registrationFormType')
            ) {
                $orderId = request()->input('order_id');
                $oldTransactionId = request()->input('transaction_id');
                $newTransactionId = substr(uniqid(), -8);

                $apiEndpoint = env('MERCHANT_API_URL');
                $merchantId = env('MERCHANT_ID');
                $authPass = env('MERCHANT_AUTH_PASSWORD');

                $client = new Client();
                $response = $client->request('PUT', $apiEndpoint . '/order/' . $orderId . '/transaction/' . $newTransactionId, [
                    'auth' => [
                        'merchant.' . $merchantId,
                        $authPass,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'apiOperation' => "PAY",
                        "authentication" => [
                            "transactionId" => $oldTransactionId,
                        ],
                        "order" => [
                            "reference" => $orderId,
                        ],
                        "session" => [
                            'id' => $sessionId,
                        ],
                        "transaction" => [
                            "reference" => $orderId,
                        ],
                    ]
                ]);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (
                    $data['response']['gatewayCode'] == "APPROVED" &&
                    $data['response']['gatewayRecommendation'] == "NO_ACTION" &&
                    $data['transaction']['authenticationStatus'] == "AUTHENTICATION_SUCCESSFUL" &&
                    $data['transaction']['type'] == "PAYMENT"
                ) {
                    RccAwardsMainParticipant::find($mainDelegateId)->fill([
                        'registration_status' => "confirmed",
                        'payment_status' => "paid",
                        'paid_date_time' => Carbon::now(),

                        'registration_confirmation_sent_count' => 1,
                        'registration_confirmation_sent_datetime' => Carbon::now(),
                    ])->save();

                    $mainParticipant = RccAwardsMainParticipant::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainParticipant->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                    $transactionId = RccAwardsParticipantTransaction::where('participant_id', $mainDelegateId)->where('participant_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($event->category == $eventCategoryC) {
                            $getEventcode = $code;
                        }
                    }

                    $tempTransactionId = "$event->year" . "$getEventcode" . "$lastDigit";
                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $entryFormId = RccAwardsDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'entryForm')->value('id');
                    $entryFormFileName = RccAwardsDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'entryForm')->value('document_file_name');

                    $getSupportingDocumentFiles = RccAwardsDocument::where('event_id', $event->id)->where('participant_id', $mainDelegateId)->where('document_type', 'supportingDocument')->get();

                    $supportingDocumentsDownloadId = [];
                    $supportingDocumentsDownloadFileName = [];

                    if ($getSupportingDocumentFiles->isNotEmpty()) {
                        foreach ($getSupportingDocumentFiles as $supportingDocument) {
                            $supportingDocumentsDownloadId[] = $supportingDocument->id;
                            $supportingDocumentsDownloadFileName[] = $supportingDocument->document_file_name;
                        }
                    }

                    $downloadLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/';

                    $details1 = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'jobTitle' => $mainParticipant->job_title,
                        'companyName' => $mainParticipant->company_name,
                        'emailAddress' => $mainParticipant->email_address,
                        'mobileNumber' => $mainParticipant->mobile_number,
                        'city' => $mainParticipant->city,
                        'country' => $mainParticipant->country,
                        'category' => $mainParticipant->category,
                        'subCategory' => ($mainParticipant->sub_category != null) ? $mainParticipant->sub_category : 'N/A',
                        'entryFormId' => $entryFormId,
                        'entryFormFileName' => $entryFormFileName,
                        'supportingDocumentsDownloadId' => $supportingDocumentsDownloadId,
                        'supportingDocumentsDownloadFileName' => $supportingDocumentsDownloadFileName,
                        'downloadLink' => $downloadLink,

                        'amountPaid' => $mainParticipant->unit_price,
                        'transactionId' => $tempTransactionId,
                        'invoiceLink' => $invoiceLink,
                        'badgeLink' => env('APP_URL') . "/" . $event->category . "/" . $event->id . "/view-badge" . "/" . "main" . "/" . $mainParticipant->id,
                    ];

                    $details2 = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventYear' => $event->year,

                        'invoiceAmount' => $mainParticipant->total_amount,
                        'amountPaid' => $mainParticipant->total_amount,
                        'balance' => 0,
                        'invoiceLink' => $invoiceLink,
                    ];

                    try {
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaid($details1));
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationPaymentConfirmation($details2));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaid($details1));
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationPaymentConfirmation($details2));
                    }


                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "success"]);
                } else {
                    // (This is the part where we will send them email notification failed and redirect them)
                    RccAwardsMainParticipant::find($mainDelegateId)->fill([
                        'registration_status' => "pending",
                        'payment_status' => "unpaid",
                    ])->save();

                    $mainParticipant = RccAwardsMainParticipant::where('id', $mainDelegateId)->first();
                    $event = Event::where('id', $mainParticipant->event_id)->first();
                    $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                    if ($event->category == "AF") {
                        $bankDetails = config('app.bankDetails.AF');
                    } else {
                        $bankDetails = config('app.bankDetails.DEFAULT');
                    }

                    $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                    $details = [
                        'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                        'eventLink' => $event->link,
                        'eventName' => $event->name,
                        'eventCategory' => $event->category,
                        'eventDates' => $eventFormattedData,
                        'eventLocation' => $event->location,
                        'bankDetails' => $bankDetails,
                        'invoiceLink' => $invoiceLink,
                        'eventYear' => $event->year,
                    ];

                    try {
                        Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                    } catch (\Exception $e) {
                        Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                    }

                    return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
                }
            } else {
                // (This is the part where we will send them email notification failed and redirect them)
                RccAwardsMainParticipant::find($mainDelegateId)->fill([
                    'registration_status' => "pending",
                    'payment_status' => "unpaid",
                ])->save();

                $mainParticipant = RccAwardsMainParticipant::where('id', $mainDelegateId)->first();
                $event = Event::where('id', $mainParticipant->event_id)->first();
                $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');

                if ($event->category == "AF") {
                    $bankDetails = config('app.bankDetails.AF');
                } else {
                    $bankDetails = config('app.bankDetails.DEFAULT');
                }

                $invoiceLink = env('APP_URL') . '/' . $event->category . '/' . $event->id . '/view-invoice/' . $mainDelegateId;

                $details = [
                    'name' => $mainParticipant->salutation . " " . $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                    'eventLink' => $event->link,
                    'eventName' => $event->name,
                    'eventCategory' => $event->category,
                    'eventDates' => $eventFormattedData,
                    'eventLocation' => $event->location,
                    'bankDetails' => $bankDetails,
                    'invoiceLink' => $invoiceLink,
                    'eventYear' => $event->year,
                ];

                try {
                    Mail::to($mainParticipant->email_address)->cc(config('app.ccEmailNotif.default'))->send(new RegistrationCardDeclined($details));
                } catch (\Exception $e) {
                    Mail::to(config('app.ccEmailNotif.error'))->send(new RegistrationCardDeclined($details));
                }

                return redirect()->route('register.loading.view', ['eventCategory' => $event->category, 'eventId' => $event->id, 'eventYear' => $event->year, 'mainDelegateId' => $mainDelegateId, 'status' => "failed"]);
            }
        }
    }

    public function downloadFile($documentId)
    {
        if (RccAwardsDocument::where('id', $documentId)->exists()) {
            $documentFilePathTemp = RccAwardsDocument::where('id', $documentId)->value('document');

            $documentFilePath = Str::replace('public', 'storage', $documentFilePathTemp);

            if (!Storage::url($documentFilePath)) {
                abort(404, 'File not found');
            }

            $mimeType = Storage::mimeType($documentFilePath);

            $path = parse_url($documentFilePath, PHP_URL_PATH);
            $filename = basename($path);

            $headers = [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return Response::download($documentFilePath, $filename, $headers);
        } else {
            abort(404, 'File not found');
        }
    }

    public function eventDownloadFile($eventCategory, $eventId, $documentId)
    {
        if (Event::where('id', $eventId)->exists()) {
            if ($eventCategory == "SCEA") {
                if (AwardsParticipantDocument::where('id', $documentId)->exists()) {
                    $documentFilePathTemp = AwardsParticipantDocument::where('id', $documentId)->value('document');
                    $documentFilePath = Str::replace('public', 'storage', $documentFilePathTemp);

                    if (!Storage::url($documentFilePath)) {
                        abort(404, 'File not found');
                    }

                    $mimeType = Storage::mimeType($documentFilePath);

                    $path = parse_url($documentFilePath, PHP_URL_PATH);
                    $filename = basename($path);

                    $headers = [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ];

                    return Response::download($documentFilePath, $filename, $headers);
                } else {
                    abort(404, 'File not found');
                }
            } else {
                if (RccAwardsDocument::where('id', $documentId)->exists()) {
                    $documentFilePathTemp = RccAwardsDocument::where('id', $documentId)->value('document');
                    $documentFilePath = Str::replace('public', 'storage', $documentFilePathTemp);

                    if (!Storage::url($documentFilePath)) {
                        abort(404, 'File not found');
                    }

                    $mimeType = Storage::mimeType($documentFilePath);

                    $path = parse_url($documentFilePath, PHP_URL_PATH);
                    $filename = basename($path);

                    $headers = [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ];

                    return Response::download($documentFilePath, $filename, $headers);
                } else {
                    abort(404, 'File not found');
                }
            }
        } else {
            abort(404, 'Event not found');
        }
    }







    public function registrationFailedViewEvents($eventCategory, $eventId, $mainDelegateId)
    {
        $mainDelegate = MainDelegate::where('id', $mainDelegateId)->first();

        if ($mainDelegate->confirmation_status == "failed" || $mainDelegate->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainDelegateId;

            if ($eventCategory == "AF" || $eventCategory == "AFS" || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainDelegate->confirmation_date_time == null) {
                MainDelegate::find($mainDelegateId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "failed",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainDelegate->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationFailedViewSpouse($eventCategory, $eventId, $mainSpouseId)
    {
        $mainSpouse = MainSpouse::where('id', $mainSpouseId)->first();

        if ($mainSpouse->confirmation_status == "failed" || $mainSpouse->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainSpouseId;

            if ($eventCategory == "AF" || $eventCategory == "AFS"  || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainSpouse->confirmation_date_time == null) {
                MainSpouse::find($mainSpouseId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "failed",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainSpouse->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationFailedViewVisitor($eventCategory, $eventId, $mainVisitorId)
    {
        $mainVisitor = MainVisitor::where('id', $mainVisitorId)->first();

        if ($mainVisitor->confirmation_status == "failed" || $mainVisitor->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainVisitorId;

            $bankDetails = config('app.bankDetails.AF');

            if ($mainVisitor->confirmation_date_time == null) {
                MainVisitor::find($mainVisitorId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "failed",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainVisitor->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationFailedViewRccAwards($eventCategory, $eventId, $mainParticipantId)
    {
        $mainParticipant = RccAwardsMainParticipant::where('id', $mainParticipantId)->first();

        if ($mainParticipant->confirmation_status == "failed" || $mainParticipant->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainParticipantId;

            if ($eventCategory == "AF" || $eventCategory == "AFS"  || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainParticipant->confirmation_date_time == null) {
                RccAwardsMainParticipant::find($mainParticipantId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "failed",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainParticipant->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationFailedViewAwards($eventCategory, $eventId, $mainParticipantId)
    {
        $mainParticipant = AwardsMainParticipant::where('id', $mainParticipantId)->first();

        if ($mainParticipant->confirmation_status == "failed" || $mainParticipant->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainParticipantId;

            $bankDetails = config('app.bankDetails.DEFAULT');

            if ($mainParticipant->confirmation_date_time == null) {
                AwardsMainParticipant::find($mainParticipantId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "failed",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainParticipant->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }



    public function registrationSuccessViewEvents($eventCategory, $eventId, $mainDelegateId)
    {
        $mainDelegate = MainDelegate::where('id', $mainDelegateId)->first();

        if ($mainDelegate->confirmation_status == "success" || $mainDelegate->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainDelegateId;

            if ($eventCategory == "AF" || $eventCategory == "AFS" || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainDelegate->confirmation_date_time == null) {
                MainDelegate::find($mainDelegateId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "success",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainDelegate->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationSuccessViewSpouse($eventCategory, $eventId, $mainSpouseId)
    {
        $mainSpouse = MainSpouse::where('id', $mainSpouseId)->first();

        if ($mainSpouse->confirmation_status == "success" || $mainSpouse->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainSpouseId;

            if ($eventCategory == "AF" || $eventCategory == "AFS" || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainSpouse->confirmation_date_time == null) {
                MainSpouse::find($mainSpouseId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "success",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainSpouse->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationSuccessViewVisitor($eventCategory, $eventId, $mainVisitorId)
    {
        $mainVisitor = MainVisitor::where('id', $mainVisitorId)->first();

        if ($mainVisitor->confirmation_status == "success" || $mainVisitor->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainVisitorId;

            $bankDetails = config('app.bankDetails.AF');

            if ($mainVisitor->confirmation_date_time == null) {
                MainVisitor::find($mainVisitorId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "success",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainVisitor->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationSuccessViewRccAwards($eventCategory, $eventId, $mainParticipantId)
    {
        $mainParticipant = RccAwardsMainParticipant::where('id', $mainParticipantId)->first();

        if ($mainParticipant->confirmation_status == "success" || $mainParticipant->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainParticipantId;

            if ($eventCategory == "AF" || $eventCategory == "AFS" || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            if ($mainParticipant->confirmation_date_time == null) {
                RccAwardsMainParticipant::find($mainParticipantId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "success",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainParticipant->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function registrationSuccessViewAwards($eventCategory, $eventId, $mainParticipantId)
    {
        $mainParticipant = AwardsMainParticipant::where('id', $mainParticipantId)->first();

        if ($mainParticipant->confirmation_status == "success" || $mainParticipant->confirmation_date_time == null) {
            $invoiceLink = env('APP_URL') . '/' . $eventCategory . '/' . $eventId . '/view-invoice/' . $mainParticipantId;
            $bankDetails = config('app.bankDetails.DEFAULT');

            if ($mainParticipant->confirmation_date_time == null) {
                AwardsMainParticipant::find($mainParticipantId)->fill([
                    'confirmation_date_time' => Carbon::now(),
                    'confirmation_status' => "success",
                ])->save();
            }

            return [
                'invoiceLink' => $invoiceLink,
                'bankDetails' => $bankDetails,
                'paymentStatus' => $mainParticipant->payment_status,
            ];
        } else {
            abort(404, 'The URL is incorrect');
        }
    }



    public function getInvoiceEvents($eventCategory, $eventId, $registrantId)
    {

        if (MainDelegate::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
            $invoiceDetails = array();
            $countFinalQuantity = 0;

            $mainDelegate = MainDelegate::where('id', $registrantId)->where('event_id', $eventId)->first();

            if ($mainDelegate->pass_type == "fullMember") {
                $passType = "Full member";
            } else if ($mainDelegate->pass_type == "member") {
                $passType = "Member";
            } else {
                $passType = "Non-Member";
            }

            $addMainDelegate = true;
            if ($mainDelegate->delegate_cancelled) {
                if ($mainDelegate->delegate_refunded || $mainDelegate->delegate_replaced) {
                    $addMainDelegate = false;
                }
            }

            if ($mainDelegate->delegate_replaced_by_id == null & (!$mainDelegate->delegate_refunded)) {
                $countFinalQuantity++;
            }

            if ($addMainDelegate) {
                $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainDelegate->pcode_used)->first();

                if ($promoCode != null) {
                    if ($promoCode->badge_type == $mainDelegate->badge_type) {
                        $promoCodeUsed = $mainDelegate->pcode_used;
                        $mainDiscount = $promoCode->discount;
                        $mainDiscountType = $promoCode->discount_type;
                    } else {
                        $promoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $promoCode->id)->where('badge_type', $mainDelegate->badge_type)->first();

                        if ($promoCodeAdditionalBadgeType != null) {
                            $promoCodeUsed = $mainDelegate->pcode_used;
                            $mainDiscount = $promoCode->discount;
                            $mainDiscountType = $promoCode->discount_type;
                        } else {
                            $promoCodeUsed = null;
                            $mainDiscount = 0;
                            $mainDiscountType = null;
                        }
                    }
                } else {
                    $promoCodeUsed = null;
                    $mainDiscount = 0;
                    $mainDiscountType = null;
                }


                if ($mainDelegate->badge_type == "Leaders of Tomorrow") {
                    $delegateDescription = "Delegate Registration Fee - Leaders of Tomorrow";
                } else {
                    if ($mainDiscountType != null) {
                        if ($mainDiscountType == "percentage") {
                            if ($mainDiscount == 100) {
                                if ($mainDelegate->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                                    $delegateDescription = "Delegate Registration Fee - Complimentary - Conference only";
                                } else if ($mainDelegate->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                                    $delegateDescription = "Delegate Registration Fee - Complimentary - Workshop only";
                                } else {
                                    $delegateDescription = "Delegate Registration Fee - Complimentary";
                                }
                            } else if ($mainDiscount > 0 && $mainDiscount < 100) {
                                if ($mainDelegate->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                                    $delegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate - Conference only";
                                } else if ($mainDelegate->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                                    $delegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate - Workshop only";
                                } else {
                                    $delegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate";
                                }
                            } else {
                                $delegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                            }
                        } else if ($mainDiscountType == "price") {
                            $delegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                        } else {
                            $delegateDescription = $promoCode->new_rate_description;
                        }
                    } else {
                        $delegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                    }
                }
                if ($mainDiscountType != null) {
                    if ($mainDiscountType == "percentage") {
                        $tempUnitPrice = $mainDelegate->unit_price;
                        $tempTotalDiscount = $mainDelegate->unit_price * ($mainDiscount / 100);
                        $tempTotalAmount = $mainDelegate->unit_price - ($mainDelegate->unit_price * ($mainDiscount / 100));
                    } else if ($mainDiscountType == "price") {
                        $tempUnitPrice = $mainDelegate->unit_price;
                        $tempTotalDiscount = $mainDiscount;
                        $tempTotalAmount = $mainDelegate->unit_price - $mainDiscount;
                    } else {
                        $tempUnitPrice = $promoCode->new_rate;
                        $tempTotalDiscount = 0;
                        $tempTotalAmount = $promoCode->new_rate;
                    }
                } else {
                    $tempUnitPrice = $mainDelegate->unit_price;
                    $tempTotalDiscount = 0;
                    $tempTotalAmount = $mainDelegate->unit_price;
                }

                array_push($invoiceDetails, [
                    'delegateDescription' => $delegateDescription,
                    'delegateNames' => [
                        $mainDelegate->first_name . " " . $mainDelegate->middle_name . " " . $mainDelegate->last_name,
                    ],
                    'badgeType' => $mainDelegate->badge_type,
                    'quantity' => 1,
                    'totalUnitPrice' => $tempUnitPrice,
                    'totalDiscount' => $tempTotalDiscount,
                    'totalNetAmount' =>  $tempTotalAmount,
                    'promoCodeDiscount' => $mainDiscount,
                    'promoCodeUsed' => $promoCodeUsed,
                ]);
            }


            $subDelegates = AdditionalDelegate::where('main_delegate_id', $registrantId)->get();
            if (!$subDelegates->isEmpty()) {
                foreach ($subDelegates as $subDelegate) {

                    if ($subDelegate->delegate_replaced_by_id == null & (!$subDelegate->delegate_refunded)) {
                        $countFinalQuantity++;
                    }

                    $addSubDelegate = true;
                    if ($subDelegate->delegate_cancelled) {
                        if ($subDelegate->delegate_refunded || $subDelegate->delegate_replaced) {
                            $addSubDelegate = false;
                        }
                    }

                    if ($addSubDelegate) {
                        $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subDelegate->pcode_used)->first();

                        if ($subPromoCode != null) {
                            if ($subPromoCode->badge_type == $subDelegate->badge_type) {
                                $subPromoCodeUsed = $subDelegate->pcode_used;
                                $subDiscount = $subPromoCode->discount;
                                $subDiscountType = $subPromoCode->discount_type;
                            } else {
                                $subPromoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $subPromoCode->id)->where('badge_type', $subDelegate->badge_type)->first();

                                if ($subPromoCodeAdditionalBadgeType != null) {
                                    $subPromoCodeUsed = $subDelegate->pcode_used;
                                    $subDiscount = $subPromoCode->discount;
                                    $subDiscountType = $subPromoCode->discount_type;
                                } else {
                                    $subPromoCodeUsed = null;
                                    $subDiscount = 0;
                                    $subDiscountType = null;
                                }
                            }
                        } else {
                            $subPromoCodeUsed = null;
                            $subDiscount = 0;
                            $subDiscountType = null;
                        }


                        $checkIfExisting = false;
                        $existingIndex = 0;

                        for ($j = 0; $j < count($invoiceDetails); $j++) {
                            if ($subDelegate->badge_type == $invoiceDetails[$j]['badgeType'] && $subPromoCodeUsed == $invoiceDetails[$j]['promoCodeUsed']) {
                                $existingIndex = $j;
                                $checkIfExisting = true;
                                break;
                            }
                        }

                        if ($checkIfExisting) {
                            array_push(
                                $invoiceDetails[$existingIndex]['delegateNames'],
                                $subDelegate->first_name . " " . $subDelegate->middle_name . " " . $subDelegate->last_name
                            );

                            $quantityTemp = $invoiceDetails[$existingIndex]['quantity'] + 1;

                            if ($subDiscountType != null) {
                                if ($subDiscountType == "percentage") {
                                    $totalDiscountTemp = ($mainDelegate->unit_price * ($invoiceDetails[$existingIndex]['promoCodeDiscount'] / 100)) * $quantityTemp;
                                    $totalNetAmountTemp = ($mainDelegate->unit_price * $quantityTemp) - $totalDiscountTemp;
                                } else if ($subDiscountType == "price") {
                                    $totalDiscountTemp = $invoiceDetails[$existingIndex]['promoCodeDiscount'] * $quantityTemp;
                                    $totalNetAmountTemp = ($mainDelegate->unit_price * $quantityTemp) - $totalDiscountTemp;
                                } else {
                                    $totalDiscountTemp = 0;
                                    $totalNetAmountTemp = $subPromoCode->new_rate * $quantityTemp;
                                }
                            } else {
                                $totalDiscountTemp = $invoiceDetails[$existingIndex]['promoCodeDiscount'];
                                $totalNetAmountTemp = ($mainDelegate->unit_price * $quantityTemp) - $totalDiscountTemp;
                            }

                            $invoiceDetails[$existingIndex]['quantity'] = $quantityTemp;
                            $invoiceDetails[$existingIndex]['totalDiscount'] = $totalDiscountTemp;
                            $invoiceDetails[$existingIndex]['totalNetAmount'] = $totalNetAmountTemp;
                        } else {

                            if ($subDelegate->badge_type == "Leaders of Tomorrow") {
                                $subDelegateDescription = "Delegate Registration Fee - Leaders of Tomorrow";
                            } else {
                                if ($subDiscountType != null) {
                                    if ($subDiscountType == "percentage") {
                                        if ($subDiscount == 100) {
                                            if ($mainDelegate->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                                                $subDelegateDescription = "Delegate Registration Fee - Complimentary - Conference only";
                                            } else if ($mainDelegate->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                                                $subDelegateDescription = "Delegate Registration Fee - Complimentary - Workshop only";
                                            } else {
                                                $subDelegateDescription = "Delegate Registration Fee - Complimentary";
                                            }
                                        } else if ($subDiscount > 0 && $subDiscount < 100) {
                                            if ($mainDelegate->accessType == AccessTypes::CONFERENCE_ONLY->value) {
                                                $subDelegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate - Conference only";
                                            } else if ($mainDelegate->accessType == AccessTypes::WORKSHOP_ONLY->value) {
                                                $subDelegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate - Workshop only";
                                            } else {
                                                $subDelegateDescription = "Delegate Registration Fee - " . $passType . " discounted rate";
                                            }
                                        } else {
                                            $subDelegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                                        }
                                    } else if ($subDiscountType == "price") {
                                        $subDelegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                                    } else {
                                        $subDelegateDescription = $subPromoCode->new_rate_description;
                                    }
                                } else {
                                    $subDelegateDescription = "Delegate Registration Fee - {$mainDelegate->rate_type_string}";
                                }
                            }
                            if ($subDiscountType != null) {
                                if ($subDiscountType == "percentage") {
                                    $tempSubUnitPrice = $mainDelegate->unit_price;
                                    $tempSubTotalDiscount = $mainDelegate->unit_price * ($subDiscount / 100);
                                    $tempSubTotalAmount = $mainDelegate->unit_price - ($mainDelegate->unit_price * ($subDiscount / 100));
                                } else if ($subDiscountType == "price") {
                                    $tempSubUnitPrice = $mainDelegate->unit_price;
                                    $tempSubTotalDiscount = $subDiscount;
                                    $tempSubTotalAmount = $mainDelegate->unit_price - $subDiscount;
                                } else {
                                    $tempSubUnitPrice = $subPromoCode->new_rate;
                                    $tempSubTotalDiscount = 0;
                                    $tempSubTotalAmount = $subPromoCode->new_rate;
                                }
                            } else {
                                $tempSubUnitPrice = $mainDelegate->unit_price;
                                $tempSubTotalDiscount = 0;
                                $tempSubTotalAmount = $mainDelegate->unit_price;
                            }

                            array_push($invoiceDetails, [
                                'delegateDescription' => $subDelegateDescription,
                                'delegateNames' => [
                                    $subDelegate->first_name . " " . $subDelegate->middle_name . " " . $subDelegate->last_name,
                                ],
                                'badgeType' => $subDelegate->badge_type,
                                'quantity' => 1,
                                'totalUnitPrice' => $tempSubUnitPrice,
                                'totalDiscount' => $tempSubTotalDiscount,
                                'totalNetAmount' =>  $tempSubTotalAmount,
                                'promoCodeDiscount' => $subDiscount,
                                'promoCodeUsed' => $subPromoCodeUsed,
                            ]);
                        }
                    }
                }
            }

            $transactionId = Transaction::where('delegate_id', $mainDelegate->id)->where('delegate_type', "main")->value('id');

            $tempYear = Carbon::parse($mainDelegate->registered_date_time)->format('y');
            $lastDigit = 1000 + intval($transactionId);

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($event->category == $eventCategoryC) {
                    $getEventcode = $code;
                }
            }

            $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
            $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";


            if ($eventCategory == "AF") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            // if ($eventCategory == "GLF" || $eventCategory == "DFCLW1") {
            //     $eventFormattedData = Carbon::parse($event->event_end_date)->format('j F Y');
            // } else if ($eventCategory == "PSW" && $event->year == "2025") {
            //     $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F') . ' - ' . Carbon::parse($event->event_end_date)->format('j F Y');
            // } else {
            //     $eventFormattedData = Carbon::parse($event->event_start_date)->format('j') . '-' . Carbon::parse($event->event_end_date)->format('j F Y');
            // }

            $start = Carbon::parse($event->event_start_date);
            $end   = Carbon::parse($event->event_end_date);

            if ($eventCategory == "GLF" || $eventCategory == "DFCLW1") {
                $eventFormattedData = $end->format('j F Y');
            } else if ($eventCategory == "PSW" && $event->year == "2025") {
                $eventFormattedData = $start->format('j F') . ' - ' . $end->format('j F Y');
            } else {
                if ($start->format('F Y') === $end->format('F Y')) {
                    $eventFormattedData = $start->format('j') . '–' . $end->format('j F Y');
                } else {
                    $eventFormattedData = $start->format('j F') . ' – ' . $end->format('j F Y');
                }
            }

            if ($mainDelegate->alternative_company_name == null) {
                $finalCompanyName = $mainDelegate->company_name;
            } else {
                $finalCompanyName = $mainDelegate->alternative_company_name;
            }

            if ($event->category == "ANC" && $event->year == "2024") {
                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                    $invoiceDescription = $event->name . ' – 11-12 September 2024  at ' . $event->location;
                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                    $invoiceDescription = "Operational Excellence in the GCC Agri-Nutrients Industry Workshop – 10th September 2024 at " .  $event->location;
                } else {
                    $invoiceDescription = "Operational Excellence in the GCC Agri-Nutrients Industry Workshop and " . $event->name . ' – ' . $eventFormattedData . ' at ' . $event->location;
                }
            } else if ($event->category == "PSC" && $event->year == "2024") {
                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                    $invoiceDescription = $event->name . ' – 08-10 October 2024  at ' . $event->location;
                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                    $invoiceDescription = "Process Safety Workshops – 7th October 2024 at " .  $event->location;
                } else {
                    $invoiceDescription = "Process Safety Workshops and " . $event->name . ' – ' . $eventFormattedData . ' at ' . $event->location;
                }
            } else if ($event->category == "SCC" && $event->year == "2025") {
                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                    $invoiceDescription = $event->name . ' – 27-28 May 2025 at ' . $event->location;
                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                    $invoiceDescription = "Gulf SQAS Workshop – 26th May 2025 at the Sofitel Dubai Downtown";
                } else {
                    $invoiceDescription = "Gulf SQAS Workshop – 26th May 2025 at the Sofitel Dubai Downtown and " . $event->name . ' – ' . $eventFormattedData . ' at ' . $event->location;
                }
            } else if ($event->category == "ANC" && $event->year == "2025") {
                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                    $invoiceDescription = $event->name . ' – 30 September-01 October 2025  at ' . $event->location;
                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                    $invoiceDescription = "3rd Operational Excellence Workshop – 29th September 2025 at " .  $event->location;
                } else {
                    $invoiceDescription = "3rd Operational Excellence Workshop – 29th September 2025 and " . $event->name . ' - 30 September-01 October 2025  at ' . $event->location;
                }
            } else if ($event->category == "RCC" && $event->year == "2025") {
                if ($mainDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                    $invoiceDescription = $event->name . ' – 14-15 October 2025 at ' . $event->location;
                } else if ($mainDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                    $invoiceDescription = "Pre-Conference Workshops – 13th October 2025 at " .  $event->location;
                } else {
                    $invoiceDescription = "Pre-Conference Workshops – 13th October 2025 and " . $event->name . ' - 14-15 October 2025 at ' . $event->location;
                }
            } else {
                $invoiceDescription = $event->name . ' – ' . $eventFormattedData . ' at ' . $event->location;
            }

            $invoiceData = [
                "finalEventStartDate" => Carbon::parse($event->event_start_date)->format('d M Y'),
                "finalEventEndDate" => Carbon::parse($event->event_end_date)->format('d M Y'),
                "invoiceDescription" => $invoiceDescription,
                "eventFormattedData" => $eventFormattedData,
                "companyName" => $finalCompanyName,
                "companyAddress" => $mainDelegate->company_address,
                "companyCity" => $mainDelegate->company_city,
                "companyCountry" => $mainDelegate->company_country,
                "invoiceDate" => Carbon::parse($mainDelegate->registered_date_time)->format('d/m/Y'),
                "invoiceNumber" => $tempInvoiceNumber,
                "bookRefNumber" => $tempBookReference,
                "paymentStatus" => $mainDelegate->payment_status,
                "registrationMethod" => $mainDelegate->registration_method,
                "eventName" => $event->name,
                "eventLocation" => $event->location,
                "eventVat" => $event->event_vat,
                'vat_price' => $mainDelegate->vat_price,
                'net_amount' => $mainDelegate->net_amount,
                'total_amount' => $mainDelegate->total_amount,
                'unit_price' => $mainDelegate->unit_price,
                'invoiceDetails' => $invoiceDetails,
                'bankDetails' => $bankDetails,
                'finalQuantity' => $countFinalQuantity,
                'total_amount_string' => ucwords($this->numberToWords($mainDelegate->total_amount)),
            ];

            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function getInvoiceSpouse($eventCategory, $eventId, $registrantId)
    {
        if (MainSpouse::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
            $countFinalQuantity = 0;

            $mainSpouse = MainSpouse::where('id', $registrantId)->where('event_id', $eventId)->first();

            $addMainSpouse = true;
            if ($mainSpouse->spouse_cancelled) {
                if ($mainSpouse->spouse_refunded || $mainSpouse->spouse_replaced) {
                    $addMainSpouse = false;
                }
            }

            if ($mainSpouse->spouse_replaced_by_id == null & (!$mainSpouse->spouse_refunded)) {
                $countFinalQuantity++;
            }

            if ($addMainSpouse) {
                $fullname = $mainSpouse->first_name . " " . $mainSpouse->middle_name . " " . $mainSpouse->last_name;
            }


            $subSpouses = AdditionalSpouse::where('main_spouse_id', $registrantId)->get();
            if (!$subSpouses->isEmpty()) {
                foreach ($subSpouses as $subSpouse) {
                    if ($subSpouse->spouse_replaced_by_id == null & (!$subSpouse->spouse_refunded)) {
                        $countFinalQuantity++;
                    }

                    $addSubSpouse = true;
                    if ($subSpouse->spouse_cancelled) {
                        if ($subSpouse->spouse_refunded || $subSpouse->spouse_replaced) {
                            $addSubSpouse = false;
                        }
                    }

                    if ($addSubSpouse) {
                        $fullname = $subSpouse->first_name . " " . $subSpouse->middle_name . " " . $subSpouse->last_name;
                    }
                }
            }

            $transactionId = SpouseTransaction::where('spouse_id', $mainSpouse->id)->where('spouse_type', "main")->value('id');

            $tempYear = Carbon::parse($mainSpouse->registered_date_time)->format('y');
            $lastDigit = 1000 + intval($transactionId);

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($event->category == $eventCategoryC) {
                    $getEventcode = $code;
                }
            }

            $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
            $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

            if ($eventCategory == "AF" || $eventCategory == "AFS" || $eventCategory == "AFV") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            $eventFormattedData = Carbon::parse($event->event_start_date)->format('j') . '-' . Carbon::parse($event->event_end_date)->format('j F Y');
            $invoiceData = [
                "finalEventStartDate" => Carbon::parse($event->event_start_date)->format('d M Y'),
                "finalEventEndDate" => Carbon::parse($event->event_end_date)->format('d M Y'),
                "eventFormattedData" => $eventFormattedData,
                "fullname" => $fullname,
                "address" => $mainSpouse->address,
                "country" => $mainSpouse->country,
                "city" => $mainSpouse->city,
                "invoiceDate" => Carbon::parse($mainSpouse->registered_date_time)->format('d/m/Y'),
                "invoiceNumber" => $tempInvoiceNumber,
                "bookRefNumber" => $tempBookReference,
                "paymentStatus" => $mainSpouse->payment_status,
                "eventName" => $event->name,
                "eventLocation" => $event->location,
                "eventVat" => $event->event_vat,
                'vat_price' => $mainSpouse->vat_price,
                'net_amount' => $mainSpouse->net_amount,
                'total_amount' => $mainSpouse->total_amount,
                'unit_price' => $mainSpouse->unit_price,

                'registrationMethod' => "online",

                'day_one' => $mainSpouse->day_one,
                'day_two' => $mainSpouse->day_two,
                'day_three' => $mainSpouse->day_three,
                'day_four' => $mainSpouse->day_four,

                'bankDetails' => $bankDetails,
                'finalQuantity' => $countFinalQuantity,
                'total_amount_string' => ucwords($this->numberToWords($mainSpouse->total_amount)),
            ];

            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function getInvoiceVisitor($eventCategory, $eventId, $registrantId)
    {
        if (MainVisitor::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
            $invoiceDetails = array();
            $countFinalQuantity = 0;

            $mainVisitor = MainVisitor::where('id', $registrantId)->where('event_id', $eventId)->first();

            if ($mainVisitor->pass_type == "fullMember") {
                $passType = "Full member";
            } else if ($mainVisitor->pass_type == "member") {
                $passType = "Member";
            } else {
                $passType = "Non-Member";
            }

            $addMainVisitor = true;
            if ($mainVisitor->visitor_cancelled) {
                if ($mainVisitor->visitor_refunded || $mainVisitor->visitor_replaced) {
                    $addMainVisitor = false;
                }
            }

            if ($mainVisitor->visitor_replaced_by_id == null & (!$mainVisitor->visitor_refunded)) {
                $countFinalQuantity++;
            }

            if ($addMainVisitor) {
                $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainVisitor->pcode_used)->first();

                if ($promoCode != null) {
                    if ($promoCode->badge_type == $mainVisitor->badge_type) {
                        $promoCodeUsed = $mainVisitor->pcode_used;
                        $mainDiscount = $promoCode->discount;
                        $mainDiscountType = $promoCode->discount_type;
                    } else {
                        $promoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $promoCode->id)->where('badge_type', $mainVisitor->badge_type)->first();

                        if ($promoCodeAdditionalBadgeType != null) {
                            $promoCodeUsed = $mainVisitor->pcode_used;
                            $mainDiscount = $promoCode->discount;
                            $mainDiscountType = $promoCode->discount_type;
                        } else {
                            $promoCodeUsed = null;
                            $mainDiscount = 0;
                            $mainDiscountType = null;
                        }
                    }
                } else {
                    $promoCodeUsed = null;
                    $mainDiscount = 0;
                    $mainDiscountType = null;
                }

                if ($mainVisitor->badge_type == "Leaders of Tomorrow") {
                    $visitorDescription = "Visitor Registration Fee - Leaders of Tomorrow";
                } else {
                    if ($mainDiscountType != null) {
                        if ($mainDiscountType == "percentage") {
                            if ($mainDiscount == 100) {
                                $visitorDescription = "Visitor Registration Fee - Complimentary";
                            } else if ($mainDiscount > 0 && $mainDiscount < 100) {
                                $visitorDescription = "Visitor Registration Fee - " . $passType . " discounted rate";
                            } else {
                                $visitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                            }
                        } else if ($mainDiscountType == "price") {
                            $visitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                        } else {
                            $visitorDescription = $promoCode->new_rate_description;
                        }
                    } else {
                        $visitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                    }
                }
                if ($mainDiscountType != null) {
                    if ($mainDiscountType == "percentage") {
                        $tempUnitPrice = $mainVisitor->unit_price;
                        $tempTotalDiscount = $mainVisitor->unit_price * ($mainDiscount / 100);
                        $tempTotalAmount = $mainVisitor->unit_price - ($mainVisitor->unit_price * ($mainDiscount / 100));
                    } else if ($mainDiscountType == "price") {
                        $tempUnitPrice = $mainVisitor->unit_price;
                        $tempTotalDiscount = $mainDiscount;
                        $tempTotalAmount = $mainVisitor->unit_price - $mainDiscount;
                    } else {
                        $tempUnitPrice = $promoCode->new_rate;
                        $tempTotalDiscount = 0;
                        $tempTotalAmount = $promoCode->new_rate;
                    }
                } else {
                    $tempUnitPrice = $mainVisitor->unit_price;
                    $tempTotalDiscount = 0;
                    $tempTotalAmount = $mainVisitor->unit_price;
                }

                array_push($invoiceDetails, [
                    'visitorDescription' => $visitorDescription,
                    'visitorNames' => [
                        $mainVisitor->first_name . " " . $mainVisitor->middle_name . " " . $mainVisitor->last_name,
                    ],
                    'badgeType' => $mainVisitor->badge_type,
                    'quantity' => 1,
                    'totalUnitPrice' => $tempUnitPrice,
                    'totalDiscount' => $tempTotalDiscount,
                    'totalNetAmount' =>  $tempTotalAmount,
                    'promoCodeDiscount' => $mainDiscount,
                    'promoCodeUsed' => $promoCodeUsed,
                ]);
            }


            $subVisitors = AdditionalVisitor::where('main_visitor_id', $registrantId)->get();
            if (!$subVisitors->isEmpty()) {
                foreach ($subVisitors as $subVisitor) {

                    if ($subVisitor->visitor_replaced_by_id == null & (!$subVisitor->visitor_refunded)) {
                        $countFinalQuantity++;
                    }

                    $addSubVisitor = true;
                    if ($subVisitor->visitor_cancelled) {
                        if ($subVisitor->visitor_refunded || $subVisitor->visitor_replaced) {
                            $addSubVisitor = false;
                        }
                    }

                    if ($addSubVisitor) {
                        $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subVisitor->pcode_used)->first();

                        if ($subPromoCode != null) {
                            if ($subPromoCode->badge_type == $subVisitor->badge_type) {
                                $subPromoCodeUsed = $subVisitor->pcode_used;
                                $subDiscount = $subPromoCode->discount;
                                $subDiscountType = $subPromoCode->discount_type;
                            } else {
                                $subPromoCodeAdditionalBadgeType = PromoCodeAddtionalBadgeType::where('event_id', $eventId)->where('promo_code_id', $subPromoCode->id)->where('badge_type', $subVisitor->badge_type)->first();

                                if ($subPromoCodeAdditionalBadgeType != null) {
                                    $subPromoCodeUsed = $subVisitor->pcode_used;
                                    $subDiscount = $subPromoCode->discount;
                                    $subDiscountType = $subPromoCode->discount_type;
                                } else {
                                    $subPromoCodeUsed = null;
                                    $subDiscount = 0;
                                    $subDiscountType = null;
                                }
                            }
                        } else {
                            $subPromoCodeUsed = null;
                            $subDiscount = 0;
                            $subDiscountType = null;
                        }


                        $checkIfExisting = false;
                        $existingIndex = 0;

                        for ($j = 0; $j < count($invoiceDetails); $j++) {
                            if ($subVisitor->badge_type == $invoiceDetails[$j]['badgeType'] && $subPromoCodeUsed == $invoiceDetails[$j]['promoCodeUsed']) {
                                $existingIndex = $j;
                                $checkIfExisting = true;
                                break;
                            }
                        }

                        if ($checkIfExisting) {
                            array_push(
                                $invoiceDetails[$existingIndex]['visitorNames'],
                                $subVisitor->first_name . " " . $subVisitor->middle_name . " " . $subVisitor->last_name
                            );

                            $quantityTemp = $invoiceDetails[$existingIndex]['quantity'] + 1;

                            if ($subDiscountType != null) {
                                if ($subDiscountType == "percentage") {
                                    $totalDiscountTemp = ($mainVisitor->unit_price * ($invoiceDetails[$existingIndex]['promoCodeDiscount'] / 100)) * $quantityTemp;
                                    $totalNetAmountTemp = ($mainVisitor->unit_price * $quantityTemp) - $totalDiscountTemp;
                                } else if ($subDiscountType == "price") {
                                    $totalDiscountTemp = $invoiceDetails[$existingIndex]['promoCodeDiscount'] * $quantityTemp;
                                    $totalNetAmountTemp = ($mainVisitor->unit_price * $quantityTemp) - $totalDiscountTemp;
                                } else {
                                    $totalDiscountTemp = 0;
                                    $totalNetAmountTemp = $subPromoCode->new_rate * $quantityTemp;
                                }
                            } else {
                                $totalDiscountTemp = $invoiceDetails[$existingIndex]['promoCodeDiscount'];
                                $totalNetAmountTemp = ($mainVisitor->unit_price * $quantityTemp) - $totalDiscountTemp;
                            }

                            $invoiceDetails[$existingIndex]['quantity'] = $quantityTemp;
                            $invoiceDetails[$existingIndex]['totalDiscount'] = $totalDiscountTemp;
                            $invoiceDetails[$existingIndex]['totalNetAmount'] = $totalNetAmountTemp;
                        } else {

                            if ($subVisitor->badge_type == "Leaders of Tomorrow") {
                                $subVisitorDescription = "Visitor Registration Fee - Leaders of Tomorrow";
                            } else {
                                if ($subDiscountType != null) {
                                    if ($subDiscountType == "percentage") {
                                        if ($subDiscount == 100) {
                                            $subVisitorDescription = "Visitor Registration Fee - Complimentary";
                                        } else if ($subDiscount > 0 && $subDiscount < 100) {
                                            $subVisitorDescription = "Visitor Registration Fee - " . $passType . " discounted rate";
                                        } else {
                                            $subVisitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                                        }
                                    } else if ($subDiscountType == "price") {
                                        $subVisitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                                    } else {
                                        $subVisitorDescription = $subPromoCode->new_rate_description;
                                    }
                                } else {
                                    $subVisitorDescription = "Visitor Registration Fee - {$mainVisitor->rate_type_string}";
                                }
                            }
                            if ($subDiscountType != null) {
                                if ($subDiscountType == "percentage") {
                                    $tempSubUnitPrice = $mainVisitor->unit_price;
                                    $tempSubTotalDiscount = $mainVisitor->unit_price * ($subDiscount / 100);
                                    $tempSubTotalAmount = $mainVisitor->unit_price - ($mainVisitor->unit_price * ($subDiscount / 100));
                                } else if ($subDiscountType == "price") {
                                    $tempSubUnitPrice = $mainVisitor->unit_price;
                                    $tempSubTotalDiscount = $subDiscount;
                                    $tempSubTotalAmount = $mainVisitor->unit_price - $subDiscount;
                                } else {
                                    $tempSubUnitPrice = $subPromoCode->new_rate;
                                    $tempSubTotalDiscount = 0;
                                    $tempSubTotalAmount = $subPromoCode->new_rate;
                                }
                            } else {
                                $tempSubUnitPrice = $mainVisitor->unit_price;
                                $tempSubTotalDiscount = 0;
                                $tempSubTotalAmount = $mainVisitor->unit_price;
                            }

                            array_push($invoiceDetails, [
                                'visitorDescription' => $subVisitorDescription,
                                'visitorNames' => [
                                    $subVisitor->first_name . " " . $subVisitor->middle_name . " " . $subVisitor->last_name,
                                ],
                                'badgeType' => $subVisitor->badge_type,
                                'quantity' => 1,
                                'totalUnitPrice' => $tempSubUnitPrice,
                                'totalDiscount' => $tempSubTotalDiscount,
                                'totalNetAmount' =>  $tempSubTotalAmount,
                                'promoCodeDiscount' => $subDiscount,
                                'promoCodeUsed' => $subPromoCodeUsed,
                            ]);
                        }
                    }
                }
            }

            $transactionId = VisitorTransaction::where('visitor_id', $mainVisitor->id)->where('visitor_type', "main")->value('id');

            $tempYear = Carbon::parse($mainVisitor->registered_date_time)->format('y');
            $lastDigit = 1000 + intval($transactionId);

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($event->category == $eventCategoryC) {
                    $getEventcode = $code;
                }
            }

            $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
            $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

            $bankDetails = config('app.bankDetails.AF');

            $eventFormattedData = Carbon::parse($event->event_start_date)->format('j') . '-' . Carbon::parse($event->event_end_date)->format('j F Y');

            if ($mainVisitor->alternative_company_name == null) {
                $finalCompanyName = $mainVisitor->company_name;
            } else {
                $finalCompanyName = $mainVisitor->alternative_company_name;
            }

            $invoiceData = [
                "finalEventStartDate" => Carbon::parse($event->event_start_date)->format('d M Y'),
                "finalEventEndDate" => Carbon::parse($event->event_end_date)->format('d M Y'),
                "eventFormattedData" => $eventFormattedData,
                "companyName" => $finalCompanyName,
                "companyAddress" => $mainVisitor->company_address,
                "companyCity" => $mainVisitor->company_city,
                "companyCountry" => $mainVisitor->company_country,
                "invoiceDate" => Carbon::parse($mainVisitor->registered_date_time)->format('d/m/Y'),
                "invoiceNumber" => $tempInvoiceNumber,
                "bookRefNumber" => $tempBookReference,
                "paymentStatus" => $mainVisitor->payment_status,
                "registrationMethod" => $mainVisitor->registration_method,
                "eventName" => $event->name,
                "eventLocation" => $event->location,
                "eventVat" => $event->event_vat,
                'vat_price' => $mainVisitor->vat_price,
                'net_amount' => $mainVisitor->net_amount,
                'total_amount' => $mainVisitor->total_amount,
                'unit_price' => $mainVisitor->unit_price,
                'invoiceDetails' => $invoiceDetails,
                'bankDetails' => $bankDetails,
                'finalQuantity' => $countFinalQuantity,
                'total_amount_string' => ucwords($this->numberToWords($mainVisitor->total_amount)),
            ];

            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function getInvoiceRccAwards($eventCategory, $eventId, $registrantId)
    {
        if (RccAwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
            $invoiceDetails = array();
            $countFinalQuantity = 0;

            $mainParticipant = RccAwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->first();

            $addMainParticipant = true;
            if ($mainParticipant->participant_cancelled) {
                if ($mainParticipant->participant_refunded || $mainParticipant->participant_replaced) {
                    $addMainParticipant = false;
                }
            }

            if ($mainParticipant->participant_replaced_by_id == null & (!$mainParticipant->participant_refunded)) {
                $countFinalQuantity++;
            }

            if ($addMainParticipant) {
                array_push($invoiceDetails, [
                    'delegateDescription' => "Awards Submission fee",
                    // 'delegateNames' => [
                    //     $mainParticipant->first_name . " " . $mainParticipant->middle_name . " " . $mainParticipant->last_name,
                    // ],
                    'delegateNames' => [
                        "Category: " . $mainParticipant->category,
                    ],
                    'badgeType' => null,
                    'quantity' => 1,
                    'totalDiscount' => 0,
                    'totalNetAmount' =>  $mainParticipant->unit_price,
                    'promoCodeDiscount' => 0,
                ]);
            }


            $subParticipants = RccAwardsAdditionalParticipant::where('main_participant_id', $registrantId)->get();
            if (!$subParticipants->isEmpty()) {
                foreach ($subParticipants as $subParticipant) {
                    if ($subParticipant->participant_replaced_by_id == null & (!$subParticipant->participant_refunded)) {
                        $countFinalQuantity++;
                    }

                    $addSubParticipant = true;
                    if ($subParticipant->participant_cancelled) {
                        if ($subParticipant->participant_refunded || $subParticipant->participant_replaced) {
                            $addSubParticipant = false;
                        }
                    }

                    if ($addSubParticipant) {
                        $existingIndex = 0;

                        if (count($invoiceDetails) == 0) {
                            array_push($invoiceDetails, [
                                'delegateDescription' => "Awards Submission fee",
                                // 'delegateNames' => [
                                //     $subParticipant->first_name . " " . $subParticipant->middle_name . " " . $subParticipant->last_name,
                                // ],
                                'delegateNames' => [
                                    "Category: " . $mainParticipant->category,
                                ],
                                'badgeType' => null,
                                'quantity' => 1,
                                'totalDiscount' => 0,
                                'totalNetAmount' =>  $mainParticipant->unit_price,
                                'promoCodeDiscount' => 0,
                            ]);
                        }
                    }
                }
            }

            $transactionId = RccAwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');

            $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
            $lastDigit = 1000 + intval($transactionId);

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($event->category == $eventCategoryC) {
                    $getEventcode = $code;
                }
            }

            $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
            $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

            if ($eventCategory == "AF") {
                $bankDetails = config('app.bankDetails.AF');
            } else {
                $bankDetails = config('app.bankDetails.DEFAULT');
            }

            $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');
            $fullname = $mainParticipant->first_name . ' ' . $mainParticipant->middle_name . ' ' . $mainParticipant->last_name;
            $invoiceData = [
                "finalEventStartDate" => Carbon::parse($event->event_start_date)->format('d M Y'),
                "finalEventEndDate" => Carbon::parse($event->event_end_date)->format('d M Y'),
                "eventFormattedData" => $eventFormattedData,
                "companyName" => $mainParticipant->company_name,
                "participantName" => $fullname,
                "companyAddress" => $mainParticipant->address,
                "companyCity" => $mainParticipant->city,
                "companyCountry" => $mainParticipant->country,
                "invoiceDate" => Carbon::parse($mainParticipant->registered_date_time)->format('d/m/Y'),
                "invoiceNumber" => $tempInvoiceNumber,
                "bookRefNumber" => $tempBookReference,
                "paymentStatus" => $mainParticipant->payment_status,
                "registrationMethod" => null,
                "eventName" => $event->name,
                "eventLocation" => $event->location,
                "eventVat" => $event->event_vat,
                'vat_price' => $mainParticipant->vat_price,
                'net_amount' => $mainParticipant->net_amount,
                'total_amount' => $mainParticipant->total_amount,
                'unit_price' => $mainParticipant->unit_price,
                'invoiceDetails' => $invoiceDetails,
                'bankDetails' => $bankDetails,
                'finalQuantity' => $countFinalQuantity,
                'total_amount_string' => ucwords($this->numberToWords($mainParticipant->total_amount)),
            ];

            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }



    public function getInvoiceAwards($eventCategory, $eventId, $registrantId)
    {
        if (AwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
            $invoiceDetails = array();
            $countFinalQuantity = 0;

            $mainParticipant = AwardsMainParticipant::where('id', $registrantId)->where('event_id', $eventId)->first();

            $addMainParticipant = true;
            if ($mainParticipant->participant_cancelled) {
                if ($mainParticipant->participant_refunded || $mainParticipant->participant_replaced) {
                    $addMainParticipant = false;
                }
            }

            if ($mainParticipant->participant_replaced_by_id == null & (!$mainParticipant->participant_refunded)) {
                $countFinalQuantity++;
            }

            if ($addMainParticipant) {
                array_push($invoiceDetails, [
                    'delegateDescription' => "Awards Submission fee",
                    'delegateNames' => [
                        "Category: " . $mainParticipant->category,
                    ],
                    'badgeType' => null,
                    'quantity' => 1,
                    'totalDiscount' => 0,
                    'totalNetAmount' =>  $mainParticipant->unit_price,
                    'promoCodeDiscount' => 0,
                ]);
            }


            $subParticipants = AwardsAdditionalParticipant::where('main_participant_id', $registrantId)->get();
            if (!$subParticipants->isEmpty()) {
                foreach ($subParticipants as $subParticipant) {
                    if ($subParticipant->participant_replaced_by_id == null & (!$subParticipant->participant_refunded)) {
                        $countFinalQuantity++;
                    }

                    $addSubParticipant = true;
                    if ($subParticipant->participant_cancelled) {
                        if ($subParticipant->participant_refunded || $subParticipant->participant_replaced) {
                            $addSubParticipant = false;
                        }
                    }

                    if ($addSubParticipant) {
                        $existingIndex = 0;

                        if (count($invoiceDetails) == 0) {
                            array_push($invoiceDetails, [
                                'delegateDescription' => "Awards Submission fee",
                                'delegateNames' => [
                                    "Category: " . $mainParticipant->category,
                                ],
                                'badgeType' => null,
                                'quantity' => 1,
                                'totalDiscount' => 0,
                                'totalNetAmount' =>  $mainParticipant->unit_price,
                                'promoCodeDiscount' => 0,
                            ]);
                        }
                    }
                }
            }

            $transactionId = AwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');

            $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
            $lastDigit = 1000 + intval($transactionId);

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($event->category == $eventCategoryC) {
                    $getEventcode = $code;
                }
            }

            $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
            $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

            $bankDetails = config('app.bankDetails.DEFAULT');

            $eventFormattedData = Carbon::parse($event->event_start_date)->format('j F Y');
            $fullname = $mainParticipant->first_name . ' ' . $mainParticipant->middle_name . ' ' . $mainParticipant->last_name;

            if ($mainParticipant->alternative_company_name == null) {
                $finalCompanyName = $mainParticipant->company_name;
            } else {
                $finalCompanyName = $mainParticipant->alternative_company_name;
            }

            $invoiceData = [
                "finalEventStartDate" => Carbon::parse($event->event_start_date)->format('d M Y'),
                "finalEventEndDate" => Carbon::parse($event->event_end_date)->format('d M Y'),
                "eventFormattedData" => $eventFormattedData,
                "companyName" => $finalCompanyName,
                "participantName" => $fullname,
                "companyAddress" => $mainParticipant->address,
                "companyCity" => $mainParticipant->city,
                "companyCountry" => $mainParticipant->country,
                "invoiceDate" => Carbon::parse($mainParticipant->registered_date_time)->format('d/m/Y'),
                "invoiceNumber" => $tempInvoiceNumber,
                "bookRefNumber" => $tempBookReference,
                "paymentStatus" => $mainParticipant->payment_status,
                "eventName" => $event->name,
                "eventLocation" => $event->location,
                "eventVat" => $event->event_vat,
                'vat_price' => $mainParticipant->vat_price,
                'net_amount' => $mainParticipant->net_amount,
                'total_amount' => $mainParticipant->total_amount,
                'unit_price' => $mainParticipant->unit_price,
                'invoiceDetails' => $invoiceDetails,
                'bankDetails' => $bankDetails,
                'finalQuantity' => $countFinalQuantity,
                'total_amount_string' => ucwords($this->numberToWords($mainParticipant->total_amount)),
                "registrationMethod" => $mainParticipant->registration_method,
            ];

            return $invoiceData;
        } else {
            abort(404, 'The URL is incorrect');
        }
    }



    public function eventRegistrantsExportData($eventCategory, $eventId)
    {
        $finalExcelData = array();
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();

        $mainDelegates = MainDelegate::where('event_id', $eventId)->get();
        if (!$mainDelegates->isEmpty()) {
            foreach ($mainDelegates as $mainDelegate) {
                $mainTransactionId = Transaction::where('delegate_id', $mainDelegate->id)->where('delegate_type', "main")->value('id');

                $tempYear = Carbon::parse($mainDelegate->registered_date_time)->format('y');
                $lastDigit = 1000 + intval($mainTransactionId);

                foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                    if ($event->category == $eventCategoryC) {
                        $getEventcode = $code;
                    }
                }

                $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
                $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

                $promoCodeDiscount = null;

                $unitPrice = $mainDelegate->unit_price;
                $discountPrice = 0.0;
                $netAMount = $unitPrice - $discountPrice;
                $vatPrice = $netAMount * ($event->event_vat / 100);
                $totalAmount = $netAMount + $vatPrice;
                $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainDelegate->pcode_used)->first();

                if ($promoCode  != null) {
                    $promoCodeDiscount = $promoCode->discount;
                    $discountType = $promoCode->discount_type;

                    if ($discountType == "percentage") {
                        $unitPrice = $mainDelegate->unit_price;
                        $discountPrice = $unitPrice * ($promoCodeDiscount / 100);
                        $netAMount = $unitPrice - $discountPrice;
                        $vatPrice = $netAMount * ($event->event_vat / 100);
                        $totalAmount = $netAMount + $vatPrice;
                    } else if ($discountType == "price") {
                        $unitPrice = $mainDelegate->unit_price;
                        $discountPrice = $promoCodeDiscount;
                        $netAMount = $unitPrice - $discountPrice;
                        $vatPrice = $netAMount * ($event->event_vat / 100);
                        $totalAmount = $netAMount + $vatPrice;
                    } else {
                        $unitPrice = $promoCode->new_rate;
                        $discountPrice = 0.0;
                        $netAMount = $unitPrice - $discountPrice;
                        $vatPrice = $netAMount * ($event->event_vat / 100);
                        $totalAmount = $netAMount + $vatPrice;
                    }
                } else {
                    $unitPrice = $mainDelegate->unit_price;
                    $discountPrice = 0.0;
                    $netAMount = $unitPrice - $discountPrice;
                    $vatPrice = $netAMount * ($event->event_vat / 100);
                    $totalAmount = $netAMount + $vatPrice;
                }

                $printedBadgeCount = 0;
                $printedBadgeDateTime = null;

                $printedBadges = PrintedBadge::where('event_id', $eventId)->where('delegate_id', $mainDelegate->id)->where('delegate_type', 'main')->get();

                if ($printedBadges->isNotEmpty()) {
                    foreach ($printedBadges as $printedBadge) {
                        $printedBadgeCount++;
                        $printedBadgeDateTime = $printedBadge->printed_date_time;
                    }
                }

                $scannedBadgeCount = 0;
                $scannedBadgeDateTime = null;

                $scannedBadges = ScannedDelegate::where('event_id', $eventId)->where('delegate_id', $mainDelegate->id)->where('delegate_type', 'main')->get();

                if ($scannedBadges->isNotEmpty()) {
                    foreach ($scannedBadges as $scannedBadge) {
                        $scannedBadgeCount++;
                        $scannedBadgeDateTime = $scannedBadge->scanned_date_time;
                    }
                }

                if ($mainDelegate->delegate_cancelled != null) {
                }

                if ($mainDelegate->alternative_company_name != null) {
                    $finalCompanyName = $mainDelegate->alternative_company_name;
                } else {
                    $finalCompanyName = $mainDelegate->company_name;
                }

                array_push($finalExcelData, [
                    'transaction_id' => $tempBookReference,
                    'id' => $mainDelegate->id,
                    'delegateType' => 'Main',
                    'event' => $eventCategory,
                    'access_type' => $mainDelegate->access_type,
                    'pass_type' => $mainDelegate->pass_type,
                    'rate_type' => ($netAMount == 0) ? 'Complementary' : $mainDelegate->rate_type,

                    'company_name' => $finalCompanyName,
                    'company_sector' => $mainDelegate->company_sector,
                    'company_address' => $mainDelegate->company_address,
                    'company_city' => $mainDelegate->company_city,
                    'company_country' => $mainDelegate->company_country,
                    'company_telephone_number' => $mainDelegate->company_telephone_number,
                    'company_mobile_number' => $mainDelegate->company_mobile_number,
                    'assistant_email_address' => $mainDelegate->assistant_email_address,

                    'salutation' => $mainDelegate->salutation,
                    'first_name' => $mainDelegate->first_name,
                    'middle_name' => $mainDelegate->middle_name,
                    'last_name' => $mainDelegate->last_name,
                    'email_address' => $mainDelegate->email_address,
                    'mobile_number' => $mainDelegate->mobile_number,
                    'job_title' => $mainDelegate->job_title,
                    'nationality' => $mainDelegate->nationality,
                    'badge_type' => $mainDelegate->badge_type,
                    'pcode_used' => $mainDelegate->pcode_used,
                    'country' => $mainDelegate->country,

                    'interests' => $mainDelegate->interests,

                    'heard_where' => $mainDelegate->heard_where,

                    'seat_number' => $mainDelegate->seat_number,

                    'attending_plenary' => $mainDelegate->attending_plenary,
                    'attending_symposium' => $mainDelegate->attending_symposium,
                    'attending_solxchange' => $mainDelegate->attending_solxchange,
                    'attending_yf' => $mainDelegate->attending_yf,
                    'attending_networking_dinner' => $mainDelegate->attending_networking_dinner,
                    'attending_welcome_dinner' => $mainDelegate->attending_welcome_dinner,
                    'attending_gala_dinner' => $mainDelegate->attending_gala_dinner,
                    'attending_sustainability' => $mainDelegate->attending_sustainability,

                    'receive_whatsapp_notifications' => $mainDelegate->receive_whatsapp_notifications,

                    'car_park_needed' => $mainDelegate->car_park_needed,

                    'optional_interests' => $mainDelegate->optional_interests,

                    'unit_price' => $unitPrice,
                    'discount_price' => $discountPrice,
                    'net_amount' => $netAMount,
                    'vat_price' => $vatPrice,
                    'total_amount' => $totalAmount,
                    'printed_badge_count' => $printedBadgeCount,
                    'printed_badge_date_time' => $printedBadgeDateTime,

                    'scanned_badge_count' => $scannedBadgeCount,
                    'scanned_badge_date_time' => $scannedBadgeDateTime,

                    // PLEASE CONTINUE HERE
                    'payment_status' => $mainDelegate->delegate_refunded ? 'refunded' : $mainDelegate->payment_status,
                    'registration_status' => $mainDelegate->delegate_cancelled ? 'cancelled' : $mainDelegate->registration_status,
                    'mode_of_payment' => $mainDelegate->mode_of_payment,
                    'invoice_number' => $tempInvoiceNumber,
                    'reference_number' => $tempBookReference,
                    'registration_date_time' => $mainDelegate->registered_date_time,
                    'paid_date_time' => $mainDelegate->paid_date_time,

                    // NEW june 6 2023
                    'registration_method' => $mainDelegate->registration_method,
                    'transaction_remarks' => $mainDelegate->transaction_remarks,

                    // NEW may 29 2024
                    'registration_confirmation_sent_count' => $mainDelegate->registration_confirmation_sent_count,
                    'registration_confirmation_sent_datetime' => $mainDelegate->registration_confirmation_sent_datetime,

                    'delegate_cancelled' => $mainDelegate->delegate_cancelled,
                    'delegate_replaced' => $mainDelegate->delegate_replaced,
                    'delegate_refunded' => $mainDelegate->delegate_refunded,

                    'delegate_replaced_type' => null,
                    'delegate_original_from_id' => null,
                    'delegate_replaced_from_id' => null,
                    'delegate_replaced_by_id' => $mainDelegate->delegate_replaced_by_id,

                    'delegate_cancelled_datetime' => $mainDelegate->delegate_cancelled_datetime,
                    'delegate_refunded_datetime' => $mainDelegate->delegate_refunded_datetime,
                    'delegate_replaced_datetime' => $mainDelegate->delegate_replaced_datetime,
                ]);

                $subDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegate->id)->get();

                if (!$subDelegates->isEmpty()) {
                    foreach ($subDelegates as $subDelegate) {
                        $subTransactionId = Transaction::where('delegate_id', $subDelegate->id)->where('delegate_type', "sub")->value('id');

                        $promoCodeDiscount = null;

                        $unitPrice = $mainDelegate->unit_price;
                        $discountPrice = 0.0;
                        $netAMount = $unitPrice - $discountPrice;
                        $vatPrice = $netAMount * ($event->event_vat / 100);
                        $totalAmount = $netAMount + $vatPrice;

                        $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subDelegate->pcode_used)->first();


                        if ($subPromoCode  != null) {
                            $promoCodeDiscount = $subPromoCode->discount;
                            $discountType = $subPromoCode->discount_type;

                            if ($discountType == "percentage") {
                                $unitPrice = $mainDelegate->unit_price;
                                $discountPrice = $unitPrice * ($promoCodeDiscount / 100);
                                $netAMount = $unitPrice - $discountPrice;
                                $vatPrice = $netAMount * ($event->event_vat / 100);
                                $totalAmount = $netAMount + $vatPrice;
                            } else if ($discountType == "price") {
                                $unitPrice = $mainDelegate->unit_price;
                                $discountPrice = $promoCodeDiscount;
                                $netAMount = $unitPrice - $discountPrice;
                                $vatPrice = $netAMount * ($event->event_vat / 100);
                                $totalAmount = $netAMount + $vatPrice;
                            } else {
                                $unitPrice = $subPromoCode->new_rate;
                                $discountPrice = 0.0;
                                $netAMount = $unitPrice - $discountPrice;
                                $vatPrice = $netAMount * ($event->event_vat / 100);
                                $totalAmount = $netAMount + $vatPrice;
                            }
                        } else {
                            $unitPrice = $mainDelegate->unit_price;
                            $discountPrice = 0.0;
                            $netAMount = $unitPrice - $discountPrice;
                            $vatPrice = $netAMount * ($event->event_vat / 100);
                            $totalAmount = $netAMount + $vatPrice;
                        }

                        $lastDigit = 1000 + intval($subTransactionId);
                        $tempBookReferenceSub = "$event->year" . "$getEventcode" . "$lastDigit";

                        $printedBadgeCount = 0;
                        $printedBadgeDateTime = null;

                        $printedBadges = PrintedBadge::where('event_id', $eventId)->where('delegate_id', $subDelegate->id)->where('delegate_type', 'sub')->get();

                        if ($printedBadges->isNotEmpty()) {
                            foreach ($printedBadges as $printedBadge) {
                                $printedBadgeCount++;
                                $printedBadgeDateTime = $printedBadge->printed_date_time;
                            }
                        }


                        $scannedBadgeCount = 0;
                        $scannedBadgeDateTime = null;

                        $scannedBadges = ScannedDelegate::where('event_id', $eventId)->where('delegate_id', $subDelegate->id)->where('delegate_type', 'sub')->get();

                        if ($scannedBadges->isNotEmpty()) {
                            foreach ($scannedBadges as $scannedBadge) {
                                $scannedBadgeCount++;
                                $scannedBadgeDateTime = $scannedBadge->scanned_date_time;
                            }
                        }

                        array_push($finalExcelData, [
                            'transaction_id' => $tempBookReferenceSub,
                            'id' => $subDelegate->id,
                            'delegateType' => 'Sub',
                            'event' => $eventCategory,
                            'access_type' => $mainDelegate->access_type,
                            'pass_type' => $mainDelegate->pass_type,
                            'rate_type' => $mainDelegate->rate_type,

                            'company_name' => $finalCompanyName,
                            'company_sector' => $mainDelegate->company_sector,
                            'company_address' => $mainDelegate->company_address,
                            'company_city' => $mainDelegate->company_city,
                            'company_country' => $mainDelegate->company_country,
                            'company_telephone_number' => $mainDelegate->company_telephone_number,
                            'company_mobile_number' => $mainDelegate->company_mobile_number,
                            'assistant_email_address' => $mainDelegate->assistant_email_address,

                            'salutation' => $subDelegate->salutation,
                            'first_name' => $subDelegate->first_name,
                            'middle_name' => $subDelegate->middle_name,
                            'last_name' => $subDelegate->last_name,
                            'email_address' => $subDelegate->email_address,
                            'mobile_number' => $subDelegate->mobile_number,
                            'job_title' => $subDelegate->job_title,
                            'nationality' => $subDelegate->nationality,
                            'badge_type' => $subDelegate->badge_type,
                            'pcode_used' => $subDelegate->pcode_used,
                            'country' => $subDelegate->country,

                            'heard_where' => $mainDelegate->heard_where,

                            'interests' => $subDelegate->interests,

                            'seat_number' => $subDelegate->seat_number,

                            'attending_plenary' => $mainDelegate->attending_plenary,
                            'attending_symposium' => $mainDelegate->attending_symposium,
                            'attending_solxchange' => $mainDelegate->attending_solxchange,
                            'attending_yf' => $mainDelegate->attending_yf,
                            'attending_networking_dinner' => $mainDelegate->attending_networking_dinner,
                            'attending_welcome_dinner' => $mainDelegate->attending_welcome_dinner,
                            'attending_gala_dinner' => $mainDelegate->attending_gala_dinner,
                            'attending_sustainability' => $mainDelegate->attending_sustainability,

                            'receive_whatsapp_notifications' => $mainDelegate->receive_whatsapp_notifications,

                            'car_park_needed' => $mainDelegate->car_park_needed,

                            'optional_interests' => $mainDelegate->optional_interests,

                            'unit_price' => $unitPrice,
                            'discount_price' => $discountPrice,
                            'net_amount' => $netAMount,
                            'vat_price' => $vatPrice,
                            'total_amount' => $totalAmount,

                            'printed_badge_count' => $printedBadgeCount,
                            'printed_badge_date_time' => $printedBadgeDateTime,

                            'scanned_badge_count' => $scannedBadgeCount,
                            'scanned_badge_date_time' => $scannedBadgeDateTime,

                            // PLEASE CONTINUE HERE
                            'payment_status' => $subDelegate->delegate_refunded ? 'refunded' : $mainDelegate->payment_status,
                            'registration_status' => $subDelegate->delegate_cancelled ? 'cancelled' : $mainDelegate->registration_status,
                            'mode_of_payment' => $mainDelegate->mode_of_payment,
                            'invoice_number' => $tempInvoiceNumber,
                            'reference_number' => $tempBookReference,
                            'registration_date_time' => $mainDelegate->registered_date_time,
                            'paid_date_time' => $mainDelegate->paid_date_time,

                            // NEW june 6 2023
                            'registration_method' => $mainDelegate->registration_method,
                            'transaction_remarks' => $mainDelegate->transaction_remarks,

                            // NEW may 29 2024
                            'registration_confirmation_sent_count' => $mainDelegate->registration_confirmation_sent_count,
                            'registration_confirmation_sent_datetime' => $mainDelegate->registration_confirmation_sent_datetime,

                            'delegate_cancelled' => $subDelegate->delegate_cancelled,
                            'delegate_replaced' => $subDelegate->delegate_replaced,
                            'delegate_refunded' => $subDelegate->delegate_refunded,

                            'delegate_replaced_type' => $subDelegate->delegate_replaced_type,
                            'delegate_original_from_id' => $subDelegate->delegate_original_from_id,
                            'delegate_replaced_from_id' => $subDelegate->delegate_replaced_from_id,
                            'delegate_replaced_by_id' => $subDelegate->delegate_replaced_by_id,

                            'delegate_cancelled_datetime' => $subDelegate->delegate_cancelled_datetime,
                            'delegate_refunded_datetime' => $subDelegate->delegate_refunded_datetime,
                            'delegate_replaced_datetime' => $subDelegate->delegate_replaced_datetime,
                        ]);
                    }
                }
            }
        }
        $currentDate = Carbon::now()->format('Y-m-d');
        $fileName = $eventCategory . ' ' . $event->year . ' Transactions ' . '[' . $currentDate . '].csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array(
            'Transaction Id',
            'ID',
            'Delegate Type',
            'Event',
            'Access Type',
            'Pass Type',
            'Rate Type',

            'Promo Code used',
            'Badge Type',
            'Salutation',
            'First Name',
            'Last Name',
            'Email Address',
            'Mobile Number',
            'Country',
            'Job Title',
            'Nationality',

            'Company Name',
            'Company Address',
            'Company City',
            'Company Country',
            'Telephone Number',
            'Company Mobile Number',
            'Assistant Email Address',

            'Middle Name',

            'Unit Price',
            'Discount Price',
            'Net Amount',
            'Vat Price',
            'Total Amount',
            'Payment Status',
            'Registration Status',
            'Payment method',
            'Invoice Number',
            'Reference Number',
            'Registered Date & Time',
            'Paid Date & Time',
            'Printed badge count',
            'Printed badge date time',

            'Scanned badge count',
            'Scanned badge date time',

            'Company Sector',

            'Registration Method',
            'Transaction Remarks',

            'Registration Confirmation Sent Count',
            'Registration Confirmation Last Sent Date Time',

            'Delegate Cancelled',
            'Delegate Replaced',
            'Delegate Refunded',

            'Delegate Replaced Type',
            'Delegate Original From Id',
            'Delegate Replaced From Id',
            'Delegate Replaced By Id',

            'Delegate Cancelled Date & Time',
            'Delegate Refunded Date & Time',
            'Delegate Replaced Date & Time',

            'Heard Where',

            'Interests',

            'Seat Number',

            'Attending to Plenary',
            'Attending to Symposium',
            'Attending to Solutions XChange',
            'Attending to Youth Forum',
            'Attending to Networking Dinner',
            'Attending to Welcome Dinner',
            'Attending to Gala Dinner',
            'Attending to Sustainability Pavilion',

            'Would like to receive WhatsApp notifications',

            'Car Park Needed',

            'Optional Interests'
        );

        $callback = function () use ($finalExcelData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($finalExcelData as $data) {
                fputcsv(
                    $file,
                    array(
                        $data['transaction_id'],
                        $data['id'],
                        $data['delegateType'],
                        $data['event'],
                        $data['access_type'],
                        $data['pass_type'],
                        $data['rate_type'],

                        $data['pcode_used'],
                        $data['badge_type'],

                        $data['salutation'],
                        $data['first_name'],
                        $data['last_name'],
                        $data['email_address'],
                        "\t" . $data['mobile_number'],
                        $data['country'],
                        $data['job_title'],
                        $data['nationality'],

                        $data['company_name'],
                        $data['company_address'],
                        $data['company_city'],
                        $data['company_country'],
                        "\t" . $data['company_telephone_number'],
                        "\t" . $data['company_mobile_number'],
                        $data['assistant_email_address'],

                        $data['middle_name'],

                        $data['unit_price'],
                        $data['discount_price'],
                        $data['net_amount'],
                        $data['vat_price'],
                        $data['total_amount'],
                        $data['payment_status'],
                        $data['registration_status'],
                        $data['mode_of_payment'],
                        $data['invoice_number'],
                        $data['reference_number'],
                        $data['registration_date_time'],
                        $data['paid_date_time'],
                        $data['printed_badge_count'],
                        $data['printed_badge_date_time'],

                        $data['scanned_badge_count'],
                        $data['scanned_badge_date_time'],

                        $data['company_sector'],

                        $data['registration_method'],
                        $data['transaction_remarks'],

                        $data['registration_confirmation_sent_count'],
                        $data['registration_confirmation_sent_datetime'],

                        $data['delegate_cancelled'],
                        $data['delegate_replaced'],
                        $data['delegate_refunded'],

                        $data['delegate_replaced_type'],
                        $data['delegate_original_from_id'],
                        $data['delegate_replaced_from_id'],
                        $data['delegate_replaced_by_id'],

                        $data['delegate_cancelled_datetime'],
                        $data['delegate_refunded_datetime'],
                        $data['delegate_replaced_datetime'],

                        $data['heard_where'],

                        $data['interests'],

                        $data['seat_number'],

                        $data['attending_plenary'],
                        $data['attending_symposium'],
                        $data['attending_solxchange'],
                        $data['attending_yf'],
                        $data['attending_networking_dinner'],
                        $data['attending_welcome_dinner'],
                        $data['attending_gala_dinner'],
                        $data['attending_sustainability'],

                        $data['receive_whatsapp_notifications'],

                        $data['car_park_needed'],

                        $data['optional_interests'],
                    )
                );
            }
            fclose($file);
        };
        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }

    public function spouseRegistrantsExportData($eventCategory, $eventId)
    {
        $finalExcelData = array();
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();

        $mainSpouses = MainSpouse::where('event_id', $eventId)->get();
        if (!$mainSpouses->isEmpty()) {
            foreach ($mainSpouses as $mainSpouse) {
                $mainTransactionId = SpouseTransaction::where('spouse_id', $mainSpouse->id)->where('spouse_type', "main")->value('id');

                $tempYear = Carbon::parse($mainSpouse->registered_date_time)->format('y');
                $lastDigit = 1000 + intval($mainTransactionId);

                foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                    if ($event->category == $eventCategoryC) {
                        $getEventcode = $code;
                    }
                }

                $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
                $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

                $discountPrice = 0.0;
                $netAMount = $mainSpouse->unit_price;

                array_push($finalExcelData, [
                    'transaction_id' => $tempBookReference,
                    'id' => $mainSpouse->id,
                    'spouseType' => 'Main',
                    'event' => $eventCategory,
                    'pass_type' => $mainSpouse->pass_type,
                    'rate_type' => $mainSpouse->rate_type,

                    'day_one' => $mainSpouse->day_one,
                    'day_two' => $mainSpouse->day_two,
                    'day_three' => $mainSpouse->day_three,
                    'day_four' => $mainSpouse->day_four,

                    'reference_delegate_name' => $mainSpouse->reference_delegate_name,

                    'salutation' => $mainSpouse->salutation,
                    'first_name' => $mainSpouse->first_name,
                    'middle_name' => $mainSpouse->middle_name,
                    'last_name' => $mainSpouse->last_name,
                    'email_address' => $mainSpouse->email_address,
                    'mobile_number' => $mainSpouse->mobile_number,
                    'nationality' => $mainSpouse->nationality,
                    'country' => $mainSpouse->country,
                    'city' => $mainSpouse->city,

                    'unit_price' => $mainSpouse->unit_price,
                    'discount_price' => $discountPrice,
                    'net_amount' => $netAMount,
                    'printed_badge_date' => null,

                    // PLEASE CONTINUE HERE
                    'total_amount' => $mainSpouse->total_amount,
                    'payment_status' => $mainSpouse->spouse_refunded ? 'refunded' : $mainSpouse->payment_status,
                    'registration_status' => $mainSpouse->spouse_cancelled ? 'cancelled' : $mainSpouse->registration_status,
                    'mode_of_payment' => $mainSpouse->mode_of_payment,
                    'invoice_number' => $tempInvoiceNumber,
                    'reference_number' => $tempBookReference,
                    'registration_date_time' => $mainSpouse->registered_date_time,
                    'paid_date_time' => $mainSpouse->paid_date_time,

                    // NEW june 6 2023
                    'registration_method' => $mainSpouse->registration_method,
                    'transaction_remarks' => $mainSpouse->transaction_remarks,

                    'spouse_cancelled' => $mainSpouse->spouse_cancelled,
                    'spouse_replaced' => $mainSpouse->spouse_replaced,
                    'spouse_refunded' => $mainSpouse->spouse_refunded,

                    'spouse_replaced_type' => null,
                    'spouse_original_from_id' => null,
                    'spouse_replaced_from_id' => null,
                    'spouse_replaced_by_id' => $mainSpouse->spouse_replaced_by_id,

                    'spouse_cancelled_datetime' => $mainSpouse->spouse_cancelled_datetime,
                    'spouse_refunded_datetime' => $mainSpouse->spouse_refunded_datetime,
                    'spouse_replaced_datetime' => $mainSpouse->spouse_replaced_datetime,
                ]);

                $subSpouses = AdditionalSpouse::where('main_spouse_id', $mainSpouse->id)->get();

                if (!$subSpouses->isEmpty()) {
                    foreach ($subSpouses as $subSpouse) {
                        $subTransactionId = SpouseTransaction::where('spouse_id', $subSpouse->id)->where('spouse_type', "sub")->value('id');

                        $discountPrice = 0.0;
                        $netAMount = $mainSpouse->unit_price;

                        $lastDigit = 1000 + intval($subTransactionId);
                        $tempBookReferenceSub = "$event->year" . "$getEventcode" . "$lastDigit";

                        array_push($finalExcelData, [
                            'transaction_id' => $tempBookReferenceSub,
                            'id' => $subSpouse->id,
                            'spouseType' => 'Sub',
                            'event' => $eventCategory,
                            'pass_type' => $mainSpouse->pass_type,
                            'rate_type' => $mainSpouse->rate_type,

                            'day_one' => $mainSpouse->day_one,
                            'day_two' => $mainSpouse->day_two,
                            'day_three' => $mainSpouse->day_three,
                            'day_four' => $mainSpouse->day_four,

                            'reference_delegate_name' => $mainSpouse->reference_delegate_name,

                            'salutation' => $subSpouse->salutation,
                            'first_name' => $subSpouse->first_name,
                            'middle_name' => $subSpouse->middle_name,
                            'last_name' => $subSpouse->last_name,
                            'email_address' => $subSpouse->email_address,
                            'mobile_number' => $subSpouse->mobile_number,
                            'nationality' => $subSpouse->nationality,
                            'country' => $subSpouse->country,
                            'city' => $subSpouse->city,

                            'unit_price' => $mainSpouse->unit_price,
                            'discount_price' => $discountPrice,
                            'net_amount' => $netAMount,
                            'printed_badge_date' => null,

                            // PLEASE CONTINUE HERE
                            'total_amount' => $mainSpouse->total_amount,
                            'payment_status' => $subSpouse->spouse_refunded ? 'refunded' : $mainSpouse->payment_status,
                            'registration_status' => $subSpouse->spouse_cancelled ? 'cancelled' : $mainSpouse->registration_status,
                            'mode_of_payment' => $mainSpouse->mode_of_payment,
                            'invoice_number' => $tempInvoiceNumber,
                            'reference_number' => $tempBookReference,
                            'registration_date_time' => $mainSpouse->registered_date_time,
                            'paid_date_time' => $mainSpouse->paid_date_time,

                            // NEW june 6 2023
                            'registration_method' => $mainSpouse->registration_method,
                            'transaction_remarks' => $mainSpouse->transaction_remarks,

                            'spouse_cancelled' => $subSpouse->spouse_cancelled,
                            'spouse_replaced' => $subSpouse->spouse_replaced,
                            'spouse_refunded' => $subSpouse->spouse_refunded,

                            'spouse_replaced_type' => $subSpouse->spouse_replaced_type,
                            'spouse_original_from_id' => $subSpouse->spouse_original_from_id,
                            'spouse_replaced_from_id' => $subSpouse->spouse_replaced_from_id,
                            'spouse_replaced_by_id' => $subSpouse->spouse_replaced_by_id,

                            'spouse_cancelled_datetime' => $subSpouse->spouse_cancelled_datetime,
                            'spouse_refunded_datetime' => $subSpouse->spouse_refunded_datetime,
                            'spouse_replaced_datetime' => $subSpouse->spouse_replaced_datetime,
                        ]);
                    }
                }
            }
        }

        $currentDate = Carbon::now()->format('Y-m-d');
        $fileName = $eventCategory . ' ' . $event->year . ' Transactions ' . '[' . $currentDate . '].csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'Transaction Id',
            'ID',
            'Spouse Type',
            'Event',
            'Rate Type',

            'Day one',
            'Day two',
            'Day three',
            'Day four',

            'Reference Delegate Full Name',

            'Salutation',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email Address',
            'Mobile Number',
            'Nationality',
            'Country',
            'City',

            'Unit Price',
            'Discount Price',
            'Total Amount',
            'Payment Status',
            'Registration Status',
            'Payment method',
            'Invoice Number',
            'Reference Number',
            'Registered Date & Time',
            'Paid Date & Time',
            'Printed badge',

            'Registration Method',
            'Transaction Remarks',

            'Spouse Cancelled',
            'Spouse Replaced',
            'Spouse Refunded',

            'Spouse Replaced Type',
            'Spouse Original From Id',
            'Spouse Replaced From Id',
            'Spouse Replaced By Id',

            'Spouse Cancelled Date & Time',
            'Spouse Refunded Date & Time',
            'Spouse Replaced Date & Time',

        );

        $callback = function () use ($finalExcelData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($finalExcelData as $data) {
                fputcsv(
                    $file,
                    array(
                        $data['transaction_id'],
                        $data['id'],
                        $data['spouseType'],
                        $data['event'],
                        $data['rate_type'],

                        $data['day_one'],
                        $data['day_two'],
                        $data['day_three'],
                        $data['day_four'],

                        $data['reference_delegate_name'],

                        $data['salutation'],
                        $data['first_name'],
                        $data['middle_name'],
                        $data['last_name'],
                        $data['email_address'],
                        $data['mobile_number'],
                        $data['nationality'],
                        $data['country'],
                        $data['city'],

                        $data['unit_price'],
                        $data['discount_price'],
                        $data['net_amount'],
                        $data['payment_status'],
                        $data['registration_status'],
                        $data['mode_of_payment'],
                        $data['invoice_number'],
                        $data['reference_number'],
                        $data['registration_date_time'],
                        $data['paid_date_time'],
                        $data['printed_badge_date'],

                        $data['registration_method'],
                        $data['transaction_remarks'],

                        $data['spouse_cancelled'],
                        $data['spouse_replaced'],
                        $data['spouse_refunded'],

                        $data['spouse_replaced_type'],
                        $data['spouse_original_from_id'],
                        $data['spouse_replaced_from_id'],
                        $data['spouse_replaced_by_id'],

                        $data['spouse_cancelled_datetime'],
                        $data['spouse_refunded_datetime'],
                        $data['spouse_replaced_datetime'],

                    )
                );
            }
            fclose($file);
        };
        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }

    public function visitorRegistrantsExportData($eventCategory, $eventId)
    {
        $finalExcelData = array();
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();

        $mainVisitors = MainVisitor::where('event_id', $eventId)->get();
        if (!$mainVisitors->isEmpty()) {
            foreach ($mainVisitors as $mainVisitor) {
                $mainTransactionId = VisitorTransaction::where('visitor_id', $mainVisitor->id)->where('visitor_type', "main")->value('id');

                $tempYear = Carbon::parse($mainVisitor->registered_date_time)->format('y');
                $lastDigit = 1000 + intval($mainTransactionId);

                foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                    if ($event->category == $eventCategoryC) {
                        $getEventcode = $code;
                    }
                }

                $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
                $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

                $promoCodeDiscount = null;
                $discountPrice = 0.0;
                $netAMount = 0.0;

                $promoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $mainVisitor->pcode_used)->first();

                if ($promoCode  != null) {
                    $promoCodeDiscount = $promoCode->discount;
                    $discountType = $promoCode->discount_type;

                    if ($discountType == "percentage") {
                        $discountPrice = $mainVisitor->unit_price * ($promoCodeDiscount / 100);
                        $netAMount = $mainVisitor->unit_price - $discountPrice;
                    } else {
                        $discountPrice = $promoCodeDiscount;
                        $netAMount = $mainVisitor->unit_price - $discountPrice;
                    }
                } else {
                    $discountPrice = 0.0;
                    $netAMount = $mainVisitor->unit_price;
                }

                $printedBadgeCount = 0;
                $printedBadgeDateTime = null;

                $printedBadges = VisitorPrintedBadge::where('event_id', $eventId)->where('visitor_id', $mainVisitor->id)->where('visitor_type', 'main')->get();

                if ($printedBadges->isNotEmpty()) {
                    foreach ($printedBadges as $printedBadge) {
                        $printedBadgeCount++;
                        $printedBadgeDateTime = $printedBadge->printed_date_time;
                    }
                }

                $scannedBadgeCount = 0;
                $scannedBadgeDateTime = null;

                $scannedBadges = ScannedVisitor::where('event_id', $eventId)->where('visitor_id', $mainVisitor->id)->where('visitor_type', 'main')->get();

                if ($scannedBadges->isNotEmpty()) {
                    foreach ($scannedBadges as $scannedBadge) {
                        $scannedBadgeCount++;
                        $scannedBadgeDateTime = $scannedBadge->scanned_date_time;
                    }
                }

                array_push($finalExcelData, [
                    'transaction_id' => $tempBookReference,
                    'id' => $mainVisitor->id,
                    'visitorType' => 'Main',
                    'event' => $eventCategory,
                    'pass_type' => $mainVisitor->pass_type,
                    'rate_type' => ($netAMount == 0) ? 'Complementary' : $mainVisitor->rate_type,

                    'company_name' => $mainVisitor->company_name,
                    'company_sector' => $mainVisitor->company_sector,
                    'company_address' => $mainVisitor->company_address,
                    'company_city' => $mainVisitor->company_city,
                    'company_country' => $mainVisitor->company_country,
                    'company_telephone_number' => $mainVisitor->company_telephone_number,
                    'company_mobile_number' => $mainVisitor->company_mobile_number,
                    'assistant_email_address' => $mainVisitor->assistant_email_address,

                    'salutation' => $mainVisitor->salutation,
                    'first_name' => $mainVisitor->first_name,
                    'middle_name' => $mainVisitor->middle_name,
                    'last_name' => $mainVisitor->last_name,
                    'email_address' => $mainVisitor->email_address,
                    'mobile_number' => $mainVisitor->mobile_number,
                    'job_title' => $mainVisitor->job_title,
                    'nationality' => $mainVisitor->nationality,
                    'badge_type' => $mainVisitor->badge_type,
                    'pcode_used' => $mainVisitor->pcode_used,

                    'heard_where' => $mainVisitor->heard_where,

                    'unit_price' => $mainVisitor->unit_price,
                    'discount_price' => $discountPrice,
                    'net_amount' => $netAMount,
                    'printed_badge_count' => $printedBadgeCount,
                    'printed_badge_date_time' => $printedBadgeDateTime,

                    'scanned_badge_count' => $scannedBadgeCount,
                    'scanned_badge_date_time' => $scannedBadgeDateTime,

                    // PLEASE CONTINUE HERE
                    'total_amount' => $mainVisitor->total_amount,
                    'payment_status' => $mainVisitor->visitor_refunded ? 'refunded' : $mainVisitor->payment_status,
                    'registration_status' => $mainVisitor->visitor_cancelled ? 'cancelled' : $mainVisitor->registration_status,
                    'mode_of_payment' => $mainVisitor->mode_of_payment,
                    'invoice_number' => $tempInvoiceNumber,
                    'reference_number' => $tempBookReference,
                    'registration_date_time' => $mainVisitor->registered_date_time,
                    'paid_date_time' => $mainVisitor->paid_date_time,

                    // NEW june 6 2023
                    'registration_method' => $mainVisitor->registration_method,
                    'transaction_remarks' => $mainVisitor->transaction_remarks,

                    'visitor_cancelled' => $mainVisitor->visitor_cancelled,
                    'visitor_replaced' => $mainVisitor->visitor_replaced,
                    'visitor_refunded' => $mainVisitor->visitor_refunded,

                    'visitor_replaced_type' => null,
                    'visitor_original_from_id' => null,
                    'visitor_replaced_from_id' => null,
                    'visitor_replaced_by_id' => $mainVisitor->visitor_replaced_by_id,

                    'visitor_cancelled_datetime' => $mainVisitor->visitor_cancelled_datetime,
                    'visitor_refunded_datetime' => $mainVisitor->visitor_refunded_datetime,
                    'visitor_replaced_datetime' => $mainVisitor->visitor_replaced_datetime,
                ]);

                $subVisitors = AdditionalVisitor::where('main_visitor_id', $mainVisitor->id)->get();

                if (!$subVisitors->isEmpty()) {
                    foreach ($subVisitors as $subVisitor) {
                        $subTransactionId = VisitorTransaction::where('visitor_id', $subVisitor->id)->where('visitor_type', "sub")->value('id');

                        $promoCodeDiscount = null;
                        $discountPrice = 0.0;
                        $netAMount = 0.0;

                        $subPromoCode = PromoCode::where('event_id', $eventId)->where('event_category', $eventCategory)->where('promo_code', $subVisitor->pcode_used)->first();

                        if ($subPromoCode  != null) {
                            $promoCodeDiscount = $subPromoCode->discount;
                            $discountType = $subPromoCode->discount_type;

                            if ($discountType == "percentage") {
                                $discountPrice = $mainVisitor->unit_price * ($promoCodeDiscount / 100);
                                $netAMount = $mainVisitor->unit_price - $discountPrice;
                            } else {
                                $discountPrice = $promoCodeDiscount;
                                $netAMount = $mainVisitor->unit_price - $discountPrice;
                            }
                        } else {
                            $discountPrice = 0.0;
                            $netAMount = $mainVisitor->unit_price;
                        }

                        $lastDigit = 1000 + intval($subTransactionId);
                        $tempBookReferenceSub = "$event->year" . "$getEventcode" . "$lastDigit";

                        $printedBadgeCount = 0;
                        $printedBadgeDateTime = null;

                        $printedBadges = VisitorPrintedBadge::where('event_id', $eventId)->where('visitor_id', $subVisitor->id)->where('visitor_type', 'sub')->get();

                        if ($printedBadges->isNotEmpty()) {
                            foreach ($printedBadges as $printedBadge) {
                                $printedBadgeCount++;
                                $printedBadgeDateTime = $printedBadge->printed_date_time;
                            }
                        }

                        $scannedBadgeCount = 0;
                        $scannedBadgeDateTime = null;

                        $scannedBadges = ScannedVisitor::where('event_id', $eventId)->where('visitor_id', $subVisitor->id)->where('visitor_type', 'sub')->get();

                        if ($scannedBadges->isNotEmpty()) {
                            foreach ($scannedBadges as $scannedBadge) {
                                $scannedBadgeCount++;
                                $scannedBadgeDateTime = $scannedBadge->scanned_date_time;
                            }
                        }

                        array_push($finalExcelData, [
                            'transaction_id' => $tempBookReferenceSub,
                            'id' => $subVisitor->id,
                            'visitorType' => 'Sub',
                            'event' => $eventCategory,
                            'pass_type' => $mainVisitor->pass_type,
                            'rate_type' => $mainVisitor->rate_type,

                            'company_name' => $mainVisitor->company_name,
                            'company_sector' => $mainVisitor->company_sector,
                            'company_address' => $mainVisitor->company_address,
                            'company_city' => $mainVisitor->company_city,
                            'company_country' => $mainVisitor->company_country,
                            'company_telephone_number' => $mainVisitor->company_telephone_number,
                            'company_mobile_number' => $mainVisitor->company_mobile_number,
                            'assistant_email_address' => $mainVisitor->assistant_email_address,

                            'salutation' => $subVisitor->salutation,
                            'first_name' => $subVisitor->first_name,
                            'middle_name' => $subVisitor->middle_name,
                            'last_name' => $subVisitor->last_name,
                            'email_address' => $subVisitor->email_address,
                            'mobile_number' => $subVisitor->mobile_number,
                            'job_title' => $subVisitor->job_title,
                            'nationality' => $subVisitor->nationality,
                            'badge_type' => $subVisitor->badge_type,
                            'pcode_used' => $subVisitor->pcode_used,

                            'heard_where' => $mainVisitor->heard_where,

                            'attending_plenary' => $mainVisitor->attending_plenary,
                            'attending_symposium' => $mainVisitor->attending_symposium,
                            'attending_solxchange' => $mainVisitor->attending_solxchange,
                            'attending_yf' => $mainVisitor->attending_yf,
                            'attending_networking_dinner' => $mainVisitor->attending_networking_dinner,
                            'attending_welcome_dinner' => $mainVisitor->attending_welcome_dinner,
                            'attending_gala_dinner' => $mainVisitor->attending_gala_dinner,

                            'unit_price' => $mainVisitor->unit_price,
                            'discount_price' => $discountPrice,
                            'net_amount' => $netAMount,

                            'printed_badge_count' => $printedBadgeCount,
                            'printed_badge_date_time' => $printedBadgeDateTime,

                            'scanned_badge_count' => $scannedBadgeCount,
                            'scanned_badge_date_time' => $scannedBadgeDateTime,

                            // PLEASE CONTINUE HERE
                            'total_amount' => $mainVisitor->total_amount,
                            'payment_status' => $subVisitor->visitor_refunded ? 'refunded' : $mainVisitor->payment_status,
                            'registration_status' => $subVisitor->visitor_cancelled ? 'cancelled' : $mainVisitor->registration_status,
                            'mode_of_payment' => $mainVisitor->mode_of_payment,
                            'invoice_number' => $tempInvoiceNumber,
                            'reference_number' => $tempBookReference,
                            'registration_date_time' => $mainVisitor->registered_date_time,
                            'paid_date_time' => $mainVisitor->paid_date_time,

                            // NEW june 6 2023
                            'registration_method' => $mainVisitor->registration_method,
                            'transaction_remarks' => $mainVisitor->transaction_remarks,

                            'visitor_cancelled' => $subVisitor->visitor_cancelled,
                            'visitor_replaced' => $subVisitor->visitor_replaced,
                            'visitor_refunded' => $subVisitor->visitor_refunded,

                            'visitor_replaced_type' => $subVisitor->visitor_replaced_type,
                            'visitor_original_from_id' => $subVisitor->visitor_original_from_id,
                            'visitor_replaced_from_id' => $subVisitor->visitor_replaced_from_id,
                            'visitor_replaced_by_id' => $subVisitor->visitor_replaced_by_id,

                            'visitor_cancelled_datetime' => $subVisitor->visitor_cancelled_datetime,
                            'visitor_refunded_datetime' => $subVisitor->visitor_refunded_datetime,
                            'visitor_replaced_datetime' => $subVisitor->visitor_replaced_datetime,
                        ]);
                    }
                }
            }
        }

        $currentDate = Carbon::now()->format('Y-m-d');
        $fileName = $eventCategory . ' ' . $event->year . ' Transactions ' . '[' . $currentDate . '].csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'Transaction Id',
            'ID',
            'Visitor Type',
            'Event',
            'Pass Type',
            'Rate Type',

            'Promo Code used',
            'Badge Type',
            'Salutation',
            'First Name',
            'Last Name',
            'Email Address',
            'Mobile Number 1',
            'Job Title',
            'Nationality',

            'Company Name',
            'Company Address',
            'City',
            'Country',
            'Telephone Number',
            'Mobile Number 2',
            'Assistant Email Address',

            'Middle Name',

            'Unit Price',
            'Discount Price',
            'Total Amount',
            'Payment Status',
            'Registration Status',
            'Payment method',
            'Invoice Number',
            'Reference Number',
            'Registered Date & Time',
            'Paid Date & Time',
            'Printed badge count',
            'Printed badge date time',

            'Scanned badge count',
            'Scanned badge date time',

            'Company Sector',

            'Registration Method',
            'Transaction Remarks',

            'Visitor Cancelled',
            'Visitor Replaced',
            'Visitor Refunded',

            'Visitor Replaced Type',
            'Visitor Original From Id',
            'Visitor Replaced From Id',
            'Visitor Replaced By Id',

            'Visitor Cancelled Date & Time',
            'Visitor Refunded Date & Time',
            'Visitor Replaced Date & Time',

            'Heard Where',
        );

        $callback = function () use ($finalExcelData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($finalExcelData as $data) {
                fputcsv(
                    $file,
                    array(
                        $data['transaction_id'],
                        $data['id'],
                        $data['visitorType'],
                        $data['event'],
                        $data['pass_type'],
                        $data['rate_type'],

                        $data['pcode_used'],
                        $data['badge_type'],

                        $data['salutation'],
                        $data['first_name'],
                        $data['last_name'],
                        $data['email_address'],
                        $data['mobile_number'],
                        $data['job_title'],
                        $data['nationality'],

                        $data['company_name'],
                        $data['company_address'],
                        $data['company_city'],
                        $data['company_country'],
                        $data['company_telephone_number'],
                        $data['company_mobile_number'],
                        $data['assistant_email_address'],

                        $data['middle_name'],

                        $data['unit_price'],
                        $data['discount_price'],
                        $data['net_amount'],
                        $data['payment_status'],
                        $data['registration_status'],
                        $data['mode_of_payment'],
                        $data['invoice_number'],
                        $data['reference_number'],
                        $data['registration_date_time'],
                        $data['paid_date_time'],
                        $data['printed_badge_count'],
                        $data['printed_badge_date_time'],

                        $data['scanned_badge_count'],
                        $data['scanned_badge_date_time'],

                        $data['company_sector'],

                        $data['registration_method'],
                        $data['transaction_remarks'],

                        $data['visitor_cancelled'],
                        $data['visitor_replaced'],
                        $data['visitor_refunded'],

                        $data['visitor_replaced_type'],
                        $data['visitor_original_from_id'],
                        $data['visitor_replaced_from_id'],
                        $data['visitor_replaced_by_id'],

                        $data['visitor_cancelled_datetime'],
                        $data['visitor_refunded_datetime'],
                        $data['visitor_replaced_datetime'],

                        $data['heard_where'],
                    )
                );
            }
            fclose($file);
        };
        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }


    public function rccAwardsRegistrantsExportData($eventCategory, $eventId)
    {
        $finalExcelData = array();
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();

        $mainParticipants = RccAwardsMainParticipant::where('event_id', $eventId)->get();
        if (!$mainParticipants->isEmpty()) {
            foreach ($mainParticipants as $mainParticipant) {
                $mainTransactionId = RccAwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');

                $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
                $lastDigit = 1000 + intval($mainTransactionId);

                foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                    if ($event->category == $eventCategoryC) {
                        $getEventcode = $code;
                    }
                }

                $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
                $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

                $discountPrice = 0.0;
                $netAMount = $mainParticipant->unit_price;

                $entryFormId = RccAwardsDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('id');

                $getSupportingDocumentFiles = RccAwardsDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'supportingDocument')->get();

                $supportingDocumentLinks = [];

                if ($getSupportingDocumentFiles->isNotEmpty()) {
                    foreach ($getSupportingDocumentFiles as $supportingDocument) {
                        $supportingDocumentLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/' . $supportingDocument->id;
                        $supportingDocumentLinks[] = $supportingDocumentLink;
                    }
                }

                $entryFormDownloadLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/' . $entryFormId;

                $vatPrice = $netAMount * ($event->event_vat / 100);

                array_push($finalExcelData, [
                    'transaction_id' => $tempBookReference,
                    'id' => $mainParticipant->id,
                    'participantType' => 'Main',
                    'event' => $eventCategory,
                    'pass_type' => $mainParticipant->pass_type,
                    'rate_type' => $mainParticipant->rate_type,

                    'category' => $mainParticipant->category,
                    'sub_category' => ($mainParticipant->sub_category == null) ? 'N/A' : $mainParticipant->sub_category,
                    'company_name' => $mainParticipant->company_name,

                    'salutation' => $mainParticipant->salutation,
                    'first_name' => $mainParticipant->first_name,
                    'middle_name' => $mainParticipant->middle_name,
                    'last_name' => $mainParticipant->last_name,
                    'email_address' => $mainParticipant->email_address,
                    'mobile_number' => $mainParticipant->mobile_number,
                    'address' => $mainParticipant->address,
                    'country' => $mainParticipant->country,
                    'city' => $mainParticipant->city,
                    'job_title' => $mainParticipant->job_title,

                    'entryFormDownloadLink' => $entryFormDownloadLink,
                    'supportingDocumentLinks' => $supportingDocumentLinks,

                    'unit_price' => $mainParticipant->unit_price,
                    'discount_price' => $discountPrice,
                    'net_amount' => $netAMount,
                    'vat_price' => $vatPrice,
                    'printed_badge_date' => null,

                    // PLEASE CONTINUE HERE
                    'total_amount' => $mainParticipant->total_amount,
                    'payment_status' => $mainParticipant->delegate_refunded ? 'refunded' : $mainParticipant->payment_status,
                    'registration_status' => $mainParticipant->delegate_cancelled ? 'cancelled' : $mainParticipant->registration_status,
                    'mode_of_payment' => $mainParticipant->mode_of_payment,
                    'invoice_number' => $tempInvoiceNumber,
                    'reference_number' => $tempBookReference,
                    'registration_date_time' => $mainParticipant->registered_date_time,
                    'paid_date_time' => $mainParticipant->paid_date_time,

                    // NEW june 6 2023
                    'registration_method' => $mainParticipant->registration_method,
                    'transaction_remarks' => $mainParticipant->transaction_remarks,

                    'participant_cancelled' => $mainParticipant->participant_cancelled,
                    'participant_replaced' => $mainParticipant->participant_replaced,
                    'participant_refunded' => $mainParticipant->participant_refunded,

                    'participant_replaced_type' => null,
                    'participant_original_from_id' => null,
                    'participant_replaced_from_id' => null,
                    'participant_replaced_by_id' => $mainParticipant->participant_replaced_by_id,

                    'participant_cancelled_datetime' => $mainParticipant->participant_cancelled_datetime,
                    'participant_refunded_datetime' => $mainParticipant->participant_refunded_datetime,
                    'participant_replaced_datetime' => $mainParticipant->participant_replaced_datetime,
                ]);

                $subParticipants = RccAwardsAdditionalParticipant::where('main_participant_id', $mainParticipant->id)->get();

                if (!$subParticipants->isEmpty()) {
                    foreach ($subParticipants as $subParticipant) {
                        $subTransactionId = RccAwardsParticipantTransaction::where('participant_id', $subParticipant->id)->where('participant_type', "sub")->value('id');

                        $discountPrice = 0.0;
                        $netAMount = $mainParticipant->unit_price;

                        $lastDigit = 1000 + intval($subTransactionId);
                        $tempBookReferenceSub = "$event->year" . "$getEventcode" . "$lastDigit";

                        array_push($finalExcelData, [
                            'transaction_id' => $tempBookReferenceSub,
                            'id' => $subParticipant->id,
                            'participantType' => 'Sub',
                            'event' => $eventCategory,
                            'pass_type' => $mainParticipant->pass_type,
                            'rate_type' => $mainParticipant->rate_type,

                            'category' => $mainParticipant->category,
                            'sub_category' => ($mainParticipant->sub_category == null) ? 'N/A' : $mainParticipant->sub_category,
                            'company_name' => $mainParticipant->company_name,

                            'salutation' => $subParticipant->salutation,
                            'first_name' => $subParticipant->first_name,
                            'middle_name' => $subParticipant->middle_name,
                            'last_name' => $subParticipant->last_name,
                            'email_address' => $subParticipant->email_address,
                            'mobile_number' => $subParticipant->mobile_number,
                            'address' => $subParticipant->address,
                            'country' => $subParticipant->country,
                            'city' => $subParticipant->city,
                            'job_title' => $subParticipant->job_title,

                            'entryFormDownloadLink' => $entryFormDownloadLink,
                            'supportingDocumentLinks' => $supportingDocumentLinks,

                            'unit_price' => $mainParticipant->unit_price,
                            'discount_price' => $discountPrice,
                            'net_amount' => $netAMount,
                            'vat_price' => $vatPrice,
                            'printed_badge_date' => null,

                            // PLEASE CONTINUE HERE
                            'total_amount' => $mainParticipant->total_amount,
                            'payment_status' => $subParticipant->delegate_refunded ? 'refunded' : $mainParticipant->payment_status,
                            'registration_status' => $subParticipant->delegate_cancelled ? 'cancelled' : $mainParticipant->registration_status,
                            'mode_of_payment' => $mainParticipant->mode_of_payment,
                            'invoice_number' => $tempInvoiceNumber,
                            'reference_number' => $tempBookReference,
                            'registration_date_time' => $mainParticipant->registered_date_time,
                            'paid_date_time' => $mainParticipant->paid_date_time,

                            // NEW june 6 2023
                            'registration_method' => $mainParticipant->registration_method,
                            'transaction_remarks' => $mainParticipant->transaction_remarks,

                            'participant_cancelled' => $subParticipant->participant_cancelled,
                            'participant_replaced' => $subParticipant->participant_replaced,
                            'participant_refunded' => $subParticipant->participant_refunded,

                            'participant_replaced_type' => $subParticipant->participant_replaced_type,
                            'participant_original_from_id' => $subParticipant->participant_original_from_id,
                            'participant_replaced_from_id' => $subParticipant->participant_replaced_from_id,
                            'participant_replaced_by_id' => $subParticipant->participant_replaced_by_id,

                            'participant_cancelled_datetime' => $subParticipant->participant_cancelled_datetime,
                            'participant_refunded_datetime' => $subParticipant->participant_refunded_datetime,
                            'participant_replaced_datetime' => $subParticipant->participant_replaced_datetime,
                        ]);
                    }
                }
            }
        }

        $currentDate = Carbon::now()->format('Y-m-d');
        $fileName = $eventCategory . ' ' . $event->year . ' Transactions ' . '[' . $currentDate . '].csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'Transaction Id',
            'ID',
            'Participant Type',
            'Event',
            'Pass Type',
            'Rate Type',

            'Category',
            'Sub Category',
            'Company Name',

            'Salutation',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email Address',
            'Mobile Number',
            'Address',
            'Country',
            'City',
            'Job Title',

            'Entry Form',
            'Supporting Document 1',
            'Supporting Document 2',
            'Supporting Document 3',
            'Supporting Document 4',

            'Unit Price',
            'Discount Price',
            'Vat Price',
            'Total Amount',
            'Payment Status',
            'Registration Status',
            'Payment method',
            'Invoice Number',
            'Reference Number',
            'Registered Date & Time',
            'Paid Date & Time',
            'Printed badge',

            'Registration Method',
            'Transaction Remarks',

            'Participant Cancelled',
            'Participant Replaced',
            'Participant Refunded',

            'Participant Replaced Type',
            'Participant Original From Id',
            'Participant Replaced From Id',
            'Participant Replaced By Id',

            'Participant Cancelled Date & Time',
            'Participant Refunded Date & Time',
            'Participant Replaced Date & Time',
        );

        $callback = function () use ($finalExcelData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($finalExcelData as $data) {
                fputcsv(
                    $file,
                    array(
                        $data['transaction_id'],
                        $data['id'],
                        $data['participantType'],
                        $data['event'],
                        $data['pass_type'],
                        $data['rate_type'],

                        $data['category'],
                        $data['sub_category'],
                        $data['company_name'],

                        $data['salutation'],
                        $data['first_name'],
                        $data['middle_name'],
                        $data['last_name'],
                        $data['email_address'],
                        $data['mobile_number'],
                        $data['address'],
                        $data['country'],
                        $data['city'],
                        $data['job_title'],

                        $data['entryFormDownloadLink'],
                        $data['supportingDocumentLinks'][0] ?? 'N/A',
                        $data['supportingDocumentLinks'][1] ?? 'N/A',
                        $data['supportingDocumentLinks'][2] ?? 'N/A',
                        $data['supportingDocumentLinks'][3] ?? 'N/A',

                        $data['unit_price'],
                        $data['discount_price'],
                        $data['vat_price'],
                        $data['net_amount'],
                        $data['payment_status'],
                        $data['registration_status'],
                        $data['mode_of_payment'],
                        $data['invoice_number'],
                        $data['reference_number'],
                        $data['registration_date_time'],
                        $data['paid_date_time'],
                        $data['printed_badge_date'],

                        $data['registration_method'],
                        $data['transaction_remarks'],

                        $data['participant_cancelled'],
                        $data['participant_replaced'],
                        $data['participant_refunded'],

                        $data['participant_replaced_type'],
                        $data['participant_original_from_id'],
                        $data['participant_replaced_from_id'],
                        $data['participant_replaced_by_id'],

                        $data['participant_cancelled_datetime'],
                        $data['participant_refunded_datetime'],
                        $data['participant_replaced_datetime'],
                    )
                );
            }
            fclose($file);
        };

        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }



    public function awardsRegistrantsExportData($eventCategory, $eventId)
    {
        $finalExcelData = array();
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();

        $mainParticipants = AwardsMainParticipant::where('event_id', $eventId)->get();
        if (!$mainParticipants->isEmpty()) {
            foreach ($mainParticipants as $mainParticipant) {
                $mainTransactionId = AwardsParticipantTransaction::where('participant_id', $mainParticipant->id)->where('participant_type', "main")->value('id');

                $tempYear = Carbon::parse($mainParticipant->registered_date_time)->format('y');
                $lastDigit = 1000 + intval($mainTransactionId);

                foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                    if ($event->category == $eventCategoryC) {
                        $getEventcode = $code;
                    }
                }

                $tempInvoiceNumber = "$event->category" . "$tempYear" . "/" . "$lastDigit";
                $tempBookReference = "$event->year" . "$getEventcode" . "$lastDigit";

                $discountPrice = 0.0;
                $netAMount = $mainParticipant->unit_price;

                $entryFormId = AwardsParticipantDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'entryForm')->value('id');
                $getSupportingDocumentFiles = AwardsParticipantDocument::where('event_id', $eventId)->where('participant_id', $mainParticipant->id)->where('document_type', 'supportingDocument')->get();

                $supportingDocumentLinks = [];

                if ($getSupportingDocumentFiles->isNotEmpty()) {
                    foreach ($getSupportingDocumentFiles as $supportingDocument) {
                        $supportingDocumentLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/' . $supportingDocument->id;
                        $supportingDocumentLinks[] = $supportingDocumentLink;
                    }
                }

                $entryFormDownloadLink = env('APP_URL') . "/" . $event->category . "/" . $event->id . '/download-file/' . $entryFormId;

                $vatPrice = $netAMount * ($event->event_vat / 100);

                array_push($finalExcelData, [
                    'transaction_id' => $tempBookReference,
                    'id' => $mainParticipant->id,
                    'participantType' => 'Main',
                    'event' => $eventCategory,
                    'pass_type' => $mainParticipant->pass_type,
                    'rate_type' => $mainParticipant->rate_type,

                    'category' => $mainParticipant->category,
                    'sub_category' => ($mainParticipant->sub_category == null) ? 'N/A' : $mainParticipant->sub_category,
                    'company_name' => $mainParticipant->company_name,

                    'salutation' => $mainParticipant->salutation,
                    'first_name' => $mainParticipant->first_name,
                    'middle_name' => $mainParticipant->middle_name,
                    'last_name' => $mainParticipant->last_name,
                    'email_address' => $mainParticipant->email_address,
                    'mobile_number' => $mainParticipant->mobile_number,
                    'address' => $mainParticipant->address,
                    'country' => $mainParticipant->country,
                    'city' => $mainParticipant->city,
                    'job_title' => $mainParticipant->job_title,
                    'nationality' => $mainParticipant->nationality,

                    'entryFormDownloadLink' => $entryFormDownloadLink,
                    'supportingDocumentLinks' => $supportingDocumentLinks,

                    'unit_price' => $mainParticipant->unit_price,
                    'discount_price' => $discountPrice,
                    'net_amount' => $netAMount,
                    'vat_price' => $vatPrice,

                    // PLEASE CONTINUE HERE
                    'total_amount' => $mainParticipant->total_amount,
                    'payment_status' => $mainParticipant->delegate_refunded ? 'refunded' : $mainParticipant->payment_status,
                    'registration_status' => $mainParticipant->delegate_cancelled ? 'cancelled' : $mainParticipant->registration_status,
                    'mode_of_payment' => $mainParticipant->mode_of_payment,
                    'invoice_number' => $tempInvoiceNumber,
                    'reference_number' => $tempBookReference,
                    'registration_date_time' => $mainParticipant->registered_date_time,
                    'paid_date_time' => $mainParticipant->paid_date_time,

                    // NEW june 6 2023
                    'registration_method' => $mainParticipant->registration_method,
                    'transaction_remarks' => $mainParticipant->transaction_remarks,

                    'participant_cancelled' => $mainParticipant->participant_cancelled,
                    'participant_replaced' => $mainParticipant->participant_replaced,
                    'participant_refunded' => $mainParticipant->participant_refunded,

                    'participant_replaced_type' => null,
                    'participant_original_from_id' => null,
                    'participant_replaced_from_id' => null,
                    'participant_replaced_by_id' => $mainParticipant->participant_replaced_by_id,

                    'participant_cancelled_datetime' => $mainParticipant->participant_cancelled_datetime,
                    'participant_refunded_datetime' => $mainParticipant->participant_refunded_datetime,
                    'participant_replaced_datetime' => $mainParticipant->participant_replaced_datetime,
                ]);

                $subParticipants = AwardsAdditionalParticipant::where('main_participant_id', $mainParticipant->id)->get();

                if (!$subParticipants->isEmpty()) {
                    foreach ($subParticipants as $subParticipant) {
                        $subTransactionId = AwardsParticipantTransaction::where('participant_id', $subParticipant->id)->where('participant_type', "sub")->value('id');

                        $discountPrice = 0.0;
                        $netAMount = $mainParticipant->unit_price;

                        $lastDigit = 1000 + intval($subTransactionId);
                        $tempBookReferenceSub = "$event->year" . "$getEventcode" . "$lastDigit";

                        array_push($finalExcelData, [
                            'transaction_id' => $tempBookReferenceSub,
                            'id' => $subParticipant->id,
                            'participantType' => 'Sub',
                            'event' => $eventCategory,
                            'pass_type' => $mainParticipant->pass_type,
                            'rate_type' => $mainParticipant->rate_type,

                            'category' => $mainParticipant->category,
                            'sub_category' => ($mainParticipant->sub_category == null) ? 'N/A' : $mainParticipant->sub_category,
                            'company_name' => $mainParticipant->company_name,

                            'salutation' => $subParticipant->salutation,
                            'first_name' => $subParticipant->first_name,
                            'middle_name' => $subParticipant->middle_name,
                            'last_name' => $subParticipant->last_name,
                            'email_address' => $subParticipant->email_address,
                            'mobile_number' => $subParticipant->mobile_number,
                            'address' => $subParticipant->address,
                            'country' => $subParticipant->country,
                            'city' => $subParticipant->city,
                            'job_title' => $subParticipant->job_title,
                            'nationality' => $subParticipant->nationality,

                            'entryFormDownloadLink' => $entryFormDownloadLink,
                            'supportingDocumentLinks' => $supportingDocumentLinks,

                            'unit_price' => $mainParticipant->unit_price,
                            'discount_price' => $discountPrice,
                            'net_amount' => $netAMount,
                            'vat_price' => $vatPrice,

                            // PLEASE CONTINUE HERE
                            'total_amount' => $mainParticipant->total_amount,
                            'payment_status' => $subParticipant->delegate_refunded ? 'refunded' : $mainParticipant->payment_status,
                            'registration_status' => $subParticipant->delegate_cancelled ? 'cancelled' : $mainParticipant->registration_status,
                            'mode_of_payment' => $mainParticipant->mode_of_payment,
                            'invoice_number' => $tempInvoiceNumber,
                            'reference_number' => $tempBookReference,
                            'registration_date_time' => $mainParticipant->registered_date_time,
                            'paid_date_time' => $mainParticipant->paid_date_time,

                            // NEW june 6 2023
                            'registration_method' => $mainParticipant->registration_method,
                            'transaction_remarks' => $mainParticipant->transaction_remarks,

                            'participant_cancelled' => $subParticipant->participant_cancelled,
                            'participant_replaced' => $subParticipant->participant_replaced,
                            'participant_refunded' => $subParticipant->participant_refunded,

                            'participant_replaced_type' => $subParticipant->participant_replaced_type,
                            'participant_original_from_id' => $subParticipant->participant_original_from_id,
                            'participant_replaced_from_id' => $subParticipant->participant_replaced_from_id,
                            'participant_replaced_by_id' => $subParticipant->participant_replaced_by_id,

                            'participant_cancelled_datetime' => $subParticipant->participant_cancelled_datetime,
                            'participant_refunded_datetime' => $subParticipant->participant_refunded_datetime,
                            'participant_replaced_datetime' => $subParticipant->participant_replaced_datetime,
                        ]);
                    }
                }
            }
        }

        $currentDate = Carbon::now()->format('Y-m-d');
        $fileName = $eventCategory . ' ' . $event->year . ' Transactions ' . '[' . $currentDate . '].csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'Transaction Id',
            'ID',
            'Participant Type',
            'Event',
            'Pass Type',
            'Rate Type',

            'Category',
            'Company Name',

            'Salutation',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email Address',
            'Mobile Number',
            'Address',
            'Country',
            'City',
            'Nationality',
            'Job Title',

            'Entry Form',
            'Supporting Document 1',
            'Supporting Document 2',
            'Supporting Document 3',
            'Supporting Document 4',

            'Unit Price',
            'Discount Price',
            'Vat Price',
            'Total Amount',
            'Payment Status',
            'Registration Status',
            'Payment method',
            'Invoice Number',
            'Reference Number',
            'Registered Date & Time',
            'Paid Date & Time',

            'Registration Method',
            'Transaction Remarks',

            'Participant Cancelled',
            'Participant Replaced',
            'Participant Refunded',

            'Participant Replaced Type',
            'Participant Original From Id',
            'Participant Replaced From Id',
            'Participant Replaced By Id',

            'Participant Cancelled Date & Time',
            'Participant Refunded Date & Time',
            'Participant Replaced Date & Time',

        );

        $callback = function () use ($finalExcelData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($finalExcelData as $data) {
                fputcsv(
                    $file,
                    array(
                        $data['transaction_id'],
                        $data['id'],
                        $data['participantType'],
                        $data['event'],
                        $data['pass_type'],
                        $data['rate_type'],

                        $data['category'],
                        $data['company_name'],

                        $data['salutation'],
                        $data['first_name'],
                        $data['middle_name'],
                        $data['last_name'],
                        $data['email_address'],
                        $data['mobile_number'],
                        $data['address'],
                        $data['country'],
                        $data['city'],
                        $data['nationality'],
                        $data['job_title'],

                        $data['entryFormDownloadLink'],
                        $data['supportingDocumentLinks'][0] ?? 'N/A',
                        $data['supportingDocumentLinks'][1] ?? 'N/A',
                        $data['supportingDocumentLinks'][2] ?? 'N/A',
                        $data['supportingDocumentLinks'][3] ?? 'N/A',

                        $data['unit_price'],
                        $data['discount_price'],
                        $data['vat_price'],
                        $data['net_amount'],
                        $data['payment_status'],
                        $data['registration_status'],
                        $data['mode_of_payment'],
                        $data['invoice_number'],
                        $data['reference_number'],
                        $data['registration_date_time'],
                        $data['paid_date_time'],

                        $data['registration_method'],
                        $data['transaction_remarks'],

                        $data['participant_cancelled'],
                        $data['participant_replaced'],
                        $data['participant_refunded'],

                        $data['participant_replaced_type'],
                        $data['participant_original_from_id'],
                        $data['participant_replaced_from_id'],
                        $data['participant_replaced_by_id'],

                        $data['participant_cancelled_datetime'],
                        $data['participant_refunded_datetime'],
                        $data['participant_replaced_datetime'],
                    )
                );
            }
            fclose($file);
        };

        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }
}
