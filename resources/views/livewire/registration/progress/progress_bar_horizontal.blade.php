{{-- PROGRESS BAR --}}
<div class="horizontal-progress mx-5">
    {{-- STEP 1 --}}
    <div class="grid grid-cols-horizontalProgressBarGrid">
        <div class="bg-registrationPrimaryColor font-bold text-white rounded-full flex items-center justify-center"
            style="height: 55px; width: 55px; font-size: 18px;">
            1
        </div>

        <div class="flex items-center justify-center">
            <div style="height: 2px;" class="w-full bg-registrationPrimaryColor"></div>
        </div>

        <div class="{{ $currentStep >= 2 ? 'bg-registrationPrimaryColor text-white' : 'text-registrationPrimaryColor bg-white border-solid border-registrationPrimaryColor border-2' }} font-bold rounded-full flex items-center justify-center"
            style="height: 55px; width: 55px; font-size: 18px;">
            2
        </div>

        <div class="flex items-center justify-center">
            <div style="height: 2px;" class="w-full bg-registrationPrimaryColor"></div>
        </div>

        <div class="{{ $currentStep >= 3 ? 'bg-registrationPrimaryColor text-white' : 'text-registrationPrimaryColor bg-white border-solid border-registrationPrimaryColor border-2' }} font-bold rounded-full flex items-center justify-center"
            style="height: 55px; width: 55px; font-size: 18px;">
            3
        </div>

        <div class="flex items-center justify-center">
            <div style="height: 2px;" class="w-full bg-registrationPrimaryColor"></div>
        </div>

        <div class="{{ $currentStep >= 4 ? 'bg-registrationPrimaryColor text-white' : 'text-registrationPrimaryColor bg-white border-solid border-registrationPrimaryColor border-2' }} font-bold rounded-full flex items-center justify-center"
            style="height: 55px; width: 55px; font-size: 18px;">
            4
        </div>

        @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
            <div class="flex items-center justify-center">
                <div style="height: 2px;" class="w-full bg-registrationPrimaryColor"></div>
            </div>

            <div class="{{ $currentStep >= 5 ? 'bg-registrationPrimaryColor text-white' : 'text-registrationPrimaryColor bg-white border-solid border-registrationPrimaryColor border-2' }} font-bold rounded-full flex items-center justify-center"
                style="height: 55px; width: 55px; font-size: 18px;">
                5
            </div>
        @endif
    </div>


    {{-- <div class="grid grid-cols-horizontalProgressBarGrid mt-5">
        <div class="text-sm md:text-base font-bold text-registrationPrimaryColor text-center flex justify-center">
            Registration type
        </div>

        
        
        <div class="w-full"></div>


        <div class="text-sm md:text-base font-bold text-registrationPrimaryColor text-center flex justify-center">
            Company details
        </div>

        <div class="w-full"></div>


        <div class="text-sm md:text-base font-bold text-registrationPrimaryColor text-center flex justify-center">
            Delegate details
        </div>

        <div class="w-full"></div>


        <div class="text-sm md:text-base font-bold text-registrationPrimaryColor text-center flex justify-center">
            Package summary
        </div>

        @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
            <div class="w-full"></div>


            <div class="text-sm md:text-base font-bold text-registrationPrimaryColor text-center flex justify-center">
                Payment details
            </div>
        @endif
    </div> --}}



    <div class="grid grid-cols-horizontalProgressBarGrid mt-5">
        <div class="text-xs leading-tight md:text-base md:leading-normal font-bold text-registrationPrimaryColor text-center flex justify-center">
            Registration type
        </div>


        <div class="w-full"></div>



        <div class="text-xs leading-tight md:text-base md:leading-normal font-bold text-registrationPrimaryColor text-center flex justify-center">
            Company details
        </div>


        <div class="w-full"></div>



        <div class="text-xs leading-tight md:text-base md:leading-normal font-bold text-registrationPrimaryColor text-center flex justify-center">
            Delegate details
        </div>


        <div class="w-full"></div>



        <div class="text-xs leading-tight md:text-base md:leading-normal font-bold text-registrationPrimaryColor text-center flex justify-center">
            Package summary
        </div>


        @if ($event->category != 'GLF' && $event->category != 'DFCLW1')
            <div class="w-full"></div>



            <div class="text-xs leading-tight md:text-base md:leading-normal font-bold text-registrationPrimaryColor text-center flex justify-center">
                Payment details
            </div>
        @endif
    </div>
</div>
