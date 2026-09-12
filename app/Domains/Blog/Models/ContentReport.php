<?php

namespace App\Domains\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContentReport extends Model
{
    protected $fillable = [
        'reporter_id', 'reportable_type', 'reportable_id',
        'reason', 'details', 'status', 'reviewed_by', 'reviewed_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function reasons(): array
    {
        return [
            'spam'           => 'Spam or misleading',
            'misinformation' => 'Misinformation / false news',
            'hate_speech'    => 'Hate speech or harassment',
            'violence'       => 'Violence or dangerous content',
            'copyright'      => 'Copyright violation',
            'other'          => 'Other',
        ];
    }
}
