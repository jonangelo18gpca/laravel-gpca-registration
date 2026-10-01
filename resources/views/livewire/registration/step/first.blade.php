<div class="mx-5">
    @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
        @if (
            $event->wo_eb_full_member_rate != null ||
                $event->wo_eb_member_rate != null ||
                $event->wo_eb_nmember_rate != null ||
                $event->wo_std_full_member_rate != null ||
                $event->wo_std_member_rate != null ||
                $event->wo_std_nmember_rate != null)
            @include('livewire.registration.step.rates_table.wo_rates')
        @endif

        @if (
            $event->co_eb_full_member_rate != null ||
                $event->co_eb_member_rate != null ||
                $event->co_eb_nmember_rate != null ||
                $event->co_std_full_member_rate != null ||
                $event->co_std_member_rate != null ||
                $event->co_std_nmember_rate != null)
            <div class="mt-8"></div>
            @include('livewire.registration.step.rates_table.co_rates')
        @endif

        {{-- <div class="mt-8"></div>
        @include('livewire.registration.step.rates_table.fe_rates') --}}

        @if ($event->category != 'AF')
            <div class="mt-8"></div>
            @include('livewire.registration.step.rates_table.fe_rates')
        @endif

        @if ($event->category == 'IPAW' && $event->year == '2025')
            <div class="mt-5">
                Academia professionals and students are eligible for a discounted delegate registration rate. To know
                more, please contact Mohamed Zaman at <a href="mailto:zaman@gpca.org.ae"
                    target="_blank"><strong>zaman@gpca.org.ae</strong></a>.
            </div>
        @endif
    @endif


    <style>
        .af-pass-options-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (max-width: 767px) {
            .af-pass-options-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if ($event->category == 'AF')
        {{-- Youth promo modal --}}
        <div id="youth-promo-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 sm:p-6" role="dialog"
            aria-modal="true" aria-labelledby="youth-promo-modal-title">

            {{-- Dark overlay --}}
            <div class="fixed inset-0 bg-black bg-opacity-60" onclick="closeYouthPromoModal()">
            </div>

            {{-- Modal content --}}
            <div class="relative z-10 my-auto w-full max-w-md rounded-lg bg-white p-5 shadow-2xl sm:p-6">
                <button type="button" onclick="closeYouthPromoModal()"
                    class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full
                   text-2xl leading-none text-gray-500 transition hover:bg-gray-100 hover:text-black"
                    aria-label="Close promo code modal">
                    &times;
                </button>

                <h3 id="youth-promo-modal-title"
                    class="pr-8 text-center text-xl font-bold text-registrationPrimaryColor sm:text-2xl">
                    Youth Promo Code
                </h3>

                <p class="mt-3 text-center text-sm leading-6 text-gray-600">
                    Use this code during registration to receive the Youth Pass rate.
                </p>

                <div class="mt-5 rounded-md bg-registrationPrimaryColor px-4 py-4 text-center">
                    <p class="text-xs uppercase tracking-widest text-white opacity-80">
                        Promo Code
                    </p>

                    <p id="youth-promo-code"
                        class="mt-1 break-all text-2xl font-bold tracking-wider text-white sm:text-3xl">
                        YOUTH700
                    </p>
                </div>

                <button type="button" id="copy-youth-promo-button" onclick="copyYouthPromoCode()"
                    class="mt-5 w-full rounded-md border-2 border-registrationPrimaryColor
                   px-4 py-3 font-bold text-registrationPrimaryColor transition duration-200
                   hover:bg-registrationPrimaryColor hover:text-white
                   focus:outline-none focus:ring-2 focus:ring-registrationPrimaryColor focus:ring-offset-2">
                    Copy Code
                </button>
            </div>
        </div>

        <script>
            function openYouthPromoModal() {
                document.getElementById('youth-promo-modal').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeYouthPromoModal() {
                document.getElementById('youth-promo-modal').classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            function copyYouthPromoCode() {
                const promoCode = document.getElementById('youth-promo-code').innerText.trim();
                const copyButton = document.getElementById('copy-youth-promo-button');

                navigator.clipboard.writeText(promoCode)
                    .then(function() {
                        copyButton.innerText = 'Copied!';
                        copyButton.classList.remove('text-registrationPrimaryColor');
                        copyButton.classList.add('bg-registrationPrimaryColor', 'text-white');

                        setTimeout(function() {
                            copyButton.innerText = 'Copy Code';
                            copyButton.classList.remove('bg-registrationPrimaryColor', 'text-white');
                            copyButton.classList.add('text-registrationPrimaryColor');
                        }, 2000);
                    })
                    .catch(function() {
                        copyButton.innerText = 'Unable to copy — copy manually';

                        setTimeout(function() {
                            copyButton.innerText = 'Copy Code';
                        }, 2500);
                    });
            }
        </script>


        <div class="bg-gray-200 rounded-lg p-5 sm:p-6 md:p-8">
            <h1 class="text-2xl text-registrationPrimaryColor font-bold text-center">
                REGISTRATION OPTIONS
            </h1>
            <div class="bg-white rounded-md mt-5 p-4 md:p-6">
                <div class="af-pass-options-grid">
                    {{-- Delegate Pass --}}
                    <div class="border border-gray-300 rounded-lg p-4 sm:p-5 md:p-6 flex flex-col min-w-0">


                        <img src="https://www.gpcaforum.com/wp-content/uploads/2026/09/yfbanner-scaled.png"
                            alt="Delegate Pass" class="mb-6 h-auto w-full rounded-lg object-cover">

                        <h2
                            class="text-xl text-registrationPrimaryColor font-bold text-center pb-4 mb-5 my-5 border-b border-gray-200">
                            DELEGATE PASS
                        </h2>

                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3 pl-4">
                                Pass includes:
                            </p>

                            <div class="px-4 space-y-3">
                                <p class="text-black">• Opening Ceremony</p>
                                <p class="text-black">•	Ministerial sessions</p>
                                <p class="text-black">•	Keynote addresses</p>
                                <p class="text-black">•	Access to the exhibition halls</p>
                                <p class="text-black">•	Delegate Lunch</p>
                                <p class="text-black">•	Welcome Reception</p>
                                <p class="text-black">•	Networking sessions</p>
                                <p class="text-black">•	GPCA Features</p>
                                <p class="text-black">•	Meeting rooms (upon invitation from the partner)</p>
                                <p class="text-black">•	Closing Ceremony</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 mt-8 pt-5">
                            <p class="text-registrationPrimaryColor font-bold mb-3">
                                Rate:
                            </p>

                            <div class="flex justify-between items-center bg-gray-50 rounded-md py-3 px-4 mb-3">
                                <span class="text-black font-medium">Member</span>

                                <span class="font-bold text-registrationPrimaryColor whitespace-nowrap">
                                    $ {{ number_format($event->std_member_rate ?? 0, 2) }} + 5% UAE VAT
                                </span>
                            </div>

                            <div class="flex justify-between items-center bg-gray-50 rounded-md py-3 px-4">
                                <span class="text-black font-medium">Non-Member</span>

                                <span class="font-bold text-registrationPrimaryColor whitespace-nowrap">
                                    $ {{ number_format($event->std_nmember_rate ?? 0, 2) }} + 5% UAE VAT
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Youth --}}
                    <div class="border border-gray-300 rounded-lg p-4 sm:p-5 md:p-6 flex flex-col min-w-0">

                        <img src="https://www.gpcaforum.com/wp-content/uploads/2026/09/afbanner-scaled.png"
                            alt="Youth Pass" class="mb-6 h-auto w-full rounded-lg object-cover">
                        <h2
                            class="text-xl text-registrationPrimaryColor font-bold text-center pb-4 mb-5 my-5 border-b border-gray-200">
                            YOUTH PASS
                        </h2>

                        {{-- Pass includes --}}
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3 pl-4">
                                Pass includes:
                            </p>

                            <div class="px-4 space-y-3">

                                <p class="text-black">•	Opening Ceremony</p>
                                <p class="text-black">•	Access to the Youth Forum sessions</p>
                                <p class="text-black">•	Youth lunches and breaks</p>
                                <p class="text-black">•	Welcome Reception</p>
                                <p class="text-black">•	Networking sessions</p>
                                <p class="text-black">•	Youth Forum Activations and Features</p>
                                <p class="text-black">•	Closing Ceremony</p>

                               
                            </div>
                        </div>

                        {{-- Rate and promo code --}}
                        <div class="border-t border-gray-200 mt-8 pt-5">
                            <p class="text-registrationPrimaryColor font-bold mb-3">
                                Rate:
                            </p>

                            <div class="flex justify-between items-center bg-gray-50 rounded-md py-3 px-4">
                                <span class="text-black font-medium">Youth Pass</span>

                                <span class="font-bold text-registrationPrimaryColor whitespace-nowrap">
                                    $ 700.00 + 5% UAE VAT
                                </span>
                            </div>

                            {{-- Clickable promo code --}}
                            {{-- Promo code button --}}
                            <button type="button" onclick="openYouthPromoModal()"
                                class="mt-5 w-full flex items-center justify-center rounded-md border-2
           border-registrationPrimaryColor px-4 py-3 font-bold
           text-registrationPrimaryColor transition duration-200
           hover:bg-registrationPrimaryColor hover:text-white
           focus:outline-none focus:ring-2 focus:ring-registrationPrimaryColor focus:ring-offset-2">
                                Reveal Promo Code
                            </button>

                
                        </div>


                              
                    </div>

                    

                    

                </div>
                
                                     <div class="text-registrationPrimaryColor italic text-sm mt-5 w-full text-center ">
      For group booking discount, please contact our sales team at <a href="mailto:forumregistration@gpca.org.ae" class="text-registrationPrimaryColor underline">forumregistration@gpca.org.ae</a> or call +971 4 5106666 ext. 103, 106.
            </div>
            </div>
        </div>
    @endif


    <div class="grid grid-cols-2 gap-5 mt-10">
        @if ($delegateFees->isNotEmpty())
            <div class="col-span-2 lg:col-span-1">
                <div class="bg-gray-200 py-4 px-2">
                    <h1 class="text-2xl text-registrationPrimaryColor font-bold text-center">DELEGATE FEE INCLUDES:</h1>
                    <div class="bg-white mx-1 mt-5 px-14 py-5">
                        <ul class="list-disc">
                            @foreach ($delegateFees as $delegateFee)
                                <li class="text-registrationPrimaryColor"><span
                                        class="text-black">{{ $delegateFee->description }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                {{-- @if ($event->category == 'AF' && $event->year == '2023')
                    <p class="mt-5">If you are interested with the GPCA spouse program, please click here to <a
                            href="https://www.gpcaforum.com/spouse-program/" target="_blank"
                            class="text-blue-600 hover:underline font-semibold">learn more</a> and <a
                            href="https://www.gpcaregistration.com/register/2023/AFS/11" target="_blank"
                            class="text-blue-600 hover:underline font-semibold">register.</a></p>
                @endif --}}

                <div class="mt-5">
                    <input type="checkbox" wire:model.lazy="termsCondition" id="terms-condition">
                    <label for="terms-condition">I agree to the <a
                            href="https://www.gpca.org.ae/terms-and-condition-events-registration/" target="_blank"
                            class="text-registrationPrimaryColor underline">Terms and Conditions</a> and <a
                            href="https://www.gpca.org.ae/privacy-policy/" target="_blank"
                            class="text-registrationPrimaryColor underline">Privacy Policy</a>.</label>

                    @error('termsCondition')
                        <div class="text-red-500 text-xs italic mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <p class="mt-5">For inquiries or to speak with a member of our team, please contact <strong>Faheem
                        Chowdhury</strong>, <em>Head of Events</em>, at <a
                        href="mailto:faheem@gpca.org.ae">faheem@gpca.org.ae</a> or call +971 4 451 0666 ext. 122.</p>
            </div>
        @endif


        <div
            class="col-span-2 {{ $delegateFees->isNotEmpty() ? 'lg:col-span-1' : 'lg:col-span-2' }} lg:col-span-1 flex flex-col gap-5">

            @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
                <div class="bg-gray-200 py-4 px-2">
                    <h1 class="text-2xl text-registrationPrimaryColor font-bold text-center">DO YOU WISH TO BECOME A
                        MEMBER?
                    </h1>
                    <div class="bg-white mx-1 mt-5 p-5 space-y-5">
                        <p>Do you wish to become a member and avail preferred rates and other benefit?</p>
                        <p>If <strong>YES</strong>, please contact our sales team: members@gpca.org.ae</p>
                        <p>If <strong>NO</strong>, please proceed with the registration</p>
                    </div>
                </div>
            @endif







            <div class="bg-gray-200 py-4 px-2">
                <h1 class="text-2xl text-registrationPrimaryColor font-bold text-center">DELEGATE PASS TYPE</h1>
                <div class="bg-white mx-1 mt-5 p-5">
                    <div class="flex flex-row justify-center items-center gap-5">
                        @if ($event->eb_full_member_rate != null || $event->std_full_member_rate != null)
                            <button wire:click.prevent="fullMemberClicked"
                                class="{{ $delegatePassType == 'fullMember' ? 'bg-registrationPrimaryColor text-white' : 'hover:bg-registrationPrimaryColor hover:text-white border-registrationPrimaryColor border-2 bg-white text-registrationPrimaryColor' }} w-48 py-2 rounded-md">Full
                                member</button>
                        @endif
                        <button wire:click.prevent="memberClicked"
                            class="{{ $delegatePassType == 'member' ? 'bg-registrationPrimaryColor text-white' : 'hover:bg-registrationPrimaryColor hover:text-white border-registrationPrimaryColor border-2 bg-white text-registrationPrimaryColor' }} w-48 py-2 rounded-md">Member</button>
                        <button wire:click.prevent="nonMemberClicked"
                            class="{{ $delegatePassType == 'nonMember' ? 'bg-registrationPrimaryColor text-white' : 'hover:bg-registrationPrimaryColor hover:text-white border-registrationPrimaryColor border-2 bg-white text-registrationPrimaryColor' }} w-48 py-2 rounded-md">Non-member</button>
                    </div>

                    @if ($delegatePassTypeError != null)
                        <div class="text-red-500 text-sm italic mt-2 text-center">
                            {{ $delegatePassTypeError }}
                        </div>
                    @endif

                    @if ($delegatePassType != null)
                        <div class="mt-10">
                            <div class="text-registrationPrimaryColor">
                                Company name <span class="text-red-500">*</span>
                            </div>
                            <div>
                                @if ($event->eb_full_member_rate != null || $event->std_full_member_rate != null)
                                    @if ($delegatePassType == 'fullMember')
                                        <select wire:model.lazy="companyName"
                                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                            <option value=""></option>
                                            @foreach ($members as $member)
                                                @if ($member->type == 'full')
                                                    <option value="{{ $member->name }}">
                                                        {{ $member->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @elseif($delegatePassType == 'member')
                                        <select wire:model.lazy="companyName"
                                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                            <option value=""></option>
                                            @foreach ($members as $member)
                                                @if ($member->type == 'associate')
                                                    <option value="{{ $member->name }}">
                                                        {{ $member->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        <input placeholder="Company Name" type="text"
                                            wire:model.lazy="companyName"
                                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                    @endif
                                @else
                                    @if ($delegatePassType == 'member')
                                        <select wire:model.lazy="companyName"
                                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                            <option value=""></option>
                                            @foreach ($members as $member)
                                                <option value="{{ $member->name }}">
                                                    {{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input placeholder="Company Name" type="text"
                                            wire:model.lazy="companyName"
                                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                    @endif
                                @endif

                                @error('companyName')
                                    <div class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        @if (
                            $event->co_eb_full_member_rate != null ||
                                $event->co_eb_member_rate != null ||
                                $event->co_eb_nmember_rate != null ||
                                $event->co_std_full_member_rate != null ||
                                $event->co_std_member_rate != null ||
                                $event->co_std_nmember_rate != null ||
                                $event->wo_eb_full_member_rate != null ||
                                $event->wo_eb_member_rate != null ||
                                $event->wo_eb_nmember_rate != null ||
                                $event->wo_std_full_member_rate != null ||
                                $event->wo_std_member_rate != null ||
                                $event->wo_std_nmember_rate != null)
                            <div class="mt-5">
                                <div class="text-registrationPrimaryColor">
                                    Access type <span class="text-red-500">*</span>
                                </div>
                                <div>
                                    <select wire:model.lazy="accessType"
                                        class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                                        <option value="fullEvent">Full event access</option>
                                        @if (
                                            $event->co_eb_full_member_rate != null ||
                                                $event->co_eb_member_rate != null ||
                                                $event->co_eb_nmember_rate != null ||
                                                $event->co_std_full_member_rate != null ||
                                                $event->co_std_member_rate != null ||
                                                $event->co_std_nmember_rate != null)
                                            <option value="conferenceOnly">Conference only</option>
                                        @endif
                                        @if (
                                            $event->wo_eb_full_member_rate != null ||
                                                $event->wo_eb_member_rate != null ||
                                                $event->wo_eb_nmember_rate != null ||
                                                $event->wo_std_full_member_rate != null ||
                                                $event->wo_std_member_rate != null ||
                                                $event->wo_std_nmember_rate != null)
                                            <option value="workshopOnly">Workshop only</option>


                                            {{-- <option value="workshopOnly">
    @if ($event->year == 2026 && $event->category == 'RCC')
        Some other words
    @else
        Workshop only
    @endif
</option> --}}
                                        @endif
                                    </select>


                                    @error('accessType')
                                        <div class="text-red-500 text-xs italic mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if ($delegateFees->isEmpty())
        <div class="col-span-2 mt-5">
            <input type="checkbox" wire:model.lazy="termsCondition" id="terms-condition">
            <label for="terms-condition">I agree to the <a
                    href="https://www.gpca.org.ae/terms-and-condition-events-registration/" target="_blank"
                    class="text-registrationPrimaryColor underline">Terms and Conditions</a> and <a
                    href="https://www.gpca.org.ae/privacy-policy/" target="_blank"
                    class="text-registrationPrimaryColor underline">Privacy Policy</a>.</label>

            @error('termsCondition')
                <div class="text-red-500 text-xs italic mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <p class="col-span-2 mt-5">For inquiries or to speak with a member of our team, please contact our sales team
            at
            <a href="mailto:sales@gpca.org.ae" class="underline">sales@gpca.org.ae</a> or call +971 4 451 0666 ext.
            103,
            106.
        </p>
    @endif
</div>
