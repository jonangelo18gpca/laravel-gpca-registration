<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypes;
use App\Http\Controllers\Controller;
use App\Models\AdditionalDelegate;
use App\Models\AdditionalVisitor;
use App\Models\Event;
use App\Models\EventRegistrationType;
use App\Models\MainDelegate;
use App\Models\MainVisitor;
use App\Models\PrintedBadge;
use App\Models\ScannedDelegate;
use App\Models\ScannedVisitor;
use App\Models\Transaction;
use App\Models\VisitorPrintedBadge;
use App\Models\VisitorTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DelegateController extends Controller
{

    // =========================================================
    //                       RENDER VIEWS
    // =========================================================

    public function manageDelegateView()
    {
        return view('admin.delegates.delegate', [
            'pageTitle' => 'Manage Delegate',
        ]);
    }


    public function eventDelegateView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists() && $eventCategory != "AFS" && $eventCategory != "RCCA") {

            if ($eventCategory == "AFV") {
                $pageTitle = "Event Visitors";
            } else {
                $pageTitle = "Event Delegates";
            }

            return view('admin.events.delegates.delegates', [
                "pageTitle" => $pageTitle,
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function delegateDetailView($eventCategory, $eventId, $delegateType, $delegateId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists() && $eventCategory != "AFS" && $eventCategory != "RCCA") {
            if ($eventCategory == "AFV") {
                $visitorType = $delegateType;
                $visitorId = $delegateId;

                $finalVisitor = array();
                $tempVisitor = array();

                if ($visitorType == "main") {
                    $tempVisitor = MainVisitor::where('id', $visitorId)->first();
                } else {
                    $tempVisitor = AdditionalVisitor::where('id', $visitorId)->first();
                }

                if ($tempVisitor != null) {
                    $eventYear = Event::where('id', $eventId)->value('year');

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($eventCategory == $eventCategoryC) {
                            $eventCode = $code;
                        }
                    }

                    if ($visitorType  == "main") {
                        $tempYear = Carbon::parse($tempVisitor->registered_date_time)->format('y');
                        $transactionId = VisitorTransaction::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('visitor_type', "main")->value('id');
                        $lastDigit = 1000 + intval($transactionId);

                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;
                        $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;

                        $finalVisitor = [
                            'eventCategory' => $eventCategory,
                            'eventId' => $eventId,
                            'visitorType' => $visitorType,
                            'visitorId' => $visitorId,
                            'mainVisitorId' => $visitorId,

                            'salutation' => $tempVisitor->salutation,
                            'first_name' => $tempVisitor->first_name,
                            'middle_name' => $tempVisitor->middle_name,
                            'last_name' => $tempVisitor->last_name,
                            'email_address' => $tempVisitor->email_address,
                            'mobile_number' => $tempVisitor->mobile_number,
                            'nationality' => $tempVisitor->nationality,
                            'job_title' => $tempVisitor->job_title,
                            'badge_type' => $tempVisitor->badge_type,

                            'pass_type' => $tempVisitor->pass_type,
                            'company_name' => $tempVisitor->company_name,
                            'alternative_company_name' => $tempVisitor->alternative_company_name,
                            'company_sector' => $tempVisitor->company_sector,
                            'company_address' => $tempVisitor->company_address,
                            'company_country' => $tempVisitor->company_country,
                            'company_city' => $tempVisitor->company_city,
                            'company_telephone_number' => $tempVisitor->company_telephone_number,
                            'company_mobile_number' => $tempVisitor->company_mobile_number,

                            'finalTransactionId' => $finalTransactionId,
                            'invoiceNumber' => $invoiceNumber,
                        ];
                    } else {
                        $mainVisitorInfo = MainVisitor::where('id', $tempVisitor->main_visitor_id)->first();

                        $tempYear = Carbon::parse($mainVisitorInfo->registered_date_time)->format('y');

                        $transactionId = VisitorTransaction::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('visitor_type', "sub")->value('id');

                        $lastDigit = 1000 + intval($transactionId);
                        $transactionId2 = VisitorTransaction::where('event_id', $eventId)->where('visitor_id', $mainVisitorInfo->id)->where('visitor_type', "main")->value('id');
                        $lastDigit2 = 1000 + intval($transactionId2);

                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;
                        $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit2;

                        $finalVisitor = [
                            'visitorType' => $visitorType,
                            'visitorId' => $visitorId,
                            'mainVisitorId' => $mainVisitorInfo->id,

                            'salutation' => $tempVisitor->salutation,
                            'first_name' => $tempVisitor->first_name,
                            'middle_name' => $tempVisitor->middle_name,
                            'last_name' => $tempVisitor->last_name,
                            'email_address' => $tempVisitor->email_address,
                            'mobile_number' => $tempVisitor->mobile_number,
                            'nationality' => $tempVisitor->nationality,
                            'job_title' => $tempVisitor->job_title,
                            'badge_type' => $tempVisitor->badge_type,

                            'pass_type' => $mainVisitorInfo->pass_type,
                            'company_name' => $mainVisitorInfo->company_name,
                            'alternative_company_name' => $mainVisitorInfo->alternative_company_name,
                            'company_sector' => $mainVisitorInfo->company_sector,
                            'company_address' => $mainVisitorInfo->company_address,
                            'company_country' => $mainVisitorInfo->company_country,
                            'company_city' => $mainVisitorInfo->company_city,
                            'company_telephone_number' => $mainVisitorInfo->company_telephone_number,
                            'company_mobile_number' => $mainVisitorInfo->company_mobile_number,

                            'finalTransactionId' => $finalTransactionId,
                            'invoiceNumber' => $invoiceNumber,
                        ];
                    }

                    return view('admin.events.delegates.delegates_detail', [
                        "pageTitle" => "Event Visitor",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "finalVisitor" => $finalVisitor,
                    ]);
                } else {
                    abort(404, 'The URL is incorrect');
                }
            } else {
                $finalDelegate = array();
                $tempDelegate = array();

                if ($delegateType == "main") {
                    $tempDelegate = MainDelegate::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalDelegate::where('id', $delegateId)->first();
                }

                if ($tempDelegate != null) {
                    $eventYear = Event::where('id', $eventId)->value('year');

                    foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                        if ($eventCategory == $eventCategoryC) {
                            $eventCode = $code;
                        }
                    }

                    if ($delegateType  == "main") {

                        $tempYear = Carbon::parse($tempDelegate->registered_date_time)->format('y');

                        $transactionId = Transaction::where('event_id', $eventId)->where('delegate_id', $delegateId)->where('delegate_type', "main")->value('id');

                        $lastDigit = 1000 + intval($transactionId);

                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;
                        $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;

                        if ($tempDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                            $finalAccessType = "Conference only";
                        } else if ($tempDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                            $finalAccessType = "Workshop only";
                        } else {
                            $finalAccessType = "Full event";
                        }

                        $finalDelegate = [
                            'eventCategory' => $eventCategory,
                            'eventId' => $eventId,
                            'delegateType' => $delegateType,
                            'delegateId' => $delegateId,
                            'mainDelegateId' => $delegateId,

                            'salutation' => $tempDelegate->salutation,
                            'first_name' => $tempDelegate->first_name,
                            'middle_name' => $tempDelegate->middle_name,
                            'last_name' => $tempDelegate->last_name,
                            'email_address' => $tempDelegate->email_address,
                            'mobile_number' => $tempDelegate->mobile_number,
                            'nationality' => $tempDelegate->nationality,
                            'job_title' => $tempDelegate->job_title,
                            'badge_type' => $tempDelegate->badge_type,
                            'country' => $tempDelegate->country,

                            'seat_number' => $tempDelegate->seat_number,

                            'access_type' => $finalAccessType,
                            'pass_type' => $tempDelegate->pass_type,
                            'company_name' => $tempDelegate->company_name,
                            'alternative_company_name' => $tempDelegate->alternative_company_name,
                            'company_sector' => $tempDelegate->company_sector,
                            'company_address' => $tempDelegate->company_address,
                            'company_country' => $tempDelegate->company_country,
                            'company_city' => $tempDelegate->company_city,
                            'company_telephone_number' => $tempDelegate->company_telephone_number,
                            'company_mobile_number' => $tempDelegate->company_mobile_number,

                            'finalTransactionId' => $finalTransactionId,
                            'invoiceNumber' => $invoiceNumber,
                        ];
                    } else {
                        $mainDelegateInfo = MainDelegate::where('id', $tempDelegate->main_delegate_id)->first();

                        $tempYear = Carbon::parse($mainDelegateInfo->registered_date_time)->format('y');

                        $transactionId = Transaction::where('event_id', $eventId)->where('delegate_id', $delegateId)->where('delegate_type', "sub")->value('id');

                        $lastDigit = 1000 + intval($transactionId);
                        $transactionId2 = Transaction::where('event_id', $eventId)->where('delegate_id', $mainDelegateInfo->id)->where('delegate_type', "main")->value('id');
                        $lastDigit2 = 1000 + intval($transactionId2);

                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;
                        $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit2;

                        if ($tempDelegate->access_type == AccessTypes::CONFERENCE_ONLY->value) {
                            $finalAccessType = "Conference only";
                        } else if ($tempDelegate->access_type == AccessTypes::WORKSHOP_ONLY->value) {
                            $finalAccessType = "Workshop only";
                        } else {
                            $finalAccessType = "Full event";
                        }

                        $finalDelegate = [
                            'delegateType' => $delegateType,
                            'delegateId' => $delegateId,
                            'mainDelegateId' => $mainDelegateInfo->id,

                            'salutation' => $tempDelegate->salutation,
                            'first_name' => $tempDelegate->first_name,
                            'middle_name' => $tempDelegate->middle_name,
                            'last_name' => $tempDelegate->last_name,
                            'email_address' => $tempDelegate->email_address,
                            'mobile_number' => $tempDelegate->mobile_number,
                            'nationality' => $tempDelegate->nationality,
                            'job_title' => $tempDelegate->job_title,
                            'badge_type' => $tempDelegate->badge_type,
                            'country' => $tempDelegate->country,

                            'seat_number' => $tempDelegate->seat_number,

                            'access_type' => $finalAccessType,
                            'pass_type' => $mainDelegateInfo->pass_type,
                            'company_name' => $mainDelegateInfo->company_name,
                            'alternative_company_name' => $mainDelegateInfo->alternative_company_name,
                            'company_sector' => $mainDelegateInfo->company_sector,
                            'company_address' => $mainDelegateInfo->company_address,
                            'company_country' => $mainDelegateInfo->company_country,
                            'company_city' => $mainDelegateInfo->company_city,
                            'company_telephone_number' => $mainDelegateInfo->company_telephone_number,
                            'company_mobile_number' => $mainDelegateInfo->company_mobile_number,

                            'finalTransactionId' => $finalTransactionId,
                            'invoiceNumber' => $invoiceNumber,
                        ];
                    }

                    return view('admin.events.delegates.delegates_detail', [
                        "pageTitle" => "Event Delegate",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "finalDelegate" => $finalDelegate,
                    ]);
                } else {
                    abort(404, 'The URL is incorrect');
                }
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function printedBadgeListView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            return view('admin.events.printed-badge.printed_badge_list', [
                "pageTitle" => "Printed badges",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function scannedDelegateListView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if ($eventCategory == "AFV") {
                $pageTitle = "Scanned visitors";
            } else {
                $pageTitle = "Scanned delegates";
            }
            return view('admin.events.scanned-delegate.scanned_delegate_list', [
                "pageTitle" => $pageTitle,
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function scannedDelegateListCategorizedView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $pageTitle = "Scanned delegates";
            return view('admin.events.scanned-delegate.categorized.scanned_delegate_list_categorized', [
                "pageTitle" => $pageTitle,
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }


    public function eventEmailBroadcastView($eventCategory, $eventId, $badgeCategory)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            return view('admin.events.broadcast.email_broadcast', [
                "pageTitle" => "Email broadcast",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
                "badgeCategory" => $badgeCategory,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function scanQrView($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            return view('admin.events.scan-qr.scan_qr', [
                "pageTitle" => "Scan QR",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function scanQrV2View($eventCategory, $eventId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            return view('admin.events.scan-qr-v2.scan_qr_v2', [
                "pageTitle" => "Scan QR",
                "eventCategory" => $eventCategory,
                "eventId" => $eventId,
            ]);
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function digitalHelper($eventCategory, $eventId)
    {
        $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
        if ($event) {
            if ($eventCategory == "AF" && $event->year == "2026") {
                return view('digital-helper.2026.af.af-digital-helper', [
                    "pageTitle" => "Digital helper - " . $event->name,
                    "event" => $event,
                ]);
            } else if ($eventCategory == "AF" && $event->year == "2025") {
                return view('digital-helper.2025.af.af-digital-helper', [
                    "pageTitle" => "Digital helper - " . $event->name,
                    "event" => $event,
                ]);
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function digitalHelperFAQ($eventCategory, $eventId)
    {
        $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
        if ($event) {
            if ($eventCategory == "AF" && $event->year == "2026") {
                return view('digital-helper.2026.af.af-digital-helper-faq', [
                    "pageTitle" => "Digital helper FAQs - " . $event->name,
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                ]);
            } else if ($eventCategory == "AF" && $event->year == "2025") {
                return view('digital-helper.2025.af.af-digital-helper-faq', [
                    "pageTitle" => "Digital helper FAQs - " . $event->name,
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                ]);
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function addDelegatesToGripView($eventCategory, $eventId)
    {
        $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
        if ($event) {
            if ($eventCategory == "AF" && $event->year == "2026") {
                return view('admin.delegates.add-to-grip', [
                    "pageTitle" => "Add delegates to Grip - " . $event->name,
                    "event" => $event,
                    "eventCategory" => $eventCategory,
                    "eventId" => $eventId,
                ]);
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function updateLogsView($eventCategory, $eventId)
    {
        $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();
        if ($event) {
            return view('admin.delegates.update-logs', [
                "pageTitle" => "Delgate details update logs - " . $event->name,
                "event" => $event,
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

    public function delegateDetailScanBadge($eventCategory, $eventId, $delegateType, $delegateId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            if ($eventCategory == "AFV") {
                $tempVisitor = array();
                $visitorId = $delegateId;
                $visitorType = $delegateType;

                if ($visitorType == "main") {
                    $tempVisitor = MainVisitor::where('id', $visitorId)->first();
                } else {
                    $tempVisitor = AdditionalVisitor::where('id', $visitorId)->first();
                }

                if ($tempVisitor == null) {
                    return view('admin.events.scanned-delegate.scanned_feedback', [
                        "pageTitle" => "Scanned Visitor",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "delegateType" => $visitorType,
                        "delegateId" => $visitorId,
                        "status" => "Failed",
                    ]);
                } else {
                    ScannedVisitor::create([
                        'event_id' => $eventId,
                        'event_category' => $eventCategory,
                        'visitor_id' => $visitorId,
                        'visitor_type' => $visitorType,
                        'scanned_date_time' => Carbon::now(),
                    ]);

                    if ($visitorType == "main") {
                        $companyName = $tempVisitor->company_name;
                    } else {
                        $companyName = MainVisitor::where('id', $tempVisitor->main_visitor_id)->value('company_name');
                    }

                    return view('admin.events.scanned-delegate.scanned_feedback', [
                        "pageTitle" => "Scanned Visitor",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "delegateType" => $visitorType,
                        "delegateId" => $visitorId,
                        "status" => "Success",
                        "name" => $tempVisitor->salutation . ' ' . $tempVisitor->first_name . ' ' . $tempVisitor->middle_name . ' ' . $tempVisitor->last_name,
                        "jobTitle" => $tempVisitor->job_title,
                        "companyName" => $companyName,
                    ]);
                }
            } else {
                $tempDelegate = array();

                if ($delegateType == "main") {
                    $tempDelegate = MainDelegate::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalDelegate::where('id', $delegateId)->first();
                }

                if ($tempDelegate == null) {
                    return view('admin.events.scanned-delegate.scanned_feedback', [
                        "pageTitle" => "Scanned delegate",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "delegateType" => $delegateType,
                        "delegateId" => $delegateId,
                        "status" => "Failed",
                    ]);
                } else {
                    ScannedDelegate::create([
                        'event_id' => $eventId,
                        'event_category' => $eventCategory,
                        'delegate_id' => $delegateId,
                        'delegate_type' => $delegateType,
                        'scanned_date_time' => Carbon::now(),
                    ]);

                    if ($delegateType == "main") {
                        $companyName = $tempDelegate->company_name;
                    } else {
                        $companyName = MainDelegate::where('id', $tempDelegate->main_delegate_id)->value('company_name');
                    }

                    return view('admin.events.scanned-delegate.scanned_feedback', [
                        "pageTitle" => "Scanned delegate",
                        "eventCategory" => $eventCategory,
                        "eventId" => $eventId,
                        "delegateType" => $delegateType,
                        "delegateId" => $delegateId,
                        "status" => "Success",
                        "name" => $tempDelegate->salutation . ' ' . $tempDelegate->first_name . ' ' . $tempDelegate->middle_name . ' ' . $tempDelegate->last_name,
                        "jobTitle" => $tempDelegate->job_title,
                        "companyName" => $companyName,
                    ]);
                }
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function delegateDetailPrintBadge($eventCategory, $eventId, $delegateType, $delegateId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

            $finalDelegate = array();
            $tempDelegate = array();

            if ($eventCategory == "AFV") {
                if ($delegateType == "main") {
                    $tempDelegate = MainVisitor::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalVisitor::where('id', $delegateId)->first();
                }
            } else {
                if ($delegateType == "main") {
                    $tempDelegate = MainDelegate::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalDelegate::where('id', $delegateId)->first();
                }
            }


            $frontBanner = public_path(Storage::url($event->badge_front_banner));
            $finalFrontBanner = str_replace('\/', '/', $frontBanner);

            $backtBanner = public_path(Storage::url($event->badge_back_banner));
            $finalBackBanner = str_replace('\/', '/', $backtBanner);

            if ($eventCategory == "PSW") {
                // $finalHeight = (15.2 / 2.54) * 72;
                // $finalWidth = (23.3 / 2.54) * 72;


                // $finalHeight = (12.5 / 2.54) * 72;
                // $finalWidth = (17.2 / 2.54) * 72;


                $finalHeight = (13.1 / 2.54) * 72;
                $finalWidth = (18.2 / 2.54) * 72;
            } else {
                // $finalHeight = (15.2 / 2.54) * 72;
                // $finalWidth = (22.3 / 2.54) * 72;


                // $finalHeight = (15.6 / 2.54) * 72;
                // $finalWidth = (25.8 / 2.54) * 72;


                $finalHeight = (13.1 / 2.54) * 72;
                $finalWidth = (21.0 / 2.54) * 72;
            }

            $combinedString = "gpca@reg" . ',' . $eventId . ',' . $eventCategory . ',' . $delegateId . ',' . $delegateType;
            $finalCryptString = base64_encode($combinedString);
            $scanDelegateUrl = 'gpca' . $finalCryptString;

            if ($tempDelegate != null) {
                $registrationType = EventRegistrationType::where('event_id', $eventId)->where('event_category', $eventCategory)->where('registration_type', $tempDelegate->badge_type)->first();

                if ($delegateType  == "main") {
                    if ($tempDelegate->salutation == "Dr." || $tempDelegate->salutation == "Prof.") {
                        $delegateSalutation = $tempDelegate->salutation;
                    } else {
                        $delegateSalutation = null;
                    }


                    if ($tempDelegate->alternative_company_name != null) {
                        $companyName = $tempDelegate->alternative_company_name;
                    } else {
                        $companyName = $tempDelegate->company_name;
                    }

                    $seatNumber = null;
                    if ($eventCategory == "AF") {
                        if ($tempDelegate->seat_number != null) {
                            $seatNumber = $tempDelegate->seat_number;
                        }
                    }

                    $finalDelegate = [
                        'salutation' => $delegateSalutation,
                        'first_name' => $tempDelegate->first_name,
                        'middle_name' => $tempDelegate->middle_name,
                        'last_name' => $tempDelegate->last_name,
                        'job_title' => $tempDelegate->job_title,
                        'badge_type' => $tempDelegate->badge_type,
                        'companyName' => $companyName,
                        'frontBanner' =>  $finalFrontBanner,
                        'backBanner' =>  $finalBackBanner,
                        'textColor' => $event->badge_footer_link_color,
                        'bgColor' => $event->badge_footer_bg_color,
                        'link' => $event->badge_footer_link,

                        'frontText' => $registrationType->badge_footer_front_name,
                        'frontTextColor' => $registrationType->badge_footer_front_text_color,
                        'frontTextBGColor' => $registrationType->badge_footer_front_bg_color,
                        'backText' => $registrationType->badge_footer_back_name,
                        'backTextColor' => $registrationType->badge_footer_back_text_color,
                        'backTextBGColor' => $registrationType->badge_footer_back_bg_color,
                        'finalWidth' => $finalWidth,
                        'finalHeight' => $finalHeight,

                        'seatNumber' => $seatNumber,

                        'scanDelegateUrl' => $scanDelegateUrl,
                        'badgeName' => $tempDelegate->last_name . '_' . $companyName . '_BADGE',
                    ];
                } else {
                    if ($eventCategory == "AFV") {
                        $mainDelegateInfo = MainVisitor::where('id', $tempDelegate->main_visitor_id)->first();
                    } else {
                        $mainDelegateInfo = MainDelegate::where('id', $tempDelegate->main_delegate_id)->first();
                    }

                    if ($tempDelegate->salutation == "Dr." || $tempDelegate->salutation == "Prof.") {
                        $delegateSalutation = $tempDelegate->salutation;
                    } else {
                        $delegateSalutation = null;
                    }

                    if ($mainDelegateInfo->alternative_company_name != null) {
                        $companyName = $mainDelegateInfo->alternative_company_name;
                    } else {
                        $companyName = $mainDelegateInfo->company_name;
                    }

                    $seatNumber = null;
                    if ($eventCategory == "AF") {
                        if ($tempDelegate->seat_number != null) {
                            $seatNumber = $tempDelegate->seat_number;
                        }
                    }

                    $finalDelegate = [
                        'salutation' => $delegateSalutation,
                        'first_name' => $tempDelegate->first_name,
                        'middle_name' => $tempDelegate->middle_name,
                        'last_name' => $tempDelegate->last_name,
                        'job_title' => $tempDelegate->job_title,
                        'badge_type' => $tempDelegate->badge_type,
                        'companyName' => $companyName,
                        'frontBanner' =>  $finalFrontBanner,
                        'backBanner' =>  $finalBackBanner,
                        'textColor' => $event->badge_footer_link_color,
                        'bgColor' => $event->badge_footer_bg_color,
                        'link' => $event->badge_footer_link,

                        'frontText' => $registrationType->badge_footer_front_name,
                        'frontTextColor' => $registrationType->badge_footer_front_text_color,
                        'frontTextBGColor' => $registrationType->badge_footer_front_bg_color,
                        'backText' => $registrationType->badge_footer_back_name,
                        'backTextColor' => $registrationType->badge_footer_back_text_color,
                        'backTextBGColor' => $registrationType->badge_footer_back_bg_color,
                        'finalWidth' => $finalWidth,
                        'finalHeight' => $finalHeight,

                        'seatNumber' => $seatNumber,

                        'scanDelegateUrl' => $scanDelegateUrl,
                        'badgeName' => $tempDelegate->last_name . '_' . $companyName . '_BADGE',
                    ];
                }

                if ($eventCategory == "PSW") {
                    $pdf = Pdf::loadView('admin.events.delegates.delegate_badgev6', $finalDelegate, [
                        'margin_top' => 0,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
                    ]);
                    $pdf->setPaper('A4', 'portrait');
                } else {
                    $pdf = Pdf::loadView('admin.events.delegates.delegate_badgev7', $finalDelegate, [
                        'margin_top' => 0,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
                    ]);
                    $pdf->setPaper(array(0, 0, $finalWidth, $finalHeight), 'custom');
                }

                // return view('admin.events.delegates.delegate_badgev4', $finalDelegate);
                return $pdf->stream($finalDelegate['badgeName'] . '.pdf',  array('Attachment' => 0));
                // return $pdf->download($finalDelegate['badgeName'] . '.pdf');
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }




    public function delegateDetailPublicPrintBadge($eventCategory, $eventId, $delegateType, $delegateId)
    {
        if (Event::where('category', $eventCategory)->where('id', $eventId)->exists()) {
            $event = Event::where('category', $eventCategory)->where('id', $eventId)->first();

            $finalDelegate = array();
            $tempDelegate = array();

            if ($eventCategory == "AFV") {
                if ($delegateType == "main") {
                    $tempDelegate = MainVisitor::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalVisitor::where('id', $delegateId)->first();
                }
            } else {
                if ($delegateType == "main") {
                    $tempDelegate = MainDelegate::where('id', $delegateId)->first();
                } else {
                    $tempDelegate = AdditionalDelegate::where('id', $delegateId)->first();
                }
            }


            $frontBanner = public_path(Storage::url($event->badge_front_banner));
            $finalFrontBanner = str_replace('\/', '/', $frontBanner);

            $backtBanner = public_path(Storage::url($event->badge_back_banner));
            $finalBackBanner = str_replace('\/', '/', $backtBanner);

            if ($eventCategory == "PSW") {
                // $finalHeight = (15.2 / 2.54) * 72;
                // $finalWidth = (23.3 / 2.54) * 72;


                // $finalHeight = (12.5 / 2.54) * 72;
                // $finalWidth = (17.2 / 2.54) * 72;


                $finalHeight = (13.1 / 2.54) * 72;
                $finalWidth = (18.2 / 2.54) * 72;
            } else {
                // $finalHeight = (15.2 / 2.54) * 72;
                // $finalWidth = (22.3 / 2.54) * 72;


                // $finalHeight = (15.6 / 2.54) * 72;
                // $finalWidth = (25.8 / 2.54) * 72;


                $finalHeight = (13.1 / 2.54) * 72;
                $finalWidth = (21.0 / 2.54) * 72;
            }

            $combinedString = "gpca@reg" . ',' . $eventId . ',' . $eventCategory . ',' . $delegateId . ',' . $delegateType;
            $finalCryptString = base64_encode($combinedString);
            $scanDelegateUrl = 'gpca' . $finalCryptString;

            if ($tempDelegate != null) {
                $registrationType = EventRegistrationType::where('event_id', $eventId)->where('event_category', $eventCategory)->where('registration_type', $tempDelegate->badge_type)->first();

                if ($delegateType  == "main") {
                    if ($tempDelegate->salutation == "Dr." || $tempDelegate->salutation == "Prof.") {
                        $delegateSalutation = $tempDelegate->salutation;
                    } else {
                        $delegateSalutation = null;
                    }


                    if ($tempDelegate->alternative_company_name != null) {
                        $companyName = $tempDelegate->alternative_company_name;
                    } else {
                        $companyName = $tempDelegate->company_name;
                    }

                    $seatNumber = null;
                    if ($eventCategory == "AF") {
                        if ($tempDelegate->seat_number != null) {
                            $seatNumber = $tempDelegate->seat_number;
                        }
                    }

                    $finalDelegate = [
                        'salutation' => $delegateSalutation,
                        'first_name' => $tempDelegate->first_name,
                        'middle_name' => $tempDelegate->middle_name,
                        'last_name' => $tempDelegate->last_name,
                        'job_title' => $tempDelegate->job_title,
                        'badge_type' => $tempDelegate->badge_type,
                        'companyName' => $companyName,
                        'frontBanner' =>  $finalFrontBanner,
                        'backBanner' =>  $finalBackBanner,
                        'textColor' => $event->badge_footer_link_color,
                        'bgColor' => $event->badge_footer_bg_color,
                        'link' => $event->badge_footer_link,

                        'frontText' => $registrationType->badge_footer_front_name,
                        'frontTextColor' => $registrationType->badge_footer_front_text_color,
                        'frontTextBGColor' => $registrationType->badge_footer_front_bg_color,
                        'backText' => $registrationType->badge_footer_back_name,
                        'backTextColor' => $registrationType->badge_footer_back_text_color,
                        'backTextBGColor' => $registrationType->badge_footer_back_bg_color,
                        'finalWidth' => $finalWidth,
                        'finalHeight' => $finalHeight,

                        'seatNumber' => $seatNumber,

                        'scanDelegateUrl' => $scanDelegateUrl,
                        'badgeName' => $tempDelegate->last_name . '_' . $companyName . '_BADGE',
                    ];
                } else {
                    if ($eventCategory == "AFV") {
                        $mainDelegateInfo = MainVisitor::where('id', $tempDelegate->main_visitor_id)->first();
                    } else {
                        $mainDelegateInfo = MainDelegate::where('id', $tempDelegate->main_delegate_id)->first();
                    }

                    if ($tempDelegate->salutation == "Dr." || $tempDelegate->salutation == "Prof.") {
                        $delegateSalutation = $tempDelegate->salutation;
                    } else {
                        $delegateSalutation = null;
                    }

                    if ($mainDelegateInfo->alternative_company_name != null) {
                        $companyName = $mainDelegateInfo->alternative_company_name;
                    } else {
                        $companyName = $mainDelegateInfo->company_name;
                    }

                    $seatNumber = null;
                    if ($eventCategory == "AF") {
                        if ($tempDelegate->seat_number != null) {
                            $seatNumber = $tempDelegate->seat_number;
                        }
                    }

                    $finalDelegate = [
                        'salutation' => $delegateSalutation,
                        'first_name' => $tempDelegate->first_name,
                        'middle_name' => $tempDelegate->middle_name,
                        'last_name' => $tempDelegate->last_name,
                        'job_title' => $tempDelegate->job_title,
                        'badge_type' => $tempDelegate->badge_type,
                        'companyName' => $companyName,
                        'frontBanner' =>  $finalFrontBanner,
                        'backBanner' =>  $finalBackBanner,
                        'textColor' => $event->badge_footer_link_color,
                        'bgColor' => $event->badge_footer_bg_color,
                        'link' => $event->badge_footer_link,

                        'frontText' => $registrationType->badge_footer_front_name,
                        'frontTextColor' => $registrationType->badge_footer_front_text_color,
                        'frontTextBGColor' => $registrationType->badge_footer_front_bg_color,
                        'backText' => $registrationType->badge_footer_back_name,
                        'backTextColor' => $registrationType->badge_footer_back_text_color,
                        'backTextBGColor' => $registrationType->badge_footer_back_bg_color,
                        'finalWidth' => $finalWidth,
                        'finalHeight' => $finalHeight,

                        'seatNumber' => $seatNumber,

                        'scanDelegateUrl' => $scanDelegateUrl,
                        'badgeName' => $tempDelegate->last_name . '_' . $companyName . '_BADGE',
                    ];
                }

                if ($eventCategory == "PSW") {
                    $pdf = Pdf::loadView('admin.events.delegates.delegate_badgev6', $finalDelegate, [
                        'margin_top' => 0,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
                    ]);
                    $pdf->setPaper('A4', 'portrait');
                } else {
                    $pdf = Pdf::loadView('admin.events.delegates.delegate_badgev7', $finalDelegate, [
                        'margin_top' => 0,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
                    ]);
                    $pdf->setPaper(array(0, 0, $finalWidth, $finalHeight), 'custom');
                }

                if ($eventCategory == "AFV") {
                    VisitorPrintedBadge::create([
                        'event_id' => $eventId,
                        'event_category' => $eventCategory,
                        'visitor_id' => $delegateId,
                        'visitor_type' => $delegateType,
                        'printed_date_time' => Carbon::now(),
                    ]);
                } else {
                    PrintedBadge::create([
                        'event_id' => $eventId,
                        'event_category' => $eventCategory,
                        'delegate_id' => $delegateId,
                        'delegate_type' => $delegateType,
                        'printed_date_time' => Carbon::now(),
                    ]);
                }

                // return view('admin.events.delegates.delegate_badgev4', $finalDelegate);
                // return $pdf->stream($finalDelegate['badgeName'] . '.pdf',  array('Attachment'=> 0) );
                return $pdf->download($finalDelegate['badgeName'] . '.pdf');
            } else {
                abort(404, 'The URL is incorrect');
            }
        } else {
            abort(404, 'The URL is incorrect');
        }
    }

    public function scannedDelegateExportAllData($eventCategory, $eventId)
    {
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();
        if ($event) {
            $finalListsOfScannedDelegates = $this->getScannedDelegates($event);
            $currentDate = Carbon::now()->format('Y-m-d');
            $fileName = $eventCategory . ' ' . $event->year . ' Scanned Delegates ' . '[' . $currentDate . '].csv';
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $columns = array(
                'Transaction Id',
                'Company Name',
                'Salutation',
                'First name',
                'Middle name',
                'Last name',
                'Job title',
                'Email address',
                'Registration type',
                'Scanned date',
                'Scanned time',
                'Scanned location',
            );

            $callback = function () use ($finalListsOfScannedDelegates, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($finalListsOfScannedDelegates as $data) {
                    fputcsv(
                        $file,
                        array(
                            $data['delegateTransactionId'],
                            $data['delegateCompany'],
                            $data['delegateSalutation'],
                            $data['delegateFirstName'],
                            $data['delegateMiddleName'],
                            $data['delegateLastName'],
                            $data['delegateJobTitle'],
                            $data['delegateEmailAddress'],
                            $data['delegateBadgeType'],
                            $data['delegateScannedDate'],
                            $data['delegateScannedTime'],
                            $data['delegateScannedLocation'],
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


    public function scannedDelegateExportCategorizedData($eventCategory, $eventId)
    {
        $event = Event::where('id', $eventId)->where('category', $eventCategory)->first();
        if ($event) {
            $dayTimings = config('app.scanTimings.2024.PSC');
            $finalListsOfScannedDelegates = $this->getScannedDelegates($event);

            $finalData = array();

            if (count($dayTimings) > 0) {
                foreach ($dayTimings as $dayNameKey => $day) {
                    $dayName = $dayNameKey;
                    if (count($day) > 0) {
                        foreach ($day as $timeNameKey => $timings) {
                            $timeName = $timeNameKey;
                            $start_time = $timings['start_time'];
                            $end_time = $timings['end_time'];
                            $date = $timings['date'];
                            $startTime = Carbon::parse($start_time)->format('H:i:s');
                            $endTime = Carbon::parse($end_time)->format('H:i:s');
                            foreach ($finalListsOfScannedDelegates as $finalListsOfScannedDelegate) {
                                if ($date == Carbon::parse($finalListsOfScannedDelegate['delegateScannedDate'])->format('Y-m-d')) {
                                    $delegateScannedTime = Carbon::parse($finalListsOfScannedDelegate['delegateScannedTime'])->format('H:i:s');
                                    if ($delegateScannedTime >= $startTime && $delegateScannedTime < $endTime) {
                                        array_push($finalData, [
                                            'delegateDayName' => $dayName,
                                            'delegateTime' => "$startTime - $endTime",
                                            'delegateTimeName' => $timeName,
                                            'delegateTransactionId' => $finalListsOfScannedDelegate['delegateTransactionId'],
                                            'delegateCompany' => $finalListsOfScannedDelegate['delegateCompany'],
                                            'delegateSalutation' => $finalListsOfScannedDelegate['delegateSalutation'],
                                            'delegateFirstName' => $finalListsOfScannedDelegate['delegateFirstName'],
                                            'delegateMiddleName' => $finalListsOfScannedDelegate['delegateMiddleName'],
                                            'delegateLastName' => $finalListsOfScannedDelegate['delegateLastName'],
                                            'delegateJobTitle' => $finalListsOfScannedDelegate['delegateJobTitle'],
                                            'delegateEmailAddress' => $finalListsOfScannedDelegate['delegateEmailAddress'],
                                            'delegateBadgeType' => $finalListsOfScannedDelegate['delegateBadgeType'],
                                            'delegateScannedDate' => $finalListsOfScannedDelegate['delegateScannedDate'],
                                            'delegateScannedTime' => $finalListsOfScannedDelegate['delegateScannedTime'],
                                            'delegateScannedLocation' => $finalListsOfScannedDelegate['delegateScannedLocation'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $currentDate = Carbon::now()->format('Y-m-d');
            $fileName = $eventCategory . ' ' . $event->year . ' Categorized Scanned Delegates ' . '[' . $currentDate . '].csv';
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $columns = array(
                'Day',
                'Time',
                'Time description',
                'Transaction Id',
                'Company Name',
                'Salutation',
                'First name',
                'Middle name',
                'Last name',
                'Job title',
                'Email address',
                'Registration type',
                'Scanned date',
                'Scanned time',
                'Scanned location',
            );

            $callback = function () use ($finalData, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($finalData as $data) {
                    fputcsv(
                        $file,
                        array(
                            $data['delegateDayName'],
                            $data['delegateTime'],
                            $data['delegateTimeName'],
                            $data['delegateTransactionId'],
                            $data['delegateCompany'],
                            $data['delegateSalutation'],
                            $data['delegateFirstName'],
                            $data['delegateMiddleName'],
                            $data['delegateLastName'],
                            $data['delegateJobTitle'],
                            $data['delegateEmailAddress'],
                            $data['delegateBadgeType'],
                            $data['delegateScannedDate'],
                            $data['delegateScannedTime'],
                            $data['delegateScannedLocation'],
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

    public function getScannedDelegates($event)
    {
        $eventCategory = $event->category;
        $eventId = $event->id;
        foreach (config('app.eventCategories') as $eventCategoryC => $code) {
            if ($eventCategory == $eventCategoryC) {
                $eventCode = $code;
            }
        }

        $finalListsOfScannedDelegates = array();

        $scannedDelegates = ScannedDelegate::where('event_id', $eventId)->get();
        if ($scannedDelegates->isNotEmpty()) {
            foreach ($scannedDelegates as $scannedDelegate) {
                if ($scannedDelegate->delegate_type == "main") {
                    $mainDelegate = MainDelegate::where('id', $scannedDelegate->delegate_id)->first();

                    $transactionId = Transaction::where('event_id', $eventId)->where('delegate_id', $mainDelegate->id)->where('delegate_type', "main")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    $finalTransactionId = $event->year . $eventCode . $lastDigit;

                    if ($mainDelegate->alternative_company_name != null) {
                        $companyName = $mainDelegate->alternative_company_name;
                    } else {
                        $companyName = $mainDelegate->company_name;
                    }

                    $carbonDateTime = Carbon::parse($scannedDelegate->scanned_date_time);

                    array_push($finalListsOfScannedDelegates, [
                        'delegateTransactionId' => $finalTransactionId,
                        'delegateCompany' => $companyName,
                        'delegateSalutation' => $mainDelegate->salutation,
                        'delegateFirstName' => $mainDelegate->first_name,
                        'delegateMiddleName' => $mainDelegate->middle_name,
                        'delegateLastName' => $mainDelegate->last_name,
                        'delegateJobTitle' => $mainDelegate->job_title,
                        'delegateEmailAddress' => $mainDelegate->email_address,
                        'delegateBadgeType' => $mainDelegate->badge_type,
                        'delegateScannedDate' => $carbonDateTime->toDateString(),
                        'delegateScannedTime' => $carbonDateTime->toTimeString(),
                        'delegateScannedLocation' => $scannedDelegate->scanner_location,
                    ]);
                } else {
                    $additionalDelegate = AdditionalDelegate::where('id', $scannedDelegate->delegate_id)->first();

                    $transactionId = Transaction::where('event_id', $eventId)->where('delegate_id', $additionalDelegate->id)->where('delegate_type', "sub")->value('id');
                    $lastDigit = 1000 + intval($transactionId);

                    $finalTransactionId = $event->year . $eventCode . $lastDigit;

                    $mainDelegate = MainDelegate::where('id', $additionalDelegate->main_delegate_id)->first();

                    if ($mainDelegate->alternative_company_name != null) {
                        $mainDelegateCompany = $mainDelegate->alternative_company_name;
                    } else {
                        $mainDelegateCompany = $mainDelegate->company_name;
                    }

                    $carbonDateTime = Carbon::parse($scannedDelegate->scanned_date_time);

                    array_push($finalListsOfScannedDelegates, [
                        'delegateTransactionId' => $finalTransactionId,
                        'delegateCompany' => $mainDelegateCompany,
                        'delegateSalutation' => $additionalDelegate->salutation,
                        'delegateFirstName' => $additionalDelegate->first_name,
                        'delegateMiddleName' => $additionalDelegate->middle_name,
                        'delegateLastName' => $additionalDelegate->last_name,
                        'delegateJobTitle' => $additionalDelegate->job_title,
                        'delegateEmailAddress' => $additionalDelegate->email_address,
                        'delegateBadgeType' => $additionalDelegate->badge_type,
                        'delegateScannedDate' => $carbonDateTime->toDateString(),
                        'delegateScannedTime' => $carbonDateTime->toTimeString(),
                        'delegateScannedLocation' => $scannedDelegate->scanner_location,
                    ]);
                }
            }
        }
        return $finalListsOfScannedDelegates;
    }

    // =========================================================
    //                       RENDER APIS
    // =========================================================
    public function apiGetConfirmedDelegatesList($eventCategory, $eventYear)
    {
        $event = Event::where('category', $eventCategory)->where('year', $eventYear)->first();

        if ($event) {
            $delegateList = array();
            $eventId = $event->id;

            foreach (config('app.eventCategories') as $eventCategoryC => $code) {
                if ($eventCategory == $eventCategoryC) {
                    $eventCode = $code;
                }
            }

            $mainDelegates = MainDelegate::where('event_id', $eventId)->get();
            if (!$mainDelegates->isEmpty()) {
                foreach ($mainDelegates as $mainDelegate) {
                    if ($mainDelegate->access_type != AccessTypes::WORKSHOP_ONLY->value) {
                        $companyName = "";

                        if ($mainDelegate->alternative_company_name != null) {
                            $companyName = $mainDelegate->alternative_company_name;
                        } else {
                            $companyName = $mainDelegate->company_name;
                        }

                        if ($mainDelegate->delegate_replaced_by_id == null && (!$mainDelegate->delegate_refunded)) {
                            if ($mainDelegate->registration_status == "confirmed") {

                                $tempYear = Carbon::parse($mainDelegate->registered_date_time)->format('y');
                                $transactionId = Transaction::where('event_id', $eventId)->where('delegate_id', $mainDelegate->id)->where('delegate_type', "main")->value('id');
                                $lastDigit = 1000 + intval($transactionId);

                                $finalTransactionId = $eventYear . $eventCode . $lastDigit;
                                $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit;

                                array_push($delegateList, [
                                    'mainDelegateId' => $mainDelegate->id,
                                    'delegateId' => $mainDelegate->id,
                                    'delegateTransactionId' => $finalTransactionId,
                                    'delegateInvoiceNumber' => $invoiceNumber,
                                    'delegateType' => "main",
                                    'delegatePassType' => $mainDelegate->pass_type,
                                    'delegateCompany' => $companyName,
                                    'delegateCompanyCountry' => $mainDelegate->company_country,
                                    'delegateCompanyMobileNumber' => $mainDelegate->company_mobile_number,
                                    'delegateJobTitle' => $mainDelegate->job_title,
                                    'delegateSalutation' => $mainDelegate->salutation,
                                    'delegateFName' => $mainDelegate->first_name,
                                    'delegateMName' => $mainDelegate->middle_name,
                                    'delegateLName' => $mainDelegate->last_name,
                                    'delegateEmailAddress' => $mainDelegate->email_address,
                                    'delegateMobileNumber' => $mainDelegate->mobile_number,
                                    'delegateNationality' => $mainDelegate->nationality,
                                    'delegateCountry' => $mainDelegate->country,
                                    'delegateBadgeType' => $mainDelegate->badge_type,
                                ]);
                            }
                        }

                        $subDelegates = AdditionalDelegate::where('main_delegate_id', $mainDelegate->id)->get();

                        if (!$subDelegates->isEmpty()) {
                            foreach ($subDelegates as $subDelegate) {

                                if ($subDelegate->delegate_replaced_by_id == null && (!$subDelegate->delegate_refunded)) {
                                    if ($mainDelegate->registration_status == "confirmed") {

                                        $tempYear = Carbon::parse($subDelegate->registered_date_time)->format('y');
                                        $transactionId = Transaction::where('delegate_id', $subDelegate->id)->where('delegate_type', "sub")->value('id');
                                        $lastDigit = 1000 + intval($transactionId);
                                        $finalTransactionId = $eventYear . $eventCode . $lastDigit;

                                        $transactionId2 = Transaction::where('event_id', $eventId)->where('delegate_id', $subDelegate->main_delegate_id)->where('delegate_type', "main")->value('id');
                                        $lastDigit2 = 1000 + intval($transactionId2);
                                        $invoiceNumber = $eventCategory . $tempYear . "/" . $lastDigit2;

                                        array_push($delegateList, [
                                            'mainDelegateId' => $mainDelegate->id,
                                            'delegateId' => $subDelegate->id,
                                            'delegateTransactionId' => $finalTransactionId,
                                            'delegateInvoiceNumber' => $invoiceNumber,
                                            'delegateType' => "sub",
                                            'delegatePassType' => $mainDelegate->pass_type,
                                            'delegateCompany' => $companyName,
                                            'delegateCompanyCountry' => $mainDelegate->company_country,
                                            'delegateCompanyMobileNumber' => $mainDelegate->company_mobile_number,
                                            'delegateJobTitle' => $subDelegate->job_title,
                                            'delegateSalutation' => $subDelegate->salutation,
                                            'delegateFName' => $subDelegate->first_name,
                                            'delegateMName' => $subDelegate->middle_name,
                                            'delegateLName' => $subDelegate->last_name,
                                            'delegateEmailAddress' => $subDelegate->email_address,
                                            'delegateMobileNumber' => $subDelegate->mobile_number,
                                            'delegateNationality' => $subDelegate->nationality,
                                            'delegateCountry' => $subDelegate->country,
                                            'delegateBadgeType' => $subDelegate->badge_type,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return response()->json([
                'status' => 200,
                'message' => 'Confirmed attendees',
                'data' => $delegateList,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Event does not exist',
                'data' => null,
            ], 404);
        }
    }
}
