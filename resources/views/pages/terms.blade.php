<x-layouts.public title="Terms of service" description="Terms for using the Zytecs Research website and student portal, including quotations, document handling, cancellations, refunds and academic integrity." :breadcrumbs="[['Terms of service']]">
    <x-page-header eyebrow="Legal" title="Terms of service" :breadcrumbs="[['Terms of service']]" lede="Last updated {{ config('zytecs.business.policies_updated') }}." />
    <div class="container-page py-16">
        @include('pages.partials.policy-review-notice')
        <div class="prose-zr">
            <h2>About these terms</h2>
            <p>These terms apply to your use of the Zytecs Research website and student portal and to any support you agree with us. By creating an account you accept them.</p>

            <h2>Our services</h2>
            <p>We provide academic guidance, tutoring-style explanations, research support, feedback and proofreading. Our services are for learning. You remain responsible for the work you submit to your institution and for following its rules. See our <a href="{{ route('academic-integrity') }}">academic integrity commitment</a>.</p>

            <h2>Your account</h2>
            <ul>
                <li>Give accurate details, including a WhatsApp number you can be contacted on.</li>
                <li>Keep your password secure and do not share your account.</li>
                <li>We may suspend accounts that are misused or used to request services we do not provide.</li>
            </ul>

            <h2>Submitting a brief</h2>
            <p>Submitting a brief is a request for review, not an order. After reviewing it we will confirm in writing what we can help with, the timeline and the price. No agreement exists, and no payment is due, until you accept that confirmation.</p>

            <h2>Documents you upload</h2>
            <p>Only upload documents you are entitled to share. Do not upload passwords, login details or unnecessary personal information. We store documents privately and handle them as described in the <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>

            <h2>Timelines</h2>
            <p>Timelines are confirmed for each request once scope and availability are known. We do not guarantee to meet a deadline that we have not confirmed in writing.</p>

            <h2>Payments, cancellations and refunds</h2>
            <p>Payment terms are confirmed with each quotation. If you cancel before any work has started, we will refund any amount you have paid. If you cancel after work has started, we may keep a fair proportion for work already done. If we cannot deliver what was agreed, we will refund the undelivered part. Contact us on WhatsApp or by email to cancel.</p>

            <h2>Revisions</h2>
            <p>Where an agreement includes follow-up feedback or revisions, they cover the agreed scope only. New requirements are treated as a new request.</p>

            <h2>No guarantees of outcomes</h2>
            <p>We do not guarantee grades, marks, pass rates or any academic outcome.</p>

            <h2>Liability</h2>
            <p>We provide our services with reasonable care and skill. To the extent permitted by law, we are not liable for academic decisions made by your institution or for losses arising from use of our guidance contrary to your institution's rules.</p>

            <h2>Changes and contact</h2>
            <p>We may update these terms and will show the latest date above. Questions: <a href="mailto:{{ config('zytecs.business.email') }}">{{ config('zytecs.business.email') }}</a>.</p>
        </div>
    </div>
</x-layouts.public>
