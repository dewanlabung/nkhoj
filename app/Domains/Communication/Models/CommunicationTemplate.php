<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    protected $table = 'communication_templates';

    protected $fillable = [
        'name', 'description', 'category', 'html_template',
        'variables', 'thumbnail_url', 'usage_count',
        'last_used_at', 'created_by',
    ];

    protected $casts = [
        'variables' => 'json',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public static function getCategories(): array
    {
        return ['newsletter', 'announcement', 'promotion', 'event'];
    }

    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getVariableList(): array
    {
        return $this->variables ?? [
            '{{first_name}}',
            '{{last_name}}',
            '{{email}}',
            '{{username}}',
            '{{unsubscribe_link}}',
            '{{year}}',
            '{{current_date}}',
        ];
    }
}
