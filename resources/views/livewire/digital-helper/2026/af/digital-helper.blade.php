<div>
    @if ($showCollectYourBadgeDetails)
        @include('livewire.digital-helper.2025.af.digital_helper_details')
    @else
        <div class="w-10/12 mx-auto md:w-full md:px-10">
            <p
                class="text-registrationPrimaryColor text-2xl md:text-4xl text-center font-bold font-montserrat mt-5 md:mt-10">
                How to collect your badge</p>

            <p class="text-center text-lg mt-5" style="color: #6C6C6C;">Please select one of the options below to search
                for
                your badge. If you do not know your details yet, please <a
                    href="{{ route('digital.helper.faq.view', ['eventCategory' => $event->category, 'eventId' => $event->id]) }}"
                    target="_blank" class="underline text-blue-700">click here</a>.</p>

            <div class="flex flex-col gap-8 mt-10 justify-center md:w-1/2 mx-auto w-full">
                <div wire:click.prevent="optionClicked('email')"
                    class="bg-registrationPrimaryColor hover:bg-registrationPrimaryColorHover rounded-2xl w-full flex flex-col object-center items-center py-5 cursor-pointer">
                    <img src="https://www.gpcaforum.com/wp-content/uploads/2024/10/email_address.png" alt=""
                        class="w-24">
                    <p class="text-white mt-5 font-semibold">Email address</p>
                </div>

                <div wire:click.prevent="optionClicked('transactionId')"
                    class="bg-registrationPrimaryColor hover:bg-registrationPrimaryColorHover rounded-2xl w-full flex flex-col object-center items-center py-5 cursor-pointer">
                    <img src="https://www.gpcaforum.com/wp-content/uploads/2024/10/transaction_id.png" alt=""
                        class="w-24">
                    <p class="text-white mt-5 font-semibold">Transaction ID</p>
                </div>

                <div wire:click.prevent="optionClicked('name')"
                    class="bg-registrationPrimaryColor hover:bg-registrationPrimaryColorHover rounded-2xl w-full flex flex-col object-center items-center py-5 cursor-pointer">
                    <img src="https://www.gpcaforum.com/wp-content/uploads/2024/10/name.png" alt=""
                        class="w-16">
                    <p class="text-white mt-5 font-semibold">Name</p>
                </div>
            </div>
        </div>
    @endif

    @if ($showNotFoundText)
        @include('livewire.digital-helper.digital_helper_404')
    @endif

    @if ($showInputFormModal)
        @include('livewire.digital-helper.digital_helper_form')
    @endif
</div>
