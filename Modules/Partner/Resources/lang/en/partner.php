<?php

return [
    'title'                    => 'Partners',
    'add_title'                => 'Add Partner',
    'edit_title'               => 'Edit Partner',
    'lbl_partner'              => 'Partner',
    'lbl_no_partner'           => 'No Partner',
    'lbl_logo'                 => 'Logo',
    'lbl_email'                => 'Email',
    'lbl_phone'                => 'Phone',
    'lbl_website'              => 'Website',
    'lbl_description'          => 'Description',
    'lbl_company_name'         => 'Company Name',
    'placeholder_name'         => 'Enter partner name',
    'placeholder_email'        => 'Enter email address',
    'placeholder_phone'        => 'Enter phone number',
    'placeholder_website'      => 'Enter website URL',
    'placeholder_description'  => 'Enter partner description',

    // Account management
    'lbl_account'              => 'Partner Account',
    'lbl_create_account'       => 'Create User Account',
    'lbl_create_account_help'  => 'Enable to create a login account for this partner. They will be able to manage their content from the admin panel.',
    'account_linked'           => 'Account linked',
    'no_account_linked'        => 'No user account linked to this partner yet.',

    // Registration (frontend)
    'register_title'           => 'Partner Registration',
    'register_subtitle'        => 'Create a partner account to manage your content.',
    'register_section_account' => 'Your Account',
    'register_section_company' => 'Your Company',
    'register_submit'          => 'Create Partner Account',
    'already_have_account'     => 'Already have an account?',
    'register_success'         => 'Welcome! Your partner account has been created successfully.',

    // Allowed content types
    'lbl_content_types'        => 'Allowed Content Types',
    'lbl_content_types_help'   => 'Select the content types this partner is allowed to manage.',
    'content_type_video'       => 'Videos',
    'content_type_movie'       => 'Movies',
    'content_type_tvshow'      => 'TV Shows',
    'content_type_livetv'      => 'Live TV',

    // Validation
    'validation_title'         => 'Partner Content Validation',
    'pending_badge'            => 'pending',
    'status_pending'           => 'Pending',
    'status_approved'          => 'Approved',
    'status_rejected'          => 'Rejected',
    'approve'                  => 'Approve',
    'reject'                   => 'Reject',
    'content_approved'         => 'Content approved successfully.',
    'content_rejected'         => 'Content rejected.',
    'no_content_to_validate'   => 'No content to display for the selected filters.',
    // Fiche & stats
    'lbl_videos_count'    => 'Videos',
    'lbl_videos_active'   => 'Active videos',
    'lbl_videos_inactive' => 'Inactive videos',
    'lbl_movies_active'   => 'Active films',
    'lbl_videos_total'    => 'Total content',

    // Rejection
    'rejection_reason'         => 'Rejection reason',
    'placeholder_rejection'    => 'Explain why this content was rejected...',
    'content_rejected_reason'  => 'Content rejected with reason.',

    // Login
    'login_title'      => 'Partner Login',
    'login_subtitle'   => 'Sign in to manage your content.',
    'no_account'       => 'No account?',
    'not_a_partner'    => 'This account is not registered as a partner.',

    // Video management
    'add_video'              => 'Add Video',
    'edit_video'             => 'Edit Video',
    'submit_for_validation'  => 'Submit for validation',
    'video_submitted'        => 'Video submitted for validation.',
    'video_updated'          => 'Video updated and resubmitted for validation.',

    // Financial / PPV
    'lbl_commission_rate'   => 'Platform Commission',
    'lbl_commission_help'   => 'Percentage retained by the platform. Leave blank to define later.',
    'ppv_price_info'        => 'Your proposed price will be reviewed by the admin before publication.',
    'proposed_by_partner'   => 'Proposed by partner',
    'price_set_by_admin'    => 'Set by admin',

    'admin_price_review_info'    => 'You can keep the partner\'s proposed price or set a different one.',
    'leave_blank_keep_proposed'   => 'Leave blank to keep partner\'s price',

    'add_movie'    => 'Add Movie',
    'edit_movie'   => 'Edit Movie',
    'add_tvshow'   => 'Add TV Show',
    'edit_tvshow'  => 'Edit TV Show',
    'add_livetv'   => 'Add Live TV Channel',
    'edit_livetv'  => 'Edit Live TV Channel',

    // Seasons & Episodes
    'seasons'            => 'Seasons',
    'episodes'           => 'Episodes',
    'add_season'         => 'Add Season',
    'edit_season'        => 'Edit Season',
    'add_episode'        => 'Add Episode',
    'edit_episode'       => 'Edit Episode',
    'season_created'     => 'Season submitted for validation.',
    'season_updated'     => 'Season updated and resubmitted for validation.',
    'episode_created'    => 'Episode submitted for validation.',
    'episode_updated'    => 'Episode updated and resubmitted for validation.',
    'lbl_season_number'  => 'Season Number',
    'lbl_episode_number' => 'Episode Number',

    // Guided flow
    'no_tvshow_yet'     => 'No TV show yet',
    'tvshow_flow_desc'  => 'To add episodes, you must first create a TV show, then add a season.',
    'no_season_yet'     => 'No season yet',
    'season_flow_desc'  => 'To add episodes, start by creating a season for this TV show.',

    // Image validation
    'images_required_hint' => 'Poster and TV poster are required for proper display on web and mobile.',
    'image_required'        => 'This image is required.',

    'preview_video' => 'Preview Video',
    // Contract
    'contract_title' => 'Contract & Agreement',
    'contract_hint' => 'Upload the signed contract with the partner (PDF, Word).',
    'contract_status' => 'Contract status',
    'contract_none' => 'No contract',
    'contract_pending' => 'Awaiting signature',
    'contract_signed' => 'Signed',
    'contract_signed_at' => 'Signed on',
    'contract_file' => 'Contract file',
    'contract_formats' => 'Accepted: PDF, DOC, DOCX (max 10 MB)',
    'contract_current' => 'Current contract',
    'contract_delete_confirm' => 'Delete this contract permanently?',
    'contract_deleted' => 'Contract deleted.',

    // Notifications
    'notif_approved_subject' => 'Your content ":name" has been approved',
    'notif_rejected_subject' => 'Your content ":name" has been rejected',
    'notif_hello' => 'Hello :name,',
    'notif_approved_line' => 'Your content ":name" (:type) has been approved and is now visible on the platform.',
    'notif_rejected_line' => 'Your content ":name" (:type) has unfortunately been rejected.',
    'notif_rejection_reason' => 'Rejection reason',
    'notif_view_dashboard' => 'View my dashboard',
    'notifications' => 'Notifications',
    'mark_all_read' => 'Mark all as read',
    'no_notifications' => 'No notifications',

    // Quota
    'video_quota' => 'Content quota',
    'quota_unlimited' => 'Unlimited',
    'quota_videos' => 'contents',
    'quota_help' => 'Leave empty for unlimited. Counts videos + movies + shows + live TV.',
    'quota_usage' => 'Quota usage',
    'quota_exceeded' => 'Quota reached (:current/:max contents). Delete content or contact admin.',
    'quota_exceeded_warning' => 'Quota reached! You cannot add more content.',

    'contract_invalid_type' => 'Invalid file type. Only PDF, DOC and DOCX are accepted.',
    'contract_too_large' => 'File is too large (maximum 10 MB).',

];
