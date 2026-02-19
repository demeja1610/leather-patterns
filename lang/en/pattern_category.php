<?php
return [
    'pattern_categories' => 'Pattern categories',
    'creation' => 'Create pattern category',
    'edition' => 'Edit pattern category',

    'id' => 'ID',
    'name' => 'Name',
    'patterns_count' => 'Patterns count',
    'replacement_for_count' => 'Replaces count',
    'created_at' => 'Created at',
    'replacement' => 'Replace to',
    'remove_on_appear' => 'Remove from patterns on appear',
    'remove_on_appear_short' => 'Remove on appear',
    'is_published' => 'Is published',
    'has_patterns' => 'Used by patterns',
    'has_replacement' => 'Has replacement',

    'admin' => [
        'created' => 'Category with name: `:name` successfully created',
        'updated' => 'Category with id: `:id` successfully updated',
        'failed_to_update' => 'Failed to update category with id: `:id`',
        'single_delete_success' => 'Category with name: `:name` successfully deleted',
        'single_failed_to_delete' => 'Failed to delete category with name: `:name`',
        'success_mass_deleted' => ':count categories successfully deleted',
        'patterns_not_empty' => 'Category with name: `:name` cannot be deleted because it is used by :count patterns',
        'category_needed_for_replace_or_remove' => 'Category with name: `:name` cannot be deleted because it is needed to system to replace or remove it from patterns on appear',
        'category_is_replacement_for' => 'Category with name: `:name` cannot be deleted because it is replacement category for :count other categories',
        'confirm_delete_text' => 'Please confirm category delete',
        'cannot_remove_and_replace_same_time' => 'Category cannot be removed and replaced at the same time, please choose one of the options',
    ],
];
