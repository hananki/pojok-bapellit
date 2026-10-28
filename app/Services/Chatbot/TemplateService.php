<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotTemplate;
use Illuminate\Support\Collection;

/**
 * LAPIS 5: Template jawaban (database + config/chatbot_templates.php) dan menu cepat.
 */
class TemplateService
{
    /**
     * Daftar menu cepat (DB + Config).
     */
    public function menus(): Collection
    {
        $dbMenus = ChatbotTemplate::where('is_active', true)
            ->get(['id', 'label'])
            ->map(fn ($tpl) => [
                'id' => 'db_'.$tpl->id,
                'label' => $tpl->label,
            ])
            ->values();

        $configMenus = collect(config('chatbot_templates', []))
            ->map(fn ($tpl) => [
                'id' => $tpl['id'],
                'label' => $tpl['label'],
            ])
            ->values();

        return $dbMenus->merge($configMenus)->values();
    }

    /**
     * Ambil template berdasarkan ID (tombol menu). Return null jika tidak ditemukan.
     */
    public function find(string $id): ?array
    {
        if (str_starts_with($id, 'db_')) {
            $tpl = ChatbotTemplate::find(substr($id, 3));

            if ($tpl && $tpl->is_active) {
                return [
                    'reply' => $tpl->reply,
                    'source' => 'database_template',
                    'id' => $id,
                ];
            }

            return null;
        }

        $tpl = collect(config('chatbot_templates', []))->firstWhere('id', $id);

        if ($tpl) {
            return [
                'reply' => $tpl['reply'],
                'source' => 'config_template',
                'id' => $id,
            ];
        }

        return null;
    }

    /**
     * Normalisasi keywords.
     */
    public function normalizeKeywords(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $raw = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $raw);
        }

        return array_values(array_filter(
            array_map(fn ($k) => mb_strtolower(trim((string) $k)), (array) $raw),
            fn ($k) => $k !== ''
        ));
    }

    /**
     * Match template. Prioritas: Database > Config.
     */
    public function match(string $message): ?array
    {
        $lower = mb_strtolower($message);

        $dbTemplate = ChatbotTemplate::where('is_active', true)
            ->get()
            ->first(function ($t) use ($lower) {
                foreach ($this->normalizeKeywords($t->keywords) as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        return true;
                    }
                }

                return false;
            });

        if ($dbTemplate) {
            return [
                'reply' => $dbTemplate->reply,
                'id' => 'db_'.$dbTemplate->id,
                'source' => 'database_template',
            ];
        }

        foreach (config('chatbot_templates', []) as $tpl) {
            foreach ($this->normalizeKeywords($tpl['keywords'] ?? []) as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return [
                        'reply' => $tpl['reply'],
                        'id' => $tpl['id'],
                        'source' => 'config_template',
                    ];
                }
            }
        }

        return null;
    }

    public function allTemplates(): array
    {
        $list = [];

        foreach (ChatbotTemplate::where('is_active', true)->get(['label', 'reply', 'keywords']) as $t) {
            $list[] = [
                'label' => (string) $t->label,
                'reply' => (string) $t->reply,
                'keywords' => $t->keywords,
            ];
        }

        foreach (config('chatbot_templates', []) as $t) {
            if (empty($t['label']) || empty($t['reply'])) {
                continue;
            }
            $list[] = [
                'label' => (string) $t['label'],
                'reply' => (string) $t['reply'],
                'keywords' => $t['keywords'] ?? [],
            ];
        }

        return $list;
    }
}
