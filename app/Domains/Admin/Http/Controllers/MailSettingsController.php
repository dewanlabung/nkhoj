<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Validators\MailCredentialsValidator;
use Illuminate\Http\Request;

class MailSettingsController extends BaseAdminController
{
    public function edit()
    {
        $this->requireAdmin();

        $settings = [
            'mail_mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];

        return view('admin.mail-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->requireAdmin();

        $data = $request->validate([
            'mail_mailer' => 'required|in:smtp,sendmail,log',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // If testing, validate SMTP credentials
        if ($request->boolean('test')) {
            $validator = new MailCredentialsValidator();
            $error = $validator->validate($data);

            if ($error) {
                return back()->withErrors(['mail' => $error])->withInput();
            }

            return back()->with('success', 'SMTP configuration is working correctly!');
        }

        // Save to .env file
        $this->saveToEnv($data);

        return back()->with('success', 'Mail settings saved successfully.');
    }

    private function saveToEnv(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $envKey = strtoupper($key);
            $pattern = "/^{$envKey}=.*/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$envKey}=" . (is_null($value) ? '' : $value), $envContent);
            } else {
                $envContent .= "\n{$envKey}=" . (is_null($value) ? '' : $value);
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
