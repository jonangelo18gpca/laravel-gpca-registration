<?php

namespace App\Http\Livewire;

use App\Models\MainDelegate as MainDelegates;
use Livewire\Component;

class DigitalHelper extends Component
{
    public $event, $eventCode, $confirmedDelegates = array();
    public $inputtedData, $currentOption, $currentDelegate;
    public $showInputFormModal = false;
    public $showCollectYourBadgeDetails = false;
    public $showNotFoundText = false;

    protected $listeners = ['loadDelegates' => 'fetchConfirmedDelgates'];

    public function mount($event)
    {
        $this->event = $event;
        $this->eventCode = config('app.eventCategories')[$event->category];
    }

    public function render()
    {
        if ($this->event->year == "2026") {
            return view('livewire.digital-helper.2026.af.digital-helper');
        } else {
            return view('livewire.digital-helper.2025.af.digital-helper');
        }
    }

    public function optionClicked($option)
    {
        $this->currentOption = $option;
        $this->showCollectYourBadgeDetails = false;
        $this->showInputFormModal = true;
    }

    public function cancelClicked()
    {
        $this->showInputFormModal = false;
        $this->inputtedData = null;
        $this->currentOption = false;
        $this->resetValidation();
    }

    public function searchClicked()
    {
        $this->validate([
            'inputtedData' => 'required'
        ]);

        if ($this->currentOption == "email") {
            $this->currentDelegate = $this->searchViaEmail();
        } else if ($this->currentOption == "transactionId") {
            $this->currentDelegate = $this->searchViaTransactionId();
        } else {
            $this->currentDelegate = $this->searchViaName();
        }

        if ($this->currentDelegate) {
            $this->showInputFormModal = false;
            $this->currentOption = false;
            $this->inputtedData = null;
            $this->showCollectYourBadgeDetails = true;
        } else {
            $this->showNotFoundText = true;
        }
    }

    public function tryAgainClicked()
    {
        $this->showNotFoundText = false;
    }

    public function searchAgainClicked()
    {
        $this->showCollectYourBadgeDetails = false;
        $this->currentDelegate = null;
    }

    public function searchViaName()
    {
        $currentDelegate = null;

        foreach ($this->confirmedDelegates as $delegate) {
            if (strtolower(trim($delegate['name'])) == strtolower(trim($this->inputtedData))) {
                $currentDelegate = $delegate;
                break;
            }
        }

        return $currentDelegate;
    }

    public function searchViaTransactionId()
    {
        $currentDelegate = null;

        foreach ($this->confirmedDelegates as $delegate) {
            if (strtolower(trim($delegate['transactionId'])) == strtolower(trim($this->inputtedData))) {
                $currentDelegate = $delegate;
                break;
            }
        }

        return $currentDelegate;
    }

    public function searchViaEmail()
    {
        $currentDelegate = null;

        foreach ($this->confirmedDelegates as $delegate) {
            if (strtolower(trim($delegate['emailAddress'])) == strtolower(trim($this->inputtedData))) {
                $currentDelegate = $delegate;
                break;
            }
        }

        return $currentDelegate;
    }



    public function getTextAndVisualDetails2025($companyName, $badgeType, $isPrinted)
    {
        $letterCounters1 = [
            'ABC' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/abc-2.png',
            ],
            'DEF' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/def-2.png',
            ],
            'GHI' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/ghi-2.png',
            ],
            'JKLMN' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/jklmn-2.png',
            ],
            'OPQ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/opq-2.png',
            ],
            'SR' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/rs.png',
            ],
            'TUV' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/tuv.png',
            ],
            'WXYZ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/wxyz-2.png',
            ],
        ];

        $howToCollectYourBadge = null;
        $imageLinks = [];
        $firstLetter = strtoupper(substr($companyName, 0, 1));

        if (strtoupper($badgeType) == "VIP" || strtoupper($badgeType) == "SPEAKER") {
            $imageLinks = [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/vip-speakers-2.png',
            ];
            $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter \"VIP & SPEAKERS\". You can find the counter assignment in the image below:";
        } else if (strtoupper($badgeType) == "YOUTH COUNCIL" || strtoupper($badgeType) == "YOUTH FORUM") {
            $imageLinks = [
                'https://www.gpcaforum.com/wp-content/uploads/2025/12/youth-forum-2.png',
            ];
            $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter \"YOUTH FORUM\". You can find the counter assignment in the image below:";
        } else {
            if (strtoupper($companyName) == "GPIC") {
                $imageLinks = [
                    'https://www.gpcaforum.com/wp-content/uploads/2025/12/gpic-2.png',
                ];

                $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter 'GPIC'. You can find the counter assignment based on your company's first letter in the image below";
            } else if (strtoupper($companyName) == "3P GULF GROUP") {
                $imageLinks = [
                    'https://www.gpcaforum.com/wp-content/uploads/2025/12/abc-2.png',
                ];

                $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter 'ABC'.";
            } else {
                $counterGroup = null;
                foreach ($letterCounters1 as $counterKey => $counter) {
                    $counterArrayOfLetters = str_split($counterKey);
                    foreach ($counterArrayOfLetters as $letter) {
                        if ($firstLetter == $letter) {
                            $counterGroup = $counterKey;
                            $imageLinks = $counter;
                            break;
                        }
                    }
                    if ($counterGroup != null) {
                        break;
                    }
                }

                $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter '$counterGroup'. You can find the counter assignment based on your company's first letter in the image below";
            }
        }

        return [
            'howToCollectYourBadge' => $howToCollectYourBadge,
            'imageLinks' => $imageLinks,
        ];
    }


  public function getTextAndVisualDetails2026($companyName, $badgeType, $isPrinted)
{
    $base = 'https://www.gpcaforum.com/wp-content/uploads/2026/11/';
    $img1 = $base . 'vip1.png';        // VIP & Speaker + ABCD
    $img2 = $base . 'companies1.png';  // EFGH / IJKL
    $img3 = $base . 'companies2.png';  // MNOP / QRST
    $img4 = $base . 'companies3.png';  // UVWXYZ / REGISTRATION (TODO: confirm, probably companies3.png)

    $letterCounters = [
        'ABCD'   => [$img1],
        'EFGH'   => [$img2],
        'IJKL'   => [$img2],
        'MNOP'   => [$img3],
        'QRST'   => [$img3],
        'UVWXYZ' => [$img4],
    ];

    $howToCollectYourBadge = null;
    $imageLinks = [];
    $badge = strtoupper($badgeType);
    $firstLetter = strtoupper(substr(trim($companyName), 0, 1));

    if ($badge == "VIP" || $badge == "SPEAKER") {
        $imageLinks = [$img1];
        $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter \"VIP & SPEAKERS\". You can find the counter assignment in the image below:";
    } else {
        $counterGroup = null;

        if (preg_match('/^[A-Z]$/', $firstLetter)) {
            foreach ($letterCounters as $counterKey => $counter) {
                if (strpos($counterKey, $firstLetter) !== false) {
                    $counterGroup = $counterKey;
                    $imageLinks = $counter;
                    break;
                }
            }
        }

        if ($counterGroup) {
            $howToCollectYourBadge = "Please proceed to the Registration Area and line up at counter '$counterGroup'. You can find the counter assignment based on your company's first letter in the image below";
        } else {
            $imageLinks = [$img4];
            $howToCollectYourBadge = "Please proceed to the Registration counter. You can find the counter assignment in the image below:";
        }
    }

    return [
        'howToCollectYourBadge' => $howToCollectYourBadge,
        'imageLinks' => $imageLinks,
    ];
}




    private function getBadgeDetails($companyName, $badgeType, $isPrinted)
    {
        if ($this->event->year == "2026") {
            return $this->getTextAndVisualDetails2026($companyName, $badgeType, $isPrinted);
        }
        return $this->getTextAndVisualDetails2025($companyName, $badgeType, $isPrinted);
    }



    public function getTextAndVisualDetails2024($companyName, $badgeType, $isPrinted)
    {
        $letterCounters1 = [
            'ABCD' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-a-to-d.png',
            ],
            'EFGHI' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-e-to-i.png',
            ],
            'JKLMN' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-j-to-n.png',
            ],
            'OPQ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-o-to-q.png',
            ],
            'S' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-s.png',
            ],
            'RT' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-rt.png',
            ],
            'UVWXYZ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-u-to-z.png',
            ],
        ];

        $letterCounters2 = [
            'ABCDEFGHIJ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/exhibitor-counter.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/exhibitor-a-to-j.png'
            ],
            'KLMNOPQRSTUVWXYZ' => [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/exhibitor-counter.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/exhibitor-k-to-z.png',
            ],
        ];

        $howToCollectYourBadge = null;
        $imageLinks = [];
        $firstLetter = strtoupper(substr($companyName, 0, 1));

        if (strtoupper($badgeType) == "VIP" || strtoupper($badgeType) == "SPEAKER") {
            $imageLinks = [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/vip-counter-2.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/vip-counter-1.png',
            ];
            $howToCollectYourBadge = "Please proceed to the Madinat Al Ifran Theatre Foyer and line up at counter \"VIP/SPEAKERS\". You can find the counter assignment in the image below:";
        } else if (strtoupper($badgeType) == "EXHIBITOR" || strtoupper($badgeType) == "MEDIA") {
            if ($isPrinted == true) {
                $counterGroup = null;
                foreach ($letterCounters2 as $counterKey => $counter) {
                    $counterArrayOfLetters = str_split($counterKey);
                    foreach ($counterArrayOfLetters as $letter) {
                        if ($firstLetter == $letter) {
                            $counterGroup = $counterKey;
                            $imageLinks = $counter;
                            break;
                        }
                    }
                    if ($counterGroup != null) {
                        break;
                    }
                }

                $howToCollectYourBadge = "Please proceed to the Exhibition Foyer and line up at counter '{$counterKey}'. You can find the counter assignment based on your company's first letter in the image below";
            } else {
                $imageLinks = [
                    'https://www.gpcaforum.com/wp-content/uploads/2024/12/fast-track-1.png',
                    'https://www.gpcaforum.com/wp-content/uploads/2024/12/fast-track-2.png'
                ];
                $howToCollectYourBadge = "Please proceed to the Exhibition Foyer and you can look for the fast track counter to print your badge";
            }
        } else if (strtoupper($badgeType) == "YOUTH COUNCIL" || strtoupper($badgeType) == "YOUTH FORUM") {
            $imageLinks = [
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-yf.png',
            ];
            $howToCollectYourBadge = "Please proceed to the Madinat Al Ifran Theatre Foyer and line up at counter \"Youth\". You can find the counter assignment in the image below:";
        } else {
            if ($isPrinted == true) {
                if (strtoupper($companyName) == "3P GULF GROUP") {
                    $imageLinks = [
                        'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-foyer-counters.png',
                        'https://www.gpcaforum.com/wp-content/uploads/2024/12/madinat-o-to-q.png',
                    ];

                    $howToCollectYourBadge = "Please proceed to the Madinat Al Ifran Theatre Foyer and line up at counter 'OPQ'. You can find the counter assignment based on your company's first letter in the image below";
                } else {
                    $counterGroup = null;
                    foreach ($letterCounters1 as $counterKey => $counter) {
                        $counterArrayOfLetters = str_split($counterKey);
                        foreach ($counterArrayOfLetters as $letter) {
                            if ($firstLetter == $letter) {
                                $counterGroup = $counterKey;
                                $imageLinks = $counter;
                                break;
                            }
                        }
                        if ($counterGroup != null) {
                            break;
                        }
                    }

                    $howToCollectYourBadge = "Please proceed to the Madinat Al Ifran Theatre Foyer and line up at counter '$counterGroup'. You can find the counter assignment based on your company's first letter in the image below";
                }
            } else {
                $imageLinks = [
                    'https://www.gpcaforum.com/wp-content/uploads/2024/12/fast-track-1.png',
                    'https://www.gpcaforum.com/wp-content/uploads/2024/12/fast-track-2.png'
                ];
                $howToCollectYourBadge = "Please proceed to the Exhibition Foyer and you can look for the fast track counter to print your badge";
            }
        }

        return [
            'howToCollectYourBadge' => $howToCollectYourBadge,
            'imageLinks' => $imageLinks,
        ];
    }


    public function fetchConfirmedDelgates()
    {
        $allDelegates = array();
        $mainDelegates = MainDelegates::with(['additionalDelegates', 'transaction', 'printedBadges', 'scannedBadges'])->where('event_id', $this->event->id)->get();
        if ($mainDelegates->isNotEmpty()) {
            foreach ($mainDelegates as $mainDelegate) {

                $companyName = $mainDelegate->alternative_company_name ?? $mainDelegate->company_name;

                if ($mainDelegate->delegate_replaced_by_id == null && (!$mainDelegate->delegate_refunded)) {
                    if ($mainDelegate->registration_status == "confirmed") {

                        $transactionId = $mainDelegate->transaction->id;
                        $lastDigit = 1000 + intval($transactionId);
                        $finalTransactionId = $this->event->year . $this->eventCode . $lastDigit;

                        $delegateSalutation = null;
                        if ($mainDelegate->salutation == "Dr." || $mainDelegate->salutation == "Prof.") {
                            $delegateSalutation = $mainDelegate->salutation;
                        }

                        $name = $delegateSalutation . ' ' . $mainDelegate->first_name . ' ' . $mainDelegate->middle_name . ' ' . $mainDelegate->last_name;

                        $isPrinted = false;
                        $isCollected = false;
                        $isCollectedBy = null;
                        if ($mainDelegate->printedBadges->isNotEmpty()) {
                            foreach ($mainDelegate->printedBadges as $printedBadge) {
                                $isPrinted = true;

                                if ($printedBadge->collected_by) {
                                    $isCollected = true;
                                    $isCollectedBy = $printedBadge->collected_by;
                                }
                            }
                        }

                        $visuals = [];
                        $howToCollectYourBadge = null;

                        if ($isCollectedBy != null) {
                            $howToCollectYourBadge = $isCollectedBy;
                        } else {
                            // $data = $this->getTextAndVisualDetails2025($companyName, $mainDelegate->badge_type, $isPrinted);
                            $data = $this->getBadgeDetails($companyName, $mainDelegate->badge_type, $isPrinted);
                            $howToCollectYourBadge = $data['howToCollectYourBadge'];
                            $visuals = $data['imageLinks'];
                        }

                        $finalDelegate = [
                            'transactionId' => $finalTransactionId,
                            'name' => $this->formatFullName($name),
                            'jobTitle' => $mainDelegate->job_title,
                            'companyName' => $companyName,
                            'badgeType' => $mainDelegate->badge_type,
                            'emailAddress' => $mainDelegate->email_address,
                            'isPrinted' => $isPrinted,
                            'isCollected' => $isCollected,
                            'howToCollectYourBadge' => $howToCollectYourBadge,
                            'visuals' => $visuals,
                        ];

                        array_push($allDelegates, $finalDelegate);
                    }
                }

                if (!$mainDelegate->additionalDelegates->isEmpty()) {
                    foreach ($mainDelegate->additionalDelegates as $subDelegate) {

                        if ($subDelegate->delegate_replaced_by_id == null && (!$subDelegate->delegate_refunded)) {
                            if ($mainDelegate->registration_status == "confirmed") {

                                $transactionId = $subDelegate->transaction->id;
                                $lastDigit = 1000 + intval($transactionId);
                                $finalTransactionId = $this->event->year . $this->eventCode . $lastDigit;

                                $delegateSalutation = null;
                                if ($subDelegate->salutation == "Dr." || $subDelegate->salutation == "Prof.") {
                                    $delegateSalutation = $subDelegate->salutation;
                                }

                                $name = $delegateSalutation . ' ' . $subDelegate->first_name . ' ' . $subDelegate->middle_name . ' ' . $subDelegate->last_name;

                                $isPrinted = false;
                                $isCollected = false;
                                $isCollectedBy = null;

                                if ($subDelegate->printedBadges->isNotEmpty()) {
                                    foreach ($subDelegate->printedBadges as $printedBadge) {
                                        $isPrinted = true;

                                        if ($printedBadge->collected) {
                                            $isCollected = true;
                                            $isCollectedBy = $printedBadge->collected_by;
                                        }
                                    }
                                }
                                $visuals = [];
                                $howToCollectYourBadge = null;

                                if ($isCollectedBy != null) {
                                    $howToCollectYourBadge = $isCollectedBy;
                                } else {
                                    // $data = $this->getTextAndVisualDetails2025($companyName, $subDelegate->badge_type, $isPrinted);
                                    $data = $this->getBadgeDetails($companyName, $subDelegate->badge_type, $isPrinted);
                                    $howToCollectYourBadge = $data['howToCollectYourBadge'];
                                    $visuals = $data['imageLinks'];
                                }

                                $finalDelegate = [
                                    'transactionId' => $finalTransactionId,
                                    'name' => $this->formatFullName($name),
                                    'jobTitle' => $subDelegate->job_title,
                                    'companyName' => $companyName,
                                    'badgeType' => $subDelegate->badge_type,
                                    'emailAddress' => $subDelegate->email_address,
                                    'isPrinted' => $isPrinted,
                                    'isCollected' => $isCollected,
                                    'howToCollectYourBadge' => $howToCollectYourBadge,
                                    'visuals' => $visuals,
                                ];

                                array_push($allDelegates, $finalDelegate);
                            }
                        }
                    }
                }
            }
        }

        usort($allDelegates, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $this->confirmedDelegates = $allDelegates;
        $this->dispatchBrowserEvent('remove-dh-loading-screen');
    }

    function formatFullName($name)
    {
        return preg_replace('/\s+/', ' ', trim($name));
    }
}
