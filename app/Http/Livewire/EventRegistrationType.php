<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event as Events;
use App\Models\EventRegistrationType as EventRegistrationTypes;

class EventRegistrationType extends Component
{
    public $eventCategory, $eventId;
    public $event;
    public $registrationTypes;
    public $updateRegistrationType = false;

    public $registrationTypeId, $registrationType, $badgeFooterFrontName, $badgeFooterFrontBGColor, $badgeFooterFrontTextColor, $badgeFooterBackName, $badgeFooterBackBGColor, $badgeFooterBackTextColor;

    public $badgeView = false;
    public $badgeViewFFText, $badgeViewFBText, $badgeViewFFBGColor, $badgeViewFBBGColor, $badgeViewFFTextColor, $badgeViewFBTextColor;

    protected $listeners = ['updateRegistrationTypeConfirmed' => 'updateRegistrationType', 'addRegistrationTypeConfirmed' => 'addRegistrationType'];

    public function mount($eventCategory, $eventId)
    {
        $this->event = Events::where('id', $eventId)->where('category', $eventCategory)->first();
        $this->eventCategory = $eventCategory;
        $this->eventId = $eventId;



        // ✅ ADD THESE DEFAULTS
        $this->badgeFooterFrontBGColor = '#000000';
        $this->badgeFooterFrontTextColor = '#ffffff';

        $this->badgeFooterBackBGColor = '#000000';
        $this->badgeFooterBackTextColor = '#ffffff';
    }

    public function render()
    {
        $this->registrationTypes = EventRegistrationTypes::where('event_id', $this->eventId)->where('event_category', $this->eventCategory)->get();
        return view('livewire.admin.events.registration-type.event-registration-type');
    }

    public function addRegistrationTypeConfirmation()
    {
        $this->validate(
            [
                'registrationType' => 'required',
                'badgeFooterFrontName' => 'required',
                'badgeFooterFrontBGColor' => 'required',
                'badgeFooterFrontTextColor' => 'required',
                'badgeFooterBackName' => 'required',
                'badgeFooterBackBGColor' => 'required',
                'badgeFooterBackTextColor' => 'required',
            ]
        );

        $this->dispatchBrowserEvent('swal:add-registration-type-confirmation', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => "",
        ]);
    }


    public function addRegistrationType()
    {
        EventRegistrationTypes::create([
            'event_id' => $this->eventId,
            'event_category' => $this->eventCategory,
            'registration_type' => $this->registrationType,
            'badge_footer_front_name' => $this->badgeFooterFrontName,
            'badge_footer_front_bg_color' => $this->badgeFooterFrontBGColor,
            'badge_footer_front_text_color' => $this->badgeFooterFrontTextColor,
            'badge_footer_back_name' => $this->badgeFooterBackName,
            'badge_footer_back_bg_color' => $this->badgeFooterBackBGColor,
            'badge_footer_back_text_color' => $this->badgeFooterBackTextColor,
            'active' => true,
        ]);

        $this->registrationType = null;
        $this->badgeFooterFrontName = null;
        $this->badgeFooterFrontBGColor = '#000000';
        $this->badgeFooterFrontTextColor = '#ffffff';
        $this->badgeFooterBackName = null;
        $this->badgeFooterBackBGColor = '#000000';
        $this->badgeFooterBackTextColor = '#ffffff';

        $this->dispatchBrowserEvent('swal:add-registration-type', [
            'type' => 'success',
            'message' => 'Registration type added Successfully!',
            'text' => ''
        ]);
    }

    public function updateStatus($registrationTypeId, $registrationTypeActive)
    {
        EventRegistrationTypes::find($registrationTypeId)->fill(
            [
                'active' => !$registrationTypeActive,
            ],
        )->save();
    }


    public function showEditRegistrationType($registrationTypeId)
    {
        $registrationType = EventRegistrationTypes::findOrFail($registrationTypeId);
        $this->registrationType = $registrationType->registration_type;
        $this->badgeFooterFrontName = $registrationType->badge_footer_front_name;
        $this->badgeFooterFrontBGColor = $registrationType->badge_footer_front_bg_color;
        $this->badgeFooterFrontTextColor = $registrationType->badge_footer_front_text_color;
        $this->badgeFooterBackName = $registrationType->badge_footer_back_name;
        $this->badgeFooterBackBGColor = $registrationType->badge_footer_back_bg_color;
        $this->badgeFooterBackTextColor = $registrationType->badge_footer_back_text_color;
        $this->registrationTypeId = $registrationType->id;
        $this->updateRegistrationType = true;
    }

    public function cancelEditRegistrationType()
    {
        $this->registrationType = null;
        $this->badgeFooterFrontName = null;
        $this->badgeFooterFrontBGColor = null;
        $this->badgeFooterFrontTextColor = null;
        $this->badgeFooterBackName = null;
        $this->badgeFooterBackBGColor = null;
        $this->badgeFooterBackTextColor = null;
        $this->registrationTypeId = null;
        $this->updateRegistrationType = false;
    }

    public function updateRegistrationTypeConfirmation()
    {
        $this->validate(
            [
                'registrationType' => 'required',
                'badgeFooterFrontName' => 'required',
                'badgeFooterFrontBGColor' => 'required',
                'badgeFooterFrontTextColor' => 'required',
                'badgeFooterBackName' => 'required',
                'badgeFooterBackBGColor' => 'required',
                'badgeFooterBackTextColor' => 'required',
            ]
        );

        $this->dispatchBrowserEvent('swal:update-registration-type-confirmation', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => "",
        ]);
    }


    public function updateRegistrationType()
    {
        EventRegistrationTypes::find($this->registrationTypeId)->fill([
            'registration_type' => $this->registrationType,
            'badge_footer_front_name' => $this->badgeFooterFrontName,
            'badge_footer_front_bg_color' => $this->badgeFooterFrontBGColor,
            'badge_footer_front_text_color' => $this->badgeFooterFrontTextColor,
            'badge_footer_back_name' => $this->badgeFooterBackName,
            'badge_footer_back_bg_color' => $this->badgeFooterBackBGColor,
            'badge_footer_back_text_color' => $this->badgeFooterBackTextColor,
        ])->save();

        $this->registrationType = null;
        $this->badgeFooterFrontName = null;
        $this->badgeFooterFrontBGColor = null;
        $this->badgeFooterFrontTextColor = null;
        $this->badgeFooterBackName = null;
        $this->badgeFooterBackBGColor = null;
        $this->badgeFooterBackTextColor = null;
        $this->registrationTypeId = null;
        $this->updateRegistrationType = false;

        $this->dispatchBrowserEvent('swal:update-registration-type', [
            'type' => 'success',
            'message' => 'Registration Type updated successfully!',
            'text' => ''
        ]);
    }

    public function showSampleBadge($registrationTypeId)
    {
        $registrationType = EventRegistrationTypes::findOrFail($registrationTypeId);

        $this->badgeViewFFText = $registrationType->badge_footer_front_name;
        $this->badgeViewFBText = $registrationType->badge_footer_back_name;

        $this->badgeViewFFBGColor = $registrationType->badge_footer_front_bg_color;
        $this->badgeViewFBBGColor = $registrationType->badge_footer_back_bg_color;

        $this->badgeViewFFTextColor = $registrationType->badge_footer_front_text_color;
        $this->badgeViewFBTextColor = $registrationType->badge_footer_back_text_color;

        $this->badgeView = true;
    }

    public function closeSampleBadge()
    {
        $this->badgeViewFFText = null;
        $this->badgeViewFBText = null;

        $this->badgeViewFFBGColor = null;
        $this->badgeViewFBBGColor = null;

        $this->badgeViewFFTextColor = null;
        $this->badgeViewFBTextColor = null;

        $this->badgeView = false;
    }


    public function updatedRegistrationType($value)
{
    // Only auto-fill if empty to avoid overwriting manual edits

    if (empty($this->badgeFooterFrontName)) {
        $this->badgeFooterFrontName = $value;
    }

    if (empty($this->badgeFooterBackName)) {
        $this->badgeFooterBackName = $value;
    }
}
}
