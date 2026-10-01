{{-- MAIN DELEGATE --}}
<div class="mx-5">
    <div class="text-registrationPrimaryColor italic font-bold text-xl">
        Delegate details
    </div>

    <div class="mt-5 grid grid-cols-2 gap-y-3 gap-x-5">
        {{-- ROW 1 --}}
        <div class="space-y-2 col-span-2">
            <div class="grid grid-cols-11 gap-x-5">
                <div class="col-span-11 sm:col-span-2">
                    <div class="text-registrationPrimaryColor">
                        Salutation
                    </div>
                    <div>
                        <select wire:model.lazy="salutation"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                            <option value=""></option>
                            @foreach ($salutations as $salutation)
                                <option value="{{ $salutation }}">{{ $salutation }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-span-11 sm:col-span-3">
                    <div class="text-registrationPrimaryColor">
                        First name <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <input placeholder="First name" type="text" wire:model.lazy="firstName"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                        @error('firstName')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-span-11 sm:col-span-3">
                    <div class="text-registrationPrimaryColor">
                        Middle name
                    </div>
                    <div>
                        <input placeholder="Middle name" type="text" wire:model.lazy="middleName"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                    </div>
                </div>

                <div class="col-span-11 sm:col-span-3">
                    <div class="text-registrationPrimaryColor">
                        Last name <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <input placeholder="Last name" type="text" wire:model.lazy="lastName"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                        @error('lastName')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>


        {{-- ROW 2 --}}
        <div class="space-y-2 col-span-2">
            <div class="grid grid-cols-3 gap-x-5">
                <div class="col-span-3 sm:col-span-1">
                    <div class="text-registrationPrimaryColor">
                        Email address <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <input placeholder="Email address" type="text" wire:model.lazy="emailAddress"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                        @error('emailAddress')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($emailMainExistingError != null)
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $emailMainExistingError }}
                            </div>
                        @endif

                        @if ($emailMainAlreadyUsedError != null)
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $emailMainAlreadyUsedError }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-span-3 sm:col-span-1">
                    <div class="text-registrationPrimaryColor">
                        Mobile number <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <input placeholder="xxxxxxx" type="text" wire:model.lazy="mobileNumber"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                        @error('mobileNumber')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-span-3 sm:col-span-1">
                    <div class="text-registrationPrimaryColor">
                        Country <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <select wire:model.lazy="country"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                            <option value=""></option>
                            @foreach ($countries as $countryChoice)
                                <option value="{{ $countryChoice }}">
                                    {{ $countryChoice }}
                                </option>
                            @endforeach
                        </select>

                        @error('country')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>


        {{-- ROW 3 --}}
        <div class="space-y-2 col-span-2">
            <div class="grid grid-cols-3 gap-x-5">
                <div class="col-span-3 sm:col-span-1">
                    <div class="text-registrationPrimaryColor">
                        Nationality <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <select wire:model.lazy="nationality"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">
                            <option value=""></option>
                            <option value="N/A">N/A</option>
                            @foreach ($countries as $countryChoice)
                                <option value="{{ $countryChoice }}">
                                    {{ $countryChoice }}
                                </option>
                            @endforeach
                        </select>

                        @error('nationality')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-span-3 sm:col-span-1">
                    <div class="text-registrationPrimaryColor">
                        Job title <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <input placeholder="Job title" type="text" wire:model.lazy="jobTitle"
                            class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                        @error('jobTitle')
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
                    <div class="col-span-3 sm:col-span-1">
                        <div class="text-registrationPrimaryColor">
                            Promo code
                        </div>
                        <div class="flex">

                            @if ($promoCodeSuccessMain != null)
                                <input readonly type="text" wire:model.lazy="promoCode"
                                    class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-none cursor-not-allowed">

                                <button wire:click.prevent="removePromoCodeMain" wire:key="btnRemovePromoCodeMain"
                                    type="button" class="bg-red-300 px-5 ml-2">Remove</button>
                            @else
                                <input placeholder="Enter your promo code here" type="text"
                                    wire:model.lazy="promoCode"
                                    class="bg-registrationInputFieldsBGColor w-full py-1 px-3 outline-registrationPrimaryColor">

                                <button wire:click.prevent="applyPromoCodeMain" wire:key="btnApplyPromoCodeMain"
                                    type="button"
                                    class="bg-registrationPrimaryColor text-white px-5 ml-2 hover:bg-registrationPrimaryColorHover">Apply</button>
                            @endif
                        </div>

                        @if ($promoCodeFailMain != null)
                            <div class="text-red-500 text-xs italic mt-1">
                                {{ $promoCodeFailMain }}
                            </div>
                        @endif

                        @if ($promoCodeSuccessMain != null)
                            <div class="text-green-500 text-xs italic mt-1">
                                {{ $promoCodeSuccessMain }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($event->category == 'RCW')
            {{-- ROW 4 --}}
            <div class="space-y-2 col-span-2">
                <div class="text-registrationPrimaryColor">
                    Which RC code are you interested in?
                </div>
                <div>
                    @foreach (['Community Awareness and Emergency Management', 'Distribution', 'Product Stewardship', 'Process Safety', 'Health & Safety', 'Security', 'Environmental Protection'] as $interestIndex => $interest)
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model.lazy="mainDelegateInterests"
                                value="{{ $interest }}" id="mainDelegateOption-{{ $interestIndex }}">
                            <label for="mainDelegateOption-{{ $interestIndex }}">{{ $interest }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif



        @if ($event->category == 'AF')
            {{-- AF feature interests --}}
            <div class="space-y-2 col-span-2">
                <div class="text-registrationPrimaryColor">
                    I am interested in the below features
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach (['Sustainability Pavilion', 'Startup Nexus', 'Women in Chemicals'] as $interestIndex => $interest)
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model.lazy="mainDelegateInterests"
                                value="{{ $interest }}" id="mainDelegateAFOption-{{ $interestIndex }}">

                            <label for="mainDelegateAFOption-{{ $interestIndex }}">
                                {{ $interest }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif



    </div>

    {{-- @if ($event->category == 'AF' && $event->year == '2023')
        <p class="mt-5">If you are interested with the GPCA spouse program, please click here to <a
                href="https://www.gpcaforum.com/spouse-program/" target="_blank"
                class="text-blue-600 hover:underline font-semibold">learn more</a> and <a
                href="https://www.gpcaregistration.com/register/2023/AFS/11" target="_blank"
                class="text-blue-600 hover:underline font-semibold">register.</a></p>
    @endif --}}
</div>

@if ($event->category != 'GLF' && $event->category != 'DFCLW1')
    @if (!empty($additionalDelegates))
        <div class="mt-10 mx-5">
            <div class="text-registrationPrimaryColor italic font-bold text-xl">
                Additional delegate(s)
            </div>

            <div class="mt-5">
                @php $count = 2; @endphp
                @foreach ($additionalDelegates as $additionalDelegate)
                    <div class="bg-registrationCardBGColor px-5 py-2 mt-5 flex justify-between rounded-md">
                        <div>
                            <div class="font-bold text-2xl flex items-center gap-2 mt-1">
                                <p>{{ $additionalDelegate['subSalutation'] }}
                                    {{ $additionalDelegate['subFirstName'] }}
                                    {{ $additionalDelegate['subMiddleName'] }}
                                    {{ $additionalDelegate['subLastName'] }}
                                </p>
                                <p
                                    class="border-2 border-registrationPrimaryColor rounded-full text-registrationPrimaryColor py-1 px-3 text-sm">
                                    {{ $additionalDelegate['subBadgeType'] }}</p>
                            </div>
                            <p class="mt-2"> {{ $additionalDelegate['subEmailAddress'] }},
                                {{ $additionalDelegate['subMobileNumber'] }}, {{ $additionalDelegate['subJobTitle'] }}
                            </p>
                            <p>Country: {{ $additionalDelegate['subCountry'] }}</p>
                            <p>Nationality: {{ $additionalDelegate['subNationality'] }}</p>
                            @if ($additionalDelegate['subPromoCode'] == null)
                                <p>Promo code used: None</p>
                            @else
                                <p>Promo code used: <span
                                        class="font-bold">{{ $additionalDelegate['subPromoCode'] }}</span>
                                    <span
                                        class="text-green-500 text-xs italic mt-1">({{ $additionalDelegate['promoCodeSuccessSub'] }})</span>
                                </p>
                            @endif

                            @if ($event->category == 'RCW')
                                <p>Interests:
                                    @if (count($additionalDelegate['subDelegateInterests']) > 0)
                                        @foreach ($additionalDelegate['subDelegateInterests'] as $subDelegateInterestIndex => $subDelegateInterest)
                                            @if ($subDelegateInterestIndex == count($additionalDelegate['subDelegateInterests']) - 1)
                                                {{ $subDelegateInterest }}
                                            @else
                                                {{ $subDelegateInterest }},
                                            @endif
                                        @endforeach
                                    @endif
                                </p>
                            @endif




                        </div>
                        <div class="flex flex-col justify-between items-end">
                            <p class="text-registrationPrimaryColor font-bold">Delegate {{ $count }}</p>
                            <div class="flex gap-3">
                                <div wire:click.prevent="openEditModal('{{ $additionalDelegate['subDelegateId'] }}')"
                                    class="cursor-pointer hover:text-yellow-600 text-yellow-500">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </div>

                                <div wire:click.prevent="removeAdditionalDelegate('{{ $additionalDelegate['subDelegateId'] }}')"
                                    class="cursor-pointer hover:text-red-600 text-red-500">
                                    <i class="fa-solid fa-trash"></i>
                                    Remove
                                </div>
                            </div>
                        </div>
                    </div>

                    @php $count++; @endphp
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-10 flex flex-col sm:flex-row gap-10 mx-5">
        <div class="col-span-1">

            @if ($showAddDelegateModal)
                @include('livewire.registration.modal.add_delegate_modal')
            @endif

            @if ($showEditDelegateModal)
                @include('livewire.registration.modal.edit_delegate_modal')
            @endif

            @if (
                $firstName != null &&
                    $lastName != null &&
                    $emailAddress != null &&
                    $mobileNumber != null &&
                    $nationality != null &&
                    $jobTitle != null &&
                    $badgeType != null &&
                    $country != null &&
                    count($additionalDelegates) < 4)
                <button wire:click.prevent="openAddModal" type="button" wire:key="btnOpenAddModal"
                    class="cursor-pointer hover:bg-registrationPrimaryColor hover:text-white font-bold border-registrationPrimaryColor border-2 bg-white text-registrationPrimaryColor rounded-md py-4 px-10">+
                    Add delegate</button>
            @else
                <button disabled type="button"
                    class="cursor-not-allowed font-bold border-gray-600 border-2 bg-white text-gray-600  rounded-md py-4 px-10">+
                    Add delegate</button>
            @endif
        </div>

        <div class="col-span-1">
            <div class="text-registrationPrimaryColor italic font-bold text-xl">
                Do you wish to invite more delegates?
            </div>

            <div class="text-registrationPrimaryColor italic text-sm mt-2 w-full sm:w-3/5">
                If you wish to register more than 5 delegates, please contact our sales team at
                forumregistration@gpca.org.ae or call +971 4 5106666 ext. 153
            </div>
        </div>
    </div>
@endif
