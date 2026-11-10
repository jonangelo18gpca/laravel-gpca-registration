<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $pageTitle }}</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Roboto&display=swap"
        rel="stylesheet">

    {{-- VITE --}}
    @vite('resources/css/app.css')
</head>

<body class="font-montserrat">
    <div class="mb-10">
        <img src="https://www.gpcaforum.com/wp-content/uploads/2026/11/20th-Annual-GPCA-Forum-kv_FINAL.png"
            class="w-full object-fill object-center md:hidden block">

        <img src="https://gpcaregistration.com/storage/event/2026/banners/uP8RkFF0NhAir4aUk9cozCf0hdKKM2iXVpKhVbpP.png"
            class="w-full object-fill object-center md:block hidden">

        <div class="w-10/12 mx-auto md:w-full md:px-10">
            <p class="text-registrationPrimaryColor text-2xl md:text-4xl md:text-left text-center font-bold font-montserrat mt-5 md:mt-10">How to Find
                Your Registration Details</p>

            <div>
                <p class="font-bold mt-10 text-lg">1. Sample registration confirmation email</p>
                <p class="mt-4">Here’s what your confirmation email should look like:</p>
                <img src="https://www.gpcaforum.com/wp-content/uploads/2026/11/confirm-email.png" class="w-full md:w-96 block">
                <p class="font-bold mt-5">Key details to look for:</p>
                <p>Event name: 20<sup>th</sup> Annual GPCA Forum</p>
                <p>Confirmation Status: Confirmed</p>
                <p>Full name: [Your name]</p>
                <p>Transaction ID: [Your transaction ID]</p>
            </div>
            
            <div>
                <p class="font-bold mt-10 text-lg">2. What to Do If You Did Not Receive Your Confirmation Email</p>
                <p class="mt-2">If you haven't received your confirmation email, please follow these steps:</p>
                <ul class="list-disc ml-5 mt-4">
                    <li>
                        <p class="font-bold">Check Your Spam/Junk Folder:</p>
                        <p>Sometimes, emails may end up in your spam or junk folder. Make sure to look there!</p>
                    </li>
                    <li>
                        <p class="font-bold">Search for the Email:</p>
                        <p>Look for an email with the subject line: "Registration confirmation for the 20<sup>th</sup> Annual GPCA Forum"</p>
                    </li>
                    <li>
                        <p class="font-bold">Sender’s Email:</p>
                        <p>The confirmation email will be sent from: <a href="mailto:forumregistration@gpca.org.ae" class="underline text-blue-700">forumregistration@gpca.org.ae</a> (GPCA Events Registration)</p>
                    </li>
                </ul>
            </div>
            
            <div>
                <p class="font-bold mt-10 text-lg">3. Still Can’t Find Your Email?</p>
                <p class="mt-2">If you’ve checked all the above and still cannot find your confirmation email, it’s possible that your registration is not confirmed yet.</p>

                <p class="mt-4 font-bold">Contact Us for Assistance:</p>
                <p>Please reach out to us at <a href="mailto:forumregistration@gpca.org.ae" class="underline text-blue-700">forumregistration@gpca.org.ae</a>, and we will help you verify your registration status.</p>
            </div>
        </div>
    </div>
</body>

</html>
