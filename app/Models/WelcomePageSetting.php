<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $company_name
 * @property string|null $company_short_name
 * @property string|null $company_logo
 * @property string|null $company_description
 * @property string $hero_title
 * @property string|null $hero_description
 * @property string|null $hero_background_image
 * @property string $hero_primary_button_text
 * @property string|null $hero_primary_button_url
 * @property string|null $hero_secondary_button_text
 * @property string|null $hero_secondary_button_url
 * @property bool $show_hero_section
 * @property string $features_section_title
 * @property string|null $features_section_description
 * @property string $workflow_section_title
 * @property string|null $workflow_section_description
 * @property string $cta_title
 * @property string|null $cta_description
 * @property string $cta_button_text
 * @property string|null $cta_button_url
 * @property bool $show_cta_section
 * @property string $footer_company_name
 * @property string|null $footer_description
 * @property string $copyright_text
 * @property bool $show_footer_links
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'company_name',
    'company_short_name',
    'company_logo',
    'company_description',
    'hero_title',
    'hero_description',
    'hero_background_image',
    'hero_primary_button_text',
    'hero_primary_button_url',
    'hero_secondary_button_text',
    'hero_secondary_button_url',
    'show_hero_section',
    'features_section_title',
    'features_section_description',
    'workflow_section_title',
    'workflow_section_description',
    'cta_title',
    'cta_description',
    'cta_button_text',
    'cta_button_url',
    'show_cta_section',
    'footer_company_name',
    'footer_description',
    'copyright_text',
    'show_footer_links',
    'created_by',
    'updated_by',
])]
class WelcomePageSetting extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_hero_section' => 'boolean',
            'show_cta_section' => 'boolean',
            'show_footer_links' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this setting.
     */
    public function createdByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this setting.
     */
    public function updatedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the logo image URL or default
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->company_logo) {
            return asset('storage/' . $this->company_logo);
        }

        return asset('images/logo-placeholder.png');
    }

    /**
     * Get the background image URL or default
     */
    public function getBackgroundImageUrlAttribute(): string
    {
        if ($this->hero_background_image) {
            return asset('storage/' . $this->hero_background_image);
        }

        return asset('images/hero-bg-placeholder.jpg');
    }

    /**
     * Scope to get the single settings record
     */
    public function scopeFirst($query)
    {
        return $query->firstOrCreate(
            [],
            [
                'company_name' => 'Fertilizer Management System',
                'hero_title' => 'Welcome to Fertilizer Management System',
                'hero_primary_button_text' => 'Get Started',
                'features_section_title' => 'Key Features',
                'workflow_section_title' => 'How It Works',
                'cta_title' => 'Ready to Get Started?',
                'cta_button_text' => 'Start Now',
                'footer_company_name' => 'Fertilizer Management System',
                'copyright_text' => '© 2024 All rights reserved.',
            ]
        );
    }
}
