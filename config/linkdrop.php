<?php

return [
    /*
     * How long (in minutes) email-verification and password-reset tokens remain valid.
     */
    'token_expiry_minutes' => 60,

    /*
     * Maximum number of links allowed per plan tier.
     */
    'free_tier_link_limit' => 10,
    'pro_tier_link_limit' => 999,

    /*
     * Maximum file upload size in kilobytes.
     */
    'max_file_upload_kb' => 20480,
];
