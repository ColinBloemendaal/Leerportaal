<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * One case per notification a reseller can override with their own
 * subject + Markdown body -- see App\Models\ResellerMailTemplate.
 *
 * The placeholder tokens each case declares are the ONLY substitutions
 * ever applied to reseller-authored text (App\Support\Mail\PlaceholderRenderer
 * does plain string replacement against this fixed whitelist, never a
 * templating engine, so there is no way for override text to reach
 * arbitrary PHP/Blade execution).
 */
enum MailTemplateType: string
{
    case UserInvited = 'user_invited';

    public function label(): string
    {
        return match ($this) {
            self::UserInvited => 'User invitation',
        };
    }

    /**
     * Token => human-readable description, shown in the editor UI so
     * resellers know what they can reference in their Markdown body.
     *
     * @return array<string, string>
     */
    public function placeholders(): array
    {
        return match ($this) {
            self::UserInvited => [
                'reseller_name' => 'The reseller organization\'s name',
                'invitee_name' => 'The name of the person being invited',
                'accept_url' => 'The link to accept the invitation',
            ],
        };
    }

    public function defaultSubject(): string
    {
        return match ($this) {
            self::UserInvited => '{{ reseller_name }} invited you to Leerportaal',
        };
    }
}
