<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Enums;

/**
 * Token abilities (scopes) for the CMS v1 API.
 *
 * Granted on issue via `php artisan api:token-issue --abilities=...`
 * Enforced on each route via the abilities middleware.
 *
 * Naming: <resource>:<action>. Action is one of:
 *   - read     list + show
 *   - write    create + update (drafts only — never publishes)
 *   - delete   destroy
 *   - upload   multipart media binding (separate from write to gate big payloads)
 *   - moderate approve/reject (comments only)
 */
enum ApiAbility: string
{
    case ContentRead = 'content:read';
    case ContentWrite = 'content:write';
    case ContentDelete = 'content:delete';

    case MediaRead = 'media:read';
    case MediaUpload = 'media:upload';
    case MediaDelete = 'media:delete';

    case BlockRead = 'block:read';
    case BlockWrite = 'block:write';

    case TableRead = 'table:read';
    case TableWrite = 'table:write';

    case MenuRead = 'menu:read';
    case MenuWrite = 'menu:write';

    case SliderRead = 'slider:read';
    case SliderWrite = 'slider:write';

    case AuthorRead = 'author:read';
    case AuthorWrite = 'author:write';

    case RedirectRead = 'redirect:read';
    case RedirectWrite = 'redirect:write';

    case CommentModerate = 'comment:moderate';

    case SettingRead = 'setting:read';
    case SettingWrite = 'setting:write';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $a) => $a->value, self::cases());
    }
}
