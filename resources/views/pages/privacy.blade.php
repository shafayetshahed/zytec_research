<x-layouts.public title="Privacy policy" description="How Zytecs Research collects, uses, stores, protects and deletes student information and uploaded documents." :breadcrumbs="[['Privacy policy']]">
    <x-page-header eyebrow="Legal" title="Privacy policy" :breadcrumbs="[['Privacy policy']]" lede="Last updated {{ config('zytecs.business.policies_updated') }}." />
    <div class="container-page py-16">
        @include('pages.partials.policy-review-notice')
        <div class="prose-zr">
            <h2>Who we are</h2>
            <p>Zytecs Research ("we", "us") provides academic support and research services and operates <a href="{{ config('zytecs.business.url') }}">{{ config('zytecs.business.url') }}</a>. You can contact us about privacy at <a href="mailto:{{ config('zytecs.business.email') }}">{{ config('zytecs.business.email') }}</a>.</p>

            <h2>What we collect</h2>
            <ul>
                <li><strong>Account details:</strong> your name, email address, WhatsApp number and a securely hashed password.</li>
                <li><strong>Submission details:</strong> assignment name, module code and any optional details you choose to give (institution, country of study, subject, assessment type, deadline and description).</li>
                <li><strong>Documents:</strong> the brief and any supporting files you upload.</li>
                <li><strong>Enquiries:</strong> what you send through the contact form.</li>
                <li><strong>Security records:</strong> sign-in times, IP addresses and an audit trail of important account and administrative actions.</li>
            </ul>
            <p>We never ask for university portal, email or learning-platform passwords. Please do not include them in any message or file.</p>

            <h2>How we use it</h2>
            <ul>
                <li>To create and secure your account, including verifying your email with a one-time code.</li>
                <li>To review your brief, contact you on WhatsApp or email, and provide the support you agree to.</li>
                <li>To notify our team when you submit a brief or enquiry.</li>
                <li>To keep the service secure and prevent misuse.</li>
            </ul>
            <p>We do not sell your information, use it for advertising, publish your documents, or use your documents to train artificial intelligence systems. We do not use your work in our portfolio or marketing without your written permission.</p>

            <h2>Who can see it</h2>
            <p>Uploaded files are stored privately on our server, outside the public part of the website. Only you and authorised Zytecs Research team members can access them, after signing in. Our email and hosting providers process data on our behalf to run the service.</p>

            <h2>Cookies</h2>
            <p>We use only essential cookies: a session cookie that keeps you signed in and a security token that protects forms. We do not use advertising or analytics cookies.</p>

            <h2>How long we keep it</h2>
            <ul>
                <li>Submissions marked completed, closed or declined, and their files, are deleted automatically {{ config('zytecs.retention.closed_submission_days') }} days after closure.</li>
                <li>Your account remains until you ask us to delete it.</li>
                <li>Payment records (amount, currency, date, method and request reference, without your documents) are kept for accounting and tax purposes even after a request is deleted.</li>
                <li>Website enquiries are kept for up to 12 months.</li>
                <li>Security and audit records are kept for as long as needed to protect the service.</li>
            </ul>

            <h2>Your choices and rights</h2>
            <p>You can update your name and WhatsApp number in your profile, and request deletion of your account and data from the profile page or by emailing us. Depending on where you live and study, you may have additional rights, such as access to or correction of your data. Contact us to make a request.</p>

            <h2>International students and transfers</h2>
            <p>We operate from Bangladesh and work with students in the United Kingdom, Australia, Canada, the United States and elsewhere. Your information may therefore be processed in countries other than your own. We protect it with access controls, encrypted connections (HTTPS) and private storage.</p>

            <h2>Security</h2>
            <p>Passwords are hashed, verification codes are stored only in protected form and expire quickly, sign-in and verification attempts are rate-limited, and administrative access is restricted and logged. No system is perfectly secure; tell us immediately if you suspect a problem.</p>

            <h2>Changes</h2>
            <p>We will update this page when our practices change and show the date of the latest version at the top.</p>
        </div>
    </div>
</x-layouts.public>
