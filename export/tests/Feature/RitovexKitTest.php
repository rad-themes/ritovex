<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Stache;
use Tests\TestCase;

class RitovexKitTest extends TestCase
{
    public function test_homepage_renders_ritovex_content_and_native_navigation(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk()->assertSeeText('Automate complex operations with intelligent workflows.');
        $response->assertSee('href="/services"', false);
        $response->assertSee('mailto:hello@ritovex.test', false);
        $response->assertDontSee('webflow.js');
    }

    public function test_feature_detail_renders_from_a_collection_entry(): void
    {
        $this->withoutVite();

        $response = $this->get('/services/ai-automation-engine');

        $response->assertOk()->assertSeeText('AI Automation Engine');
        $response->assertSeeText('Intelligent decision loops powered by custom LLM models.');
    }

    public function test_contact_page_uses_a_real_form_with_validation_fields(): void
    {
        $this->withoutVite();

        $response = $this->get('/contact');

        $response->assertOk()->assertSee('method="POST"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="_token"', false);
    }

    #[DataProvider('pageRoutes')]
    public function test_each_content_route_renders_with_a_title(string $route, string $title): void
    {
        $this->withoutVite();

        $this->get($route)->assertOk()->assertSeeText($title);
    }

    /** @return array<string, array{string, string}> */
    public static function pageRoutes(): array
    {
        return [
            'features' => ['/services', 'Features'],
            'integrations' => ['/cases', 'Integrations'],
            'pricing' => ['/pricing', 'Pricing'],
            'blog' => ['/blog', 'Blog'],
            'contact' => ['/contact', 'Contact'],
            'ai engine' => ['/services/ai-automation-engine', 'AI Automation Engine'],
            'analytics' => ['/services/real-time-analytics', 'Real-time Analytics'],
            'slack integration' => ['/cases/slack', 'Slack Integration'],
            'github integration' => ['/cases/github', 'GitHub Integration'],
            'scaling saas article' => ['/blog/scaling-saas-workflows', 'How to scale SaaS workflows with AI'],
            'observability article' => ['/blog/real-time-observability-guide', 'A guide to real-time observability'],
        ];
    }

    public function test_invalid_quote_is_rejected_by_server_validation(): void
    {
        $this->postJson('/!/forms/quote', [
            'email' => 'invalid-email',
            'message' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_valid_quote_is_stored_and_honeypot_submission_is_discarded(): void
    {
        Mail::fake();
        $submissionDirectory = sys_get_temp_dir().'/ritovex-forms-'.bin2hex(random_bytes(8));
        Stache::store('form-submissions')->directory($submissionDirectory.'/');

        try {
            $details = [
                'name' => 'Ritovex Customer',
                'email' => 'dev@company.com',
                'message' => 'We would like to test the AI automation runtime.',
            ];

            $this->postJson('/!/forms/quote', $details)
                ->assertOk()->assertJson(['success' => true, 'submission_created' => true]);

            $submissions = FormSubmission::whereForm('quote');
            $this->assertCount(1, $submissions);
            $this->assertSame($details['email'], $submissions->first()->get('email'));
            $this->assertFileExists($submissions->first()->path());

            $this->postJson('/!/forms/quote', [...$details, 'website' => 'spam.example.com'])
                ->assertOk()->assertJson(['success' => true, 'submission_created' => false]);

            $this->assertCount(1, FormSubmission::whereForm('quote'));
            Mail::assertNothingSent();
        } finally {
            File::deleteDirectory($submissionDirectory);
        }
    }

    public function test_missing_page_shows_the_custom_not_found_page(): void
    {
        $this->withoutVite();

        $this->get('/missing-ritovex-page')->assertNotFound()->assertSeeText('404');
    }
}
