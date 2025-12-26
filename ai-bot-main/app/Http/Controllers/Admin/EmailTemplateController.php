<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $emailTemplates = EmailTemplate::query()
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.email-templates.index', compact('emailTemplates'));
    }

    public function show(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load('translations');

        // Пытаемся получить английский перевод
        $translation = $emailTemplate->translations
            ->where('locale', 'en')
            ->pluck('value', 'code')
            ->toArray();

        // Если нет английского перевода, пытаемся получить русский
        if (!isset($translation['title']) || !isset($translation['message'])) {
            $ruTranslation = $emailTemplate->translations
                ->where('locale', 'ru')
                ->pluck('value', 'code')
                ->toArray();
            
            if (isset($ruTranslation['title']) && isset($ruTranslation['message'])) {
                $translation = $ruTranslation;
            } else {
                abort(404, 'Translation not found for this email template.');
            }
        }

        $params = [
            'service_name' => 'ChatGPT',
            'amount' => '10.00 USD',
            'email' => 'john@example.com',
            'url' => url('/reset-password/example')
        ];

        // Используем специальные шаблоны для предпросмотра
        $subject = \App\Services\EmailService::renderTemplate($translation['title'], $params);
        $body = null;
        
        switch ($emailTemplate->code) {
            case 'reset_password':
                $body = view('emails.reset-password', [
                    'translation' => $translation,
                    'url' => $params['url'],
                    'email' => $params['email'],
                ])->render();
                break;
            case 'subscription_activated':
                $body = view('emails.templates.subscription-activated', $params)->render();
                break;
            case 'payment_confirmation':
                $body = view('emails.templates.payment-confirmation', $params)->render();
                break;
            case 'subscription_expiring':
                $body = view('emails.templates.subscription-expiring', $params)->render();
                break;
            default:
                // Для остальных шаблонов используем сообщение из БД
                $body = \App\Services\EmailService::renderTemplate($translation['message'], $params);
                break;
        }

        return view('emails.base', [
            'subject' => $subject,
            'body' => $body,
            'button' => false, // Отключаем кнопку по умолчанию для предпросмотра
        ]);
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load('translations');
        $emailTemplateData = $emailTemplate->translations->groupBy('locale')->map(function ($translations) {
            return $translations->pluck('value', 'code')->toArray();
        });

        return view('admin.email-templates.edit', compact('emailTemplate', 'emailTemplateData'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate($this->getRules());

        $emailTemplate->update($validated);
        $emailTemplate->saveTranslation($validated);

        $route = $request->has('save')
            ? route('admin.email-templates.edit', $emailTemplate->id)
            : route('admin.email-templates.index');

        return redirect($route)->with('success', 'Email template successfully updated.');
    }

    private function getRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        foreach (config('langs') as $lang => $flag) {
            foreach(EmailTemplate::TRANSLATION_FIELDS as $field) {
                $rules[$field . '.' . $lang] = ['nullable', 'string'];
            }
        }

        return $rules;
    }
}
