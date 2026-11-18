<div class="fixed z-20 inset-0 overflow-y-auto">
    <form>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-10 text-center sm:block sm:p-0">

            {{-- BACKDROP --}}
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            {{-- FOR CENTERING THE CONTENT --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div class="w-full inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <p
                        class="text-registrationPrimaryColor text-2xl md:text-4xl text-center font-bold font-montserrat ">
                        No matching records found</p>

                    <p class="mt-5">We couldn’t find any registration details with the information you provided. Please check your entry and try again.</p>
                    <ul class="list-disc ml-5 mt-2">
                        <li>If you entered your <strong>email</strong>: Ensure you’re using the same email you registered with.</li>
                        <li>If you entered your <strong>transaction ID</strong>: Double-check for any typos or missing characters.</li>
                        <li>If you entered your <strong>name</strong>: Make sure you’re using the exact name as in your registration.</li>
                    </ul>

                    <p class="mt-4">If you <strong>do not know your details yet</strong>, please <a
                        href="{{ route('digital.helper.faq.view', ['eventCategory' => $event->category, 'eventId' => $event->id]) }}"
                        target="_blank" class="underline text-blue-700">click here</a> for more information on how to find your registration details.</p>

                    <p class="mt-6 font-bold">Not Confirmed or Paid Yet?</p>
                    <p>If you are not confirmed or have not paid yet, please proceed to the new registration counter. You can find the location of the new registration counter in the image below:</p>

                    <div class="flex flex-col gap-3 items-center">
                        <img src="https://www.gpcaforum.com/wp-content/uploads/2026/11/companies3.png" class="w-full block">
                    </div>

                    <p class="mt-4">
                        If you still encounter issues, please contact us at <a href="mailto:forumregistration@gpca.org.ae" class="underline text-blue-700">forumregistration@gpca.org.ae</a> for assistance.
                    </p>
                </div>
                <div class="px-4 py-3">
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 "
                        wire:click.prevent="tryAgainClicked">Try again</button>
                </div>
            </div>
        </div>
    </form>
</div>
