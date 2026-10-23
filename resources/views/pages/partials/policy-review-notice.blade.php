@unless (config('zytecs.business.policies_reviewed'))
    <div class="alert-warning alert mb-10" role="note">
        <x-icon name="info" class="mt-0.5 size-5" />
        <p>This policy is a working draft prepared for Zytecs Research and is awaiting legal review. It describes how we currently intend to handle your information.</p>
    </div>
@endunless
