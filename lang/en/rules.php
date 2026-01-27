<?php

return [
    'title' => 'Bot Settings',
    'subtitle' => 'Manage keywords and automated replies for your chatbot.',
    'create_button' => 'Create New Bot',
    'search_placeholder' => 'Search bot keywords...',
    'total_rules' => 'Total: :count Rules',
    'info_trigger' => 'Bot Info (Trigger)',
    'platform' => 'Platform',
    'match_type' => 'Match Type',
    'status' => 'Status',
    'status_active' => 'Active',
    'status_inactive' => 'Inactive',
    'actions' => 'Actions',
    'empty_title' => 'No Bot Rules Yet',
    'empty_description' => 'Create auto-reply rules to respond to customer messages automatically based on specific keywords.',
    'empty_action' => 'Create First Rule',
    'show_all' => 'Showing all rules.',
    
    // Modal
    'modal_create_title' => 'Add New Bot Rule',
    'modal_edit_title' => 'Edit Bot Rule',
    'modal_subtitle' => 'Configure keywords and automated replies.',
    'label_trigger' => 'Trigger Keyword',
    'placeholder_trigger' => 'e.g.: hospital schedule, cost, location',
    'help_trigger' => 'Separate multiple keywords with | (pipe) for multiple triggers.',
    'label_match_type' => 'Match Type',
    'label_priority' => 'Priority',
    'label_reply' => 'Bot Reply',
    'placeholder_reply' => 'Write your reply message here...',
    'label_active' => 'Activate this rule immediately',
    'button_cancel' => 'Cancel',
    'button_save' => 'Save Bot',
    'button_update' => 'Update Bot',
    'button_saving' => 'Saving...',
    
    // Delete Modal
    'delete_title' => 'Delete Bot Rule?',
    'delete_description' => 'Deleted rules cannot be restored. The bot will no longer reply to this keyword.',
    'delete_confirm' => 'Yes, Delete',
    'delete_cancelling' => 'Deleting...',
    
    // Help
    'help_title' => 'Bot Settings',
    'help_description' => 'Here you can set keywords that will trigger an automatic response from the bot.',
    'tip_1' => 'Click "Create New Bot" to add a new rule',
    'tip_2' => 'Enter keywords frequently asked by customers',
    'tip_3' => 'Write an informative and friendly reply',
    'tip_4' => 'Use the toggle to enable/disable rules',
    'tip_5' => 'Separate multiple keywords with | (e.g.: price|cost|fee)',
    
    // Toasts
    'success_created' => 'New bot rule created successfully',
    'success_updated' => 'Bot rule updated successfully',
    'success_deleted' => 'Bot rule deleted successfully',
    'success_status' => 'Bot status updated',
    'error_generic' => 'An error occurred',
    'error_status' => 'Failed to update status',
    'error_delete' => 'Failed to delete bot rule',
];
