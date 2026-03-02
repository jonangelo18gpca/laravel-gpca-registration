<div>
    <div>
        <img src="{{ Storage::url($event->banner) }}" alt="" class="w-full object-cover">
    </div>
    <div class="px-5">
        <div class="float-left">
            @include('livewire.admin.events.promo-codes.add')
        </div>


        <div class="shadow-lg my-5 pt-5 bg-white rounded-md" style="margin-left: 320px; ">


            {{-- previous-code commented by jon --}}
            {{-- <div class="mb-5">
                <a href="{{ route('admin.event.promo-codes.export.data', ['eventCategory' => $event->category, 'eventId' => $event->id]) }}"
                    target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-5 rounded-md text-lg text-center">Export
                    Data to Excel</a>
            </div> --}}



            <div class="mb-5 flex items-center gap-3">


                <a href="{{ route('admin.event.promo-codes.export.data', ['eventCategory' => $event->category, 'eventId' => $event->id]) }}"
                    target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-5 rounded-md text-lg text-center">
                    Export Data to Excel
                </a>


                <input type="file" wire:model="importFile" accept=".csv" class="border rounded-md p-2 text-sm" />

 <button
    wire:click="previewImport"
    wire:loading.attr="disabled"
    wire:target="importFile,previewImport"
    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-5 rounded-md text-lg">

    <span wire:loading.remove wire:target="importFile,previewImport">
        Import CSV
    </span>

    <span wire:loading wire:target="importFile">
        Uploading...
    </span>

    <span wire:loading wire:target="previewImport">
        Checking...
    </span>

</button>

                <a href="{{ route('admin.event.promo-codes.template', [
                    'eventCategory' => $event->category,
                    'eventId' => $event->id,
                ]) }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-5 rounded-md text-lg text-center">
                    Download CSV Template
                </a>




            </div>

            @if (session()->has('success'))
                <div class="text-green-600">{{ session('success') }}</div>
            @endif

            @if (session()->has('error'))
                <div class="text-red-600">{{ session('error') }}</div>
            @endif

            <h1 class="text-center text-2xl bg-registrationPrimaryColor text-white py-4">Promo codes for fixed rate</h1>
            <div class="grid grid-cols-11 gap-5 p-4 px-4 text-center items-center bg-blue-600 text-white ">
                <div class="col-span-1 break-words">Code</div>
                <div class="col-span-1 break-words">Registration Type</div>
                <div class="col-span-1 break-words">New rate</div>
                <div class="col-span-1 break-words">New rate description</div>
                <div class="col-span-1 break-words">Description</div>
                <div class="col-span-1 break-words">Remaining Usage</div>
                <div class="col-span-1 break-words">Total Usage</div>
                <div class="col-span-1 break-words">Number of Codes</div>
                <div class="col-span-1 break-words">Validity</div>
                <div class="col-span-1 break-words">Status</div>
                <div class="col-span-1 break-words">Edit</div>
            </div>

            @if (empty($promoCodesFixedRatesArr))
                <div class="bg-red-400 text-white text-center py-3 mt-2 rounded-md">
                    There are no codes yet.
                </div>
            @else
                @foreach ($promoCodesFixedRatesArr as $promoCodesFixedRateIndex => $promoCodesFixedRate)
                    <div
                        class="grid grid-cols-11 gap-5 py-2 px-4 mb-1 text-center items-center {{ $promoCodesFixedRateIndex % 2 == 0 ? 'bg-registrationInputFieldsBGColor' : 'bg-registrationCardBGColor' }}">
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['promo_code'] }}</div>
                        <div class="col-span-1 break-words">
                            {{ $promoCodesFixedRate['badge_type'] }}
                            <br>
                            @if (!empty($promoCodesFixedRate['additionalBadgeType']))
                                @foreach ($promoCodesFixedRate['additionalBadgeType'] as $regType)
                                    {{ $regType['badgeType'] }} <br>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-span-1 break-words">
                            $ {{ number_format($promoCodesFixedRate['new_rate'], 2, '.', ',') }}
                        </div>
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['new_rate_description'] }}</div>
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['description'] }}</div>
                        <div class="col-span-1 break-words">
                            {{ $promoCodesFixedRate['number_of_codes'] - $promoCodesFixedRate['total_usage'] }}
                        </div>
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['total_usage'] }}</div>
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['number_of_codes'] }}</div>
                        <div class="col-span-1 break-words">{{ $promoCodesFixedRate['validity'] }}</div>
                        <div class="col-span-1 break-words ">
                            @if ($promoCodesFixedRate['active'])
                                <button
                                    wire:click="updateStatus({{ $promoCodesFixedRate['id'] }}, {{ $promoCodesFixedRate['active'] }})"
                                    class="text-gray-700 bg-green-300 hover:bg-green-500 hover:text-white py-1 px-2 text-sm rounded-md">Active</button>
                            @else
                                <button
                                    wire:click="updateStatus({{ $promoCodesFixedRate['id'] }}, {{ $promoCodesFixedRate['active'] }})"
                                    class="text-gray-700 bg-red-300 hover:bg-red-500 hover:text-white py-1 px-2 text-sm rounded-md">Inactive</button>
                            @endif
                        </div>
                        <div class="col-span-1 break-words">
                            <div wire:click="showEditPromoCode({{ $promoCodesFixedRate['id'] }})"
                                class="cursor-pointer hover:text-yellow-600 text-yellow-500">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Details
                            </div>

                            <div wire:click="showEditRegistrationTypes({{ $promoCodesFixedRate['id'] }})"
                                class="cursor-pointer hover:text-teal-600 text-teal-500 mt-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Reg Types
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>


        <div class="shadow-lg my-5 pt-5 bg-white rounded-md" style="margin-left: 320px; ">
            <h1 class="text-center text-2xl bg-registrationPrimaryColor text-white py-4">Promo codes for discounted
                price
                & percentage</h1>
            <div class="grid grid-cols-11 gap-5 p-4 px-4 text-center items-center bg-blue-600 text-white ">
                <div class="col-span-1 break-words">Code</div>
                <div class="col-span-1 break-words">Registration Type</div>
                <div class="col-span-1 break-words">Discount</div>
                <div class="col-span-2 break-words">Description</div>
                <div class="col-span-1 break-words">Remaining Usage</div>
                <div class="col-span-1 break-words">Total Usage</div>
                <div class="col-span-1 break-words">Number of Codes</div>
                <div class="col-span-1 break-words">Validity</div>
                <div class="col-span-1 break-words">Status</div>
                <div class="col-span-1 break-words">Edit</div>
            </div>

            @if (empty($promoCodesPercentPricesArr))
                <div class="bg-red-400 text-white text-center py-3 mt-2 rounded-md">
                    There are no codes yet.
                </div>
            @else
                @foreach ($promoCodesPercentPricesArr as $promoCodesPercentPriceIndex => $promoCodesPercentPrice)
                    <div
                        class="grid grid-cols-11 gap-5 py-2 px-4 mb-1 text-center items-center {{ $promoCodesPercentPriceIndex % 2 == 0 ? 'bg-registrationInputFieldsBGColor' : 'bg-registrationCardBGColor' }}">
                        <div class="col-span-1 break-words">{{ $promoCodesPercentPrice['promo_code'] }}</div>
                        <div class="col-span-1 break-words">
                            {{ $promoCodesPercentPrice['badge_type'] }}
                            <br>
                            @if (!empty($promoCodesPercentPrice['additionalBadgeType']))
                                @foreach ($promoCodesPercentPrice['additionalBadgeType'] as $regType)
                                    {{ $regType['badgeType'] }} <br>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-span-1 break-words">
                            @if ($promoCodesPercentPrice['discount_type'] == 'percentage')
                                {{ $promoCodesPercentPrice['discount'] }}%
                            @else
                                $ {{ number_format($promoCodesPercentPrice['discount'], 2, '.', ',') }}
                            @endif
                        </div>
                        <div class="col-span-2 break-words">{{ $promoCodesPercentPrice['description'] }}</div>
                        <div class="col-span-1 break-words">
                            {{ $promoCodesPercentPrice['number_of_codes'] - $promoCodesPercentPrice['total_usage'] }}
                        </div>
                        <div class="col-span-1 break-words">{{ $promoCodesPercentPrice['total_usage'] }}</div>
                        <div class="col-span-1 break-words">{{ $promoCodesPercentPrice['number_of_codes'] }}</div>
                        <div class="col-span-1 break-words">{{ $promoCodesPercentPrice['validity'] }}</div>
                        <div class="col-span-1 break-words ">
                            @if ($promoCodesPercentPrice['active'])
                                <button
                                    wire:click="updateStatus({{ $promoCodesPercentPrice['id'] }}, {{ $promoCodesPercentPrice['active'] }})"
                                    class="text-gray-700 bg-green-300 hover:bg-green-500 hover:text-white py-1 px-2 text-sm rounded-md">Active</button>
                            @else
                                <button
                                    wire:click="updateStatus({{ $promoCodesPercentPrice['id'] }}, {{ $promoCodesPercentPrice['active'] }})"
                                    class="text-gray-700 bg-red-300 hover:bg-red-500 hover:text-white py-1 px-2 text-sm rounded-md">Inactive</button>
                            @endif
                        </div>
                        <div class="col-span-1 break-words">
                            <div wire:click="showEditPromoCode({{ $promoCodesPercentPrice['id'] }})"
                                class="cursor-pointer hover:text-yellow-600 text-yellow-500">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Details
                            </div>

                            <div wire:click="showEditRegistrationTypes({{ $promoCodesPercentPrice['id'] }})"
                                class="cursor-pointer hover:text-teal-600 text-teal-500 mt-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Reg Types
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if ($updatePromoCode)
            @include('livewire.admin.events.promo-codes.edit')
        @endif

        @if ($updateRegistrationTypes)
            @include('livewire.admin.events.promo-codes.edit_registration_types')
        @endif
    </div>



@if($showImportConfirm)

<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white p-6 rounded shadow-lg w-96">

        <h2 class="text-xl font-bold mb-4">Confirm Import</h2>

        <p class="mb-4">
            New Codes: <strong>{{ $newCount }}</strong><br>
            Duplicate Codes: <strong>{{ $duplicateCount }}</strong>
        </p>

        <p class="mb-4">
            Are you sure you want to import?
        </p>

        <div class="flex justify-end gap-3">

            <button wire:click="$set('showImportConfirm', false)"
                class="bg-gray-400 text-white px-4 py-2 rounded">
                Cancel
            </button>

            <button wire:click="confirmImport"
                class="bg-blue-600 text-white px-4 py-2 rounded">
                Yes Import
            </button>

        </div>

    </div>

</div>

@endif

</div>
