<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * One row per API write call: who/what/when. Read-only from app code —
 * inserted by AuditApiRequest middleware after the response is built.
 */
final class ApiAudit extends Model
{
    protected $table = 'api_audits';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'response_meta' => 'array',
    ];

    public function token()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'token_id');
    }
}
