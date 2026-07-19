<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'css' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }

    /** Render template with substituted variables. */
    public function render(array $vars = []): string
    {
        $html = $this->header_html.$this->body_html.$this->footer_html;
        foreach ($vars as $k => $v) {
            $html = str_replace('{{'.$k.'}}', is_scalar($v) ? (string) $v : '', $html);
        }
        return $html;
    }
}
