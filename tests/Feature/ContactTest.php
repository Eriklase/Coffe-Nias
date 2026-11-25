<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_valid_messages_and_rejects_invalid_email_addresses(): void
    {
        Mail::fake();

        $this->from(route('home'))
            ->post(route('contact.send'), [
                'nama' => 'Pelanggan',
                'email' => 'pelanggan@example.com',
                'pesan' => 'Saya ingin bertanya tentang menu kopi.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessage::class, function (ContactMessage $message): bool {
            return $message->data['email'] === 'pelanggan@example.com';
        });

        $this->from(route('home'))
            ->post(route('contact.send'), [
                'nama' => 'Pelanggan',
                'email' => 'bukan-email',
                'pesan' => 'Pesan tidak boleh terkirim.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');

        Mail::assertSent(ContactMessage::class, 1);
    }
}
